<?php

include "auth.php";
include "config.php";
include "role_access.php";

allowRoles([
    'System Administrator',
    'HR Administrator'
]);

/* =========================================================
   GET DOCUMENT ID
========================================================= */

$id = intval($_GET['id'] ?? $_POST['id'] ?? 0);

if($id <= 0){
    die("Invalid document ID.");
}


/* =========================================================
   LOAD DOCUMENT
========================================================= */

$stmt = $conn->prepare("
    SELECT *
    FROM documents
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if($result->num_rows === 0){
    die("Document record not found.");
}

$document = $result->fetch_assoc();


/* =========================================================
   UPDATE DOCUMENT
========================================================= */

if(isset($_POST['update_document'])){

    $document_type = trim($_POST['document_type'] ?? '');
    $version = trim($_POST['version'] ?? '');
    $termination_date = trim($_POST['termination_date'] ?? '');

    if($document_type === ''){
        die("Document type is required.");
    }

    if($version === ''){
        $version = 'v1';
    }


    /* =====================================================
       FILE VARIABLES
    ===================================================== */

    $oldFile = $document['file_name'];

    $newFile = $oldFile;

    $upload_dir = "uploads/documents/";

    if(!is_dir($upload_dir)){
        mkdir($upload_dir, 0777, true);
    }


    /* =====================================================
       CHECK IF NEW FILE WAS UPLOADED
    ===================================================== */

    if(
        isset($_FILES['document_file']) &&
        $_FILES['document_file']['error'] !== UPLOAD_ERR_NO_FILE
    ){

        if($_FILES['document_file']['error'] !== UPLOAD_ERR_OK){
            die("There was an error uploading the new file.");
        }


        $file = $_FILES['document_file']['name'];

        $tmp = $_FILES['document_file']['tmp_name'];

        $size = $_FILES['document_file']['size'];


        /* ================================================
           ALLOWED FILE TYPES
        ================================================ */

        $allowed = [
            'pdf',
            'doc',
            'docx',
            'jpg',
            'jpeg',
            'png'
        ];

        $ext = strtolower(
            pathinfo($file, PATHINFO_EXTENSION)
        );


        if(!in_array($ext, $allowed, true)){
            die("Invalid file type.");
        }


        /* ================================================
           MAXIMUM FILE SIZE = 5MB
        ================================================ */

        if($size > 5 * 1024 * 1024){

            echo "
            <script>
                alert('File size exceeds 5MB.');
                history.back();
            </script>";

            exit();
        }


        /* ================================================
           CREATE NEW FILE NAME
        ================================================ */

        $safeName = preg_replace(
            '/[^A-Za-z0-9._-]/',
            '_',
            basename($file)
        );

        $newFile = uniqid('', true) . '_' . $safeName;


        /* ================================================
           MOVE NEW FILE
        ================================================ */

        if(!move_uploaded_file(
            $tmp,
            $upload_dir . $newFile
        )){

            die("Failed to upload the new file.");
        }
    }


    /* =====================================================
       UPDATE DATABASE
    ===================================================== */

    $update = $conn->prepare("
        UPDATE documents
        SET
            document_type = ?,
            file_name = ?,
            version = ?,
            termination_date = ?
        WHERE id = ?
    ");

    $update->bind_param(
        "ssssi",
        $document_type,
        $newFile,
        $version,
        $termination_date,
        $id
    );


    if($update->execute()){

        /* ================================================
           DELETE OLD FILE ONLY IF NEW FILE WAS SUCCESSFULLY
           SAVED
        ================================================ */

        if(
            $newFile !== $oldFile &&
            !empty($oldFile)
        ){

            $oldPath = $upload_dir . basename($oldFile);

            if(file_exists($oldPath)){
                unlink($oldPath);
            }
        }


        echo "
        <script>
            alert('Document updated successfully.');
            window.location.href='documents.php';
        </script>";

        exit();

    }else{

        /* ================================================
           IF DATABASE UPDATE FAILED, REMOVE NEW FILE
        ================================================ */

        if(
            $newFile !== $oldFile &&
            file_exists($upload_dir . $newFile)
        ){

            unlink($upload_dir . $newFile);
        }

        die("Failed to update document.");
    }
}

?>
<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1">

<title>Edit Document | JOPMIS</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<link
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
rel="stylesheet">

<link rel="stylesheet"
href="assets/css/theme.css">

<style>

body{
    background:#f1f5f9;
    font-family:'Segoe UI',sans-serif;
}

.main{
    margin-left:270px;
    min-height:100vh;
}

.topbar{
    background:white;
    padding:15px 25px;
    box-shadow:0 2px 10px rgba(0,0,0,.05);
}

.page-content{
    padding:30px;
}

.edit-card{
    max-width:900px;
    margin:auto;
    background:white;
    border-radius:20px;
    padding:30px;
    box-shadow:0 5px 20px rgba(0,0,0,.08);
}

.form-label{
    font-weight:600;
}

.current-file{
    background:#f8fafc;
    border:1px solid #e2e8f0;
    border-radius:10px;
    padding:15px;
}

@media(max-width:991px){

    .main{
        margin-left:0;
    }

    .page-content{
        padding:15px;
    }

}

</style>

</head>

<body>

<div class="main">

    <!-- TOPBAR -->

    <div class="topbar d-flex justify-content-between align-items-center">

        <div class="d-flex align-items-center">

            <a
                href="documents.php"
                class="btn btn-outline-secondary me-3">

                <i class="fas fa-arrow-left"></i>

            </a>

            <h4 class="mb-0">
                Edit Document
            </h4>

        </div>

    </div>


    <!-- CONTENT -->

    <div class="page-content">

        <div class="edit-card">

            <div class="mb-4">

                <h4 class="fw-bold">
                    <i class="fas fa-file-pen text-primary me-2"></i>
                    Edit Document
                </h4>

                <p class="text-muted mb-0">
                    Update the document information below.
                </p>

            </div>


            <form
                method="POST"
                enctype="multipart/form-data">

                <input
                    type="hidden"
                    name="id"
                    value="<?= (int)$document['id']; ?>">


                <!-- EMPLOYEE -->

                <div class="mb-4">

                    <label class="form-label">
                        Employee
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        value="<?= htmlspecialchars(
                            $document['employee_id'] .
                            ' - ' .
                            $document['employee_name']
                        ); ?>"
                        readonly>

                </div>


                <div class="row">


                    <!-- DOCUMENT TYPE -->

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Document Type
                        </label>

                        <select
                            name="document_type"
                            class="form-select"
                            required>

                            <?php
                            $types = [
                                'Resume / PDS',
                                'Birth Certificate',
                                'Diploma',
                                'Training Certificate',
                                'Performance Rating',
                                'Medical Certificate',
                                'NBI Clearance',
                                'Other'
                            ];

                            foreach($types as $type):
                            ?>

                            <option
                                value="<?= htmlspecialchars($type); ?>"
                                <?= $document['document_type'] === $type ? 'selected' : ''; ?>>

                                <?= htmlspecialchars($type); ?>

                            </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- VERSION -->

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Version
                        </label>

                        <input
                            type="text"
                            name="version"
                            class="form-control"
                            value="<?= htmlspecialchars(
                                $document['version'] ?? 'v1'
                            ); ?>">

                    </div>


                    <!-- EXPIRATION DATE -->

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Expiration Date
                        </label>

                        <input
                            type="date"
                            name="termination_date"
                            class="form-control"
                            value="<?= htmlspecialchars(
                                $document['termination_date'] ?? ''
                            ); ?>">

                    </div>


                    <!-- CURRENT FILE -->

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Current File
                        </label>

                        <div class="current-file">

                            <i class="fas fa-file me-2 text-primary"></i>

                            <?= htmlspecialchars(
                                $document['file_name']
                            ); ?>

                        </div>

                    </div>


                    <!-- NEW FILE -->

                    <div class="col-12 mb-4">

                        <label class="form-label">
                            Replace File
                        </label>

                        <input
                            type="file"
                            name="document_file"
                            class="form-control"
                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">

                        <small class="text-muted">

                            Leave this empty if you want to keep
                            the current file.

                            Maximum size: 5MB.

                        </small>

                    </div>

                </div>


                <!-- BUTTONS -->

                <div class="d-flex justify-content-end gap-2">

                    <a
                        href="documents.php"
                        class="btn btn-secondary">

                        Cancel

                    </a>

                    <button
                        type="submit"
                        name="update_document"
                        class="btn btn-primary">

                        <i class="fas fa-save me-1"></i>

                        Save Changes

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<script
src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>