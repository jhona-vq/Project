<?php

include "config.php";

$id = $_GET['id'];

$conn->query("
DELETE FROM users
WHERE id='$id'
");

header("Location: users.php");
exit();

?>