<?php
include "auth.php";
include "config.php";

$id = $_GET['id'];

$result = $conn->query("
SELECT *
FROM users
WHERE id='$id'
");

$user = $result->fetch_assoc();

if(isset($_POST['update_user'])){

    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $status = $_POST['status'];

    $conn->query("
    UPDATE users
    SET
        full_name='$full_name',
        email='$email',
        role='$role',
        status='$status'
    WHERE id='$id'
    ");

    header("Location: users.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit User</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body class="bg-light">

<div class="container mt-5">

<div class="card">
<div class="card-header">
<h4>Edit User</h4>
</div>

<div class="card-body">

<form method="POST">

<div class="mb-3">
<label>Full Name</label>
<input
type="text"
name="full_name"
class="form-control"
value="<?= $user['full_name']; ?>"
required>
</div>

<div class="mb-3">
<label>Email</label>
<input
type="email"
name="email"
class="form-control"
value="<?= $user['email']; ?>"
required>
</div>

<div class="mb-3">
<label>Role</label>

<select name="role" class="form-control">

<option <?= ($user['role']=="System Administrator")?'selected':''; ?>>
System Administrator
</option>

<option <?= ($user['role']=="HR Administrator")?'selected':''; ?>>
HR Administrator
</option>

</select>

</div>

<div class="mb-3">
<label>Status</label>

<select name="status" class="form-control">

<option <?= ($user['status']=="Active")?'selected':''; ?>>
Active
</option>

<option <?= ($user['status']=="Inactive")?'selected':''; ?>>
Inactive
</option>

</select>

<div class="mb-3">
<label>New Password</label>
<input
type="password"
name="password"
class="form-control">
<small>Leave blank if no changes.</small>
</div>

</div>

<button
type="submit"
name="update_user"
class="btn btn-primary">
Update User
</button>

<a href="users.php" class="btn btn-secondary">
Cancel
</a>

</form>

</div>
</div>

</div>

</body>
</html>