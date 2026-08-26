<?php
session_start();
include "config.php";

$email = $_SESSION['reset_email'] ?? '';
$user_otp = trim($_POST['otp'] ?? '');

$status = "";
$message = "";
$showReset = false;

if(empty($email)){
    $status = "error";
    $message = "SESSION LOST: Email not found. Please resend OTP.";
}else{

    $stmt = $conn->prepare("SELECT otp_code, otp_expiry FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows == 0){
        $status = "error";
        $message = "User not found.";
    }else{

        $row = $result->fetch_assoc();

        if(strtotime($row['otp_expiry']) < time()){
            $status = "error";
            $message = "OTP expired. Please request again.";
        }else{

            if($user_otp == $row['otp_code']){

                $clear = $conn->prepare("UPDATE users SET otp_code=NULL, otp_expiry=NULL WHERE email=?");
                $clear->bind_param("s", $email);
                $clear->execute();

                $_SESSION['otp_verified'] = true;
                $_SESSION['reset_email'] = $email;

                $status = "success";
                $message = "OTP verified successfully!";
                $showReset = true;

            }else{
                $status = "error";
                $message = "Invalid OTP!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verify OTP</title>

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
        }

        .header{
            background:#2563eb;
            color:white;
            padding:15px;
            text-align:center;
            font-weight:bold;
            font-size:18px;
            border-radius:15px 15px 0 0;
        }

        .body{
            padding:25px;
            text-align:center;
        }

        .otp-input{
            border-radius:10px;
            padding:10px;
        }

        .btn-primary{
            width:100%;
            padding:10px;
            font-weight:600;
        }

        .success{
            color:green;
            font-weight:600;
        }

        .error{
            color:red;
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

        <!-- FORM -->
        <form method="POST">

            <label class="mb-2">Enter OTP Code</label>

            <input type="text"
                   name="otp"
                   class="form-control otp-input mb-3"
                   placeholder="6-digit OTP"
                   required>

            <button type="submit" class="btn btn-primary">
                Verify OTP
            </button>

        </form>

        <!-- RESULT -->
        <?php if($message){ ?>

            <div class="mt-3 <?= $status; ?>">
                <?= $message; ?>
            </div>

        <?php } ?>

        <!-- RESET LINK -->
        <?php if($showReset){ ?>

            <a href="reset-password.php" class="btn btn-success mt-3 w-100">
                Go to Reset Password
            </a>

        <?php } ?>

    </div>

</div>

</body>
</html>