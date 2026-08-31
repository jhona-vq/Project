<?php

include "auth.php";
include "config.php";
include "role_access.php";

/*
|--------------------------------------------------------------------------
| ROLE ACCESS
|--------------------------------------------------------------------------
*/
allowRoles([
    'System Administrator',
    'HR Administrator'
]);


/*
|--------------------------------------------------------------------------
| GET PERSONNEL ID
|--------------------------------------------------------------------------
*/
$id = $_GET['id'] ?? '';

$row = [];


/*
|--------------------------------------------------------------------------
| GET PERSONNEL DATA
|--------------------------------------------------------------------------
*/
if ($id != '') {

    $id = intval($id);

    $query = $conn->query("
        SELECT *
        FROM personnel
        WHERE id = '$id'
    ");

    if ($query && $query->num_rows > 0) {
        $row = $query->fetch_assoc();
    }
}


/*
|--------------------------------------------------------------------------
| GENERATE NEXT EMPLOYEE ID
|--------------------------------------------------------------------------
|
| Format:
| EMP-0001
| EMP-0002
| EMP-0003
|
|--------------------------------------------------------------------------
*/

$result = $conn->query("
    SELECT MAX(
        CAST(
            SUBSTRING(employee_id, 5)
            AS UNSIGNED
        )
    ) AS max_id
    FROM personnel
    WHERE employee_id LIKE 'EMP-%'
");

if ($result && $result->num_rows > 0) {

    $empRow = $result->fetch_assoc();

    if (!empty($empRow['max_id'])) {
        $number = (int)$empRow['max_id'] + 1;
    } else {
        $number = 1;
    }

} else {

    $number = 1;

}

$employee_id = "EMP-" . str_pad(
    $number,
    4,
    "0",
    STR_PAD_LEFT
);


/*
|--------------------------------------------------------------------------
| SAVE PERSONNEL
|--------------------------------------------------------------------------
*/

if (isset($_POST['save_personnel'])) {

    /*
    |--------------------------------------------------------------------------
    | GET FORM VALUES
    |--------------------------------------------------------------------------
    */

    $employee_id = $_POST['employee_id'] ?? '';

    $last_name = $_POST['last_name'] ?? '';
    $first_name = $_POST['first_name'] ?? '';
    $middle_name = $_POST['middle_name'] ?? '';

    $birth_date = $_POST['birth_date'] ?? '';
    $sex = $_POST['sex'] ?? '';
    $date_hired = $_POST['date_hired'] ?? '';
    $employment_status = $_POST['employment_status'] ?? '';
    $office_assignment = $_POST['office_assignment'] ?? '';
    $province = $_POST['province'] ?? '';

    $position_title = $_POST['position_title'] ?? '';
    $employment_category = $_POST['employment_category'] ?? '';
    $contract_start = $_POST['contract_start'] ?? '';
    $contract_end = $_POST['contract_end'] ?? '';
    $supervisor = $_POST['supervisor'] ?? '';
    $daily_rate = $_POST['daily_rate'] ?? '';
    $monthly_rate = $_POST['monthly_rate'] ?? '';

    $place_of_birth = $_POST['place_of_birth'] ?? '';
    $civil_status = $_POST['civil_status'] ?? '';
    $height = $_POST['height'] ?? '';
    $weight = $_POST['weight'] ?? '';
    $blood_type = $_POST['blood_type'] ?? '';

    $umid_no = $_POST['umid_no'] ?? '';
    $pagibig_no = $_POST['pagibig_no'] ?? '';
    $philhealth_no = $_POST['philhealth'] ?? '';
    $psn = $_POST['psn'] ?? '';
    $tin_no = $_POST['tin'] ?? '';
    $agency_employee_no = $_POST['agency_employee_no'] ?? '';

    $citizenship = $_POST['citizenship'] ?? '';

    $residential_address = $_POST['residential_address'] ?? '';
    $permanent_address = $_POST['permanent_address'] ?? '';

    $telephone_no = $_POST['telephone_no'] ?? '';
    $contact_no = $_POST['contact_no'] ?? '';
    $email = $_POST['email'] ?? '';


    /*
    |--------------------------------------------------------------------------
    | INSERT PERSONNEL
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("

        INSERT INTO personnel (

            employee_id,
            last_name,
            first_name,
            middle_name,

            birth_date,
            sex,
            date_hired,
            employment_status,
            office_assignment,
            province,

            position_title,
            employment_category,
            contract_start,
            contract_end,
            supervisor,
            daily_rate,
            monthly_rate,
        
            place_of_birth,
            civil_status,
            height,
            weight,
            blood_type,

            umid_no,
            pagibig_no,
            philhealth_no,
            psn,
            tin_no,
            agency_employee_no,

            citizenship,

            residential_address,
            permanent_address,

            telephone_no,
            contact_no,
            email

        )

        VALUES (

            ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?,
            ?, ?,
            ?, ?, ?

        )

    ");


    /*
    |--------------------------------------------------------------------------
    | CHECK PREPARE
    |--------------------------------------------------------------------------
    */

    if (!$stmt) {

        die("Prepare failed: " . $conn->error);

    }


    /*
    |--------------------------------------------------------------------------
    | BIND PARAMETERS
    |--------------------------------------------------------------------------
    |
    | 35 VALUES = 35 type characters
    |
    | s = string
    | d = decimal/double
    |
    |--------------------------------------------------------------------------
    */

    $stmt->bind_param(

        "sssssssssssssssssssssssssss",
    
        $employee_id,
    
        $last_name,
        $first_name,
        $middle_name,
    
        $birth_date,
        $sex,
        $date_hired,
        $employment_status,
        $office_assignment,
        $province,

        $position_title,
        $employment_category,
        $contract_start,
        $contract_end,
        $supervisor,
        $daily_rate,
        $monthly_rate,
    
        $place_of_birth,
        $civil_status,
        $height,
        $weight,
        $blood_type,
    
        $umid_no,
        $pagibig_no,
        $philhealth_no,
        $psn,
        $tin_no,
        $agency_employee_no,
    
        $citizenship,
    
        $residential_address,
        $permanent_address,
    
        $telephone_no,
        $contact_no,
        $email
    
    );

    /*
    |--------------------------------------------------------------------------
    | EXECUTE
    |--------------------------------------------------------------------------
    */

    if ($stmt->execute()) {

        $new_id = $conn->insert_id;

        echo "
        <script>

            alert('Personnel Added Successfully!');

            window.location='personnel.php?id=" . $new_id . "';

        </script>
        ";

        exit;

    } else {

        echo "
        <script>

            alert(" . json_encode($stmt->error) . ");

        </script>
        ";

    }

    $stmt->close();
}

?>

<!DOCTYPE html>
<html>
<head>
<title>Personnel Profile</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

<style>

:root{
    --primary:#2563eb;
    --secondary:#1e40af;
    --light:#f8fafc;
    --dark:#0f172a;
    --border:#e5e7eb;
    --shadow:0 10px 30px rgba(0,0,0,.08);
}

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    background:var(--light);
    font-family:'Segoe UI',sans-serif;
    overflow-x:hidden;
}

/* =======================
SIDEBAR
======================= */

.wrapper{
    display:flex;
}

.sidebar{
    width:270px;
    height:100vh;
    position:fixed;
    left:0;
    top:0;
    overflow-y:auto;
    background:linear-gradient(180deg,#020617,#0f172a,#1e3a8a);
    color:#fff;
    transition:.35s;
    z-index:1050;
}

.logo{
    padding:25px;
    text-align:center;
    border-bottom:1px solid rgba(255,255,255,.1);
}

.logo h4{
    margin-bottom:5px;
    font-weight:bold;
}

.sidebar ul{
    list-style:none;
    padding:0;
    margin:0;
}

.sidebar ul li a{
    display:block;
    padding:15px 25px;
    color:#fff;
    text-decoration:none;
    transition:.3s;
}

.sidebar ul li a:hover{
    background:rgba(255,255,255,.12);
}

.sidebar ul li a.active{
    background:rgba(255,255,255,.15);
    border-left:5px solid #60a5fa;
}

.sidebar ul li ul li a{
    padding-left:45px;
    font-size:15px;
}

/* =======================
MAIN
======================= */

.main{
    margin-left:270px;
    width:calc(100% - 270px);
    min-height:100vh;
}

/* =======================
TOPBAR
======================= */

.topbar{
    height:70px;
    background:#fff;
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:0 25px;
    box-shadow:0 3px 15px rgba(0,0,0,.05);
    position:sticky;
    top:0;
    z-index:100;
}

.page-content{
    padding:30px;
}
.page-title{

font-size:28px;
font-weight:700;
margin-bottom:25px;

}

/* =======================
PROFILE CARD
======================= */

.profile-photo-box{
    text-align:center;
    border:1px solid #e5e7eb;
    border-radius:18px;
    padding:30px;
    background:#fff;
    height:100%;
}

.profile-photo{
    width:170px;
    height:170px;
    border-radius:50%;
    object-fit:cover;
    border:5px solid #fff;
    box-shadow:0 8px 25px rgba(0,0,0,.15);
}

.section-title{
    color:#2563eb;
    font-weight:700;
    letter-spacing:.5px;
}

.card p{
    margin-bottom:18px;
    font-size:15px;
}

.card strong{
    color:#111827;
}

.btn{
    border-radius:12px;
    font-weight:600;
}

.badge{
    font-size:13px;
}

/* =======================
CARDS
======================= */

.card{
    border:none;
    border-radius:18px;
    box-shadow:var(--shadow);
}
.card-header{

background:#fff;
border-bottom:1px solid #e5e7eb;
font-weight:600;

}

/* =======================
TABS
======================= */

.profile-tabs{

background:#fff;
padding:15px;
border-radius:18px;
box-shadow:0 3px 10px rgba(0,0,0,.08);

}

.profile-tabs .nav-link{

border-radius:12px;
padding:12px 25px;
font-weight:600;
color:#475569;
transition:.3s;

}

.profile-tabs .nav-link:hover{

background:#eff6ff;
color:#2563eb;

}

.profile-tabs .nav-link.active{

background:#2563eb;
color:#fff;

}

/* ===========================
   MODERN ACCORDION
=========================== */

.accordion-item{

border:none;
border-radius:18px !important;
overflow:hidden;
margin-bottom:20px;
box-shadow:0 4px 12px rgba(0,0,0,.08);

}

.accordion-header{

background:white;

}

.accordion-button{

background:#fff;
font-size:16px;
font-weight:600;
padding:18px 20px;
box-shadow:none;
border:none;

}

.accordion-button i{

width:28px;
color:#2563eb;

}
.accordion-body{

padding:25px;

}

/* =======================
TABLE
======================= */

.table-responsive{
    overflow-x:auto;
    border-radius:15px;
}

.table{
    width:100%;
    margin-bottom:0;
    vertical-align:middle;
}

.table thead th{
    background:#2563eb;
    color:#fff;
    border:none;
    padding:14px;
    white-space:nowrap;
    font-weight:600;
}

.table tbody td{
    padding:14px;
    vertical-align:middle;
}

.table-hover tbody tr:hover{
    background:#eff6ff;
}
/* =======================
FORM
======================= */

.form-control,
.form-select{
    border-radius:10px;
}

/* =======================
MODAL
======================= */

.modal-dialog{
    max-width:1200px;
}

.modal-content{
    border-radius:18px;
    max-height:95vh;
    display:flex;
    flex-direction:column;
}

.modal-body{
    overflow-y:auto;
    max-height:calc(95vh - 130px);
}

.modal-header,
.modal-footer{
    flex-shrink:0;
}

/* =======================
OVERLAY
======================= */

.sidebar-overlay{
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.45);
    z-index:1040;
}

.sidebar-overlay.show{
    display:block;
}

/* =======================
DARK MODE
======================= */

.dark-mode{
    background:#0f172a;
    color:#fff;
}
.dark-mode .text-muted{
    color:#cbd5e1 !important;
}
.dark-mode label{
    color:#fff;
}
.dark-mode small{
    color:#cbd5e1 !important;
}
.dark-mode .card-header{
    background:#1e293b;
    color:#fff;
    border-color:#334155;
}
.dark-mode .accordion-body{
    background:#1e293b;
    color:#fff;
}

.dark-mode .card,
.dark-mode .profile-card,
.dark-mode .topbar,
.dark-mode .accordion-item,
.dark-mode .modal-content{
    background:#1e293b;
    color:#fff;
}

.dark-mode .table{
    color:#fff;
}
.dark-mode th{
    color:#fff;
}
.dark-mode td{
    color:#fff;
}

.dark-mode .form-control,
.dark-mode .form-select{
    background:#334155;
    color:#fff;
    border:1px solid #475569;
}

.dark-mode .accordion-button{
    background:#334155;
    color:#fff;
}
.dark-mode .accordion-button{
    background:#1e293b;
    color:#fff;
}
.dark-mode .accordion-button:not(.collapsed){
    background:#2563eb;
    color:#fff;
}

.dark-mode .profile-tabs{
    background:#1e293b;
}

.dark-mode .profile-tabs .nav-link{
    color:#cbd5e1;
    background:transparent;
}

.dark-mode .profile-tabs .nav-link:hover{
    background:#334155;
    color:#fff;
}

.dark-mode .profile-tabs .nav-link.active{
    background:#2563eb;
    color:#fff;
}
.dark-mode .table-light{
    --bs-table-bg:#334155;
    --bs-table-color:#fff;
}
.dark-mode .table-primary{
    --bs-table-bg:#2563eb;
    --bs-table-color:#fff;
}

.dark-mode .form-control,
.dark-mode .form-select,
.dark-mode textarea{
    background:#334155;
    color:#fff;
    border:1px solid #475569;
}

.dark-mode .form-control::placeholder{
    color:#cbd5e1;
}

.dark-mode .btn-link{
    color:#fff;
}

.dark-mode hr{
    border-color:#475569;
}
/* Cards */

.dark-mode .card{
    background:#1e293b;
    color:#fff;
}

.dark-mode .card-header{
    background:#1e293b;
    color:#fff;
    border-bottom:1px solid #334155;
}

/* Text */

.dark-mode label{
    color:#cbd5e1;
}

.dark-mode .text-muted{
    color:#cbd5e1 !important;
}

.dark-mode .fw-semibold{
    color:#fff;
}

.dark-mode strong{
    color:#fff;
}

.dark-mode h1,
.dark-mode h2,
.dark-mode h3,
.dark-mode h4,
.dark-mode h5,
.dark-mode h6{
    color:#fff;
}

.dark-mode p{
    color:#e2e8f0;
}
/* =======================
   DARK MODE - SERVICE RECORD
======================= */

.dark-mode #service .table td,
.dark-mode #service .table th{
    background:#1e293b;
    color:#fff;
    border-color:#475569;
}

