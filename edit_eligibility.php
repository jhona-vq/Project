<?php

include "auth.php";
include "config.php";
include "role_access.php";

allowRoles([
    'System Administrator',
    'HR Administrator'
]);


/* =========================================================
   GET ELIGIBILITY ID
========================================================= */

/*
 * Normally ang Edit button ay dapat:
 *
 * edit_eligibility.php?id=7
 *
 * where 7 = personnel_eligibility.id
 *
 * We also support:
 *
 * edit_eligibility.php?eligibility_id=7
 */

$eligibility_id = intval(
    $_GET['eligibility_id']
    ?? $_GET['id']
    ?? $_POST['eligibility_id']
    ?? 0
);


if ($eligibility_id <= 0) {
    die("Invalid eligibility ID.");
}


/* =========================================================
   LOAD EXISTING ELIGIBILITY
========================================================= */

$stmt = $conn->prepare("
    SELECT *
    FROM personnel_eligibility
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param(
    "i",
    $eligibility_id
);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows === 0) {

    die("Eligibility record not found.");

}


$row = $result->fetch_assoc();


/* =========================================================
   GET PERSONNEL ID FROM ELIGIBILITY RECORD
========================================================= */

$personnel_id = intval(
    $row['personnel_id'] ?? 0
);


if ($personnel_id <= 0) {

    die("Invalid personnel ID.");

}


/* =========================================================
   LOAD PERSONNEL INFORMATION
========================================================= */

