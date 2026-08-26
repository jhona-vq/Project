<?php
include "config.php";

$id=$_GET['id'];

$row=$conn->query("
SELECT *
FROM performance
WHERE id='$id'
")->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
<title>View Evaluation</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container mt-4">

<div class="card">

<div class="card-header">
<h4>Performance Evaluation Details</h4>
</div>

<div class="card-body">

<table class="table">

<tr>
<th>Employee ID</th>
<td><?= $row['employee_id']; ?></td>
</tr>

<tr>
<th>Employee Name</th>
<td><?= $row['employee_name']; ?></td>
</tr>

<tr>
<th>Evaluation Period</th>
<td><?= $row['evaluation_period']; ?></td>
</tr>

<tr>
<th>Rating</th>
<td><?= $row['rating']; ?></td>
</tr>

<tr>
<th>Evaluator</th>
<td><?= $row['evaluator']; ?></td>
</tr>

<tr>
<th>Comments</th>
<td><?= $row['comments']; ?></td>
</tr>

<tr>
<th>Status</th>
<td><?= $row['status']; ?></td>
</tr>

</table>

<a href="performance.php" class="btn btn-secondary">
Back
</a>

</div>

</div>

</div>

</body>
</html>