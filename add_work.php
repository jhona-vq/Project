<?php
include "auth.php";
include "config.php";

$personnel_id = $_GET['id'] ?? '';

if(isset($_POST['save_work'])){

    $date_from = $_POST['date_from'];
    $date_to = $_POST['date_to'];
    $position_title = trim($_POST['position_title']);
    $department = trim($_POST['department']);
    $monthly_salary = $_POST['monthly_salary'];
    $salary_grade = trim($_POST['salary_grade']);
    $status_of_appointment = $_POST['status_of_appointment'];
    $government_service = $_POST['government_service'];

    $stmt = $conn->prepare("
    INSERT INTO personnel_work_experience
    (
        personnel_id,
        date_from,
        date_to,
        position_title,
        department,
        monthly_salary,
        salary_grade,
        status_of_appointment,
        government_service
    )
    VALUES(?,?,?,?,?,?,?,?,?)
    ");

    $stmt->bind_param(
        "issssdsss",
        $personnel_id,
        $date_from,
        $date_to,
        $position_title,
        $department,
        $monthly_salary,
        $salary_grade,
        $status_of_appointment,
        $government_service
    );

    $stmt->execute();

    echo "
    <script>
    alert('Work Experience Added Successfully.');
    window.location='personnel.php?id=$personnel_id';
    </script>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Add Work Experience</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

<script>
if(localStorage.getItem("theme") === "dark"){
    document.documentElement.classList.add("dark-mode");
}
</script>

<style>

body{
    background:#f5f7fb;
}

.card{
    border:none;
    border-radius:18px;
    box-shadow:0 8px 20px rgba(0,0,0,.08);
}

.card-header{
    font-size:20px;
    font-weight:bold;
}

label{
    font-weight:600;
}
.dark-mode{
    background:#0f172a;
    color:#fff;
}

.dark-mode .card{
    background:#1e293b;
    color:#fff;
}

.dark-mode .card-header{
    background:#1e293b !important;
    color:#fff;
}

.dark-mode .section-title{
    background:#2563eb;
    color:#fff;
}

.dark-mode label{
    color:#fff;
}

.dark-mode .form-control,
.dark-mode .form-select{
    background:#334155;
    color:#fff;
    border:1px solid #475569;
}
</style>

</head>

<body>

<div class="container py-4">

<div class="card">

<div class="card-header bg-primary text-white">

<i class="fas fa-briefcase"></i>

Work Experience

</div>

<div class="card-body">

<form method="POST">

<div class="row">

<div class="col-md-6 mb-3">

<label>Inclusive Date From</label>

<input
type="date"
name="date_from"
class="form-control"
required>

</div>

<div class="col-md-6 mb-3">

<label>Inclusive Date To</label>

<input
type="date"
name="date_to"
class="form-control">

</div>
<div class="col-md-6 mb-3">

<label>Position Title</label>

<input
type="text"
name="position_title"
class="form-control"
required>

</div>

<div class="col-md-6 mb-3">

<label>Department / Agency / Office / Company</label>

<input
type="text"
name="department"
class="form-control"
required>

</div>
<div class="col-md-4 mb-3">

<label>Monthly Salary</label>

<input
type="number"
step="0.01"
name="monthly_salary"
class="form-control">

</div>

<div class="col-md-4 mb-3">

<label>Salary Grade / Step</label>

<input
type="text"
name="salary_grade"
class="form-control">

</div>
<div class="col-md-4 mb-3">

<label>Status of Appointment</label>

<select
name="status_of_appointment"
class="form-select">

<option value="">Select</option>

<option>Permanent</option>
<option>Temporary</option>
<option>Casual</option>
<option>Contractual</option>
<option>Job Order</option>
<option>Contract of Service</option>
<option>Co-Terminus</option>
<option>Elective</option>
<option>Appointed</option>
<option>Others</option>

</select>

</div>
<div class="col-md-6 mb-3">

<label>Government Service</label>

<select
name="government_service"
class="form-select">

<option>Yes</option>

<option>No</option>

</select>

</div>
<div class="text-end">

<a
href="personnel.php?id=<?= $personnel_id ?>"
class="btn btn-secondary">

Back

</a>

<button
type="submit"
name="save_work"
class="btn btn-primary">

<i class="fas fa-save"></i>

Save Work Experience

</button>

</div>

</form>

</div>

</div>

</div>
<script>
document.addEventListener("DOMContentLoaded",function(){

    if(localStorage.getItem("theme")==="dark"){
        document.body.classList.add("dark-mode");
    }

});
</script>

</body>

</html>