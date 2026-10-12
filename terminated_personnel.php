<?php

include "auth.php";
include "config.php";


/* =========================================================
   PAGINATION
========================================================= */

$limit = isset($_GET['limit'])
    ? (int)$_GET['limit']
    : 10;

if ($limit < 1) {
    $limit = 10;
}


$page = isset($_GET['page'])
    ? (int)$_GET['page']
    : 1;

if ($page < 1) {
    $page = 1;
}


$start = ($page - 1) * $limit;


/* =========================================================
   COUNT CURRENT TERMINATED PERSONNEL

   BASIS:
   Latest contract of each employee must be TERMINATED.

   Old terminated contracts are ignored if the employee
   already has a newer Active contract.
========================================================= */

$countResult = $conn->query("
    SELECT COUNT(*) AS total
    FROM contracts c

    INNER JOIN (
        SELECT
            employee_id,
            MAX(id) AS latest_id
        FROM contracts
        GROUP BY employee_id
    ) latest
        ON c.employee_id = latest.employee_id
        AND c.id = latest.latest_id

    WHERE LOWER(TRIM(c.status)) = 'terminated'
");


$totalTerminated = 0;

if ($countResult) {

    $countRow = $countResult->fetch_assoc();

    $totalTerminated = (int)$countRow['total'];

}


$total_pages = ($totalTerminated > 0)
    ? ceil($totalTerminated / $limit)
    : 1;


/* =========================================================
   GET CURRENT TERMINATED PERSONNEL

   Only the LATEST contract of each employee is checked.

   If the latest contract is Terminated,
   the employee appears here.

   If the latest contract is Active,
   the employee does NOT appear here even if
   they have an older terminated contract.
========================================================= */

$result = $conn->query("
    SELECT c.*
    FROM contracts c

    INNER JOIN (
        SELECT
            employee_id,
            MAX(id) AS latest_id
        FROM contracts
        GROUP BY employee_id
    ) latest

        ON c.employee_id = latest.employee_id
        AND c.id = latest.latest_id

    WHERE LOWER(TRIM(c.status)) = 'terminated'

    ORDER BY
        c.end_date DESC,
        c.employee_name ASC

    LIMIT $start, $limit
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

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1"
>

<title>Terminated Personnel Report | JOPMIS</title>


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
    background:linear-gradient(135deg,#991b1b,#dc2626);
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

.badge-terminated{
    background:#dc2626;
    color:white;
    font-size:13px;
    padding:8px 12px;
    border-radius:8px;
}

.days-terminated{
    color:#dc2626;
    font-weight:bold;
}

.search-box{
    max-width:350px;
}

.empty-row{
    text-align:center;
    color:#64748b;
    padding:30px !important;
}

.pagination{
    margin-bottom:0;
}


@media print{

    .no-print{
        display:none !important;
    }

    body{
        background:white;
    }

    .report-header{
        background:#dc2626 !important;
        -webkit-print-color-adjust:exact;
        print-color-adjust:exact;
    }

    .card{
        box-shadow:none;
    }

}


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

    <div
        class="d-flex justify-content-between align-items-center flex-wrap"
    >


        <div>

            <h2 class="mb-1">

                <i class="fas fa-user-times"></i>

                Terminated Personnel Report

            </h2>


            <p class="mb-0">

                Personnel with Terminated Contracts

            </p>

        </div>


        <div class="text-end">

            <h3 class="mb-0">

                <?= $totalTerminated; ?>

            </h3>


            <small>

                Total Terminated Personnel

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
            class="d-flex justify-content-between flex-wrap gap-2 mb-3"
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
                id="terminatedTable"
            >


                <thead class="table-dark">

                    <tr>

                        <th>Employee ID</th>

                        <th>Employee Name</th>

                        <th>Position</th>

                        <th>Contract End Date</th>

                        <th>Days Terminated</th>

                        <th>Status</th>

                    </tr>

                </thead>


                <tbody>


                <?php if($result->num_rows > 0): ?>


                    <?php
                    while($row = $result->fetch_assoc()):

                        $daysTerminated = 0;

                        if (
                            !empty($row['end_date']) &&
                            $row['end_date'] !== '0000-00-00'
                        ) {
                            $endDate = new DateTime($row['end_date']);
                            $today = new DateTime(date('Y-m-d'));

                            // Bilang ng araw mula sa contract end date
                            if ($endDate < $today) {
                                $daysTerminated = $endDate->diff($today)->days;
                            }
                        }
                    ?>


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
                                        $row['employee_name']
                                    ); ?>

                                </a>

                            </td>



                            <!-- POSITION -->

                            <td>

                                <?= htmlspecialchars(
                                    $row['position_title']
                                ); ?>

                            </td>



                            <!-- CONTRACT END DATE -->

                            <td>

                                <?php

                                if(
                                    !empty($row['end_date']) &&
                                    $row['end_date'] !== '0000-00-00'
                                ){

                                    echo date(
                                        "F d, Y",
                                        strtotime($row['end_date'])
                                    );

                                }else{

                                    echo "-";

                                }

                                ?>

                            </td>



                            <!-- DAYS TERMINATED -->

                            <td class="days-terminated">

                                <?php if($daysTerminated > 0): ?>

                                    <?= $daysTerminated; ?> Day(s)

                                <?php else: ?>

                                    <span class="text-muted">
                                        N/A
                                    </span>

                                <?php endif; ?>

                            </td>



                            <!-- STATUS -->

                            <td>

                                <span class="badge badge-terminated">

                                    <i class="fas fa-times-circle"></i>

                                    Terminated

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

                            <i
                                class="fas fa-users-slash fa-2x mb-2"
                            ></i>

                            <br>

                            No terminated personnel found.

                        </td>

                    </tr>


                <?php endif; ?>


                </tbody>


            </table>


        </div>



        <!-- =================================================
             PAGINATION
        ================================================= -->

        <?php if($total_pages > 1): ?>

        <nav class="mt-3">

            <ul class="pagination justify-content-center">


                <!-- PREVIOUS -->

                <?php if($page > 1): ?>

                    <li class="page-item">

                        <a
                            class="page-link"
                            href="?page=<?= $page - 1; ?>&limit=<?= $limit; ?>"
                        >

                            Previous

                        </a>

                    </li>

                <?php endif; ?>



                <!-- PAGE NUMBERS -->

                <?php for(
                    $i = 1;
                    $i <= $total_pages;
                    $i++
                ): ?>


                    <li
                        class="page-item
                        <?= ($i == $page) ? 'active' : ''; ?>"
                    >

                        <a
                            class="page-link"
                            href="?page=<?= $i; ?>&limit=<?= $limit; ?>"
                        >

                            <?= $i; ?>

                        </a>

                    </li>


                <?php endfor; ?>



                <!-- NEXT -->

                <?php if($page < $total_pages): ?>

                    <li class="page-item">

                        <a
                            class="page-link"
                            href="?page=<?= $page + 1; ?>&limit=<?= $limit; ?>"
                        >

                            Next

                        </a>

                    </li>

                <?php endif; ?>


            </ul>

        </nav>

        <?php endif; ?>


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
                "#terminatedTable tbody tr"
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