.dark-mode #service .table-hover tbody tr:hover{
    background:#334155;
}
/* ======================================
   DARK MODE - PDS CONTENT
====================================== */

/* Accordion body */
.dark-mode .accordion-body{
    background:#1e293b !important;
    color:#fff;
}

/* Lahat ng text sa loob ng accordion */
.dark-mode .accordion-body,
.dark-mode .accordion-body p,
.dark-mode .accordion-body span,
.dark-mode .accordion-body div,
.dark-mode .accordion-body td,
.dark-mode .accordion-body th,
.dark-mode .accordion-body label,
.dark-mode .accordion-body h1,
.dark-mode .accordion-body h2,
.dark-mode .accordion-body h3,
.dark-mode .accordion-body h4,
.dark-mode .accordion-body h5,
.dark-mode .accordion-body h6,
.dark-mode .accordion-body a{
    color:#fff !important;
}

/* Tables */
.dark-mode .accordion-body table{
    background:#1e293b;
}

.dark-mode .accordion-body table td{
    background:#1e293b;
    color:#fff !important;
    border-color:#475569;
}

.dark-mode .accordion-body table th{
    background:#334155;
    color:#fff !important;
    border-color:#475569;
}

/* Table responsive */
.dark-mode .table-responsive{
    background:#1e293b;
}

