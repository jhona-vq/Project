<?php
include "auth.php";
include "config.php";

$employee_id = $_GET['id'] ?? '';

if($employee_id == ''){
    die("Invalid Employee.");
}

/* Personnel Information */
$getEmployee = $conn->query("
SELECT *
FROM personnel
WHERE employee_id='$employee_id'
");

if($getEmployee->num_rows == 0){
    die("Employee not found.");
}

$emp = $getEmployee->fetch_assoc();

/* Contract History */
$getContracts = $conn->query("
SELECT *
FROM contracts
WHERE employee_id='$employee_id'
ORDER BY start_date DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Contract History</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

<style>

body{
    background:#f1f5f9;
    font-family:'Segoe UI',sans-serif;
}

.header{
    background:linear-gradient(135deg,#1e3a8a,#2563eb);
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

</style>

</head>

<body>

<div class="container py-4">

<div class="header">

<h2>

<i class="fas fa-history"></i>

Contract History

</h2>

<p class="mb-0">

<?= $emp['last_name']; ?>,
<?= $emp['first_name']; ?>
<?= $emp['middle_name']; ?>

</p>

</div>

<div class="card mb-4">

<div class="card-body">

<div class="row">

<div class="col-md-4">

<strong>Employee ID</strong><br>

<?= $emp['employee_id']; ?>

</div>

<div class="col-md-4">

<strong>Position</strong><br>

<?= $emp['position_title']; ?>

</div>

<div class="col-md-4">

<strong>Office</strong><br>

<?= $emp['office_assignment']; ?>

</div>

</div>

</div>

</div>

<div class="card">

<div class="card-header bg-primary text-white">

<h5 class="mb-0">

All Contracts

</h5>

</div>

<div class="card-body">

<div class="table-responsive">

<table class="table table-bordered table-hover">

<thead class="table-dark">

<tr>

<th>Contract ID</th>

<th>Start Date</th>

<th>End Date</th>

<th>Position</th>

<th>Monthly Rate</th>

<th>Status</th>

</tr>

</thead>

<tbody>

<?php

if($getContracts->num_rows > 0){

while($row = $getContracts->fetch_assoc()){

$status = strtolower($row['status']);

$badge = "secondary";

if($status=="active"){
    $badge="success";
}
elseif($status=="expired"){
    $badge="danger";
}
elseif($status=="renewed"){
    $badge="primary";
}
elseif($status=="terminated"){
    $badge="dark";
}

?>

<tr>

<td><?= $row['contract_id']; ?></td>

<td><?= date('M d, Y',strtotime($row['start_date'])); ?></td>

<td><?= date('M d, Y',strtotime($row['end_date'])); ?></td>

<td><?= $row['position_title']; ?></td>

<td>

₱<?= number_format($row['monthly_rate'],2); ?>

</td>

<td>

<span class="badge bg-<?= $badge; ?>">

<?= strtoupper($row['status']); ?>

</span>

</td>

</tr>

<?php

}

}else{

?>

<tr>

<td colspan="6" class="text-center">

No Contract History Found.

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

<div class="mt-3">

<a href="javascript:history.back()"
class="btn btn-secondary">

<i class="fas fa-arrow-left"></i>

Back

</a>

<button
onclick="window.print()"
class="btn btn-primary">

<i class="fas fa-print"></i>

Print

</button>

</div>

</div>

</div>

</div>

</body>
</html>