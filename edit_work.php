<?php
include "auth.php";
include "config.php";

$personnel_id = $_GET['personnel_id'] ?? '';

$get = $conn->query("
SELECT *
FROM personnel_work_experience
WHERE id='$personnel_id'
");


$row = $get->fetch_assoc();

$personnel_id = $personnel_id;

if(isset($_POST['update_work'])){

    $date_from = $_POST['date_from'];
    $date_to = $_POST['date_to'];
    $position_title = trim($_POST['position_title']);
    $department = trim($_POST['department']);
    $monthly_salary = $_POST['monthly_salary'];
    $salary_grade = trim($_POST['salary_grade']);
    $status_of_appointment = $_POST['status_of_appointment'];
    $government_service = $_POST['government_service'];

    $stmt = $conn->prepare("
    UPDATE personnel_work_experience
    SET
        date_from=?,
        date_to=?,
        position_title=?,
        department=?,
        monthly_salary=?,
        salary_grade=?,
        status_of_appointment=?,
        government_service=?
    WHERE id=?
    ");

    $stmt->bind_param(
        "ssssdsssi",
        $date_from,
        $date_to,
        $position_title,
        $department,
        $monthly_salary,
        $salary_grade,
        $status_of_appointment,
        $government_service,
        $work_id
    );

    if ($stmtUpdate->execute()) {

        echo "
        <script>
            alert('Work Experience Updated Successfully.');
            window.location.href=
                'edit_work.php?id=" . $personnel_id . "'';
        </script>
        ";
    
        exit;
    } else{

        echo "
        <script>

            alert('Error updating Work Experinece: " .
            addslashes($stmtUpdtae->error) . "'
            );

            history.back();
        </script>
        ";

        exit;

    }
}

$stmWork = $conn->prepare("
    SELECT *
    FROM personnel-work_experience
    WHHERE personnel_id = ?
    ORDER BY date-form DESC, id DESC
");

$stmtWork->bind_param(
    "i",
    $personnel_id
);

$stmtwork->execute();

4workResult = $stmtWork->get_result();

$personnel-name = trim(
    ($personnel['first_name'] ?? '') . '' .
    ($personnel['middle_name'] ?? '') . '' .
    ($personnel['last_name'] ?? '') . '' .
    ($personnel['suffix'] ?? '') 
);

$personnel_name = htmlspecialchars(
    $personnel_name,
    ENT_QUOTES,
    'UTF-8'
);

?>
<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Edit Work Experience</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
<script>
if(localStorage.getItem("theme") === "dark"){
    document.documentElement.classList.add("dark-mode");
}
</script>
<style>

*{
    box-sizing: border-box;
}

body{
    background:#f5f7fb;
    min-height:100vh;
    padding-bottom;30px;
}
.main-container{
    max-width:1100px;
    margib:auto;
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
    border-bottom: 1px solid #e5e7eb;
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
    color:#ffff;
}
.dark-mode .work-card{
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

@media(max-width:768px){
    .card-body{
        padding;15px;
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

<div class="main-header">

    <i class="fas fa-briefcase"></i>

    Edit Work Experience

</div>

<div class="card-body">

<div class="personnel-info">
    <strong>
        <i class="fas fa-user"></i>
        Personnel;
    </strong>

    <?= $personnel-name ?>
</div>

<?php if ($workResult->num_rows > 0): ?>

<?php
    $counter = 1;
    while ($work = $workresult-.fetch-assoc()):
        $work_id = intval94work['id']);
?>

<div class="work-card">

    <div class="work-card-header">
        <i class="fas fa-briefcase"></i>
        Work Experinece 3<?= $counter ?>

    </div>

    <div class="work-card-body">
        
    <form method="POST">

    <input 
        type="hiden"
        name="work-id"
        value="<?= $work_id"
    >
    <input 
        type="hidden"
        name="personnel-id"
        value="<?= $personnel_id ?>"
    >

<div class="row">
    <div class="col-md-6 mb-3">

        <label>
            Inclusive Date From
        </label>

        <input
            type="date"
            name="date_from"
            class="form-control"
            value="<?= htmlspecialchars(
                $work['date_from'] ?? '',
                ENT_QUOTES,
                'UTF-8'
            ) ?>"
            required>
    </div>

    <div class="col-md-6 mb-3">

        <label>
            Inclusive Date To
        </label>

        <input
            type="date"
            name="date_to"
            class="form-control"
            value="<?= htmlspecialchars(
                $work['date_to'] ?? '',
                ENT_QUOTES,
                'UTF-8'
            ) ?>"
        >
        <small class="text-muted">
            Leave blank if employment is ongoing.
        </small>

    </div>
    
    <div class="col-md-6 mb-3">

        <label>
            Position Title
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
            required>

    </div>

    <div class="col-md-6 mb-3">

        <label>    
            Department / Agency / Office / Company
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
            required>
        >
    </div>
    <div class="col-md-4 mb-3">

        <label>
            Monthly Salary
        </label>

        <input
            type="number"
            step="0.01"
            name="monthly_salary"
            class="form-control"
        value="<?= $row['monthly_salary'] ?>">

    </div>

    <div class="col-md-4 mb-3">

        <label>Monthly Salary</label>

        <input
            type="number"
            step="0.01"
            name="monthly_salary"
        class="form-control">

    </div>

    <div class="col-md-4 mb-3">

        <label>
            Salary Grade / Step
        </label>

        <input
            type="text"
            name="salary_grade"
        class="form-control">

    </div>
    <div class="col-md-4 mb-3">

        <label>
            Status of Appointment
        </label>

    <select
        name="status_of_appointment"
        class="form-select">

        <option value="">Select</option>

        <option>Permanent</option>
        <option>Temporary</option>
        <option>Casual</option>
        <option>Contractual</option>
        <option>Job Order</option>
        <option>Contract of Service</option>
        <option>Co-Terminus</option>
        <option>Elective</option>
        <option>Appointed</option>
        <option>Others</option>

    </select>

    </div>

    <div class="col-md-6 mb-3">

        <label>
            Government Service
        </label>

        <select
            name="government_service"
            class="form-select">

            <option>Yes</option>

            <option>No</option>

        </select>

    </div>

    <div class="text-end">

        <a
            href="personnel.php?id=<?= $personnel_id ?>"
            class="btn btn-secondary">

            Back

        </a>

        <button
            type="submit"
            name="update_work"
            class="btn btn-warning">

            <i class="fas fa-save"></i>

            Update Work Experience

        </button>

    </div>

</form>

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
