<?php
session_start();
include "config.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$message = "";
$status = "";

if(isset($_POST['send_otp'])){

    $email = trim($_POST['email']);

    $check = $conn->prepare("SELECT id FROM users WHERE email=?");
    $check->bind_param("s",$email);
    $check->execute();
    $result = $check->get_result();

    if($result->num_rows > 0){

        $otp = rand(100000,999999);
        $expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));

        $update = $conn->prepare("
            UPDATE users
            SET otp_code=?, otp_expiry=?
            WHERE email=?
        ");
        $update->bind_param("sss",$otp,$expiry,$email);
        $update->execute();

        $mail = new PHPMailer(true);

        try{

            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;

            $mail->Username   = 'comelecadmin@gmail.com';
            $mail->Password   = 'knlc hykq cgnn xfld';

            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('comelecadmin@gmail.com','JOPMIS');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset OTP';

            $mail->Body = "
            <h2>JOPMIS Password Recovery</h2>
            <p>Your OTP Code is:</p>
            <h1>$otp</h1>
            <p>This code expires in 5 minutes.</p>
            ";

            $mail->send();

            $_SESSION['reset_email'] = $email;

            header("Location: verify-otp.php");
            exit();

        }catch(Exception $e){
            $message = "Email sending failed!";
            $status = "error";
        }

    }else{
        $message = "Email not found!";
        $status = "error";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>

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
            padding:18px;
            text-align:center;
            font-size:18px;
            font-weight:bold;
        }

        .body{
            padding:25px;
        }

        .form-control{
            border-radius:10px;
            padding:10px;
        }

        .btn-primary{
            width:100%;
            padding:10px;
            font-weight:600;
            border-radius:10px;
        }

        .success{
            color:green;
            font-weight:600;
            text-align:center;
        }

        .error{
            color:red;
            font-weight:600;
            text-align:center;
        }

        .icon{
            font-size:50px;
            text-align:center;
            margin-bottom:10px;
            color:#2563eb;
        }

        .subtitle{
            text-align:center;
            font-size:14px;
            color:#64748b;
            margin-bottom:15px;
        }
    </style>
</head>
<body>

<div class="card">

    <div class="header">
        Forgot Password
    </div>

    <div class="body">

        <div class="icon">🔐</div>
        <div class="subtitle">
            Enter your email to receive OTP verification code
        </div>

        <!-- MESSAGE -->
        <?php if($message){ ?>
            <div class="<?= $status; ?> mb-3">
                <?= $message; ?>
            </div>
        <?php } ?>

        <!-- FORM -->
        <form method="POST">

            <label class="mb-2">Email Address</label>

            <input type="email"
                   name="email"
                   class="form-control mb-3"
                   placeholder="Enter your registered email"
                   required>

            <button type="submit"
                    name="send_otp"
                    class="btn btn-primary">
                Send OTP
            </button>

        </form>

    </div>

</div>

</body>
</html>