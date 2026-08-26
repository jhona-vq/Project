<?php
include "auth.php";
include "config.php";
include "role_access.php";

allowRoles([
    'System Administrator',
    'HR Administrator',
]);

if(isset($_POST['save_user'])){

    $full_name = $_POST['full_name'];
    $email = $_POST['email'];

    $password = password_hash(
        $_POST['password'],
        PASSWORD_DEFAULT
    );

    $role = $_POST['role'];
    $status = $_POST['status'];

    $conn->query("
    INSERT INTO users(
        full_name,
        email,
        password,
        role,
        status
    )
    VALUES(
        '$full_name',
        '$email',
        '$password',
        '$role',
        '$status'
    )
    ");

    header("Location: users.php");
    exit();
}
?>

<?php

$totalUsers = $conn->query("
SELECT COUNT(*) total
FROM users
")->fetch_assoc()['total'];

$sysAdmin = $conn->query("
SELECT COUNT(*) total
FROM users
WHERE role='System Administrator'
")->fetch_assoc()['total'];

$hrAdmin = $conn->query("
SELECT COUNT(*) total
FROM users
WHERE role='HR Administrator'
")->fetch_assoc()['total'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>User Management | JOPMIS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

<link rel="stylesheet" href="assets/css/theme.css">

<style>
body{background:#f1f5f9;font-family:'Segoe UI',sans-serif}
/* DARK MODE */

.dark-mode{
    background:#0f172a !important;
    color:#fff;
}

.dark-mode .topbar{
    background:#1e293b !important;
    color:#fff;
}

.dark-mode .page-content{
    background:#0f172a;
}

.dark-mode .card{
    background:#1e293b !important;
    color:#fff;
}

.dark-mode .table{
    color:#fff;
}

.dark-mode .table-bordered{
    border-color:#334155;
}

.dark-mode .modal-content{
    background:#1e293b;
    color:#fff;
}

.dark-mode .form-control,
.dark-mode .form-select{
    background:#334155;
    color:#fff;
    border:1px solid #475569;
}

.dark-mode .card-header{
    background:#334155;
    color:#fff;
}
.wrapper{display:flex}
.sidebar{width:270px;min-height:100vh;position:fixed;background:linear-gradient(180deg,#020617,#0f172a,#1e3a8a);color:#fff}
.logo{text-align:center;padding:25px;border-bottom:1px solid rgba(255,255,255,.1)}
.sidebar ul{list-style:none;padding:0;margin:0}
.sidebar a{display:block;color:#fff;text-decoration:none;padding:15px 25px}
.sidebar a:hover,.active-menu{background:rgba(255,255,255,.1)}
.main{margin-left:270px;width:100%}
.topbar{background:#fff;padding:15px 25px;box-shadow:0 2px 10px rgba(0,0,0,.05)}
.page-content{padding:25px}
.card{border:none;border-radius:15px;box-shadow:0 5px 15px rgba(0,0,0,.08)}
.role-card{height:100%}
.badge-role{font-size:13px}
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

<div class="sidebar">
<div class="logo">
<h4>JOPMIS</h4>
<small>COMELEC - CAR</small>
</div>

<ul>
<li><a href="dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a></li>
<li><a href="personnel.php"><i class="fas fa-users"></i> Personnel</a></li>
<li><a href="contracts.php"><i class="fas fa-file-signature"></i> Contracts</a></li>
<li><a href="documents.php"><i class="fas fa-folder-open"></i> Documents</a></li>
<li><a href="performance.php"><i class="fas fa-star"></i> Performance</a></li>
<li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
<li><a href="users.php" class="active-menu"><i class="fas fa-user-cog"></i> User Management</a></li>
<li><a href="settings.php"><i class="fas fa-cogs"></i> Settings</a></li>
<li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
</ul>
</div>

<div class="main">

<div class="topbar d-flex justify-content-between align-items-center">

    <div class="d-flex align-items-center">

        <button
            id="sidebarToggle"
            class="btn btn-primary me-3 d-lg-none">

            <i class="fas fa-bars"></i>

        </button>

        <h4 class="mb-0">User Management Module</h4>

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

<div class="row g-4">

<div class="col-lg-4 col-md-6">
<div class="card text-center">
<div class="card-body">
<h2><?= $totalUsers; ?></h2>
<p>Total Users</p>
</div>
</div>
</div>

<div class="col-lg-4 col-md-6">
<div class="card text-center">
<div class="card-body">
<h2><?= $sysAdmin; ?></h2>
<p>System Admins</p>
</div>
</div>
</div>

<div class="col-lg-4 col-md-6">
<div class="card text-center">
<div class="card-body">
<h2><?= $hrAdmin; ?></h2>
<p>HR Admins</p>
</div>
</div>
</div>

</div>

<div class="row mt-4 justify-content-center g-4">

<div class="col-lg-5 col-md-6">
<div class="card role-card">
<div class="card-body">
<h5><span class="badge bg-danger">System Administrator</span></h5>
<hr>
<ul>
<li>Full Access</li>
<li>Manage Users</li>
<li>System Configuration</li>
</ul>
</div>
</div>
</div>

<div class="col-lg-5 col-md-6">
<div class="card role-card">
<div class="card-body">
<h5><span class="badge bg-primary">HR Administrator</span></h5>
<hr>
<ul>
<li>Add/Edit Personnel</li>
<li>Generate Reports</li>
<li>Manage Contracts</li>
</ul>
</div>
</div>
</div>

</div>

<div class="card mt-4">

<div class="card-header">
<h5>User Accounts</h5>
</div>

<div class="card-body">

<form method="GET">
<div class="row mb-3">

<div class="col-md-4">
<input
type="text"
name="search"
class="form-control"
placeholder="Search User"
value="<?= isset($_GET['search']) ? $_GET['search'] : ''; ?>">
</div>

<div class="col-md-2">
<button type="submit" class="btn btn-primary">
Search
</button>
</div>

</div>
</form>

</div>
</div>

<div class="table-responsive mt-3">

<table class="table table-bordered">

<thead class="table-dark">
<tr>
<th>User ID</th>
<th>Full Name</th>
<th>Email</th>
<th>Role</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>

<tbody>

<?php

$search = isset($_GET['search'])
? $_GET['search']
: '';

if($search != ''){

    $result = $conn->query("
    SELECT *
    FROM users
    WHERE full_name LIKE '%$search%'
    OR username LIKE '%$search%'
    OR role LIKE '%$search%'
    ORDER BY id DESC
    ");

}else{

    $result = $conn->query("
    SELECT *
    FROM users
    ORDER BY id DESC
    ");

}

while($row = $result->fetch_assoc()){

?>

<tr>

<td>USR-<?= str_pad($row['id'],3,'0',STR_PAD_LEFT); ?></td>

<td><?= $row['full_name']; ?></td>

<td><?= $row['email']; ?></td>

<td>

<?php

if($row['role']=="System Administrator"){
    echo '<span class="badge bg-danger">'.$row['role'].'</span>';
}
elseif($row['role']=="HR Administrator"){
    echo '<span class="badge bg-primary">'.$row['role'].'</span>';
}
else{
    echo '<span class="badge bg-secondary">'.$row['role'].'</span>';
}

?>

</td>

<td>

<?php

if($row['status']=="Active"){
    echo '<span class="badge bg-success">Active</span>';
}else{
    echo '<span class="badge bg-danger">Inactive</span>';
}

?>

</td>

<td>

<a href="edit_user.php?id=<?= $row['id']; ?>"
class="btn btn-warning btn-sm">
<i class="fas fa-edit"></i>
</a>

<a href="delete_user.php?id=<?= $row['id']; ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Delete User?')">
<i class="fas fa-trash"></i>
</a>

</td>

</tr>

<?php } 
?>

</tbody>

</table>

</div>

</div>

</div>

</div>

</div>

</div>

<div class="modal fade" id="addUserModal">

<div class="modal-dialog">

<div class="modal-content">

<div class="modal-header">
<h5>Add User</h5>
<button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<form method="POST">

<label>Full Name</label>
<input
type="text"
name="full_name"
class="form-control mb-3"
required>

<label>Email</label>
<input
type="email"
name="email"
class="form-control mb-3"
required>

<label>Password</label>
<input
type="password"
name="password"
class="form-control mb-3"
required>

<label>User Role</label>
<select
name="role"
class="form-control mb-3">
<option>System Administrator</option>
<option>HR Administrator</option>
</select>

<label>Status</label>
<select
name="status"
class="form-control mb-3">
<option>Active</option>
<option>Inactive</option>
</select>

<div class="modal-footer">
<button type="button"
class="btn btn-secondary"
data-bs-dismiss="modal">
Cancel
</button>

<button
type="submit"
name="save_user"
class="btn btn-primary">
Save User
</button>
</div>

</form>

</div>

</div>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

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

<script src="assets/js/theme.js"></script>

</body>
</html>