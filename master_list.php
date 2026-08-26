<?php
include "auth.php";
include "config.php";

$result = $conn->query("
SELECT *
FROM personnel
ORDER BY last_name ASC
");

$totalPersonnel = $result->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Master List Report | JOPMIS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

<style>

body{
    background:#f1f5f9;
    font-family:'Segoe UI',sans-serif;
}

.report-header{
    background:linear-gradient(135deg,#1e3a8a,#2563eb);
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

.search-box{
    max-width:350px;
}

.badge-status{
    background:#16a34a;
    color:white;
    font-size:13px;
    padding:6px 10px;
}

.name-text{
    font-weight:600;
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

<!-- HEADER -->
<div class="report-header">

    <div class="d-flex justify-content-between align-items-center">

        <div>
            <h2>
                <i class="fas fa-users"></i>
                Master List Report
            </h2>
            <p class="mb-0">Complete List of All Personnel</p>
        </div>

        <div>
            <h3><?= $totalPersonnel; ?></h3>
            <small>Total Personnel</small>
        </div>

    </div>

</div>

<!-- CARD -->
<div class="card">

<div class="card-body">

<!-- TOP CONTROLS -->
<div class="d-flex justify-content-between flex-wrap gap-2 mb-3">

<input
type="text"
id="searchInput"
class="form-control search-box"
placeholder="Search Personnel...">

<div class="no-print">

<button onclick="window.print()" class="btn btn-dark">
<i class="fas fa-print"></i> Print
</button>

<a href="reports.php" class="btn btn-primary">
<i class="fas fa-arrow-left"></i> Back
</a>

</div>

</div>

<!-- TABLE -->
<div class="table-responsive">

<table class="table table-bordered table-hover">

<thead class="table-dark">
<tr>
<th>ID</th>
<th>Name</th>
<th>Position</th>
<th>Province</th>
<th>Contact</th>
<th>Email</th>
<th>Status</th>
<th width="120">Action</th>
</tr>
</thead>

<tbody>

<?php while($row = $result->fetch_assoc()){ ?>

<tr>

<td><?= $row['employee_id']; ?></td>

<td class="name-text">
<?= $row['last_name']; ?>,
<?= $row['first_name']; ?>
<?= $row['middle_name']; ?>
</td>

<td><?= $row['position_title']; ?></td>

<td><?= $row['province']; ?></td>

<td><?= $row['contact_number']; ?></td>

<td><?= $row['email']; ?></td>

<td>
<span class="badge badge-status">
<?= $row['employment_status']; ?>
</span>
</td>

<td>

<a href="contract_history.php?id=<?= $row['employee_id']; ?>"
class="btn btn-primary btn-sm">

<i class="fas fa-history"></i>
View History

</a>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

</div>

</div>

<!-- SEARCH SCRIPT -->
<script>

document.getElementById("searchInput")
.addEventListener("keyup", function(){

let value = this.value.toLowerCase();

let rows = document.querySelectorAll("tbody tr");

rows.forEach(function(row){

row.style.display =
row.innerText.toLowerCase().includes(value)
? ""
: "none";

});

});

</script>

</body>
</html>