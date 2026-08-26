<?php
include "auth.php";
include "config.php";

$training_id=$_GET['training_id'];

$get=$conn->query("
SELECT personnel_id
FROM personnel_learning_development
WHERE id='$training_id'
");

$row=$get->fetch_assoc();

$conn->query("
DELETE FROM personnel_learning_development
WHERE id='$training_id'
");

echo "
<script>

alert('Training Deleted Successfully.');

window.location='personnel.php?id=".$row['personnel_id']."';

</script>";
?>