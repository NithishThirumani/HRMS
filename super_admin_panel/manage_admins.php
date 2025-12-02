<?php
session_start();
require_once('../connection.php');

// Check if user is logged in
if (!isset($_SESSION['email']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'super_admin') {
    header("Location: ../login.php");
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    switch ($action) {
        case 'add':
            $username = mysqli_real_escape_string($con, $_POST['username']);
            $email = mysqli_real_escape_string($con, $_POST['email']);
            $gender = mysqli_real_escape_string($con, $_POST['gender']);
            $contact = mysqli_real_escape_string($con, $_POST['contact']);
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            
            // Handle file upload
            $pic = '';
            if (isset($_FILES['pic']) && $_FILES['pic']['error'] == 0) {
                $target_dir = "../uploads/profile_pics/";
                $pic = time() . '_' . basename($_FILES["pic"]["name"]);
                move_uploaded_file($_FILES["pic"]["tmp_name"], $target_dir . $pic);
            }
            
            $query = "INSERT INTO admin (user_name, email, gender, contact, pic, role, password, status) 
                     VALUES ('$username', '$email', '$gender', '$contact', '$pic', 'admin', '$password', 'Active')";
            
            if (mysqli_query($con, $query)) {
                $_SESSION['success'] = "Administrator added successfully!";
            } else {
                $_SESSION['error'] = "Error adding administrator: " . mysqli_error($con);
            }
            break;
            
        case 'edit':
            $id = mysqli_real_escape_string($con, $_POST['admin_id']);
            $username = mysqli_real_escape_string($con, $_POST['username']);
            $email = mysqli_real_escape_string($con, $_POST['email']);
            $gender = mysqli_real_escape_string($con, $_POST['gender']);
            $contact = mysqli_real_escape_string($con, $_POST['contact']);
            
            $query = "UPDATE admin SET user_name='$username', email='$email', gender='$gender', contact='$contact' 
                     WHERE id='$id' AND role='admin'";
            
            if (mysqli_query($con, $query)) {
                $_SESSION['success'] = "Administrator updated successfully!";
            } else {
                $_SESSION['error'] = "Error updating administrator: " . mysqli_error($con);
            }
            break;
            
        case 'delete':
            $id = mysqli_real_escape_string($con, $_POST['admin_id']);
            $query = "DELETE FROM admin WHERE id='$id' AND role='admin'";
            if (mysqli_query($con, $query)) {
                $_SESSION['success'] = "Administrator deleted successfully!";
            } else {
                $_SESSION['error'] = "Error deleting administrator: " . mysqli_error($con);
            }
            break;
            
        case 'toggle_status':
            $id = mysqli_real_escape_string($con, $_POST['admin_id']);
            $status = $_POST['status'] == 'Active' ? 'Inactive' : 'Active';
            $query = "UPDATE admin SET status='$status' WHERE id='$id' AND role='admin'";
            if (mysqli_query($con, $query)) {
                $_SESSION['success'] = "Administrator status updated successfully!";
            } else {
                $_SESSION['error'] = "Error updating administrator status: " . mysqli_error($con);
            }
            break;
    }
    header("Location: manage_admins.php");
    exit();
}

// Get all admins
$admin_query = "SELECT * FROM admin WHERE role='admin' ORDER BY user_name";
$admin_result = mysqli_query($con, $admin_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Administrators</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            height: 100vh;
            background: #343a40;
            color: white;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            display: block;
        }
        .sidebar a:hover {
            background: #454d55;
        }
        .card {
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .footer {
            background: #f8f9fa;
            padding: 1rem 0;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
        .admin-table {
            background: white;
            border-radius: 8px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        .admin-table th {
            background: #4e73df;
            color: white;
        }
        .profile-pic {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <h4 class="text-center py-3">Super Admin Panel</h4>
                <a href="index.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
                <a href="manage_admins.php" class="active"><i class="fas fa-users-cog me-2"></i> Manage Admins</a>
                <a href="system_settings.php"><i class="fas fa-cogs me-2"></i> System Settings</a>
                <a href="backup_database.php"><i class="fas fa-database me-2"></i> Database Backup</a>
                <a href="audit_logs.php"><i class="fas fa-history me-2"></i> Audit Logs</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 ms-auto">
                <div class="container mt-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>Manage Administrators</h2>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAdminModal">
                            <i class="fas fa-user-plus"></i> Add New Administrator
                        </button>
                    </div>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php 
                                echo $_SESSION['success'];
                                unset($_SESSION['success']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php 
                                echo $_SESSION['error'];
                                unset($_SESSION['error']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Admins Table -->
                    <div class="admin-table p-4">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Administrator</th>
                                    <th>Email</th>
                                    <th>Gender</th>
                                    <th>Contact</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($admin = mysqli_fetch_assoc($admin_result)): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if ($admin['pic']): ?>
                                                    <img src="../uploads/profile_pics/<?php echo htmlspecialchars($admin['pic']); ?>" 
                                                         class="profile-pic me-2">
                                                <?php endif; ?>
                                                <?php echo htmlspecialchars($admin['user_name']); ?>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($admin['email']); ?></td>
                                        <td><?php echo htmlspecialchars($admin['gender']); ?></td>
                                        <td><?php echo htmlspecialchars($admin['contact']); ?></td>
                                        <td>
                                            <div class="form-check form-switch">
                                                <input type="checkbox" class="form-check-input status-toggle" 
                                                       data-id="<?php echo $admin['id']; ?>"
                                                       <?php echo $admin['status'] == 'Active' ? 'checked' : ''; ?>>
                                                <label class="form-check-label">
                                                    <?php echo $admin['status']; ?>
                                                </label>
                                            </div>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary edit-admin" 
                                                    data-id="<?php echo $admin['id']; ?>"
                                                    data-username="<?php echo htmlspecialchars($admin['user_name']); ?>"
                                                    data-email="<?php echo htmlspecialchars($admin['email']); ?>"
                                                    data-gender="<?php echo htmlspecialchars($admin['gender']); ?>"
                                                    data-contact="<?php echo htmlspecialchars($admin['contact']); ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-admin"
                                                    data-id="<?php echo $admin['id']; ?>"
                                                    data-username="<?php echo htmlspecialchars($admin['user_name']); ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Admin Modal -->
    <div class="modal fade" id="addAdminModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Administrator</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="" method="POST" enctype="multipart/form-data" id="addAdminForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Gender</label>
                            <select name="gender" class="form-control" required>
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Contact</label>
                            <input type="text" name="contact" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Profile Picture</label>
                            <input type="file" name="pic" class="form-control" accept="image/*">
                        </div>
                        <div class="mb-3">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Administrator</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Admin Modal -->
    <div class="modal fade" id="editAdminModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Administrator</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="admin_id" id="edit_admin_id">
                        <div class="mb-3">
                            <label>Username</label>
                            <input type="text" name="username" id="edit_username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" id="edit_email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Gender</label>
                            <select name="gender" id="edit_gender" class="form-control" required>
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Contact</label>
                            <input type="text" name="contact" id="edit_contact" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Admin Modal -->
    <div class="modal fade" id="deleteAdminModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Administrator</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="admin_id" id="delete_admin_id">
                        <p>Are you sure you want to delete administrator <strong id="delete_admin_username"></strong>?</p>
                        <p class="text-danger">This action cannot be undone!</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Administrator</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Status Toggle Form -->
    <form id="statusForm" action="" method="POST" style="display: none;">
        <input type="hidden" name="action" value="toggle_status">
        <input type="hidden" name="admin_id" id="status_admin_id">
        <input type="hidden" name="status" id="status_value">
    </form>

    <!-- Footer -->
    <footer class="footer text-center">
        <div class="container">
            <span>Copyright © <?php echo date('Y'); ?> Employeeshub. All rights reserved.</span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        // Edit Admin
        $('.edit-admin').click(function() {
            var id = $(this).data('id');
            var username = $(this).data('username');
            var email = $(this).data('email');
            var gender = $(this).data('gender');
            var contact = $(this).data('contact');

            $('#edit_admin_id').val(id);
            $('#edit_username').val(username);
            $('#edit_email').val(email);
            $('#edit_gender').val(gender);
            $('#edit_contact').val(contact);

            $('#editAdminModal').modal('show');
        });

        // Delete Admin
        $('.delete-admin').click(function() {
            var id = $(this).data('id');
            var username = $(this).data('username');

            $('#delete_admin_id').val(id);
            $('#delete_admin_username').text(username);

            $('#deleteAdminModal').modal('show');
        });

        // Status Toggle
        $('.status-toggle').change(function() {
            var id = $(this).data('id');
            var status = $(this).prop('checked') ? 'Active' : 'Inactive';

            $('#status_admin_id').val(id);
            $('#status_value').val(status);
            $('#statusForm').submit();
        });

        // Form Validation
        $('#addAdminForm').submit(function(e) {
            var password = $('input[name="password"]').val();
            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long');
            }
        });
    });
    </script>
</body>
</html>