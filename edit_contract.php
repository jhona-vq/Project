<?php

include "auth.php";
include "config.php";
include "role_access.php";

allowRoles([
    'System Administrator',
    'HR Administrator'
]);


/* =========================================================
   DETERMINE CONTRACT MODE
========================================================= */

$isNewHistory = (
    isset($_GET['new']) &&
    $_GET['new'] == '1'
);


/* =========================================================
   GET IDS
========================================================= */

$id = intval(
    $_GET['id']
    ?? $_POST['id']
    ?? 0
);

$fromId = intval(
    $_GET['from_id']
    ?? $_POST['from_id']
    ?? 0
);


/* =========================================================
   VALIDATE ID
========================================================= */

if($isNewHistory){

    /*
       New contract history.

       from_id = existing contract
       na gagawing basis ng bagong contract.
    */

    if($fromId <= 0){

        die("Invalid source contract ID.");

    }

}else{

    /*
       Normal edit mode.
    */

    if($id <= 0){

        die("Invalid contract ID.");

    }

}


/* =========================================================
   LOAD CONTRACT
========================================================= */

$loadId = $isNewHistory
    ? $fromId
    : $id;


$stmt = $conn->prepare("
    SELECT *
    FROM contracts
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param(
    "i",
    $loadId
);

$stmt->execute();

$result = $stmt->get_result();


if($result->num_rows === 0){

    die("Contract not found.");

}


$row = $result->fetch_assoc();


/* =========================================================
   UPLOAD DIRECTORY
========================================================= */

$upload_dir = "uploads/contracts/";


if(!is_dir($upload_dir)){

    mkdir(
        $upload_dir,
        0777,
        true
    );

}


/* =========================================================
   FILE UPLOAD FUNCTION
========================================================= */

function saveContractFiles(
    $conn,
    $files,
    $type,
    $contract_db_id,
    $upload_dir
){

    $allowed = [
        'pdf',
        'doc',
        'docx',
        'xls',
        'xlsx',
        'jpg',
        'jpeg',
        'png'
    ];


    if(
        !isset($files['name']) ||
        !is_array($files['name'])
    ){

        return;

    }


    foreach($files['name'] as $key => $filename){

        if(
            empty($filename) ||
            !isset($files['tmp_name'][$key])
        ){

            continue;

        }


        if(
            !isset($files['error'][$key]) ||
            $files['error'][$key] !== UPLOAD_ERR_OK
        ){

            continue;

        }


        $ext = strtolower(
            pathinfo(
                $filename,
                PATHINFO_EXTENSION
            )
        );


        if(!in_array($ext, $allowed, true)){

            continue;

        }


        /* -----------------------------------------
           SAFE ORIGINAL NAME
        ----------------------------------------- */

        $original_name = basename($filename);

        $original_name = preg_replace(
            '/[^A-Za-z0-9.\_-]/',
            '_',
            $original_name
        );


        /* -----------------------------------------
           UNIQUE FILE NAME
        ----------------------------------------- */

        $newname =
            time() .
            '_' .
            uniqid() .
            '_' .
            $original_name;


        $destination =
            $upload_dir .
            $newname;


        /* -----------------------------------------
           MOVE FILE
        ----------------------------------------- */

        if(
            move_uploaded_file(
                $files['tmp_name'][$key],
                $destination
            )
        ){

            $stmtDoc = $conn->prepare("
                INSERT INTO contract_documents
                (
                    contract_id,
                    document_type,
                    file_name
                )
                VALUES (?, ?, ?)
            ");


            $stmtDoc->bind_param(
                "iss",
                $contract_db_id,
                $type,
                $newname
            );


            $stmtDoc->execute();

            $stmtDoc->close();

        }

    }

}


/* =========================================================
   CREATE NEW CONTRACT HISTORY
========================================================= */

if(isset($_POST['create_contract_history'])){

    $employee_id = trim(
        $_POST['employee_id'] ?? ''
    );

    $employee_name = trim(
        $_POST['employee_name'] ?? ''
    );

    $position_title = trim(
        $_POST['position_title'] ?? ''
    );

    $start_date = trim(
        $_POST['start_date'] ?? ''
    );

    $end_date = trim(
        $_POST['end_date'] ?? ''
    );

    $status = trim(
        $_POST['status'] ?? 'Active'
    );


    /* -----------------------------------------
       VALIDATION
    ----------------------------------------- */

    if(
        $employee_id === '' ||
        $employee_name === '' ||
        $position_title === '' ||
        $start_date === '' ||
        $end_date === '' ||
        $status === ''
    ){

        echo "
        <script>
            alert('Please complete all required contract information.');
            history.back();
        </script>
        ";

        exit;

    }


    /* -----------------------------------------
       GENERATE NEW CONTRACT ID
    ----------------------------------------- */

    $nextResult = $conn->query("
        SELECT COALESCE(MAX(id), 0) + 1 AS next_id
        FROM contracts
    ");


    if(!$nextResult){

        die(
            "Unable to generate contract ID: " .
            htmlspecialchars($conn->error)
        );

    }


    $nextRow = $nextResult->fetch_assoc();

    $nextId = (int)$nextRow['next_id'];


    $newContractId =
        'CON-' .
        str_pad(
            $nextId,
            4,
            '0',
            STR_PAD_LEFT
        );


    /* -----------------------------------------
       INSERT NEW CONTRACT
    ----------------------------------------- */

    $insert = $conn->prepare("
        INSERT INTO contracts
        (
            contract_id,
            employee_id,
            employee_name,
            position_title,
            start_date,
            end_date,
            status
        )
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");


    $insert->bind_param(
        "sssssss",
        $newContractId,
        $employee_id,
        $employee_name,
        $position_title,
        $start_date,
        $end_date,
        $status
    );


    if(!$insert->execute()){

        die(
            "Failed to create new contract: " .
            htmlspecialchars($insert->error)
        );

    }


    /* -----------------------------------------
       GET NEW DATABASE ID
    ----------------------------------------- */

    $newContractDbId = $conn->insert_id;


    $insert->close();


    /* -----------------------------------------
       SAVE NEW FILES
    ----------------------------------------- */

    if(isset($_FILES['appointment'])){

        saveContractFiles(
            $conn,
            $_FILES['appointment'],
            'Appointment',
            $newContractDbId,
            $upload_dir
        );

    }


    if(isset($_FILES['contract_file'])){

        saveContractFiles(
            $conn,
            $_FILES['contract_file'],
            'Contract',
            $newContractDbId,
            $upload_dir
        );

    }


    if(isset($_FILES['renewal'])){

        saveContractFiles(
            $conn,
            $_FILES['renewal'],
            'Renewal',
            $newContractDbId,
            $upload_dir
        );

    }


    if(isset($_FILES['certification'])){

        saveContractFiles(
            $conn,
            $_FILES['certification'],
            'Certification',
            $newContractDbId,
            $upload_dir
        );

    }


    /* -----------------------------------------
       SUCCESS
    ----------------------------------------- */

    echo "
    <script>

        alert('New contract added successfully.');

        window.location.href =
            'view_contract.php?id={$newContractDbId}';

    </script>
    ";

    exit;

}


/* =========================================================
   UPDATE EXISTING CONTRACT
========================================================= */

if(isset($_POST['update_contract'])){

    $employee_id = trim(
        $_POST['employee_id'] ?? ''
    );

    $employee_name = trim(
        $_POST['employee_name'] ?? ''
    );

    $position_title = trim(
        $_POST['position_title'] ?? ''
    );

    $start_date = trim(
        $_POST['start_date'] ?? ''
    );

    $end_date = trim(
        $_POST['end_date'] ?? ''
    );

    $status = trim(
        $_POST['status'] ?? 'Active'
    );


    /* -----------------------------------------
       VALIDATION
    ----------------------------------------- */

    if(
        $employee_id === '' ||
        $employee_name === '' ||
        $position_title === '' ||
        $start_date === '' ||
        $end_date === '' ||
        $status === ''
    ){

        echo "
        <script>
            alert('Please complete all required contract information.');
            history.back();
        </script>
        ";

        exit;

    }


    /* -----------------------------------------
       UPDATE
    ----------------------------------------- */

    $update = $conn->prepare("
        UPDATE contracts
        SET
            employee_id = ?,
            employee_name = ?,
            position_title = ?,
            start_date = ?,
            end_date = ?,
            status = ?
        WHERE id = ?
    ");


    $update->bind_param(
        "ssssssi",
        $employee_id,
        $employee_name,
        $position_title,
        $start_date,
        $end_date,
        $status,
        $id
    );


    if(!$update->execute()){

        die(
            "Failed to update contract: " .
            htmlspecialchars($update->error)
        );

    }


    $update->close();


    /* -----------------------------------------
       SAVE ADDITIONAL FILES
    ----------------------------------------- */

    if(isset($_FILES['appointment'])){

        saveContractFiles(
            $conn,
            $_FILES['appointment'],
            'Appointment',
            $id,
            $upload_dir
        );

    }


    if(isset($_FILES['contract_file'])){

        saveContractFiles(
            $conn,
            $_FILES['contract_file'],
            'Contract',
            $id,
            $upload_dir
        );

    }


    if(isset($_FILES['renewal'])){

        saveContractFiles(
            $conn,
            $_FILES['renewal'],
            'Renewal',
            $id,
            $upload_dir
        );

    }


    if(isset($_FILES['certification'])){

        saveContractFiles(
            $conn,
            $_FILES['certification'],
            'Certification',
            $id,
            $upload_dir
        );

    }


    echo "
    <script>

        alert('Contract updated successfully.');

        window.location.href =
            'view_contract.php?id={$id}';

    </script>
    ";

    exit;

}


/* =========================================================
   LOAD EXISTING DOCUMENTS
========================================================= */

$docStmt = $conn->prepare("
    SELECT *
    FROM contract_documents
    WHERE contract_id = ?
    ORDER BY document_type ASC, id DESC
");

$docStmt->bind_param(
    "i",
    $loadId
);

$docStmt->execute();

$documents = $docStmt->get_result();

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1">

<title>
<?= $isNewHistory ? 'Add New Contract' : 'Edit Contract'; ?> | JOPMIS
</title>


<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">


<link
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
rel="stylesheet">


<style>

body{
    background:#f1f5f9;
    font-family:'Segoe UI',sans-serif;
}

.container{
    max-width:1100px;
}

.card{
    border:none;
    border-radius:15px;
    box-shadow:0 5px 20px rgba(0,0,0,.08);
}

.card-header{
    border-radius:15px 15px 0 0 !important;
}

.form-label{
    font-weight:600;
}

.file-card{
    border:1px solid #e2e8f0;
    border-radius:12px;
    padding:15px;
    margin-bottom:12px;
}

.file-icon{
    width:45px;
    height:45px;
    border-radius:10px;
    background:#eff6ff;
    display:flex;
    align-items:center;
    justify-content:center;
    color:#2563eb;
    font-size:20px;
}

.section-title{
    font-weight:600;
    margin-bottom:15px;
}

@media(max-width:768px){

    .container{
        padding-left:15px;
        padding-right:15px;
    }

}

</style>

</head>


<body>


<div class="container mt-5 mb-5">


<!-- =====================================================
     TOP BUTTONS
===================================================== -->

<div class="d-flex justify-content-between align-items-center mb-3">

    <a
        href="view_contract.php?id=<?= (int)($isNewHistory ? $fromId : $id); ?>"
        class="btn btn-secondary">

        <i class="fas fa-arrow-left"></i>

        Back to Contract

    </a>    


    <a
        href="contracts.php"
        class="btn btn-outline-secondary">

        <i class="fas fa-list"></i>

        Contract Records

    </a>

</div>


<!-- =====================================================
     EDIT CONTRACT
===================================================== -->

<div class="card">


<div class="card-header <?= $isNewHistory ? 'bg-success text-white' : 'bg-warning'; ?>">

    <h4 class="mb-0">

        <?php if($isNewHistory): ?>

            <i class="fas fa-plus-circle"></i>

            Add New Contract

        <?php else: ?>

            <i class="fas fa-edit"></i>

            Edit Contract

        <?php endif; ?>

    </h4>

</div>


<div class="card-body">


<form
    method="POST"
    enctype="multipart/form-data"
>


<input
    type="hidden"
    name="id"
    value="<?= (int)$id; ?>"
>

<?php if($isNewHistory): ?>

    <input
        type="hidden"
        name="from_id"
        value="<?= (int)$fromId; ?>"
    >

<?php endif; ?>


<!-- =====================================================
     CONTRACT INFORMATION
===================================================== -->

<h5 class="section-title">

    <i class="fas fa-file-signature text-primary"></i>

    Contract Information

</h5>


<div class="row">


    <!-- CONTRACT ID -->

    <div class="col-md-4 mb-3">

        <label class="form-label">
            Contract ID
        </label>

        <input
            type="text"
            class="form-control"
            value="<?= $isNewHistory
                ? 'AUTO GENERATED'
                : htmlspecialchars($row['contract_id']);
            ?>"
            readonly
        >

    </div>


    <!-- EMPLOYEE ID -->

    <div class="col-md-4 mb-3">

        <label class="form-label">
            Employee ID
        </label>

        <input
            type="text"
            name="employee_id"
            class="form-control"
            value="<?= htmlspecialchars($row['employee_id']); ?>"
            required
        >

    </div>


    <!-- EMPLOYEE NAME -->

    <div class="col-md-4 mb-3">

        <label class="form-label">
            Employee Name
        </label>

        <input
            type="text"
            name="employee_name"
            class="form-control"
            value="<?= htmlspecialchars($row['employee_name']); ?>"
            required
        >

    </div>


    <!-- POSITION -->

    <div class="col-md-6 mb-3">

        <label class="form-label">
            Position
        </label>

        <input
            type="text"
            name="position_title"
            class="form-control"
            value="<?= htmlspecialchars($row['position_title']); ?>"
            required
        >

    </div>


    <!-- STATUS -->

    <div class="col-md-6 mb-3">

        <label class="form-label">
            Status
        </label>

        <select
            name="status"
            class="form-select"
            required
        >

            <option
                value="Active"
                <?= ($row['status'] === 'Active') ? 'selected' : ''; ?>
            >
                Active
            </option>


            <option
                value="Renewed"
                <?= ($row['status'] === 'Renewed') ? 'selected' : ''; ?>
            >
                Renewed
            </option>


            <option
                value="Terminated"
                <?= ($row['status'] === 'Terminated') ? 'selected' : ''; ?>
            >
                Terminated
            </option>


            <option
                value="Expired"
                <?= ($row['status'] === 'Expired') ? 'selected' : ''; ?>
            >
                Expired
            </option>

        </select>

    </div>


    <!-- START DATE -->

    <div class="col-md-6 mb-3">

        <label class="form-label">
            Start Date
        </label>

        <input
            type="date"
            name="start_date"
            class="form-control"
            value="<?= htmlspecialchars($row['start_date']); ?>"
            required
        >

    </div>


    <!-- END DATE -->

    <div class="col-md-6 mb-3">

        <label class="form-label">
            End Date
        </label>

        <input
            type="date"
            name="end_date"
            class="form-control"
            value="<?= htmlspecialchars($row['end_date']); ?>"
            required
        >

    </div>


</div>


<hr class="my-4">


<!-- =====================================================
     EXISTING FILES
===================================================== -->

<h5 class="section-title">

    <i class="fas fa-folder-open text-primary"></i>

    Existing Contract Files

</h5>


<?php if($documents->num_rows > 0){ ?>


    <?php while($doc = $documents->fetch_assoc()){ ?>


        <?php

        $extension = strtolower(
            pathinfo(
                $doc['file_name'],
                PATHINFO_EXTENSION
            )
        );


        if($extension === 'pdf'){

            $icon = 'fa-file-pdf';

        }
        elseif(
            in_array(
                $extension,
                ['doc','docx']
            )
        ){

            $icon = 'fa-file-word';

        }
        elseif(
            in_array(
                $extension,
                ['xls','xlsx']
            )
        ){

            $icon = 'fa-file-excel';

        }
        elseif(
            in_array(
                $extension,
                ['jpg','jpeg','png']
            )
        ){

            $icon = 'fa-file-image';

        }
        else{

            $icon = 'fa-file';

        }

        ?>


        <div class="file-card">


            <div class="row align-items-center">


                <div class="col-md-8">


                    <div class="d-flex align-items-center">


                        <div class="file-icon me-3">

                            <i class="fas <?= $icon; ?>"></i>

                        </div>


                        <div>

                            <strong>

                                <?= htmlspecialchars(
                                    $doc['document_type']
                                ); ?>

                            </strong>


                            <br>


                            <small class="text-muted">

                                <?= htmlspecialchars(
                                    $doc['file_name']
                                ); ?>

                            </small>

                        </div>

                    </div>

                </div>


                <div class="col-md-4 text-md-end mt-3 mt-md-0">


                    <a
                        href="download_contract_file.php?id=<?= (int)$doc['id']; ?>"
                        class="btn btn-primary btn-sm"
                    >

                        <i class="fas fa-download"></i>

                        Download

                    </a>


                </div>


            </div>


        </div>


    <?php } ?>


<?php }else{ ?>


    <div class="alert alert-secondary">

        <i class="fas fa-info-circle"></i>

        No files are currently attached to this contract.

    </div>


<?php } ?>


<hr class="my-4">


<!-- =====================================================
     UPLOAD ADDITIONAL FILES
===================================================== -->

<h5 class="section-title">

    <i class="fas fa-upload text-primary"></i>

    Upload Additional Files

</h5>


<p class="text-muted">

    You can upload additional appointment, contract,
    renewal, or certification documents.

</p>


<div class="row">


    <!-- APPOINTMENT -->

    <div class="col-md-6 mb-3">

        <label class="form-label">
            Appointment
        </label>

        <input
            type="file"
            name="appointment[]"
            class="form-control"
            multiple
            accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
        >

        <small class="text-muted">
            PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG
        </small>

    </div>


    <!-- CONTRACT -->

    <div class="col-md-6 mb-3">

        <label class="form-label">
            Contract of Service
        </label>

        <input
            type="file"
            name="contract_file[]"
            class="form-control"
            multiple
            accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
        >

        <small class="text-muted">
            PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG
        </small>

    </div>


    <!-- RENEWAL -->

    <div class="col-md-6 mb-3">

        <label class="form-label">
            Renewal
        </label>

        <input
            type="file"
            name="renewal[]"
            class="form-control"
            multiple
            accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
        >

        <small class="text-muted">
            PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG
        </small>

    </div>


    <!-- CERTIFICATION -->

    <div class="col-md-6 mb-3">

        <label class="form-label">
            Certification
        </label>

        <input
            type="file"
            name="certification[]"
            class="form-control"
            multiple
            accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
        >

        <small class="text-muted">
            PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG
        </small>

    </div>


</div>


<!-- =====================================================
     BUTTONS
===================================================== -->

<div class="d-flex justify-content-between mt-4">

    <a
        href="view_contract.php?id=<?= (int)($isNewHistory ? $fromId : $id); ?>"
        class="btn btn-secondary">

        <i class="fas fa-arrow-left"></i>

        Cancel

    </a>


    <?php if($isNewHistory): ?>

        <button
            type="submit"
            name="create_contract_history"
            class="btn btn-success"
        >

            <i class="fas fa-plus"></i>

            Create New Contract

        </button>

    <?php else: ?>

        <button
            type="submit"
            name="update_contract"
            class="btn btn-success"
        >

            <i class="fas fa-save"></i>

            Update Contract

        </button>

    <?php endif; ?>

</div>


</form>


</div>

</div>


</div>


</body>

</html>
