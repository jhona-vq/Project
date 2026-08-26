<?php
include "auth.php";
include "db.php";

$user_id = $_SESSION['user_id'];

if(isset($_POST['save_profile'])){

    $full_name = mysqli_real_escape_string(
        $conn,
        $_POST['full_name']
    );

    $email = mysqli_real_escape_string(
        $conn,
        $_POST['email']
    );

    $photo = "";

    // Check kung may bagong picture
    if(!empty($_FILES['profile_photo']['name'])){

        $upload_dir = "uploads/profile/";

        if(!is_dir($upload_dir)){
            mkdir($upload_dir,0777,true);
        }

        $photo =
            time().'_'.
            $_FILES['profile_photo']['name'];

        move_uploaded_file(
            $_FILES['profile_photo']['tmp_name'],
            $upload_dir.$photo
        );

        mysqli_query($conn,"
        UPDATE users
        SET
        full_name='$full_name',
        email='$email',
        profile_photo='$photo'
        WHERE id='$user_id'
        ");

    }else{

        mysqli_query($conn,"
        UPDATE users
        SET
        full_name='$full_name',
        email='$email'
        WHERE id='$user_id'
        ");
    }

    $_SESSION['full_name'] = $full_name;

    header("Location: my_profile.php");
    exit();
}

$result = mysqli_query($conn,"
SELECT *
FROM users
WHERE id='$user_id'
");

$user = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>
<title>My Profile</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">

<div class="card p-4">

<h3>My Profile</h3>

<div class="text-center mb-4">

<?php if(!empty($user['profile_photo'])){ ?>

<img
src="uploads/profile/<?= $user['profile_photo']; ?>"
width="150"
height="150"
style="object-fit:cover"
class="rounded-circle border">

<?php }else{ ?>

<img
src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png"
width="150"
class="rounded-circle">

<?php } ?>

</div>

<form method="POST" enctype="multipart/form-data">

<div class="mb-3">
<label>Full Name</label>
<input
type="text"
name="full_name"
class="form-control"
value="<?= $user['full_name']; ?>">
</div>

<div class="mb-3">
<label>Email</label>
<input
type="email"
name="email"
class="form-control"
value="<?= $user['email']; ?>">
</div>

<div class="mb-3">
<label>Profile Photo</label>
<input
type="file"
name="profile_photo"
class="form-control">
</div>

<div class="mb-3">
<label>Role</label>
<input
type="text"
class="form-control"
value="<?= $user['role']; ?>"
readonly>
</div>

<button
type="submit"
name="save_profile"
class="btn btn-primary">
Save Changes
</button>

<a href="dashboard.php"
class="btn btn-secondary">
Back
</a>

</form>

</div>

</div>

</body>
</html>