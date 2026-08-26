<?php
include "config.php";

$id = $_GET['id'];

$result = $conn->query("SELECT * FROM contracts WHERE id='$id'");
$row = $result->fetch_assoc();

if(isset($_POST['update_contract'])){

    $employee_id = $_POST['employee_id'];
    $employee_name = $_POST['employee_name'];
    $position_title = $_POST['position_title'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $status = $_POST['status'];

    $conn->query("
        UPDATE contracts SET
        employee_id='$employee_id',
        employee_name='$employee_name',
        position_title='$position_title',
        start_date='$start_date',
        end_date='$end_date',
        status='$status'
        WHERE id='$id'
    ");

    header("Location: contracts.php");
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Contract</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body>

<div class="container mt-5">

<div class="card shadow">

<div class="card-header bg-warning">
<h4>Edit Contract</h4>
</div>

<div class="card-body">

<form method="POST">

<div class="row">

<div class="col-md-6 mb-3">
<label>Employee ID</label>
<input type="text"
name="employee_id"
class="form-control"
value="<?= $row['employee_id']; ?>">
</div>

<div class="col-md-6 mb-3">
<label>Employee Name</label>
<input type="text"
name="employee_name"
class="form-control"
value="<?= $row['employee_name']; ?>">
</div>

<div class="col-md-6 mb-3">
<label>Position</label>
<input type="text"
name="position_title"
class="form-control"
value="<?= $row['position_title']; ?>">
</div>

<div class="col-md-6 mb-3">
<label>Status</label>

<select name="status" class="form-control">

<option value="Active"
<?= ($row['status']=="Active") ? "selected" : ""; ?>>
Active
</option>

<option value="Expired"
<?= ($row['status']=="Expired") ? "selected" : ""; ?>>
Expired
</option>

<option value="Renewed"
<?= ($row['status']=="Renewed") ? "selected" : ""; ?>>
Renewed
</option>

<option value="Terminated"
<?= ($row['status']=="Terminated") ? "selected" : ""; ?>>
Terminated
</option>

</select>

</div>

<div class="col-md-6 mb-3">
<label>Start Date</label>
<input type="date"
name="start_date"
class="form-control"
value="<?= $row['start_date']; ?>">
</div>

<div class="col-md-6 mb-3">
<label>End Date</label>
<input type="date"
name="end_date"
class="form-control"
value="<?= $row['end_date']; ?>">
</div>

</div>

<button type="submit"
name="update_contract"
class="btn btn-success">
Update Contract
</button>

<a href="contracts.php" class="btn btn-secondary">
Cancel
</a>

</form>

</div>

</div>

</div>

</body>
</html>