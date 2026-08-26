<?php

include "config.php";

$id=$_GET['id'];

$conn->query("
DELETE FROM performance
WHERE id='$id'
");

header("Location: performance.php");
exit();
?>