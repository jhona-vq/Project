<?php
include "auth.php";
include "config.php";

$voluntary_id = $_GET['voluntary_id'];

$get = $conn->query("
SELECT personnel_id
FROM personnel_voluntary_work
WHERE id='$voluntary_id'
");

$row = $get->fetch_assoc();

$conn->query("
DELETE FROM personnel_voluntary_work
WHERE id='$voluntary_id'
");

echo "
<script>

alert('Voluntary Work Deleted Successfully.');

window.location='personnel.php?id=".$row['personnel_id']."';

</script>
";
?>