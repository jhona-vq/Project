<?php
include "config.php";

if(isset($_POST['restore'])){

    $filename =
    $_FILES['backup_file']['tmp_name'];

    if($filename != ''){

        $sql = file_get_contents($filename);

        mysqli_multi_query($conn,$sql);

        echo "
        <script>
        alert('Database Restored Successfully');
        window.location='settings.php';
        </script>
        ";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Restore Database</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body class="bg-light">

<div class="container mt-5">

<div class="card">

<div class="card-header bg-primary text-white">
Restore Database Backup
</div>

<div class="card-body">

<form method="POST" enctype="multipart/form-data">

<label>Select SQL Backup File</label>

<input
type="file"
name="backup_file"
accept=".sql"
class="form-control mb-3"
required>

<button
type="submit"
name="restore"
class="btn btn-success">

Restore Backup

</button>

<a href="settings.php"
class="btn btn-secondary">

Back

</a>

</form>

</div>

</div>

</div>

</body>
</html>