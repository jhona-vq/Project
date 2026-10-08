<?php

include "auth.php";
include "config.php";

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$document_id = intval($_GET['id'] ?? 0);

if($document_id <= 0){
    die("Invalid document.");
}


/* =========================================
   GET DOCUMENT
========================================= */

$stmt = $conn->prepare("
    SELECT
        cd.id,
        cd.contract_id,
        cd.document_type,
        cd.file_name,
        c.contract_id AS contract_number,
        c.employee_name
    FROM contract_documents cd
    INNER JOIN contracts c
        ON c.id = cd.contract_id
    WHERE cd.id = ?
    LIMIT 1
");

$stmt->bind_param("i", $document_id);
$stmt->execute();

$result = $stmt->get_result();

if($result->num_rows === 0){
    die("File not found in database.");
}

$file = $result->fetch_assoc();


/* =========================================
   FILE PATH
========================================= */

$upload_dir = __DIR__ . DIRECTORY_SEPARATOR . "uploads"
            . DIRECTORY_SEPARATOR . "contracts";

$file_name = basename($file['file_name']);

$file_path = $upload_dir . DIRECTORY_SEPARATOR . $file_name;


/* =========================================
   CHECK FILE
========================================= */

if(!file_exists($file_path)){
    die("The physical file does not exist.");
}

if(!is_file($file_path)){
    die("Invalid file.");
}


/* =========================================
   DOWNLOAD
========================================= */

$download_name =
    $file['document_type'] .
    "_" .
    $file['contract_number'] .
    "_" .
    $file_name;

$download_name =
    preg_replace(
        '/[^A-Za-z0-9._-]/',
        '_',
        $download_name
    );


/* =========================================
   MIME TYPE
========================================= */

$mime = mime_content_type($file_path);

if(!$mime){
    $mime = 'application/octet-stream';
}


/* =========================================
   HEADERS
========================================= */

header("Content-Type: " . $mime);

header(
    'Content-Disposition: attachment; filename="' .
    $download_name .
    '"'
);

header("Content-Length: " . filesize($file_path));

header("Cache-Control: private, must-revalidate");
header("Pragma: public");
header("Expires: 0");


/* =========================================
   OUTPUT FILE
========================================= */

readfile($file_path);

exit;