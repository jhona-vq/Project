<?php
include "auth.php";
include "config.php";

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if ($page < 1) {
    $page = 1;
}

$start = ($page - 1) * $limit;

/* TOTAL CONTRACTS */
$total_contracts = $conn->query("
SELECT COUNT(*) as total FROM contracts
")->fetch_assoc()['total'];

$total_pages = ceil($total_contracts / $limit);

?>
<?php
$totalPersonnel = $conn->query("
SELECT COUNT(*) AS total
FROM personnel
")->fetch_assoc()['total'];

$activePersonnel = $conn->query("
SELECT COUNT(*) AS total
FROM contracts
WHERE status='Active'
AND end_date >= CURDATE()
")->fetch_assoc()['total'];

$terminatedContracts = $conn->query("
SELECT COUNT(*) AS total
FROM contracts
WHERE end_date < CURDATE()
")->fetch_assoc()['total'];

$dueRenewal = $conn->query("
SELECT COUNT(*) AS total
FROM contracts
WHERE end_date BETWEEN CURDATE()
AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
")->fetch_assoc()['total'];

?>
<?php

// Province Data
$provinceLabels = [];
$provinceCounts = [];

$provinceQuery = $conn->query("
SELECT province, COUNT(*) total
FROM personnel
GROUP BY province
");

while($row = $provinceQuery->fetch_assoc()){
    $provinceLabels[] = $row['province'];
    $provinceCounts[] = $row['total'];
}

// Gender Data
$male = $conn->query("
SELECT COUNT(*) total
FROM personnel
WHERE sex='Male'
")->fetch_assoc()['total'];

$female = $conn->query("
SELECT COUNT(*) total
FROM personnel
WHERE sex='Female'
")->fetch_assoc()['total'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Reports | JOPMIS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

<link rel="stylesheet" href="assets/css/theme.css">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body{background:#f1f5f9;font-family:'Segoe UI',sans-serif}
.wrapper{display:flex}
.sidebar{width:270px;min-height:100vh;position:fixed;color:#fff;background:linear-gradient(180deg,#020617,#0f172a,#1e3a8a)}
.logo{text-align:center;padding:25px;border-bottom:1px solid rgba(255,255,255,.1)}
.sidebar ul{list-style:none;padding:0;margin:0}
.sidebar a{display:block;color:#fff;text-decoration:none;padding:15px 25px}
.sidebar a:hover,.active-menu{background:rgba(255,255,255,.1)}
.main{margin-left:270px;width:100%}
.topbar{background:#fff;padding:15px 25px;box-shadow:0 2px 10px rgba(0,0,0,.05)}
.page-content{padding:25px}
.card{border:none;border-radius:15px;box-shadow:0 5px 15px rgba(0,0,0,.08)}
.chart-box{background:#fff;padding:20px;border-radius:15px}
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

.card-header{
    padding:20px;
}

#searchContract{
    min-width:250px;
}

#entries{
    min-width:150px;
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
<li><a href="reports.php" class="active-menu"><i class="fas fa-chart-bar"></i> Reports</a></li>
<li><a href="users.php"><i class="fas fa-user-cog"></i> User Management</a></li>
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

        <h4 class="mb-0">Reports Module</h4>

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

<div class="col-md-3">
<div class="card">
<div class="card-body">
<h6>Total Personnel</h6>
<h2><?= $totalPersonnel; ?></h2>
</div>
</div>
</div>

<div class="col-md-3">
<div class="card">
<div class="card-body">
<h6>Active Personnel</h6>
<h2><?= $activePersonnel; ?></h2>
</div>
</div>
</div>

<div class="col-md-3">
<div class="card">
<div class="card-body">
<h6>Terminated Contracts</h6>
<h2><?= $terminatedContracts; ?></h2>
</div>
</div>
</div>

<div class="col-md-3">
<div class="card">
<div class="card-body">
<h6>Due Renewal</h6>
<h2><?= $dueRenewal; ?></h2>
</div>
</div>
</div>

</div>

<div class="card-body">

<div class="card mt-4 p-3">

<h5 class="mb-3">Personnel Reports</h5>

<div class="d-flex flex-wrap gap-2">

<a href="master_list.php" class="btn btn-primary">
<i class="fas fa-users"></i> Master List
</a>

<a href="active_personnel.php" class="btn btn-success">
<i class="fas fa-user-check"></i> Active
</a>

<a href="terminated_personnel.php" class="btn btn-danger">
<i class="fas fa-user-times"></i> Terminated
</a>

<a href="due_renewal.php" class="btn btn-warning">
<i class="fas fa-sync"></i> Due Renewal
</a>

</div>

</div>

<!-- CHARTS SECTION START -->
<div class="row g-4 mt-3">

    <!-- PROVINCE CHART -->
    <div class="col-lg-6">
        <div class="card p-3 h-100">
            <h5 class="mb-3">Personnel by Province</h5>

            <div style="height:320px;">
                <canvas id="provinceChart"></canvas>
            </div>

        </div>
    </div>

    <!-- GENDER CHART -->
    <div class="col-lg-6">
        <div class="card p-3 h-100">
            <h5 class="mb-3">Personnel by Gender</h5>

            <div style="height:320px;">
                <canvas id="genderChart"></canvas>
            </div>

        </div>
    </div>

</div>


<!-- EMPLOYMENT STATUS (FULL WIDTH) -->
<div class="row g-4 mt-1">

    <div class="col-12">
        <div class="card p-3">

            <h5 class="mb-3">Employment Status Overview</h5>

            <div style="height:350px;">
                <canvas id="statusChart"></canvas>
            </div>

        </div>
    </div>

</div>
<!-- CHARTS SECTION END -->

<div class="card mt-4 p-3">
<h5 class="mb-3">Export Reports</h5>

<div class="d-flex gap-2 flex-wrap">

<a href="export_pdf.php" class="btn btn-danger">
<i class="fas fa-file-pdf"></i> Export PDF
</a>

<a href="export_excel.php" class="btn btn-success">
<i class="fas fa-file-excel"></i> Export Excel
</a>

<a href="export_csv.php" class="btn btn-primary">
<i class="fas fa-file-csv"></i> Export CSV
</a>

</div>
</div>
<div class="card-header d-flex justify-content-between align-items-center mt-3">

    <h5>Contract Reports</h5>

    <div class="d-flex gap-2 mt-3 ms-2">
        <input
            type="text"
            id="searchContract"
            class="form-control"
            placeholder="Search contracts..."
            style="width:250px;">

        <select
            id="entries"
            class="form-select"
            style="width:150px;">

            <option value="10" <?= $limit==10?'selected':''; ?>>10 entries</option>
            <option value="25" <?= $limit==25?'selected':''; ?>>25 entries</option>
            <option value="50" <?= $limit==50?'selected':''; ?>>50 entries</option>
            <option value="100" <?= $limit==100?'selected':''; ?>>100 entries</option>

        </select>
    </div>

</div>

<div class="card-body table-responsive mt-4">
<table class="table table-bordered">
<thead class="table-dark">
<tr>
<th>Employee ID</th>
<th>Personnel</th>
<th>Contract End</th>
<th>Status</th>
</tr>
</thead>
<tbody>

<?php

$result = $conn->query("
SELECT *
FROM contracts
ORDER BY end_date ASC
LIMIT $start, $limit
");

while($row = $result->fetch_assoc()){

?>

<tr>

<td><?= $row['employee_id']; ?></td>

<td><?= $row['employee_name']; ?></td>

<td><?= $row['end_date']; ?></td>

<td>

<?php

if($row['end_date'] < date('Y-m-d')){
    echo '<span class="badge bg-danger">Terminated</span>';
}
elseif($row['end_date'] <= date('Y-m-d',strtotime('+30 days'))){
    echo '<span class="badge bg-warning">Due Renewal</span>';
}
else{
    echo '<span class="badge bg-success">'.$row['status'].'</span>';
}

?>

</td>

</tr>

<?php } ?>

</tbody>
</table>

<?php
$from = $start + 1;
$to = min($start + $limit, $total_contracts);
?>

<p class="text-muted mt-2">
Showing <?= $from; ?> to <?= $to; ?>
of <?= $total_contracts; ?> entries
</p>

<nav class="mt-3">
<ul class="pagination justify-content-center">

<?php if($page > 1){ ?>
<li class="page-item">
<a class="page-link" href="?page=<?= $page-1 ?>&limit=<?= $limit ?>">Previous</a>
</li>
<?php } ?>

<?php for($i = 1; $i <= $total_pages; $i++){ ?>
<li class="page-item <?= ($i==$page)?'active':''; ?>">
<a class="page-link" href="?page=<?= $i ?>&limit=<?= $limit ?>">
<?= $i ?>
</a>
</li>
<?php } ?>

<?php if($page < $total_pages){ ?>
<li class="page-item">
<a class="page-link" href="?page=<?= $page+1 ?>&limit=<?= $limit ?>">Next</a>
</li>
<?php } ?>

</ul>
</nav>

</div>
</div>

</div>
</div>
</div>

<script>
new Chart(document.getElementById('provinceChart'),{
    type:'bar',
    data:{
        labels: <?= json_encode($provinceLabels); ?>,
        datasets:[{
            label:'Personnel',
            data: <?= json_encode($provinceCounts); ?>
        }]
    }
});

new Chart(document.getElementById('genderChart'),{
    type:'pie',
    data:{
        labels:['Male','Female'],
        datasets:[{
            data:[<?= $male; ?>,<?= $female; ?>]
        }]
    }
});

new Chart(document.getElementById('statusChart'),{
    type:'doughnut',
    data:{
        labels:[
            'Active',
            'Terminated',
            'Due Renewal'
        ],
        datasets:[{
            data:[
                <?= $activePersonnel; ?>,
                <?= $terminatedContracts; ?>,
                <?= $dueRenewal; ?>
            ]
        }]
    }
});
</script>

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

<script>
document.getElementById('searchContract')
.addEventListener('keyup', function(){

    let value = this.value.toLowerCase();

    let rows = document.querySelectorAll(
        'table tbody tr'
    );

    rows.forEach(function(row){

        let text = row.innerText.toLowerCase();

        if(text.includes(value)){
            row.style.display = '';
        }else{
            row.style.display = 'none';
        }

    });

});
</script>

<script>
document.getElementById('entries')
.addEventListener('change', function(){

    window.location =
        'reports.php?limit=' + this.value;

});
</script>

</body>
</html>