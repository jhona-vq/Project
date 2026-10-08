<?php

include __DIR__ . "/auth.php";
include __DIR__ . "/config.php";
include __DIR__ . "/role_access.php";

allowRoles([
    'System Administrator',
    'HR Administrator'
]);


/* =========================================================
   GET PERSONNEL ID
========================================================= */

$id = intval(
    $_GET['id']
    ?? $_POST['id']
    ?? 0
);

if ($id <= 0) {
    die("Invalid personnel ID.");
}


/* =========================================================
   LOAD PERSONNEL RECORD
========================================================= */

$stmt = $conn->prepare("
    SELECT *
    FROM personnel
    WHERE id = ?
    LIMIT 1
");

if (!$stmt) {
    die("Load personnel failed: " . $conn->error);
}

$stmt->bind_param("i", $id);

if (!$stmt->execute()) {
    die("Load personnel failed: " . $stmt->error);
}

$result = $stmt->get_result();

if ($result->num_rows === 0) {

    $stmt->close();

    die("Personnel record not found.");

}

$row = $result->fetch_assoc();

$stmt->close();


/* =========================================================
   UPDATE PERSONNEL
========================================================= */

if (isset($_POST['update_personnel'])) {


    /* =====================================================
       PERSONAL INFORMATION
    ===================================================== */

    $employee_id =
        trim($_POST['employee_id'] ?? '');

    $last_name =
        trim($_POST['last_name'] ?? '');

    $first_name =
        trim($_POST['first_name'] ?? '');

    $middle_name =
        trim($_POST['middle_name'] ?? '');

    $suffix =
        trim($_POST['suffix'] ?? '');

    $birth_date =
        $_POST['birth_date'] ?? null;

    $sex =
        trim($_POST['sex'] ?? '');

    $civil_status =
        trim($_POST['civil_status'] ?? '');

    $place_of_birth =
        trim($_POST['place_of_birth'] ?? '');

    $height =
        $_POST['height'] ?? null;

    $weight =
        $_POST['weight'] ?? null;

    $blood_type =
        trim($_POST['blood_type'] ?? '');


    /* =====================================================
       EMPLOYMENT INFORMATION
    ===================================================== */

    $position_title =
        trim($_POST['position_title'] ?? '');

    $employment_category =
        trim($_POST['employment_category'] ?? '');

    $office_assignment =
        trim($_POST['office_assignment'] ?? '');

    $province =
        trim($_POST['province'] ?? '');

    $date_hired =
        $_POST['date_hired'] ?? null;

    $employment_status =
        trim($_POST['employment_status'] ?? '');

    $supervisor =
        trim($_POST['supervisor'] ?? '');

    $daily_rate =
        $_POST['daily_rate'] ?? null;

    $monthly_rate =
        $_POST['monthly_rate'] ?? null;


    /* =====================================================
       GOVERNMENT INFORMATION
    ===================================================== */

    $tin_no =
        trim($_POST['tin_no'] ?? '');

    $philhealth_no =
        trim($_POST['philhealth_no'] ?? '');

    $pagibig_no =
        trim($_POST['pagibig_no'] ?? '');

    $umid_no =
        trim($_POST['umid_no'] ?? '');

    $psn =
        trim($_POST['psn'] ?? '');

    $agency_employee_no =
        trim($_POST['agency_employee_no'] ?? '');


    /* =====================================================
       CITIZENSHIP
    ===================================================== */

    $citizenship =
        trim($_POST['citizenship'] ?? '');

    $dual_citizenship_type =
        trim($_POST['dual_citizenship_type'] ?? '');

    $citizenship_country =
        trim($_POST['citizenship_country'] ?? '');


    /* =====================================================
       CONTACT INFORMATION
    ===================================================== */

    $telephone_no =
        trim($_POST['telephone_no'] ?? '');

    $contact_no =
        trim($_POST['contact_no'] ?? '');

    $email =
        trim($_POST['email'] ?? '');


    /* =====================================================
       ADDRESS
    ===================================================== */

    $residential_address =
        trim($_POST['residential_address'] ?? '');

    $permanent_address =
        trim($_POST['permanent_address'] ?? '');


    /* =====================================================
       CONVERT EMPTY VALUES TO NULL
    ===================================================== */

    if ($birth_date === '') {
        $birth_date = null;
    }

    if ($date_hired === '') {
        $date_hired = null;
    }

    if ($height === '') {
        $height = null;
    }

    if ($weight === '') {
        $weight = null;
    }

    if ($daily_rate === '') {
        $daily_rate = null;
    }

    if ($monthly_rate === '') {
        $monthly_rate = null;
    }

    if ($dual_citizenship_type === '') {
        $dual_citizenship_type = null;
    }

    if ($citizenship_country === '') {
        $citizenship_country = null;
    }


    /* =====================================================
       UPDATE DATABASE
    ===================================================== */

    $stmt = $conn->prepare("

        UPDATE personnel

        SET

            employee_id = ?,
            last_name = ?,
            first_name = ?,
            middle_name = ?,
            suffix = ?,

            birth_date = ?,
            sex = ?,
            civil_status = ?,
            place_of_birth = ?,
            height = ?,
            weight = ?,
            blood_type = ?,

            position_title = ?,
            employment_category = ?,
            office_assignment = ?,
            province = ?,
            date_hired = ?,
            employment_status = ?,
            supervisor = ?,
            daily_rate = ?,
            monthly_rate = ?,

            tin_no = ?,
            philhealth_no = ?,
            pagibig_no = ?,
            umid_no = ?,
            psn = ?,
            agency_employee_no = ?,

            citizenship = ?,
            dual_citizenship_type = ?,
            citizenship_country = ?,

            telephone_no = ?,
            contact_no = ?,
            email = ?,

            residential_address = ?,
            permanent_address = ?

        WHERE id = ?

    ");

    if (!$stmt) {
        die("Update prepare failed: " . $conn->error);
    }


    /* =====================================================
       BIND PARAMETERS
       
       35 strings + 1 integer
    ===================================================== */

    $types = str_repeat("s", 35) . "i";

    $stmt->bind_param(

        $types,

        $employee_id,
        $last_name,
        $first_name,
        $middle_name,
        $suffix,

        $birth_date,
        $sex,
        $civil_status,
        $place_of_birth,
        $height,
        $weight,
        $blood_type,

        $position_title,
        $employment_category,
        $office_assignment,
        $province,
        $date_hired,
        $employment_status,
        $supervisor,
        $daily_rate,
        $monthly_rate,

        $tin_no,
        $philhealth_no,
        $pagibig_no,
        $umid_no,
        $psn,
        $agency_employee_no,

        $citizenship,
        $dual_citizenship_type,
        $citizenship_country,

        $telephone_no,
        $contact_no,
        $email,

        $residential_address,
        $permanent_address,

        $id

    );


    /* =====================================================
       EXECUTE UPDATE
    ===================================================== */

    if (!$stmt->execute()) {

        die(
            "Update failed: " .
            $stmt->error
        );

    }


    $stmt->close();


    /* =====================================================
       REDIRECT
    ===================================================== */

    header(
        "Location: personnel.php?id=" . $id
    );

    exit();

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Edit Personnel</title>


<!-- Bootstrap -->

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">


<!-- Font Awesome -->

<link
rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">


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
    max-width:1100px;
}


.card{
    border:none;
    border-radius:20px;
    box-shadow:0 8px 25px rgba(0,0,0,.08);
    overflow:hidden;
}


.card-header{
    padding:18px 22px;
}


.card-header h3{
    font-size:21px;
    font-weight:700;
}


.card-body{
    padding:25px;
}


.section-title{
    background:#0d6efd;
    color:#fff;
    padding:12px 20px;
    border-radius:10px;
    margin-top:30px;
    margin-bottom:20px;
    font-size:18px;
    font-weight:bold;
}


label{
    font-weight:600;
    margin-bottom:7px;
}


.form-control,
.form-select{
    border-radius:10px;
    padding:10px 13px;
}


.form-control:focus,
.form-select:focus{
    box-shadow:0 0 0 .2rem rgba(13,110,253,.15);
}


textarea.form-control{
    resize:vertical;
}


.button-area{
    margin-top:30px;
    padding-top:20px;
    border-top:1px solid #e5e7eb;
}


.btn{
    border-radius:10px;
    padding:9px 18px;
}


/* =========================================================
   CITIZENSHIP
========================================================= */

.citizenship-box{
    padding:15px;
    border:1px solid #e5e7eb;
    border-radius:12px;
}


.form-check-label{
    font-weight:500;
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


.dark-mode .form-control::placeholder{
    color:#cbd5e1;
}


.dark-mode .form-check-label{
    color:#fff;
}


.dark-mode .citizenship-box{
    border-color:#475569;
}


.dark-mode .button-area{
    border-color:#475569;
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

    .card-header h3{
        font-size:18px;
    }

    .section-title{
        font-size:16px;
    }

    .button-area{
        text-align:stretch !important;
    }

    .button-area .btn{
        width:100%;
        margin-bottom:8px;
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

<div class="card-header bg-primary text-white">

    <h3 class="mb-0">

        <i class="fas fa-user-edit me-2"></i>

        Edit Personnel Information

    </h3>

</div>


<div class="card-body">


<form method="POST">


<input
    type="hidden"
    name="id"
    value="<?= (int)$id ?>"
>


<!-- =====================================================
     BASIC INFORMATION
===================================================== -->

<div class="section-title">

    <i class="fas fa-user me-2"></i>

    Basic Information

</div>


<div class="row">


<!-- Employee ID -->

<div class="col-md-4 mb-3">

    <label>Employee ID</label>

    <input
        type="text"
        name="employee_id"
        class="form-control"
        value="<?= htmlspecialchars(
            $row['employee_id'] ?? ''
        ) ?>"
    >

</div>


<!-- Last Name -->

<div class="col-md-4 mb-3">

    <label>Last Name</label>

    <input
        type="text"
        name="last_name"
        class="form-control"
        value="<?= htmlspecialchars(
            $row['last_name'] ?? ''
        ) ?>"
    >

</div>


<!-- First Name -->

<div class="col-md-4 mb-3">

    <label>First Name</label>

    <input
        type="text"
        name="first_name"
        class="form-control"
        value="<?= htmlspecialchars(
            $row['first_name'] ?? ''
        ) ?>"
    >

</div>


<!-- Middle Name -->

<div class="col-md-6 mb-3">

    <label>Middle Name</label>

    <input
        type="text"
        name="middle_name"
        class="form-control"
        value="<?= htmlspecialchars(
            $row['middle_name'] ?? ''
        ) ?>"
    >

</div>


<!-- Suffix -->

<div class="col-md-6 mb-3">

    <label>Suffix</label>

    <input
        type="text"
        name="suffix"
        class="form-control"
        value="<?= htmlspecialchars(
            $row['suffix'] ?? ''
        ) ?>"
    >

