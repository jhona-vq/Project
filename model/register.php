<?php
include "../config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    // Password Match
    if ($password !== $confirm) {
        echo "<script>
                alert('Passwords do not match!');
                window.history.back();
              </script>";
        exit();
    }

    // Check Existing Email
    $check = $conn->query("SELECT id FROM users WHERE email='$email'");

    if ($check && $check->num_rows > 0) {
        echo "<script>
                alert('Email already exists!');
                window.history.back();
              </script>";
        exit();
    }

    // Hash Password
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    // Insert User
    $sql = "INSERT INTO users (full_name, email, password, role, status)
            VALUES ('$fullname', '$email', '$hashed', 'User', 'Active')";

    if ($conn->query($sql)) {
        echo "<script>
                alert('Account created successfully!');
                window.location='../login.php';
              </script>";
    } else {
        echo 'Database Error: ' . $conn->error;
    }
}
?>