/* Address cards */
.dark-mode .accordion-body .card{
    background:#334155;
    color:#fff;
}

.dark-mode .accordion-body .card-body{
    background:#334155;
    color:#fff;
}

/* Horizontal line */
.dark-mode hr{
    border-color:#475569;
}
/* =======================
RESPONSIVE
======================= */

@media(max-width:991px){

.sidebar{
    transform:translateX(-100%);
}

.sidebar.active{
    transform:translateX(0);
}

.main{
    margin-left:0;
    width:100%;
}

.page-content{
    padding:20px;
}

.profile-photo{
    width:120px;
    height:120px;
}

.profile-photo-box{
    margin-bottom:25px;
}

.section-title{
    text-align:center;
}

.d-grid{
    margin-top:20px;
}

}
@media(max-width:768px){

.topbar{
    padding:15px;
}

.page-content{
    padding:15px;
}

.profile-tabs .nav{
    flex-direction:column;
    gap:10px;
}

.profile-tabs .nav-link{
    width:100%;
    text-align:center;
}

.table{
    font-size:14px;
}

.accordion-body{
    padding:15px;
}

}
@media(max-width:576px){

.profile-photo{
    width:100px;
    height:100px;
}

.topbar h4{
    font-size:20px;
}

}

/* ==========================
   ACCORDION ANIMATION
========================== */

.accordion-button{
    transition:all .3s ease;
}

.accordion-item{
    transition:all .3s ease;
}

.accordion-item:hover{
    transform:translateY(-2px);
}

/*=================================
MODERN ACCORDION
=================================*/

.modern-accordion{

border:none;
border-radius:18px;
overflow:hidden;
margin-bottom:18px;
box-shadow:0 5px 15px rgba(0,0,0,.08);
transition:.3s;

}

.modern-accordion:hover{

transform:translateY(-2px);

}

.modern-accordion .accordion-button{

background:#fff;
padding:20px 25px;
font-weight:600;
font-size:16px;
box-shadow:none;

}

.modern-accordion .accordion-button:not(.collapsed){

background:#f8fbff;

color:#2563eb;

}

.modern-accordion .accordion-button::after{

transform:scale(.9);

}

.accordion-title{

display:flex;
align-items:center;
gap:15px;

}

.accordion-icon{

width:42px;
height:42px;
border-radius:12px;
background:#eff6ff;
display:flex;
align-items:center;
justify-content:center;
color:#2563eb;
font-size:18px;

}

.modern-accordion .accordion-body{

padding:25px;
background:#fff;

}

</style>

</head>
<body id="body">

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
<i class="fas fa-chart-line"></i>
Dashboard
</a>
</li>

<li>

<a
data-bs-toggle="collapse"
href="#personnelMenu">

<i class="fas fa-users"></i>
Personnel Management
<i class="fas fa-chevron-down float-end mt-1"></i>

</a>

<div class="collapse show" id="personnelMenu">

<ul class="list-unstyled ms-4 mt-2">

<li>
<a href="personnel.php">
<i class="fas fa-id-card"></i>
Personnel Profiles
</a>
</li>

<li>
<a href="personnel.php?tab=service">
    <i class="fas fa-briefcase"></i>
    Service Records
</a>
</li>

</ul>

</div>

</li>

<li>
<a href="contracts.php">
<i class="fas fa-file-signature"></i>
Contracts
</a>
</li>

<li>
<a href="documents.php">
<i class="fas fa-folder-open"></i>
Documents
</a>
</li>

<li>
<a href="reports.php">
<i class="fas fa-chart-bar"></i>
Reports
</a>
</li>

<li>
<a href="users.php">
<i class="fas fa-user-cog"></i>
User Management
</a>
</li>

<li>
<a href="settings.php">
<i class="fas fa-cogs"></i>
Settings
</a>
</li>

<li>
<a href="logout.php">
<i class="fas fa-sign-out-alt"></i>
Logout
</a>
</li>

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

</div>

<div>

<button
id="themeToggle"
class="btn btn-link">

<i
id="themeIcon"
class="fas fa-moon">
</i>

</button>

</div>

</div>

<div class="page-content">

<!-- PAGE TITLE -->

<div class="d-flex justify-content-between align-items-center flex-wrap mb-4">

    <div>

        <h2 class="fw-bold mb-1">
            Personnel Profile
        </h2>

        <p class="text-muted mb-0">
            View and manage personnel information.
        </p>

    </div>

    <div>

        <button
        class="btn btn-primary rounded-pill px-4"
        data-bs-toggle="modal"
        data-bs-target="#addPersonnelModal">

            <i class="fas fa-user-plus me-2"></i>

            Add Personnel

        </button>

    </div>

</div>

<!-- PROFILE CARD -->

<div class="card border-0 shadow-sm rounded-4 mb-4">

<div class="card-body p-4">

