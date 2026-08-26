<?php
include "auth.php";
include "config.php";

$id = $_GET['id'];

$result = $conn->query("
SELECT *
FROM trainings
WHERE id='$id'
");

$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
<title>View Training</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container mt-4">

<div class="card">

<div class="card-header">
<h4>Training Details</h4>
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
<th>Training Title</th>
<td><?= $row['training_title']; ?></td>
</tr>

<tr>
<th>Organizer</th>
<td><?= $row['organizer']; ?></td>
</tr>

<tr>
<th>Date Conducted</th>
<td><?= $row['training_date']; ?></td>
</tr>

<tr>
<th>Hours</th>
<td><?= $row['hours']; ?> hrs</td>
</tr>

<tr>
<th>Training Type</th>
<td><?= $row['training_type']; ?></td>
</tr>

<tr>
<th>Certificate</th>
<td>

<?php if($row['certificate_file']!=""){ ?>

<a href="uploads/training/<?= $row['certificate_file']; ?>"
target="_blank"
class="btn btn-success">
View Certificate
</a>

<?php } else { ?>

No Certificate Uploaded

<?php } ?>

</td>
</tr>

</table>

<a href="training.php" class="btn btn-secondary">
Back
</a>

</div>
</div>

</div>

</body>
</html>