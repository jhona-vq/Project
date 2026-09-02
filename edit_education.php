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

if ($personnelResult->num_rows === 0) {
    die("Personnel record not found.");
}

$personnel = $personnelResult->fetch_assoc();


/* =========================================================
   LOAD EXISTING EDUCATION
========================================================= */

$education = [];

$stmtEducation = $conn->prepare("
    SELECT *
    FROM personnel_education
    WHERE personnel_id = ?
    ORDER BY id ASC
");

$stmtEducation->bind_param("i", $personnel_id);
$stmtEducation->execute();

$getEducation = $stmtEducation->get_result();

$secondaryCount = 0;

while ($row = $getEducation->fetch_assoc()) {

    if ($row['level'] === "Elementary") {

        $education['Elementary'] = $row;

    } elseif ($row['level'] === "Secondary") {

        /*
         * Since JHS and SHS are both stored as "Secondary",
         * the first record is treated as JHS and the second as SHS.
         */

        if ($secondaryCount == 0) {

            $education['Secondary_JHS'] = $row;

        } else {

            $education['Secondary_SHS'] = $row;

        }

        $secondaryCount++;

    } elseif ($row['level'] === "Vocational/Trade Course") {

        $education['Vocational/Trade Course'] = $row;

    } elseif ($row['level'] === "College") {

        $education['College'] = $row;

    } elseif ($row['level'] === "Graduate Studies") {

        $education['Graduate Studies'] = $row;
    }
}


/* =========================================================
   DEFAULT EMPTY DATA
========================================================= */

$defaultEducation = [
    'school_name'   => '',
    'degree'        => '',
    'period_from'   => '',
    'period_to'     => '',
    'highest_level' => '',
    'year_graduated'=> '',
    'scholarship'   => ''
];


/* =========================================================
   UPDATE EDUCATION
========================================================= */

if (isset($_POST['update_education'])) {

    $levels = [

        "Elementary",
        "Secondary_JHS",
        "Secondary_SHS",
        "Vocational/Trade Course",
        "College",
        "Graduate Studies"

    ];

    foreach ($levels as $level) {

        $school = trim($_POST['school_name'][$level] ?? '');
        $degree = trim($_POST['degree'][$level] ?? '');
        $from = trim($_POST['period_from'][$level] ?? '');
        $to = trim($_POST['period_to'][$level] ?? '');
        $highest = trim($_POST['highest_level'][$level] ?? '');
        $graduated = trim($_POST['year_graduated'][$level] ?? '');
        $scholarship = trim($_POST['scholarship'][$level] ?? '');


        /* =========================================
           DETERMINE DATABASE LEVEL
        ========================================= */

        if (
            $level === "Secondary_JHS" ||
            $level === "Secondary_SHS"
        ) {

            $saveLevel = "Secondary";

        } else {

            $saveLevel = $level;
        }


        /* =========================================
           CHECK IF RECORD ALREADY EXISTS
        ========================================= */

        if (isset($education[$level])) {

            $education_id = intval($education[$level]['id']);

            /*
             * UPDATE EXISTING RECORD
             */

            $stmt = $conn->prepare("
                UPDATE personnel_education
                SET
                    school_name = ?,
                    degree = ?,
                    period_from = ?,
                    period_to = ?,
                    highest_level = ?,
                    year_graduated = ?,
                    scholarship = ?
                WHERE id = ?
                AND personnel_id = ?
            ");

            $stmt->bind_param(
                "sssssssii",
                $school,
                $degree,
                $from,
                $to,
                $highest,
                $graduated,
                $scholarship,
                $education_id,
                $personnel_id
            );

            $stmt->execute();

        } else {

            /*
             * INSERT NEW RECORD ONLY IF
             * THE USER ACTUALLY ENTERED SOMETHING
             */

            if (
                $school !== '' ||
                $degree !== '' ||
                $from !== '' ||
                $to !== '' ||
                $highest !== '' ||
                $graduated !== '' ||
                $scholarship !== ''
            ) {

                $stmt = $conn->prepare("
                    INSERT INTO personnel_education
                    (
                        personnel_id,
                        level,
                        school_name,
                        degree,
                        period_from,
                        period_to,
                        highest_level,
                        year_graduated,
                        scholarship
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt->bind_param(
                    "issssssss",
                    $personnel_id,
                    $saveLevel,
                    $school,
                    $degree,
                    $from,
                    $to,
                    $highest,
                    $graduated,
                    $scholarship
                );

                $stmt->execute();
            }
        }
    }


    /* =========================================
       SUCCESS
    ========================================= */

    echo "
    <script>
        alert('Educational Background Updated Successfully.');
        window.location.href = 'personnel.php?id=" . $personnel_id . "';
    </script>
    ";

    exit;
}


/* =========================================================
   HELPER FUNCTION
========================================================= */

function eduValue($education, $level, $field, $defaultEducation)
{
    return htmlspecialchars(
        $education[$level][$field] ?? $defaultEducation[$field] ?? '',
        ENT_QUOTES,
        'UTF-8'
    );
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Edit Educational Background</title>

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

*{
    box-sizing:border-box;
}

body{
    background:#f5f7fb;
    color:#0f172a;
    transition:.3s;
}

.container{
    max-width:1100px;
}

.main-card{
    border:none;
    border-radius:18px;
    box-shadow:0 8px 25px rgba(0,0,0,.08);
    overflow:hidden;
}

.card-header{
    font-size:20px;
    font-weight:600;
    padding:20px 25px;
}

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
}

.level-card{
    border-radius:15px;
    margin-bottom:25px;
    border:1px solid #e5e7eb;
    overflow:hidden;
}

.level-title{
    background:#2563eb;
    color:white;
    padding:15px 20px;
    font-size:18px;
    font-weight:600;
}

.level-body{
    padding:25px;
}

.section-subtitle{
    font-size:17px;
    font-weight:600;
    color:#2563eb;
    margin-bottom:15px;
}

label{
    font-weight:500;
    margin-bottom:6px;
}

.form-control{
    min-height:44px;
    border-radius:9px;
}

.action-buttons{
    display:flex;
    justify-content:space-between;
    gap:10px;
    margin-top:30px;
    padding-top:20px;
    border-top:1px solid #e5e7eb;
}

.action-buttons .btn{
    min-width:130px;
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

.dark-mode .level-card{
    border-color:#475569;
}

.dark-mode .level-body{
    background:#1e293b;
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

    .personnel-info{
        padding:15px;
    }

    .level-body{
        padding:18px;
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

<div class="card-header bg-primary text-white">

    <i class="fas fa-graduation-cap me-2"></i>

    Edit Educational Background

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


<div class="card-body p-4">


<form method="POST">

<!-- IMPORTANT -->
<input
type="hidden"
name="personnel_id"
value="<?= $personnel_id ?>">


<?php

$levels = [

    "Elementary",
    "Secondary",
    "Vocational/Trade Course",
    "College",
    "Graduate Studies"

];


foreach($levels as $level):


/* =====================================================
   SECONDARY
===================================================== */

if($level === "Secondary"):

?>

<div class="level-card">

    <div class="level-title">

        <i class="fas fa-school me-2"></i>

        Secondary Education

    </div>


    <div class="level-body">


        <!-- ============================
             JUNIOR HIGH SCHOOL
        ============================= -->

        <div class="section-subtitle">

            Junior High School

        </div>


        <div class="row">

            <div class="col-md-6 mb-3">

                <label>School Name</label>

                <input
                type="text"
                name="school_name[Secondary_JHS]"
                class="form-control"
                value="<?= eduValue(
                    $education,
                    'Secondary_JHS',
                    'school_name',
                    $defaultEducation
                ) ?>">

            </div>


            <div class="col-md-6 mb-3">

                <label>Basic Education / Degree / Course</label>

                <input
                type="text"
                name="degree[Secondary_JHS]"
                class="form-control"
                value="<?= eduValue(
                    $education,
                    'Secondary_JHS',
                    'degree',
                    $defaultEducation
                ) ?>">

            </div>


            <div class="col-md-3 mb-3">

                <label>Period From</label>

                <input
                type="date"
                name="period_from[Secondary_JHS]"
                class="form-control"
                value="<?= eduValue(
                    $education,
                    'Secondary_JHS',
                    'period_from',
                    $defaultEducation
                ) ?>">

            </div>


            <div class="col-md-3 mb-3">

                <label>Period To</label>

                <input
                type="date"
                name="period_to[Secondary_JHS]"
                class="form-control"
                value="<?= eduValue(
                    $education,
                    'Secondary_JHS',
                    'period_to',
                    $defaultEducation
                ) ?>">

            </div>


            <div class="col-md-3 mb-3">

                <label>Year Graduated</label>

                <input
                type="text"
                name="year_graduated[Secondary_JHS]"
                class="form-control"
                value="<?= eduValue(
                    $education,
                    'Secondary_JHS',
                    'year_graduated',
                    $defaultEducation
                ) ?>">

            </div>


            <div class="col-md-3 mb-3">

                <label>Scholarship / Honors</label>

                <input
                type="text"
                name="scholarship[Secondary_JHS]"
                class="form-control"
                value="<?= eduValue(
                    $education,
                    'Secondary_JHS',
                    'scholarship',
                    $defaultEducation
                ) ?>">

            </div>

        </div>


        <hr>


        <!-- ============================
             SENIOR HIGH SCHOOL
        ============================= -->

        <div class="section-subtitle">

            Senior High School

        </div>


        <div class="row">

            <div class="col-md-6 mb-3">

                <label>School Name</label>

                <input
                type="text"
                name="school_name[Secondary_SHS]"
                class="form-control"
                value="<?= eduValue(
                    $education,
                    'Secondary_SHS',
                    'school_name',
                    $defaultEducation
                ) ?>">

            </div>


            <div class="col-md-6 mb-3">

                <label>Track / Strand</label>

                <input
                type="text"
                name="degree[Secondary_SHS]"
                class="form-control"
                value="<?= eduValue(
                    $education,
                    'Secondary_SHS',
                    'degree',
                    $defaultEducation
                ) ?>">

            </div>


            <div class="col-md-3 mb-3">

                <label>Period From</label>

                <input
                type="date"
                name="period_from[Secondary_SHS]"
                class="form-control"
                value="<?= eduValue(
                    $education,
                    'Secondary_SHS',
                    'period_from',
                    $defaultEducation
                ) ?>">

            </div>


            <div class="col-md-3 mb-3">

                <label>Period To</label>

                <input
                type="date"
                name="period_to[Secondary_SHS]"
                class="form-control"
                value="<?= eduValue(
                    $education,
                    'Secondary_SHS',
                    'period_to',
                    $defaultEducation
                ) ?>">

            </div>


            <div class="col-md-3 mb-3">

                <label>Year Graduated</label>

                <input
                type="text"
                name="year_graduated[Secondary_SHS]"
                class="form-control"
                value="<?= eduValue(
                    $education,
                    'Secondary_SHS',
                    'year_graduated',
                    $defaultEducation
                ) ?>">

            </div>


            <div class="col-md-3 mb-3">

                <label>Scholarship / Honors</label>

                <input
                type="text"
                name="scholarship[Secondary_SHS]"
                class="form-control"
                value="<?= eduValue(
                    $education,
                    'Secondary_SHS',
                    'scholarship',
                    $defaultEducation
                ) ?>">

            </div>

        </div>

    </div>

