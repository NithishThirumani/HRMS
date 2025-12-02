<?php
include('session.php');
include('connection.php');

// Get employee ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: view_labor_cards.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $labour_card_no = mysqli_real_escape_string($con, $_POST['labour_card_no']);
    $labour_card_start_date = mysqli_real_escape_string($con, $_POST['labour_card_start_date']);
    $labour_card_end_date = mysqli_real_escape_string($con, $_POST['labour_card_end_date']);
    $MOLID = mysqli_real_escape_string($con, $_POST['MOLID']);
    
    // Update the employee record
    $update_query = "UPDATE employees SET 
                     labour_card_no = ?, 
                     labour_card_start_date = ?, 
                     labour_card_end_date = ?, 
                     MOLID = ? 
                     WHERE id = ?";
    
    $stmt = mysqli_prepare($con, $update_query);
    mysqli_stmt_bind_param($stmt, "ssssi", $labour_card_no, $labour_card_start_date, $labour_card_end_date, $MOLID, $id);
    
    if (mysqli_stmt_execute($stmt)) {
        $success_message = "Labor card information updated successfully!";
    } else {
        $error_message = "Error updating labor card information: " . mysqli_error($con);
    }
    
    mysqli_stmt_close($stmt);
}

// Fetch current employee data
$query = "SELECT id, eid, full_name, designation, EmpDiv, MOLID, 
          labour_card_no, labour_card_start_date, labour_card_end_date, status 
          FROM employees WHERE id = ?";

$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    header('Location: view_labor_cards.php');
    exit();
}

$employee = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Edit Labor Card - <?php echo htmlspecialchars($employee['full_name']); ?></title>

    <!-- Custom fonts and styles -->
    <link href="img/favicon.png" rel="icon">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>

<body id="page-top">
    <?php include('sidebar.php'); ?>
    <?php include('header.php'); ?>

    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Edit Labor Card</h1>
            <a href="view_labor_cards.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left fa-sm"></i> Back to Labor Cards
            </a>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-2"></i>
                <?php echo $success_message; ?>
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <?php echo $error_message; ?>
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <!-- Edit Form -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    Edit Labor Card for: <?php echo htmlspecialchars($employee['full_name']); ?>
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row">
                        <!-- Employee Information (Read-only) -->
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">Employee Information</h6>
                            <div class="form-group">
                                <label class="form-label">Employee ID</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($employee['eid']); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($employee['full_name']); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Designation</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($employee['designation']); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Department</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($employee['EmpDiv']); ?>" readonly>
                            </div>
                        </div>

                        <!-- Labor Card Information (Editable) -->
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">Labor Card Information</h6>
                            <div class="form-group">
                                <label class="form-label">Labour Card Number</label>
                                <input type="text" name="labour_card_no" class="form-control" 
                                       value="<?php echo htmlspecialchars($employee['labour_card_no'] ?? ''); ?>" 
                                       placeholder="Enter labour card number">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="labour_card_start_date" class="form-control" 
                                       value="<?php echo $employee['labour_card_start_date'] ?? ''; ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">End Date</label>
                                <input type="date" name="labour_card_end_date" class="form-control" 
                                       value="<?php echo $employee['labour_card_end_date'] ?? ''; ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">MOL ID</label>
                                <input type="text" name="MOLID" class="form-control" 
                                       value="<?php echo htmlspecialchars($employee['MOLID'] ?? ''); ?>" 
                                       placeholder="Enter MOL ID">
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-end">
                                <a href="view_labor_cards.php" class="btn btn-secondary mr-2">
                                    <i class="fas fa-times mr-1"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-1"></i> Update Labor Card
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Include Scripts -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>

    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    </script>

    <?php include_once('footer.php'); ?>
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
</body>
</html>
