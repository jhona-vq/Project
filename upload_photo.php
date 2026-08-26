<?php
include "auth.php";
include "config.php";

$id = $_GET['id'] ?? '';

$get = $conn->query("
SELECT *
FROM personnel
WHERE id='$id'
");

$row = $get->fetch_assoc();

if(isset($_POST['upload'])){

    if($_FILES['photo']['name'] != ''){

        $filename =
        time().'_'.
        basename($_FILES['photo']['name']);

        move_uploaded_file(
            $_FILES['photo']['tmp_name'],
            'uploads/profile/'.$filename
        );

        $conn->query("
        UPDATE personnel
        SET profile_photo='$filename'
        WHERE id='$id'
        ");

        header("Location: personnel.php?id=".$id);
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Upload Profile Photo</title>

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

.upload-card{
    max-width:600px;
    margin:50px auto;
    background:white;
    border-radius:20px;
    padding:40px;
    box-shadow:0 5px 20px rgba(0,0,0,.1);
}

.profile-photo{
    width:180px;
    height:180px;
    border-radius:50%;
    object-fit:cover;
    border:5px solid #e2e8f0;
}

.upload-box{
    border:2px dashed #cbd5e1;
    border-radius:15px;
    padding:30px;
    text-align:center;
    background:#f8fafc;
}

.upload-box:hover{
    border-color:#2563eb;
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

<div class="container">

<div class="upload-card">

<div class="text-center mb-4">

<img
src="uploads/profile/<?=
$row['profile_photo'] ?? 'default.png'
?>"
class="profile-photo mb-3">

<h4>
<?= strtoupper(
($row['last_name'] ?? '') . ", " .
($row['first_name'] ?? '')
) ?>
</h4>

<p class="text-muted">
<?= $row['position_title'] ?? '' ?>
</p>

</div>

<form method="POST" enctype="multipart/form-data">

<div class="upload-box mb-4">

<i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>

<h5>Select Profile Photo</h5>

<input
type="file"
name="photo"
class="form-control mt-3"
accept="image/*"
required>

</div>

<div class="d-grid gap-2">

<button
type="submit"
name="upload"
class="btn btn-primary btn-lg">

<i class="fas fa-upload"></i>
Upload Photo

</button>

<a
href="personnel.php?id=<?= $id ?>"
class="btn btn-secondary">

<i class="fas fa-arrow-left"></i>
Back to Profile

</a>

</div>

</form>

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