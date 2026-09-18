<?php

    include "auth.php";
    include "config.php";
    include "role_access.php";

    /* =========================================================
        ONLY SYSTEM ADMINISTRATOR AND HR ADMINISTRATOR
        CAN DELETE DOCUMENTS
    ========================================================= */

    allowRoles([
        'System Administrator',
        'HR Administrator'
    ]);


    /* =========================================================
        GET DOCUMENT ID
    ========================================================= */

    $id = intval($_GET['id'] ?? 0);

    if($id <= 0){
        header("Location: documents.php");
        exit();
    }


    /* =========================================================
        GET DOCUMENT FILE NAME
    ========================================================= */

    $stmt = $conn->prepare("
        SELECT file_name
        FROM documents
        WHERE id = ?
    ");

    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();

    if($result->num_rows === 0){
        header("Location: documents.php");
        exit();
    }

    $document = $result->fetch_assoc();

    $file_name = $document['file_name'];


    /* =========================================================
        DELETE DATABASE RECORD
    ========================================================= */

    $delete = $conn->prepare("
        DELETE FROM documents
        WHERE id = ?
    ");

    $delete->bind_param("i", $id);

    if($delete->execute()){

     /* =====================================================
       ELETE PHYSICAL FILE
    ===================================================== */

    if(!empty($file_name)){

        $safe_file = basename($file_name);

        $file_path = "uploads/documents/" . $safe_file;

        if(file_exists($file_path)){
            unlink($file_path);
        }
    }
    }


    /* =========================================================
        RETURN TO DOCUMENTS PAGE
    ========================================================= */

    header("Location: documents.php");
    exit();

?>
