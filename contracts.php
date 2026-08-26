<?php
include "auth.php";
include "config.php";
include "role_access.php";



if(isset($_POST['save_contract'])){

    $contract_id = $_POST['contract_id'];
    $employee_id = $_POST['employee_id'];
    $employee_name = $_POST['employee_name'];
    $position_title = $_POST['position_title'];
    $employment_type = $_POST['employment_type'];
    $salary_grade    = $_POST['salary_grade'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("
SELECT *
FROM contracts
WHERE employee_id=?
AND status='Active'
");

$stmt->bind_param("s", $employee_id);
$stmt->execute();
$check = $stmt->get_result();

    if($check->num_rows > 0){
        echo "
        <script>
        alert('This employee already has an active contract.');
        window.location='contracts.php';
        </script>";
        exit();
    }

    // Upload Folder
    $upload_dir = "uploads/contracts/";

    if(!is_dir($upload_dir)){
        mkdir($upload_dir,0777,true);
    }

    function saveFiles(
        $conn,
        $files,
        $type,
        $contract_id,
        $upload_dir
    ){
        $allowed =
        ['pdf','doc','docx','jpg','jpeg','png'];

        foreach($files['name'] as $key => $filename){
    
            if($filename == ''){
                continue;
            }
            $ext = strtolower(
                pathinfo($filename, PATHINFO_EXTENSION)
            );
            
            if(!in_array($ext,$allowed)){
                continue;
            }
    
            $newname =
                time().'_'.$filename;
    
            move_uploaded_file(
                $files['tmp_name'][$key],
                $upload_dir.$newname
            );
    
            $conn->query("
            INSERT INTO contract_documents(
                contract_id,
                document_type,
                file_name
            )
            VALUES(
                '$contract_id',
                '$type',
                '$newname'
            )
            ");
        }
    }

    $sql = "INSERT INTO contracts(
        contract_id,
        employee_id,
        employee_name,
        position_title,
        start_date,
        end_date,
        status
    )
    VALUES(
        '$contract_id',
        '$employee_id',
        '$employee_name',
        '$position_title',
        '$start_date',
        '$end_date',
        '$status'
    )";

    $conn->query($sql);
    /* ======================================
   AUTO CREATE SERVICE RECORD
====================================== */

$getPersonnel = $conn->query("
SELECT *
FROM personnel
WHERE employee_id='$employee_id'
");

if($getPersonnel->num_rows > 0){

    $person = $getPersonnel->fetch_assoc();

    $personnel_id = $person['id'];

    $office = $person['office_assignment'];

    $salary = $person['monthly_rate'];

    $conn->query("
    INSERT INTO service_records(

        personnel_id,
        contract_id,
        employee_id,
        employee_name,
        position_title,
        employment_type,
        office_assignment,
        date_from,
        date_to,
        monthly_rate,
        salary_grade,
        status

    )

    VALUES(

        '$personnel_id',
        '$contract_id',
        '$employee_id',
        '$employee_name',
        '$position_title',
        '$employment_type',
        '$office',
        '$start_date',
        '$end_date',
        '$salary',
        '$salary_grade',
        '$status'

    )
    ");

}

/* ======================================
   END AUTO SERVICE RECORD
====================================== */

saveFiles(
    $conn,
    $_FILES['appointment'],
    'Appointment',
    $contract_id,
    $upload_dir
);
    saveFiles(
        $conn,
        $_FILES['appointment'],
        'Appointment',
        $contract_id,
        $upload_dir
    );
    
    saveFiles(
        $conn,
        $_FILES['contract_file'],
        'Contract',
        $contract_id,
        $upload_dir
    );
    
    saveFiles(
        $conn,
        $_FILES['renewal'],
        'Renewal',
        $contract_id,
        $upload_dir
    );
    
    saveFiles(
        $conn,
        $_FILES['certification'],
        'Certification',
        $contract_id,
        $upload_dir
    );

    header("Location: contracts.php");
    exit();
}
?>
<?php

$conn->query("
UPDATE contracts
SET status='Active'
WHERE end_date < CURDATE()
AND status='Active'
");

$result = $conn->query("
SELECT contract_id
FROM contracts
ORDER BY id DESC
LIMIT 1
");

if($result->num_rows > 0){

    $row = $result->fetch_assoc();

    $last_id = $row['contract_id'];

    $number = (int)substr($last_id,4);

    $number++;

    $new_contract_id = "CON-" . str_pad($number,4,"0",STR_PAD_LEFT);

}else{

    $new_contract_id = "CON-0001";

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Contracts | JOPMIS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

<link rel="stylesheet" href="assets/css/theme.css">

<style>

:root{
    --sidebar:#0f172a;
    --bg:#f1f5f9;
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

.stat-card{
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
<li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
<li><a href="users.php"><i class="fas fa-user-cog"></i> User Management</a></li>
<li><a href="settings.php"><i class="fas fa-cogs"></i> Settings</a></li>
<li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
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

        <h4 class="mb-0">Contract Management</h4>

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

<div class="row mb-3">

<div class="col-md-4">
    <form method="GET" class="d=flex">
        <input
            type="text"
            class="form-control"
            id="searchContract"
            name="search"
            value="<?= $_GET['search'] ?? ''; ?>"
            placeholder="Search Contracts">
    </form>
</div>

</div>


<!-- STATS -->
<div class="row g-4">

<div class="col-md-3">
<div class="card p-3">
<h6>Active Contracts</h6>
<?php
$active = $conn->query("
SELECT COUNT(*) as total
FROM contracts
WHERE status='Active'
");

$activeRow = $active->fetch_assoc();
?>

<h3><?= $activeRow['total']; ?></h3>
</div>
</div>

<div class="col-md-3">
<div class="card p-3">
<h6>Termination After 90 Days</h6>
<?php
$q90 = $conn->query("
SELECT COUNT(*) as total
FROM contracts
WHERE DATEDIFF(end_date,CURDATE()) BETWEEN 61 AND 90
");

$r90 = $q90->fetch_assoc();
?>

<h3><?= $r90['total']; ?></h3>
</div>
</div>

<div class="col-md-3">
<div class="card p-3">
<h6>Termination After 60 Days</h6>
<?php
$q60 = $conn->query("
SELECT COUNT(*) as total
FROM contracts
WHERE DATEDIFF(end_date,CURDATE()) BETWEEN 31 AND 60
");

$r60 = $q60->fetch_assoc();
?>

<h3><?= $r60['total']; ?></h3>
</div>
</div>

<div class="col-md-3">
<div class="card p-3">
<h6>Termination After 30 Days</h6>
<?php
$q30 = $conn->query("
SELECT COUNT(*) as total
FROM contracts
WHERE DATEDIFF(end_date,CURDATE()) <= 30
AND DATEDIFF(end_date,CURDATE()) >= 0
");

$r30 = $q30->fetch_assoc();
?>

<h3><?= $r30['total']; ?></h3>
</div>
</div>

</div>

<!-- BUTTON -->
<div class="d-flex justify-content-between align-items-center mt-4 mb-3">

<h5>Contract Records</h5>

<?php if($_SESSION['role'] == 'System Administrator' || $_SESSION['role'] == 'HR Administrator'){ ?>
<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addContract">
<i class="fas fa-plus"></i> Create Contract
</button>
<?php } ?>

</div>

<!-- TABLE -->
<div class="card table-card p-3">

<table class="table table-bordered">
<thead class="table-dark">
<tr>
<th>ID</th>
<th>Employee</th>
<th>Position</th>
<th>Start</th>
<th>End</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>

<tbody>

<?php

// Pagination
$limit = 10;

$contract_page = isset($_GET['contract_page'])
    ? (int)$_GET['contract_page']
    : 1;

if($contract_page < 1){
    $contract_page = 1;
}

$start = ($contract_page - 1) * $limit;

// Count all records
$search = $_GET['search'] ?? '';
$search_param = "%".$search."%";

$stmtTotal = $conn->prepare("
SELECT COUNT(*) as total
FROM contracts
WHERE
employee_name LIKE ?
OR employee_id LIKE ?
OR contract_id LIKE ?
OR position_title LIKE ?
");

$stmtTotal->bind_param(
    "ssss",
    $search_param,
    $search_param,
    $search_param,
    $search_param
);

$stmtTotal->execute();
$total_result = $stmtTotal->get_result();

$total_row = $total_result->fetch_assoc();
$total_records = $total_row['total'];

$contract_total_pages =
ceil($total_records / $limit);

// Get contracts per page

$stmt = $conn->prepare("
SELECT *
FROM contracts
WHERE
employee_name LIKE ?
OR employee_id LIKE ?
OR contract_id LIKE ?
OR position_title LIKE ?
ORDER BY id DESC
LIMIT ?, ?
");

$stmt->bind_param(
    "ssssii",
    $search_param,
    $search_param,
    $search_param,
    $search_param,
    $start,
    $limit
);

$stmt->execute();
$result = $stmt->get_result();

while($row = $result->fetch_assoc()){

?>
<tr>

<td><?= $row['contract_id']; ?></td>
<td><?= $row['employee_name']; ?></td>
<td><?= $row['position_title']; ?></td>
<td><?= $row['start_date']; ?></td>
<td><?= $row['end_date']; ?></td>

<td>
<span class="badge bg-success">
<?= $row['status']; ?>
</span>
</td>

<td>
    <a href="view_contract.php?id=<?= $row['id']; ?>"
       class="btn btn-info btn-sm">
        <i class="fas fa-eye"></i>
    </a>

    <?php if($_SESSION['role'] === 'System Administrator' || $_SESSION['role'] === 'HR Administrator'){ ?>

        <a href="edit_contract.php?id=<?= $row['id']; ?>"
           class="btn btn-warning btn-sm">
            <i class="fas fa-edit"></i>
        </a>

        <?php if($row['status'] != 'Terminated'){ ?>
        <a href="renew_contract.php?id=<?= $row['id']; ?>"
           class="btn btn-success btn-sm"
           onclick="return confirm('Renew this contract?')">
            <i class="fas fa-sync"></i>
        </a>
        <?php } ?>
        
        <a href="delete_contract.php?id=<?= $row['id']; ?>"
           class="btn btn-danger btn-sm"
           onclick="return confirm('Delete Contract?')">
            <i class="fas fa-trash"></i>
        </a>

    <?php } ?>
   
</td>
</tr>
<?php } ?>
</tbody>
</table>

<nav class="mt-3">
<ul class="pagination justify-content-center">

<?php if($contract_page > 1){ ?>
<li class="page-item">
<a class="page-link"
href="?contract_page=<?= $i ?>&alert_page=<?= $_GET['alert_page'] ?? 1 ?>&search=<?= urlencode($search) ?>">
Previous
</a>
</li>
<?php } ?>

<?php
for($i=1; $i <= $contract_total_pages; $i++){
?>
<li class="page-item <?= ($i==$contract_page)?'active':''; ?>">
<a class="page-link"
href="?contract_page=<?= $i ?>&alert_page=<?= $_GET['alert_page'] ?? 1 ?>&search=<?= urlencode($search) ?>">
<?= $i ?>
</a>
</li>
<?php } ?>

<?php if($contract_page < $contract_total_pages){ ?>
<li class="page-item">
<a class="page-link"
href="?contract_page=<?= $i ?>&alert_page=<?= $_GET['alert_page'] ?? 1 ?>&search=<?= urlencode($search) ?>">
Next
</a>
</li>
<?php } ?>

</ul>
</nav>

</div>

<!-- ALERTS -->
<div class="card mt-4 p-3">

<h5>Contract Termination Alerts</h5>

<table class="table">
<tr>
<th>Employee</th>
<th>End Date</th>
<th>Status</th>
</tr>

<?php

$alert_page = isset($_GET['alert_page'])
    ? (int)$_GET['alert_page']
    : 1;

if($alert_page < 1){
    $alert_page = 1;
}

$alert_limit = 5;
$alert_start = ($alert_page - 1) * $alert_limit;

$count_alert = $conn->query("
SELECT COUNT(*) as total
FROM contracts
WHERE DATEDIFF(end_date,CURDATE()) <= 90
");

$row_alert = $count_alert->fetch_assoc();

$alert_total_pages =
ceil($row_alert['total'] / $alert_limit);

$alerts = $conn->query("
SELECT *,
DATEDIFF(end_date,CURDATE()) as days_left
FROM contracts
WHERE DATEDIFF(end_date,CURDATE()) <= 90
ORDER BY end_date ASC
LIMIT $alert_start,$alert_limit
");

while($a = $alerts->fetch_assoc()){

    if($a['days_left'] <= 30){
        $badge = "danger";
        $label = "30 Days";
    }
    elseif($a['days_left'] <= 60){
        $badge = "warning";
        $label = "60 Days";
    }
    else{
        $badge = "primary";
        $label = "90 Days";
    }

?>
<tr>
<td><?= $a['employee_name']; ?></td>
<td><?= $a['end_date']; ?></td>
<td>
<span class="badge bg-<?= $badge; ?>">
<?= $label; ?>
</span>
</td>
</tr>
<?php } ?>

</table>

<nav class="mt-3">
<ul class="pagination justify-content-center">

<?php if($alert_page > 1){ ?>
<li class="page-item">
<a class="page-link"
href="?contract_page=<?= $contract_page ?>&alert_page=<?= $alert_page-1 ?>">
Previous
</a>
</li>
<?php } ?>

<?php
for($i=1;$i<=$alert_total_pages;$i++){
?>
<li class="page-item <?= ($i==$alert_page)?'active':''; ?>">
<a class="page-link"
href="?contract_page=<?= $contract_page ?>&alert_page=<?= $i ?>">
<?= $i ?>
</a>
</li>
<?php } ?>

<?php if($alert_page < $alert_total_pages){ ?>
<li class="page-item">
<a class="page-link"
href="?contract_page=<?= $contract_page ?>&alert_page=<?= $alert_page+1 ?>">
Next
</a>
</li>
<?php } ?>

</ul>
</nav>

</div>

</div>
</div>

</div>

<!-- MODAL -->
<div class="modal fade" id="addContract">
<div class="modal-dialog modal-xl">
<div class="modal-content">

<div class="modal-header">
<h5>Create Contract</h5>
<button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<form method="POST"
      enctype="multipart/form-data">

<div class="row">

<div class="col-md-4 mb-3">
<label>Contract ID</label>
<input type="text" class="form-control" name="contract_id" value="<?= $new_contract_id; ?>" readonly>
</div>

<div class="col-md-4 mb-3">
<label>Employee ID</label>
<select
class="form-control"
name="employee_id"
id="employee_id">

<option value="">
Select Employee
</option>

<?php

$personnel = $conn->query("
SELECT *
FROM personnel
ORDER BY first_name ASC
");

while($p = $personnel->fetch_assoc()){

?>

<option
value="<?= $p['employee_id']; ?>"
data-name="<?= $p['first_name'].' '.$p['last_name']; ?>"
data-position="<?= $p['position_title']; ?>">

<?= $p['employee_id']; ?>
 -
<?= $p['first_name']; ?>
 <?= $p['last_name']; ?>

</option>

<?php } ?>

</select>
</div>

<div class="col-md-4 mb-3">
<label>Name</label>
<input type="text"
class="form-control"
name="employee_name"
id="employee_name"
readonly>
</div>

<div class="col-md-4 mb-3">
<label>Position</label>
<input type="text"
class="form-control"
name="position_title"
id="position_title"
readonly>
</div>

<div class="col-md-4 mb-3">
    <label>Employment Type</label>
    <input type="text"
           class="form-control"
           name="employment_type"
           placeholder="Example: Job Order">
</div>

<div class="col-md-4 mb-3">
    <label>Salary Grade</label>
    <input type="text"
           class="form-control"
           name="salary_grade"
           placeholder="Optional">
</div>

<div class="col-md-4 mb-3">
<label>Start Date</label>
<input type="date" class="form-control" name="start_date">
</div>

<div class="col-md-4 mb-3">
<label>End Date</label>
<input type="date" class="form-control" name="end_date">
</div>

<div class="col-md-4 mb-3">
<label>Status</label>
<select class="form-control" name="status">
<option>Active</option>
<option>Renewed</option>
<option>Terminated</option>
</select>
</div>

</div>

<hr>

<h6>Documents</h6>

<div class="row">

<div class="col-md-6 mb-3">
<label>Appointment</label>
<input type="file" class="form-control" name="appointment[]" multiple>
</div>

<div class="col-md-6 mb-3">
<label>Contract of Service</label>
<input type="file" class="form-control" name="contract_file[]" multiple>
</div>

<div class="col-md-6 mb-3">
<label>Renewal</label>
<input type="file" class="form-control" name="renewal[]" multiple>
</div>

<div class="col-md-6 mb-3">
<label>Certification</label>
<input type="file" class="form-control" name="certification[]" multiple>
</div>

</div>

</div>

<div class="modal-footer">
<button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
<button type="submit"
    class="btn btn-primary"
    name="save_contract">
    Save Contract
</button>
</div>

</form>
</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

document.getElementById('employee_id')
.addEventListener('change', function(){

    let selected =
    this.options[this.selectedIndex];

    document.getElementById('employee_name').value =
    selected.getAttribute('data-name');

    document.getElementById('position_title').value =
    selected.getAttribute('data-position');

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
document.getElementById('searchContract').addEventListener('keyup', function(){

    let value = this.value.toLowerCase();
    let rows =
document.querySelectorAll(
'.table-card tbody tr'
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
</body>
</html>
