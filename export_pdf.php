<?php
include "config.php";
?>

<!DOCTYPE html>
<html>
<head>
<title>Personnel Report</title>

<style>
body{
font-family:Arial;
}

table{
width:100%;
border-collapse:collapse;
}

table,th,td{
border:1px solid black;
padding:8px;
}
</style>

</head>

<body onload="window.print()">

<h2>Personnel Report</h2>

<table>

<tr>
<th>ID</th>
<th>Name</th>
<th>Position</th>
<th>Province</th>
<th>Status</th>
</tr>

<?php

$result = $conn->query("
SELECT *
FROM personnel
");

while($row = $result->fetch_assoc()){

$fullname =
$row['first_name'].' '.
$row['middle_name'].' '.
$row['last_name'];

?>

<tr>

<td><?= $row['employee_id']; ?></td>

<td><?= $fullname; ?></td>

<td><?= $row['position_title']; ?></td>

<td><?= $row['province']; ?></td>

<td><?= $row['employment_status']; ?></td>

</tr>

<?php } ?>

</table>

</body>
</html>