</div>


<!-- Sex -->

<div class="col-md-4 mb-3">

    <label>Sex</label>

    <select
        name="sex"
        class="form-select"
    >

        <option value="">Select</option>

        <option
            value="Male"
            <?= ($row['sex'] ?? '') === 'Male'
                ? 'selected'
                : '' ?>
        >
            Male
        </option>

        <option
            value="Female"
            <?= ($row['sex'] ?? '') === 'Female'
                ? 'selected'
                : '' ?>
        >
            Female
        </option>

    </select>

</div>


<!-- Civil Status -->

<div class="col-md-4 mb-3">

    <label>Civil Status</label>

    <select
        name="civil_status"
        class="form-select"
    >

        <option value="">Select</option>

        <option
            value="Single"
            <?= ($row['civil_status'] ?? '') === 'Single'
                ? 'selected'
                : '' ?>
        >
            Single
        </option>

        <option
            value="Married"
            <?= ($row['civil_status'] ?? '') === 'Married'
                ? 'selected'
                : '' ?>
        >
            Married
        </option>

        <option
            value="Widowed"
            <?= ($row['civil_status'] ?? '') === 'Widowed'
                ? 'selected'
                : '' ?>
        >
            Widowed
        </option>

        <option
            value="Separated"
            <?= ($row['civil_status'] ?? '') === 'Separated'
                ? 'selected'
                : '' ?>
        >
            Separated
        </option>

    </select>

