<?php
include "auth.php";
include "config.php";

$conn->query("
UPDATE documents
SET status='Terminated'
WHERE termination_date < CURDATE()
");

if(isset($_POST['upload_document'])){

    $employee_id = $_POST['employee_id'];
    $employee_name = $_POST['employee_name'];
    $document_type = $_POST['document_type'];
    $version = $_POST['version'];
    $termination_date = $_POST['termination_date'];

    $upload_dir = "uploads/documents/";

    if(!is_dir($upload_dir)){
        mkdir($upload_dir,0777,true);
    }

    foreach($_FILES['document_file']['name'] as $key => $file){

        $allowed = [
            'pdf',
            'doc',
            'docx',
            'jpg',
            'jpeg',
            'png'
        ];
        
        $ext = strtolower(
            pathinfo($file, PATHINFO_EXTENSION)
        );
        
        if(!in_array($ext,$allowed)){
            continue;
        }

        if(empty($file)){
            continue;
        }

        $size =
$_FILES['document_file']['size'][$key];

if($size > 5 * 1024 * 1024){

    echo "
    <script>
    alert('File size exceeds 5MB.');
    window.location='documents.php';
    </script>";

    exit();
}
    
        $tmp =
        $_FILES['document_file']['tmp_name'][$key];
    
        $newFile =
        uniqid().'_'.basename($file);
    
        move_uploaded_file(
            $tmp,
            $upload_dir.$newFile
        );
    
        $stmt = $conn->prepare("
            INSERT INTO documents(
            employee_id,
            employee_name,
            document_type,
            file_name,
            version,
            termination_date
            )
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "ssssss",
            $employee_id,
            $employee_name,
            $document_type,
            $newFile,
            $version,
            $termination_date
        );

        $stmt->execute();
    }

    header("Location: documents.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Documents | JOPMIS</title>

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

/* BADGE STYLE */
.badge-doc{
    font-size:12px;
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

<!-- TOPBAR -->
<div class="topbar d-flex justify-content-between align-items-center">

    <div class="d-flex align-items-center">

        <button
            id="sidebarToggle"
            class="btn btn-primary me-3 d-lg-none">

            <i class="fas fa-bars"></i>

        </button>

        <h4 class="mb-0">Document Management</h4>

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

<!-- DOCUMENT TYPES -->
<div class="row g-4">

<div class="col-md-3">
<div class="card p-3">
<h6>Personal Files</h6>
<ul class="small">
<li>Resume / PDS</li>
<li>Birth Certificate</li>
<li>Diploma</li>
</ul>
</div>
</div>

<div class="col-md-3">
<div class="card p-3">
<h6>Work Documents</h6>
<ul class="small">
<li>Performance Rating</li>
<li>Training Certificates</li>
<li>Appointment</li>
</ul>
</div>
</div>

<div class="col-md-3">
<div class="card p-3">
<h6>Clearances</h6>
<ul class="small">
<li>Medical Certificate</li>
<li>NBI Clearance</li>
<li>Other Clearances</li>
</ul>
</div>
</div>

<div class="col-md-3">
<div class="card p-3">
<h6>Monitoring</h6>
<p class="small mb-0">Tracks document validity and termination</p>
</div>
</div>

</div>

<!-- DOCUMENT TABLE -->
<div class="row mt-4 mb-3">
    <div class="col-md-4">
        <form method="GET">
            <input
                type="text"
                class="form-control"
                name="search"
                placeholder="Search Documents"
                value="<?= $_GET['search'] ?? ''; ?>">
        </form>
    </div>
</div>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Uploaded Documents</h5>

    <button
        class="btn btn-primary"
        data-bs-toggle="modal"
        data-bs-target="#uploadModal">

        <i class="fas fa-upload"></i>
        Upload Document

    </button>
</div>

<div class="table-responsive mt-3">

<table class="table table-bordered">

<thead class="table-dark">
<tr>
<th>Employee</th>
<th>Document Type</th>
<th>File Name</th>
<th>Date Uploaded</th>
<th>Version</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>

<tbody>

<?php

$limit_docs = 10;

$page_docs = isset($_GET['page_docs'])
    ? (int)$_GET['page_docs']
    : 1;

if($page_docs < 1){
    $page_docs = 1;
}

$start_docs = ($page_docs - 1) * $limit_docs;

$search = $_GET['search'] ?? '';

$total_docs = $conn->query("
SELECT COUNT(*) as total
FROM documents
WHERE
employee_name LIKE '%$search%'
OR employee_id LIKE '%$search%'
OR document_type LIKE '%$search%'
OR file_name LIKE '%$search%'
");

$total_docs_row = $total_docs->fetch_assoc();

$total_docs_pages = ceil(
    $total_docs_row['total'] / $limit_docs
);

// GET DOCUMENTS PER PAGE
$result = $conn->query("
SELECT *
FROM documents
WHERE
employee_name LIKE '%$search%'
OR employee_id LIKE '%$search%'
OR document_type LIKE '%$search%'
OR file_name LIKE '%$search%'
ORDER BY id DESC
LIMIT $start_docs, $limit_docs
");

while($row = $result->fetch_assoc()){

?>

<tr>

<td><?= $row['employee_name']; ?></td>

<td><?= $row['document_type']; ?></td>

<td><?= $row['file_name']; ?></td>

<td><?= $row['upload_date']; ?></td>

<td><?= $row['version']; ?></td>

<?php
$badge = "success";

if($row['status'] == 'Terminated'){
    $badge = "danger";
}
elseif($row['status'] == 'Terminating Soon'){
    $badge = "warning";
}
?>

<td>
<span class="badge bg-<?= $badge; ?>">
<?= $row['status']; ?>
</span>
</td>

<td>

<a href="view_document.php?id=<?= $row['id']; ?>"
class="btn btn-info btn-sm">
<i class="fas fa-eye"></i>
</a>

<?php if($_SESSION['role'] === 'System Administrator' || $_SESSION['role'] === 'HR Administrator'){ ?>
<a href="uploads/documents/<?= $row['file_name']; ?>"
class="btn btn-success btn-sm"
download>
<i class="fas fa-download"></i>
</a>

<a href="delete_document.php?id=<?= $row['id']; ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Delete Document?')">
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

<?php if($page_docs > 1){ ?>
<li class="page-item">
<a class="page-link"
href="?page_docs=<?= $page_docs-1; ?>&search=<?= $search; ?>">
Previous
</a>
</li>
<?php } ?>

<?php
for($i=1; $i<=$total_docs_pages; $i++){
?>
<li class="page-item <?= ($i==$page_docs)?'active':''; ?>">
<a class="page-link"
href="?page_docs=<?= $i; ?>&search=<?= $search; ?>">
<?= $i; ?>
</a>
</li>
<?php } ?>

<?php if($page_docs < $total_docs_pages){ ?>
<li class="page-item">
<a class="page-link"
href="?page_docs=<?= $page_docs+1; ?>&search=<?= $search; ?>">
Next
</a>
</li>
<?php } ?>

</ul>
</nav>

</div>

</div>

<!-- TERMINATION MONITORING -->
<div class="card mt-4 p-3">

<h5>Termination Monitoring (Clearances)</h5>

<table class="table">

<thead>
<tr>
<th>Employee</th>
<th>Document</th>
<th>Termination Date</th>
<th>Status</th>
</tr>
</thead>

<tbody>

<?php

$limit_exp = 10;

$page_exp = isset($_GET['page_exp'])
    ? (int)$_GET['page_exp']
    : 1;

if($page_exp < 1){
    $page_exp = 1;
}

$start_exp = ($page_exp - 1) * $limit_exp;

$total_exp = $conn->query("
SELECT COUNT(*) as total
FROM documents
WHERE termination_date IS NOT NULL
");

$total_exp_row = $total_exp->fetch_assoc();

$total_exp_pages = ceil(
    $total_exp_row['total'] / $limit_exp
);


$today = date('Y-m-d');

$result = $conn->query("
SELECT *
FROM documents
WHERE termination_date IS NOT NULL
ORDER BY termination_date ASC
LIMIT $start_exp, $limit_exp
");

while($row = $result->fetch_assoc()){

$status = "Active";
$badge = "success";

if($row['termination_date'] < $today){

    $status = "Terminated";
    $badge = "danger";

}else{

    $days = floor(
        (strtotime($row['termination_date']) - time())
        / 86400
    );

    if($days <= 30){

        $status = "Terminating Soon";
        $badge = "warning";
    }
}

?>

<tr>

<td><?= $row['employee_name']; ?></td>

<td><?= $row['document_type']; ?></td>

<td><?= $row['termination_date']; ?></td>

<td>
<span class="badge bg-<?= $badge; ?>">
<?= $status; ?>
</span>
</td>

</tr>

<?php } ?>

</tbody>

</table>

<nav class="mt-3">
<ul class="pagination justify-content-center">

<?php if($page_exp > 1){ ?>
<li class="page-item">
<a class="page-link"
href="?page_docs=<?= $page_docs ?? 1; ?>&page_exp=<?= $page_exp-1; ?>">
Previous
</a>
</li>
<?php } ?>

<?php
for($i=1; $i<=$total_exp_pages; $i++){
?>
<li class="page-item <?= ($i==$page_exp)?'active':''; ?>">
<a class="page-link"
href="?page_docs=<?= $page_docs ?? 1; ?>&page_exp=<?= $i; ?>">
<?= $i; ?>
</a>
</li>
<?php } ?>

<?php if($page_exp < $total_exp_pages){ ?>
<li class="page-item">
<a class="page-link"
href="?page_docs=<?= $page_docs ?? 1; ?>&page_exp=<?= $page_exp+1; ?>">
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

<!-- UPLOAD MODAL -->
<div class="modal fade" id="uploadModal">
<div class="modal-dialog modal-lg">
<div class="modal-content">

<div class="modal-header">
<h5>Upload Document</h5>
<button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<form method="POST" enctype="multipart/form-data">

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
<label>Document Type</label>
<select class="form-control" name="document_type" required>
<option>Resume / PDS</option>
<option>Birth Certificate</option>
<option>Diploma</option>
<option>Training Certificate</option>
<option>Performance Rating</option>
<option>Medical Certificate</option>
<option>NBI Clearance</option>
<option>Other</option>
</select>
</div>

<div class="col-md-6 mb-3">
<label>Upload File</label>
<input type="file" class="form-control" name="document_file[]" multiple accept=".pdf,.doc,.jpg,.jpeg,.png" capture="environment" required>
<small> You can select multiple files.</small>
</div>

<div class="col-md-6 mb-3">
<label>Version</label>
<input type="text" class="form-control" name="version" value="v1">
</div>

<div class="col-md-6 mb-3">

<label>Expiration Date</label>

<input
type="date"
name="termination_date"
class="form-control">

</div>

</div>


</div>

<div class="modal-footer">
<button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
<button
type="submit"
name="upload_document"
class="btn btn-primary">
Upload
</button>
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

<script>
document.getElementById("employeeSelect").addEventListener("change", function(){

    let selected = this.options[this.selectedIndex];
    let name = selected.getAttribute("data-name");

    document.getElementById("employeeName").value = name;

});
</script>

</html>