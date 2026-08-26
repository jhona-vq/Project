<?php

if (!isset($_SESSION)) {
    session_start();
}

$currentRole = $_SESSION['role'] ?? null;

function allowRoles($roles)
{
    global $currentRole;

    if (!$currentRole || !in_array($currentRole, $roles)) {
        header('Location: dashboard.php');
        exit();
    }
}
?>