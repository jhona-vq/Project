<?php
include "auth.php";
include "config.php";

$personnel_id = $_GET['personnel_id'] ?? '';


/* ==========================
   LOAD EXISTING EDUCATION
========================== */

$education = [];

$getEducation = $conn->query("
SELECT *
FROM personnel_education
WHERE personnel_id='$personnel_id'
ORDER BY id ASC
");

$secondaryCount = 0;

while($row = $getEducation->fetch_assoc()){

    if($row['level']=="Elementary"){

        $education['Elementary'] = $row;

    }

    elseif($row['level']=="Secondary"){

        if($secondaryCount==0){

            $education['Secondary_JHS'] = $row;

        }else{

            $education['Secondary_SHS'] = $row;

        }

        $secondaryCount++;

    }

    elseif($row['level']=="Vocational/Trade Course"){

        $education['Vocational/Trade Course'] = $row;

    }

    elseif($row['level']=="College"){

        $education['College'] = $row;

    }

    elseif($row['level']=="Graduate Studies"){

        $education['Graduate Studies'] = $row;

    }

}

/* ==========================
   UPDATE EDUCATION
========================== */

if(isset($_POST['update_education'])){

    $levels = [

        "Elementary",

        "Secondary_JHS",

        "Secondary_SHS",

        "Vocational/Trade Course",

        "College",

        "Graduate Studies"

    ];

    foreach($levels as $level){

        $school = $_POST['school_name'][$level] ?? '';
        $degree = $_POST['degree'][$level] ?? '';
        $from = $_POST['period_from'][$level] ?? '';
        $to = $_POST['period_to'][$level] ?? '';
        $highest = $_POST['highest_level'][$level] ?? '';
        $graduated = $_POST['year_graduated'][$level] ?? '';
        $scholarship = $_POST['scholarship'][$level] ?? '';

        if(
            $level=="Secondary_JHS" ||
            $level=="Secondary_SHS"
        ){
            $saveLevel="Secondary";
        }else{
            $saveLevel=$level;
        }

        if(isset($education[$level])){

            $stmt=$conn->prepare("
            UPDATE personnel_education
            SET

            school_name=?,
            degree=?,
            period_from=?,
            period_to=?,
            highest_level=?,
            year_graduated=?,
            scholarship=?

            WHERE id=?
            ");

            $stmt->bind_param(

            "sssssssi",

            $school,
            $degree,
            $from,
            $to,
            $highest,
            $graduated,
            $scholarship,

            $education[$level]['id']

            );

            $stmt->execute();

        }

    }

    echo "
    <script>

    alert('Educational Background Updated Successfully.');

    window.location='personnel.php?id=$personnel_id';

    </script>";

    exit;

}
?>

<!DOCTYPE html>
<html>
<head>

<title>Add Educational Background</title>

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

.level-card{
    border-radius:15px;
    margin-bottom:25px;
    border:1px solid #e5e7eb;
}

.level-title{
    background:#2563eb;
    color:white;
    padding:15px 20px;
    border-radius:15px 15px 0 0;
    font-size:18px;
    font-weight:600;
}

.level-body{
    padding:25px;
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

<i class="fas fa-graduation-cap"></i>

Educational Background

</div>

<div class="card-body">

<form method="POST">
<?php

$levels = [

"Elementary",

"Secondary",

"Vocational/Trade Course",

"College",

"Graduate Studies"

];

foreach($levels as $level){

    // ==========================
    // SECONDARY
    // ==========================
    if($level=="Secondary"){
?>

<div class="level-card">

<div class="level-title">
<i class="fas fa-school me-2"></i>
Secondary
</div>

<div class="level-body">

<h5 class="text-primary">
Junior High School
</h5>

<div class="row">

<div class="col-md-6 mb-3">
<label>School Name</label>
<input type="text"
name="school_name[Secondary_JHS]"
class="form-control">
</div>

<div class="col-md-6 mb-3">
<label>Highest Level</label>
<input type="text"
name="highest_level[Secondary_JHS]"
class="form-control">
</div>

<div class="col-md-3 mb-3">
<label>Period From</label>
<input type="date"
name="period_from[Secondary_JHS]"
class="form-control">
</div>

<div class="col-md-3 mb-3">
<label>Period To</label>
<input type="date"
name="period_to[Secondary_JHS]"
class="form-control">
</div>

<div class="col-md-3 mb-3">
<label>Year Graduated</label>
<input type="number"
name="year_graduated[Secondary_JHS]"
class="form-control">
</div>

<div class="col-md-3 mb-3">
<label>Scholarship / Honors</label>
<input type="text"
name="scholarship[Secondary_JHS]"
class="form-control">
</div>

</div>

<hr>

<h5 class="text-primary">
Senior High School
</h5>

<div class="row">

<div class="col-md-6 mb-3">
<label>School Name</label>
<input type="text"
name="school_name[Secondary_SHS]"
class="form-control">
</div>

<div class="col-md-6 mb-3">
<label>Track / Strand</label>
<input type="text"
name="degree[Secondary_SHS]"
class="form-control">
</div>

<div class="col-md-3 mb-3">
<label>Period From</label>
<input type="date"
name="period_from[Secondary_SHS]"
class="form-control">
</div>

<div class="col-md-3 mb-3">
<label>Period To</label>
<input type="date"
name="period_to[Secondary_SHS]"
class="form-control">
</div>

<div class="col-md-3 mb-3">
<label>Year Graduated</label>
<input type="number"
name="year_graduated[Secondary_SHS]"
class="form-control">
</div>

<div class="col-md-3 mb-3">
<label>Scholarship / Honors</label>
<input type="text"
name="scholarship[Secondary_SHS]"
class="form-control">
</div>

</div>

</div>

</div>

<?php
        continue;
    }
?>

<div class="level-card">

<div class="level-title">
<i class="fas fa-school me-2"></i>
<?= $level ?>
</div>

<div class="level-body">

<div class="row">

<div class="col-md-6 mb-3">
<label>School Name</label>
<input type="text"
name="school_name[<?= $level ?>]"
class="form-control">
</div>

<div class="col-md-6 mb-3">
<label>Basic Education / Degree / Course</label>
<input type="text"
name="degree[<?= $level ?>]"
class="form-control">
</div>

<div class="col-md-3 mb-3">
<label>Period From</label>
<input type="date"
name="period_from[<?= $level ?>]"
class="form-control">
</div>

<div class="col-md-3 mb-3">
<label>Period To</label>
<input type="date"
name="period_to[<?= $level ?>]"
class="form-control">
</div>

<div class="col-md-3 mb-3">
<label>Highest Level / Units Earned</label>
<input type="text"
name="highest_level[<?= $level ?>]"
class="form-control">
</div>

<div class="col-md-3 mb-3">
<label>Year Graduated</label>
<input type="text"
name="year_graduated[<?= $level ?>]"
class="form-control">
</div>

<div class="col-md-12">
<label>Scholarship / Academic Honors</label>
<input type="text"
name="scholarship[<?= $level ?>]"
class="form-control">
</div>

</div>

</div>

</div>

<?php
}
?>
<div class="text-end">

<a
href="personnel.php?id=<?= $personnel_id ?>"
class="btn btn-secondary">

Back

</a>

<button
type="submit"
name="update_education"
class="btn btn-primary">

<i class="fas fa-save"></i>

Update All

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