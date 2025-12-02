<?php include('session.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Update Profile - Employee Portal</title>
    
    <link href="img/favicon.png" rel="icon">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.css" rel="stylesheet">
    <link href="css/main.css" rel="stylesheet">
    
    <style>
        .profile-update-container {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 0 30px rgba(0,0,0,0.1);
            padding: 40px;
            margin: 30px auto;
            max-width: 800px;
        }
        .profile-header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f8f9fc;
        }
        .profile-picture-section {
            position: relative;
            display: inline-block;
            margin-bottom: 20px;
        }
        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid #4e73df;
            box-shadow: 0 0 20px rgba(78, 115, 223, 0.3);
        }
        .profile-picture-overlay {
            position: absolute;
            bottom: 0;
            right: 0;
            background: #4e73df;
            color: white;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .profile-picture-overlay:hover {
            background: #2e59d9;
            transform: scale(1.1);
        }
        .form-section {
            background: #f8f9fc;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 25px;
        }
        .form-section h5 {
            color: #4e73df;
            margin-bottom: 20px;
            font-weight: 600;
            border-bottom: 2px solid #4e73df;
            padding-bottom: 10px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-label {
            font-weight: 600;
            color: #5a5c69;
            margin-bottom: 8px;
        }
        .form-control {
            border: 2px solid #e3e6f0;
            border-radius: 8px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }
        .btn-update {
            background: linear-gradient(135deg, #4e73df 0%, #2e59d9 100%);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-update:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(78, 115, 223, 0.4);
        }
        .btn-cancel {
            background: #858796;
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-cancel:hover {
            background: #6c757d;
            transform: translateY(-2px);
        }
        .alert {
            border-radius: 10px;
            border: none;
            padding: 15px 20px;
        }
        .file-input-wrapper {
            position: relative;
            display: inline-block;
            cursor: pointer;
        }
        .file-input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        .required-field::after {
            content: " *";
            color: #e74a3b;
        }
        .help-text {
            font-size: 12px;
            color: #858796;
            margin-top: 5px;
        }
    </style>
</head>

<body id="page-top">
    <?php include('sidebar.php'); ?>
    <?php include('topbar.php'); ?>

    <div class="container-fluid">
        <?php
        include('connection.php');
        
        // Get user data
        $un = $_SESSION['user_name'];
        $query = "SELECT * FROM employees WHERE user_name = ?";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "s", $un);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_array($result);
        
        // Fetch departments for dropdown
        $departments = [];
        $dept_query = "SELECT id, name FROM departments ORDER BY name ASC";
        $dept_result = mysqli_query($con, $dept_query);
        if ($dept_result) {
            while ($dept_row = mysqli_fetch_assoc($dept_result)) {
                $departments[] = $dept_row;
            }
        }
        
        // Handle form submission
        if(isset($_POST['update_profile'])) {
            $success = true;
            $errors = [];
            
            // Validate and sanitize input
            $first_name = trim($_POST['first_name']);
            $last_name = trim($_POST['last_name']);
            $email = trim($_POST['email']);
            $contact = trim($_POST['contact']);
            $dob = $_POST['dob'];
            $gender = $_POST['gender'];
            $address = trim($_POST['address']);
            $department_id = $_POST['department_id'] ?? null;
            $degree = trim($_POST['degree']);
            
            // Validation
            if(empty($first_name)) {
                $errors[] = "First name is required";
                $success = false;
            }
            if(empty($last_name)) {
                $errors[] = "Last name is required";
                $success = false;
            }
            if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Valid email is required";
                $success = false;
            }
            if(empty($contact)) {
                $errors[] = "Contact number is required";
                $success = false;
            }
            
            // Backend validation for department_id before update
            if (empty($department_id) || !ctype_digit($department_id)) {
                $errors[] = "Please select a valid department.";
                $success = false;
            } else {
                // Check if department exists
                $dept_check = mysqli_prepare($con, "SELECT id FROM departments WHERE id = ?");
                mysqli_stmt_bind_param($dept_check, "i", $department_id);
                mysqli_stmt_execute($dept_check);
                mysqli_stmt_store_result($dept_check);
                if (mysqli_stmt_num_rows($dept_check) == 0) {
                    $errors[] = "Selected department does not exist.";
                    $success = false;
                }
                mysqli_stmt_close($dept_check);
            }
            
            // Handle profile picture upload
            $profile_pic = $user['profile_pic']; // Keep existing if no new upload
            if(!empty($_FILES['profile_pic']['name'])) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
                $file_type = $_FILES['profile_pic']['type'];
                
                if(!in_array($file_type, $allowed_types)) {
                    $errors[] = "Please upload only JPG, JPEG or PNG files";
                    $success = false;
                } else {
                    $upload_dir = "../admin_panel/uploads/profile_pics/";
                    if(!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $file_extension = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
                    $new_filename = uniqid() . '_' . $user['eid'] . '.' . $file_extension;
                    $target_path = $upload_dir . $new_filename;
                    
                    if(move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_path)) {
                        $profile_pic = $new_filename;
                    } else {
                        $errors[] = "Error uploading profile picture";
                        $success = false;
                    }
                }
            }
            
            // Update database if validation passes
            if($success) {
                $update_query = "UPDATE employees SET 
                    first_name = ?, 
                    last_name = ?, 
                    email = ?,
                    contact = ?,
                    birthday = ?,
                    gender = ?,
                    address = ?,
                    department_id = ?,
                    degree = ?,
                    profile_pic = ?
                    WHERE user_name = ?";
                
                $stmt = mysqli_prepare($con, $update_query);
                mysqli_stmt_bind_param($stmt, "sssssssssss", 
                    $first_name, $last_name, $email, $contact, $dob, 
                    $gender, $address, $department_id, $degree, $profile_pic, $un);
                
                if(mysqli_stmt_execute($stmt)) {
                    echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                            <i class='fas fa-check-circle mr-2'></i>
                            Profile updated successfully!
                            <button type='button' class='close' data-dismiss='alert'>
                                <span>&times;</span>
                            </button>
                          </div>";
                    
                    // Refresh user data
                    $query = "SELECT * FROM employees WHERE user_name = ?";
                    $stmt2 = mysqli_prepare($con, $query);
                    mysqli_stmt_bind_param($stmt2, "s", $un);
                    mysqli_stmt_execute($stmt2);
                    $result2 = mysqli_stmt_get_result($stmt2);
                    $user = mysqli_fetch_array($result2);
                } else {
                    echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                            <i class='fas fa-exclamation-triangle mr-2'></i>
                            Error updating profile: " . mysqli_error($con) . "
                            <button type='button' class='close' data-dismiss='alert'>
                                <span>&times;</span>
                            </button>
                          </div>";
                }
            } else {
                echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                        <i class='fas fa-exclamation-triangle mr-2'></i>
                        " . implode('<br>', $errors) . "
                        <button type='button' class='close' data-dismiss='alert'>
                            <span>&times;</span>
                        </button>
                      </div>";
            }
        }
        ?>

        <div class="profile-update-container">
            <div class="profile-header">
                <h2 class="h3 text-gray-800 mb-3">
                    <i class="fas fa-user-edit mr-2"></i>Update Profile
                </h2>
                <p class="text-muted">Keep your information up to date</p>
            </div>

            <form method="POST" enctype="multipart/form-data" id="profileForm">
                <!-- Profile Picture Section -->
                <div class="form-section">
                    <h5><i class="fas fa-camera mr-2"></i>Profile Picture</h5>
                    <div class="text-center">
                        <div class="profile-picture-section">
                            <img src="../admin_panel/uploads/profile_pics/<?php echo $user['profile_pic'] ?: 'default.jpg'; ?>" 
                                 alt="Profile Picture" 
                                 class="profile-picture"
                                 id="profilePreview"
                                 onerror="this.src='img/undraw_profile.svg'">
                            <div class="profile-picture-overlay" onclick="document.getElementById('profilePicInput').click()">
                                <i class="fas fa-camera"></i>
                            </div>
                        </div>
                        <input type="file" 
                               id="profilePicInput" 
                               name="profile_pic" 
                               accept="image/*" 
                               style="display: none;"
                               onchange="previewImage(this)">
                        <p class="help-text mt-2">Click the camera icon to change your profile picture</p>
                    </div>
                </div>

                <!-- Personal Information -->
                <div class="form-section">
                    <h5><i class="fas fa-user mr-2"></i>Personal Information</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label required-field">First Name</label>
                                <input type="text" 
                                       class="form-control" 
                                       name="first_name" 
                                       value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" 
                                       required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label required-field">Last Name</label>
                                <input type="text" 
                                       class="form-control" 
                                       name="last_name" 
                                       value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" 
                                       required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label required-field">Email Address</label>
                                <input type="email" 
                                       class="form-control" 
                                       name="email" 
                                       value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" 
                                       required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label required-field">Contact Number</label>
                                <input type="tel" 
                                       class="form-control" 
                                       name="contact" 
                                       value="<?php echo htmlspecialchars($user['contact'] ?? ''); ?>" 
                                       required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" 
                                       class="form-control" 
                                       name="dob" 
                                       value="<?php echo $user['birthday'] ?? ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Gender</label>
                                <select class="form-control" name="gender">
                                    <option value="">Select Gender</option>
                                    <option value="Male" <?php echo ($user['gender'] ?? '') == 'Male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo ($user['gender'] ?? '') == 'Female' ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other" <?php echo ($user['gender'] ?? '') == 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" 
                                  name="address" 
                                  rows="3" 
                                  placeholder="Enter your complete address"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>
                </div>

                <!-- Professional Information -->
                <div class="form-section">
                    <h5><i class="fas fa-briefcase mr-2"></i>Professional Information</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label required-field">Department</label>
                                <select class="form-control" name="department_id" required>
                                    <option value="">Select Department</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?php echo $dept['id']; ?>" <?php echo (($user['department_id'] ?? '') == $dept['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($dept['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Degree/Qualification</label>
                                <input type="text" 
                                       class="form-control" 
                                       name="degree" 
                                       value="<?php echo htmlspecialchars($user['degree'] ?? ''); ?>" 
                                       placeholder="e.g., Bachelor's in Computer Science">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="text-center mt-4">
                    <button type="submit" name="update_profile" class="btn btn-update btn-lg mr-3">
                        <i class="fas fa-save mr-2"></i>Update Profile
                    </button>
                    <a href="Manage_profile.php" class="btn btn-cancel btn-lg">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <?php include('footer.php'); ?>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>

    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profilePreview').src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Form validation
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            var requiredFields = document.querySelectorAll('.required-field');
            var isValid = true;

            requiredFields.forEach(function(field) {
                var input = field.parentElement.querySelector('input, select, textarea');
                if (!input.value.trim()) {
                    input.style.borderColor = '#e74a3b';
                    isValid = false;
                } else {
                    input.style.borderColor = '#e3e6f0';
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields marked with *');
            }
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>