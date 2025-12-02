<?php
include('session.php');
include('connection.php');

$un = $_SESSION['user_name'];
// Fetch user data
$q = "SELECT * FROM employees WHERE user_name='$un'";
$res = mysqli_query($con, $q);
$user = mysqli_fetch_assoc($res);
if (!is_array($user)) {
    echo "Profile not found.";
    exit;
}

// Fetch departments for dropdown
$departments = [];
$dept_res = mysqli_query($con, "SELECT id, name FROM departments ORDER BY name");
while ($row = mysqli_fetch_assoc($dept_res)) {
    $departments[] = $row;
}

// Handle form submission
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = mysqli_real_escape_string($con, $_POST['first_name'] ?? '');
    $last_name = mysqli_real_escape_string($con, $_POST['last_name'] ?? '');
    $email = mysqli_real_escape_string($con, $_POST['email'] ?? '');
    $gender = mysqli_real_escape_string($con, $_POST['gender'] ?? '');
    $contact = mysqli_real_escape_string($con, $_POST['contact'] ?? '');
    $designation = mysqli_real_escape_string($con, $_POST['designation'] ?? '');
    $department_id = intval($_POST['department_id'] ?? 0);
    $birthday = mysqli_real_escape_string($con, $_POST['birthday'] ?? '');
    $maritalsts = mysqli_real_escape_string($con, $_POST['maritalsts'] ?? '');
    $profile_pic = $user['profile_pic'];

    // Handle profile picture upload
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
        $new_name = uniqid('profile_', true) . '.' . $ext;
        $upload_dir = '../admin_panel/uploads/profile_pics/';
        if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }
        $dest = $upload_dir . $new_name;
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $dest)) {
            $profile_pic = $new_name;
        } else {
            $msg = '<div class="alert alert-danger">Profile picture upload failed.</div>';
        }
    }

    $update = "UPDATE employees SET first_name='$first_name', last_name='$last_name', email='$email', gender='$gender', contact='$contact', designation='$designation', department_id=$department_id, birthday='$birthday', maritalsts='$maritalsts', profile_pic='$profile_pic' WHERE user_name='$un'";
    if (mysqli_query($con, $update)) {
        header('Location: Manage_profile.php?success=1');
        exit;
    } else {
        $msg = '<div class="alert alert-danger">Update failed. Please try again.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Edit Profile</title>
    <link href="img/favicon.png" rel="icon">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); min-height: 100vh; }
        .edit-profile-card {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            border-radius: 30px;
            box-shadow: 0 8px 32px 0 rgba(31,38,135,0.15);
            padding: 2.5rem 2rem 2rem 2rem;
        }
        .edit-profile-title {
            font-size: 2rem;
            font-weight: 700;
            color: #a259f7;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .form-group label { font-weight: 600; color: #a259f7; }
        .form-control, .custom-select { border-radius: 20px; }
        .btn-primary {
            background: linear-gradient(90deg, #a259f7 0%, #f5576c 100%);
            border: none;
            border-radius: 30px;
            padding: 0.7rem 2.2rem;
            font-size: 1.1rem;
            font-weight: 700;
            box-shadow: 0 2px 8px rgba(162,89,247,0.15);
            transition: background 0.2s;
        }
        .btn-primary:hover {
            background: linear-gradient(90deg, #f5576c 0%, #a259f7 100%);
        }
        .profile-pic-preview {
            width: 100px; height: 100px; border-radius: 50%; object-fit: cover; margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="edit-profile-card">
        <div class="edit-profile-title">Edit Profile</div>
        <?php if ($msg) echo $msg; ?>
        <form method="post" enctype="multipart/form-data">
            <div class="form-group text-center">
                <img src="<?php echo $user['profile_pic'] ? '../admin_panel/uploads/profile_pics/' . htmlspecialchars($user['profile_pic']) : 'https://ui-avatars.com/api/?name=' . urlencode($user['first_name'] ?? 'User'); ?>" class="profile-pic-preview" alt="Profile Picture">
            </div>
            <div class="form-group">
                <label>Profile Picture</label>
                <input type="file" name="profile_pic" class="form-control-file">
            </div>
            <div class="form-group">
                <label>First Name</label>
                <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Last Name</label>
                <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Gender</label>
                <select name="gender" class="custom-select" required>
                    <option value="">Select Gender</option>
                    <option value="Male" <?php if (($user['gender'] ?? '') == 'Male') echo 'selected'; ?>>Male</option>
                    <option value="Female" <?php if (($user['gender'] ?? '') == 'Female') echo 'selected'; ?>>Female</option>
                    <option value="Other" <?php if (($user['gender'] ?? '') == 'Other') echo 'selected'; ?>>Other</option>
                </select>
            </div>
            <div class="form-group">
                <label>Contact</label>
                <input type="text" name="contact" class="form-control" value="<?php echo htmlspecialchars($user['contact'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Designation</label>
                <input type="text" name="designation" class="form-control" value="<?php echo htmlspecialchars($user['designation'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Department</label>
                <select name="department_id" class="custom-select" required>
                    <option value="">Select Department</option>
                    <?php foreach ($departments as $dept) { ?>
                        <option value="<?php echo $dept['id']; ?>" <?php if (($user['department_id'] ?? 0) == $dept['id']) echo 'selected'; ?>><?php echo htmlspecialchars($dept['name']); ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group">
                <label>Date of Birth</label>
                <input type="date" name="birthday" class="form-control" value="<?php echo htmlspecialchars($user['birthday'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Marital Status</label>
                <select name="maritalsts" class="custom-select">
                    <option value="">Select Status</option>
                    <option value="Single" <?php if (($user['maritalsts'] ?? '') == 'Single') echo 'selected'; ?>>Single</option>
                    <option value="Married" <?php if (($user['maritalsts'] ?? '') == 'Married') echo 'selected'; ?>>Married</option>
                    <option value="Divorced" <?php if (($user['maritalsts'] ?? '') == 'Divorced') echo 'selected'; ?>>Divorced</option>
                    <option value="Widowed" <?php if (($user['maritalsts'] ?? '') == 'Widowed') echo 'selected'; ?>>Widowed</option>
                </select>
            </div>
            <div class="form-group text-center">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="Manage_profile.php" class="btn btn-secondary ml-2">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html> 