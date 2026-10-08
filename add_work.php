<?php
include "auth.php";
include "config.php";

$personnel_id = $_GET['id'] ?? '';

/* =========================================================
   SAVE WORK EXPERIENCE
========================================================= */

if(isset($_POST['save_work'])){

    $date_from_raw = trim($_POST['date_from'] ?? '');
    $date_to_raw   = trim($_POST['date_to'] ?? '');

    $position_title = trim($_POST['position_title'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $monthly_salary = $_POST['monthly_salary'] ?? '';
    $salary_grade = trim($_POST['salary_grade'] ?? '');
    $status_of_appointment = trim($_POST['status_of_appointment'] ?? '');
    $government_service = trim($_POST['government_service'] ?? '');


    /* =====================================================
       CONVERT DD/MM/YYYY TO YYYY-MM-DD
    ===================================================== */

    function convertDateToMysql($date){

        if(empty($date)){
            return null;
        }

        $parts = explode('/', $date);

        if(count($parts) !== 3){
            return false;
        }

        $day   = (int)$parts[0];
        $month = (int)$parts[1];
        $year  = (int)$parts[2];

        if(!checkdate($month, $day, $year)){
            return false;
        }

        return sprintf(
            "%04d-%02d-%02d",
            $year,
            $month,
            $day
        );
    }


    /* =====================================================
       DATE FROM
    ===================================================== */

    $date_from = convertDateToMysql($date_from_raw);

    if($date_from === false){

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

    if(strtolower($date_to_raw) === 'present' || $date_to_raw === ''){

        $date_to = null;

    }else{

        $date_to = convertDateToMysql($date_to_raw);

        if($date_to === false){

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

    if($date_to !== null && $date_from > $date_to){

        echo "
        <script>
        alert('Inclusive Date To cannot be earlier than Inclusive Date From.');
        history.back();
        </script>";

        exit;
    }


    /* =====================================================
       INSERT WORK EXPERIENCE
    ===================================================== */

    $stmt = $conn->prepare("
        INSERT INTO personnel_work_experience
        (
            personnel_id,
            date_from,
            date_to,
            position_title,
            department,
            monthly_salary,
            salary_grade,
            status_of_appointment,
            government_service
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "issssdsss",
        $personnel_id,
        $date_from,
        $date_to,
        $position_title,
        $department,
        $monthly_salary,
        $salary_grade,
        $status_of_appointment,
        $government_service
    );


    if(!$stmt->execute()){

        die(
            "Failed to save work experience: " .
            htmlspecialchars($stmt->error)
        );

    }


    echo "
    <script>
    alert('Work Experience Added Successfully.');
    window.location='personnel.php?id=" . (int)$personnel_id . "';
    </script>";

    exit;
}

?>

<!DOCTYPE html>
<html>

<head>

<title>Add Work Experience | JOPMIS</title>

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


/* =========================================================
   DATE INPUT
========================================================= */

.date-help{
    font-size:13px;
    color:#6b7280;
    margin-top:5px;
}

.dark-mode .date-help{
    color:#cbd5e1;
}


/* =========================================================
   PRESENT CHECKBOX
========================================================= */

.present-option{
    margin-top:8px;
}

.present-option label{
    font-weight:500;
    cursor:pointer;
}

.present-option input{
    cursor:pointer;
}

</style>

</head>


<body>


<div class="container py-4">

    <div class="card">

        <!-- HEADER -->

        <div class="card-header bg-primary text-white">

            <i class="fas fa-briefcase"></i>

            Work Experience

        </div>


        <div class="card-body">


            <form method="POST">


                <div class="row">


                    <!-- =====================================
                         DATE FROM
                    ====================================== -->

                    <div class="col-md-6 mb-3">

                        <label>
                            Inclusive Date From
                            <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            name="date_from"
                            id="date_from"
                            class="form-control"
                            placeholder="DD/MM/YYYY"
                            maxlength="10"
                            required
                        >

                        <div class="date-help">
                            Example: 01/06/2025
                        </div>

                    </div>


                    <!-- =====================================
                         DATE TO
                    ====================================== -->

                    <div class="col-md-6 mb-3">

                        <label>
                            Inclusive Date To
                        </label>

                        <input
                            type="text"
                            name="date_to"
                            id="date_to"
                            class="form-control"
                            placeholder="DD/MM/YYYY"
                            maxlength="10"
                        >

                        <div class="present-option">

                            <input
                                type="checkbox"
                                id="present_checkbox"
                            >

                            <label for="present_checkbox">
                                Present
                            </label>

                        </div>

                        <div class="date-help">
                            Enter DD/MM/YYYY or select Present if the employee is still working.
                        </div>

                    </div>


                    <!-- =====================================
                         POSITION
                    ====================================== -->

                    <div class="col-md-6 mb-3">

                        <label>
                            Position Title
                            <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            name="position_title"
                            class="form-control"
                            required
                        >

                    </div>


                    <!-- =====================================
                         DEPARTMENT
                    ====================================== -->

                    <div class="col-md-6 mb-3">

                        <label>
                            Department / Agency / Office / Company
                            <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            name="department"
                            class="form-control"
                            required
                        >

                    </div>


                    <!-- =====================================
                         MONTHLY SALARY
                    ====================================== -->

                    <div class="col-md-4 mb-3">

                        <label>
                            Monthly Salary
                        </label>

                        <input
                            type="number"
                            step="0.01"
                            name="monthly_salary"
                            class="form-control"
                        >

                    </div>


                    <!-- =====================================
                         SALARY GRADE
                    ====================================== -->

                    <div class="col-md-4 mb-3">

                        <label>
                            Salary Grade / Step
                        </label>

                        <input
                            type="text"
                            name="salary_grade"
                            class="form-control"
                        >

                    </div>


                    <!-- =====================================
                         STATUS OF APPOINTMENT
                    ====================================== -->

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

                            <option value="Permanent">
                                Permanent
                            </option>

                            <option value="Temporary">
                                Temporary
                            </option>

                            <option value="Casual">
                                Casual
                            </option>

                            <option value="Contractual">
                                Contractual
                            </option>

                            <option value="Job Order">
                                Job Order
                            </option>

                            <option value="Contract of Service">
                                Contract of Service
                            </option>

                            <option value="Co-Terminus">
                                Co-Terminus
                            </option>

                            <option value="Elective">
                                Elective
                            </option>

                            <option value="Appointed">
                                Appointed
                            </option>

                            <option value="Others">
                                Others
                            </option>

                        </select>

                    </div>


                    <!-- =====================================
                         GOVERNMENT SERVICE
                    ====================================== -->

                    <div class="col-md-6 mb-3">

                        <label>
                            Government Service
                        </label>

                        <select
                            name="government_service"
                            class="form-select"
                        >

                            <option value="Yes">
                                Yes
                            </option>

                            <option value="No">
                                No
                            </option>

                        </select>

                    </div>


                </div>


                <!-- =========================================
                     BUTTONS
                ========================================== -->

                <div class="text-end mt-3">

                    <a
                        href="personnel.php?id=<?= (int)$personnel_id ?>"
                        class="btn btn-secondary"
                    >

                        <i class="fas fa-arrow-left"></i>

                        Back

                    </a>


                    <button
                        type="submit"
                        name="save_work"
                        class="btn btn-primary"
                    >

                        <i class="fas fa-save"></i>

                        Save Work Experience

                    </button>

                </div>


            </form>


        </div>

    </div>

</div>


<script>

/* =========================================================
   DATE FORMAT
   Automatically adds / when typing
========================================================= */

function formatDateInput(input){

    input.addEventListener("input", function(){

        let value = this.value.replace(/\D/g, "");

        if(value.length > 8){
            value = value.substring(0, 8);
        }

        if(value.length >= 5){

            value =
                value.substring(0,2) +
                "/" +
                value.substring(2,4) +
                "/" +
                value.substring(4);

        }else if(value.length >= 3){

            value =
                value.substring(0,2) +
                "/" +
                value.substring(2);

        }

        this.value = value;

    });

}


/* Apply date formatting */

formatDateInput(
    document.getElementById("date_from")
);

formatDateInput(
    document.getElementById("date_to")
);


/* =========================================================
   PRESENT CHECKBOX
========================================================= */

const presentCheckbox =
    document.getElementById("present_checkbox");

const dateTo =
    document.getElementById("date_to");


presentCheckbox.addEventListener("change", function(){

    if(this.checked){

        dateTo.value = "Present";

        dateTo.readOnly = true;

    }else{

        dateTo.value = "";

        dateTo.readOnly = false;

        dateTo.focus();

    }

});


/* =========================================================
   THEME
========================================================= */

document.addEventListener("DOMContentLoaded", function(){

    if(localStorage.getItem("theme") === "dark"){

        document.body.classList.add("dark-mode");

    }

});

</script>


</body>

</html>
