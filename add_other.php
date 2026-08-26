<?php
include "auth.php";
include "config.php";

$personnel_id = $_GET['id'] ?? '';

if(isset($_POST['save_other'])){

    $skills_hobbies = $_POST['skills_hobbies'];
    $non_academic = $_POST['non_academic'];
    $membership = $_POST['membership'];

    $stmt = $conn->prepare("
    INSERT INTO personnel_other_information
    (
        personnel_id,
        skills_hobbies,
        non_academic,
        membership
    )
    VALUES(?,?,?,?)
    ");

    $stmt->bind_param(
        "isss",
        $personnel_id,
        $skills_hobbies,
        $non_academic,
        $membership
    );

    $stmt->execute();

    echo "
    <script>

    alert('Other Information Added Successfully.');

    window.location='personnel.php?id=$personnel_id';

    </script>";

    exit;
}
?>

<!DOCTYPE html>
<html>

<head>

<title>Add Other Information</title>

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

<i class="fas fa-circle-info me-2"></i>

Other Information

</div>

<div class="card-body">

<form method="POST">

<div class="mb-3">

<label>Special Skills and Hobbies</label>

<textarea
name="skills_hobbies"
class="form-control"
rows="4"
required></textarea>

</div>

<div class="mb-3">

<label>Non-Academic Distinctions / Recognition</label>

<textarea
name="non_academic"
class="form-control"
rows="4"
required></textarea>

</div>

<div class="mb-3">

<label>Membership in Association / Organization</label>

<textarea
name="membership"
class="form-control"
rows="4"
required></textarea>

</div>

<div class="text-end">

<a
href="personnel.php?id=<?= $personnel_id ?>"
class="btn btn-secondary">

Back

</a>

<button
type="submit"
name="save_other"
class="btn btn-primary">

<i class="fas fa-save"></i>

Save

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