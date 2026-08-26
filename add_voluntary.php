<?php
include "auth.php";
include "config.php";

$personnel_id = $_GET['id'] ?? '';

if(isset($_POST['save_voluntary'])){

    $organization_name = trim($_POST['organization_name']);
    $organization_address = trim($_POST['organization_address']);
    $date_from = $_POST['date_from'];
    $date_to = $_POST['date_to'];
    $hours = $_POST['hours'];
    $position = trim($_POST['position']);

    $stmt = $conn->prepare("
    INSERT INTO personnel_voluntary_work
    (
        personnel_id,
        organization_name,
        organization_address,
        date_from,
        date_to,
        hours,
        position
    )
    VALUES(?,?,?,?,?,?,?)
    ");

    $stmt->bind_param(
        "issssis",
        $personnel_id,
        $organization_name,
        $organization_address,
        $date_from,
        $date_to,
        $hours,
        $position
    );

    $stmt->execute();

    echo "
    <script>
    alert('Voluntary Work Added Successfully.');
    window.location='personnel.php?id=$personnel_id';
    </script>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Add Voluntary Work</title>

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

<i class="fas fa-handshake-angle"></i>

Voluntary Work

</div>

<div class="card-body">

<form method="POST">

<div class="row">

<div class="col-md-12 mb-3">

<label>Name & Address of Organization</label>

<input
type="text"
name="organization_name"
class="form-control"
required>

</div>

<div class="col-md-12 mb-3">

<label>Organization Address</label>

<input
type="text"
name="organization_address"
class="form-control"
required>

</div>

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
class="form-control"
required>

</div>

<div class="col-md-6 mb-3">

<label>Number of Hours</label>

<input
type="number"
name="hours"
class="form-control"
required>

</div>

<div class="col-md-6 mb-3">

<label>Position / Nature of Work</label>

<input
type="text"
name="position"
class="form-control"
required>

</div>

</div>

<div class="text-end">

<a
href="personnel.php?id=<?= $personnel_id ?>"
class="btn btn-secondary">

Back

</a>

<button
type="submit"
name="save_voluntary"
class="btn btn-primary">

<i class="fas fa-save"></i>

Save Voluntary Work

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