</div>


<!-- Birth Date -->

<div class="col-md-4 mb-3">

    <label>Birth Date</label>

    <input
        type="date"
        name="birth_date"
        class="form-control"
        value="<?= htmlspecialchars(
            $row['birth_date'] ?? ''
        ) ?>"
    >

</div>


<!-- Place of Birth -->

<div class="col-md-12 mb-3">

    <label>Place of Birth</label>

    <input
        type="text"
        name="place_of_birth"
        class="form-control"
        value="<?= htmlspecialchars(
            $row['place_of_birth'] ?? ''
        ) ?>"
    >

</div>


<!-- Height -->

<div class="col-md-4 mb-3">

    <label>Height (m)</label>

    <input
        type="number"
        step="0.01"
        min="0"
        name="height"
        class="form-control"
        value="<?= htmlspecialchars(
            $row['height'] ?? ''
        ) ?>"
    >

</div>


<!-- Weight -->

<div class="col-md-4 mb-3">

    <label>Weight (kg)</label>

    <input
        type="number"
        step="0.01"
        min="0"
        name="weight"
        class="form-control"
        value="<?= htmlspecialchars(
            $row['weight'] ?? ''
        ) ?>"
    >

</div>


<!-- Blood Type -->

<div class="col-md-4 mb-3">

    <label>Blood Type</label>

    <select
        name="blood_type"
        class="form-select"
    >

        <option value="">Select</option>

        <?php
        $blood_types = [
            'A+',
            'A-',
            'B+',
            'B-',
            'AB+',
            'AB-',
            'O+',
            'O-'
        ];
        ?>

        <?php foreach($blood_types as $blood): ?>

            <option
                value="<?= $blood ?>"
                <?= ($row['blood_type'] ?? '') === $blood
                    ? 'selected'
                    : '' ?>
            >
                <?= $blood ?>
            </option>

        <?php endforeach; ?>

    </select>

