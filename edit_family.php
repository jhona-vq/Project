<?php
include "auth.php";
include "config.php";

$personnel_id = $_GET['id'] ?? '';


/* ==========================
   LOAD EXISTING RECORDS
========================== */

$spouse = [];
$father = [];
$mother = [];
$children = [];

$getFamily = $conn->query("
SELECT *
FROM personnel_family
WHERE personnel_id='$personnel_id'
");

while($row = $getFamily->fetch_assoc()){

    if($row['relationship']=="Spouse"){
        $spouse = $row;
    }

    elseif($row['relationship']=="Father"){
        $father = $row;
    }

    elseif($row['relationship']=="Mother"){
        $mother = $row;
    }

    elseif($row['relationship']=="Child"){
        $children[] = $row;
    }

}

/* ==========================
   UPDATE FAMILY
========================== */

if(isset($_POST['update_family'])){

    savePerson(
        $conn,
        $personnel_id,
        "Spouse",

        $_POST['spouse_last_name'],
        $_POST['spouse_first_name'],
        $_POST['spouse_middle_name'],
        $_POST['spouse_suffix'],

        $_POST['spouse_occupation'],
        $_POST['spouse_employer'],
        $_POST['spouse_business_address'],
        $_POST['spouse_telephone'],

        null
    );

    savePerson(
        $conn,
        $personnel_id,
        "Father",

        $_POST['father_last_name'],
        $_POST['father_first_name'],
        $_POST['father_middle_name'],
        $_POST['father_suffix'],

        "",
        "",
        "",
        "",

        null
    );

    savePerson(
        $conn,
        $personnel_id,
        "Mother",

        $_POST['mother_last_name'],
        $_POST['mother_first_name'],
        $_POST['mother_middle_name'],
        "",

        "",
        "",
        "",
        "",

        null
    );

    /* ==========================
       UPDATE CHILDREN
    ========================== */

    $conn->query("
    DELETE FROM personnel_family
    WHERE personnel_id='$personnel_id'
    AND relationship='Child'
    ");

    if(isset($_POST['child_last_name'])){

        foreach($_POST['child_last_name'] as $i => $last){

            if(trim($last)==""){
                continue;
            }

            $first  = $_POST['child_first_name'][$i];
            $middle = $_POST['child_middle_name'][$i];
            $suffix = $_POST['child_suffix'][$i];
            $birth  = $_POST['child_birth_date'][$i];

            $relationship = "Child";

            $blank = "";

            $stmt = $conn->prepare("
            INSERT INTO personnel_family
            (
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
            )
            VALUES
            (?,?,?,?,?,?,?,?,?,?,?)
            ");

            $stmt->bind_param(
                "issssssssss",
                $personnel_id,
                $relationship,
                $last,
                $first,
                $middle,
                $suffix,
                $blank,
                $blank,
                $blank,
                $blank,
                $birth
            );

            $stmt->execute();
        }

    }

    echo "
    <script>

    alert('Family Background Updated Successfully.');

    window.location='personnel.php?id=$personnel_id';

    </script>";

    exit;

}

/* ==========================
   INSERT OR UPDATE FUNCTION
========================== */

function savePerson(

    $conn,

    $personnel_id,

    $relationship,

    $last,

    $first,

    $middle,

    $suffix,

    $occupation,

    $employer,

    $business,

    $telephone,

    $birth

){

    $check = $conn->query("

    SELECT id

    FROM personnel_family

    WHERE personnel_id='$personnel_id'

    AND relationship='$relationship'

    ");

    if($check->num_rows>0){

        $old = $check->fetch_assoc();

        $stmt = $conn->prepare("

        UPDATE personnel_family

        SET

        last_name=?,
        first_name=?,
        middle_name=?,
        suffix=?,

        occupation=?,
        employer=?,
        business_address=?,
        telephone=?,
        birth_date=?

        WHERE id=?

        ");

        $stmt->bind_param(

        "sssssssssi",

        $last,
        $first,
        $middle,
        $suffix,

        $occupation,
        $employer,
        $business,
        $telephone,
        $birth,

        $old['id']

        );

    }else{

        $stmt = $conn->prepare("

        INSERT INTO personnel_family(

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

        )

        VALUES(

        ?,?,?,?,?,?,?,?,?,?,?

        )

        ");

        $stmt->bind_param(

        "issssssssss",

        $personnel_id,

        $relationship,

        $last,

        $first,

        $middle,

        $suffix,

        $occupation,

        $employer,

        $business,

        $telephone,

        $birth

        );

    }

    $stmt->execute();

}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Edit Family Background</title>

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

.section-title{
    background:#0d6efd;
    color:white;
    padding:12px 20px;
    border-radius:12px 12px 0 0;
    font-weight:bold;
}

.section-body{
    padding:20px;
}

.child-card{
    border:1px solid #dee2e6;
    border-radius:12px;
    padding:15px;
    margin-bottom:15px;
    background:white;
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
/* =========================
   DARK MODE - CHILD CARD
========================= */

.dark-mode .child-card{
    background:#1e293b;
    border:1px solid #475569;
    color:#fff;
}

.dark-mode .child-card label{
    color:#fff;
}

.dark-mode .child-card .form-control{
    background:#334155;
    border:1px solid #475569;
    color:#fff;
}

.dark-mode .child-card .form-control:focus{
    background:#334155;
    color:#fff;
    border-color:#60a5fa;
    box-shadow:none;
}
</style>

</head>

<body>

<div class="container py-4">

<div class="card">

<div class="card-header bg-warning text-dark">

<h4 class="mb-0">

<i class="fas fa-edit me-2"></i>

Edit Family Background

</h4>

</div>

<div class="card-body">

<form method="POST">
<div class="mb-4">

<div class="section-title">

Spouse

</div>

<div class="section-body">

<div class="row">

<div class="col-md-3 mb-3">
<label>Last Name</label>
<input type="text"
name="spouse_last_name"
class="form-control"
value="<?= $spouse['last_name'] ?? '' ?>">
</div>

<div class="col-md-3 mb-3">
<label>First Name</label>
<input type="text"
name="spouse_first_name"
class="form-control"
value="<?= $spouse['first_name'] ?? '' ?>">
</div>

<div class="col-md-3 mb-3">
<label>Middle Name</label>
<input type="text"
name="spouse_middle_name"
class="form-control"
value="<?= $spouse['middle_name'] ?? '' ?>">
</div>

<div class="col-md-3 mb-3">
<label>Suffix</label>
<input type="text"
name="spouse_suffix"
class="form-control"
value="<?= $spouse['suffix'] ?? '' ?>">
</div>

<div class="col-md-3 mb-3">
<label>Occupation</label>
<input type="text"
name="spouse_occupation"
class="form-control"
value="<?= $spouse['occupation'] ?? '' ?>">
</div>

<div class="col-md-3 mb-3">
<label>Employer / Business</label>
<input type="text"
name="spouse_employer"
class="form-control"
value="<?= $spouse['employer'] ?? '' ?>">
</div>

<div class="col-md-3 mb-3">
<label>Business Address</label>
<input type="text"
name="spouse_business_address"
class="form-control"
value="<?= $spouse['business_address'] ?? '' ?>">
</div>

<div class="col-md-3 mb-3">
<label>Telephone</label>
<input type="text"
name="spouse_telephone"
class="form-control"
value="<?= $spouse['telephone'] ?? '' ?>">
</div>

</div>

</div>

</div>
<div class="mb-4">

<div class="section-title">

Father

</div>

<div class="section-body">

<div class="row">

<div class="col-md-3 mb-3">
<label>Last Name</label>
<input
type="text"
name="father_last_name"
class="form-control"
value="<?= $father['last_name'] ?? '' ?>">
</div>

<div class="col-md-3 mb-3">
<label>First Name</label>
<input
type="text"
name="father_first_name"
class="form-control"
value="<?= $father['first_name'] ?? '' ?>">
</div>

<div class="col-md-3 mb-3">
<label>Middle Name</label>
<input
type="text"
name="father_middle_name"
class="form-control"
value="<?= $father['middle_name'] ?? '' ?>">
</div>

<div class="col-md-3 mb-3">
<label>Suffix</label>
<input
type="text"
name="father_suffix"
class="form-control"
value="<?= $father['suffix'] ?? '' ?>">
</div>

</div>

</div>

</div>
<div class="mb-4">

<div class="section-title">

Mother's Maiden Name

</div>

<div class="section-body">

<div class="row">

<div class="col-md-4 mb-3">
<label>Last Name</label>
<input
type="text"
name="mother_last_name"
class="form-control"
value="<?= $mother['last_name'] ?? '' ?>">
</div>

<div class="col-md-4 mb-3">
<label>First Name</label>
<input
type="text"
name="mother_first_name"
class="form-control"
value="<?= $mother['first_name'] ?? '' ?>">
</div>

<div class="col-md-4 mb-3">
<label>Middle Name</label>
<input
type="text"
name="mother_middle_name"
class="form-control"
value="<?= $mother['middle_name'] ?? '' ?>">
</div>

</div>

</div>

</div>

<div class="mb-4">

<div class="section-title d-flex justify-content-between align-items-center">

<span>
<i class="fas fa-child me-2"></i>
Children
</span>

<button
type="button"
class="btn btn-light btn-sm"
onclick="addChild()">

<i class="fas fa-plus"></i>

Add Child

</button>

</div>

<div class="section-body">

<div id="childrenContainer">

<?php
if(count($children)>0){

foreach($children as $child){
?>

<div class="child-card">

<div class="row">

<div class="col-md-3 mb-3">

<label>Last Name</label>

<input
type="text"
name="child_last_name[]"
class="form-control"
value="<?= $child['last_name'] ?>">

</div>

<div class="col-md-3 mb-3">

<label>First Name</label>

<input
type="text"
name="child_first_name[]"
class="form-control"
value="<?= $child['first_name'] ?>">

</div>

<div class="col-md-2 mb-3">

<label>Middle Name</label>

<input
type="text"
name="child_middle_name[]"
class="form-control"
value="<?= $child['middle_name'] ?>">

</div>

<div class="col-md-2 mb-3">

<label>Suffix</label>

<input
type="text"
name="child_suffix[]"
class="form-control"
value="<?= $child['suffix'] ?>">

</div>

<div class="col-md-2 mb-3">

<label>Date of Birth</label>

<input
type="date"
name="child_birth_date[]"
class="form-control"
value="<?= $child['birth_date'] ?>">

</div>

<div class="col-12 text-end">

<button
type="button"
class="btn btn-danger btn-sm"
onclick="removeChild(this)">

<i class="fas fa-trash"></i>

Remove

</button>

</div>

</div>

</div>

<?php
}
}else{
?>

<div class="child-card">

<div class="row">

<div class="col-md-3 mb-3">

<label>Last Name</label>

<input
type="text"
name="child_last_name[]"
class="form-control">

</div>

<div class="col-md-3 mb-3">

<label>First Name</label>

<input
type="text"
name="child_first_name[]"
class="form-control">

</div>

<div class="col-md-2 mb-3">

<label>Middle Name</label>

<input
type="text"
name="child_middle_name[]"
class="form-control">

</div>

<div class="col-md-2 mb-3">

<label>Suffix</label>

<input
type="text"
name="child_suffix[]"
class="form-control">

</div>

<div class="col-md-2 mb-3">

<label>Date of Birth</label>

<input
type="date"
name="child_birth_date[]"
class="form-control">

</div>

</div>

</div>

<?php } ?>

</div>

</div>

</div>

<div class="text-end mt-4">

<a
href="personnel.php?id=<?= $personnel_id ?>"
class="btn btn-secondary">

<i class="fas fa-arrow-left"></i>

Back

</a>

<button
type="submit"
name="update_family"
class="btn btn-primary">

<i class="fas fa-save"></i>

Update Family Background

</button>

</div>

</form>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

function addChild(){

const html = `
<div class="child-card">

<div class="row">

<div class="col-md-3 mb-3">

<label>Last Name</label>

<input
type="text"
name="child_last_name[]"
class="form-control">

</div>

<div class="col-md-3 mb-3">

<label>First Name</label>

<input
type="text"
name="child_first_name[]"
class="form-control">

</div>

<div class="col-md-2 mb-3">

<label>Middle Name</label>

<input
type="text"
name="child_middle_name[]"
class="form-control">

</div>

<div class="col-md-2 mb-3">

<label>Suffix</label>

<input
type="text"
name="child_suffix[]"
class="form-control">

</div>

<div class="col-md-2 mb-3">

<label>Date of Birth</label>

<input
type="date"
name="child_birth_date[]"
class="form-control">

</div>

<div class="col-12 text-end">

<button
type="button"
class="btn btn-danger btn-sm"
onclick="removeChild(this)">

<i class="fas fa-trash"></i>

Remove

</button>

</div>

</div>

</div>
`;

document
.getElementById("childrenContainer")
.insertAdjacentHTML("beforeend", html);

}

function removeChild(btn){

btn.closest(".child-card").remove();

}

</script>

<script>
document.addEventListener("DOMContentLoaded",function(){

    if(localStorage.getItem("theme")==="dark"){
        document.body.classList.add("dark-mode");
    }

});
</script>
</body>
</html>