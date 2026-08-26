<?php
session_start();
include "config.php";

if(!isset($_SESSION['otp_verified'])){
    header("Location: login.php");
    exit();
}

$email = $_SESSION['reset_email'];

$message = "";

if(isset($_POST['reset'])){

    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    if($password !== $confirm){
        $message = "<div class='alert alert-danger'>Passwords do not match!</div>";
    }else{

        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("
            UPDATE users
            SET password=?,
                otp_code=NULL,
                otp_expiry=NULL
            WHERE email=?
        ");

        $stmt->bind_param("ss",$hashed,$email);
        $stmt->execute();

        session_destroy();

        echo "
        <script>
        alert('Password Changed Successfully');
        window.location='login.php';
        </script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

    <style>
        body{
            background: linear-gradient(135deg,#0f172a,#1e3a8a);
            height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            font-family:Segoe UI;
        }

        .card{
            width:100%;
            max-width:420px;
            border:none;
            border-radius:15px;
            box-shadow:0 10px 30px rgba(0,0,0,.3);
        }

        .card-header{
            background:#2563eb;
            color:white;
            text-align:center;
            font-weight:bold;
            font-size:18px;
            border-radius:15px 15px 0 0;
        }

        .form-control{
            border-radius:10px;
            padding:10px;
        }

        .btn-primary{
            width:100%;
            padding:10px;
            font-weight:600;
        }

        .input-group-text{
            cursor:pointer;
            background:#fff;
        }
    </style>
</head>
<body>

<div class="card">

    <div class="card-header">
        Reset Your Password
    </div>

    <div class="card-body p-4">

        <?php if($message) echo $message; ?>

        <form method="POST">

            <!-- PASSWORD -->
            <label>New Password</label>
            <div class="input-group mb-3">
                <input type="password" id="password" name="password" class="form-control" required>
                <span class="input-group-text" onclick="togglePassword('password', this)">
                    <i class="fa-solid fa-eye"></i>
                </span>
            </div>

            <!-- CONFIRM PASSWORD -->
            <label>Confirm Password</label>
            <div class="input-group mb-3">
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                <span class="input-group-text" onclick="togglePassword('confirm_password', this)">
                    <i class="fa-solid fa-eye"></i>
                </span>
            </div>

            <button type="submit" name="reset" class="btn btn-primary">
                Update Password
            </button>

        </form>

    </div>

</div>

<script>
function togglePassword(fieldId, iconBox){

    const input = document.getElementById(fieldId);
    const icon = iconBox.querySelector("i");

    if(input.type === "password"){
        input.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    }else{
        input.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}
</script>

</body>
</html>