<?php
include "config.php";
include "role_access.php";

allowRoles([
    'System Administrator',
    'HR Administrator'
]);

if(isset($_GET['id'])){

    $id = $_GET['id'];

    $stmt = $conn->prepare("
    UPDATE personnel
    SET deleted = 1
    WHERE id = ?
    ");

    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: personnel.php");
exit();
?>