<div class="row align-items-center">

<!-- PROFILE -->

<div class="col-xl-3 col-lg-4 text-center">

<img

src="uploads/profile/<?= !empty($row['profile_photo']) ? $row['profile_photo'] : 'default.png'; ?>"

class="profile-photo mb-3">

<h4 class="fw-bold">

<?= strtoupper(

($row['last_name'] ?? '').", ".

($row['first_name'] ?? '')." ".

($row['middle_name'] ?? '')

); ?>

</h4>

<p class="text-muted mb-3">

<?= $row['position_title'] ?? 'No Position'; ?>

</p>

<span class="badge bg-success px-4 py-2 rounded-pill">

<?= ucfirst($row['employment_status'] ?? 'Unknown'); ?>

</span>

</div>

<!-- BASIC INFO -->

<div class="col-xl-6 col-lg-5 mt-4 mt-lg-0">

    <h5 class="section-title mb-4">
        <i class="fas fa-id-card text-primary me-2"></i>
        Basic Information
    </h5>

    <div class="row g-3">

<div class="col-md-6">
<label class="text-muted small">Employee ID</label>
<div class="fw-semibold"><?= $row['employee_id'] ?? '-' ?></div>
</div>

<div class="col-md-6">
<label class="text-muted small">Gender</label>
<div class="fw-semibold"><?= $row['sex'] ?? '-' ?></div>
</div>

<div class="col-md-6">
<label class="text-muted small">Last Name</label>
<div class="fw-semibold"><?= $row['last_name'] ?? '-' ?></div>
</div>

<div class="col-md-6">
<label class="text-muted small">Date Hired</label>
<div class="fw-semibold"><?= $row['date_hired'] ?? '-' ?></div>
</div>

<div class="col-md-6">
<label class="text-muted small">First Name</label>
<div class="fw-semibold"><?= $row['first_name'] ?? '-' ?></div>
</div>

<div class="col-md-6">
<label class="text-muted small">Status</label>

<?php
$status = strtolower($row['employment_status'] ?? '');

$badge = "secondary";

if($status=="active") $badge="success";
elseif($status=="expired") $badge="danger";
elseif($status=="inactive") $badge="warning";
?>

<div>

<span class="badge bg-<?= $badge ?> px-3 py-2 rounded-pill">

<?= ucfirst($status ?: '-') ?>

</span>

</div>

</div>

<div class="col-md-6">
<label class="text-muted small">Middle Name</label>
<div class="fw-semibold"><?= $row['middle_name'] ?? '-' ?></div>
</div>

<div class="col-md-6">
<label class="text-muted small">Office</label>
<div class="fw-semibold"><?= $row['office_assignment'] ?? '-' ?></div>
</div>

<div class="col-md-6">
<label class="text-muted small">Birth Date</label>
<div class="fw-semibold"><?= $row['birth_date'] ?? '-' ?></div>
</div>

<div class="col-md-6">
<label class="text-muted small">Province</label>
<div class="fw-semibold"><?= $row['province'] ?? '-' ?></div>
</div>

<div class="col-md-6">
<label class="text-muted small">Position Title</label>
<div class="fw-semibold"><?= $row['position_title'] ?? '-' ?></div>
</div>

<div class="col-md-6">
<label class="text-muted small">Employment Category</label>
<div class="fw-semibold"><?= $row['employment_category'] ?? '-' ?></div>
</div>

<div class="col-md-6">
<label class="text-muted small">Contract Start</label>
<div class="fw-semibold"><?= $row['contract_start'] ?? '-' ?></div>
</div>

<div class="col-md-6">
<label class="text-muted small">Contract End</label>
<div class="fw-semibold"><?= $row['contract_end'] ?? '-' ?></div>
</div>

<div class="col-md-6">
<label class="text-muted small">Supervisor</label>
<div class="fw-semibold"><?= $row['supervisor'] ?? '-' ?></div>
</div>

<div class="col-md-6">
<label class="text-muted small">Daily Rate</label>
<div class="fw-semibold"><?= $row['daily_rate'] ?? '-' ?></div>
</div>

<div class="col-md-6">
<label class="text-muted small">Monthly Rate</label>
<div class="fw-semibold"><?= $row['monthly_rate'] ?? '-' ?></div>
</div>
        
</div>

</div>


<!-- ACTIONS -->

<div class="col-xl-3 col-lg-3 mt-4 mt-lg-0">

<div class="d-grid gap-3">

<a
href="edit_personnel.php?id=<?= $id ?>"
class="btn btn-primary rounded-pill">

<i class="fas fa-pen me-2"></i>

Edit Information

</a>

<a
href="upload_photo.php?id=<?= $id ?>"
class="btn btn-outline-primary rounded-pill">

<i class="fas fa-camera me-2"></i>

Upload Photo

</a>

<a
href="send_password.php?id=<?= $id ?>"
class="btn btn-outline-secondary rounded-pill">

<i class="fas fa-paper-plane me-2"></i>

Send Password

</a>

</div>

</div>

</div>

</div>

</div>


<div class="mt-4"></div>

<div class="profile-tabs mb-4">

    <ul class="nav nav-pills" id="personnelTabs" role="tablist">

        <li class="nav-item me-2">

            <button
                class="nav-link active"
                id="pds-tab"
                data-bs-toggle="pill"
                data-bs-target="#pds"
                type="button">

                <i class="fas fa-id-card me-2"></i>
                Personal Data Sheet

            </button>

        </li>

        <li class="nav-item">

            <button
                class="nav-link"
                id="service-tab"
                data-bs-toggle="pill"
                data-bs-target="#service"
                type="button">

                <i class="fas fa-briefcase me-2"></i>
                Service Record

            </button>

        </li>

    </ul>

</div>
<!-- =========================
     PDS TAB
========================= -->
<div class="tab-content mt-4">

<div class="tab-pane fade show active"
id="pds">

<div class="accordion" id="pdsAccordion">

<div class="accordion-item shadow-sm mb-3">
<h2 class="accordion-header">
<button
class="accordion-button collapsed"
type="button"
data-bs-toggle="collapse"
aria-expanded="false"
data-bs-target="#personal">
<i class="fas fa-user-circle me-2"></i>
Personal Information
</button>
</h2>

<div
id="personal"
class="accordion-collapse collapse"
data-bs-parent="#pdsAccordion">

<div class="accordion-body">

<div class="row">

<div class="col-md-6">

<div class="table-responsive">

<table class="table table-bordered table-hover">

<tr>
<th width="40%">Place of Birth</th>
<td><?= $row['place_of_birth'] ?? '' ?></td>
</tr>

<tr>
<th>Civil Status</th>
<td><?= $row['civil_status'] ?? '' ?></td>
</tr>

<tr>
<th>Height (m)</th>
<td><?= $row['height'] ?? '' ?></td>
</tr>

<tr>
<th>Weight (kg)</th>
<td><?= $row['weight'] ?? '' ?></td>
</tr>

<tr>
<th>Blood Type</th>
<td><?= $row['blood_type'] ?? '' ?></td>
</tr>

