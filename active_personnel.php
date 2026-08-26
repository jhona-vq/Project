<?php
include "auth.php";
include "config.php";

$result = $conn->query("
SELECT *
FROM contracts
WHERE status='Active'
ORDER BY employee_name ASC
");

$totalActive = $result->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Active Personnel Report | JOPMIS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

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

.badge-active{
    background:#22c55e;
    color:white;
    font-size:13px;
    padding:8px 12px;
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
<i class="fas fa-user-check"></i>
Active Personnel Report
</h2>

<p class="mb-0">
List of Personnel with Active Contracts
</p>

</div>

<div>

<h3><?= $totalActive; ?></h3>
<small>Total Active Personnel</small>

</div>

</div>

</div>

<div class="card">

<div class="card-body">

<div class="d-flex justify-content-between flex-wrap gap-2 mb-3">

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

<?php while($row = $result->fetch_assoc()){ ?>

<tr>

<td><?= $row['employee_id']; ?></td>

<td><?= $row['employee_name']; ?></td>

<td><?= $row['position_title']; ?></td>

<td>
<?= date("F d, Y", strtotime($row['start_date'])); ?>
</td>

<td>
<?= date("F d, Y", strtotime($row['end_date'])); ?>
</td>

<td>

<span class="badge badge-active">
Active
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

let value = this.value.toLowerCase();

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