<?php
include('session.php');
include('connection.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$emp_id = $_SESSION['user_id'];

// Handle form submissions
if (isset($_POST['surrender'])) {
    $leave_id = $_POST['leave_id'];

    // Update leave status to surrendered
    $stmt = $con->prepare("UPDATE leaves SET status = 'Surrendered', modified_at = NOW() WHERE id = ? AND emp_id = ? AND status = 'Approved'");
    $stmt->bind_param("ii", $leave_id, $emp_id);

    if ($stmt->execute()) {
        echo "<script>alert('Leave successfully surrendered!'); window.location.href='leaves.php';</script>";
    } else {
        echo "<script>alert('Error surrendering leave!');</script>";
    }
}

if (isset($_POST['modify_dates'])) {
    $leave_id = $_POST['leave_id'];
    $new_start = $_POST['new_start_date'];
    $new_end = $_POST['new_end_date'];

    // Calculate new total days
    $start_date = new DateTime($new_start);
    $end_date = new DateTime($new_end);
    $interval = $start_date->diff($end_date);
    $new_total_days = $interval->days + 1;

    // Update leave dates
    $stmt = $con->prepare("UPDATE leaves SET start_date = ?, end_date = ?, total_days = ?, status = 'Pending Date Change', modified_at = NOW() WHERE id = ? AND emp_id = ? AND status = 'Approved'");
    $stmt->bind_param("ssiii", $new_start, $new_end, $new_total_days, $leave_id, $emp_id);

    if ($stmt->execute()) {
        echo "<script>alert('Date modification request submitted!'); window.location.href='leaves.php';</script>";
    } else {
        echo "<script>alert('Error modifying leave dates!');</script>";
    }
}

// Get approved leaves - modify the query to only show leaves that haven't started yet
$query = "SELECT * FROM leaves WHERE emp_id = ? AND status = 'Approved' AND start_date > CURRENT_DATE ORDER BY start_date";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Modify Leave</title>
    <link href="img/favicon.png" rel="icon">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.css" rel="stylesheet">
    <script src="js/jquery-3.6.4.min.js"></script>
    <link href="vendor/select2/select2.min.css" rel="stylesheet">
    <link href="vendor/datepicker/daterangepicker.css" rel="stylesheet">
    <link rel="stylesheet" href="css/main.css">

    <style>
        .card-1 {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            background-color: #fff;
        }

        .card-body {
            padding: 2.5rem;
        }

        .title {
            color: #2c3e50;
            font-size: 24px;
            font-weight: 500;
            margin-bottom: 30px;
            border-bottom: 2px solid #4e73df;
            padding-bottom: 10px;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .input--style-1 {
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            width: 100%;
            transition: border-color 0.3s;
            margin-bottom: 1rem;
        }

        .input--style-1:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
            outline: none;
        }

        label {
            color: #34495e;
            font-weight: 500;
            margin-bottom: 0.5rem;
            display: block;
        }

        .btn--radius {
            border-radius: 5px;
            padding: 12px 24px;
            font-weight: 500;
        }

        #leave_summary {
            background-color: #f8f9fc;
            border: 1px solid #e3e6f0;
            border-radius: 8px;
            padding: 20px;
        }

        #leave_summary h5 {
            color: #2c3e50;
            font-size: 18px;
            margin-bottom: 15px;
        }

        #certificate_upload {
            background-color: #f8f9fc;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 1.5rem;
        }

        .text-muted {
            color: #6c757d;
            font-size: 0.875rem;
        }

        .wrapper--w680 {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }

        .page-wrapper {
            min-height: calc(100vh - 60px);
            background-color: #f8f9fc;
            padding: 40px 0;
        }


        .table thead th {
            background-color: #4e73df;
            color: #ffffff;
            border-color: #4668ce;
            vertical-align: middle;
            padding: 12px 15px;
            font-weight: 500;
        }

        .table-bordered thead th {
            border-bottom-width: 1px;
        }

        .table tbody tr:hover {
            background-color: #f8f9fc;
        }

        .table {
            margin-bottom: 0;
            border: 1px solid #e3e6f0;
        }

        .table td {
            vertical-align: middle;
            padding: 12px 15px;
            white-space: nowrap;
        }

        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.875rem;
        }

        .action-buttons {
            white-space: nowrap;
            display: flex;
            gap: 10px;
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
    </style>

</head>

<body id="page-top">
    <?php include('sidebar.php'); ?>
    <?php include('topbar.php'); ?>

    <div class="container-fluid">
        <form id="registrationForm" method="POST" enctype="multipart/form-data">
            <div class="page-wrapper">
                <div class="wrapper--w680">
                    <div class="card card-1">
                        <div class="card-body">
                            <h2 class="title">Modify Approved Leaves</h2>

                            <?php if ($result->num_rows > 0) { ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Leave Type</th>
                                                <th>Start Date</th>
                                                <th>End Date</th>
                                                <th>Total Days</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($row = $result->fetch_assoc()) { ?>
                                                <tr>
                                                    <td><?php echo $row['type_of_leave']; ?></td>
                                                    <td><?php echo $row['start_date']; ?></td>
                                                    <td><?php echo $row['end_date']; ?></td>
                                                    <td><?php echo $row['total_days']; ?></td>
                                                    <td>
                                                        <div class="action-buttons">
                                                            <button class="btn btn-warning btn-sm"
                                                                onclick="showModifyModal(<?php echo $row['id']; ?>, '<?php echo $row['start_date']; ?>', '<?php echo $row['end_date']; ?>')">
                                                                Modify Dates
                                                            </button>
                                                            <button class="btn btn-danger btn-sm"
                                                                onclick="confirmSurrender(<?php echo $row['id']; ?>)">
                                                                Surrender Leave
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php } else { ?>
                                <div class="alert alert-info">No approved upcoming leaves found.</div>
                            <?php } ?>
                        </div>

                        <!-- Modify Dates Modal -->
                        <div class="modal fade" id="modifyModal" tabindex="-1" role="dialog">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Modify Leave Dates</h5>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <form method="POST">
                                        <div class="modal-body">
                                            <input type="hidden" name="leave_id" id="modify_leave_id">
                                            <div class="form-group">
                                                <label>New Start Date</label>
                                                <input type="date" class="form-control" name="new_start_date"
                                                    id="new_start_date" required>
                                            </div>
                                            <div class="form-group">
                                                <label>New End Date</label>
                                                <input type="date" class="form-control" name="new_end_date"
                                                    id="new_end_date" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-dismiss="modal">Close</button>
                                            <button type="submit" name="modify_dates"
                                                class="btn btn-primary">Submit</button>
                                        </div>
                                </div>
                            </div>
                        </div>
                    </div>
        </form>
    </div>
    </div>
    </div>

    <!-- Surrender Form (Hidden) -->
    <form id="surrenderForm" method="POST" style="display: none;">
        <input type="hidden" name="leave_id" id="surrender_leave_id">
        <input type="hidden" name="surrender" value="1">
    </form>

    <?php include('footer.php'); ?>

    <script>
        function showModifyModal(leaveId, startDate, endDate) {
            // Check if leave period has already started
            const today = new Date();
            const leaveStart = new Date(startDate);

            if (leaveStart <= today) {
                alert('Cannot modify leaves that have already started or passed.');
                return;
            }

            document.getElementById('modify_leave_id').value = leaveId;
            document.getElementById('new_start_date').value = startDate;
            document.getElementById('new_end_date').value = endDate;

            // Set minimum date as tomorrow and maximum as original start date
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            document.getElementById('new_start_date').min = tomorrow.toISOString().split('T')[0];
            document.getElementById('new_start_date').max = startDate;
            document.getElementById('new_end_date').min = tomorrow.toISOString().split('T')[0];

            $('#modifyModal').modal('show');
        }

        function confirmSurrender(leaveId) {
            if (confirm('Are you sure you want to surrender this leave?')) {
                document.getElementById('surrender_leave_id').value = leaveId;
                document.getElementById('surrenderForm').submit();
            }
        }

        // Validate dates
        document.getElementById('new_end_date').addEventListener('change', function () {
            var startDate = new Date(document.getElementById('new_start_date').value);
            var endDate = new Date(this.value);
            var today = new Date();

            if (endDate < startDate) {
                alert('End date cannot be before start date');
                this.value = document.getElementById('new_start_date').value;
            }

            if (startDate <= today) {
                alert('Cannot select dates before or on current date');
                this.value = '';
            }
        });

        document.getElementById('new_start_date').addEventListener('change', function () {
            var selectedDate = new Date(this.value);
            var today = new Date();

            if (selectedDate <= today) {
                alert('Cannot select dates before or on current date');
                this.value = '';
            }
        });
    </script>


    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="js/demo/datatables-demo.js"></script>
</body>
</body>

</html>