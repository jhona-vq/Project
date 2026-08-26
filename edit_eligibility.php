<?php
include "auth.php";
include "config.php";

$personnel_id = $_GET['personnel_id'] ?? '';


$get = $conn->query("
SELECT *
FROM personnel_eligibility
WHERE id='$personnel_id'
");

$row = $get->fetch_assoc();

if(isset($_POST['update'])){

    $stmt = $conn->prepare("
    UPDATE personnel_eligibility
    SET
        eligibility=?,
        rating=?,
        exam_date=?,
        exam_place=?,
        license_number=?,
        valid_until=?
    WHERE id=?
    ");

    $stmt->bind_param(
        "ssssssi",
        $_POST['eligibility'],
        $_POST['rating'],
        $_POST['exam_date'],
        $_POST['exam_place'],
        $_POST['license_number'],
        $_POST['valid_until'],
        $eligibility_id
    );

    $stmt->execute();

    echo "
    <script>
    alert('Eligibility Updated Successfully.');
    window.location='personnel.php?id=$personnel_id';
    </script>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Edit Eligibility</title>

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

<div class="card-header bg-warning">

<i class="fas fa-edit me-2"></i>

Edit Eligibility

</div>

<div class="card-body">

<form method="POST">

<div class="mb-3">

<label>Eligibility</label>

<input
type="text"
name="eligibility"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Rating</label>

<input
type="text"
name="rating"
class="form-control"
required>
</div>

<div class="row">

<div class="col-md-6 mb-3">

<label>Exam Date</label>

<input
type="date"
name="exam_date"
class="form-control"
required>

</div>

<div class="col-md-6 mb-3">

<label>Valid Until</label>

<input
type="date"
name="valid_until"
class="form-control"
required>

</div>

</div>

<div class="mb-3">

<label>Exam Place</label>

<input
type="text"
name="exam_place"
class="form-control"
required>

</div>

<div class="mb-3">

<label>License Number</label>

<input
type="text"
name="license_number"
class="form-control"
required>

</div>

<div class="text-end">

<a
href="personnel.php?id=<?= $personnel_id ?>"
class="btn btn-secondary">

<i class="fas fa-arrow-left"></i>

Back

</a>

<button
type="submit"
name="update"
class="btn btn-primary">

<i class="fas fa-save"></i>

Update

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