<?php
include "auth.php";
include "config.php";

$personnel_id=$_GET['personnel_id'] ?? '';

$get=$conn->query("
SELECT *
FROM personnel_learning_development
WHERE id='$personnel_id'
");

$row=$get->fetch_assoc();

if(isset($_POST['update'])){

$stmt=$conn->prepare("
UPDATE personnel_learning_development
SET
training_title=?,
date_from=?,
date_to=?,
hours=?,
training_type=?,
conducted_by=?
WHERE id=?
");

$stmt->bind_param(
"sssissi",

$_POST['training_title'],
$_POST['date_from'],
$_POST['date_to'],
$_POST['hours'],
$_POST['training_type'],
$_POST['conducted_by'],
$training_id

);

$stmt->execute();

echo "
<script>

alert('Training Updated Successfully.');

window.location='personnel.php?id=".$row['personnel_id']."';

</script>";

exit;

}
?>

<!DOCTYPE html>
<html>

<head>

<title>Add Learning & Development</title>

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

<i class="fas fa-book-open-reader me-2"></i>

Learning & Development

</div>

<div class="card-body">

<form method="POST">

<div class="row">

<div class="col-md-12 mb-3">

<label>Title of Learning & Development</label>

<textarea
name="training_title"
class="form-control"
rows="3"
required></textarea>

</div>

<div class="col-md-6 mb-3">

<label>Inclusive Date From</label>

<input
type="date"
name="date_from"
value="$row['date_from']"
class="form-control"
required>

</div>

<div class="col-md-6 mb-3">

<label>Inclusive Date To</label>

<input
type="date"
name="date_to"
value="$row['date_to']"
class="form-control"
required>

</div>

<div class="col-md-4 mb-3">

<label>Number of Hours</label>

<input
type="number"
name="hours"
value="$row['hours']"
class="form-control"
required>

</div>

<div class="col-md-4 mb-3">

<label>Type of L&D</label>

<select
name="training_type"
class="form-select"
required>

<option value="">Select</option>

<option>Managerial</option>

<option>Supervisory</option>

<option>Technical</option>

<option>Leadership</option>

<option>Executive</option>

<option>Foundational</option>

<option>Others</option>


</select>

</div>

<div class="col-md-4 mb-3">

<label>Conducted / Sponsored By</label>

<input
type="text"
name="conducted_by"
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
name="save_training"
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