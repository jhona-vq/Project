<?php
include "auth.php";
include "config.php";

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if($page < 1) $page = 1;

$start = ($page - 1) * $limit;

// total records
$total_result = $conn->query("SELECT COUNT(*) as total FROM performance");
$total_records = $total_result->fetch_assoc()['total'];

$total_pages = ceil($total_records / $limit);

if(isset($_POST['save_evaluation'])){

    $employee_id = mysqli_real_escape_string($conn,$_POST['employee_id']);
    $employee_name = mysqli_real_escape_string($conn,$_POST['employee_name']);
    $evaluation_period = mysqli_real_escape_string($conn,$_POST['evaluation_period']);
    $rating = mysqli_real_escape_string($conn,$_POST['rating']);
    $evaluator = mysqli_real_escape_string($conn,$_POST['evaluator']);
    $comments = mysqli_real_escape_string($conn,$_POST['comments']);

    $employee_id = $_POST['employee_id'];
    $employee_name = $_POST['employee_name'];
    $evaluation_period = $_POST['evaluation_period'];
    $rating = $_POST['rating'];
    $evaluator = $_POST['evaluator'];
    $comments = $_POST['comments'];

    if($rating >= 4.5){
        $status = "Outstanding";
    }
    elseif($rating >= 3){
        $status = "Average";
    }
    else{
        $status = "Poor";
    }

    $conn->query("
    INSERT INTO performance(
        employee_id,
        employee_name,
        evaluation_period,
        rating,
        evaluator,
        comments,
        status
    )
    VALUES(
        '$employee_id',
        '$employee_name',
        '$evaluation_period',
        '$rating',
        '$evaluator',
        '$comments',
        '$status'
    )
    ");
    $check = $conn->query("
        SELECT *
        FROM performance
        WHERE employee_id='$employee_id'
        AND evaluation_period='$evaluation_period'
    ");

    if($check->num_rows > 0){
        echo "
        <script>
        alert('Evaluation already exists for this period.');
        window.location='performance.php';
        </script>";
        exit();
    }

    header('Location: performance.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Performance Monitoring | JOPMIS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

<link rel="stylesheet" href="assets/css/theme.css">

<style>

body{
    background:#f1f5f9;
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
    background:white;
    padding:15px 25px;
    box-shadow:0 2px 10px rgba(0,0,0,.05);
}

.page-content{
    padding:25px;
}

/* CARDS */
.card{
    border:none;
    border-radius:15px;
    box-shadow:0 5px 15px rgba(0,0,0,.08);
}

/* TABLE */
.table-card{
    border:none;
    border-radius:15px;
    box-shadow:0 5px 15px rgba(0,0,0,.08);
}

.badge-good{ background:#16a34a; }
.badge-mid{ background:#f59e0b; }
.badge-low{ background:#dc2626; }

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
<li><a href="dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a></li>
<li><a href="personnel.php"><i class="fas fa-users"></i> Personnel</a></li>
<li><a href="contracts.php"><i class="fas fa-file-signature"></i> Contracts</a></li>
<li><a href="documents.php"><i class="fas fa-folder-open"></i> Documents</a></li>
<li><a href="performance.php"><i class="fas fa-star"></i> Performance</a></li>
<li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
<li><a href="users.php"><i class="fas fa-user-cog"></i> User Management</a></li>
<li><a href="settings.php"><i class="fas fa-cogs"></i> Settings</a></li>
<li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
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

        <h4 class="mb-0">Performance Monitoring</h4>

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
<?php if(
    isset($_SESSION['role']) &&
    ($_SESSION['role']=='System Administrator' || $_SESSION['role']=='HR Administrator')
){ ?>
    <button
        class="btn btn-primary mb-4"
        data-bs-toggle="modal"
        data-bs-target="#addEval">

        <i class="fas fa-plus"></i>
        Add Evaluation
    </button>
<?php } ?>

<!-- PERFORMANCE SUMMARY -->
<div class="row g-4">

<div class="col-md-4">
<div class="card p-3">
<h6>Outstanding Personnel</h6>
<?php
$outstanding =
$conn->query("
SELECT COUNT(*) total
FROM performance
WHERE status='Outstanding'
")->fetch_assoc()['total'];
?>

<h3><?= $outstanding; ?></h3>
</div>
</div>

<div class="col-md-4">
<div class="card p-3">
<h6>Need Improvement</h6>
<?php
$poor =
$conn->query("
SELECT COUNT(*) total
FROM performance
WHERE status='Poor'
")->fetch_assoc()['total'];
?>

<h3><?= $poor; ?></h3>
</div>
</div>

<div class="col-md-4">
<div class="card p-3">
<h6>Evaluated This Year</h6>
<?php
$total =
$conn->query("
SELECT COUNT(*) total
FROM performance
WHERE YEAR(created_at)=YEAR(CURDATE())
")->fetch_assoc()['total'];
?>

<h3><?= $total; ?></h3>
</div>
</div>

</div>

<!-- PERFORMANCE TABLE -->
<div class="card table-card mt-4 p-3">

<h5>Performance Records</h5>

<div class="table-responsive mt-3">

<table class="table table-bordered">

<thead class="table-dark">
<tr>
<th>Employee</th>
<th>Evaluation Period</th>
<th>Rating</th>
<th>Evaluator</th>
<th>Comments</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>

<tbody>

<?php

$result = $conn->query("
SELECT *
FROM performance
ORDER BY id DESC
LIMIT $start, $limit
");

while($row = $result->fetch_assoc()){

if($row['status']=="Outstanding"){
    $badge="success";
}
elseif($row['status']=="Average"){
    $badge="warning";
}
else{
    $badge="danger";
}

?>

<tr>

<td><?= $row['employee_name']; ?></td>

<td><?= $row['evaluation_period']; ?></td>

<td><?= $row['rating']; ?></td>

<td><?= $row['evaluator']; ?></td>

<td><?= $row['comments']; ?></td>

<td>
<span class="badge bg-<?= $badge; ?>">
<?= $row['status']; ?>
</span>
</td>

<td>

<a href="view_performance.php?id=<?= $row['id']; ?>"
class="btn btn-info btn-sm">
<i class="fas fa-eye"></i>
</a>

<?php if($_SESSION['role'] === 'System Administrator' || $_SESSION['role'] === 'HR Administrator'){ ?>

<a href="edit_performance.php?id=<?= $row['id']; ?>"
class="btn btn-warning btn-sm">
<i class="fas fa-edit"></i>
</a>

<a href="delete_performance.php?id=<?= $row['id']; ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Delete evaluation?')">
<i class="fas fa-trash"></i>
</a>

<?php } ?>
</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>
<nav class="mt-3">
<ul class="pagination justify-content-center">

<?php if($page > 1){ ?>
<li class="page-item">
<a class="page-link" href="?page=<?= $page-1; ?>&limit=<?= $limit; ?>">
Previous
</a>
</li>
<?php } ?>

<?php for($i=1; $i <= $total_pages; $i++){ ?>
<li class="page-item <?= ($i==$page)?'active':''; ?>">
<a class="page-link" href="?page=<?= $i; ?>&limit=<?= $limit; ?>">
<?= $i; ?>
</a>
</li>
<?php } ?>

<?php if($page < $total_pages){ ?>
<li class="page-item">
<a class="page-link" href="?page=<?= $page+1; ?>&limit=<?= $limit; ?>">
Next
</a>
</li>
<?php } ?>

</ul>
</nav>

</div>

<!-- REPORTS -->
<div class="row mt-4">

<div class="col-lg-6">
<div class="card p-3">
<h5>Outstanding Personnel</h5>
<ul>

<?php

$result = $conn->query("
SELECT employee_name
FROM performance
WHERE status='Outstanding'
");

while($row=$result->fetch_assoc()){

echo "<li>".$row['employee_name']."</li>";

}

?>

</ul>
</div>
</div>

<div class="col-lg-6">
<div class="card p-3">
<h5>Personnel Requiring Improvement</h5>
<ul>

<?php

$result = $conn->query("
SELECT employee_name
FROM performance
WHERE status='Poor'
");

while($row=$result->fetch_assoc()){

echo "<li>".$row['employee_name']."</li>";

}

?>

</ul>
</div>
</div>

</div>

<!-- HISTORICAL -->
<div class="card mt-4 p-3">

<h5>Historical Ratings</h5>

<table class="table table-striped">

<thead>
<tr>
<th>Employee</th>
<th>2025</th>
<th>2026</th>
<th>2027</th>
</tr>
</thead>

<tbody>

<?php

$result = $conn->query("
SELECT
employee_name,
AVG(rating) avg_rating
FROM performance
GROUP BY employee_name
");

while($row=$result->fetch_assoc()){

?>

<tr>

<td><?= $row['employee_name']; ?></td>

<td colspan="3">
<?= number_format($row['avg_rating'],1); ?>
</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>
</div>
</div>

<!-- ADD EVALUATION MODAL -->
<div class="modal fade" id="addEval">
<div class="modal-dialog modal-lg">
<div class="modal-content">

<div class="modal-header">
<h5>Add Performance Evaluation</h5>
<button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<form method="POST">

<div class="row">

<?php
$employees = $conn->query("
SELECT employee_id, first_name, last_name
FROM personnel
ORDER BY first_name ASC
");
?>

<div class="col-md-12 mb-3">
<label>Select Employee</label>

<select name="employee_id" id="employeeSelect" class="form-control" required>
    <option value="">-- Select Employee --</option>

    <?php while($emp = $employees->fetch_assoc()){ ?>
        <option
            value="<?= $emp['employee_id']; ?>"
            data-name="<?= $emp['first_name'].' '.$emp['last_name']; ?>"
        >
            <?= $emp['employee_id']; ?> - <?= $emp['first_name'].' '.$emp['last_name']; ?>
        </option>
    <?php } ?>
</select>
</div>

<input type="hidden" name="employee_name" id="employeeName">

<div class="col-md-6 mb-3">
<label>Evaluation Period</label>
<select
name="evaluation_period"
class="form-control">
<option>Q1</option>
<option>Q2</option>
<option>Q3</option>
<option>Q4</option>
</select>
</div>

<div class="col-md-6 mb-3">
<label>Rating (1-5)</label>
<input
type="number"
step="0.1"
name="rating"
class="form-control"
min="1"
max="5">
</div>

<div class="col-md-6 mb-3">
<label>Evaluator</label>
<input
type="text"
name="evaluator"
class="form-control">
</div>

<div class="col-md-12 mb-3">
<label>Comments</label>
<textarea
name="comments"
class="form-control"></textarea>
</div>

</div>

<div class="modal-footer">

<button
type="button"
class="btn btn-secondary"
data-bs-dismiss="modal">
Cancel
</button>

<button
type="submit"
name="save_evaluation"
class="btn btn-primary">
Save Evaluation
</button>

</div>

</form>


</div>
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

<script>
document.getElementById("employeeSelect").addEventListener("change", function(){

    let selected = this.options[this.selectedIndex];
    let name = selected.getAttribute("data-name");

    document.getElementById("employeeName").value = name;

});
</script>

</body>
</html>