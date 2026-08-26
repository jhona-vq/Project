<?php
include "auth.php";
include "config.php";

$other_id = $_GET['other_id'];

$get = $conn->query("
SELECT personnel_id
FROM personnel_other_information
WHERE id='$other_id'
");

$row = $get->fetch_assoc();

$conn->query("
DELETE
FROM personnel_other_information
WHERE id='$other_id'
");

echo "
<script>

alert('Other Information Deleted Successfully.');

window.location='personnel.php?id=".$row['personnel_id']."';

</script>";
?>