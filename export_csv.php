<?php
include "config.php";

header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=personnel_report.csv");

$output = fopen("php://output","w");

fputcsv($output,[
    "Employee ID",
    "Employee Name",
    "Position",
    "Province",
    "Status"
]);

$result = $conn->query("
SELECT *
FROM personnel
");

while($row = $result->fetch_assoc()){

    $fullname =
        $row['first_name'].' '.
        $row['middle_name'].' '.
        $row['last_name'];

    fputcsv($output,[
        $row['employee_id'],
        $fullname,
        $row['position_title'],
        $row['province'],
        $row['employment_status']
    ]);
}

fclose($output);
exit();
?>