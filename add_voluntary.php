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
   SAVE VOLUNTARY WORK
========================================================= */

if(isset($_POST['save_voluntary'])){

    $organization_name = trim(
        $_POST['organization_name'] ?? ''
    );

    $organization_address = trim(
        $_POST['organization_address'] ?? ''
    );

    $date_from_raw = trim(
        $_POST['date_from'] ?? ''
    );

    $date_to_raw = trim(
        $_POST['date_to'] ?? ''
    );

    $date_to_present = isset(
        $_POST['date_to_present']
    );

    $hours = trim(
        $_POST['hours'] ?? ''
    );

    $position = trim(
        $_POST['position'] ?? ''
    );


    /* =====================================================
       REQUIRED VALIDATION
    ===================================================== */

    if($organization_name === ''){

        echo "
        <script>
            alert('Organization Name is required.');
            history.back();
        </script>";

        exit;
    }


    if($organization_address === ''){

        echo "
        <script>
            alert('Organization Address is required.');
            history.back();
        </script>";

        exit;
    }


    if($date_from_raw === ''){

        echo "
        <script>
            alert('Inclusive Date From is required.');
            history.back();
        </script>";

        exit;
    }


    if($position === ''){

        echo "
        <script>
            alert('Position / Nature of Work is required.');
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

    if(
        $date_to_present ||
        strtolower($date_to_raw) === 'present' ||
        $date_to_raw === ''
    ){

        $date_to = null;

    }else{

        $date_to = convertDateToMysql(
            $date_to_raw
        );

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

    if(
        $date_to !== null &&
        $date_from > $date_to
    ){

        echo "
        <script>
            alert('Inclusive Date To cannot be earlier than Inclusive Date From.');
            history.back();
        </script>";

        exit;
    }


    /* =====================================================
       INSERT VOLUNTARY WORK
    ===================================================== */

    $stmt = $conn->prepare("
        INSERT INTO personnel_voluntary_work
        (
            personnel_id,
            organization_name,
            organization_address,
            date_from,
            date_to,
            hours,
            position
        )
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");


    $stmt->bind_param(
        "issssis",
        $personnel_id,
        $organization_name,
        $organization_address,
        $date_from,
        $date_to,
        $hours,
        $position
    );


    if(!$stmt->execute()){

        die(
            "Failed to save voluntary work: " .
            htmlspecialchars($stmt->error)
        );

    }


    echo "
    <script>

        alert('Voluntary Work Added Successfully.');

        window.location='personnel.php?id=" .
        (int)$personnel_id .
        "';

    </script>";

    exit;
}

?>

<!DOCTYPE html>

<html>

<head>

<title>Add Voluntary Work | JOPMIS</title>


<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
>


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
   DATE HELP
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

</style>

</head>


<body>


<div class="container py-4">


    <div class="card">


        <!-- =============================================
             HEADER
        ============================================== -->

        <div class="card-header bg-primary text-white">

            <i class="fas fa-handshake-angle"></i>

            Voluntary Work

        </div>


        <div class="card-body">


            <form method="POST">


                <div class="row">


                    <!-- =================================
                         ORGANIZATION NAME
                    ================================== -->

                    <div class="col-md-12 mb-3">

                        <label>

                            Name & Address of Organization

                            <span class="text-danger">*</span>

                        </label>


                        <input
                            type="text"
                            name="organization_name"
                            class="form-control"
                            required
                        >

                    </div>


                    <!-- =================================
                         ORGANIZATION ADDRESS
                    ================================== -->

                    <div class="col-md-12 mb-3">

                        <label>

                            Organization Address

                            <span class="text-danger">*</span>

                        </label>


                        <input
                            type="text"
                            name="organization_address"
                            class="form-control"
                            required
                        >

                    </div>


                    <!-- =================================
                         DATE FROM
                    ================================== -->

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


                    <!-- =================================
                         DATE TO
                    ================================== -->

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
                                name="date_to_present"
                                id="date_to_present"
                                value="1"
                            >


                            <label for="date_to_present">

                                Present

                            </label>

                        </div>


                        <div class="date-help">

                            Enter DD/MM/YYYY or select Present if ongoing.

                        </div>

                    </div>


                    <!-- =================================
                         NUMBER OF HOURS
                    ================================== -->

                    <div class="col-md-6 mb-3">

                        <label>

                            Number of Hours

                            <span class="text-danger">*</span>

                        </label>


                        <input
                            type="number"
                            name="hours"
                            class="form-control"
                            min="0"
                            required
                        >

                    </div>


                    <!-- =================================
                         POSITION
                    ================================== -->

                    <div class="col-md-6 mb-3">

                        <label>

                            Position / Nature of Work

                            <span class="text-danger">*</span>

                        </label>


                        <input
                            type="text"
                            name="position"
                            class="form-control"
                            required
                        >

                    </div>


                </div>


                <!-- =====================================
                     BUTTONS
                ====================================== -->

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
                        name="save_voluntary"
                        class="btn btn-primary"
                    >

                        <i class="fas fa-save"></i>

                        Save Voluntary Work

                    </button>


                </div>


            </form>


        </div>

    </div>

</div>


<script>

/* =========================================================
   DATE FORMATTER
   DD/MM/YYYY
========================================================= */

function formatDateInput(input){

    input.addEventListener("input", function(){

        let value =
            this.value.replace(/\D/g, "");


        if(value.length > 8){

            value =
                value.substring(0, 8);

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
    document.getElementById(
        "date_to_present"
    );


const dateTo =
    document.getElementById(
        "date_to"
    );


presentCheckbox.addEventListener(
    "change",
    function(){

        if(this.checked){

            dateTo.value = "Present";

            dateTo.readOnly = true;

        }else{

            dateTo.value = "";

            dateTo.readOnly = false;

            dateTo.focus();

        }

    }
);


/* =========================================================
   DARK MODE
========================================================= */

document.addEventListener(
    "DOMContentLoaded",
    function(){

        if(
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
