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
    $_GET['personnel_id']
    ?? $_GET['id']
    ?? $_POST['personnel_id']
    ?? 0
);

if ($personnel_id <= 0) {
    die("Invalid personnel ID.");
}


/* =========================================================
   LOAD PERSONNEL
========================================================= */

$stmtPersonnel = $conn->prepare("
    SELECT *
    FROM personnel
    WHERE id = ?
    LIMIT 1
");

$stmtPersonnel->bind_param("i", $personnel_id);
$stmtPersonnel->execute();

$personnelResult = $stmtPersonnel->get_result();

if (!$personnelResult || $personnelResult->num_rows === 0) {
    die("Personnel record not found.");
}

$personnel = $personnelResult->fetch_assoc();


/* =========================================================
   FUNCTION: CONVERT DD/MM/YYYY TO MYSQL DATE
========================================================= */

function convertDateToMysql($date)
{
    $date = trim($date);

    if ($date === '') {
        return null;
    }

    $parts = explode('/', $date);

    if (count($parts) !== 3) {
        return false;
    }

    $day   = (int)$parts[0];
    $month = (int)$parts[1];
    $year  = (int)$parts[2];

    if (!checkdate($month, $day, $year)) {
        return false;
    }

    return sprintf(
        "%04d-%02d-%02d",
        $year,
        $month,
        $day
    );
}


/* =========================================================
   UPDATE WORK EXPERIENCE
========================================================= */

if (isset($_POST['update_work'])) {

    $work_id = intval($_POST['work_id'] ?? 0);

    if ($work_id <= 0) {
        die("Invalid Work Experience ID.");
    }


    /* =====================================================
       GET FORM DATA
    ===================================================== */

    $date_from_raw = trim(
        $_POST['date_from'] ?? ''
    );

    $date_to_raw = trim(
        $_POST['date_to'] ?? ''
    );

    $date_to_present = isset(
        $_POST['date_to_present']
    );


    $position_title = trim(
        $_POST['position_title'] ?? ''
    );

    $department = trim(
        $_POST['department'] ?? ''
    );

    $monthly_salary = trim(
        $_POST['monthly_salary'] ?? ''
    );

    $salary_grade = trim(
        $_POST['salary_grade'] ?? ''
    );

    $status_of_appointment = trim(
        $_POST['status_of_appointment'] ?? ''
    );

    $government_service = trim(
        $_POST['government_service'] ?? 'No'
    );


    /* =====================================================
       REQUIRED FIELD VALIDATION
    ===================================================== */

    if ($date_from_raw === '') {

        echo "
        <script>
            alert('Inclusive Date From is required.');
            history.back();
        </script>";

        exit;
    }


    if ($position_title === '') {

        echo "
        <script>
            alert('Position Title is required.');
            history.back();
        </script>";

        exit;
    }


    if ($department === '') {

        echo "
        <script>
            alert('Department / Agency / Office / Company is required.');
            history.back();
        </script>";

        exit;
    }


    /* =====================================================
       CONVERT DATE FROM
    ===================================================== */

    $date_from = convertDateToMysql(
        $date_from_raw
    );

    if ($date_from === false) {

        echo "
        <script>
            alert('Invalid Inclusive Date From. Please use DD/MM/YYYY.');
            history.back();
        </script>";

        exit;
    }


    /* =====================================================
       DATE TO
       PRESENT = NULL
    ===================================================== */

    if (
        $date_to_present ||
        strtolower($date_to_raw) === 'present' ||
        $date_to_raw === '' ||
        $date_to_raw === '0000-00-00'
    ) {

        $date_to = null;

    } else {

        $date_to = convertDateToMysql(
            $date_to_raw
        );

        if ($date_to === false) {

            echo "
            <script>
                alert('Invalid Inclusive Date To. Please use DD/MM/YYYY or select Present.');
                history.back();
            </script>";

            exit;
        }
    }


    /* =====================================================
       CHECK DATE ORDER
    ===================================================== */

    if (
        $date_to !== null &&
        $date_from > $date_to
    ) {

        echo "
        <script>
            alert('Inclusive Date To cannot be earlier than Inclusive Date From.');
            history.back();
        </script>";

        exit;
    }


    /* =====================================================
       EMPTY SALARY
    ===================================================== */

    if ($monthly_salary === '') {
        $monthly_salary = null;
    }


    /* =====================================================
       UPDATE SPECIFIC WORK EXPERIENCE
    ===================================================== */

    $stmtUpdate = $conn->prepare("
        UPDATE personnel_work_experience
        SET
            date_from = ?,
            date_to = ?,
            position_title = ?,
            department = ?,
            monthly_salary = ?,
            salary_grade = ?,
            status_of_appointment = ?,
            government_service = ?
        WHERE id = ?
        AND personnel_id = ?
    ");


    /*
       Using string for salary so NULL can be stored
       correctly when the salary field is empty.
    */

    $stmtUpdate->bind_param(
        "ssssssssii",
        $date_from,
        $date_to,
        $position_title,
        $department,
        $monthly_salary,
        $salary_grade,
        $status_of_appointment,
        $government_service,
        $work_id,
        $personnel_id
    );


    if ($stmtUpdate->execute()) {

        echo "
        <script>

            alert('Work Experience Updated Successfully.');

            window.location.href =
                'edit_work.php?id=" . (int)$personnel_id . "';

        </script>
        ";

        exit;

    } else {

        echo "
        <script>

            alert('Error updating Work Experience: " .
            addslashes($stmtUpdate->error) . "');

            history.back();

        </script>
        ";

        exit;
    }
}


/* =========================================================
   LOAD ALL WORK EXPERIENCE
========================================================= */

$stmtWork = $conn->prepare("
    SELECT *
    FROM personnel_work_experience
    WHERE personnel_id = ?
    ORDER BY date_from DESC, id DESC
");

$stmtWork->bind_param(
    "i",
    $personnel_id
);

$stmtWork->execute();

$workResult = $stmtWork->get_result();


/* =========================================================
   PERSONNEL NAME
========================================================= */

$personnel_name = trim(
    ($personnel['first_name'] ?? '') . ' ' .
    ($personnel['middle_name'] ?? '') . ' ' .
    ($personnel['last_name'] ?? '') . ' ' .
    ($personnel['suffix'] ?? '')
);

$personnel_name = htmlspecialchars(
    $personnel_name,
    ENT_QUOTES,
    'UTF-8'
);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Edit Work Experience | JOPMIS</title>


<!-- Bootstrap -->

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
>


<!-- Font Awesome -->

<link
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    rel="stylesheet"
>


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

    min-height:100vh;

    padding-bottom:30px;

}


.main-container{

    max-width:1100px;

    margin:auto;

}


.card{

    border:none;

    border-radius:18px;

    box-shadow:0 10px 25px rgba(0,0,0,.08);

    overflow:hidden;

}


.main-header{

    background:#f59e0b;

    color:#111827;

    font-size:21px;

    font-weight:700;

    padding:18px 22px;

}


.card-body{

    padding:25px;

}


.personnel-info{

    background:#f8fafc;

    border:1px solid #e5e7eb;

    border-radius:12px;

    padding:16px 18px;

    margin-bottom:25px;

}


.work-card{

    border:1px solid #e5e7eb;

    border-radius:15px;

    margin-bottom:25px;

    overflow:hidden;

    background:#fff;

}


.work-card-header{

    background:#f8fafc;

    border-bottom:1px solid #e5e7eb;

    padding:14px 18px;

    font-size:17px;

    font-weight:700;

}


.work-card-body{

    padding:20px;

}


label{

    font-weight:600;

    margin-bottom:7px;

}


.form-control,
.form-select{

    min-height:44px;

    border-radius:10px;

}


.btn{

    border-radius:10px;

    padding:10px 18px;

    font-weight:600;

}


/* =========================================================
   DATE HELP
========================================================= */

.date-help{

    font-size:13px;

    color:#6b7280;

    margin-top:5px;

}


/* =========================================================
   PRESENT OPTION
========================================================= */

.present-option{

    margin-top:8px;

}


.present-option label{

    font-weight:500;

    margin-left:5px;

    cursor:pointer;

}


.present-option input{

    cursor:pointer;

}


/* =========================================================
   DARK MODE
========================================================= */

.dark-mode body{

    background:#0f172a;

    color:#fff;

}


.dark-mode .card{

    background:#1e293b;

    color:#fff;

}


.dark-mode .personnel-info{

    background:#334155;

    border-color:#475569;

    color:#fff;

}


.dark-mode .work-card{

    background:#1e293b;

    border-color:#475569;

}


.dark-mode .work-card-header{

    background:#334155;

    border-color:#475569;

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


.dark-mode .form-select option{

    background:#334155;

    color:#fff;

}


.dark-mode .date-help{

    color:#cbd5e1;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media(max-width:768px){

    .card-body{

        padding:15px;

    }


    .main-header{

        font-size:18px;

    }


    .work-card-body{

        padding:15px;

    }


    .action-buttons{

        display:flex;

        flex-direction:column-reverse;

        gap:10px;

    }


    .action-buttons button,
    .action-buttons a{

        width:100%;

    }

}

</style>

</head>


<body>


<div class="container py-4 main-container">


<div class="card">


<!-- =====================================================
     MAIN HEADER
====================================================== -->

<div class="main-header">

    <i class="fas fa-briefcase"></i>

    Edit Work Experience

</div>


<div class="card-body">


<!-- =====================================================
     PERSONNEL
====================================================== -->

<div class="personnel-info">

    <strong>

        <i class="fas fa-user"></i>

        Personnel:

    </strong>

    <?= $personnel_name ?>

</div>


<?php if ($workResult->num_rows > 0): ?>


<!-- =====================================================
     ALL WORK EXPERIENCE RECORDS
====================================================== -->

<?php

$counter = 1;

while ($work = $workResult->fetch_assoc()):

    $work_id = intval($work['id']);


    /* =================================================
       DATE FROM FOR DISPLAY
    ================================================= */

    if(
        !empty($work['date_from']) &&
        $work['date_from'] !== '0000-00-00'
    ){

        $display_date_from = date(
            'd/m/Y',
            strtotime($work['date_from'])
        );

    }else{

        $display_date_from = '';

    }


    /* =================================================
       DATE TO FOR DISPLAY
    ================================================= */

    if(
        empty($work['date_to']) ||
        $work['date_to'] === '0000-00-00'
    ){

        $display_date_to = 'Present';

        $is_present = true;

    }else{

        $display_date_to = date(
            'd/m/Y',
            strtotime($work['date_to'])
        );

        $is_present = false;

    }

?>


<div class="work-card">


<!-- =================================================
     WORK HEADER
================================================== -->

<div class="work-card-header">

    <i class="fas fa-briefcase"></i>

    Work Experience #<?= $counter ?>

</div>


<div class="work-card-body">


<form method="POST">


<!-- WORK ID -->

<input
    type="hidden"
    name="work_id"
    value="<?= $work_id ?>"
>


<!-- PERSONNEL ID -->

<input
    type="hidden"
    name="personnel_id"
    value="<?= $personnel_id ?>"
>


<div class="row">


<!-- =================================================
     DATE FROM
================================================== -->

<div class="col-md-6 mb-3">

    <label>

        Inclusive Date From

        <span class="text-danger">*</span>

    </label>


    <input
        type="text"
        name="date_from"
        class="form-control date-input"
        value="<?= htmlspecialchars(
            $display_date_from,
            ENT_QUOTES,
            'UTF-8'
        ) ?>"
        placeholder="DD/MM/YYYY"
        maxlength="10"
        required
    >


    <div class="date-help">

        Example: 01/06/2025

    </div>

</div>


<!-- =================================================
     DATE TO
================================================== -->

<div class="col-md-6 mb-3">

    <label>

        Inclusive Date To

    </label>


    <input
        type="text"
        name="date_to"
        class="form-control date-to-input"
        value="<?= htmlspecialchars(
            $display_date_to,
            ENT_QUOTES,
            'UTF-8'
        ) ?>"
        placeholder="DD/MM/YYYY"
        maxlength="10"
        <?= $is_present ? 'readonly' : '' ?>
    >


    <div class="present-option">

        <input
            type="checkbox"
            name="date_to_present"
            value="1"
            id="present_<?= $work_id ?>"
            <?= $is_present ? 'checked' : '' ?>
        >

        <label for="present_<?= $work_id ?>">

            Present

        </label>

    </div>


    <div class="date-help">

        Enter DD/MM/YYYY or select Present.

    </div>

</div>


<!-- =================================================
     POSITION
================================================== -->

<div class="col-md-6 mb-3">

    <label>

        Position Title

        <span class="text-danger">*</span>

    </label>


    <input
        type="text"
        name="position_title"
        class="form-control"
        value="<?= htmlspecialchars(
            $work['position_title'] ?? '',
            ENT_QUOTES,
            'UTF-8'
        ) ?>"
        required
    >

</div>


<!-- =================================================
     DEPARTMENT
================================================== -->

<div class="col-md-6 mb-3">

    <label>

        Department / Agency / Office / Company

        <span class="text-danger">*</span>

    </label>


    <input
        type="text"
        name="department"
        class="form-control"
        value="<?= htmlspecialchars(
            $work['department'] ?? '',
            ENT_QUOTES,
            'UTF-8'
        ) ?>"
        required
    >

