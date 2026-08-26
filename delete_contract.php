<?php

include "config.php";

$id = $_GET['id'];

$conn->query("
DELETE FROM contracts
WHERE id='$id'
");

header("Location: contracts.php");
exit();