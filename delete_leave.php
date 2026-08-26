<?php

include "auth.php";
include "config.php";

$id = $_GET['id'];

$conn->query("
DELETE FROM leave_records
WHERE id='$id'
");

header("Location: leave.php");
exit();