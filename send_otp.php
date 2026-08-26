<?php
include "config.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_POST['email'])){
    die("Invalid access. Please use forgot password page.");
}

$email = trim($_POST['email']);

$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

$success = false;
$otp = "";

if($result->num_rows > 0){

    $otp = rand(100000, 999999);

    $_SESSION['otp'] = $otp;
    $_SESSION['email'] = $email;
    $_SESSION['otp_time'] = time();

    $success = true;

}else{
    $success = false;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Send OTP</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

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
            overflow:hidden;
        }

        .header{
            background:#2563eb;
            color:white;
            padding:15px;
            text-align:center;
            font-weight:bold;
            font-size:18px;
        }

        .body{
            padding:25px;
            text-align:center;
        }

        .otp-box{
            font-size:28px;
            font-weight:bold;
            letter-spacing:5px;
            color:#1e293b;
            background:#f1f5f9;
            padding:15px;
            border-radius:10px;
            margin:15px 0;
        }

        .btn-primary{
            width:100%;
            padding:10px;
            font-weight:600;
        }

        .error{
            color:red;
            font-weight:600;
        }

        .success{
            color:green;
            font-weight:600;
        }

        a{
            text-decoration:none;
        }
    </style>
</head>
<body>

<div class="card">

    <div class="header">
        OTP Verification
    </div>

    <div class="body">

        <?php if($success){ ?>

            <div class="success mb-2">OTP Generated Successfully</div>

            <p>Your OTP Code:</p>

            <div class="otp-box">
                <?= $otp ?>
            </div>

            <a href="verify_otp.php" class="btn btn-primary">
                Continue Verification
            </a>

        <?php } else { ?>

            <div class="error">Email not found!</div>

            <a href="forgot_password.php" class="btn btn-primary mt-3">
                Go Back
            </a>

        <?php } ?>

    </div>

</div>

</body>
</html>