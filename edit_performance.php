<?php
include "auth.php";
include "config.php";

$id = $_GET['id'];

$result = $conn->query("
SELECT *
FROM performance
WHERE id='$id'
");

$row = $result->fetch_assoc();

if(isset($_POST['update'])){

    $employee_name = $_POST['employee_name'];
    $evaluation_period = $_POST['evaluation_period'];
    $rating = $_POST['rating'];
    $evaluator = $_POST['evaluator'];
    $comments = $_POST['comments'];

    if($rating >= 4.5){
        $status = "Outstanding";
    }
    elseif($rating >= 3){
        $status = "Average";
    }
    else{
        $status = "Poor";
    }

    $conn->query("
    UPDATE performance SET
        employee_name='$employee_name',
        evaluation_period='$evaluation_period',
        rating='$rating',
        evaluator='$evaluator',
        comments='$comments',
        status='$status'
    WHERE id='$id'
    ");

    header("Location: performance.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Performance</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container mt-5">

<div class="card">

<div class="card-header">
<h4>Edit Performance Evaluation</h4>
</div>

<div class="card-body">

<form method="POST">

<div class="mb-3">
<label>Employee Name</label>
<input type="text"
name="employee_name"
class="form-control"
value="<?= $row['employee_name']; ?>">
</div>

<div class="mb-3">
<label>Evaluation Period</label>
<input type="text"
name="evaluation_period"
class="form-control"
value="<?= $row['evaluation_period']; ?>">
</div>

<div class="mb-3">
<label>Rating</label>
<input type="number"
step="0.1"
min="1"
max="5"
name="rating"
class="form-control"
value="<?= $row['rating']; ?>">
</div>

<div class="mb-3">
<label>Evaluator</label>
<input type="text"
name="evaluator"
class="form-control"
value="<?= $row['evaluator']; ?>">
</div>

<div class="mb-3">
<label>Comments</label>
<textarea
name="comments"
class="form-control"><?= $row['comments']; ?></textarea>
</div>

<button type="submit"
name="update"
class="btn btn-primary">
Update
</button>

<a href="performance.php"
class="btn btn-secondary">
Back
</a>

</form>

</div>
</div>

</div>

</body>
</html>