</div>

</div>


<!-- =====================================================
     CITIZENSHIP
===================================================== -->

<div class="section-title">

    <i class="fas fa-flag me-2"></i>

    Citizenship

</div>


<div class="citizenship-box">


<div class="form-check">

    <input
        class="form-check-input"
        type="radio"
        name="citizenship"
        value="Filipino"
        id="filipino"
        <?= ($row['citizenship'] ?? '') === 'Filipino'
            ? 'checked'
            : '' ?>
    >

    <label
        class="form-check-label"
        for="filipino"
    >
        Filipino
    </label>

</div>


<div class="form-check mt-2">

    <input
        class="form-check-input"
        type="radio"
        name="citizenship"
        value="Dual Citizenship"
        id="dual"
        <?= ($row['citizenship'] ?? '') === 'Dual Citizenship'
            ? 'checked'
            : '' ?>
    >

    <label
        class="form-check-label"
        for="dual"
    >
        Dual Citizenship
    </label>

</div>


<div
    id="dualSection"
    class="mt-3"
    style="<?= ($row['citizenship'] ?? '') === 'Dual Citizenship'
        ? ''
        : 'display:none;' ?>"
>


    <label class="mb-2">
        Type of Dual Citizenship
    </label>


    <div class="form-check">

        <input
            class="form-check-input"
            type="radio"
            name="dual_citizenship_type"
            value="By Birth"
            id="by_birth"
            <?= ($row['dual_citizenship_type'] ?? '') === 'By Birth'
                ? 'checked'
                : '' ?>
        >

        <label
            class="form-check-label"
            for="by_birth"
        >
            By Birth
        </label>

    </div>


    <div class="form-check">

        <input
            class="form-check-input"
            type="radio"
            name="dual_citizenship_type"
            value="By Naturalization"
            id="by_naturalization"
            <?= ($row['dual_citizenship_type'] ?? '') === 'By Naturalization'
                ? 'checked'
                : '' ?>
        >

        <label
            class="form-check-label"
            for="by_naturalization"
        >
            By Naturalization
        </label>

    </div>


    <div class="mt-3">

        <label>
            Please Indicate Country
        </label>

        <select
            name="citizenship_country"
            class="form-select"
        >

            <option value="">
                -- Select Country --
            </option>

            <?php
            $countries = [
                'Philippines',
                'United States',
                'Canada',
                'Australia',
                'Japan',
                'United Kingdom',
                'Singapore',
                'South Korea'
            ];
            ?>

            <?php foreach($countries as $country): ?>

                <option
                    value="<?= htmlspecialchars($country) ?>"
                    <?= ($row['citizenship_country'] ?? '') === $country
                        ? 'selected'
                        : '' ?>
                >
                    <?= htmlspecialchars($country) ?>
                </option>

            <?php endforeach; ?>

        </select>

    </div>


