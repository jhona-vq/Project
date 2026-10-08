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
   FORMAT MYSQL DATE TO DD/MM/YYYY
========================================================= */

function formatDateDisplay($date)
{
    if (empty($date)) {
        return '';
    }

    $timestamp = strtotime($date);

    if ($timestamp === false) {
        return '';
    }

    return date('d/m/Y', $timestamp);
}


/* =========================================================
   UPDATE LEARNING & DEVELOPMENT
========================================================= */

if (isset($_POST['update_training'])) {

    $training_id = intval($_POST['training_id'] ?? 0);

    $training_title =
        trim($_POST['training_title'] ?? '');

    $date_from_raw =
        trim($_POST['date_from'] ?? '');

    $date_to_raw =
        trim($_POST['date_to'] ?? '');

    $date_to_present =
        isset($_POST['date_to_present']);

    $hours =
        $_POST['hours'] ?? '';

    $training_type =
        trim($_POST['training_type'] ?? '');

    $conducted_by =
        trim($_POST['conducted_by'] ?? '');


    /* =====================================================
       VALIDATION
    ===================================================== */

    if ($training_id <= 0) {
        die("Invalid Learning & Development record.");
    }

    if (
        $training_title === '' ||
        $date_from_raw === '' ||
        $hours === '' ||
        $training_type === '' ||
        $conducted_by === ''
    ) {
        echo "
        <script>
        alert('Please complete all required fields.');
        history.back();
        </script>
        ";

        exit;
    }


    /* =====================================================
       CONVERT DATE FROM
    ===================================================== */

    $date_from = convertDateToMysql($date_from_raw);

    if ($date_from === false || $date_from === null) {

        echo "
        <script>
        alert('Please enter a valid Inclusive Date From in DD/MM/YYYY format.');
        history.back();
        </script>
        ";

        exit;
    }


    /* =====================================================
       CONVERT DATE TO
    ===================================================== */

    if ($date_to_present || $date_to_raw === '') {

        // NULL = Present / ongoing

        $date_to = null;

    } else {

        $date_to = convertDateToMysql($date_to_raw);

        if ($date_to === false) {

            echo "
            <script>
            alert('Please enter a valid Inclusive Date To in DD/MM/YYYY format.');
            history.back();
            </script>
            ";

            exit;
        }
    }


    /* =====================================================
       CHECK DATE ORDER
    ===================================================== */

    if ($date_to !== null) {

        if (strtotime($date_to) < strtotime($date_from)) {

            echo "
            <script>
            alert('Inclusive Date To cannot be earlier than Inclusive Date From.');
            history.back();
            </script>
            ";

            exit;
        }
    }


    /* =====================================================
       UPDATE RECORD
    ===================================================== */

    $stmt = $conn->prepare("
        UPDATE personnel_learning_development
        SET
            training_title = ?,
            date_from = ?,
            date_to = ?,
            hours = ?,
            training_type = ?,
            conducted_by = ?
        WHERE id = ?
        AND personnel_id = ?
    ");

    $stmt->bind_param(
        "sssdssii",
        $training_title,
        $date_from,
        $date_to,
        $hours,
        $training_type,
        $conducted_by,
        $training_id,
        $personnel_id
    );


    if ($stmt->execute()) {

        echo "
        <script>

            alert('Learning & Development Updated Successfully.');

            window.location='personnel.php?id=" . (int)$personnel_id . "';

        </script>
        ";

        exit;

    } else {

        die(
            "Error updating Learning & Development: "
            . htmlspecialchars($stmt->error)
        );

    }
}


/* =========================================================
   LOAD ALL LEARNING & DEVELOPMENT RECORDS
========================================================= */

$stmt = $conn->prepare("
    SELECT *
    FROM personnel_learning_development
    WHERE personnel_id = ?
    ORDER BY date_from DESC, id DESC
");

$stmt->bind_param("i", $personnel_id);
$stmt->execute();

$training_result = $stmt->get_result();

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Edit Learning & Development</title>


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
    color:#1e293b;
    transition:.3s;
}


.container{
    max-width:1100px;
}


.card{
    border:none;
    border-radius:18px;
    box-shadow:0 8px 25px rgba(0,0,0,.08);
    overflow:hidden;
    margin-bottom:25px;
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


.form-control,
.form-select{
    border-radius:10px;
    padding:10px 13px;
}


.form-control:focus,
.form-select:focus{
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


.training-card{
    border:1px solid #e2e8f0;
    border-radius:15px;
    padding:22px;
    margin-bottom:25px;
    background:#fff;
}


.training-title{
    font-size:17px;
    font-weight:700;
    color:#2563eb;
    margin-bottom:20px;
    padding-bottom:12px;
    border-bottom:1px solid #e5e7eb;
}


/* =========================================================
   DATE HELP
========================================================= */

.date-help{
    font-size:13px;
    color:#6c757d;
    margin-top:5px;
}


/* =========================================================
   PRESENT OPTION
========================================================= */

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


.dark-mode .training-card{
    background:#1e293b;
    border-color:#475569;
    color:#fff;
}


.dark-mode .training-title{
    color:#60a5fa;
    border-color:#475569;
}


.dark-mode label{
    color:#fff;
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


.dark-mode .form-control:focus,
.dark-mode .form-select:focus{
    background:#334155;
    color:#fff;
}


.dark-mode .date-help{
    color:#cbd5e1;
}


.dark-mode .present-option label{
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

    .training-card{
        padding:16px;
    }

}

</style>

</head>


<body>


<div class="container py-4">


<!-- =====================================================
     MAIN CARD
===================================================== -->

<div class="card">


    <!-- HEADER -->

    <div class="card-header bg-primary text-white">

        <i class="fas fa-book-open-reader me-2"></i>

        Edit Learning & Development

    </div>


    <div class="card-body">


        <!-- =================================================
             PERSONNEL INFORMATION
        ================================================== -->

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


        <!-- =================================================
             CHECK IF RECORDS EXIST
        ================================================== -->

        <?php if($training_result->num_rows > 0): ?>


            <?php $counter = 1; ?>


            <?php while($row = $training_result->fetch_assoc()): ?>


                <?php

                /*
                 * Format database dates:
                 * YYYY-MM-DD
                 * becomes
                 * DD/MM/YYYY
                 */

                $displayDateFrom =
                    formatDateDisplay($row['date_from'] ?? '');

                $displayDateTo =
                    formatDateDisplay($row['date_to'] ?? '');

                ?>


                <!-- =================================================
                     TRAINING CARD
                ================================================= -->

                <div class="training-card">


                    <div class="training-title">

                        <i class="fas fa-graduation-cap me-2"></i>

                        Learning & Development #<?= $counter ?>

                    </div>


                    <form method="POST">


                        <!-- PERSONNEL ID -->

                        <input
                            type="hidden"
                            name="personnel_id"
                            value="<?= (int)$personnel_id ?>"
                        >


                        <!-- TRAINING RECORD ID -->

                        <input
                            type="hidden"
                            name="training_id"
                            value="<?= (int)$row['id'] ?>"
                        >


                        <div class="row">


                            <!-- =================================================
                                 TITLE
                            ================================================== -->

                            <div class="col-md-12 mb-3">

                                <label>
                                    Title of Learning & Development
                                </label>

                                <textarea
                                    name="training_title"
                                    class="form-control"
                                    rows="3"
                                    required
                                ><?= htmlspecialchars(
                                    $row['training_title'] ?? ''
                                ) ?></textarea>

                            </div>


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
                                    class="form-control date-from"
                                    placeholder="DD/MM/YYYY"
                                    maxlength="10"
                                    value="<?= htmlspecialchars(
                                        $displayDateFrom
                                    ) ?>"
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
                                    class="form-control date-to"
                                    placeholder="DD/MM/YYYY"
                                    maxlength="10"
                                    value="<?= htmlspecialchars(
                                        $displayDateTo
                                    ) ?>"
                                    <?= empty($row['date_to'])
                                        ? 'readonly'
                                        : '' ?>
                                >

                                <div class="present-option">

                                    <input
                                        type="checkbox"
                                        name="date_to_present"
                                        class="date-to-present"
                                        value="1"
                                        <?= empty($row['date_to'])
                                            ? 'checked'
                                            : '' ?>
                                    >

                                    <label>
                                        Present
                                    </label>

                                </div>

                                <div class="date-help">
                                    Enter DD/MM/YYYY or select Present if ongoing.
                                </div>

                            </div>


                            <!-- =================================================
                                 HOURS
                            ================================================== -->

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
                                    value="<?= htmlspecialchars(
                                        $row['hours'] ?? ''
                                    ) ?>"
                                    required
                                >

                            </div>


                            <!-- =================================================
                                 TRAINING TYPE
                            ================================================== -->

                            <div class="col-md-4 mb-3">

                                <label>
                                    Type of L&D
                                </label>

                                <select
                                    name="training_type"
                                    class="form-select"
                                    required
                                >

                                    <option value="">
                                        Select
                                    </option>


                                    <option
                                        value="Managerial"
                                        <?= ($row['training_type'] ?? '') === 'Managerial'
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        Managerial
                                    </option>


                                    <option
                                        value="Supervisory"
                                        <?= ($row['training_type'] ?? '') === 'Supervisory'
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        Supervisory
                                    </option>


                                    <option
                                        value="Technical"
                                        <?= ($row['training_type'] ?? '') === 'Technical'
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        Technical
                                    </option>


                                    <option
                                        value="Leadership"
                                        <?= ($row['training_type'] ?? '') === 'Leadership'
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        Leadership
                                    </option>


                                    <option
                                        value="Executive"
                                        <?= ($row['training_type'] ?? '') === 'Executive'
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        Executive
                                    </option>


                                    <option
                                        value="Foundational"
                                        <?= ($row['training_type'] ?? '') === 'Foundational'
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        Foundational
                                    </option>


                                    <option
                                        value="Others"
                                        <?= ($row['training_type'] ?? '') === 'Others'
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        Others
                                    </option>

                                </select>

                            </div>


                            <!-- =================================================
                                 CONDUCTED BY
                            ================================================== -->

                            <div class="col-md-4 mb-3">

                                <label>
                                    Conducted / Sponsored By
                                </label>

                                <input
                                    type="text"
                                    name="conducted_by"
                                    class="form-control"
                                    value="<?= htmlspecialchars(
                                        $row['conducted_by'] ?? ''
                                    ) ?>"
                                    required
                                >

                            </div>

                        </div>


                        <!-- =================================================
                             UPDATE BUTTON
                        ================================================== -->

                        <div class="text-end mt-2">

                            <button
                                type="submit"
                                name="update_training"
                                class="btn btn-primary"
                            >

                                <i class="fas fa-save me-1"></i>

                                Update

                            </button>

                        </div>


                    </form>

                </div>


                <?php $counter++; ?>


            <?php endwhile; ?>


        <?php else: ?>


            <!-- =================================================
                 NO RECORD
            ================================================== -->

            <div class="alert alert-info">

                <i class="fas fa-info-circle me-2"></i>

                No Learning & Development records found
                for this personnel.

            </div>


        <?php endif; ?>


        <!-- =================================================
             BACK BUTTON
        ================================================== -->

        <div class="text-end mt-3">

            <a
                href="personnel.php?id=<?= (int)$personnel_id ?>"
                class="btn btn-secondary"
            >

                <i class="fas fa-arrow-left me-1"></i>

                Back to Personnel

            </a>

        </div>


    </div>

</div>

</div>


<script>

/* =========================================================
   FORMAT DATE INPUT
   DD/MM/YYYY
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
   PRESENT TOGGLE
========================================================= */

document.querySelectorAll(".training-card").forEach(function(card){

    const dateTo =
        card.querySelector(".date-to");

    const presentCheckbox =
        card.querySelector(".date-to-present");


    if(!dateTo || !presentCheckbox){
        return;
    }


    /* DATE FORMAT */

    const dateFrom =
        card.querySelector(".date-from");

    if(dateFrom){
        formatDateInput(dateFrom);
    }

    formatDateInput(dateTo);


    /* PRESENT CHECKBOX */

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
