<?php

include "auth.php";
include "config.php";


/* =========================================================
   COUNT ACTIVE PERSONNEL

   BASIS:
   personnel.employment_status = Active

   NOTE:
   Due Renewal is still Active.
========================================================= */

$countResult = $conn->query("
    SELECT COUNT(*) AS total
    FROM personnel
    WHERE LOWER(TRIM(employment_status)) = 'active'
");

$totalActive = 0;

if ($countResult) {
    $countRow = $countResult->fetch_assoc();
    $totalActive = (int)$countRow['total'];
}


/* =========================================================
   GET ACTIVE PERSONNEL

   BASIS:
   personnel.employment_status = Active

   We only get the latest contract information
   for display.
========================================================= */

$result = $conn->query("
    SELECT
        p.*,
        c.start_date,
        c.end_date,
        c.position_title AS contract_position

    FROM personnel p

    LEFT JOIN contracts c
        ON c.id = (
            SELECT MAX(c2.id)
            FROM contracts c2
            WHERE c2.employee_id = p.employee_id
        )

    WHERE LOWER(TRIM(p.employment_status)) = 'active'

    ORDER BY p.last_name ASC, p.first_name ASC
");


/* =========================================================
   CHECK QUERY ERROR
========================================================= */

if (!$result) {

    die(
        "Database Error: "
        . htmlspecialchars($conn->error)
    );

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1"
>

<title>Active Personnel Report | JOPMIS</title>


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
    background:linear-gradient(135deg,#15803d,#22c55e);
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

.badge-active{
    background:#22c55e;
    color:white;
    font-size:13px;
    padding:8px 12px;
    border-radius:8px;
}

.search-box{
    max-width:350px;
}

.empty-row{
    text-align:center;
    padding:30px !important;
    color:#64748b;
}

@media print{

    .no-print{
        display:none !important;
    }

    body{
        background:white;
    }

    .report-header{
        background:#198754 !important;
        color:white !important;
        box-shadow:none;
    }

    .card{
        box-shadow:none;
    }

}

</style>

</head>


<body>


<div class="container-fluid p-4">


<!-- =====================================================
     REPORT HEADER
===================================================== -->

<div class="report-header">

    <div class="d-flex justify-content-between align-items-center">

        <div>

            <h2 class="mb-1">
                <i class="fas fa-user-check"></i>
                Active Personnel Report
            </h2>

            <p class="mb-0">
                List of Personnel with Active Contracts
            </p>

        </div>


        <div class="text-end">

            <h3 class="mb-0">
                <?= $totalActive; ?>
            </h3>

            <small>
                Total Active Personnel
            </small>

        </div>

    </div>

</div>



<!-- =====================================================
     TABLE CARD
===================================================== -->

<div class="card">

    <div class="card-body">


        <!-- SEARCH + BUTTONS -->

        <div class="d-flex justify-content-between flex-wrap gap-2 mb-3">


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



        <!-- TABLE -->

        <div class="table-responsive">

            <table
                class="table table-bordered table-hover"
                id="personnelTable"
            >

                <thead class="table-success">

                    <tr>

                        <th>Employee ID</th>

                        <th>Employee Name</th>

                        <th>Position</th>

                        <th>Start Date</th>

                        <th>End Date</th>

                        <th>Status</th>

                    </tr>

                </thead>


                <tbody>


                <?php if($result->num_rows > 0): ?>


                    <?php while($row = $result->fetch_assoc()): ?>


                        <tr>


                            <!-- EMPLOYEE ID -->

                            <td>
                                <?= htmlspecialchars(
                                    $row['employee_id']
                                ); ?>
                            </td>



                            <!-- EMPLOYEE NAME -->

                            <td>

                                <a
                                    href="contract_history.php?employee_id=<?= urlencode($row['employee_id']); ?>"
                                    class="fw-bold text-decoration-none"
                                >

                                <?= htmlspecialchars(
                                    $row['last_name'] . ', ' .
                                    $row['first_name'] .
                                    (!empty($row['middle_name'])
                                        ? ' ' . $row['middle_name']
                                        : '')
                                ); ?>

                                </a>

                            </td>



                            <!-- POSITION -->

                            <td>

                                <?= htmlspecialchars(
                                    $row['position_title']
                                ); ?>

                            </td>



                            <!-- START DATE -->

                            <td>

                                <?php

                                if(
                                    !empty($row['start_date']) &&
                                    $row['start_date'] !== '0000-00-00'
                                ){

                                    echo date(
                                        "d F, Y",
                                        strtotime($row['start_date'])
                                    );

                                }else{

                                    echo "-";

                                }

                                ?>

                            </td>



                            <!-- END DATE -->

                            <td>

                                <?php

                                if(
                                    !empty($row['end_date']) &&
                                    $row['end_date'] !== '0000-00-00'
                                ){

                                    echo date(
                                        "d F, Y",
                                        strtotime($row['end_date'])
                                    );

                                }else{

                                    echo "-";

                                }

                                ?>

                            </td>



                            <!-- STATUS -->

                            <td>

                                <span class="badge badge-active">

                                    <i class="fas fa-check-circle"></i>
                                    Active

                                </span>

                            </td>


                        </tr>


                    <?php endwhile; ?>


                <?php else: ?>


                    <tr>

                        <td
                            colspan="6"
                            class="empty-row"
                        >

                            <i class="fas fa-users-slash fa-2x mb-2"></i>

                            <br>

                            No active personnel found.

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

        let value = this.value.toLowerCase();

        let rows =
            document.querySelectorAll("#personnelTable tbody tr");

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
