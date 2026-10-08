<?php
include "auth.php";
include "config.php";

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if($page < 1) $page = 1;

$start = ($page - 1) * $limit;

// total records
$total_result = $conn->query("SELECT COUNT(*) as total FROM performance");
$total_records = $total_result->fetch_assoc()['total'];

$total_pages = ceil($total_records / $limit);

if(isset($_POST['save_evaluation'])){

    $employee_id = trim($_POST['employee_id'] ?? '');
    $employee_name = trim($_POST['employee_name'] ?? '');
    $evaluation_period = trim($_POST['evaluation_period'] ?? '');
    $rating = (float)($_POST['rating'] ?? 0);
    $evaluator = trim($_POST['evaluator'] ?? '');
    $comments = trim($_POST['comments'] ?? '');


/* =========================================================
   VALIDATE EVALUATION PERIOD
   ========================================================= */

$allowedPeriods = [
    '2026-Q1',
    '2026-Q2',
    '2026-Q3',
    '2026-Q4',

    '2027-Q1',
    '2027-Q2',
    '2027-Q3',
    '2027-Q4',

    '2028-Q1',
    '2028-Q2',
    '2028-Q3',
    '2028-Q4'
];

if(!in_array($evaluation_period, $allowedPeriods, true)){

    echo "
    <script>
        alert('Please select a valid Evaluation Period.');
        window.location='performance.php';
    </script>";

    exit();
}

    /* =========================================================
       VALIDATE RATING
       ========================================================= */

    if($rating < 1 || $rating > 5){

        echo "
        <script>
            alert('Please select a valid rating from 1 to 5.');
            window.location='performance.php';
        </script>";

        exit();
    }


    /* =========================================================
       GET ADJECTIVAL RATING
       ========================================================= */

    /* =========================================================
   GET ADJECTIVAL RATING
   ========================================================= */

if($rating >= 4.50){

    $status = "Outstanding";

}
elseif($rating >= 3.50){

    $status = "Very Satisfactory";

}
elseif($rating >= 2.50){

    $status = "Satisfactory";

}
elseif($rating >= 1.50){

    $status = "Unsatisfactory";

}
else{

    $status = "Poor";

}


    /* =========================================================
       CHECK DUPLICATE BEFORE INSERT
       One employee cannot have the same evaluation period twice.
       ========================================================= */

    $checkStmt = $conn->prepare("
        SELECT id
        FROM performance
        WHERE employee_id = ?
        AND evaluation_period = ?
        LIMIT 1
    ");

    $checkStmt->bind_param(
        "ss",
        $employee_id,
        $evaluation_period
    );

    $checkStmt->execute();

    $check = $checkStmt->get_result();


    if($check->num_rows > 0){

        echo "
        <script>
            alert('Evaluation already exists for this employee and evaluation period.');
            window.location='performance.php';
        </script>";

        exit();
    }


    /* =========================================================
       INSERT PERFORMANCE RECORD
       ========================================================= */

    $stmt = $conn->prepare("
        INSERT INTO performance
        (
            employee_id,
            employee_name,
            evaluation_period,
            rating,
            evaluator,
            comments,
            status
        )
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "sssdsss",
        $employee_id,
        $employee_name,
        $evaluation_period,
        $rating,
        $evaluator,
        $comments,
        $status
    );


    if(!$stmt->execute()){

        die(
            "Failed to save evaluation: " .
            htmlspecialchars($stmt->error)
        );

    }


    echo "
    <script>
        alert('Performance evaluation saved successfully.');
        window.location='performance.php';
    </script>";

    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Performance Monitoring | JOPMIS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

<link rel="stylesheet" href="assets/css/theme.css">

<style>

body{
    background:#f1f5f9;
    font-family:'Segoe UI',sans-serif;
}

.wrapper{
    display:flex;
}

/* SIDEBAR */
.sidebar{
    width:270px;
    min-height:100vh;
    background:linear-gradient(180deg,#020617,#0f172a,#1e3a8a);
    color:white;
    position:fixed;
}

.logo{
    padding:25px;
    text-align:center;
    border-bottom:1px solid rgba(255,255,255,.1);
}

.sidebar ul{
    list-style:none;
    padding:0;
    margin:0;
}

.sidebar ul li a{
    display:block;
    color:white;
    text-decoration:none;
    padding:15px 25px;
    transition:.3s;
}

.sidebar ul li a:hover{
    background:rgba(255,255,255,.1);
    padding-left:35px;
}

.sidebar ul li a i{
    width:25px;
}

/* MAIN */
.main{
    margin-left:270px;
    width:100%;
}

.topbar{
    background:white;
    padding:15px 25px;
    box-shadow:0 2px 10px rgba(0,0,0,.05);
}

.page-content{
    padding:25px;
}

/* CARDS */
.card{
    border:none;
    border-radius:15px;
    box-shadow:0 5px 15px rgba(0,0,0,.08);
}

/* TABLE */
.table-card{
    border:none;
    border-radius:15px;
    box-shadow:0 5px 15px rgba(0,0,0,.08);
}

.badge-good{ background:#16a34a; }
.badge-mid{ background:#f59e0b; }
.badge-low{ background:#dc2626; }

@media(max-width:991px){

.sidebar{
    width:270px;
    min-height:100vh;
    position:fixed;
    top:0;
    left:0;
    z-index:1000;
    transform:translateX(-100%);
    transition:.3s;
}

.sidebar.active{
    transform:translateX(0);
}

.main{
    margin-left:0;
    width:100%;
}

}
.sidebar-overlay{
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,.5);
    z-index:999;
    display:none;
}

.sidebar-overlay.show{
    display:block;
}

</style>
</head>

<body>

<div id="sidebarOverlay" class="sidebar-overlay"></div>

<div class="wrapper">

<!-- SIDEBAR -->
<div class="sidebar">

<div class="logo">
<h4>JOPMIS</h4>
<small>COMELEC - CAR</small>
</div>

<ul>
<li><a href="dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a></li>
<li><a href="personnel.php"><i class="fas fa-users"></i> Personnel</a></li>
<li><a href="contracts.php"><i class="fas fa-file-signature"></i> Contracts</a></li>
<li><a href="documents.php"><i class="fas fa-folder-open"></i> Documents</a></li>
<li><a href="performance.php"><i class="fas fa-star"></i> Performance</a></li>
<li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
<li><a href="users.php"><i class="fas fa-user-cog"></i> User Management</a></li>
<li><a href="settings.php"><i class="fas fa-cogs"></i> Settings</a></li>
<li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
</ul>

</div>

<!-- MAIN -->
<div class="main">

<!-- TOPBAR -->
<div class="topbar d-flex justify-content-between align-items-center">

    <div class="d-flex align-items-center">

        <button
            id="sidebarToggle"
            class="btn btn-primary me-3 d-lg-none">

            <i class="fas fa-bars"></i>

        </button>

        <h4 class="mb-0">Performance Monitoring</h4>

    </div>

    <div class="d-flex align-items-center">

        <button
            id="themeToggle"
            class="btn btn-link me-3">

            <i id="themeIcon" class="fas fa-moon"></i>

        </button>

    </div>

</div>

<div class="page-content">
<?php if(
    isset($_SESSION['role']) &&
    ($_SESSION['role']=='System Administrator' || $_SESSION['role']=='HR Administrator')
){ ?>
    <button
        class="btn btn-primary mb-4"
        data-bs-toggle="modal"
        data-bs-target="#addEval">

        <i class="fas fa-plus"></i>
        Add Evaluation
    </button>
<?php } ?>

<!-- PERFORMANCE SUMMARY -->
<div class="row g-4">

<div class="col-md-4">
<div class="card p-3">
<h6>Outstanding Personnel</h6>
<?php
$outstanding =
$conn->query("
SELECT COUNT(*) total
FROM performance
WHERE status='Outstanding'
")->fetch_assoc()['total'];
?>

<h3><?= $outstanding; ?></h3>
</div>
</div>

<div class="col-md-4">
<div class="card p-3">
<h6>Need Improvement</h6>
<?php
$poor =
$conn->query("
SELECT COUNT(*) total
FROM performance
WHERE status='Poor'
")->fetch_assoc()['total'];
?>

<h3><?= $poor; ?></h3>
</div>
</div>

<div class="col-md-4">
<div class="card p-3">
<h6>Evaluated This Year</h6>
<?php
$total =
$conn->query("
SELECT COUNT(*) total
FROM performance
WHERE YEAR(created_at)=YEAR(CURDATE())
")->fetch_assoc()['total'];
?>

<h3><?= $total; ?></h3>
</div>
</div>

</div>

<!-- PERFORMANCE TABLE -->
<div class="card table-card mt-4 p-3">

<h5>Performance Records</h5>

<div class="table-responsive mt-3">

<table class="table table-bordered">

<thead class="table-dark">

<tr>

    <th rowspan="2">Employee</th>

    <th rowspan="2">
        Evaluation Period
    </th>

    <th colspan="2" class="text-center">
        Rating
    </th>

    <th rowspan="2">
        Evaluator
    </th>

    <th rowspan="2">
        Comments
    </th>

    <th rowspan="2">
        Action
    </th>

</tr>

<tr>

    <th class="text-center">
        Numerical
    </th>

    <th class="text-center">
        Adjectival
    </th>

</tr>

</thead>

<tbody>

<?php

$result = $conn->query("
SELECT *
FROM performance
ORDER BY id DESC
LIMIT $start, $limit
");

while($row = $result->fetch_assoc()){

    $ratingValue = (float)$row['rating'];

if($ratingValue >= 4.50){
    $adjectivalRating = "Outstanding";
    $badge = "success";
}
elseif($ratingValue >= 3.50){
    $adjectivalRating = "Very Satisfactory";
    $badge = "primary";
}
elseif($ratingValue >= 2.50){
    $adjectivalRating = "Satisfactory";
    $badge = "info";
}
elseif($ratingValue >= 1.50){
    $adjectivalRating = "Unsatisfactory";
    $badge = "warning";
}
else{
    $adjectivalRating = "Poor";
    $badge = "danger";
}
?>

<tr>

<td><?= $row['employee_name']; ?></td>

<td><?= $row['evaluation_period']; ?></td>

<td class="text-center">
    <strong class="fs-5">
        <?php
        $displayRating = number_format(
            (float)$row['rating'],
            2,
            '.',
            ''
        );

        $displayRating = rtrim(
            rtrim($displayRating, '0'),
            '.'
        );

        echo $displayRating;
        ?>
    </strong>
</td>

<td class="text-center">
    <span class="badge bg-<?= $badge; ?>">
        <?= htmlspecialchars($adjectivalRating); ?>
    </span>
</td>

<td><?= $row['evaluator']; ?></td>

<td><?= $row['comments']; ?></td>

<td>

<a href="view_performance.php?id=<?= $row['id']; ?>"
class="btn btn-info btn-sm">
<i class="fas fa-eye"></i>
</a>

<?php if($_SESSION['role'] === 'System Administrator' || $_SESSION['role'] === 'HR Administrator'){ ?>

<a href="edit_performance.php?id=<?= $row['id']; ?>"
class="btn btn-warning btn-sm">
<i class="fas fa-edit"></i>
</a>

<a href="delete_performance.php?id=<?= $row['id']; ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Delete evaluation?')">
<i class="fas fa-trash"></i>
</a>

<?php } ?>
</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>
<nav class="mt-3">
<ul class="pagination justify-content-center">

<?php if($page > 1){ ?>
<li class="page-item">
<a class="page-link" href="?page=<?= $page-1; ?>&limit=<?= $limit; ?>">
Previous
</a>
</li>
<?php } ?>

<?php for($i=1; $i <= $total_pages; $i++){ ?>
<li class="page-item <?= ($i==$page)?'active':''; ?>">
<a class="page-link" href="?page=<?= $i; ?>&limit=<?= $limit; ?>">
<?= $i; ?>
</a>
</li>
<?php } ?>

<?php if($page < $total_pages){ ?>
<li class="page-item">
<a class="page-link" href="?page=<?= $page+1; ?>&limit=<?= $limit; ?>">
Next
</a>
</li>
<?php } ?>

</ul>
</nav>

</div>

<!-- REPORTS -->
<div class="row mt-4">

<div class="col-lg-6">
<div class="card p-3">
<h5>Outstanding Personnel</h5>
<ul>

<?php

$result = $conn->query("
SELECT employee_name
FROM performance
WHERE status='Outstanding'
");

while($row=$result->fetch_assoc()){

echo "<li>".$row['employee_name']."</li>";

}

?>

</ul>
</div>
</div>

<div class="col-lg-6">
<div class="card p-3">
<h5>Personnel Requiring Improvement</h5>
<ul>

<?php

$result = $conn->query("
SELECT employee_name
FROM performance
WHERE status='Poor'
");

while($row=$result->fetch_assoc()){

echo "<li>".$row['employee_name']."</li>";

}

?>

</ul>
</div>
</div>

</div>

<!-- =========================================================
     HISTORICAL RATINGS
========================================================= -->

<div class="card mt-4 p-3">

    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">

        <div>
            <h5 class="mb-1">
                <i class="fas fa-chart-line me-2"></i>
                Historical Ratings
            </h5>

            <small class="text-muted">
                Annual performance rating history
            </small>
        </div>

    </div>


    <div class="table-responsive">

        <table class="table table-bordered table-hover align-middle">

            <thead class="table-dark">

                <tr>

                    <th>Employee</th>

                    <th class="text-center">
                        2026
                    </th>

                    <th class="text-center">
                        2027
                    </th>

                    <th class="text-center">
                        2028
                    </th>

                </tr>

            </thead>


            <tbody>

            <?php

            $historicalResult = $conn->query("

                SELECT

                    employee_id,

                    employee_name,

                    AVG(
                        CASE
                            WHEN evaluation_period LIKE '2026-%'
                            THEN rating
                        END
                    ) AS rating_2026,

                    AVG(
                        CASE
                            WHEN evaluation_period LIKE '2027-%'
                            THEN rating
                        END
                    ) AS rating_2027,

                    AVG(
                        CASE
                            WHEN evaluation_period LIKE '2028-%'
                            THEN rating
                        END
                    ) AS rating_2028

                FROM performance

                GROUP BY
                    employee_id,
                    employee_name

                ORDER BY
                    employee_name ASC

            ");


            function getAdjectivalRating($rating){

                if($rating === null){
                    return '';
                }

                $rating = (float)$rating;

                if($rating >= 4.5){
                    return 'Outstanding';
                }
                elseif($rating >= 3.5){
                    return 'Very Satisfactory';
                }
                elseif($rating >= 2.5){
                    return 'Satisfactory';
                }
                elseif($rating >= 1.5){
                    return 'Unsatisfactory';
                }
                else{
                    return 'Poor';
                }

            }


            function getRatingBadge($rating){

                if($rating === null){
                    return 'secondary';
                }

                $rating = (float)$rating;

                if($rating >= 4.5){
                    return 'success';
                }
                elseif($rating >= 3.5){
                    return 'primary';
                }
                elseif($rating >= 2.5){
                    return 'info';
                }
                elseif($rating >= 1.5){
                    return 'warning';
                }
                else{
                    return 'danger';
                }

            }


            if($historicalResult && $historicalResult->num_rows > 0){

                while($history = $historicalResult->fetch_assoc()){

            ?>

                <tr>

                    <td class="fw-semibold">

                        <?= htmlspecialchars(
                            $history['employee_name']
                        ); ?>

                    </td>


                    <!-- 2026 -->

                    <td class="text-center">

                        <?php if($history['rating_2026'] !== null): ?>

                            <?php
                            $rating2026 =
                                (float)$history['rating_2026'];

                            $status2026 =
                                getAdjectivalRating($rating2026);

                            $badge2026 =
                                getRatingBadge($rating2026);
                            ?>

                            <span class="badge bg-<?= $badge2026; ?>">

                                <?= number_format(
                                    $rating2026,
                                    5
                                ); ?>

                            </span>

                            <br>

                            <small>
                                <?= $status2026; ?>
                            </small>

                        <?php else: ?>

                            <span class="text-muted">
                                —
                            </span>

                        <?php endif; ?>

                    </td>


                    <!-- 2027 -->

                    <td class="text-center">

                        <?php if($history['rating_2027'] !== null): ?>

                            <?php
                            $rating2027 =
                                (float)$history['rating_2027'];

                            $status2027 =
                                getAdjectivalRating($rating2027);

                            $badge2027 =
                                getRatingBadge($rating2027);
                            ?>

                            <span class="badge bg-<?= $badge2027; ?>">

                                <?= number_format(
                                    $rating2027,
                                    5
                                ); ?>

                            </span>

                            <br>

                            <small>
                                <?= $status2027; ?>
                            </small>

                        <?php else: ?>

                            <span class="text-muted">
                                —
                            </span>

                        <?php endif; ?>

                    </td>


                    <!-- 2028 -->

                    <td class="text-center">

                        <?php if($history['rating_2028'] !== null): ?>

                            <?php
                            $rating2028 =
                                (float)$history['rating_2028'];

                            $status2028 =
                                getAdjectivalRating($rating2028);

                            $badge2028 =
                                getRatingBadge($rating2028);
                            ?>

                            <span class="badge bg-<?= $badge2028; ?>">

                                <?= number_format(
                                    $rating2028,
                                    5
                                ); ?>

                            </span>

                            <br>

                            <small>
                                <?= $status2028; ?>
                            </small>

                        <?php else: ?>

                            <span class="text-muted">
                                —
                            </span>

                        <?php endif; ?>

                    </td>

                </tr>

            <?php

                }

            }else{

            ?>

                <tr>

                    <td
                        colspan="4"
                        class="text-center text-muted py-4">

                        <i class="fas fa-chart-line fa-2x mb-2"></i>

                        <br>

                        No historical performance records found.

                    </td>

                </tr>

            <?php } ?>

            </tbody>

        </table>

    </div>

</div>

<!-- ADD EVALUATION MODAL -->
<div class="modal fade" id="addEval">
<div class="modal-dialog modal-lg">
<div class="modal-content">

<div class="modal-header">
<h5>Add Performance Evaluation</h5>
<button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<form method="POST">

<div class="row">

<?php
$employees = $conn->query("
SELECT employee_id, first_name, last_name
FROM personnel
ORDER BY first_name ASC
");
?>

<div class="col-md-12 mb-3">
<label>Select Employee</label>

<select name="employee_id" id="employeeSelect" class="form-control" required>
    <option value="">-- Select Employee --</option>

    <?php while($emp = $employees->fetch_assoc()){ ?>
        <option
            value="<?= $emp['employee_id']; ?>"
            data-name="<?= $emp['first_name'].' '.$emp['last_name']; ?>"
        >
            <?= $emp['employee_id']; ?> - <?= $emp['first_name'].' '.$emp['last_name']; ?>
        </option>
    <?php } ?>
</select>
</div>

<input type="hidden" name="employee_name" id="employeeName">

<div class="col-md-6 mb-3">

    <label class="form-label">
        Evaluation Period
    </label>

    <select
        name="evaluation_period"
        class="form-control"
        required>

        <option value="">-- Select Evaluation Period --</option>

        <optgroup label="2026">
            <option value="2026-Q1">2026 - Q1</option>
            <option value="2026-Q2">2026 - Q2</option>
            <option value="2026-Q3">2026 - Q3</option>
            <option value="2026-Q4">2026 - Q4</option>
        </optgroup>

        <optgroup label="2027">
            <option value="2027-Q1">2027 - Q1</option>
            <option value="2027-Q2">2027 - Q2</option>
            <option value="2027-Q3">2027 - Q3</option>
            <option value="2027-Q4">2027 - Q4</option>
        </optgroup>

        <optgroup label="2028">
            <option value="2028-Q1">2028 - Q1</option>
            <option value="2028-Q2">2028 - Q2</option>
            <option value="2028-Q3">2028 - Q3</option>
            <option value="2028-Q4">2028 - Q4</option>
        </optgroup>

    </select>

</div>

<!-- NUMERICAL RATING -->
<div class="col-md-6 mb-3">

    <label class="form-label">
        Numerical Rating
    </label>

    <input
    type="number"
    name="rating"
    id="ratingSelect"
    class="form-control"
    min="1"
    max="5"
    step="0.01"
    placeholder="Enter rating (1.00 - 5.00)"
    required>
</div>


<!-- ADJECTIVAL RATING -->
<div class="col-md-6 mb-3">

    <label class="form-label">
        Adjectival Rating
    </label>

    <input
        type="text"
        id="adjectivalRating"
        class="form-control"
        value=""
        placeholder="Automatically determined"
        readonly>

</div>

<div class="col-md-6 mb-3">
<label>Evaluator</label>
<input
type="text"
name="evaluator"
class="form-control">
</div>

<div class="col-md-12 mb-3">
<label>Comments</label>
<textarea
name="comments"
class="form-control"></textarea>
</div>

</div>

<div class="modal-footer">

<button
type="button"
class="btn btn-secondary"
data-bs-dismiss="modal">
Cancel
</button>

<button
type="submit"
name="save_evaluation"
class="btn btn-primary">
Save Evaluation
</button>

</div>

</form>


</div>
</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/theme.js"></script>

<script>
const themeBtn = document.getElementById("themeToggle");
const themeIcon = document.getElementById("themeIcon");

// Load theme
if(localStorage.getItem("theme") === "dark"){
    document.body.classList.add("dark-mode");
    themeIcon.classList.remove("fa-moon");
    themeIcon.classList.add("fa-sun");
}

themeBtn.addEventListener("click", function(){

    document.body.classList.toggle("dark-mode");

    if(document.body.classList.contains("dark-mode")){
        localStorage.setItem("theme","dark");
        themeIcon.classList.remove("fa-moon");
        themeIcon.classList.add("fa-sun");
    }else{
        localStorage.setItem("theme","light");
        themeIcon.classList.remove("fa-sun");
        themeIcon.classList.add("fa-moon");
    }

});
</script>
<script>
const sidebar = document.querySelector(".sidebar");
const sidebarToggle = document.getElementById("sidebarToggle");
const overlay = document.getElementById("sidebarOverlay");

sidebarToggle.addEventListener("click", () => {
    sidebar.classList.toggle("active");
    overlay.classList.toggle("show");
});

overlay.addEventListener("click", () => {
    sidebar.classList.remove("active");
    overlay.classList.remove("show");
});
</script>

<script>
document.getElementById("employeeSelect").addEventListener("change", function(){

    let selected = this.options[this.selectedIndex];
    let name = selected.getAttribute("data-name");

    document.getElementById("employeeName").value = name;

});
</script>

<script>

const ratingSelect = document.getElementById("ratingSelect");
const adjectivalRating = document.getElementById("adjectivalRating");

ratingSelect.addEventListener("change", function(){

    const rating = this.value;

    const rating = parseFloat(this.value);

let adjectival = "";

if(!isNaN(rating)){

    if(rating >= 4.50){
        adjectival = "Outstanding";
    }
    else if(rating >= 3.50){
        adjectival = "Very Satisfactory";
    }
    else if(rating >= 2.50){
        adjectival = "Satisfactory";
    }
    else if(rating >= 1.50){
        adjectival = "Unsatisfactory";
    }
    else if(rating >= 1.00){
        adjectival = "Poor";
    }

}

adjectivalRating.value = adjectival;

});

</script>

</body>
</html>