</div>


<!-- =================================================
     MONTHLY SALARY
================================================== -->

<div class="col-md-4 mb-3">

    <label>

        Monthly Salary

    </label>


    <input
        type="number"
        step="0.01"
        min="0"
        name="monthly_salary"
        class="form-control"
        value="<?= htmlspecialchars(
            $work['monthly_salary'] ?? '',
            ENT_QUOTES,
            'UTF-8'
        ) ?>"
    >

</div>


<!-- =================================================
     SALARY GRADE
================================================== -->

<div class="col-md-4 mb-3">

    <label>

        Salary Grade / Step

    </label>


    <input
        type="text"
        name="salary_grade"
        class="form-control"
        value="<?= htmlspecialchars(
            $work['salary_grade'] ?? '',
            ENT_QUOTES,
            'UTF-8'
        ) ?>"
        placeholder="e.g. SG 10 / Step 1"
    >

</div>


<!-- =================================================
     STATUS
================================================== -->

<div class="col-md-4 mb-3">

    <label>

        Status of Appointment

    </label>


    <select
        name="status_of_appointment"
        class="form-select"
    >

        <option value="">
            Select
        </option>


        <option
            value="Permanent"
            <?= ($work['status_of_appointment'] ?? '') === 'Permanent'
                ? 'selected'
                : '' ?>
        >
            Permanent
        </option>


        <option
            value="Temporary"
            <?= ($work['status_of_appointment'] ?? '') === 'Temporary'
                ? 'selected'
                : '' ?>
        >
            Temporary
        </option>


        <option
            value="Casual"
            <?= ($work['status_of_appointment'] ?? '') === 'Casual'
                ? 'selected'
                : '' ?>
        >
            Casual
        </option>


        <option
            value="Contractual"
            <?= ($work['status_of_appointment'] ?? '') === 'Contractual'
                ? 'selected'
                : '' ?>
        >
            Contractual
        </option>


        <option
            value="Job Order"
            <?= ($work['status_of_appointment'] ?? '') === 'Job Order'
                ? 'selected'
                : '' ?>
        >
            Job Order
        </option>


        <option
            value="Contract of Service"
            <?= ($work['status_of_appointment'] ?? '') === 'Contract of Service'
                ? 'selected'
                : '' ?>
        >
            Contract of Service
        </option>


        <option
            value="Co-Terminus"
            <?= ($work['status_of_appointment'] ?? '') === 'Co-Terminus'
                ? 'selected'
                : '' ?>
        >
            Co-Terminus
        </option>


        <option
            value="Elective"
            <?= ($work['status_of_appointment'] ?? '') === 'Elective'
                ? 'selected'
                : '' ?>
        >
            Elective
        </option>


        <option
            value="Appointed"
            <?= ($work['status_of_appointment'] ?? '') === 'Appointed'
                ? 'selected'
                : '' ?>
        >
            Appointed
        </option>


        <option
            value="Others"
            <?= ($work['status_of_appointment'] ?? '') === 'Others'
                ? 'selected'
                : '' ?>
        >
            Others
        </option>

    </select>

