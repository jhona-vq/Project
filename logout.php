<?php
session_start();

// alisin lang ang login session
unset($_SESSION['user_id']);
unset($_SESSION['full_name']);
unset($_SESSION['role']);

// gumawa ng message
$_SESSION['logout_success'] = "You have successfully logged out.";

header("Location: login.php");
exit();
?>