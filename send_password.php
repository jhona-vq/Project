<?php
include "config.php";

$id = $_GET['id'];

$get = $conn->query("
SELECT *
FROM personnel
WHERE id='$id'
");

$row = $get->fetch_assoc();

$email = $row['email'];

mail(
$email,
"JOPMIS Account",
"Your account has been created."
);

header(
"Location: personnel.php?id=".$id
);
?>