</div>


<!-- =================================================
     GOVERNMENT SERVICE
================================================== -->

<div class="col-md-6 mb-3">

    <label>

        Government Service

    </label>


    <select
        name="government_service"
        class="form-select"
    >

        <option
            value="Yes"
            <?= ($work['government_service'] ?? '') === 'Yes'
                ? 'selected'
                : '' ?>
        >
            Yes
        </option>


        <option
            value="No"
            <?= ($work['government_service'] ?? '') === 'No'
                ? 'selected'
                : '' ?>
        >
            No
        </option>

    </select>

</div>


</div>


<!-- =================================================
     UPDATE BUTTON
================================================== -->

<div class="d-flex justify-content-end mt-3">

    <button
        type="submit"
        name="update_work"
        class="btn btn-warning"
    >

        <i class="fas fa-save"></i>

        Update This Work Experience

    </button>

</div>


</form>


</div>

</div>


<?php

$counter++;

endwhile;

?>


<!-- =====================================================
     BACK BUTTON
====================================================== -->

<div class="d-flex justify-content-end action-buttons mt-3">

    <a
        href="personnel.php?id=<?= $personnel_id ?>"
        class="btn btn-secondary"
    >

        <i class="fas fa-arrow-left"></i>

        Back to Personnel Profile

    </a>