<tr>
<th>Citizenship</th>
<td><?= $row['citizenship'] ?? '' ?></td>
</tr>

<tr>
<th>UMID ID No.</th>
<td><?= $row['umid_no'] ?? '' ?></td>
</tr>

<tr>
<th>PAG-IBIG ID No.</th>
<td><?= $row['pagibig'] ?? '' ?></td>
</tr>

</table>
</div>

</div>

<div class="col-md-6">

<div class="table-responsive">

<table class="table table-bordered table-hover">

<tr>
<th width="40%">PhilHealth No.</th>
<td><?= $row['philhealth'] ?? '' ?></td>
</tr>

<tr>
<th>PhilSys Number (PSN)</th>
<td><?= $row['psn'] ?? '' ?></td>
</tr>

<tr>
<th>TIN No.</th>
<td><?= $row['tin'] ?? '' ?></td>
</tr>

<tr>
<th>Agency Employee No.</th>
<td><?= $row['agency_employee_no'] ?? '' ?></td>
</tr>

<tr>
<th>Telephone No.</th>
<td><?= $row['telephone_no'] ?? '' ?></td>
</tr>

<tr>
<th>Contact No.</th>
<td><?= $row['contact_no'] ?? '' ?></td>
</tr>

<tr>
<th>Email Address</th>
<td><?= $row['email'] ?? '' ?></td>
</tr>

</table>
</div>

</div>

</div>

<hr>

<div class="row">

    <div class="col-md-6">

        <label class="fw-bold mb-2">
            Residential Address
        </label>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body">

                <?= nl2br($row['residential_address'] ?? '') ?>

            </div>
        </div>

    </div>

    <div class="col-md-6">

        <label class="fw-bold mb-2">
            Permanent Address
        </label>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body">

                <?= nl2br($row['permanent_address'] ?? '') ?>

            </div>
        </div>

    </div>

</div>
</div>
</div>
</div>
</div>

