<?php
include "config.php";

$id = $_GET['id'];

$result = $conn->query("SELECT * FROM contracts WHERE id='$id'");
$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
<title>View Contract</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body>

<div class="container mt-5">

<div class="card shadow">

<div class="card-header bg-primary text-white">
<h4>Contract Details</h4>
</div>

<div class="card-body">

<table class="table table-bordered">

<tr>
<th width="250">Contract ID</th>
<td><?= $row['contract_id']; ?></td>
</tr>

<tr>
<th>Employee ID</th>
<td><?= $row['employee_id']; ?></td>
</tr>

<tr>
<th>Employee Name</th>
<td><?= $row['employee_name']; ?></td>
</tr>

<tr>
<th>Position</th>
<td><?= $row['position_title']; ?></td>
</tr>

<tr>
<th>Start Date</th>
<td><?= $row['start_date']; ?></td>
</tr>

<tr>
<th>End Date</th>
<td><?= $row['end_date']; ?></td>
</tr>

<tr>
<th>Status</th>
<td>
<span class="badge bg-success">
<?= $row['status']; ?>
</span>
</td>
</tr>

<tr>
<th>Appointment File</th>
<td>
<a href="uploads/contracts/<?= $row['appointment_file']; ?>" target="_blank">
View File
</a>
</td>
</tr>

<tr>
<th>Contract File</th>
<td>
<a href="uploads/contracts/<?= $row['contract_file']; ?>" target="_blank">
View File
</a>
</td>
</tr>

<tr>
<th>Renewal File</th>
<td>
<a href="uploads/contracts/<?= $row['renewal_file']; ?>" target="_blank">
View File
</a>
</td>
</tr>

<tr>
<th>Certification File</th>
<td>
<a href="uploads/contracts/<?= $row['certification_file']; ?>" target="_blank">
View File
</a>
</td>
</tr>

</table>

<a href="contracts.php" class="btn btn-secondary">
Back
</a>

</div>

</div>

</div>

</body>
</html>