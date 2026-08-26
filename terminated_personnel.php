<?php
include "auth.php";
include "config.php";

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if ($page < 1) {
    $page = 1;
}

$start = ($page - 1) * $limit;

$countResult = $conn->query("
SELECT COUNT(*) as total
FROM contracts
WHERE end_date < CURDATE()
");

$totalTerminated = 0;

if ($countResult) {
    $totalTerminated = $countResult->fetch_assoc()['total'];
}

$total_pages = ($limit > 0) ? ceil($totalTerminated / $limit) : 1;

/* TOTAL TERMINATED RECORDS */
$result = $conn->query("
SELECT *
FROM contracts
WHERE end_date < CURDATE()
ORDER BY end_date DESC
LIMIT $start, $limit
");

$total_pages = ceil($totalTerminated / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Terminated Personnel Report | JOPMIS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

<style>

body{
    background:#f1f5f9;
    font-family:'Segoe UI',sans-serif;
}

.report-header{
    background:linear-gradient(135deg,#991b1b,#dc2626);
    color:white;
    padding:25px;
    border-radius:15px;
    margin-bottom:20px;
}

.card{
    border:none;
    border-radius:15px;
    box-shadow:0 5px 15px rgba(0,0,0,.08);
}

.table{
    vertical-align:middle;
}

.badge-expired{
    background:#dc2626;
    color:white;
    font-size:13px;
    padding:8px 12px;
}

.days-expired{
    color:#dc2626;
    font-weight:bold;
}

.search-box{
    max-width:350px;
}

@media print{

    .no-print{
        display:none;
    }

    body{
        background:white;
    }

}

</style>
</head>
<body>

<div class="container-fluid p-4">

<div class="report-header">

<div class="d-flex justify-content-between align-items-center">

<div>
<h2>
<i class="fas fa-user-times"></i>
Terminated Personnel Report
</h2>

<p class="mb-0">
Personnel with Terminated Contracts
</p>
</div>

<div>
<h3><?= $totalTerminated; ?></h3>
<small>Total Terminated Contracts</small>
</div>

</div>

</div>

<div class="card">

<div class="card-body">

<div class="d-flex justify-content-between flex-wrap gap-2 mb-3">

<input
type="text"
id="searchInput"
class="form-control search-box"
placeholder="Search Employee...">

<div class="no-print">

<button
onclick="window.print()"
class="btn btn-dark">

<i class="fas fa-print"></i>
Print

</button>

<a
href="reports.php"
class="btn btn-primary">

<i class="fas fa-arrow-left"></i>
Back

</a>

</div>

</div>

<div class="table-responsive">

<table class="table table-bordered table-hover">

<thead class="table-dark">

<tr>
<th>Employee ID</th>
<th>Employee Name</th>
<th>Position</th>
<th>Contract End Date</th>
<th>Days Terminated</th>
<th>Status</th>
</tr>

</thead>

<tbody>

<?php while($row = $result->fetch_assoc()){

$daysTerminated =
floor(
(
strtotime(date('Y-m-d'))
-
strtotime($row['end_date'])
)
/86400
);

?>

<tr>

<td><?= $row['employee_id']; ?></td>

<td><?= $row['employee_name']; ?></td>

<td><?= $row['position_title']; ?></td>

<td>
<?= date("F d, Y", strtotime($row['end_date'])); ?>
</td>

<td class="days-terminated">
<?= $daysTerminated; ?> Day(s)
</td>

<td>

<span class="badge badge-terminated">
Terminated
</span>

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
<a class="page-link" href="?page=<?= $page-1; ?>&limit=<?= $limit; ?>">Previous</a>
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
<a class="page-link" href="?page=<?= $page+1; ?>&limit=<?= $limit; ?>">Next</a>
</li>
<?php } ?>

</ul>
</nav>

</div>

</div>

</div>

<script>

document.getElementById("searchInput")
.addEventListener("keyup", function(){

let value = this.value.toLowerCase();

let rows =
document.querySelectorAll("tbody tr");

rows.forEach(function(row){

row.style.display =
row.innerText.toLowerCase()
.includes(value)
? ""
: "none";

});

});

</script>

</body>
</html>
