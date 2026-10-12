
<?php
include "auth.php";
include "config.php";
require_once "export_data.php";

if (ob_get_length()) {
    ob_clean();
}

$filename = "JOPMIS_Personnel_" . date("Y-m-d") . ".csv";

header("Content-Type: text/csv; charset=UTF-8");
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen("php://output", "w");

// Helps Microsoft Excel display UTF-8 text correctly.
fwrite($output, "\xEF\xBB\xBF");

fputcsv($output, array_values($exportHeaders));

foreach ($exportRows as $row) {
    $line = [];

    foreach ($exportHeaders as $column => $label) {
        $line[] = $row[$column] ?? '';
    }

    fputcsv($output, $line);
}

fclose($output);
exit;
