<?php
include "auth.php";
include "config.php";

$personnel_id = $_GET['personnel_id'] ?? '';

$get = $conn->query("
SELECT personnel_id
FROM personnel_family
WHERE id='$personnel_id'
");


$row = $get->fetch_assoc();

$conn->query("
DELETE FROM personnel_family
WHERE id='$personnel_id'
");

echo "
<script>

alert('Family Background Deleted Successfully.');

window.location='personnel.php?id=$personnel_id';

</script>";
exit;
?>