<?php
include "auth.php";
include "config.php";

$id = $_GET['id'];

$get = $conn->query("
SELECT *
FROM contracts
WHERE id='$id'
");

$row = $get->fetch_assoc();

$old_end = $row['end_date'];

$new_start = date(
    'Y-m-d',
    strtotime($old_end . ' +1 day')
);

$new_end = date(
    'Y-m-d',
    strtotime($new_start . ' +3 months -1 day')
);

/* Generate Contract ID */
$result = $conn->query("
SELECT contract_id
FROM contracts
ORDER BY id DESC
LIMIT 1
");

if($result->num_rows > 0){

    $r = $result->fetch_assoc();

    $number = (int)substr($r['contract_id'],4);

    $number++;

    $contract_id =
        "CON-" .
        str_pad($number,4,"0",STR_PAD_LEFT);

}else{

    $contract_id = "CON-0001";
}

/* Save New Contract */
$conn->query("
INSERT INTO contracts(
    contract_id,
    employee_id,
    employee_name,
    position_title,
    start_date,
    end_date,
    status,
    appointment_file,
    contract_file,
    renewal_file,
    certification_file
)
VALUES(
    '$contract_id',
    '{$row['employee_id']}',
    '{$row['employee_name']}',
    '{$row['position_title']}',
    '$new_start',
    '$new_end',
    'Active',
    '{$row['appointment_file']}',
    '{$row['contract_file']}',
    '{$row['renewal_file']}',
    '{$row['certification_file']}'
)
");

/* Mark old contract as Renewed */
$conn->query("
UPDATE contracts
SET status='Renewed'
WHERE id='$id'
");

/* Update Personnel Record */
$conn->query("
UPDATE personnel
SET
    contract_start='$new_start',
    contract_end='$new_end',
    employment_status='Active'
WHERE employee_id='{$row['employee_id']}'
");

header("Location: contracts.php");
exit();
?>