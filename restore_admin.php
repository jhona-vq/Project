<?php
include "config.php";

$full_name = "Jhona Villanueva";
$email = "jhona12@gmail.com";
$password = password_hash("admin123", PASSWORD_DEFAULT);
$role = "System Administrator";
$status = "Active";

$conn->query("INSERT INTO users 
(full_name, email, password, role, status)
VALUES 
('$full_name','$email','$password','$role','$status')");

echo "System Administrator restored successfully!";
?>