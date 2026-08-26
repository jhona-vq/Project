<?php
session_start();
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
            max-width:400px;
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
            font-weight:bold;
            font-size:18px;
        }

        .body{
            padding:25px;
            text-align:center;
        }

        .otp-icon{
            font-size:50px;
            margin-bottom:10px;
            color:#2563eb;
        }

        .form-control{
            border-radius:10px;
            padding:10px;
            text-align:center;
            font-size:18px;
            letter-spacing:5px;
        }

        .btn-primary{
            width:100%;
            padding:10px;
            font-weight:600;
            border-radius:10px;
        }

        .subtitle{
            font-size:13px;
            color:#64748b;
            margin-bottom:15px;
        }
    </style>
</head>
<body>

<div class="card">

    <div class="header">
        OTP Verification
    </div>

    <div class="body">

        <div class="otp-icon">🔑</div>

        <div class="subtitle">
            Enter the 6-digit OTP sent to your email
        </div>

        <form method="POST" action="check_otp.php">

            <input type="text"
                   name="otp"
                   class="form-control mb-3"
                   placeholder="------"
                   maxlength="6"
                   required>

            <button type="submit" class="btn btn-primary">
                Verify OTP
            </button>

        </form>

    </div>

</div>

</body>
</html>