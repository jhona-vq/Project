<?php

include "auth.php";
include "config.php";


/* =========================================================
   DUE FOR RENEWAL REPORT
   =========================================================
   RULES:

   1. Only ACTIVE contracts are considered.
   2. Contract end date must be TODAY up to 30 days from TODAY.
   3. One employee = ONE record.
   4. If an employee has multiple active contracts,
      only the latest active contract is shown.
   5. Historical / expired / terminated contracts are ignored.
   6. This report DOES NOT change any contract status.
========================================================= */


/* =========================================================
   GET DUE FOR RENEWAL CONTRACTS
   =========================================================
   RULES:

   1. ONLY contracts with status = Active.
   2. Latest Active contract per employee only.
   3. End date must be TODAY up to 30 DAYS from today.
   4. Terminated contracts are NOT included.
   5. Expired contracts are NOT included.
   6. This report does NOT change contract status.
========================================================= */

$result = $conn->query("
    SELECT
        c.id,
        c.contract_id,
        c.employee_id,
        c.employee_name,
        c.position_title,
        c.start_date,
        c.end_date,
        c.status

    FROM personnel p

    INNER JOIN contracts c
        ON c.employee_id = p.employee_id

    INNER JOIN (
        SELECT
            c2.employee_id,
            MIN(c2.end_date) AS nearest_end_date
        FROM contracts c2
        INNER JOIN personnel p2
            ON p2.employee_id = c2.employee_id
        WHERE LOWER(TRIM(p2.employment_status)) = 'active'
        AND c2.end_date BETWEEN CURDATE()
                            AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        GROUP BY c2.employee_id
    ) due
        ON due.employee_id = c.employee_id
        AND due.nearest_end_date = c.end_date

    WHERE LOWER(TRIM(p.employment_status)) = 'active'

    AND c.end_date BETWEEN CURDATE()
                       AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)

    ORDER BY
        c.end_date ASC,
        c.employee_name ASC
");
/* =========================================================
   CHECK QUERY ERROR
========================================================= */

if (!$result) {

    die(
        "Database Error: " .
        htmlspecialchars($conn->error)
    );

}


$totalDue = $result->num_rows;

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1"
>

<title>Due for Renewal Report | JOPMIS</title>


<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
>

<link
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    rel="stylesheet"
>


<style>

body{
    background:#f1f5f9;
    font-family:'Segoe UI',sans-serif;
}


