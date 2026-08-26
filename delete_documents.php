<?php

include "config.php";

$id = $_GET['id'];

$conn->query("
DELETE FROM documents
WHERE id='$id'
");

header("Location: documents.php");