<?php
include "config.php";

if (isset($_POST['register'])) {

    $fullname = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    // CHECK PASSWORD MATCH
    if ($password !== $confirm) {
        echo "<script>alert('Password does not match'); window.history.back();</script>";
        exit();
    }

    // CHECK EXISTING EMAIL
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo "<script>alert('Email already exists'); window.history.back();</script>";
        exit();
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);

    // INSERT SAFE
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $full_name, $email, $hashed);

    if ($stmt->execute()) {
        echo "<script>alert('Registered successfully!'); window.location='login.php';</script>";
    } else {
        echo "<script>alert('Registration failed');</script>";
    }
}
?>


<!DOCTYPE html>
<html lang="en" class="wf-lato-n9-active wf-lato-n4-active wf-lato-n7-active wf-lato-n3-active wf-fontawesome5solid-n4-active wf-fontawesome5brands-n4-active wf-simplelineicons-n4-active wf-fontawesome5regular-n4-active wf-flaticon-n4-active wf-active"><head><style type="text/css">.swal-icon--error{border-color:#f27474;-webkit-animation:animateErrorIcon .5s;animation:animateErrorIcon .5s}.swal-icon--error__x-mark{position:relative;display:block;-webkit-animation:animateXMark .5s;animation:animateXMark .5s}.swal-icon--error__line{position:absolute;height:5px;width:47px;background-color:#f27474;display:block;top:37px;border-radius:2px}.swal-icon--error__line--left{-webkit-transform:rotate(45deg);transform:rotate(45deg);left:17px}.swal-icon--error__line--right{-webkit-transform:rotate(-45deg);transform:rotate(-45deg);right:16px}@-webkit-keyframes animateErrorIcon{0%{-webkit-transform:rotateX(100deg);transform:rotateX(100deg);opacity:0}to{-webkit-transform:rotateX(0deg);transform:rotateX(0deg);opacity:1}}@keyframes animateErrorIcon{0%{-webkit-transform:rotateX(100deg);transform:rotateX(100deg);opacity:0}to{-webkit-transform:rotateX(0deg);transform:rotateX(0deg);opacity:1}}@-webkit-keyframes animateXMark{0%{-webkit-transform:scale(.4);transform:scale(.4);margin-top:26px;opacity:0}50%{-webkit-transform:scale(.4);transform:scale(.4);margin-top:26px;opacity:0}80%{-webkit-transform:scale(1.15);transform:scale(1.15);margin-top:-6px}to{-webkit-transform:scale(1);transform:scale(1);margin-top:0;opacity:1}}@keyframes animateXMark{0%{-webkit-transform:scale(.4);transform:scale(.4);margin-top:26px;opacity:0}50%{-webkit-transform:scale(.4);transform:scale(.4);margin-top:26px;opacity:0}80%{-webkit-transform:scale(1.15);transform:scale(1.15);margin-top:-6px}to{-webkit-transform:scale(1);transform:scale(1);margin-top:0;opacity:1}}.swal-icon--warning{border-color:#f8bb86;-webkit-animation:pulseWarning .75s infinite alternate;animation:pulseWarning .75s infinite alternate}.swal-icon--warning__body{width:5px;height:47px;top:10px;border-radius:2px;margin-left:-2px}.swal-icon--warning__body,.swal-icon--warning__dot{position:absolute;left:50%;background-color:#f8bb86}.swal-icon--warning__dot{width:7px;height:7px;border-radius:50%;margin-left:-4px;bottom:-11px}@-webkit-keyframes pulseWarning{0%{border-color:#f8d486}to{border-color:#f8bb86}}@keyframes pulseWarning{0%{border-color:#f8d486}to{border-color:#f8bb86}}.swal-icon--success{border-color:#a5dc86}.swal-icon--success:after,.swal-icon--success:before{content:"";border-radius:50%;position:absolute;width:60px;height:120px;background:#fff;-webkit-transform:rotate(45deg);transform:rotate(45deg)}.swal-icon--success:before{border-radius:120px 0 0 120px;top:-7px;left:-33px;-webkit-transform:rotate(-45deg);transform:rotate(-45deg);-webkit-transform-origin:60px 60px;transform-origin:60px 60px}.swal-icon--success:after{border-radius:0 120px 120px 0;top:-11px;left:30px;-webkit-transform:rotate(-45deg);transform:rotate(-45deg);-webkit-transform-origin:0 60px;transform-origin:0 60px;-webkit-animation:rotatePlaceholder 4.25s ease-in;animation:rotatePlaceholder 4.25s ease-in}.swal-icon--success__ring{width:80px;height:80px;border:4px solid hsla(98,55%,69%,.2);border-radius:50%;box-sizing:content-box;position:absolute;left:-4px;top:-4px;z-index:2}.swal-icon--success__hide-corners{width:5px;height:90px;background-color:#fff;padding:1px;position:absolute;left:28px;top:8px;z-index:1;-webkit-transform:rotate(-45deg);transform:rotate(-45deg)}.swal-icon--success__line{height:5px;background-color:#a5dc86;display:block;border-radius:2px;position:absolute;z-index:2}.swal-icon--success__line--tip{width:25px;left:14px;top:46px;-webkit-transform:rotate(45deg);transform:rotate(45deg);-webkit-animation:animateSuccessTip .75s;animation:animateSuccessTip .75s}.swal-icon--success__line--long{width:47px;right:8px;top:38px;-webkit-transform:rotate(-45deg);transform:rotate(-45deg);-webkit-animation:animateSuccessLong .75s;animation:animateSuccessLong .75s}@-webkit-keyframes rotatePlaceholder{0%{-webkit-transform:rotate(-45deg);transform:rotate(-45deg)}5%{-webkit-transform:rotate(-45deg);transform:rotate(-45deg)}12%{-webkit-transform:rotate(-405deg);transform:rotate(-405deg)}to{-webkit-transform:rotate(-405deg);transform:rotate(-405deg)}}@keyframes rotatePlaceholder{0%{-webkit-transform:rotate(-45deg);transform:rotate(-45deg)}5%{-webkit-transform:rotate(-45deg);transform:rotate(-45deg)}12%{-webkit-transform:rotate(-405deg);transform:rotate(-405deg)}to{-webkit-transform:rotate(-405deg);transform:rotate(-405deg)}}@-webkit-keyframes animateSuccessTip{0%{width:0;left:1px;top:19px}54%{width:0;left:1px;top:19px}70%{width:50px;left:-8px;top:37px}84%{width:17px;left:21px;top:48px}to{width:25px;left:14px;top:45px}}@keyframes animateSuccessTip{0%{width:0;left:1px;top:19px}54%{width:0;left:1px;top:19px}70%{width:50px;left:-8px;top:37px}84%{width:17px;left:21px;top:48px}to{width:25px;left:14px;top:45px}}@-webkit-keyframes animateSuccessLong{0%{width:0;right:46px;top:54px}65%{width:0;right:46px;top:54px}84%{width:55px;right:0;top:35px}to{width:47px;right:8px;top:38px}}@keyframes animateSuccessLong{0%{width:0;right:46px;top:54px}65%{width:0;right:46px;top:54px}84%{width:55px;right:0;top:35px}to{width:47px;right:8px;top:38px}}.swal-icon--info{border-color:#c9dae1}.swal-icon--info:before{width:5px;height:29px;bottom:17px;border-radius:2px;margin-left:-2px}.swal-icon--info:after,.swal-icon--info:before{content:"";position:absolute;left:50%;background-color:#c9dae1}.swal-icon--info:after{width:7px;height:7px;border-radius:50%;margin-left:-3px;top:19px}.swal-icon{width:80px;height:80px;border-width:4px;border-style:solid;border-radius:50%;padding:0;position:relative;box-sizing:content-box;margin:20px auto}.swal-icon:first-child{margin-top:32px}.swal-icon--custom{width:auto;height:auto;max-width:100%;border:none;border-radius:0}.swal-icon img{max-width:100%;max-height:100%}.swal-title{color:rgba(0,0,0,.65);font-weight:600;text-transform:none;position:relative;display:block;padding:13px 16px;font-size:27px;line-height:normal;text-align:center;margin-bottom:0}.swal-title:first-child{margin-top:26px}.swal-title:not(:first-child){padding-bottom:0}.swal-title:not(:last-child){margin-bottom:13px}.swal-text{font-size:16px;position:relative;float:none;line-height:normal;vertical-align:top;text-align:left;display:inline-block;margin:0;padding:0 10px;font-weight:400;color:rgba(0,0,0,.64);max-width:calc(100% - 20px);overflow-wrap:break-word;box-sizing:border-box}.swal-text:first-child{margin-top:45px}.swal-text:last-child{margin-bottom:45px}.swal-footer{text-align:right;padding-top:13px;margin-top:13px;padding:13px 16px;border-radius:inherit;border-top-left-radius:0;border-top-right-radius:0}.swal-button-container{margin:5px;display:inline-block;position:relative}.swal-button{background-color:#7cd1f9;color:#fff;border:none;box-shadow:none;border-radius:5px;font-weight:600;font-size:14px;padding:10px 24px;margin:0;cursor:pointer}.swal-button[not:disabled]:hover{background-color:#78cbf2}.swal-button:active{background-color:#70bce0}.swal-button:focus{outline:none;box-shadow:0 0 0 1px #fff,0 0 0 3px rgba(43,114,165,.29)}.swal-button[disabled]{opacity:.5;cursor:default}.swal-button::-moz-focus-inner{border:0}.swal-button--cancel{color:#555;background-color:#efefef}.swal-button--cancel[not:disabled]:hover{background-color:#e8e8e8}.swal-button--cancel:active{background-color:#d7d7d7}.swal-button--cancel:focus{box-shadow:0 0 0 1px #fff,0 0 0 3px rgba(116,136,150,.29)}.swal-button--danger{background-color:#e64942}.swal-button--danger[not:disabled]:hover{background-color:#df4740}.swal-button--danger:active{background-color:#cf423b}.swal-button--danger:focus{box-shadow:0 0 0 1px #fff,0 0 0 3px rgba(165,43,43,.29)}.swal-content{padding:0 20px;margin-top:20px;font-size:medium}.swal-content:last-child{margin-bottom:20px}.swal-content__input,.swal-content__textarea{-webkit-appearance:none;background-color:#fff;border:none;font-size:14px;display:block;box-sizing:border-box;width:100%;border:1px solid rgba(0,0,0,.14);padding:10px 13px;border-radius:2px;transition:border-color .2s}.swal-content__input:focus,.swal-content__textarea:focus{outline:none;border-color:#6db8ff}.swal-content__textarea{resize:vertical}.swal-button--loading{color:transparent}.swal-button--loading~.swal-button__loader{opacity:1}.swal-button__loader{position:absolute;height:auto;width:43px;z-index:2;left:50%;top:50%;-webkit-transform:translateX(-50%) translateY(-50%);transform:translateX(-50%) translateY(-50%);text-align:center;pointer-events:none;opacity:0}.swal-button__loader div{display:inline-block;float:none;vertical-align:baseline;width:9px;height:9px;padding:0;border:none;margin:2px;opacity:.4;border-radius:7px;background-color:hsla(0,0%,100%,.9);transition:background .2s;-webkit-animation:swal-loading-anim 1s infinite;animation:swal-loading-anim 1s infinite}.swal-button__loader div:nth-child(3n+2){-webkit-animation-delay:.15s;animation-delay:.15s}.swal-button__loader div:nth-child(3n+3){-webkit-animation-delay:.3s;animation-delay:.3s}@-webkit-keyframes swal-loading-anim{0%{opacity:.4}20%{opacity:.4}50%{opacity:1}to{opacity:.4}}@keyframes swal-loading-anim{0%{opacity:.4}20%{opacity:.4}50%{opacity:1}to{opacity:.4}}.swal-overlay{position:fixed;top:0;bottom:0;left:0;right:0;text-align:center;font-size:0;overflow-y:auto;background-color:rgba(0,0,0,.4);z-index:10000;pointer-events:none;opacity:0;transition:opacity .3s}.swal-overlay:before{content:" ";display:inline-block;vertical-align:middle;height:100%}.swal-overlay--show-modal{opacity:1;pointer-events:auto}.swal-overlay--show-modal .swal-modal{opacity:1;pointer-events:auto;box-sizing:border-box;-webkit-animation:showSweetAlert .3s;animation:showSweetAlert .3s;will-change:transform}.swal-modal{width:478px;opacity:0;pointer-events:none;background-color:#fff;text-align:center;border-radius:5px;position:static;margin:20px auto;display:inline-block;vertical-align:middle;-webkit-transform:scale(1);transform:scale(1);-webkit-transform-origin:50% 50%;transform-origin:50% 50%;z-index:10001;transition:opacity .2s,-webkit-transform .3s;transition:transform .3s,opacity .2s;transition:transform .3s,opacity .2s,-webkit-transform .3s}@media (max-width:500px){.swal-modal{width:calc(100% - 20px)}}@-webkit-keyframes showSweetAlert{0%{-webkit-transform:scale(1);transform:scale(1)}1%{-webkit-transform:scale(.5);transform:scale(.5)}45%{-webkit-transform:scale(1.05);transform:scale(1.05)}80%{-webkit-transform:scale(.95);transform:scale(.95)}to{-webkit-transform:scale(1);transform:scale(1)}}@keyframes showSweetAlert{0%{-webkit-transform:scale(1);transform:scale(1)}1%{-webkit-transform:scale(.5);transform:scale(.5)}45%{-webkit-transform:scale(1.05);transform:scale(1.05)}80%{-webkit-transform:scale(.95);transform:scale(.95)}to{-webkit-transform:scale(1);transform:scale(1)}}</style>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport">
<link rel="icon" href="assets/img/icon.ico" type="image/x-icon">

<!-- Fonts and icons -->
<script src="assets/js/plugin/webfont/webfont.min.js"></script>
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato:300,400,700,900" media="all"><link rel="stylesheet" href="assets/css/fonts.min.css" media="all"><script>
WebFont.load({
  google: {
    "families": ["Lato:300,400,700,900"]
  },
  custom: {
    "families": ["Flaticon", "Font Awesome 5 Solid", "Font Awesome 5 Regular", "Font Awesome 5 Brands",
      "simple-line-icons"
    ],
    urls: ['assets/css/fonts.min.css']
  },
  active: function() {
    sessionStorage.fonts = true;
  }
});
</script>

<!-- CSS Files -->
<link rel="stylesheet" href="assets/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/css/atlantis.css">
<link rel="stylesheet" href="assets/css/custom.css">

<style>
#loading-container {
  position: absolute;
  display: flex;
  height: 100%;
  width: 100%;
  background-color: white;
  z-index: 9999;
}