.report-header{
    background:linear-gradient(135deg,#1e40af,#2563eb);
    color:white;
    padding:25px;
    border-radius:15px;
    margin-bottom:20px;
}


.card{
    border:none;
    border-radius:15px;
    box-shadow:0 5px 15px rgba(0,0,0,.08);
}


.table{
    vertical-align:middle;
}


.table thead th{
    white-space:nowrap;
}


.badge-renewal{
    background:#f59e0b;
    color:white;
    font-size:13px;
    padding:8px 12px;
    border-radius:8px;
}


.days-box{
    font-weight:bold;
    color:#dc2626;
}


.search-box{
    max-width:350px;
}


.history-btn{
    white-space:nowrap;
}


.empty-row{
    text-align:center;
    color:#64748b;
    padding:30px !important;
}


/* =========================================================
   PRINT
========================================================= */

@media print{

    .no-print{
        display:none !important;
    }

    body{
        background:white;
    }

    .report-header{
        background:#2563eb !important;
        -webkit-print-color-adjust:exact;
        print-color-adjust:exact;
    }

    .card{
        box-shadow:none;
    }

}


/* =========================================================
   MOBILE
========================================================= */

@media(max-width:768px){

    .container-fluid{
        padding:15px !important;
    }

    .report-header{
        padding:20px;
    }

    .report-header h2{
        font-size:22px;
    }

    .report-header .d-flex{
        gap:15px;
    }

}

</style>

</head>


<body>


<div class="container-fluid p-4">


<!-- =====================================================
     HEADER
===================================================== -->

<div class="report-header">

    <div class="d-flex justify-content-between align-items-center flex-wrap">

        <div>

            <h2 class="mb-1">

                <i class="fas fa-sync-alt"></i>

                Due for Renewal Report

            </h2>


            <p class="mb-0">

                Active Personnel Contracts Ending Within 30 Days

            </p>

        </div>


        <div class="text-end">

            <h3 class="mb-0">

                <?= $totalDue; ?>

            </h3>


            <small>

                Total Personnel Due for Renewal

            </small>

        </div>

    </div>

</div>



<!-- =====================================================
     TABLE CARD
===================================================== -->

<div class="card">

    <div class="card-body">


        <!-- =================================================
             SEARCH + BUTTONS
        ================================================= -->

        <div
            class="d-flex justify-content-between mb-3 flex-wrap gap-2"
        >


            <input
                type="text"
                id="searchInput"
                class="form-control search-box"
                placeholder="Search Employee..."
            >


            <div class="no-print">


                <button
                    onclick="window.print()"
                    class="btn btn-dark"
                >

                    <i class="fas fa-print"></i>

                    Print

                </button>


                <a
                    href="reports.php"
                    class="btn btn-primary"
                >

                    <i class="fas fa-arrow-left"></i>

                    Back

                </a>


            </div>

        </div>



        <!-- =================================================
             TABLE
        ================================================= -->

        <div class="table-responsive">


            <table
                class="table table-bordered table-hover"
                id="renewalTable"
            >


                <thead class="table-dark">

                    <tr>

                        <th>Employee ID</th>

                        <th>Employee Name</th>

                        <th>Position</th>

                        <th>Contract End Date</th>

                        <th>Days Remaining</th>

                        <th>Status</th>

                        <th class="no-print">
                            Action
                        </th>

                    </tr>

                </thead>


                <tbody>


                <?php if($totalDue > 0): ?>


                    <?php while($row = $result->fetch_assoc()): ?>


                        <?php

                        /* =================================================
                           CALCULATE DAYS REMAINING
                        ================================================= */

                        $today = new DateTime(
                            date('Y-m-d')
                        );

                        $endDate = new DateTime(
                            $row['end_date']
                        );

                        $daysLeft = (int)$today->diff(
                            $endDate
                        )->format('%r%a');

                        ?>


                        <tr>


                            <!-- EMPLOYEE ID -->

                            <td>

                                <?= htmlspecialchars(
                                    $row['employee_id']
                                ); ?>

                            </td>



                            <!-- EMPLOYEE NAME -->

                            <td class="fw-semibold">

                                <?= htmlspecialchars(
                                    $row['employee_name']
                                ); ?>

                            </td>



                            <!-- POSITION -->

                            <td>

                                <?= htmlspecialchars(
                                    $row['position_title']
                                ); ?>

                            </td>



                            <!-- END DATE -->

                            <td>

                                <?= date(
                                    "d/m/Y",
                                    strtotime($row['end_date'])
                                ); ?>

                            </td>



                            <!-- DAYS REMAINING -->

                            <td class="days-box">

                                <?php if($daysLeft === 0): ?>

                                    <span class="text-danger fw-bold">

                                        Today

                                    </span>

                                <?php elseif($daysLeft === 1): ?>

                                    1 Day

                                <?php else: ?>

                                    <?= $daysLeft; ?> Days

                                <?php endif; ?>

                            </td>



                            <!-- STATUS -->

                            <td>

                                <span class="badge badge-renewal">

                                    <i class="fas fa-clock"></i>

                                    Due for Renewal

                                </span>

                            </td>



                            <!-- ACTION -->

                            <td class="no-print">

                                <a
                                    href="contract_history.php?employee_id=<?= urlencode($row['employee_id']); ?>"
                                    class="btn btn-primary btn-sm history-btn"
                                >

                                    <i class="fas fa-history"></i>

                                    View History

                                </a>

                            </td>


                        </tr>


                    <?php endwhile; ?>


                <?php else: ?>


                    <tr>

                        <td
                            colspan="7"
                            class="empty-row"
                        >

                            <i
                                class="fas fa-check-circle fa-2x mb-2"
                            ></i>

                            <br>

                            No active personnel contracts are
                            currently due for renewal.

                        </td>

                    </tr>


                <?php endif; ?>


                </tbody>


            </table>


        </div>


    </div>

</div>


</div>



<!-- =====================================================
     SEARCH SCRIPT
===================================================== -->

<script>

document
    .getElementById("searchInput")
    .addEventListener("keyup", function(){

        let value =
            this.value.toLowerCase();

        let rows =
            document.querySelectorAll(
                "#renewalTable tbody tr"
            );


        rows.forEach(function(row){

            row.style.display =
                row.innerText
                    .toLowerCase()
                    .includes(value)
                    ? ""
                    : "none";

        });

    });

</script>


</body>

</html>