</div>


<?php

continue;

endif;


/* =====================================================
   OTHER EDUCATION LEVELS
===================================================== */

?>

<div class="level-card">


    <div class="level-title">

        <i class="fas fa-school me-2"></i>

        <?= htmlspecialchars(
            $level,
            ENT_QUOTES,
            'UTF-8'
        ) ?>

    </div>


    <div class="level-body">


        <div class="row">


            <div class="col-md-6 mb-3">

                <label>School Name</label>

                <input
                type="text"
                name="school_name[<?= htmlspecialchars($level, ENT_QUOTES, 'UTF-8') ?>]"
                class="form-control"
                value="<?= eduValue(
                    $education,
                    $level,
                    'school_name',
                    $defaultEducation
                ) ?>">

            </div>


            <div class="col-md-6 mb-3">

                <label>Basic Education / Degree / Course</label>

                <input
                type="text"
                name="degree[<?= htmlspecialchars($level, ENT_QUOTES, 'UTF-8') ?>]"
                class="form-control"
                value="<?= eduValue(
                    $education,
                    $level,
                    'degree',
                    $defaultEducation
                ) ?>">

            </div>


            <div class="col-md-3 mb-3">

                <label>Period From</label>

                <input
                type="date"
                name="period_from[<?= htmlspecialchars($level, ENT_QUOTES, 'UTF-8') ?>]"
                class="form-control"
                value="<?= eduValue(
                    $education,
                    $level,
                    'period_from',
                    $defaultEducation
                ) ?>">

            </div>


            <div class="col-md-3 mb-3">

                <label>Period To</label>

                <input
                type="date"
                name="period_to[<?= htmlspecialchars($level, ENT_QUOTES, 'UTF-8') ?>]"
                class="form-control"
                value="<?= eduValue(
                    $education,
                    $level,
                    'period_to',
                    $defaultEducation
                ) ?>">

            </div>


            <div class="col-md-3 mb-3">

                <label>Highest Level / Units Earned</label>

                <input
                type="text"
                name="highest_level[<?= htmlspecialchars($level, ENT_QUOTES, 'UTF-8') ?>]"
                class="form-control"
                value="<?= eduValue(
                    $education,
                    $level,
                    'highest_level',
                    $defaultEducation
                ) ?>">

            </div>


            <div class="col-md-3 mb-3">

                <label>Year Graduated</label>

                <input
                type="text"
                name="year_graduated[<?= htmlspecialchars($level, ENT_QUOTES, 'UTF-8') ?>]"
                class="form-control"
                value="<?= eduValue(
                    $education,
                    $level,
                    'year_graduated',
                    $defaultEducation
                ) ?>">

            </div>


            <div class="col-md-12 mb-3">

                <label>Scholarship / Academic Honors</label>

                <input
                type="text"
                name="scholarship[<?= htmlspecialchars($level, ENT_QUOTES, 'UTF-8') ?>]"
                class="form-control"
                value="<?= eduValue(
                    $education,
                    $level,
                    'scholarship',
                    $defaultEducation
                ) ?>">

            </div>


        </div>

    </div>

</div>


<?php endforeach; ?>


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
    name="update_education"
    class="btn btn-primary">

        <i class="fas fa-save me-1"></i>

        Update Educational Background

    </button>


</div>


</form>


</div>

</div>

</div>


<script>

document.addEventListener("DOMContentLoaded", function(){

    if(localStorage.getItem("theme") === "dark"){

        document.documentElement.classList.add("dark-mode");

        document.body.classList.add("dark-mode");

    }

});

</script>


</body>

</html>
