<?php
session_start();
$full_name = $_SESSION['full_name'] ?? 'User';
$role = $_SESSION['role'] ?? '';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<?php
include "db.php";

$user_id = $_SESSION['user_id'];

$getUser = mysqli_query($conn,"
SELECT *
FROM users
WHERE id='$user_id'
");

$user = mysqli_fetch_assoc($getUser);

$profile_photo = !empty($user['profile_photo'])
    ? $user['profile_photo']
    : 'default.png';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

/* ===== DASHBOARD DATA ===== */

// Personnel
$activePersonnel = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) as total FROM personnel WHERE employment_status='active'")
)['total'] ?? 0;

// Contracts
$terminatedContracts = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) as total FROM contracts WHERE end_date < CURDATE()")
)['total'] ?? 0;

$dueRenewal = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) as total FROM contracts 
    WHERE end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)")
)['total'] ?? 0;

// Provinces
$provincesCovered = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(DISTINCT province) as total FROM personnel")
)['total'] ?? 0;

// Gender
$male = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) as total FROM personnel WHERE sex='Male'")
)['total'] ?? 0;

$female = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) as total FROM personnel WHERE sex='Female'")
)['total'] ?? 0;

// Office chart
$officeLabels = [];
$officeData = [];

$officeResult = mysqli_query($conn, "
    SELECT office_assignment, COUNT(*) as total 
    FROM personnel 
    GROUP BY office_assignment
");

while ($row = mysqli_fetch_assoc($officeResult)) {
    $officeLabels[] = $row['office_assignment'];
    $officeData[] = $row['total'];
}

$updates = mysqli_query($conn, "
    SELECT personnel_name, activity, created_at
    FROM activity_logs
    ORDER BY created_at DESC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Dashboard | JOPMIS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

<link rel="stylesheet" href="assets/css/theme.css">

<style>

:root{
    --sidebar:#0f172a;
    --sidebar2:#1e293b;
    --card:#ffffff;
    --primary:#2563eb;
    --bg:#f1f5f9;
}

body{
    background:var(--bg);
    font-family:'Segoe UI',sans-serif;
    margin:0;
    padding:0;
}

body.dark-mode{
    background:#0f172a;
    color:white;
}

body.dark-mode .card,
body.dark-mode .chart-box,
body.dark-mode .topbar{
    background:#1e293b !important;
    color:white !important;
}

body.dark-mode .table{
    color:white;
}

body.dark-mode .sidebar{
    background:#020617;
}

body.dark-mode .text-muted{
    color:#cbd5e1 !important;
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
    top:0;
    left:0;
    z-index:1000;
    overflow-y:auto;
}

.logo{
    padding:25px;
    text-align:center;
    border-bottom:1px solid rgba(255,255,255,.1);
}

.logo h4{
    font-weight:bold;
    margin-bottom:8px;
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
    width:calc(100% - 270px);
    min-height:100vh;
}

/* TOPBAR */

.topbar{
    background:white;
    padding:15px 25px;
    box-shadow:0 2px 10px rgba(0,0,0,.05);
}

/* PAGE CONTENT */

.page-content{
    padding:20px;
}

/* WELCOME CARD */

.card.mb-4{
    margin-bottom:20px !important;
}

.card.mb-4 .card-body{
    padding:20px;
}

.card.mb-4 h3{
    font-size:28px;
    margin-bottom:10px;
}

.card.mb-4 p{
    font-size:14px;
}

/* STATISTICS */

.stat-card{
    border:none;
    border-radius:15px;
    box-shadow:0 3px 10px rgba(0,0,0,.08);
    height:100%;
}

.stat-card .card-body{
    padding:16px;
}

.stat-card p{
    font-size:14px;
    margin-bottom:5px;
}

.counter{
    font-size:22px;
    font-weight:bold;
}

.stat-icon{
    width:50px;
    height:50px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    color:white;
    font-size:20px;
}

.bg-blue{
    background:#2563eb;
}

.bg-green{
    background:#16a34a;
}

.bg-orange{
    background:#ea580c;
}

.bg-red{
    background:#dc2626;
}

/* CHARTS */

.chart-box{
    background:white;
    border-radius:15px;
    padding:18px;
    height:330px;
    box-shadow:0 3px 10px rgba(0,0,0,.08);
}

.chart-box h5{
    margin-bottom:15px;
}

.chart-box canvas{
    width:100% !important;
    max-height:230px !important;
}

/* RECENT UPDATES */

.table-card{
    border:none;
    border-radius:15px;
    box-shadow:0 3px 10px rgba(0,0,0,.08);
}

.table-card .card-body{
    padding:15px;
}

.table{
    font-size:14px;
}

.table th,
.table td{
    padding:10px;
}

/* MOBILE */

@media(max-width:991px){

    .sidebar{
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

    .page-content{
        padding:15px;
    }

    .chart-box{
        height:auto;
    }
}

/* OVERLAY */

.sidebar-overlay{
    position:fixed;
    inset:0;
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
    <a href="logout.php" class="dropdown-item">
      <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</li>
</ul>

</div>

 <!-- MAIN -->
 <div class="main">

<!-- TOPBAR -->
    
<div class="topbar d-flex justify-content-between align-items-center">

    <div class="d-flex align-items-center">

        <button
            id="sidebarToggle"
            class="btn btn-primary me-3 d-lg-none">

            <i class="fas fa-bars"></i>

        </button>

        <h4 class="mb-0">Dashboard</h4>

    </div>

    <div class="d-flex align-items-center">

    <button
        id="themeToggle"
        class="btn btn-link me-3">

        <i id="themeIcon" class="fas fa-moon"></i>

    </button>

    <div class="dropdown">

<button
class="btn dropdown-toggle d-flex align-items-center"
data-bs-toggle="dropdown">

<img
src="uploads/profile/<?= $profile_photo; ?>"
width="45"
height="45"
class="rounded-circle me-2"
style="object-fit:cover;">

<div class="text-start">

<div style="font-weight:600;">
<?= htmlspecialchars($full_name); ?>
</div>

<small class="text-muted">
<?= htmlspecialchars($role); ?>
</small>

</div>

</button>

<ul class="dropdown-menu dropdown-menu-end">

<li>
<a class="dropdown-item"
href="my_profile.php">
<i class="fas fa-user me-2"></i>
My Profile
</a>
</li>

<li>
<a class="dropdown-item"
href="my_profile.php">
<i class="fas fa-pen me-2"></i>
Edit Profile
</a>
</li>

<li>
<a class="dropdown-item"
href="change_password.php">
<i class="fas fa-key me-2"></i>
Change Password
</a>
</li>

<li><hr class="dropdown-divider"></li>

<li>
<a class="dropdown-item text-danger"
href="#"
onclick="confirmLogout()">
<i class="fas fa-sign-out-alt me-2"></i>
Logout
</a>
</li>

</ul>

</div>

</div>

</div>
<div class="page-content">
</div>

<!-- PAGE CONTENT -->
<div class="card mb-4 shadow-sm border-0">
    <div class="card-body">

        <h3 class="fw-bold">
            Welcome,
            <?= htmlspecialchars($_SESSION['full_name']); ?>!
        </h3>

        <p class="text-muted mb-0">
            Role:
            <?= htmlspecialchars($_SESSION['role']); ?>
        </p>

    </div>
</div>

    <!-- STATISTICS -->
    <div class="row g-3">

        <div class="col-lg-3 col-md-6">
            <div class="card stat-card">
                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>
                        <p class="mb-1">Active Personnel</p>
                        <h2 class="counter"><?php echo $activePersonnel; ?></h2>
                    </div>

                    <div class="stat-icon bg-blue">
                        <i class="fas fa-users"></i>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card stat-card">
                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>
                        <p class="mb-1">Terminated Contracts</p>
                        <h2 class="counter"><?php echo $terminatedContracts; ?></h2>
                    </div>

                    <div class="stat-icon bg-red">
                        <i class="fas fa-file-circle-xmark"></i>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card stat-card">
                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>
                        <p class="mb-1">Due Renewal</p>
                        <h2 class="counter"><?php echo $dueRenewal; ?></h2>
                    </div>

                    <div class="stat-icon bg-orange">
                        <i class="fas fa-calendar"></i>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card stat-card">
                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>
                        <p class="mb-1">Provinces Covered</p>
                        <h2 class="counter"><?php echo $provincesCovered; ?></h2>
                    </div>

                    <div class="stat-icon bg-green">
                        <i class="fas fa-map-location-dot"></i>
                    </div>

                </div>
            </div>
        </div>

    </div>

    <!-- CHARTS -->
    <div class="row mt-3">

        <div class="col-lg-6 mb-4">

            <div class="chart-box">

                <h5 class="mb-3">
                    Gender Distribution
                </h5>

                <canvas id="genderChart"></canvas>

            </div>

        </div>

        <div class="col-lg-6 mb-4">

            <div class="chart-box">

                <h5 class="mb-3">
                    Personnel Per Office
                </h5>

                <canvas id="officeChart"></canvas>

            </div>

        </div>

    </div>

    <!-- RECENT UPDATES -->
    <div class="row">

        <div class="col-12">

            <div class="card table-card">

                <div class="card-header">
                    <h5 class="mb-0">
                        Recent Updates
                    </h5>
                </div>

                <div class="card-body">

                    <div class="table-responsive">

                        <table class="table table-bordered">

                            <thead>

                                <tr>
                                    <th>Date</th>
                                    <th>Personnel</th>
                                    <th>Activity</th>
                                </tr>

                            </thead>

                            <tbody>
<?php if ($updates && mysqli_num_rows($updates) > 0): ?>
    <?php while ($row = mysqli_fetch_assoc($updates)): ?>
        <tr>
            <td><?php echo date('Y-m-d H:i', strtotime($row['created_at'])); ?></td>
            <td><?php echo $row['personnel_name']; ?></td>
            <td><?php echo $row['activity']; ?></td>
        </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr>
        <td colspan="3" class="text-center">No recent activity</td>
    </tr>
<?php endif; ?>
</tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const male = <?php echo $male; ?>;
const female = <?php echo $female; ?>;

const officeLabels = <?php echo json_encode($officeLabels); ?>;
const officeData = <?php echo json_encode($officeData); ?>;

// Gender Chart
new Chart(document.getElementById("genderChart"), {
    type: "doughnut",
    data: {
        labels: ["Male", "Female"],
        datasets: [{
            data: [male, female]
        }]
    }
});

// Office Chart
new Chart(document.getElementById("officeChart"), {
    type: "bar",
    data: {
        labels: officeLabels,
        datasets: [{
            label: "Personnel",
            data: officeData
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});
</script>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/theme.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if(isset($_SESSION['success'])): ?>
<script>
Swal.fire({
    icon: 'success',
    title: 'Welcome!',
    text: <?= json_encode($_SESSION['success']); ?>,
    timer: 1000,
    showConfirmButton: false,
    timerProgressBar: true
});
</script>
<?php unset($_SESSION['success']); ?>
<?php endif; ?>

<script>
function confirmLogout(){
    Swal.fire({
    title: 'Logout?',
    text: 'Are you sure you want to logout?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Yes, Logout',
    cancelButtonText: 'Cancel'
}).then((result) => {
    if (result.isConfirmed) {
        window.location = "logout.php";
    }
});

}
</script>

</body>
</html>