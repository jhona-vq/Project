<?php

include "config.php";

$id = $_GET['id'];

$result = $conn->query("
SELECT *
FROM documents
WHERE id='$id'
");

$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>

<title>View Document</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body>

<div class="container mt-5">

<div class="card shadow">

<div class="card-header bg-primary text-white">
<h4>Document Details</h4>
</div>

<div class="card-body">

<table class="table table-bordered">

<tr>
<th>Employee</th>
<td><?= $row['employee_name']; ?></td>
</tr>

<tr>
<th>Document Type</th>
<td><?= $row['document_type']; ?></td>
</tr>

<tr>
<th>Version</th>
<td><?= $row['version']; ?></td>
</tr>

<tr>
<th>Upload Date</th>
<td><?= $row['upload_date']; ?></td>
</tr>

<tr>
<th>File</th>

<td>

<a href="uploads/documents/<?= $row['file_name']; ?>"
target="_blank">
Open File
</a>

</td>

</tr>

</table>

<a href="documents.php" class="btn btn-secondary">
Back
</a>

</div>

</div>

</div>

</body>
</html>