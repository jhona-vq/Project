<?php
include "auth.php";
include "config.php";

$personnel_id = $_GET['id'] ?? '';


if(isset($_POST['save_eligibility'])){

    $eligibility    = trim($_POST['eligibility']);
    $rating         = trim($_POST['rating']);
    $exam_date      = $_POST['exam_date'];
    $exam_place     = trim($_POST['exam_place']);
    $license_number = trim($_POST['license_number']);
    $valid_until    = $_POST['valid_until'];

    $stmt = $conn->prepare("
    INSERT INTO personnel_eligibility
    (
        personnel_id,
        eligibility,
        rating,
        exam_date,
        exam_place,
        license_number,
        valid_until
    )
    VALUES(?,?,?,?,?,?,?)
    ");

    $stmt->bind_param(
        "issssss",
        $personnel_id,
        $eligibility,
        $rating,
        $exam_date,
        $exam_place,
        $license_number,
        $valid_until
    );

    if($stmt->execute()){

        echo "
        <script>

        alert('Eligibility Added Successfully.');

        window.location='personnel.php?id=$personnel_id';

        </script>";

        exit;

    }else{

        die($stmt->error);

    }

}
?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<title>Add Eligibility</title>

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
    box-shadow:0 10px 25px rgba(0,0,0,.08);
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

<i class="fas fa-award"></i>

Civil Service Eligibility

</div>

<div class="card-body">

<form method="POST">

<div class="row">

<div class="col-md-12 mb-3">

<label>
Civil Service Eligibility
</label>

<input
type="text"
name="eligibility"
class="form-control"
required>

</div>

<div class="col-md-4 mb-3">

<label>
Rating (If Applicable)
</label>

<input
type="text"
name="rating"
class="form-control">

</div>

<div class="col-md-4 mb-3">

<label>
Date of Examination / Conferment
</label>

<input
type="date"
name="exam_date"
class="form-control">

</div>

<div class="col-md-4 mb-3">

<label>
Valid Until
</label>

<input
type="date"
name="valid_until"
class="form-control">

</div>

<div class="col-md-6 mb-3">

<label>
Place of Examination / Conferment
</label>

<input
type="text"
name="exam_place"
class="form-control">

</div>

<div class="col-md-6 mb-3">

<label>
License Number (If Applicable)
</label>

<input
type="text"
name="license_number"
class="form-control">

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
name="save_eligibility"
class="btn btn-primary">

<i class="fas fa-save"></i>

Save Eligibility

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