<?php
include "auth.php";
include "config.php";

/*
|--------------------------------------------------------------------------
| DUE FOR RENEWAL
|--------------------------------------------------------------------------
| One employee = one record.
| We get the employee's latest contract only.
|
| Due Renewal = contract ending within the next 30 days.
|--------------------------------------------------------------------------
*/

$result = $conn->query("
    SELECT 
        c.employee_id,
        c.employee_name,
        c.position_title,
        c.start_date,
        c.end_date,
        c.status
    FROM contracts c
    INNER JOIN (
        SELECT 
            employee_id,
            MAX(end_date) AS latest_end_date
        FROM contracts
        GROUP BY employee_id
    ) latest
        ON c.employee_id = latest.employee_id
        AND c.end_date = latest.latest_end_date
    WHERE c.end_date BETWEEN CURDATE()
        AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    ORDER BY c.end_date ASC
");

$totalDue = $result ? $result->num_rows : 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Due for Renewal Report | JOPMIS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

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

.badge-renewal{
    background:#f59e0b;
    color:white;
    font-size:13px;
    padding:8px 12px;
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

}

@media(max-width:768px){

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

            <h2>
                <i class="fas fa-sync-alt"></i>
                Due for Renewal Report
            </h2>

            <p class="mb-0">
                Personnel Contracts Ending Within 30 Days
            </p>

        </div>

        <div class="text-end">

            <h3><?= $totalDue; ?></h3>

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


<!-- =====================================================
     SEARCH + BUTTONS
===================================================== -->

<div class="d-flex justify-content-between mb-3 flex-wrap gap-2">

    <input
        type="text"
        id="searchInput"
        class="form-control search-box"
        placeholder="Search Employee...">

    <div class="no-print">

        <button
            onclick="window.print()"
            class="btn btn-dark">

            <i class="fas fa-print"></i>
            Print

        </button>

        <a
            href="reports.php"
            class="btn btn-primary">

            <i class="fas fa-arrow-left"></i>
            Back

        </a>

    </div>

</div>


<!-- =====================================================
     TABLE
===================================================== -->

<div class="table-responsive">

<table class="table table-bordered table-hover">

<thead class="table-dark">

<tr>

<th>Employee ID</th>

<th>Employee Name</th>

<th>Position</th>

<th>Contract End Date</th>

<th>Days Remaining</th>

<th>Status</th>

<th class="no-print">Action</th>

</tr>

</thead>

<tbody>

<?php if($totalDue > 0){ ?>

<?php while($row = $result->fetch_assoc()){ 

    $daysLeft = ceil(
        (
            strtotime($row['end_date'])
            -
            strtotime(date('Y-m-d'))
        ) / 86400
    );

?>

<tr>

<td>
    <?= htmlspecialchars($row['employee_id']); ?>
</td>


<td class="fw-semibold">

    <?= htmlspecialchars($row['employee_name']); ?>

</td>


<td>

    <?= htmlspecialchars($row['position_title']); ?>

</td>


<td>

    <?= date(
        "F d, Y",
        strtotime($row['end_date'])
    ); ?>

</td>


<td class="days-box">

    <?= $daysLeft; ?> Day(s)

</td>


<td>

    <span class="badge badge-renewal">

        Due Renewal

    </span>

</td>


<td class="no-print">

    <a
        href="contract_history.php?id=<?= urlencode($row['employee_id']); ?>"
        class="btn btn-primary btn-sm history-btn">

        <i class="fas fa-history"></i>
        View History

    </a>

</td>

</tr>

<?php } ?>

<?php }else{ ?>

<tr>

<td colspan="7" class="text-center text-muted py-4">

    <i class="fas fa-check-circle fa-2x mb-2"></i>

    <br>

    No personnel are currently due for renewal.

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

</div>

</div>


<!-- =====================================================
     SEARCH
===================================================== -->

<script>

document
.getElementById("searchInput")
.addEventListener("keyup", function(){

    let value = this.value.toLowerCase();

    let rows = document.querySelectorAll("tbody tr");

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
