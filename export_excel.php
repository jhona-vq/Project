<?php
include "config.php";

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=personnel_report.xls");

echo "
<table border='1'>
<tr>
<th>Employee ID</th>
<th>Name</th>
<th>Position</th>
<th>Province</th>
<th>Status</th>
</tr>
";

$result = $conn->query("
SELECT *
FROM personnel
");

while($row = $result->fetch_assoc()){

    $fullname =
        $row['first_name'].' '.
        $row['middle_name'].' '.
        $row['last_name'];

    echo "
    <tr>
        <td>{$row['employee_id']}</td>
        <td>{$fullname}</td>
        <td>{$row['position_title']}</td>
        <td>{$row['province']}</td>
        <td>{$row['employment_status']}</td>
    </tr>
    ";
}

echo "</table>";
?>