</div>

</div>


<!-- =====================================================
     CONTACT INFORMATION
===================================================== -->

<div class="section-title">

    <i class="fas fa-address-book me-2"></i>

    Contact Information

</div>


<div class="row">


<div class="col-md-4 mb-3">

    <label>Telephone No.</label>

    <input
        type="text"
        name="telephone_no"
        class="form-control"
        value="<?= htmlspecialchars(
            $row['telephone_no'] ?? ''
        ) ?>"
    >

</div>


<div class="col-md-4 mb-3">

    <label>Contact No.</label>

    <input
        type="text"
        name="contact_no"
        class="form-control"
        value="<?= htmlspecialchars(
            $row['contact_no'] ?? ''
        ) ?>"
    >

</div>


<div class="col-md-4 mb-3">

    <label>Email</label>

    <input
        type="email"
        name="email"
        class="form-control"
        value="<?= htmlspecialchars(
            $row['email'] ?? ''
        ) ?>"
    >

</div>


<div class="col-md-12 mb-3">

    <label>Residential Address</label>

    <textarea
        name="residential_address"
        class="form-control"
        rows="3"
    ><?= htmlspecialchars(
        $row['residential_address'] ?? ''
    ) ?></textarea>

</div>


<div class="col-md-12 mb-3">

    <label>Permanent Address</label>

    <textarea
        name="permanent_address"
        class="form-control"
        rows="3"
    ><?= htmlspecialchars(
        $row['permanent_address'] ?? ''
    ) ?></textarea>

</div>

</div>


<!-- =====================================================
     EMPLOYMENT INFORMATION
===================================================== -->

<div class="section-title">

    <i class="fas fa-briefcase me-2"></i>

    Employment Information

</div>


<div class="row">


<!-- Position -->

<div class="col-md-6 mb-3">

    <label>Position Title</label>

    <input
        type="text"
        name="position_title"
        class="form-control"
        value="<?= htmlspecialchars(
            $row['position_title'] ?? ''
        ) ?>"
    >

</div>


<!-- Employment Category -->

<div class="col-md-6 mb-3">

    <label>Employment Category</label>

    <input
        type="text"
        name="employment_category"
        class="form-control"
        value="<?= htmlspecialchars(
            $row['employment_category'] ?? ''
        ) ?>"
    >

</div>


<!-- Office -->

<div class="col-md-6 mb-3">

    <label>Office Assignment</label>

    <input
        type="text"
        name="office_assignment"
        class="form-control"
        value="<?= htmlspecialchars(
            $row['office_assignment'] ?? ''
        ) ?>"
    >

</div>


<!-- Province -->

<div class="col-md-6 mb-3">

    <label>Province</label>

    <input
        type="text"
        name="province"
        class="form-control"
        value="<?= htmlspecialchars(
            $row['province'] ?? ''
        ) ?>"
    >

</div>


<!-- Date Hired -->

<div class="col-md-4 mb-3">

    <label>Date Hired</label>

    <input
        type="date"
        name="date_hired"
        class="form-control"
        value="<?= htmlspecialchars(
            $row['date_hired'] ?? ''
        ) ?>"
    >

</div>


<!-- Employment Status -->

<div class="col-md-6 mb-3">

    <label>Employment Status</label>

    <select
        name="employment_status"
        class="form-select"
    >

        <option value="">
            Select Status
        </option>

        <option
            value="Active"
            <?= ($row['employment_status'] ?? '') === 'Active'
                ? 'selected'
                : '' ?>
        >
            Active
        </option>

        <option
            value="Expired"
            <?= ($row['employment_status'] ?? '') === 'Expired'
                ? 'selected'
                : '' ?>
        >
            Expired
        </option>

        <option
            value="Resigned"
            <?= ($row['employment_status'] ?? '') === 'Resigned'
                ? 'selected'
                : '' ?>
        >
            Resigned
        </option>

        <option
            value="Terminated"
            <?= ($row['employment_status'] ?? '') === 'Terminated'
                ? 'selected'
                : '' ?>
        >
            Terminated
        </option>

    </select>

