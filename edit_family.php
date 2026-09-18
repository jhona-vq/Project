<?php

include "auth.php";
include "config.php";

$personnel_id = intval($_GET['id'] ?? 0);

if ($personnel_id <= 0) {
    die("Invalid personnel ID.");
}


/* =========================================================
   DATE FORMAT HELPER
   Makes sure date input receives YYYY-MM-DD
========================================================= */

function formatDateForInput($date)
{
    if (empty($date) || $date == '0000-00-00') {
        return '';
    }

    $timestamp = strtotime($date);

    if ($timestamp === false) {
        return '';
    }

    return date('Y-m-d', $timestamp);
}


/* =========================================================
   HTML ESCAPE HELPER
========================================================= */

function e($value)
{
    return htmlspecialchars(
        $value ?? '',
        ENT_QUOTES,
        'UTF-8'
    );
}


/* =========================================================
   LOAD EXISTING FAMILY RECORDS
========================================================= */

$spouse = [];
$father = [];
$mother = [];
$children = [];

$stmtFamily = $conn->prepare("
    SELECT *
    FROM personnel_family
    WHERE personnel_id = ?
    ORDER BY id ASC
");

$stmtFamily->bind_param(
    "i",
    $personnel_id
);

$stmtFamily->execute();

$getFamily = $stmtFamily->get_result();

while ($row = $getFamily->fetch_assoc()) {

    if ($row['relationship'] == "Spouse") {

        $spouse = $row;

    }
    elseif ($row['relationship'] == "Father") {

        $father = $row;

    }
    elseif ($row['relationship'] == "Mother") {

        $mother = $row;

    }
    elseif ($row['relationship'] == "Child") {

        $children[] = $row;

    }

}

$stmtFamily->close();


/* =========================================================
   UPDATE FAMILY
========================================================= */

if (isset($_POST['update_family'])) {


    /* =====================================================
       SPOUSE
    ===================================================== */

    $spouse_birth = $_POST['spouse_birth_date'] ?? '';

    if ($spouse_birth === '') {
        $spouse_birth = null;
    }
    
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
    
        $spouse_birth
    );


    /* =====================================================
       FATHER
    ===================================================== */

    $father_birth = $_POST['father_birth_date'] ?? '';

if ($father_birth === '') {
    $father_birth = null;
}

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

    $father_birth
);

    /* =====================================================
       MOTHER
    ===================================================== */

    $mother_birth = $_POST['mother_birth_date'] ?? '';

