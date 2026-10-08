<?php

include "auth.php";
include "config.php";
include "role_access.php";

allowRoles([
    'System Administrator',
    'HR Administrator'
]);


/* =========================================================
   GET PERSONNEL ID
========================================================= */

$personnel_id = intval(
    $_GET['id']
    ?? $_GET['personnel_id']
    ?? $_POST['personnel_id']
    ?? 0
);

if ($personnel_id <= 0) {
    die("Invalid personnel ID.");
}


/* =========================================================
   LOAD PERSONNEL
========================================================= */

$stmt = $conn->prepare("
    SELECT *
    FROM personnel
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $personnel_id);
$stmt->execute();

$personnel_result = $stmt->get_result();

if ($personnel_result->num_rows === 0) {
    die("Personnel record not found.");
}

$personnel = $personnel_result->fetch_assoc();


/* =========================================================
   LOAD OTHER INFORMATION
========================================================= */

$stmt = $conn->prepare("
    SELECT *
    FROM personnel_other_information
    WHERE personnel_id = ?
    LIMIT 1
");

$stmt->bind_param("i", $personnel_id);
$stmt->execute();

$other_result = $stmt->get_result();

$row = $other_result->fetch_assoc();


/* =========================================================
   UPDATE OTHER INFORMATION
========================================================= */

if (isset($_POST['update_other'])) {

    $skills_hobbies =
        trim($_POST['skills_hobbies'] ?? '');

    $non_academic =
        trim($_POST['non_academic'] ?? '');

    $membership =
        trim($_POST['membership'] ?? '');


    /* -----------------------------------------------------
       VALIDATION
    ----------------------------------------------------- */

    if (
        $skills_hobbies === '' ||
        $non_academic === '' ||
        $membership === ''
    ) {
        die("Please complete all required fields.");
    }


    /* =====================================================
       CHECK IF RECORD EXISTS
    ===================================================== */

    if ($row) {

        $other_id = intval($row['id']);


        /* -------------------------------------------------
           UPDATE EXISTING RECORD
        ------------------------------------------------- */

        $stmt = $conn->prepare("
            UPDATE personnel_other_information
            SET
                skills_hobbies = ?,
                non_academic = ?,
                membership = ?
            WHERE id = ?
            AND personnel_id = ?
        ");

        $stmt->bind_param(
            "sssii",
            $skills_hobbies,
            $non_academic,
            $membership,
            $other_id,
            $personnel_id
        );


    } else {


        /* -------------------------------------------------
           CREATE RECORD IF NONE EXISTS
        ------------------------------------------------- */

        $stmt = $conn->prepare("
            INSERT INTO personnel_other_information
            (
                personnel_id,
                skills_hobbies,
                non_academic,
                membership
            )
            VALUES (?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "isss",
            $personnel_id,
            $skills_hobbies,
            $non_academic,
            $membership
        );

    }


    /* =====================================================
       EXECUTE
    ===================================================== */

    if ($stmt->execute()) {

        echo "
        <script>

            alert('Other Information Updated Successfully.');

            window.location='personnel.php?id=" . $personnel_id . "';

        </script>
        ";

        exit;

    } else {

        die(
            "Error updating Other Information: "
            . $stmt->error
        );

    }

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Edit Other Information</title>


<!-- Bootstrap -->

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">


<!-- Font Awesome -->

<link
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
rel="stylesheet">


<script>

if(localStorage.getItem("theme") === "dark"){
    document.documentElement.classList.add("dark-mode");
}

</script>


<style>

*{
    box-sizing:border-box;
}


body{
    background:#f5f7fb;
    color:#1e293b;
    transition:.3s;
}


.container{
    max-width:1000px;
}


.card{
    border:none;
    border-radius:18px;
    box-shadow:0 8px 25px rgba(0,0,0,.08);
    overflow:hidden;
}


.card-header{
    font-size:20px;
    font-weight:bold;
    padding:18px 22px;
}


.card-body{
    padding:25px;
}


label{
    font-weight:600;
    margin-bottom:7px;
}


.form-control{
    border-radius:10px;
    padding:11px 13px;
}


.form-control:focus{
    box-shadow:0 0 0 .2rem rgba(37,99,235,.15);
}


.personnel-info{
    background:#f8fafc;
    border-radius:12px;
    padding:15px 18px;
    margin-bottom:25px;
}


.personnel-info strong{
    color:#2563eb;
}


.section-title{
    font-size:17px;
    font-weight:700;
    color:#2563eb;
    margin-bottom:20px;
    padding-bottom:12px;
    border-bottom:1px solid #e5e7eb;
}


.btn{
    border-radius:9px;
}


/* =========================================================
   DARK MODE
========================================================= */

.dark-mode{
    background:#0f172a !important;
    color:#fff !important;
}


.dark-mode body{
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


.dark-mode .personnel-info{
    background:#334155;
    color:#fff;
}


.dark-mode .section-title{
    color:#60a5fa;
    border-color:#475569;
}


.dark-mode label{
    color:#fff;
}


.dark-mode .form-control{
    background:#334155;
    color:#fff;
    border:1px solid #475569;
}


.dark-mode .form-control::placeholder{
    color:#cbd5e1;
}


.dark-mode .form-control:focus{
    background:#334155;
    color:#fff;
}


/* =========================================================
   MOBILE
========================================================= */

@media(max-width:768px){

    .container{
        padding-left:12px;
        padding-right:12px;
    }

    .card-body{
        padding:18px;
    }

    .card-header{
        font-size:18px;
    }

}

</style>

</head>


<body>


<div class="container py-4">


<div class="card">


<!-- =====================================================
     HEADER
===================================================== -->

<div class="card-header bg-warning">

    <i class="fas fa-user-edit me-2"></i>

    Edit Other Information

</div>


<div class="card-body">


<!-- =====================================================
     PERSONNEL INFORMATION
===================================================== -->

<div class="personnel-info">

    <div class="row">

        <div class="col-md-6 mb-2">

            <strong>Employee ID:</strong>

            <?= htmlspecialchars(
                $personnel['employee_id'] ?? ''
            ) ?>

        </div>


        <div class="col-md-6 mb-2">

            <strong>Name:</strong>

            <?= htmlspecialchars(
                trim(
                    ($personnel['first_name'] ?? '') . ' ' .
                    ($personnel['middle_name'] ?? '') . ' ' .
                    ($personnel['last_name'] ?? '')
                )
            ) ?>

        </div>

    </div>

</div>


<!-- =====================================================
     FORM
===================================================== -->

<form method="POST">


<input
    type="hidden"
    name="personnel_id"
    value="<?= $personnel_id ?>"
>


<div class="section-title">

    <i class="fas fa-info-circle me-2"></i>

    Other Information

</div>


<!-- =====================================================
     SPECIAL SKILLS / HOBBIES
===================================================== -->

<div class="mb-4">

    <label>
        Special Skills / Hobbies
    </label>

    <textarea
        name="skills_hobbies"
        class="form-control"
        rows="4"
        placeholder="Enter special skills or hobbies"
        required
    ><?= htmlspecialchars(
        $row['skills_hobbies'] ?? ''
    ) ?></textarea>

</div>


<!-- =====================================================
     NON-ACADEMIC DISTINCTIONS
===================================================== -->

<div class="mb-4">

    <label>
        Non-Academic Distinction / Recognition
    </label>

    <textarea
        name="non_academic"
        class="form-control"
        rows="4"
        placeholder="Enter non-academic distinctions or recognitions"
        required
    ><?= htmlspecialchars(
        $row['non_academic'] ?? ''
    ) ?></textarea>

</div>


<!-- =====================================================
     MEMBERSHIP
===================================================== -->

<div class="mb-4">

    <label>
        Membership in Association / Organization
    </label>

    <textarea
        name="membership"
        class="form-control"
        rows="4"
        placeholder="Enter memberships in associations or organizations"
        required
    ><?= htmlspecialchars(
        $row['membership'] ?? ''
    ) ?></textarea>

</div>


<!-- =====================================================
     BUTTONS
===================================================== -->

<div class="text-end">


<a
    href="personnel.php?id=<?= $personnel_id ?>"
    class="btn btn-secondary me-2"
>

    <i class="fas fa-arrow-left me-1"></i>

    Back

</a>


<button
    type="submit"
    name="update_other"
    class="btn btn-primary"
>

    <i class="fas fa-save me-1"></i>

    Update

</button>


</div>


</form>


</div>

</div>

</div>


<script>

document.addEventListener("DOMContentLoaded", function(){

    if(localStorage.getItem("theme") === "dark"){

        document.body.classList.add("dark-mode");

    }

});

</script>


</body>

</html>
