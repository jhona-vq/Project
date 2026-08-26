<?php
include "auth.php";
include "config.php";

$personnel_id = $_GET['personnel_id'] ?? '';


$conn->query("
DELETE FROM personnel_education
WHERE personnel_id='$personnel_id'
");

echo "
<script>

alert('Educational Background Deleted Successfully.');

window.location='personnel.php?id=$personnel_id';

</script>
";