<div class="accordion-item shadow-sm mb-3">

    <h2 class="accordion-header">

        <button
            class="accordion-button collapsed"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#family"
            aria-expanded="false"
            aria-controls="family">

            <i class="fas fa-people-roof me-2"></i>
            Family Background

        </button>

    </h2>


    <div
        id="family"
        class="accordion-collapse collapse"
        data-bs-parent="#pdsAccordion">

        <div class="accordion-body">


            <!-- ==========================
                 ACTION BUTTONS
            =========================== -->

            <div class="d-flex flex-wrap gap-2 mb-3">

                <a
                    href="add_family.php?id=<?= (int)$id ?>"
                    class="btn btn-primary rounded-pill px-4">

                    <i class="fas fa-plus me-1"></i>
                    Add Family Background

                </a>


                <a
                    href="edit_family.php?id=<?= (int)$id ?>"
                    class="btn btn-warning rounded-pill px-4">

                    <i class="fas fa-edit me-1"></i>
                    Edit Family Background

                </a>


                <a
                    href="delete_family.php?id=<?= (int)$id ?>"
                    class="btn btn-danger rounded-pill px-4"
                    onclick="return confirm('Delete all family background?')">

                    <i class="fas fa-trash me-1"></i>
                    Delete Family Background

                </a>

            </div>


            <!-- ==========================
                 FAMILY QUERY
            =========================== -->

            <?php

            $family = $conn->query("

                SELECT
                    id,
                    personnel_id,
                    relationship,
                    last_name,
                    first_name,
                    middle_name,
                    suffix,
                    occupation,
                    employer,
                    business_address,
                    telephone,
                    birth_date

                FROM personnel_family

                WHERE personnel_id = '" . (int)$id . "'

                ORDER BY

                    CASE relationship

                        WHEN 'Spouse' THEN 1
                        WHEN 'Father' THEN 2
                        WHEN 'Mother' THEN 3
                        WHEN 'Child' THEN 4
                        ELSE 5

                    END,

                    birth_date ASC

            ");

            ?>


            <!-- ==========================
                 FAMILY TABLE
            =========================== -->

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle mb-0">

                    <thead class="table-primary">

                        <tr>

                            <th>Relationship</th>

                            <th>Full Name</th>

                            <th>Occupation</th>

                            <th>Employer / Business</th>

                            <th>Business Address</th>

                            <th>Telephone</th>

                            <th>Birth Date</th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php if($family && $family->num_rows > 0): ?>

                        <?php while($fam = $family->fetch_assoc()): ?>


                            <?php

                            /* ==========================
                               RELATIONSHIP
                            =========================== */

                            switch($fam['relationship']){

                                case "Spouse":
                                    $relationship = "Spouse";
                                    break;

                                case "Father":
                                    $relationship = "Father";
                                    break;

                                case "Mother":
                                    $relationship = "Mother's Maiden Name";
                                    break;

                                case "Child":
                                    $relationship = "Child";
                                    break;

                                default:
                                    $relationship = $fam['relationship'];
                            }


                            /* ==========================
                               FULL NAME
                            =========================== */

                            $nameParts = [];

                            if(!empty(trim($fam['last_name']))){
                                $nameParts[] = trim($fam['last_name']) . ",";
                            }

                            if(!empty(trim($fam['first_name']))){
                                $nameParts[] = trim($fam['first_name']);
                            }

                            if(!empty(trim($fam['middle_name']))){
                                $nameParts[] = trim($fam['middle_name']);
                            }

                            if(!empty(trim($fam['suffix']))){
                                $nameParts[] = trim($fam['suffix']);
                            }


                            $fullName = trim(
                                implode(" ", $nameParts)
                            );


                            if($fullName == ""){
                                $fullName = "N/A";
                            }


                            /* ==========================
                               OTHER INFORMATION
                            =========================== */

                            $occupation = trim($fam['occupation'] ?? '');

                            $employer = trim($fam['employer'] ?? '');

                            $businessAddress = trim(
                                $fam['business_address'] ?? ''
                            );

                            $telephone = trim(
                                $fam['telephone'] ?? ''
                            );


                            /* ==========================
                               BIRTH DATE
                            =========================== */

                            if(
                                $fam['relationship'] == "Child" &&
                                !empty($fam['birth_date'])
                            ){

                                $birthDate = date(
                                    "F d, Y",
                                    strtotime($fam['birth_date'])
                                );

                            } else {

                                $birthDate = "—";

                            }

                            ?>


                            <tr>

                                <!-- RELATIONSHIP -->

                                <td>

                                    <?= htmlspecialchars(
                                        $relationship
                                    ) ?>

                                </td>


                                <!-- FULL NAME -->

                                <td>

                                    <?= htmlspecialchars(
                                        $fullName
                                    ) ?>

                                </td>


                                <!-- OCCUPATION -->

                                <td>

                                    <?= $occupation !== ''
                                        ? htmlspecialchars($occupation)
                                        : '<span class="text-muted">N/A</span>'
                                    ?>

                                </td>


                                <!-- EMPLOYER -->

                                <td>

                                    <?= $employer !== ''
                                        ? htmlspecialchars($employer)
                                        : '<span class="text-muted">N/A</span>'
                                    ?>

                                </td>


                                <!-- BUSINESS ADDRESS -->

                                <td>

                                    <?= $businessAddress !== ''
                                        ? htmlspecialchars($businessAddress)
                                        : '<span class="text-muted">N/A</span>'
                                    ?>

                                </td>


                                <!-- TELEPHONE -->

                                <td>

                                    <?= $telephone !== ''
                                        ? htmlspecialchars($telephone)
                                        : '<span class="text-muted">N/A</span>'
                                    ?>

                                </td>


                                <!-- BIRTH DATE -->

                                <td>

                                    <?= $birthDate ?>

                                </td>

                            </tr>


                        <?php endwhile; ?>


                    <?php else: ?>

                        <tr>

                            <td
                                colspan="7"
                                class="text-center text-muted py-4">

                                <i class="fas fa-users fa-2x mb-2"></i>

                                <br>

                                No family background information available.

                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>


        </div>

    </div>

</div>

<div class="accordion-item shadow-sm mb-3">
<h2 class="accordion-header">
<button
class="accordion-button collapsed"
type="button"
data-bs-toggle="collapse"
data-bs-target="#education"
aria-expanded="false"
aria-controls="education">
<i class="fas fa-graduation-cap me-2"></i>
Educational Background
</button>
</h2>

<div
id="education"
class="accordion-collapse collapse"
data-bs-parent="#pdsAccordion">

<div class="accordion-body">
<a
href="add_education.php?id=<?= $id ?>"
class="btn btn-primary rounded-pill px-4 mb-3">
<i class="fas fa-plus"></i>
Add Education
</a>

</a>

<a href="edit_education.php?id=<?= $id ?>"
class="btn btn-primary rounded-pill px-4 mb-3">
<i class="fas fa-edit"></i>

Edit Education

</a>

<a href="delete_education.php?id=<?= $id ?>"
class="btn btn-primary rounded-pill px-4 mb-3"
onclick="return confirm('Delete all educational background?')">

<i class="fas fa-trash"></i>

Delete Education

</a>

<div class="table-responsive">

<table class="table table-bordered table-hover">

<thead class="table-primary">

<tr>

<th>Level</th>
<th>School</th>
<th>Degree</th>
<th>From</th>
<th>To</th>
<th>Graduated</th>
<th width="120">Action</th>

</tr>

</thead>

<tbody>

<?php

$education = $conn->query("
SELECT *
FROM personnel_education
WHERE personnel_id='$id'
ORDER BY id ASC
");

while($edu = $education->fetch_assoc()){

?>

<tr>

<td><?= htmlspecialchars($edu['level']) ?></td>

<td><?= htmlspecialchars($edu['school_name']) ?></td>

<td><?= htmlspecialchars($edu['degree']) ?></td>

<td><?= $edu['period_from'] ?></td>

<td><?= $edu['period_to'] ?></td>

<td><?= $edu['year_graduated'] ?></td>


</tr>

<?php } ?>

</tbody>

</table>
</div>

</div>
</div>
</div>

<div class="accordion-item shadow-sm mb-3">

<h2 class="accordion-header">

<button
class="accordion-button collapsed"
type="button"
data-bs-toggle="collapse"
data-bs-target="#eligibility"
aria-expanded="false">

<i class="fas fa-award me-2"></i>

Eligibility

</button>

</h2>

<div
id="eligibility"
class="accordion-collapse collapse"
data-bs-parent="#pdsAccordion">

<div class="accordion-body">

<a
href="add_eligibility.php?id=<?= $id ?>"
class="btn btn-primary rounded-pill px-4 mb-3">

<i class="fas fa-edit"></i>

Add Eligibility

</a>

</a>

<a href="edit_eligibility.php?id=<?= $id ?>"
class="btn btn-primary rounded-pill px-4 mb-3">
<i class="fas fa-edit"></i>

Edit Eligibility

</a>

<a href="delete_eligibility.php?id=<?= $id ?>"
class="btn btn-primary rounded-pill px-4 mb-3"
onclick="return confirm('Delete all work experience?')">

<i class="fas fa-trash"></i>

Delete Eligibility

</a>

<table class="table table-bordered table-hover">

<thead class="table-primary">

<tr>

<th width="25%">Eligibility</th>
<th width="10%">Rating</th>
<th width="15%">Exam Date</th>
<th width="20%">Exam Place</th>
<th width="15%">License No.</th>
<th width="15%">Valid Until</th>

</tr>

</thead>

<tbody>

<?php

$getEligibility = $conn->query("
SELECT *
FROM personnel_eligibility
WHERE personnel_id='$id'
ORDER BY id ASC
");

if($getEligibility->num_rows>0){

while($elig = $getEligibility->fetch_assoc()){

?>

<tr>

<td><?= $elig['eligibility'] ?></td>

<td><?= $elig['rating'] ?></td>

<td><?= $elig['exam_date'] ?></td>

<td><?= $elig['exam_place'] ?></td>

<td><?= $elig['license_number'] ?></td>

<td><?= $elig['valid_until'] ?></td>

</tr>

<?php

}

}else{

?>

<tr>

<td colspan="6" class="text-center text-muted">

No eligibility record found.

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

</div>

<div class="accordion-item shadow-sm mb-3">

<h2 class="accordion-header">

<button
class="accordion-button collapsed"
type="button"
data-bs-toggle="collapse"
data-bs-target="#work"
aria-expanded="false"
aria-controls="work">

<i class="fas fa-briefcase me-2"></i>

Work Experience

</button>

</h2>

<div
id="work"
class="accordion-collapse collapse"
data-bs-parent="#pdsAccordion">

<div class="accordion-body">


<a
href="add_work.php?id=<?= $id ?>"
class="btn btn-primary rounded-pill px-4 mb-3">

<i class="fas fa-plus"></i>

Add Work Experience

</a>

</a>

<a href="edit_work.php?id=<?= $id ?>"
class="btn btn-primary rounded-pill px-4 mb-3">
<i class="fas fa-edit"></i>

Edit Work Experience

</a>

<a href="delete_work.php?id=<?= $id ?>"
class="btn btn-primary rounded-pill px-4 mb-3"
onclick="return confirm('Delete all work experience?')">

<i class="fas fa-trash"></i>

Delete Work Experience

</a>

<div class="table-responsive">

<table class="table table-bordered table-hover">

<thead class="table-primary">

<tr>

<th>Inclusive Dates</th>
<th>Position Title</th>
<th>Department / Agency / Office / Company</th>
<th>Monthly Salary</th>
<th>Salary Grade / Step</th>
<th>Status of Appointment</th>
<th>Government Service</th>


</tr>


</thead>

<tbody>

<?php

$getWork = $conn->query("
SELECT *
FROM personnel_work_experience
WHERE personnel_id='$id'
ORDER BY date_from DESC
");

if($getWork->num_rows > 0){

while($work = $getWork->fetch_assoc()){

?>

<tr>

<td>

<?= $work['date_from'] ?>

<br>

<small class="text-muted">
to
</small>

<br>

<?= $work['date_to'] ?>

</td>

<td>

<?= htmlspecialchars($work['position_title']) ?>

</td>

<td>

<?= htmlspecialchars($work['department']) ?>

</td>

<td>

₱<?= number_format($work['monthly_salary'],2) ?>

</td>

<td>

<?= htmlspecialchars($work['salary_grade']) ?>

</td>

<td>

<?= htmlspecialchars($work['status_of_appointment']) ?>

</td>

<td>

<?= htmlspecialchars($work['government_service']) ?>

</td>

</tr>

<?php

}

}else{

?>

<tr>

<td colspan="8" class="text-center text-muted">

No work experience found.

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

</div>

</div>

<div class="accordion-item shadow-sm mb-3">
<h2 class="accordion-header">
<button
class="accordion-button collapsed"
type="button"
data-bs-toggle="collapse"
data-bs-target="#voluntary"
aria-expanded="false"
aria-controls="voluntary">
<i class="fas fa-handshake-angle me-2"></i>
Voluntary Work
</button>
</h2>

<div
id="voluntary"
class="accordion-collapse collapse"
data-bs-parent="#pdsAccordion">

<div class="accordion-body">
<a
href="add_voluntary.php?id=<?= $id ?>"
class="btn btn-primary rounded-pill px-4 mb-3">
<i class="fas fa-plus"></i>
Add Voluntary Work
</a>


<a href="edit_voluntary.php?id=<?= $id ?>"
class="btn btn-primary rounded-pill px-4 mb-3">
<i class="fas fa-edit"></i>

Edit Voluntary Work

</a>

<a href="delete_voluntary.php?id=<?= $id ?>"
class="btn btn-primary rounded-pill px-4 mb-3"
onclick="return confirm('Delete all voluntary work?')">

<i class="fas fa-trash"></i>

Delete Voluntary Work

</a>

<div class="table-responsive">

<table class="table table-bordered table-hover">

<thead class="table-light">
<tr>
    <th>Name of Organization</th>
    <th>Address</th>
    <th>Inclusive Dates</th>
    <th>Number of Hours</th>
    <th>Position/Nature of Work</th>
</tr>
</thead>

<tbody>

<?php
$voluntary = $conn->query("
SELECT *
FROM personnel_voluntary_work
WHERE personnel_id='$id'
");

if($voluntary->num_rows > 0){
    while($v = $voluntary->fetch_assoc()){
?>
<tr>

<td><?= htmlspecialchars($v['organization_name']) ?></td>

<td><?= htmlspecialchars($v['organization_address']) ?></td>

<td>

<?= $v['date_from'] ?>

<br>

<small class="text-muted">to</small>

<br>

<?= $v['date_to'] ?>

</td>

<td><?= $v['hours'] ?></td>

<td><?= htmlspecialchars($v['position']) ?></td>

</tr>
<?php
    }
}else{
?>
<tr>
<td colspan="8" class="text-center text-muted">

No voluntary work records found.
</td>
</tr>
<?php } ?>

</tbody>

</table>
</div>

</div>
</div>
</div>

<div class="accordion-item shadow-sm mb-3">
<h2 class="accordion-header">
<button
class="accordion-button collapsed"
type="button"
data-bs-toggle="collapse"
data-bs-target="#training"
aria-expanded="false"
aria-controls="training">
<i class="fas fa-book-open-reader me-2"></i>
Learning & Development and Other Information
</button>
</h2>

<div
id="training"
class="accordion-collapse collapse"
data-bs-parent="#pdsAccordion">

<div class="accordion-body">
<a
href="add_training.php?id=<?= $row['id'] ?? '' ?>"
class="btn btn-primary rounded-pill px-4 mb-3">
<i class="fas fa-plus"></i>
Add Learning & Development
</a>

<a href="edit_training.php?id=<?= $id ?>"
class="btn btn-primary rounded-pill px-4 mb-3">
<i class="fas fa-edit"></i>

Edit Learning & Development

</a>

<a href="delete_training.php?id=<?= $id ?>"
class="btn btn-primary rounded-pill px-4 mb-3"
onclick="return confirm('Delete all learning & development?')">

<i class="fas fa-trash"></i>

Delete Learning & Development

</a>

<table class="table">

<tr>
<th width="28%">
Title of Learning & Development
</th>

<th width="18%">
Inclusive Dates
</th>

<th width="10%">
Hours
</th>

<th width="18%">
Type of L&D
</th>

<th width="18%">
Conducted / Sponsored By
</th>

</tr>

<?php

$getTraining = $conn->query("
SELECT *
FROM personnel_learning_development
WHERE personnel_id='$id'
");

if($getTraining->num_rows > 0){

    while($t = $getTraining->fetch_assoc()){
?>
<tr>
<td>

<?= htmlspecialchars($t['training_title']) ?>

</td>

<td>

<?= $t['date_from'] ?>

<br>

<small class="text-muted">


</small>

<br>

<?= $t['date_to'] ?>

</td>

<td>

<?= $t['hours'] ?>

</td>

<td>

<?= htmlspecialchars($t['training_type']) ?>

</td>

<td>

<?= htmlspecialchars($t['conducted_by']) ?>

</td>
</tr>
<?php
    }

}else{
?>

<tr>
    <td colspan="5" class="text-center text-danger">
        No Learning & Development record found.
    </td>
</tr>

<?php
}
?>
</table>
<hr class="my-5">

<h4 class="text-primary">

<i class="fas fa-circle-info me-2"></i>

Other Information

</h4>

<div class="mt-3">
<a
href="add_other.php?id=<?= $id ?>"
class="btn btn-primary rounded-pill px-4 mb-3">
<i class="fas fa-plus"></i>
Add Other Information
</a>

<a href="edit_other.php?id=<?= $id ?>"
class="btn btn-primary rounded-pill px-4 mb-3">
<i class="fas fa-edit"></i>

Edit Other Information

</a>

<a href="delete_other.php?id=<?= $id ?>"
class="btn btn-primary rounded-pill px-4 mb-3"
onclick="return confirm('Delete all educational background?')">

<i class="fas fa-trash"></i>

Delete Other Information

</a>

<div class="table-responsive">

<table class="table table-bordered table-hover">

<thead class="table-primary">

<tr>

<th width="30%">Special Skills / Hobbies</th>

<th width="30%">Non-Academic Distinction</th>

<th width="25%">Membership in Association</th>

</tr>

</thead>

<tbody>

<?php

$getOther = $conn->query("
SELECT *
FROM personnel_other_information
WHERE personnel_id='$id'
ORDER BY id DESC
");

if($getOther->num_rows>0){

while($other=$getOther->fetch_assoc()){

?>

<tr>

<td><?= nl2br(htmlspecialchars($other['skills_hobbies'])) ?></td>

<td><?= nl2br(htmlspecialchars($other['non_academic'])) ?></td>

<td><?= nl2br(htmlspecialchars($other['membership'])) ?></td>

</tr>

<?php

}

}else{

?>

<tr>

<td colspan="3" class="text-center text-muted">

No other information found.

</td>

</tr>

<?php } ?>

</tbody>

</table>


</div>
</div>
</div>
</div>
</div>

</div> <!-- pdsAccordion -->
</div> <!-- tab-pane #pds -->

<!-- =========================
     SERVICE RECORD TAB
========================= -->

<div class="tab-pane fade" id="service">

<?php include "service_record.php"; ?>

</div>

</div> <!-- tab-content -->
<!-- ADD PERSONNEL MODAL -->

<div class="modal fade" id="addPersonnelModal" tabindex="-1" data-bs-backdrop="static">
<div class="modal-dialog modal-xl modal-dialog-centered">
<div class="modal-content">

<div class="modal-header bg-primary text-white">
<h5 class="modal-title">
<i class="fas fa-user-plus"></i>
Add Personnel
</h5>

<button type="button"
class="btn-close btn-close-white"
data-bs-dismiss="modal">
</button>
</div>

<form action="" method="POST">

<div class="modal-body">

<h5 class="border-bottom pb-2 text-primary">
Basic Information
</h5>

<div class="row">

<div class="col-md-3 mb-3">
<label>Employee ID</label>
<input
type="text"
name="employee_id"
class="form-control"
value="<?= $employee_id ?>"
readonly>
</div>

<div class="col-md-3 mb-3">
<label>Last Name</label>
<input
type="text"
name="last_name"
class="form-control"
required>
</div>

<div class="col-md-3 mb-3">
<label>First Name</label>
<input
type="text"
name="first_name"
class="form-control"
required>
</div>

<div class="col-md-3 mb-3">
<label>Middle Name</label>
<input
type="text"
name="middle_name"
class="form-control">
</div>

<div class="col-md-3 mb-3">
<label>Date of Birth</label>
<input
type="date"
name="birth_date"
class="form-control"
required>
</div>

<div class="col-md-3 mb-3">
<label>Gender</label>

<select
name="sex"
class="form-select">

<option value="">Select</option>
<option>Male</option>
<option>Female</option>

</select>

</div>

<div class="col-md-3 mb-3">
<label>Date Hired</label>

<input
type="date"
name="date_hired"
class="form-control"
required>
</div>

<div class="col-md-3 mb-3">

<label>Status</label>

<select
name="employment_status"
class="form-select">

<option>Active</option>
<option>Inactive</option>
<option>Resigned</option>
<option>Retired</option>

</select>

</div>

<div class="col-md-6 mb-3">

<label>Office</label>

<input
type="text"
name="office_assignment"
class="form-control"
required>
</div>

<div class="col-md-6 mb-3">

<label>Province</label>

<input
type="text"
name="province"
class="form-control"
required>
</div>

</div>


<!-- PERSONAL INFORMATION -->

<hr>

<h5 class="border-bottom pb-2 text-primary">
Personal Information
</h5>

<div class="row">

<div class="col-md-4 mb-3">
<label>Place of Birth</label>
<input
type="text"
name="place_of_birth"
class="form-control">
</div>

<div class="col-md-4 mb-3">
<label>Civil Status</label>

<select
name="civil_status"
class="form-select">

<option>Single</option>
<option>Married</option>
<option>Widowed</option>
<option>Separated</option>

</select>

</div>

<div class="col-md-2 mb-3">
<label>Height (m)</label>

<input
type="number"
step=".01"
name="height"
class="form-control">

</div>

<div class="col-md-2 mb-3">
<label>Weight (kg)</label>

<input
type="number"
step=".01"
name="weight"
class="form-control">

</div>

<div class="col-md-3 mb-3">
<label>Blood Type</label>

<input
type="text"
name="blood_type"
class="form-control">

</div>

<div class="col-md-3 mb-3">
<label>UMID ID No.</label>

<input
type="text"
name="umid_no"
class="form-control">

</div>

<div class="col-md-3 mb-3">
<label>PAG-IBIG ID No.</label>

<input
type="text"
name="pagibig"
class="form-control">

</div>

<div class="col-md-3 mb-3">
<label>PhilHealth No.</label>

<input
type="text"
name="philhealth"
class="form-control">

</div>

<div class="col-md-4 mb-3">
<label>PhilSys Number (PSN)</label>

<input
type="text"
name="psn"
class="form-control">

</div>

<div class="col-md-4 mb-3">
<label>TIN No.</label>

<input
type="text"
name="tin"
class="form-control">

</div>

<div class="col-md-4 mb-3">
<label>Agency Employee No.</label>

<input
type="text"
name="agency_employee_no"
class="form-control">

</div>

<div class="col-md-6 mb-3">
<label>Citizenship</label>

<input
type="text"
name="citizenship"
class="form-control">

</div>

<div class="col-md-6 mb-3">
<label>Telephone No.</label>

<input
type="text"
name="telephone_no"
class="form-control">

</div>

<div class="col-md-6 mb-3">
<label>Mobile No.</label>

<input
type="text"
name="contact_number"
class="form-control">

</div>

<div class="col-md-6 mb-3">
<label>Email Address</label>

<input
type="email"
name="email"
class="form-control">

</div>

<div class="col-md-6 mb-3">
<label>Residential Address</label>

<textarea
name="residential_address"
class="form-control"
rows="3"></textarea>

</div>

<div class="col-md-6 mb-3">
<label>Permanent Address</label>

<textarea
name="permanent_address"
class="form-control"
rows="3"></textarea>

</div>

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
name="save_personnel"
class="btn btn-primary">

<i class="fas fa-save"></i>
Save Personnel

</button>

</div>

</form>

</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="theme.js"></script>

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
const personnelMenu =
document.getElementById('personnelMenu');

const arrow =
document.querySelector(
'a[href="#personnelMenu"] .fa-chevron-down'
);

personnelMenu.addEventListener(
'show.bs.collapse',
() => {
    arrow.classList.add('rotate');
});

personnelMenu.addEventListener(
'hide.bs.collapse',
() => {
    arrow.classList.remove('rotate');
});
</script>

<script>
const dual = document.getElementById('dual');
const filipino = document.getElementById('filipino');
const dualSection = document.getElementById('dualSection');

dual.addEventListener('change', function(){
    dualSection.style.display = 'block';
});

filipino.addEventListener('change', function(){
    dualSection.style.display = 'none';
});
</script>

<script>

document.getElementById("searchService").addEventListener("keyup",function(){

let value=this.value.toLowerCase();

let rows=document.querySelectorAll("#serviceTable tbody tr");

rows.forEach(function(row){

row.style.display=row.innerText.toLowerCase().includes(value)
?"":"none";

});

});

</script>
<script>

document.addEventListener("DOMContentLoaded",function(){

const url = new URL(window.location);

const tab = url.searchParams.get("tab");

if(tab=="service"){

const trigger =
document.getElementById("service-tab");

bootstrap.Tab.getOrCreateInstance(trigger).show();

}

});

</script>
</body>
</html>
