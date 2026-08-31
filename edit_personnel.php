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

$id = intval($_GET['id'] ?? $_POST['id'] ?? 0);

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

if ($result->num_rows == 0) {
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
       GET FORM VALUES
    ===================================================== */

    $employee_id = trim($_POST['employee_id'] ?? '');

    $last_name = trim($_POST['last_name'] ?? '');

    $first_name = trim($_POST['first_name'] ?? '');

    $middle_name = trim($_POST['middle_name'] ?? '');

    $suffix = trim($_POST['suffix'] ?? '');

    $birth_date = $_POST['birth_date'] ?? null;

    $sex = trim($_POST['sex'] ?? '');

    $date_hired = $_POST['date_hired'] ?? null;

    $employment_status =
        trim($_POST['employment_status'] ?? '');

    $office_assignment =
        trim($_POST['office_assignment'] ?? '');

    $province =
        trim($_POST['province'] ?? '');

    $place_of_birth =
        trim($_POST['place_of_birth'] ?? '');

    $civil_status =
        trim($_POST['civil_status'] ?? '');

    $height =
        $_POST['height'] ?? '';

    $weight =
        $_POST['weight'] ?? '';

    $blood_type =
        trim($_POST['blood_type'] ?? '');


    /* =====================================================
       GOVERNMENT INFORMATION
    ===================================================== */

    $umid_no =
        trim($_POST['umid_no'] ?? '');

    $pagibig_no =
        trim($_POST['pagibig_no'] ?? '');

    $philhealth_no =
        trim($_POST['philhealth_no'] ?? '');

    $psn =
        trim($_POST['psn'] ?? '');

    $tin_no =
        trim($_POST['tin_no'] ?? '');

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
       EMPTY VALUES
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
            date_hired = ?,
            employment_status = ?,
            office_assignment = ?,
            province = ?,

            place_of_birth = ?,
            civil_status = ?,
            height = ?,
            weight = ?,
            blood_type = ?,

            umid_no = ?,
            pagibig_no = ?,
            philhealth_no = ?,
            psn = ?,
            tin_no = ?,
            agency_employee_no = ?,

            citizenship = ?,
            dual_citizenship_type = ?,
            citizenship_country = ?,

            residential_address = ?,
            permanent_address = ?,

            telephone_no = ?,
            contact_no = ?,
            email = ?

        WHERE id = ?

    ");

    if (!$stmt) {
        die("Update prepare failed: " . $conn->error);
    }


    /* =====================================================
       BIND PARAMETERS
       
       30 fields + 1 ID = 31 variables

       We generate the type string automatically:
       30 strings + 1 integer
    ===================================================== */

    $types = str_repeat("s", 30) . "i";

    $stmt->bind_param(

        $types,

        $employee_id,
        $last_name,
        $first_name,
        $middle_name,
        $suffix,

        $birth_date,
        $sex,
        $date_hired,
        $employment_status,
        $office_assignment,
        $province,

        $place_of_birth,
        $civil_status,
        $height,
        $weight,
        $blood_type,

        $umid_no,
        $pagibig_no,
        $philhealth_no,
        $psn,
        $tin_no,
        $agency_employee_no,

        $citizenship,
        $dual_citizenship_type,
        $citizenship_country,

        $residential_address,
        $permanent_address,

        $telephone_no,
        $contact_no,
        $email,

        $id

    );


    /* =====================================================
       EXECUTE
    ===================================================== */

    if (!$stmt->execute()) {

        die(
            "Update failed: " .
            $stmt->error
        );

    }


    $stmt->close();


    /* =====================================================
       RETURN TO PERSONNEL PROFILE
    ===================================================== */

    header(
        "Location: /Cert-main/personnel.php?id=" . $id
    );

    exit();
}

?>
<!DOCTYPE html>
<html>
<head>
<title>Edit Personnel</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<script>
if(localStorage.getItem("theme") === "dark"){
    document.documentElement.classList.add("dark-mode");
}
</script>
<style>
body{
    background:#f5f7fb;
}

.section-title{
    background:#0d6efd;
    color:white;
    padding:12px 20px;
    border-radius:10px;
    margin-top:30px;
    margin-bottom:20px;
    font-size:18px;
    font-weight:bold;
}

