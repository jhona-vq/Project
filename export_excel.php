
<?php
include "auth.php";
include "config.php";
require_once "export_data.php";

if (ob_get_length()) {
    ob_clean();
}

$filename = "JOPMIS_Personnel_" . date("Y-m-d") . ".xls";

header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header('Content-Disposition: attachment; filename="' . $filename . '"');
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

echo "\xEF\xBB\xBF";
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        table {
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #999;
            padding: 6px;
            vertical-align: top;
        }
        th {
            background-color: #1e3a8a;
            color: white;
            font-weight: bold;
        }
    </style>
</head>
<body>
<table>
    <thead>
        <tr>
            <?php foreach ($exportHeaders as $label): ?>
                <th><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($exportRows as $row): ?>
            <tr>
                <?php foreach ($exportHeaders as $column => $label): ?>
                    <td><?= htmlspecialchars((string)($row[$column] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</body>
</html>
<?php
exit;
?>