#loading-screen {
  position: absolute;
  left: 48%;
  top: 48%;
  z-index: 9999;
  text-align: center;
}

a {
  text-decoration: none;
}


/* body,
html,
.preloader {
  background-color: #18181b !important;
  color: #a1a1aa !important;
}

th,
td {
  color: #a1a1aa !important;
}

.card {
  background-color: #27272a;
}

.card-title {
  color: #d4d4d8;
}

.card-header {
  border-bottom: 1px solid #27272a !important;
}

.modal-content {
  background-color: #27272a;
}

input,
textarea,
select {
  background-color: #18181b !important;
  color: #d4d4d8 !important;
  border-color: #27272a !important;
}

.footer {
  background-color: #18181b;
  border-top: 1px solid #27272a;
}

    .form-check>.btn-group>label {
      border: 1px solid #3f3f46;
    }


.form-control:disabled, .form-control[readonly] {
  background: #171717!important;
  border: 1px solid #262626!important;
}

.sidebar-wrapper {
  background-color: #18181b !important;
  border-right: 1px solid #27272a;
} */

</style>
  <title>Register - Job Order Personnel Management Information System (JOPMIS)</title>


  <style>
    :root{
      --green:#0e0e1c;
      --green2:#49a8b3;
      --gold:#0f1621;
      --ink:#1f2937;
      --soft:#f8f9fa;
      --card:#ffffff;
    }

   /* Page background with animated gradient + soft blobs */
   body.login{
  min-height:100vh;
  margin:0;
  display:flex;
  align-items:center;
  justify-content:center;

  /* DARK BLUE PROFESSIONAL THEME */
  background:
    radial-gradient(900px 600px at 15% 20%, rgba(59,130,246,.18), transparent 60%),
    radial-gradient(700px 500px at 85% 10%, rgba(147,197,253,.12), transparent 55%),
    radial-gradient(800px 600px at 50% 100%, rgba(30,58,138,.25), transparent 60%),
    linear-gradient(135deg, #020617, #0f172a, #1e3a8a);

  position:relative;
  overflow:hidden;
  color:#fff;
}
    /* floating decorative circles */
    .blob{position:absolute;border-radius:50%;opacity:.15;filter:blur(1px);pointer-events:none;animation:float 12s ease-in-out infinite alternate;}
    .blob.b1{width:360px;height:360px;left:-120px;top:-80px;background:#fff;}
    .blob.b2{width:240px;height:240px;right:-80px;top:15%;background:#000;opacity:.1;animation-duration:16s;}
    .blob.b3{width:420px;height:420px;right:-160px;bottom:-160px;background:#fff;animation-duration:18s;}
    @keyframes float{from{transform:translateY(0)}to{transform:translateY(30px)}}

    /* Back home button */
    .back-home{
      position:fixed; left:16px; top:16px; z-index:5;
      display:inline-flex; align-items:center; gap:8px;
      background:rgba(255,255,255,.92); color:#111; border-radius:999px;
      padding:8px 14px; text-decoration:none; font-weight:700;
      box-shadow:0 10px 24px rgba(0,0,0,.18);
      transition:transform .2s ease, box-shadow .2s ease, opacity .2s ease;
    }
    .back-home:hover{transform:translateY(-2px); box-shadow:0 14px 30px rgba(0,0,0,.22);}
    .back-home i{color:var(--green);}

    /* Center card */
    .auth-wrap{
      width:100%;
      max-width:420px;
      padding:20px;
      z-index:2;
      animation: fadeUp .6s ease both;
    }
    @keyframes fadeUp{from{transform:translateY(10px);opacity:0}to{transform:translateY(0);opacity:1}}

    .auth-card{
  background: rgba(255,255,255,.10);
  backdrop-filter: blur(22px);
  -webkit-backdrop-filter: blur(22px);

  border: 1px solid rgba(255,255,255,.25);
  border-top: 1px solid rgba(255,255,255,.35);

  border-radius: 20px;

  box-shadow:
    0 25px 60px rgba(0,0,0,.45),
    inset 0 1px 0 rgba(255,255,255,.1);

  color:#fff;

  transition: transform .3s ease, box-shadow .3s ease;
}

.auth-header{
  padding:26px 24px 12px;
  text-align:center;

  background: rgba(255,255,255,.08);
  border-bottom: 1px solid rgba(255,255,255,.15);
}

.auth-header h3{
  color:#ffffff;
  font-weight:900;
  letter-spacing:.5px;
}

.auth-header p{
  color: rgba(255,255,255,.75);
}

    .auth-body{ padding:22px 22px 10px; }

    /* Floating label look aligned with your classes */
    .form-group{ position:relative;  z-index: 2; }
    .form-control{
  background: rgba(255,255,255,.18) !important;

  color: #fff !important;
}
label.placeholder{
  position: absolute;
  top: -10px;
  left: 12px;
  background: #0f172a; /* dark blue background para litaw */
  padding: 0 6px;
  color: #ffffff !important;
  font-weight: 700;
  font-size: 13px;
  border-radius: 6px;
}
.auth-card label.placeholder,
.auth-card label{
  color: #ffffff !important;
  text-shadow: 0 2px 6px rgba(0,0,0,.6);
}
.form-control::placeholder{
  color: rgba(255,255,255,.6);
}

.form-control:focus{
  background: rgba(255,255,255,.18);
  border-color: #60a5fa;
  box-shadow: 0 0 0 .2rem rgba(59,130,246,.25);
  color:#fff;
}

    /* show/hide password icon */
    .field-icon{
      position:absolute; right:12px; top:50%; transform:translateY(-50%);
      cursor:pointer; color:#6b7280;
    }

    /* Buttons */
    .btn-primary{
  background: linear-gradient(135deg, #1e3a8a, #2563eb) !important;
  border: none !important;
  color:#fff !important;
  font-weight:800;
  box-shadow: 0 8px 20px rgba(37,99,235,.35);
}

.btn-primary:hover{
  background: linear-gradient(135deg, #3b82f6, #1d4ed8) !important;
  transform: translateY(-2px);
}

.btn-success{
  background: linear-gradient(135deg, #0f172a, #1e3a8a) !important;
  border: 1px solid rgba(255,255,255,.25) !important;
  color:#fff !important;
  font-weight:800;
}

.btn-success:hover{
  background: linear-gradient(135deg, #1e40af, #0f172a) !important;
  transform: translateY(-2px);
}

    .auth-footer{
      padding:16px 22px 22px; text-align:center; color:#6b7280; font-size:.9rem;
    }


    /* Mobile tweaks */
    @media (max-width: 480px){
      .auth-wrap{padding:14px;}
      .back-home{left:10px; top:10px; padding:7px 12px;}
    }
  </style>
<style type="text/css">/* Chart.js */
@-webkit-keyframes chartjs-render-animation{from{opacity:0.99}to{opacity:1}}@keyframes chartjs-render-animation{from{opacity:0.99}to{opacity:1}}.chartjs-render-monitor{-webkit-animation:chartjs-render-animation 0.001s;animation:chartjs-render-animation 0.001s;}</style><style type="text/css">.jqstooltip { position: absolute;left: 0px;top: 0px;visibility: hidden;background: rgb(0, 0, 0) transparent;background-color: rgba(0,0,0,0.6);filter:progid:DXImageTransform.Microsoft.gradient(startColorstr=#99000000, endColorstr=#99000000);-ms-filter: "progid:DXImageTransform.Microsoft.gradient(startColorstr=#99000000, endColorstr=#99000000)";color: white;font: 10px arial, san serif;text-align: left;white-space: nowrap;padding: 5px;border: 1px solid white;z-index: 10000;}.jqsfield { color: white;font: 10px arial, san serif;text-align: left;}</style></head>

<body class="login">
  <!-- animated background blobs -->
  <span class="blob b1"></span>
  <span class="blob b2"></span>
  <span class="blob b3"></span>

  <!-- Back to home -->
  <a href="index.html" class="back-home"><i class="fa fa-arrow-left"></i> Back Home</a>

  <div id="loading-container" class="preloader" style="display: none;">
    <div id="loading-screen">
        <div class="loader loader-lg"></div>
    </div>
</div>
  <div class="auth-wrap">
    <div class="auth-card animated fadeIn fast">
      <div class="auth-header">
        <h3>Sign In</h3>
        <p>Access your Account</p>
      </div>

      <div class="auth-body">
        
<form method="POST" action="model/register.php" autocomplete="off">

  <!-- FULL NAME -->
      <div class="form-group">
        <label class="placeholder">Full Name</label>
          <input name="fullname" type="text" class="form-control"  required>
    </div>

  <!-- EMAIL -->
      <div class="form-group">
        <label class="placeholder">Email</label>
          <input name="email" type="email" class="form-control" required>
      </div>

  <!-- PASSWORD -->
      <div class="form-group position-relative">
        <label class="placeholder">Password</label>
          <input id="reg_password" name="password" type="password" class="form-control" required>

          <span toggle="#reg_password" class="fa fa-eye field-icon toggle-password"></span>
      </div>

  <!-- CONFIRM (optional but recommended) -->
      <div class="form-group position-relative">
        <label class="placeholder">Confirm Password</label>
          <input id="confirm_password" name="confirm_password" type="password" class="form-control" required>

          <span toggle="#confirm_password" class="fa fa-eye field-icon toggle-password"></span>
      </div>

  <!-- CREATE BUTTON -->
      <div class="d-grid gap-2">
        <button type="submit" name="register" class="btn btn-primary">Create Account</button>
          <a href="login.php" class="btn btn-success">Back to Login</a>
      </div>

</form>
      </div>

    </div>
  </div>

  <!--   Core JS Files   -->
<script src="assets/js/core/jquery.3.2.1.min.js"></script>
<script src="assets/js/core/popper.min.js"></script>
<script src="assets/js/core/bootstrap.min.js"></script>

<!-- jQuery UI -->
<script src="assets/js/plugin/jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>
<script src="assets/js/plugin/jquery-ui-touch-punch/jquery.ui.touch-punch.min.js"></script>

<!-- jQuery Scrollbar -->
<script src="assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>


<!-- Chart JS -->
<script src="assets/js/plugin/chart.js/chart.min.js"></script>

<!-- jQuery Sparkline -->
<script src="assets/js/plugin/jquery.sparkline/jquery.sparkline.min.js"></script>

<!-- Chart Circle -->
<!-- <script src="assets/js/plugin/chart-circle/circles.min.js"></script> -->

<!-- Datatables -->
<script src="assets/js/plugin/datatables/datatables.min.js"></script>

<!-- Bootstrap Notify -->
<script src="assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js"></script>

<!-- jQuery Vector Maps -->
<script src="assets/js/plugin/jqvmap/jquery.vmap.min.js"></script>
<script src="assets/js/plugin/jqvmap/maps/jquery.vmap.world.js"></script>

<!-- Sweet Alert -->
<script src="assets/js/plugin/sweetalert/sweetalert.min.js"></script>

<!-- Atlantis JS -->
<script src="assets/js/atlantis.min.js"></script>

<script type="text/javascript" src="assets/webcamjs/webcam.min.js"></script>

<script src="assets/js/customFunction.js"></script>
<script src="assets/js/helpers.js"></script>

<script>
    var $window = $(window);
    $window.on("load", function() {
        // Preloader
        $(".preloader").fadeOut(500);
    });
</script>
  <script>
    // Toggle password visibility
    document.addEventListener('click', function(e){
      if(e.target.classList.contains('toggle-password')){
        const target = document.querySelector(e.target.getAttribute('toggle'));
        if(!target) return;
        const isPass = target.getAttribute('type') === 'password';
        target.setAttribute('type', isPass ? 'text' : 'password');
        e.target.classList.toggle('fa-eye');
        e.target.classList.toggle('fa-eye-slash');
      }
    });
  </script>

<script src="theme.js"></script>
</body></html>