.form-control,
.form-select{
    border-radius:10px;
}

.card{
    border-radius:20px;
}

.btn{
    border-radius:10px;
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
<div class="card shadow-lg border-0">
<div class="card-header bg-primary text-white">
    <h3 class="mb-0">
        <i class="fas fa-user-edit"></i>
        Edit Personnel Information
    </h3>
</div>

<div class="card-body">
<h3>Edit Personnel</h3>

<form method="POST">

<div class="row">
<div class=" col-md-4 mb-3">
<label>Employee ID</label>
<input type="text"
name="employee_id"
class="form-control"
value="<?php echo $row['employee_id'] ?? '' ?>">
</div>

<div class=" col-md-4mb-3">
<label>Last Name</label>
<input type="text"
name="last_name"
class="form-control"
value="<?php echo $row['last_name'] ?? '' ?>">
</div>

<div class=" col-md-4mb-3">
<label>First Name</label>
<input type="text"
name="first_name"
class="form-control"
value="<?php echo $row['first_name'] ?? '' ?>">
</div>

</div>

<div class="mb-3">
<label>Middle Name</label>
<input type="text"
name="middle_name"
class="form-control"
value="<?php echo $row['middle_name'] ?? '' ?>">
</div>

<div class="mb-3">
<label>Suffix</label>
<input type="text"
name="suffix"
class="form-control"
value="<?php echo $row['suffix'] ?? '' ?>">
</div>

<div class="mb-3">
<label>Sex</label>
<input type="text"
name="sex"
class="form-control"
value="<?php echo $row['sex'] ?? '' ?>">
</div>

<div class="mb-3">
<label>Civil Status</label>
<input type="text"
name="civil_status"
class="form-control"
value="<?php echo $row['civil_status'] ?? '' ?>">
</div>

<div class="mb-3">
<label>Birth Date</label>
<input type="text"
name="birth_date"
class="form-control"
value="<?php echo $row['birth_date'] ?? '' ?>">
</div>

<div class="mb-3">
<label>Place of Birth</label>
<input
type="text"
name="place_of_birth"
class="form-control"
value="<?php echo $row['place_of_birth'] ?? '' ?>">
</div>

<div class="mb-3">
<label>Height (m)</label>
<input
type="number"
step="0.01"
name="height"
class="form-control"
value="<?php echo $row['height'] ?? '' ?>">
</div>

<div class="mb-3">
<label>Weight (kg)</label>
<input
type="number"
step="0.01"
name="weight"
class="form-control"
value="<?php echo $row['weight'] ?? '' ?>">
</div>

<div class="mb-3">
<label>Blood Type</label>
<input
type="text"
name="blood_type"
class="form-control"
value="<?php echo $row['blood_type'] ?? '' ?>">
</div>

<div class="mb-3">
<label class="fw-bold">Citizenship</label>

<div class="form-check">
    <input
        class="form-check-input"
        type="radio"
        name="citizenship"
        value="Filipino"
        id="filipino"
        <?php if(($row['citizenship'] ?? '') == 'Filipino') echo 'checked'; ?>
    >
    <label class="form-check-label" for="filipino">
        Filipino
    </label>
</div>

<div class="form-check">
    <input
        class="form-check-input"
        type="radio"
        name="citizenship"
        value="Dual Citizenship"
        id="dual"
        <?php if(($row['citizenship'] ?? '') == 'Dual Citizenship') echo 'checked'; ?>
    >
    <label class="form-check-label" for="dual">
        Dual Citizenship
    </label>
</div>

<div id="dualSection"
     style="<?php echo (($row['citizenship'] ?? '') == 'Dual Citizenship') ? '' : 'display:none;'; ?>">

    <div class="form-check mt-3">
        <input
            class="form-check-input"
            type="radio"
            name="dual_citizenship_type"
            value="By Birth"
            id="birth"
            <?php if(($row['dual_citizenship_type'] ?? '') == 'By Birth') echo 'checked'; ?>
        >

        <label class="form-check-label" for="birth">
            By Birth
        </label>
    </div>

    <div class="form-check">
        <input
            class="form-check-input"
            type="radio"
            name="citizenship_type"
            value="By Naturalization"
            id="naturalization"
            <?php if(($row['citizenship_type'] ?? '') == 'By Naturalization') echo 'checked'; ?>
        >

        <label class="form-check-label" for="naturalization">
            By Naturalization
        </label>
    </div>

    <div class="mt-3">
        <label>Please Indicate Country</label>

        <select
            name="citizenship_country"
            class="form-control">

            <option value="">-- Select Country --</option>

            <option value="Philippines"
            <?php if(($row['citizenship_country'] ?? '')=='Philippines') echo 'selected'; ?>>
                Philippines
            </option>

            <option value="United States"
            <?php if(($row['citizenship_country'] ?? '')=='United States') echo 'selected'; ?>>
                United States
            </option>

            <option value="Canada"
            <?php if(($row['citizenship_country'] ?? '')=='Canada') echo 'selected'; ?>>
                Canada
            </option>

            <option value="Australia"
            <?php if(($row['citizenship_country'] ?? '')=='Australia') echo 'selected'; ?>>
                Australia
            </option>

            <option value="Japan"
            <?php if(($row['citizenship_country'] ?? '')=='Japan') echo 'selected'; ?>>
                Japan
            </option>

        </select>
    </div>

</div>

<div class="mb-3">
<label>Telephone No.</label>
<input
type="text"
name="telephone_no"
class="form-control"
value="<?php echo $row['telephone_no'] ?? '' ?>">
</div>

<div class="mb-3">
<label>Agency Employee No.</label>
<input
type="text"
name="agency_employee_no"
class="form-control"
value="<?php echo $row['agency_employee_no'] ?? '' ?>">
</div>

<div class="mb-3">
<label>Contact No</label>
<input type="text"
name="contact_no"
class="form-control"
value="<?php echo $row['contact_no'] ?? '' ?>">
</div>

<div class="mb-3">
<label>Email</label>
<input type="text"
name="email"
class="form-control"
value="<?php echo $row['email'] ?? '' ?>">
</div>

<div class="mb-3">

    <label>Residential Address</label>

    <textarea
        name="residential_address"
        class="form-control"
        rows="3"
    ><?= htmlspecialchars($row['residential_address'] ?? '') ?></textarea>

</div>


<div class="mb-3">

    <label>Permanent Address</label>

    <textarea
        name="permanent_address"
        class="form-control"
        rows="3"
    ><?= htmlspecialchars($row['permanent_address'] ?? '') ?></textarea>

</div>

<div class="section-title">
<i class="fas fa-users"></i>
Family Background
</div>

<div class="row">

<div class="mb-3">
<label>Spouse Name</label>
<input
type="text"
name="spouse_name"
class="form-control"
value="<?= $row['spouse_name'] ?? '' ?>">
</div>

<div class="mb-3">
<label>Occupation</label>
<input
type="text"
name="spouse_occupation"
class="form-control"
value="<?= $row['spouse_occupation'] ?? '' ?>">
</div>

<div class="mb-3">
<label>Employer/Business Name</label>
<input
type="text"
name="spouse_employer"
class="form-control"
value="<?= $row['spouse_employer'] ?? '' ?>">
</div>

<div class="mb-3">
<label>Business Address</label>
<input
type="text"
name="spouse_business_address"
class="form-control"
value="<?= $row['spouse_business_address'] ?? '' ?>">
</div>

<div class="mb-3">
<label>Telephone No.</label>
<input
type="text"
name="spouse_telephone"
class="form-control"
value="<?= $row['spouse_telephone'] ?? '' ?>">
</div>

<div class="mb-3">
<label>Father's Name</label>
<input
type="text"
name="father_name"
class="form-control"
value="<?= $row['father_name'] ?? '' ?>">
</div>

<div class="mb-3">
<label>Mother's Maiden Name</label>
<input
type="text"
name="mother_name"
class="form-control"
value="<?= $row['mother_name'] ?? '' ?>">
</div>

</div>

<div class="mb-3">
<label>Emergency Contact Person</label>
<input type="text"
name="emergency_contact_person"
class="form-control"
value="<?php echo $row['emergency_contact_person'] ?? '' ?>">
</div>

<div class="mb-3">
<label>Emergency Contact Number</label>
<input type="text"
name="emergency_contact_number"
class="form-control"
value="<?php echo $row['emergency_contact_number'] ?? '' ?>">
</div>

<div class="section-title">
<i class="fas fa-briefcase"></i>
Employment Information
</div>

<div class="row">

<div class="mb-3">
<label>Position Title</label>
<input type="text"
name="position_title"
class="form-control"
value="<?php echo $row['position_title'] ?? '' ?>">
</div>

<div class="mb-3">
<label>Employment Category</label>
<input type="text"
name="employment_category"
class="form-control"
value="<?php echo $row['employment_category'] ?? '' ?>">
</div>

<div class="mb-3">
<label>Office Assignment</label>
<input type="text"
name="office_assignment"
class="form-control"
value="<?php echo $row['office_assignment'] ?? '' ?>">
</div>

<div class="mb-3">
<label>Province</label>
<input type="text"
name="province"
class="form-control"
value="<?php echo $row['province'] ?? '' ?>">
</div>

<div class="mb-3">
<label>Date Hired</label>
<input type="text"
name="date_hired"
class="form-control"
value="<?php echo $row['date_hired'] ?? '' ?>">
</div>

<div class="mb-3">
<label>Contract Start</label>
<input type="text"
name="contract_start"
class="form-control"
value="<?php echo $row['contract_start']?? '' ?>">
</div>

<div class="mb-3">
<label>Contract End</label>
<input type="text"
name="contract_end"
class="form-control"
value="<?php echo $row['contract_end'] ?? '' ?>">
</div>

<div class="mb-3">
<label>Employment Status</label>
<input type="text"
name="employment_status"
class="form-control"
value="<?php echo $row['employment_status'] ?? '' ?>">
</div>

<div class="mb-3">
<label>Supervisor</label>
<input type="text"
name="supervisor"
class="form-control"
value="<?php echo $row['supervisor'] ?? '' ?>">
</div>

<div class="mb-3">
<label>Daily Rate</label>
<input type="text"
name="daily_rate"
class="form-control"
value="<?php echo $row['daily_rate'] ?? '' ?>">
</div>

<div class="mb-3">
<label>Monthly Rate</label>
<input type="text"
name="monthly_rate"
class="form-control"
value="<?php echo $row['monthly_rate'] ?? '' ?>">
</div>

<div class="section-title">
<i class="fas fa-id-card"></i>
Government Information
</div>

<div class="row">
<div class="mb-3">
<label>TIN</label>
<input type="text"
name="tin_no"
class="form-control"
value="<?php echo $row['tin_no'] ?? '' ?>">
</div>

<div class="mb-3">
<label>GSIS</label>
<input type="text"
name="gsis"
class="form-control"
value="<?php echo $row['gsis'] ?? '' ?>">
</div>

<div class="mb-3">
<label>Philhealth</label>
<input type="text"
name="philhealth_no"
class="form-control"
value="<?php echo $row['philhealth_no'] ?? '' ?>">
</div>

<div class="mb-3">
<label>Pagibig</label>
<input type="text"
name="pagibig_no"
class="form-control"
value="<?php echo $row['pagibig_no'] ?? '' ?>">
</div>

<div class="mb-3">
<label>UMID ID No.</label>
<input
type="text"
name="umid_no"
class="form-control"
value="<?php echo $row['umid_no'] ?? '' ?>">
</div>

<div class="mb-3">
<label>PhilSys Number (PSN)</label>
<input
type="text"
name="psn"
class="form-control"
value="<?php echo $row['psn'] ?? '' ?>">
</div>

</div>

<div class="mb-3">
<label>Status</label>

<select name="employment_status" class="form-control">

<option value="Active"
<?php if(($row['employment_status'] ?? '')=="Active") echo "selected"; ?>>
Active
</option>

<option value="Resigned"
<?php if(($row['employment_status'] ?? '')=="Resigned") echo "selected"; ?>>
Resigned
</option>

<option value="Terminated"
<?php if(($row['employment_status'] ?? '')=="Terminated") echo "selected"; ?>>
Terminated
</option>

</select>

</div>

<button type="submit"
name="update_personnel"
class="btn btn-primary">
Update Personnel
</button>

<a href="personnel.php"
class="btn btn-secondary">
Cancel
</a>

</form>

</div>

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
