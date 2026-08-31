<?php

include "auth.php";
include "config.php";

$personnel_id = $_GET['id'] ?? '';

/* ==========================
   VALIDATE PERSONNEL ID
========================== */

$personnel_id = intval($personnel_id);

if ($personnel_id <= 0) {
    die("Invalid personnel ID.");
}


/* ==========================
   LOAD EXISTING RECORDS
========================== */

$spouse = [];
$father = [];
$mother = [];
$children = [];

$stmt = $conn->prepare("
    SELECT *
    FROM personnel_family
    WHERE personnel_id = ?
");

if (!$stmt) {
    die("Load family prepare failed: " . $conn->error);
}

$stmt->bind_param(
    "i",
    $personnel_id
);

$stmt->execute();

$getFamily = $stmt->get_result();

while ($row = $getFamily->fetch_assoc()) {

    if ($row['relationship'] == "Spouse") {

        $spouse = $row;

    } elseif ($row['relationship'] == "Father") {

        $father = $row;

    } elseif ($row['relationship'] == "Mother") {

        $mother = $row;

    } elseif ($row['relationship'] == "Child") {

        $children[] = $row;
    }
}

$stmt->close();


/* ==========================
   SAVE FAMILY
========================== */

if (isset($_POST['save_family'])) {


    /* ==========================
       SAVE SPOUSE
    ========================== */

    savePerson(
        $conn,
        $personnel_id,
        "Spouse",

        $_POST['spouse_last_name'] ?? '',
        $_POST['spouse_first_name'] ?? '',
        $_POST['spouse_middle_name'] ?? '',
        $_POST['spouse_suffix'] ?? '',

        $_POST['spouse_occupation'] ?? '',
        $_POST['spouse_employer'] ?? '',
        $_POST['spouse_business_address'] ?? '',
        $_POST['spouse_telephone'] ?? '',

        null
    );


    /* ==========================
       SAVE FATHER
    ========================== */

    savePerson(
        $conn,
        $personnel_id,
        "Father",

        $_POST['father_last_name'] ?? '',
        $_POST['father_first_name'] ?? '',
        $_POST['father_middle_name'] ?? '',
        $_POST['father_suffix'] ?? '',

        $_POST['father_occupation'] ?? '',
        $_POST['father_employer'] ?? '',
        $_POST['father_business_address'] ?? '',
        $_POST['father_telephone'] ?? '',

        null
    );


    /* ==========================
       SAVE MOTHER
    ========================== */

    savePerson(
        $conn,
        $personnel_id,
        "Mother",

        $_POST['mother_last_name'] ?? '',
        $_POST['mother_first_name'] ?? '',
        $_POST['mother_middle_name'] ?? '',
        '',

        $_POST['mother_occupation'] ?? '',
        $_POST['mother_employer'] ?? '',
        $_POST['mother_business_address'] ?? '',
        $_POST['mother_telephone'] ?? '',

        null
    );


    /* ==========================
       DELETE OLD CHILDREN
    ========================== */

    $delete = $conn->prepare("
        DELETE FROM personnel_family
        WHERE personnel_id = ?
        AND relationship = 'Child'
    ");

    if (!$delete) {
        die("Delete children prepare failed: " . $conn->error);
    }

    $delete->bind_param(
        "i",
        $personnel_id
    );

    if (!$delete->execute()) {
        die("Delete children failed: " . $delete->error);
    }

    $delete->close();


    /* ==========================
       SAVE CHILDREN
    ========================== */

    if (
        isset($_POST['child_last_name']) &&
        is_array($_POST['child_last_name'])
    ) {

        foreach ($_POST['child_last_name'] as $i => $last) {

            $last = trim($last);

            /*
            Skip empty child rows
            */
            if ($last == '') {
                continue;
            }

            $first = $_POST['child_first_name'][$i] ?? '';
            $middle = $_POST['child_middle_name'][$i] ?? '';
            $suffix = $_POST['child_suffix'][$i] ?? '';

            $birth = $_POST['child_birth_date'][$i] ?? null;

            /*
            Empty date becomes NULL
            */
            if ($birth == '') {
                $birth = null;
            }

            $relationship = "Child";

            $occupation = "";
            $employer = "";
            $business_address = "";
            $telephone = "";


            /* ==========================
               INSERT CHILD
            ========================== */

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
                (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                )
            ");

            if (!$stmt) {
                die("Child prepare failed: " . $conn->error);
            }


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
                $business_address,
                $telephone,

                $birth
            );


            if (!$stmt->execute()) {
                die("Child save failed: " . $stmt->error);
            }

            $stmt->close();
        }
    }


    /* ==========================
       SUCCESS
    ========================== */

    echo "
    <script>

        alert('Family Background Saved Successfully.');

        window.location='personnel.php?id=" . $personnel_id . "';

    </script>
    ";

    exit;
}


/* ==========================
   INSERT OR UPDATE PERSON
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

) {

    /* ==========================
       CHECK EXISTING RECORD
    ========================== */

    $check = $conn->prepare("
        SELECT id
        FROM personnel_family
        WHERE personnel_id = ?
        AND relationship = ?
        LIMIT 1
    ");

    if (!$check) {
        die("Check family prepare failed: " . $conn->error);
    }

    $check->bind_param(
        "is",
        $personnel_id,
        $relationship
    );

    if (!$check->execute()) {
        die("Check family failed: " . $check->error);
    }

    $result = $check->get_result();


    /* ==========================
       UPDATE EXISTING
    ========================== */

    if ($result->num_rows > 0) {

        $old = $result->fetch_assoc();

        $family_id = intval($old['id']);

        $check->close();

        $stmt = $conn->prepare("
            UPDATE personnel_family
            SET
                last_name = ?,
                first_name = ?,
                middle_name = ?,
                suffix = ?,
                occupation = ?,
                employer = ?,
                business_address = ?,
                telephone = ?,
                birth_date = ?
            WHERE id = ?
        ");

        if (!$stmt) {
            die("Update family prepare failed: " . $conn->error);
        }

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

            $family_id
        );

        if (!$stmt->execute()) {
            die("Update family failed: " . $stmt->error);
        }

        $stmt->close();

    }


    /* ==========================
       INSERT NEW
    ========================== */

    else {

        $check->close();

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
            (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )
        ");

        if (!$stmt) {
            die("Insert family prepare failed: " . $conn->error);
        }

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

        if (!$stmt->execute()) {
            die("Insert family failed: " . $stmt->error);
        }

        $stmt->close();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Family Background</title>

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

<div class="card-header bg-primary text-white">

<h4 class="mb-0">
<i class="fas fa-people-roof"></i>
Family Background
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

<div class="col-md-3 mb-3">
<label>Occupation</label>
<input type="text"
name="father_occupation"
class="form-control"
value="<?= $father['occupation'] ?? '' ?>">
</div>

<div class="col-md-3 mb-3">
<label>Employer / Business</label>
<input type="text"
name="father_employer"
class="form-control"
value="<?= $father['employer'] ?? '' ?>">
</div>

<div class="col-md-3 mb-3">
<label>Business Address</label>
<input type="text"
name="father_business_address"
class="form-control"
value="<?= $father['business_address'] ?? '' ?>">
</div>

<div class="col-md-3 mb-3">
<label>Telephone</label>
<input type="text"
name="father_telephone"
class="form-control"
value="<?= $father['telephone'] ?? '' ?>">
</div>

<div class="col-md-2 mb-3">
<label>Date of Birth</label>
<input
type="date"
name="father_birth_date[]"
class="form-control"
value="<?= $father['birth_date'] ?>">

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

<div class="col-md-3 mb-3">
<label>Occupation</label>
<input type="text"
name="mother_occupation"
class="form-control"
value="<?= $mother['occupation'] ?? '' ?>">
</div>

<div class="col-md-3 mb-3">
<label>Employer / Business</label>
<input type="text"
name="mother_employer"
class="form-control"
value="<?= $mother['employer'] ?? '' ?>">
</div>

<div class="col-md-3 mb-3">
<label>Business Address</label>
<input type="text"
name="mother_business_address"
class="form-control"
value="<?= $mother['business_address'] ?? '' ?>">
</div>

<div class="col-md-3 mb-3">
<label>Telephone</label>
<input type="text"
name="mother_telephone"
class="form-control"
value="<?= $mother['telephone'] ?? '' ?>">
</div>

<div class="col-md-2 mb-3">
<label>Date of Birth</label>
<input
type="date"
name="mother_birth_date[]"
class="form-control"
value="<?= $mother['birth_date'] ?>">

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
name="save_family"
class="btn btn-primary">

<i class="fas fa-save"></i>

Save Family Background

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
