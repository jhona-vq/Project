<?php

include "auth.php";
include "config.php";

$personnel_id = $_GET['id'] ?? '';

/* =========================================================
   CONVERT DD/MM/YYYY TO MYSQL DATE
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
   SAVE TRAINING
========================================================= */

if(isset($_POST['save_training'])){

    $training_title = trim($_POST['training_title'] ?? '');

    $date_from_raw = trim($_POST['date_from'] ?? '');
    $date_to_raw   = trim($_POST['date_to'] ?? '');

    $date_to_present = isset($_POST['date_to_present']);

    $hours = $_POST['hours'] ?? '';

    $training_type = trim($_POST['training_type'] ?? '');

    $conducted_by = trim($_POST['conducted_by'] ?? '');


    /* =====================================================
       VALIDATE DATE FROM
    ===================================================== */

    $date_from = convertDateToMysql($date_from_raw);

    if($date_from === false || $date_from === null){

        echo "
        <script>
        alert('Please enter a valid Inclusive Date From in DD/MM/YYYY format.');
        history.back();
        </script>";

        exit;
    }


    /* =====================================================
       VALIDATE DATE TO
    ===================================================== */

    if($date_to_present || $date_to_raw === ''){

        // NULL means Present / ongoing
        $date_to = null;

    }else{

        $date_to = convertDateToMysql($date_to_raw);

        if($date_to === false){

            echo "
            <script>
            alert('Please enter a valid Inclusive Date To in DD/MM/YYYY format.');
            history.back();
            </script>";

            exit;
        }
    }


    /* =====================================================
       CHECK DATE ORDER
    ===================================================== */

    if($date_to !== null){

        if(strtotime($date_to) < strtotime($date_from)){

            echo "
            <script>
            alert('Inclusive Date To cannot be earlier than Inclusive Date From.');
            history.back();
            </script>";

            exit;
        }
    }


    /* =====================================================
       INSERT TRAINING
    ===================================================== */

    $stmt = $conn->prepare("
        INSERT INTO personnel_learning_development
        (
            personnel_id,
            training_title,
            date_from,
            date_to,
            hours,
            training_type,
            conducted_by
        )
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "isssiss",
        $personnel_id,
        $training_title,
        $date_from,
        $date_to,
        $hours,
        $training_type,
        $conducted_by
    );


    if(!$stmt->execute()){

        die(
            "Failed to save Learning & Development: "
            . htmlspecialchars($stmt->error)
        );
    }


    echo "
    <script>
    alert('Learning & Development Added Successfully.');
    window.location='personnel.php?id=" . (int)$personnel_id . "';
    </script>";

    exit;
}

?>

<!DOCTYPE html>
<html>

<head>

<title>Add Learning & Development</title>

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


/* =========================
   DATE HELP
========================= */

.date-help{
    font-size:13px;
    color:#6c757d;
    margin-top:5px;
}


/* =========================
   PRESENT OPTION
========================= */

.present-option{
    margin-top:8px;
    display:flex;
    align-items:center;
    gap:7px;
}

.present-option input{
    width:17px;
    height:17px;
}

.present-option label{
    font-weight:500;
    margin:0;
}


/* =========================
   DARK MODE
========================= */

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

.dark-mode .date-help{
    color:#cbd5e1;
}

.dark-mode .present-option label{
    color:#fff;
}

</style>

</head>


<body>


<div class="container py-4">

    <div class="card">

        <div class="card-header bg-primary text-white">

            <i class="fas fa-book-open-reader me-2"></i>

            Learning & Development

        </div>


        <div class="card-body">

            <form method="POST">

                <div class="row">


                    <!-- =====================================
                         TRAINING TITLE
                    ====================================== -->

                    <div class="col-md-12 mb-3">

                        <label>
                            Title of Learning & Development
                        </label>

                        <textarea
                            name="training_title"
                            class="form-control"
                            rows="3"
                            required></textarea>

                    </div>


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
                            required>

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
                            maxlength="10">

                        <div class="present-option">

                            <input
                                type="checkbox"
                                name="date_to_present"
                                id="date_to_present"
                                value="1">

                            <label for="date_to_present">
                                Present
                            </label>

                        </div>

                        <div class="date-help">
                            Enter DD/MM/YYYY or select Present if ongoing.
                        </div>

                    </div>


                    <!-- =====================================
                         HOURS
                    ====================================== -->

                    <div class="col-md-4 mb-3">

                        <label>
                            Number of Hours
                        </label>

                        <input
                            type="number"
                            name="hours"
                            class="form-control"
                            min="0"
                            step="0.01"
                            required>

                    </div>


                    <!-- =====================================
                         TRAINING TYPE
                    ====================================== -->

                    <div class="col-md-4 mb-3">

                        <label>
                            Type of L&D
                        </label>

                        <select
                            name="training_type"
                            class="form-select"
                            required>

                            <option value="">
                                Select
                            </option>

                            <option value="Managerial">
                                Managerial
                            </option>

                            <option value="Supervisory">
                                Supervisory
                            </option>

                            <option value="Technical">
                                Technical
                            </option>

                            <option value="Leadership">
                                Leadership
                            </option>

                            <option value="Executive">
                                Executive
                            </option>

                            <option value="Foundational">
                                Foundational
                            </option>

                            <option value="Others">
                                Others
                            </option>

                        </select>

                    </div>


                    <!-- =====================================
                         CONDUCTED BY
                    ====================================== -->

                    <div class="col-md-4 mb-3">

                        <label>
                            Conducted / Sponsored By
                        </label>

                        <input
                            type="text"
                            name="conducted_by"
                            class="form-control"
                            required>

                    </div>

                </div>


                <!-- =====================================
                     BUTTONS
                ====================================== -->

                <div class="text-end">

                    <a
                        href="personnel.php?id=<?= (int)$personnel_id ?>"
                        class="btn btn-secondary">

                        <i class="fas fa-arrow-left"></i>

                        Back

                    </a>


                    <button
                        type="submit"
                        name="save_training"
                        class="btn btn-primary">

                        <i class="fas fa-save"></i>

                        Save

                    </button>

                </div>

            </form>

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
                value.substring(0,2)
                + "/"
                + value.substring(2,4)
                + "/"
                + value.substring(4);

        }else if(value.length >= 3){

            value =
                value.substring(0,2)
                + "/"
                + value.substring(2);
        }

        this.value = value;

    });

}


/* =========================================================
   APPLY DATE FORMAT
========================================================= */

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
    document.getElementById("date_to_present");

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
   DARK MODE
========================================================= */

document.addEventListener("DOMContentLoaded", function(){

    if(localStorage.getItem("theme") === "dark"){

        document.body.classList.add("dark-mode");

    }

});

</script>


</body>

</html>