$stmtPersonnel = $conn->prepare("
    SELECT *
    FROM personnel
    WHERE id = ?
    LIMIT 1
");

$stmtPersonnel->bind_param(
    "i",
    $personnel_id
);

$stmtPersonnel->execute();

$personnelResult = $stmtPersonnel->get_result();


if ($personnelResult->num_rows === 0) {

    die("Personnel record not found.");

}


$personnel = $personnelResult->fetch_assoc();


/* =========================================================
   UPDATE ELIGIBILITY
========================================================= */

if (isset($_POST['update_eligibility'])) {

    $eligibility = trim(
        $_POST['eligibility'] ?? ''
    );

    $rating = trim(
        $_POST['rating'] ?? ''
    );

    $exam_date = trim(
        $_POST['exam_date'] ?? ''
    );

    $exam_place = trim(
        $_POST['exam_place'] ?? ''
    );

    $license_number = trim(
        $_POST['license_number'] ?? ''
    );

    $valid_option = $_POST['valid_until_option'] ?? '';

$valid_until_date = trim(
    $_POST['valid_until_date'] ?? ''
);


/* =========================================================
   DETERMINE VALID UNTIL VALUE
========================================================= */

if ($valid_option === 'no_expiration') {

    $valid_until = 'No Expiration';

} elseif ($valid_option === 'as_applicable') {

    $valid_until = 'As Applicable';

} elseif ($valid_option === 'specific_date') {

    $valid_until = $valid_until_date;

} else {

    $valid_until = '';

}


    /* =========================================
       UPDATE DATABASE
    ========================================= */

    $stmtUpdate = $conn->prepare("
        UPDATE personnel_eligibility
        SET
            eligibility = ?,
            rating = ?,
            exam_date = ?,
            exam_place = ?,
            license_number = ?,
            valid_until = ?
        WHERE id = ?
        AND personnel_id = ?
    ");


    $stmtUpdate->bind_param(
        "ssssssii",
        $eligibility,
        $rating,
        $exam_date,
        $exam_place,
        $license_number,
        $valid_until,
        $eligibility_id,
        $personnel_id
    );


    if ($stmtUpdate->execute()) {

        echo "
        <script>

            alert('Eligibility Updated Successfully.');

            window.location.href =
                'personnel.php?id=" . $personnel_id . "';

        </script>
        ";

        exit;

    } else {

        $error = "Failed to update eligibility.";

    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Edit Eligibility</title>


<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">


<link
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
rel="stylesheet">


<script>

if(localStorage.getItem("theme") === "dark"){

    document.documentElement.classList.add("dark-mode");

}

</script>


<style>

/* =========================================================
   GENERAL
========================================================= */

*{
    box-sizing:border-box;
}

body{

    background:#f5f7fb;

    color:#0f172a;

    transition:.3s;

}

.container{

    max-width:1000px;

}


/* =========================================================
   CARD
========================================================= */

.main-card{

    border:none;

    border-radius:18px;

    box-shadow:
        0 8px 25px rgba(0,0,0,.08);

    overflow:hidden;

}


/* =========================================================
   HEADER
========================================================= */

.card-header{

    font-size:20px;

    font-weight:600;

    padding:20px 25px;

}


/* =========================================================
   PERSONNEL INFO
========================================================= */

.personnel-info{

    background:#f8fafc;

    border-bottom:1px solid #e5e7eb;

    padding:18px 25px;

}

.personnel-name{

    font-size:20px;

    font-weight:700;

}

.personnel-id{

    color:#64748b;

    font-size:14px;

    margin-top:3px;

}


/* =========================================================
   FORM
========================================================= */

.card-body{

    padding:30px;

}

label{

    font-weight:600;

    margin-bottom:7px;

}

.form-control,
.form-select{

    min-height:44px;

    border-radius:9px;

}

.form-control:focus,
.form-select:focus{

    box-shadow:
        0 0 0 .2rem rgba(37,99,235,.15);

}


/* =========================================================
   SECTION
========================================================= */

.section-title{

    background:#2563eb;

    color:white;

    padding:13px 18px;

    border-radius:10px;

    font-size:17px;

    font-weight:600;

    margin-bottom:25px;

}


/* =========================================================
   BUTTONS
========================================================= */

.action-buttons{

    display:flex;

    justify-content:space-between;

    gap:10px;

    margin-top:30px;

    padding-top:20px;

    border-top:1px solid #e5e7eb;

}

.action-buttons .btn{

    min-width:150px;

}


/* =========================================================
   DARK MODE
========================================================= */

.dark-mode body{

    background:#0f172a;

    color:#f8fafc;

}

.dark-mode .main-card{

    background:#1e293b;

    color:#f8fafc;

}

.dark-mode .personnel-info{

    background:#172033;

    border-color:#334155;

}

.dark-mode .personnel-id{

    color:#cbd5e1;

}

.dark-mode label{

    color:#f8fafc;

}

.dark-mode .form-control,
.dark-mode .form-select{

    background:#334155;

    color:#fff;

    border-color:#475569;

}

.dark-mode .form-control::placeholder{

    color:#cbd5e1;

}

.dark-mode .action-buttons{

    border-color:#475569;

}


/* =========================================================
   MOBILE
========================================================= */

@media(max-width:768px){

    .container{

        padding:10px;

    }

    .card-header{

        font-size:18px;

        padding:16px;

    }

    .card-body{

        padding:18px;

    }

    .personnel-info{

        padding:15px;

    }

    .action-buttons{

        flex-direction:column;

    }

    .action-buttons .btn{

        width:100%;

    }

}

</style>

</head>


<body>


<div class="container py-4">


<div class="main-card card">


<!-- =====================================================
     HEADER
===================================================== -->

<div class="card-header bg-warning">

    <i class="fas fa-edit me-2"></i>

    Edit Eligibility

</div>


<!-- =====================================================
     PERSONNEL INFORMATION
===================================================== -->

<div class="personnel-info">

    <div class="personnel-name">

        <?= htmlspecialchars(
            trim(
                ($personnel['first_name'] ?? '') . ' ' .
                ($personnel['middle_name'] ?? '') . ' ' .
                ($personnel['last_name'] ?? '')
            ),
            ENT_QUOTES,
            'UTF-8'
        ) ?>

    </div>


    <div class="personnel-id">

        Employee ID:

        <strong>

            <?= htmlspecialchars(
                $personnel['employee_id'] ?? '',
                ENT_QUOTES,
                'UTF-8'
            ) ?>

        </strong>

    </div>

</div>


<!-- =====================================================
     FORM
===================================================== -->

<div class="card-body">


<?php if(isset($error)): ?>

    <div class="alert alert-danger">

        <?= htmlspecialchars(
            $error,
            ENT_QUOTES,
            'UTF-8'
        ) ?>

    </div>

<?php endif; ?>


<form method="POST">


<!-- IMPORTANT -->

<input
type="hidden"
name="eligibility_id"
value="<?= $eligibility_id ?>">


<input
type="hidden"
name="personnel_id"
value="<?= $personnel_id ?>">


<!-- =====================================================
     SECTION TITLE
===================================================== -->

<div class="section-title">

    <i class="fas fa-award me-2"></i>

    Eligibility Information

</div>


<!-- =====================================================
     ELIGIBILITY
===================================================== -->

<div class="mb-3">

    <label>

        Eligibility

    </label>


    <input
    type="text"
    name="eligibility"
    class="form-control"
    value="<?= htmlspecialchars(
        $row['eligibility'] ?? '',
        ENT_QUOTES,
        'UTF-8'
    ) ?>"
    required>

</div>


<!-- =====================================================
     RATING
===================================================== -->

<div class="mb-3">

    <label>

        Rating

    </label>


    <input
    type="text"
    name="rating"
    class="form-control"
    value="<?= htmlspecialchars(
        $row['rating'] ?? '',
        ENT_QUOTES,
        'UTF-8'
    ) ?>">

</div>


<!-- =====================================================
     DATE
===================================================== -->

<div class="row">


    <div class="col-md-6 mb-3">

        <label>

            Exam Date

        </label>


        <input
        type="date"
        name="exam_date"
        class="form-control"
        value="<?= htmlspecialchars(
            $row['exam_date'] ?? '',
            ENT_QUOTES,
            'UTF-8'
        ) ?>">

    </div>


    <div class="col-md-6 mb-3">

    <label>Valid Until</label>

    <?php

    $validUntil = $row['valid_until'] ?? '';

    /*
     * Determine current option
     */

    if (
        strtolower($validUntil) === 'no expiration'
        || strtolower($validUntil) === 'no_expiration'
    ) {

        $validOption = 'no_expiration';

    } elseif (
        strtolower($validUntil) === 'as applicable'
        || strtolower($validUntil) === 'as_applicable'
    ) {

        $validOption = 'as_applicable';

    } elseif ($validUntil !== '') {

        $validOption = 'specific_date';

    } else {

        $validOption = '';

    }

    ?>

    <select
        name="valid_until_option"
        id="valid_until_option"
        class="form-select"
        onchange="toggleValidUntil()"
    >

        <option value="">
            Select Validity
        </option>

        <option
            value="no_expiration"
            <?= $validOption === 'no_expiration' ? 'selected' : '' ?>
        >
            No Expiration
        </option>

        <option
            value="as_applicable"
            <?= $validOption === 'as_applicable' ? 'selected' : '' ?>
        >
            As Applicable
        </option>

        <option
            value="specific_date"
            <?= $validOption === 'specific_date' ? 'selected' : '' ?>
        >
            Specific Date
        </option>

    </select>


    <div
        id="valid_until_date_container"
        class="mt-2"
        style="display:none;"
    >

        <input
            type="date"
            name="valid_until_date"
            id="valid_until_date"
            class="form-control"
            value="<?= $validOption === 'specific_date'
                ? htmlspecialchars($validUntil, ENT_QUOTES, 'UTF-8')
                : '' ?>"
        >

    </div>

</div>


</div>


<!-- =====================================================
     EXAM PLACE
===================================================== -->

<div class="mb-3">

    <label>

        Exam Place

    </label>


    <input
    type="text"
    name="exam_place"
    class="form-control"
    value="<?= htmlspecialchars(
        $row['exam_place'] ?? '',
        ENT_QUOTES,
        'UTF-8'
    ) ?>">

</div>


<!-- =====================================================
     LICENSE NUMBER
===================================================== -->

<div class="mb-3">

    <label>

        License Number

        <span class="text-muted">

            (If Applicable)

        </span>

    </label>


    <input
    type="text"
    name="license_number"
    class="form-control"
    value="<?= htmlspecialchars(
        $row['license_number'] ?? '',
        ENT_QUOTES,
        'UTF-8'
    ) ?>">

</div>


<!-- =====================================================
     BUTTONS
===================================================== -->

<div class="action-buttons">


    <a
    href="personnel.php?id=<?= $personnel_id ?>"
    class="btn btn-secondary">

        <i class="fas fa-arrow-left me-1"></i>

        Back to Personnel Profile

    </a>


    <button
    type="submit"
    name="update_eligibility"
    class="btn btn-primary">

        <i class="fas fa-save me-1"></i>

        Update Eligibility

    </button>


</div>


</form>


</div>

</div>

</div>


<script>

document.addEventListener(
    "DOMContentLoaded",
    function(){

        if(
            localStorage.getItem("theme") === "dark"
        ){

            document.documentElement
                .classList.add("dark-mode");

            document.body
                .classList.add("dark-mode");

        }

    }
);

</script>


</body>

</html>