</div>


<?php else: ?>


<!-- =====================================================
     NO WORK EXPERIENCE
====================================================== -->

<div class="alert alert-info">

    <i class="fas fa-info-circle"></i>

    This personnel has no Work Experience records yet.

</div>


<div class="text-end">

    <a
        href="personnel.php?id=<?= $personnel_id ?>"
        class="btn btn-secondary"
    >

        <i class="fas fa-arrow-left"></i>

        Back to Personnel Profile

    </a>

</div>


<?php endif; ?>


</div>

</div>

</div>


<script>

/* =========================================================
   FORMAT DATE AS DD/MM/YYYY
========================================================= */

function formatDateInput(input){

    input.addEventListener("input", function(){

        let value = this.value.replace(/\D/g, "");

        if(value.length > 8){
            value = value.substring(0, 8);
        }


        if(value.length >= 5){

            value =
                value.substring(0, 2) +
                "/" +
                value.substring(2, 4) +
                "/" +
                value.substring(4);

        }else if(value.length >= 3){

            value =
                value.substring(0, 2) +
                "/" +
                value.substring(2);

        }


        this.value = value;

    });

}


/* =========================================================
   DATE FROM
========================================================= */

document.querySelectorAll(".date-input").forEach(function(input){

    formatDateInput(input);

});


/* =========================================================
   DATE TO
========================================================= */

document.querySelectorAll(".date-to-input").forEach(function(input){

    if(input.value !== "Present"){

        formatDateInput(input);

    }

});


/* =========================================================
   PRESENT CHECKBOX
========================================================= */

document.querySelectorAll(
    'input[name="date_to_present"]'
).forEach(function(checkbox){

    checkbox.addEventListener("change", function(){

        const workCard =
            this.closest(".work-card");

        const dateTo =
            workCard.querySelector(".date-to-input");


        if(this.checked){

            dateTo.value = "Present";

            dateTo.readOnly = true;

        }else{

            dateTo.value = "";

            dateTo.readOnly = false;

            dateTo.focus();

        }

    });

});


/* =========================================================
   THEME
========================================================= */

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
