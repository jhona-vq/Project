<?php
include "auth.php";
include "config.php";

$id = $_GET['id'];

$result = $conn->query("
SELECT *
FROM leave_records
WHERE id='$id'
");

$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
<title>View Leave</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container mt-5">

<div class="card">

<div class="card-header">
<h4>Leave Details</h4>
</div>

<div class="card-body">

<p><strong>Leave ID:</strong> <?= $row['leave_id']; ?></p>

<p><strong>Employee ID:</strong> <?= $row['employee_id']; ?></p>

<p><strong>Name:</strong> <?= $row['employee_name']; ?></p>

<p><strong>Type:</strong> <?= $row['leave_type']; ?></p>

<p><strong>Start:</strong> <?= $row['start_date']; ?></p>

<p><strong>End:</strong> <?= $row['end_date']; ?></p>

<p><strong>Status:</strong> <?= $row['status']; ?></p>

<p><strong>Reason:</strong> <?= $row['reason']; ?></p>

<a href="leave.php" class="btn btn-secondary">
Back
</a>

</div>

</div>

</div>

</body>
</html>