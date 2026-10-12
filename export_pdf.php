
<?php
include "auth.php";
include "config.php";
require_once "export_data.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>JOPMIS Personnel Report</title>

<style>
    body {
        font-family: Arial, sans-serif;
        color: #222;
        margin: 20px;
        font-size: 11px;
    }

    h1, p {
        text-align: center;
        margin: 5px 0;
    }

    .report-info {
        margin: 20px 0 12px;
        text-align: left;
    }

    .table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        table-layout: auto;
    }

    th, td {
        border: 1px solid #999;
        padding: 5px;
        text-align: left;
        vertical-align: top;
        overflow-wrap: anywhere;
    }

    th {
        background: #1e3a8a;
        color: #fff;
    }

    tr {
        page-break-inside: avoid;
    }

    .print-button {
        margin-bottom: 20px;
        padding: 10px 16px;
        background: #1e3a8a;
        color: #fff;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    @page {
        size: A3 landscape;
        margin: 10mm;
    }

    @media print {
        body {
            margin: 0;
            font-size: 8px;
        }

        .print-button {
            display: none;
        }

        th {
            background: #1e3a8a !important;
            color: white !important;
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }
    }
</style>
</head>

<body>

<button class="print-button" onclick="window.print()">
    Print / Save as PDF
</button>

<h1>JOPMIS</h1>
<p>COMELEC - CORDILLERA ADMINISTRATIVE REGION</p>
<p><strong>Complete Personnel Report</strong></p>

<div class="report-info">
    <strong>Date Generated:</strong> <?= date('F d, Y') ?><br>
    <strong>Total Employees:</strong> <?= count($exportRows) ?>
</div>

<div class="table-wrap">
<table>
    <thead>
        <tr>
            <?php foreach ($exportHeaders as $label): ?>
                <th>
                    <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                </th>
            <?php endforeach; ?>
        </tr>
    </thead>

    <tbody>
        <?php if (empty($exportRows)): ?>
            <tr>
                <td colspan="<?= count($exportHeaders) ?>">
                    No personnel records found.
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($exportRows as $row): ?>
                <tr>
                    <?php foreach ($exportHeaders as $column => $label): ?>
                        <td>
                            <?= nl2br(htmlspecialchars(
                                (string)($row[$column] ?? ''),
                                ENT_QUOTES,
                                'UTF-8'
                            )) ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
</div>

<script>
window.addEventListener("load", function () {
    window.print();
});
</script>

</body>
</html>
