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
   CONVERT MYSQL DATE TO DD/MM/YYYY
========================================================= */

function formatDateDisplay($date)
{
    if (empty($date)) {
        return '';
    }

    $timestamp = strtotime($date);

    if (!$timestamp) {
        return '';
    }

    return date('d/m/Y', $timestamp);
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

$stmt->bind_param(
    "i",
    $personnel_id
);

$stmt->execute();

$personnel_result = $stmt->get_result();

if (
    !$personnel_result ||
    $personnel_result->num_rows === 0
) {
    die("Personnel record not found.");
}

$personnel = $personnel_result->fetch_assoc();


/* =========================================================
   UPDATE VOLUNTARY WORK
========================================================= */

if (isset($_POST['update_voluntary'])) {

    $voluntary_id = intval(
        $_POST['voluntary_id'] ?? 0
    );


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
       VALIDATION
    ===================================================== */

    if ($voluntary_id <= 0) {

        die("Invalid voluntary work record.");

    }


    if ($organization_name === '') {

        die("Organization Name is required.");

    }


    if ($organization_address === '') {

        die("Organization Address is required.");

    }


    if ($date_from_raw === '') {

        die("Inclusive Date From is required.");

    }


    if ($hours === '') {

        die("Number of Hours is required.");

    }


    if ($position === '') {

        die("Position / Nature of Work is required.");

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

            alert(
                'Invalid Inclusive Date From. Please use DD/MM/YYYY.'
            );

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
        $date_to_raw === ''
    ) {

        $date_to = null;

    } else {

        $date_to = convertDateToMysql(
            $date_to_raw
        );


        if ($date_to === false) {

            echo "
            <script>

                alert(
                    'Invalid Inclusive Date To. Please use DD/MM/YYYY or select Present.'
                );

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

            alert(
                'Inclusive Date To cannot be earlier than Inclusive Date From.'
            );

            history.back();

        </script>";

        exit;
    }


    /* =====================================================
       UPDATE SPECIFIC RECORD
    ===================================================== */

    $stmtUpdate = $conn->prepare("
        UPDATE personnel_voluntary_work
        SET
            organization_name = ?,
            organization_address = ?,
            date_from = ?,
            date_to = ?,
            hours = ?,
            position = ?
        WHERE id = ?
        AND personnel_id = ?
    ");


    $stmtUpdate->bind_param(
        "ssssdsii",
        $organization_name,
        $organization_address,
        $date_from,
        $date_to,
        $hours,
        $position,
        $voluntary_id,
        $personnel_id
    );


    if ($stmtUpdate->execute()) {

        echo "
        <script>

            alert(
                'Voluntary Work Updated Successfully.'
            );

            window.location.href =
                'edit_voluntary.php?id=" .
                $personnel_id .
                "';

        </script>";

        exit;

    } else {

        die(
            "Error updating voluntary work: " .
            htmlspecialchars($stmtUpdate->error)
        );

    }
}


/* =========================================================
   LOAD ALL VOLUNTARY WORK RECORDS
========================================================= */

$stmt = $conn->prepare("
    SELECT *
    FROM personnel_voluntary_work
    WHERE personnel_id = ?
    ORDER BY date_from DESC, id DESC
");

$stmt->bind_param(
    "i",
    $personnel_id
);

$stmt->execute();

$voluntary_result = $stmt->get_result();

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Edit Voluntary Work | JOPMIS</title>


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

    document.documentElement.classList.add(
        "dark-mode"
    );

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


.form-control{

    border-radius:10px;

    padding:10px 13px;

}


.form-control:focus{

    box-shadow:
        0 0 0 .2rem
        rgba(37,99,235,.15);

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


.work-card{

    border:1px solid #e2e8f0;

    border-radius:15px;

    padding:22px;

    margin-bottom:25px;

    background:#fff;

}


.work-title{

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
   DATE HELP
========================================================= */

.date-help{

    font-size:13px;

    color:#64748b;

    margin-top:5px;

}


.present-option{

    margin-top:8px;

}


.present-option input{

    cursor:pointer;

}


.present-option label{

    font-weight:500;

    cursor:pointer;

    margin-left:5px;

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


.dark-mode .work-card{

    background:#1e293b;

    border-color:#475569;

    color:#fff;

}


.dark-mode .work-title{

    color:#60a5fa;

    border-color:#475569;

}


.dark-mode label{

    color:#fff;

}


.dark-mode .form-control{

    background:#334155;

    color:#fff;

    border-color:#475569;

}


.dark-mode .form-control::placeholder{

    color:#cbd5e1;

}


.dark-mode .form-control:focus{

    background:#334155;

    color:#fff;

}


.dark-mode .date-help{

    color:#cbd5e1;

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


    .work-card{

        padding:16px;

    }

}

</style>

</head>


<body>


<div class="container py-4">


    <div class="card">


        <!-- =================================================
             MAIN HEADER
        ================================================== -->

        <div class="card-header bg-primary text-white">

            <i class="fas fa-handshake-angle me-2"></i>

            Edit Voluntary Work

        </div>


        <div class="card-body">


            <!-- =================================================
                 PERSONNEL INFORMATION
            ================================================== -->

            <div class="personnel-info">

                <div class="row">

                    <div class="col-md-6 mb-2">

                        <strong>
                            Employee ID:
                        </strong>

                        <?= htmlspecialchars(
                            $personnel['employee_id'] ?? ''
                        ) ?>

                    </div>


                    <div class="col-md-6 mb-2">

                        <strong>
                            Name:
                        </strong>

                        <?= htmlspecialchars(
                            trim(
                                ($personnel['first_name'] ?? '') .
                                ' ' .
                                ($personnel['middle_name'] ?? '') .
                                ' ' .
                                ($personnel['last_name'] ?? '') .
                                ' ' .
                                ($personnel['suffix'] ?? '')
                            )
                        ) ?>

                    </div>

                </div>

            </div>


            <?php if ($voluntary_result->num_rows > 0): ?>


                <?php

                $counter = 1;

                ?>


                <?php while($row = $voluntary_result->fetch_assoc()): ?>


                    <?php

                    /*
                     * FORMAT DATES FOR DISPLAY
                     */

                    $displayDateFrom =
                        formatDateDisplay(
                            $row['date_from'] ?? ''
                        );


                    $displayDateTo =
                        formatDateDisplay(
                            $row['date_to'] ?? ''
                        );


                    /*
                     * IF DATE TO IS NULL,
                     * THIS RECORD IS PRESENT
                     */

                    $isPresent =
                        empty($row['date_to']);

                    ?>


                    <!-- =========================================
                         VOLUNTARY WORK RECORD
                    ========================================== -->

                    <div class="work-card">


                        <div class="work-title">

                            <i class="fas fa-briefcase me-2"></i>

                            Voluntary Work #<?= $counter ?>

                        </div>


                        <form method="POST">


                            <!-- IDs -->

                            <input
                                type="hidden"
                                name="personnel_id"
                                value="<?= $personnel_id ?>"
                            >


                            <input
                                type="hidden"
                                name="voluntary_id"
                                value="<?= (int)$row['id'] ?>"
                            >


                            <div class="row">


                                <!-- =================================
                                     ORGANIZATION
                                ================================== -->

                                <div class="col-md-12 mb-3">

                                    <label>
                                        Name of Organization
                                    </label>


                                    <input
                                        type="text"
                                        name="organization_name"
                                        class="form-control"
                                        value="<?= htmlspecialchars(
                                            $row['organization_name'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                        required
                                    >

                                </div>


                                <!-- =================================
                                     ADDRESS
                                ================================== -->

                                <div class="col-md-12 mb-3">

                                    <label>
                                        Organization Address
                                    </label>


                                    <input
                                        type="text"
                                        name="organization_address"
                                        class="form-control"
                                        value="<?= htmlspecialchars(
                                            $row['organization_address'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                        required
                                    >

                                </div>


                                <!-- =================================
                                     DATE FROM
                                ================================== -->

                                <div class="col-md-6 mb-3">

                                    <label>
                                        Inclusive Date From
                                    </label>


                                    <input
                                        type="text"
                                        name="date_from"
                                        class="form-control date-input"
                                        value="<?= htmlspecialchars(
                                            $displayDateFrom,
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
                                        class="form-control date-input date-to-input"
                                        value="<?= $isPresent
                                            ? 'Present'
                                            : htmlspecialchars(
                                                $displayDateTo,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                        placeholder="DD/MM/YYYY"
                                        maxlength="10"
                                        <?= $isPresent
                                            ? 'readonly'
                                            : '' ?>
                                    >


                                    <div class="present-option">

                                        <input
                                            type="checkbox"
                                            name="date_to_present"
                                            value="1"
                                            class="present-checkbox"
                                            <?= $isPresent
                                                ? 'checked'
                                                : '' ?>
                                        >


                                        <label>
                                            Present
                                        </label>

                                    </div>


                                    <div class="date-help">

                                        Enter DD/MM/YYYY or select
                                        Present if ongoing.

                                    </div>

                                </div>


                                <!-- =================================
                                     HOURS
                                ================================== -->

                                <div class="col-md-6 mb-3">

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
                                            $row['hours'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                        required
                                    >

                                </div>


                                <!-- =================================
                                     POSITION
                                ================================== -->

                                <div class="col-md-6 mb-3">

                                    <label>
                                        Position / Nature of Work
                                    </label>


                                    <input
                                        type="text"
                                        name="position"
                                        class="form-control"
                                        value="<?= htmlspecialchars(
                                            $row['position'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                        required
                                    >

                                </div>


                            </div>


                            <!-- =====================================
                                 BUTTON
                            ====================================== -->

                            <div class="text-end mt-2">

                                <button
                                    type="submit"
                                    name="update_voluntary"
                                    class="btn btn-primary"
                                >

                                    <i class="fas fa-save me-1"></i>

                                    Update

                                </button>

                            </div>


                        </form>


                    </div>


                    <?php

                    $counter++;

                    ?>


                <?php endwhile; ?>


            <?php else: ?>


                <div class="alert alert-info">

                    <i class="fas fa-info-circle me-2"></i>

                    No voluntary work records found
                    for this personnel.

                </div>


            <?php endif; ?>


            <!-- =================================================
                 BACK BUTTON
            ================================================== -->

            <div class="text-end mt-3">

                <a
                    href="personnel.php?id=<?= $personnel_id ?>"
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
   DD/MM/YYYY FORMATTER
========================================================= */

function formatDateInput(input){

    input.addEventListener(
        "input",
        function(){

            let value =
                this.value.replace(/\D/g, "");


            /*
             * Maximum 8 digits:
             * DDMMYYYY
             */

            if(value.length > 8){

                value =
                    value.substring(0, 8);

            }


            /*
             * DD/MM/YYYY
             */

            if(value.length >= 5){

                value =
                    value.substring(0, 2) +
                    "/" +
                    value.substring(2, 4) +
                    "/" +
                    value.substring(4);

            }
            else if(value.length >= 3){

                value =
                    value.substring(0, 2) +
                    "/" +
                    value.substring(2);

            }


            this.value = value;

        }
    );

}


/* =========================================================
   APPLY DATE FORMATTER
========================================================= */

document
    .querySelectorAll(".date-input")
    .forEach(function(input){

        formatDateInput(input);

    });


/* =========================================================
   PRESENT CHECKBOX
========================================================= */

document
    .querySelectorAll(".present-checkbox")
    .forEach(function(checkbox){

        checkbox.addEventListener(
            "change",
            function(){

                const form =
                    this.closest("form");

                const dateTo =
                    form.querySelector(
                        ".date-to-input"
                    );


                if(this.checked){

                    dateTo.value =
                        "Present";

                    dateTo.readOnly =
                        true;

                }
                else{

                    dateTo.value =
                        "";

                    dateTo.readOnly =
                        false;

                    dateTo.focus();

                }

            }
        );

    });


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
