<?php
include "auth.php";
include "config.php";
include "role_access.php";

allowRoles([
    'System Administrator',
    'HR Administrator',
]);

$setting = $conn->query("
SELECT * FROM system_settings
WHERE id=1
")->fetch_assoc();

$currentTheme = $setting['theme'];

if(isset($_POST['save_settings'])){

    $system_name = $_POST['system_name'];
    $organization = $_POST['organization'];
    $system_version = $_POST['system_version'];
    $admin_email = $_POST['admin_email'];

    $two_factor =
    isset($_POST['two_factor']) ? 1 : 0;

    $strong_password =
    isset($_POST['strong_password']) ? 1 : 0;

    $auto_logout =
    isset($_POST['auto_logout']) ? 1 : 0;

    $alert90 =
    isset($_POST['alert90']) ? 1 : 0;

    $alert60 =
    isset($_POST['alert60']) ? 1 : 0;

    $alert30 =
    isset($_POST['alert30']) ? 1 : 0;

    $expired_alert =
    isset($_POST['expired_alert']) ? 1 : 0;


    $conn->query("
    UPDATE settings SET

    system_name='$system_name',
    organization='$organization',
    system_version='$system_version',
    admin_email='$admin_email',

    two_factor='$two_factor',
    strong_password='$strong_password',
    auto_logout='$auto_logout',

    alert90='$alert90',
    alert60='$alert60',
    alert30='$alert30',
    expired_alert='$expired_alert'


    WHERE id=1
    ");

    header("Location: settings.php");
    exit();
}
$settings = $conn->query("
SELECT *
FROM settings
WHERE id=1
")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Settings | JOPMIS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

<link rel="stylesheet" href="assets/css/theme.css">

<style>

<?php if($theme=='dark'){ ?>

:root{
    --bg:#0f172a;
    --card:#1e293b;
    --text:#ffffff;
}

<?php } else { ?>

:root{
    --bg:#f1f5f9;
    --card:#ffffff;
    --text:#000000;
}

<?php } ?>

body{
    background:var(--bg);
    color:var(--text);
}

.card{
    background:var(--card);
    color:var(--text);
}

.topbar{
    background:var(--card);
    color:var(--text);
}

body{
    background:var(--bg);
    font-family:'Segoe UI',sans-serif;
}

.wrapper{
    display:flex;
}

/* SIDEBAR */

.sidebar{
    width:270px;
    min-height:100vh;
    background:linear-gradient(180deg,#020617,#0f172a,#1e3a8a);
    color:white;
    position:fixed;
}

.logo{
    padding:25px;
    text-align:center;
    border-bottom:1px solid rgba(255,255,255,.1);
}

.sidebar ul{
    list-style:none;
    padding:0;
    margin:0;
}

.sidebar ul li a{
    display:block;
    color:white;
    text-decoration:none;
    padding:15px 25px;
    transition:.3s;
}

.sidebar ul li a:hover{
    background:rgba(255,255,255,.1);
    padding-left:35px;
}

.sidebar ul li a i{
    width:25px;
}

/* MAIN */

.main{
    margin-left:270px;
    width:100%;
}

.topbar{
    background:#fff;
    padding:15px 25px;
    box-shadow:0 2px 10px rgba(0,0,0,.05)
}

.page-content{
    padding:25px;
}

.card{
    border:none;
    border-radius:15px;
    box-shadow:0 5px 15px rgba(0,0,0,.08);
    margin-bottom:20px;
}

.section-title{
    font-weight:bold;
    margin-bottom:15px;
    color:#0f172a;
}

@media(max-width:991px){

.sidebar{
    width:270px;
    min-height:100vh;
    position:fixed;
    top:0;
    left:0;
    z-index:1000;
    transform:translateX(-100%);
    transition:.3s;
}

.sidebar.active{
    transform:translateX(0);
}

.main{
    margin-left:0;
    width:100%;
}

}
.sidebar-overlay{
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,.5);
    z-index:999;
    display:none;
}

.sidebar-overlay.show{
    display:block;
}
</style>

</head>
<body>

<div id="sidebarOverlay" class="sidebar-overlay"></div>

<div class="wrapper">

<!-- SIDEBAR -->

<div class="sidebar">

<div class="logo">
<h4>JOPMIS</h4>
<small>COMELEC - CAR</small>
</div>

<ul>

<li>
<a href="dashboard.php">
<i class="fas fa-chart-line"></i> Dashboard
</a>
</li>

<li>
<a href="personnel.php">
<i class="fas fa-users"></i> Personnel
</a>
</li>

<li>
<a href="contracts.php">
<i class="fas fa-file-signature"></i> Contracts
</a>
</li>

<li>
<a href="documents.php">
<i class="fas fa-folder-open"></i> Documents
</a>
</li>

<li>
<a href="performance.php">
<i class="fas fa-star"></i> Performance
</a>
</li>

<li>
<a href="reports.php">
<i class="fas fa-chart-bar"></i> Reports
</a>
</li>

<li>
<a href="users.php">
<i class="fas fa-user-cog"></i> User Management
</a>
</li>

<li>
<a href="settings.php">
<i class="fas fa-cogs"></i> Settings
</a>
</li>

<li>
<a href="logout.php">
<i class="fas fa-sign-out-alt"></i> Logout
</a>
</li>

</ul>

</div>

<!-- MAIN -->

<div class="main">

<div class="topbar d-flex justify-content-between align-items-center">

    <div class="d-flex align-items-center">

        <button
            id="sidebarToggle"
            class="btn btn-primary me-3 d-lg-none">

            <i class="fas fa-bars"></i>

        </button>

        <h4 class="mb-0">System Settings</h4>

    </div>

    <div class="d-flex align-items-center">

        <button
            id="themeToggle"
            class="btn btn-link me-3">

            <i id="themeIcon" class="fas fa-moon"></i>

        </button>

    </div>

</div>


<div class="page-content">

<form method="POST">

<!-- SYSTEM SETTINGS -->

<div class="card">

<div class="card-header bg-primary text-white">
System Information
</div>

<div class="card-body">

<div class="row">

<div class="col-md-6 mb-3">
<label>System Name</label>
<input type="text"
class="form-control"
name="system_name"
value="<?= $settings['system_name']; ?>">
</div>

<div class="col-md-6 mb-3">
<label>Organization</label>
<input type="text"
class="form-control"
name="organization"
value="<?= $settings['organization']; ?>">
</div>

<div class="col-md-6 mb-3">
<label>System Version</label>
<input type="text"
class="form-control"
name="system_version"
value="<?= $settings['system_version']; ?>"">
</div>

<div class="col-md-6 mb-3">
<label>Administrator Email</label>
<input type="email"
class="form-control"
name="admin_email"
value="<?= $settings['admin_email']; ?>">
</div>

</div>

</div>

</div>

<!-- SECURITY -->

<div class="card">

<div class="card-header bg-success text-white">
Security Settings
</div>

<div class="card-body">

<div class="form-check mb-3">
<input
class="form-check-input"
type="checkbox"
name="two_factor"
<?= $settings['two_factor'] ? 'checked' : ''; ?>>
<label class="form-check-label">
Enable Two-Factor Authentication
</label>
</div>

<div class="form-check mb-3">
<input class="form-check-input" name="strong_password"
type="checkbox"
<?= $settings['alert90'] ? 'checked' : ''; ?>>
<label class="form-check-label">
Require Strong Passwords
</label>
</div>

<div class="form-check mb-3">
<input class="form-check-input" name="auto_logout"
type="checkbox"
<?= $settings['alert90'] ? 'checked' : ''; ?>>
<label class="form-check-label">
Auto Logout After 15 Minutes
</label>
</div>

</div>

</div>

<!-- EMAIL NOTIFICATIONS -->

<div class="card">

<div class="card-header bg-warning">
Email Notifications
</div>

<div class="card-body">

<div class="form-check mb-3">
<input class="form-check-input" name="alert90"
type="checkbox"
<?= $settings['alert90'] ? 'checked' : ''; ?>>
<label class="form-check-label">
90 Days Contract Termination Alert
</label>
</div>

<div class="form-check mb-3">
<input class="form-check-input" name="alert60"
type="checkbox"
<?= $settings['alert90'] ? 'checked' : ''; ?>>
<label class="form-check-label">
60 Days Contract Termination Alert
</label>
</div>

<div class="form-check mb-3">
<input class="form-check-input" name="alert30"
type="checkbox"
<?= $settings['alert90'] ? 'checked' : ''; ?>>
<label class="form-check-label">
30 Days Contract Termination Alert
</label>
</div>

<div class="form-check mb-3">
<input class="form-check-input" name="expired_alert"
type="checkbox"
<?= $settings['alert90'] ? 'checked' : ''; ?>>
<label class="form-check-label">
Terminated Contract Notification
</label>
</div>

</div>

</div>

<!-- BACKUP SETTINGS -->

<div class="card">

<div class="card-header bg-info text-white">
Backup & Restore
</div>

<div class="card-body">

<div class="row">

<div class="col-md-6">

<a href="backup.php"
class="btn btn-success w-100">

<i class="fas fa-download"></i>
Create Backup

</a>

</div>

<div class="col-md-6">

<a href="restore.php"
class="btn btn-primary w-100">

<i class="fas fa-upload"></i>
Restore Backup

</a>

</div>

</div>

</div>

</div>


<!-- SAVE -->

<div class="card">

<div class="card-body text-end">

<button type="reset"
class="btn btn-secondary">

<i class="fas fa-rotate-left"></i>
Reset

</button>

<button
type="submit"
name="save_settings"
class="btn btn-primary">

<i class="fas fa-save"></i>
Save Settings

</button>

</div>

</div>

</form>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="assets/js/theme.js"></script>

<script>
const themeBtn = document.getElementById("themeToggle");
const themeIcon = document.getElementById("themeIcon");

// Load theme
if(localStorage.getItem("theme") === "dark"){
    document.body.classList.add("dark-mode");
    themeIcon.classList.remove("fa-moon");
    themeIcon.classList.add("fa-sun");
}

themeBtn.addEventListener("click", function(){

    document.body.classList.toggle("dark-mode");

    if(document.body.classList.contains("dark-mode")){
        localStorage.setItem("theme","dark");
        themeIcon.classList.remove("fa-moon");
        themeIcon.classList.add("fa-sun");
    }else{
        localStorage.setItem("theme","light");
        themeIcon.classList.remove("fa-sun");
        themeIcon.classList.add("fa-moon");
    }

});
</script>
<script>
const sidebar = document.querySelector(".sidebar");
const sidebarToggle = document.getElementById("sidebarToggle");
const overlay = document.getElementById("sidebarOverlay");

sidebarToggle.addEventListener("click", () => {
    sidebar.classList.toggle("active");
    overlay.classList.toggle("show");
});

overlay.addEventListener("click", () => {
    sidebar.classList.remove("active");
    overlay.classList.remove("show");
});
</script>

</body>
</html>