</div>


<!-- Supervisor -->

<div class="col-md-6 mb-3">

    <label>Supervisor</label>

    <input
        type="text"
        name="supervisor"
        class="form-control"
        value="<?= htmlspecialchars(
            $row['supervisor'] ?? ''
        ) ?>"
    >

</div>


<!-- Daily Rate -->

<div class="col-md-6 mb-3">

    <label>Daily Rate</label>

    <input
        type="number"
        step="0.01"
        min="0"
        name="daily_rate"
        class="form-control"
        value="<?= htmlspecialchars(
            $row['daily_rate'] ?? ''
        ) ?>"
    >

</div>


<!-- Monthly Rate -->

<div class="col-md-6 mb-3">

    <label>Monthly Rate</label>

    <input
        type="number"
        step="0.01"
        min="0"
        name="monthly_rate"
        class="form-control"
        value="<?= htmlspecialchars(
            $row['monthly_rate'] ?? ''
        ) ?>"
    >

</div>

</div>


<!-- =====================================================
     GOVERNMENT INFORMATION
===================================================== -->

<div class="section-title">

    <i class="fas fa-id-card me-2"></i>

    Government Information

</div>


<div class="row">


<div class="col-md-4 mb-3">

    <label>TIN</label>

    <input
        type="text"
        name="tin_no"
        class="form-control"
        value="<?= htmlspecialchars(
            $row['tin_no'] ?? ''
        ) ?>"
    >

</div>

<div class="col-md-4 mb-3">

    <label>PhilHealth</label>

    <input
        type="text"
        name="philhealth_no"
        class="form-control"
        value="<?= htmlspecialchars(
            $row['philhealth_no'] ?? ''
        ) ?>"
    >

</div>


<div class="col-md-4 mb-3">

    <label>Pag-IBIG</label>

    <input
        type="text"
        name="pagibig_no"
        class="form-control"
        value="<?= htmlspecialchars(
            $row['pagibig_no'] ?? ''
        ) ?>"
    >

</div>


<div class="col-md-4 mb-3">

    <label>UMID ID No.</label>

    <input
        type="text"
        name="umid_no"
        class="form-control"
        value="<?= htmlspecialchars(
            $row['umid_no'] ?? ''
        ) ?>"
    >

</div>


<div class="col-md-4 mb-3">

    <label>PhilSys Number (PSN)</label>

    <input
        type="text"
        name="psn"
        class="form-control"
        value="<?= htmlspecialchars(
            $row['psn'] ?? ''
        ) ?>"
    >

</div>


<div class="col-md-12 mb-3">

    <label>Agency Employee No.</label>

    <input
        type="text"
        name="agency_employee_no"
        class="form-control"
        value="<?= htmlspecialchars(
            $row['agency_employee_no'] ?? ''
        ) ?>"
    >

</div>

</div>


<!-- =====================================================
     BUTTONS
===================================================== -->

<div class="button-area text-end">


<a
    href="personnel.php?id=<?= (int)$id ?>"
    class="btn btn-secondary me-2"
>

    <i class="fas fa-arrow-left me-1"></i>

    Cancel

</a>


<button
    type="submit"
    name="update_personnel"
    class="btn btn-primary"
>

    <i class="fas fa-save me-1"></i>

    Update Personnel

</button>


</div>


</form>


</div>

</div>

</div>


<!-- =====================================================
     JAVASCRIPT
===================================================== -->

<script>

document.addEventListener(
    "DOMContentLoaded",
    function(){

        const filipino =
            document.getElementById("filipino");

        const dual =
            document.getElementById("dual");

        const dualSection =
            document.getElementById("dualSection");


        function updateCitizenship(){

            if(dual.checked){

                dualSection.style.display = "";

            }else{

                dualSection.style.display = "none";

            }

        }


        filipino.addEventListener(
            "change",
            updateCitizenship
        );


        dual.addEventListener(
            "change",
            updateCitizenship
        );


        if(localStorage.getItem("theme") === "dark"){

            document.body.classList.add("dark-mode");

        }

    }
);

</script>


</body>

</html>
