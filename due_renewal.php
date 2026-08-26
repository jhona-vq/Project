<?php
include "auth.php";
include "config.php";

$result = $conn->query("
SELECT *
FROM contracts
WHERE end_date BETWEEN CURDATE()
AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
ORDER BY end_date ASC
");

$totalDue = $result->num_rows;
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

@media print{

    .no-print{
        display:none;
    }

    body{
        background:white;
    }

}

</style>

</head>
<body>

<div class="container-fluid p-4">

<div class="report-header">

    <div class="d-flex justify-content-between align-items-center">

        <div>
            <h2>
                <i class="fas fa-sync-alt"></i>
                Due for Renewal Report
            </h2>

            <p class="mb-0">
                Personnel Contracts Terminating Within 30 Days
            </p>
        </div>

        <div>
            <h3><?= $totalDue; ?></h3>
            <small>Total Due Renewal</small>
        </div>

    </div>

</div>

<div class="card">

<div class="card-body">

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
</tr>

</thead>

<tbody>

<?php while($row = $result->fetch_assoc()){

$daysLeft = floor(
(
strtotime($row['end_date'])
-
time()
)
/
86400
);

?>

<tr>

<td><?= $row['employee_id']; ?></td>

<td><?= $row['employee_name']; ?></td>

<td><?= $row['position_title']; ?></td>

<td><?= date("F d, Y", strtotime($row['end_date'])); ?></td>

<td class="days-box">

<?= $daysLeft; ?> Day(s)

</td>

<td>

<span class="badge badge-renewal">

Due Renewal

</span>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

</div>

</div>

<script>

document.getElementById("searchInput")
.addEventListener("keyup", function(){

let value =
this.value.toLowerCase();

let rows =
document.querySelectorAll("tbody tr");

rows.forEach(function(row){

row.style.display =
row.innerText.toLowerCase()
.includes(value)
? ""
: "none";

});

});

</script>

</body>
</html>