if ($mother_birth === '') {
    $mother_birth = null;
}

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

    $mother_birth
);


    /* =====================================================
       REMOVE OLD CHILDREN
       
       Children are rebuilt from the submitted form.
    ===================================================== */

    $deleteChildren = $conn->prepare("
        DELETE FROM personnel_family
        WHERE personnel_id = ?
        AND relationship = 'Child'
    ");

    $deleteChildren->bind_param(
        "i",
        $personnel_id
    );

    $deleteChildren->execute();
    $deleteChildren->close();


    /* =====================================================
       INSERT CHILDREN
    ===================================================== */

    if (isset($_POST['child_last_name']) &&
        is_array($_POST['child_last_name'])) {


        $childStmt = $conn->prepare("
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
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");


        foreach ($_POST['child_last_name'] as $i => $last) {

            $last = trim($last);

            /*
             * Skip completely empty child rows.
             */

            if ($last == '') {
                continue;
            }


            $first = $_POST['child_first_name'][$i] ?? '';
            $middle = $_POST['child_middle_name'][$i] ?? '';
            $suffix = $_POST['child_suffix'][$i] ?? '';

            /*
             * Children currently only use name + DOB.
             * Other fields are blank.
             */

            $occupation = '';
            $employer = '';
            $business_address = '';
            $telephone = '';

            $birth = $_POST['child_birth_date'][$i] ?? '';

            $relationship = "Child";


            $childStmt->bind_param(
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

            $childStmt->execute();

        }


        $childStmt->close();

    }


    /* =====================================================
       SUCCESS
    ===================================================== */

    echo "
    <script>

        alert('Family Background Updated Successfully.');

        window.location.href =
            'personnel.php?id=" . $personnel_id . "';

    </script>
    ";

    exit;

}


/* =========================================================
   INSERT OR UPDATE SPOUSE / FATHER / MOTHER
========================================================= */

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

    /* =====================================================
       CHECK IF RECORD EXISTS
    ===================================================== */

    $checkStmt = $conn->prepare("
        SELECT id
        FROM personnel_family
        WHERE personnel_id = ?
        AND relationship = ?
        LIMIT 1
    ");

    $checkStmt->bind_param(
        "is",
        $personnel_id,
        $relationship
    );

    $checkStmt->execute();

    $check = $checkStmt->get_result();


    /* =====================================================
       UPDATE EXISTING RECORD
    ===================================================== */

    if ($check->num_rows > 0) {

        $old = $check->fetch_assoc();

        $family_id = intval($old['id']);


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
            AND personnel_id = ?
        ");


        $stmt->bind_param(

            "sssssssssii",

            $last,
            $first,
            $middle,
            $suffix,

            $occupation,
            $employer,
            $business,

            $telephone,
            $birth,

            $family_id,
            $personnel_id

        );

    }

    /* =====================================================
       INSERT NEW RECORD
    ===================================================== */

    else {

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
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
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

    $stmt->close();

    $checkStmt->close();

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1">

<title>Edit Family Background</title>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet">

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
      rel="stylesheet">


<script>

if (localStorage.getItem("theme") === "dark") {

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


/* =========================================================
   DATE INPUT FIX
========================================================= */

input[type="date"]{
    min-height:38px;
}

/* =========================
   RESPONSIVE DATE INPUT
========================= */

.date-field {
    min-width: 0;
}

input[type="date"] {
    width: 100%;
    min-height: 38px;
}

/* Dark mode date picker */
.dark-mode input[type="date"] {
    color-scheme: dark;
}

/* Mobile */
@media (max-width: 575.98px) {

    .container {
        padding-left: 10px !important;
        padding-right: 10px !important;
    }

    .card-body {
        padding: 15px;
    }

    .section-body {
        padding: 15px;
    }

    input[type="date"] {
        min-height: 42px;
    }

    .text-end {
        text-align: stretch !important;
    }

    .text-end .btn {
        width: 100%;
        margin-top: 8px;
    }
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


<!-- =====================================================
     SPOUSE
===================================================== -->

<div class="mb-4">

<div class="section-title">

Spouse

</div>


<div class="section-body">

<div class="row">


<div class="col-md-3 mb-3">

<label>Last Name</label>

<input
type="text"
name="spouse_last_name"
class="form-control"
value="<?= e($spouse['last_name'] ?? '') ?>">

</div>


<div class="col-md-3 mb-3">

<label>First Name</label>

<input
type="text"
name="spouse_first_name"
class="form-control"
value="<?= e($spouse['first_name'] ?? '') ?>">

</div>


<div class="col-md-3 mb-3">

<label>Middle Name</label>

<input
type="text"
name="spouse_middle_name"
class="form-control"
value="<?= e($spouse['middle_name'] ?? '') ?>">

</div>


<div class="col-md-3 mb-3">

<label>Suffix</label>

<input
type="text"
name="spouse_suffix"
class="form-control"
value="<?= e($spouse['suffix'] ?? '') ?>">

</div>


<div class="col-md-3 mb-3">

<label>Occupation</label>

<input
type="text"
name="spouse_occupation"
class="form-control"
value="<?= e($spouse['occupation'] ?? '') ?>">

</div>


<div class="col-md-3 mb-3">

<label>Employer / Business</label>

<input
type="text"
name="spouse_employer"
class="form-control"
value="<?= e($spouse['employer'] ?? '') ?>">

</div>


<div class="col-md-3 mb-3">

<label>Business Address</label>

<input
type="text"
name="spouse_business_address"
class="form-control"
value="<?= e($spouse['business_address'] ?? '') ?>">

</div>


<div class="col-md-3 mb-3">

<label>Telephone</label>

<input
type="text"
name="spouse_telephone"
class="form-control"
value="<?= e($spouse['telephone'] ?? '') ?>">

</div>


<div class="col-md-2 mb-3">

<label>Date of Birth</label>

<input
type="date"
name="spouse_birth_date"
class="form-control"
value="<?= e(formatDateForInput($spouse['birth_date'] ?? '')) ?>">

</div>


</div>

</div>

</div>



<!-- =====================================================
     FATHER
===================================================== -->

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
value="<?= e($father['last_name'] ?? '') ?>">

</div>


<div class="col-md-3 mb-3">

<label>First Name</label>

<input
type="text"
name="father_first_name"
class="form-control"
value="<?= e($father['first_name'] ?? '') ?>">

</div>


<div class="col-md-3 mb-3">

<label>Middle Name</label>

<input
type="text"
name="father_middle_name"
class="form-control"
value="<?= e($father['middle_name'] ?? '') ?>">

</div>


<div class="col-md-3 mb-3">

<label>Suffix</label>

<input
type="text"
name="father_suffix"
class="form-control"
value="<?= e($father['suffix'] ?? '') ?>">

</div>


<div class="col-md-3 mb-3">

<label>Occupation</label>

<input
type="text"
name="father_occupation"
class="form-control"
value="<?= e($father['occupation'] ?? '') ?>">

</div>


<div class="col-md-3 mb-3">

<label>Employer / Business</label>

<input
type="text"
name="father_employer"
class="form-control"
value="<?= e($father['employer'] ?? '') ?>">

</div>


<div class="col-md-3 mb-3">

<label>Business Address</label>

<input
type="text"
name="father_business_address"
class="form-control"
value="<?= e($father['business_address'] ?? '') ?>">

</div>


<div class="col-md-3 mb-3">

<label>Telephone</label>

<input
type="text"
name="father_telephone"
class="form-control"
value="<?= e($father['telephone'] ?? '') ?>">

</div>


<div class="col-12 col-sm-6 col-md-3 col-lg-2 mb-3 date-field">

    <label>Date of Birth</label>

    <input
        type="date"
        name="father_birth_date"
        class="form-control"
        value="<?= e(formatDateForInput($father['birth_date'] ?? '')) ?>">

</div>


</div>

</div>

</div>



<!-- =====================================================
     MOTHER
===================================================== -->

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
value="<?= e($mother['last_name'] ?? '') ?>">

</div>


<div class="col-md-4 mb-3">

<label>First Name</label>

<input
type="text"
name="mother_first_name"
class="form-control"
value="<?= e($mother['first_name'] ?? '') ?>">

</div>


<div class="col-md-4 mb-3">

<label>Middle Name</label>

<input
type="text"
name="mother_middle_name"
class="form-control"
value="<?= e($mother['middle_name'] ?? '') ?>">

</div>


<div class="col-md-3 mb-3">

<label>Occupation</label>

<input
type="text"
name="mother_occupation"
class="form-control"
value="<?= e($mother['occupation'] ?? '') ?>">

</div>


<div class="col-md-3 mb-3">

<label>Employer / Business</label>

<input
type="text"
name="mother_employer"
class="form-control"
value="<?= e($mother['employer'] ?? '') ?>">

</div>


<div class="col-md-3 mb-3">

<label>Business Address</label>

<input
type="text"
name="mother_business_address"
class="form-control"
value="<?= e($mother['business_address'] ?? '') ?>">

</div>


<div class="col-md-3 mb-3">

<label>Telephone</label>

<input
type="text"
name="mother_telephone"
class="form-control"
value="<?= e($mother['telephone'] ?? '') ?>">

</div>


<div class="col-12 col-sm-6 col-md-3 col-lg-2 mb-3 date-field">

    <label>Date of Birth</label>

    <input
        type="date"
        name="mother_birth_date"
        class="form-control"
        value="<?= e(formatDateForInput($mother['birth_date'] ?? '')) ?>">

</div>


</div>

</div>

</div>



<!-- =====================================================
     CHILDREN
===================================================== -->

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

if (count($children) > 0) {

    foreach ($children as $child) {

?>


<div class="child-card">


<div class="row">


<div class="col-md-3 mb-3">

<label>Last Name</label>

<input
type="text"
name="child_last_name[]"
class="form-control"
value="<?= e($child['last_name']) ?>">

</div>


<div class="col-md-3 mb-3">

<label>First Name</label>

<input
type="text"
name="child_first_name[]"
class="form-control"
value="<?= e($child['first_name']) ?>">

</div>


<div class="col-md-2 mb-3">

<label>Middle Name</label>

<input
type="text"
name="child_middle_name[]"
class="form-control"
value="<?= e($child['middle_name']) ?>">

</div>


<div class="col-md-2 mb-3">

<label>Suffix</label>

<input
type="text"
name="child_suffix[]"
class="form-control"
value="<?= e($child['suffix']) ?>">

</div>


<div class="col-12 col-sm-6 col-md-3 col-lg-2 mb-3 date-field">

<label>Date of Birth</label>

<input
type="date"
name="child_birth_date[]"
class="form-control"
value="<?= formatDateForInput($child['birth_date'] ?? '') ?>">

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

} else {

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


<?php

}

?>


</div>

</div>

</div>



<!-- =====================================================
     BUTTONS
===================================================== -->

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



<script
src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


<script>

/* =========================================================
   ADD CHILD
========================================================= */

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
        .insertAdjacentHTML(
            "beforeend",
            html
        );

}


/* =========================================================
   REMOVE CHILD
========================================================= */

function removeChild(btn){

    const card =
        btn.closest(".child-card");

    if (card) {

        card.remove();

    }

}


/* =========================================================
   DARK MODE
========================================================= */

document.addEventListener(
    "DOMContentLoaded",
    function(){

        if (
            localStorage.getItem("theme") === "dark"
        ){

            document.body.classList.add(
                "dark-mode"
            );

        }

    }
);

</script>


</body>

</html>
