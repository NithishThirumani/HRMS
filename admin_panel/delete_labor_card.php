<?php
include('session.php');
include('connection.php');

// Get employee ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: view_labor_cards.php');
    exit();
}

// Handle deletion confirmation
if (isset($_POST['confirm_delete'])) {
    // Clear labor card information (set to NULL)
    $clear_query = "UPDATE employees SET 
                    labour_card_no = NULL, 
                    labour_card_start_date = NULL, 
                    labour_card_end_date = NULL, 
                    MOLID = NULL 
                    WHERE id = ?";
    
    $stmt = mysqli_prepare($con, $clear_query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        $success_message = "Labor card information deleted successfully!";
        // Redirect after 2 seconds
        header("refresh:2;url=view_labor_cards.php");
    } else {
        $error_message = "Error deleting labor card information: " . mysqli_error($con);
    }
    
    mysqli_stmt_close($stmt);
}

// Fetch employee data for confirmation
$query = "SELECT id, eid, full_name, designation, EmpDiv, MOLID, 
          labour_card_no, labour_card_start_date, labour_card_end_date 
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
    <title>Delete Labor Card - <?php echo htmlspecialchars($employee['full_name']); ?></title>

    <!-- Custom fonts and styles -->
    <link href="img/favicon.png" rel="icon">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
</head>

<body id="page-top">
    <?php include('sidebar.php'); ?>
    <?php include('header.php'); ?>

    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Delete Labor Card</h1>
            <a href="view_labor_cards.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left fa-sm"></i> Back to Labor Cards
            </a>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-2"></i>
                <?php echo $success_message; ?>
                <small class="d-block mt-1">Redirecting to labor cards page...</small>
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

        <!-- Confirmation Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-danger text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Confirm Labor Card Deletion
                </h6>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <h6 class="alert-heading">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Warning!
                    </h6>
                    <p class="mb-0">You are about to delete the labor card information for this employee. This action will:</p>
                    <ul class="mb-0 mt-2">
                        <li>Remove the labour card number</li>
                        <li>Clear the start and end dates</li>
                        <li>Remove the MOL ID</li>
                        <li>This action cannot be undone</li>
                    </ul>
                </div>

                <!-- Employee Information -->
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary mb-3">Employee Information</h6>
                        <table class="table table-bordered">
                            <tr>
                                <td><strong>Employee ID:</strong></td>
                                <td><?php echo htmlspecialchars($employee['eid']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Full Name:</strong></td>
                                <td><?php echo htmlspecialchars($employee['full_name']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Designation:</strong></td>
                                <td><?php echo htmlspecialchars($employee['designation']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Department:</strong></td>
                                <td><?php echo htmlspecialchars($employee['EmpDiv']); ?></td>
                            </tr>
                        </table>
                    </div>

                    <div class="col-md-6">
                        <h6 class="text-danger mb-3">Current Labor Card Information</h6>
                        <table class="table table-bordered">
                            <tr>
                                <td><strong>Labour Card No:</strong></td>
                                <td><?php echo htmlspecialchars($employee['labour_card_no'] ?? 'Not Set'); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Start Date:</strong></td>
                                <td><?php echo $employee['labour_card_start_date'] ? date('d-m-Y', strtotime($employee['labour_card_start_date'])) : 'Not Set'; ?></td>
                            </tr>
                            <tr>
                                <td><strong>End Date:</strong></td>
                                <td><?php echo $employee['labour_card_end_date'] ? date('d-m-Y', strtotime($employee['labour_card_end_date'])) : 'Not Set'; ?></td>
                            </tr>
                            <tr>
                                <td><strong>MOL ID:</strong></td>
                                <td><?php echo htmlspecialchars($employee['MOLID'] ?? 'Not Set'); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Confirmation Form -->
                <form method="POST" action="" class="mt-4">
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-end">
                                <a href="view_labor_cards.php" class="btn btn-secondary mr-2">
                                    <i class="fas fa-times mr-1"></i> Cancel
                                </a>
                                <button type="submit" name="confirm_delete" class="btn btn-danger" 
                                        onclick="return confirm('Are you absolutely sure you want to delete this labor card information? This action cannot be undone.')">
                                    <i class="fas fa-trash-alt mr-1"></i> Delete Labor Card
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

    <?php include_once('footer.php'); ?>
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
</body>
</html>
