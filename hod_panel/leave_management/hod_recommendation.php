<?php
include('../session.php');
include('../connection.php');
include('includes/leave_functions.php');

// Check if user is logged in
if (!isset($_SESSION['eid'])) {
    header("Location: ../../login.php");
    exit();
}

$eid = $_SESSION['eid'];

// Get the numeric ID of the current HOD
$hod_numeric_id = null;
$stmt = $con->prepare("SELECT id FROM employees WHERE eid = ?");
$stmt->bind_param("s", $eid);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $hod_numeric_id = $result->fetch_assoc()['id'];
}

// Get leaves pending recommendation
$query = "SELECT l.*, e.full_name as employee_name, e.email as employee_email,
          d.name as dept_name, e.department_id
          FROM leaves l 
          LEFT JOIN employees e ON l.emp_id = e.eid 
          LEFT JOIN departments d ON e.department_id = d.id
          WHERE l.status = 'Pending' 
          AND (l.recommender_id IS NULL OR l.recommender_id = 0)
          AND (
              l.hod_id = ? 
              OR EXISTS (
                  SELECT 1 FROM leave_hierarchy lh 
                  WHERE lh.employee_id = e.id 
                  AND lh.recommender_id = ? 
                  AND lh.type = 'recommender'
              )
          )
          ORDER BY l.applied_at DESC";

$stmt = $con->prepare($query);
$stmt->bind_param("ii", $hod_numeric_id, $hod_numeric_id);
$stmt->execute();
$result = $stmt->get_result();

$pending_recommendations = [];
while ($row = $result->fetch_assoc()) {
    $pending_recommendations[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>HOD Recommendation</title>
    <link href="img/favicon.png" rel="icon">
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <link href="../vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/custom.css">
    <style>
        .action-buttons .btn {
            margin: 2px;
        }
        .status-badge {
            font-size: 0.8em;
        }
    </style>
</head>
<body id="page-top">
<div id="wrapper">
    <?php include('../sidebar.php'); ?>
  
<div id="content-wrapper" class="d-flex flex-column">
<div id="content">
    <?php include('../header.php'); ?>
    
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">HOD Recommendation</h1>
            <a href="hod_approval.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-check fa-sm text-white-50"></i> Go to Approval
            </a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['success']; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['error']; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Leaves Pending Recommendation</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($pending_recommendations)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="recommendationTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Department</th>
                                    <th>Leave Type</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Total Days</th>
                                    <th>Reason</th>
                                    <th>Applied On</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pending_recommendations as $leave): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($leave['employee_name'] ?? $leave['user_name']); ?></td>
                                    <td><?php echo htmlspecialchars($leave['dept_name']); ?></td>
                                    <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $leave['type_of_leave']))); ?></td>
                                    <td><?php echo htmlspecialchars(date('d M Y', strtotime($leave['start_date']))); ?></td>
                                    <td><?php echo htmlspecialchars(date('d M Y', strtotime($leave['end_date']))); ?></td>
                                    <td><?php echo htmlspecialchars($leave['total_days']); ?></td>
                                    <td><?php echo htmlspecialchars($leave['reason']); ?></td>
                                    <td><?php echo htmlspecialchars(date('d M Y H:i', strtotime($leave['applied_at']))); ?></td>
                                    <td class="action-buttons">
                                        <button class="btn btn-success btn-sm" onclick="recommendLeave(<?php echo $leave['id']; ?>)">
                                            <i class="fas fa-thumbs-up"></i> Recommend
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="notRecommendLeave(<?php echo $leave['id']; ?>)">
                                            <i class="fas fa-thumbs-down"></i> Not Recommend
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No leaves pending recommendation.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
    <?php include('../footer.php'); ?>
</div>
</div>

<!-- Recommendation Modal -->
<div class="modal fade" id="recommendationModal" tabindex="-1" role="dialog" aria-labelledby="recommendationModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="recommendationModalLabel">Recommend Leave</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="recommendationForm">
                <div class="modal-body">
                    <input type="hidden" id="leaveId" name="leave_id">
                    <input type="hidden" id="actionType" name="action" value="recommend">
                    
                    <div class="form-group">
                        <label for="remarks">Remarks (Optional)</label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="3" placeholder="Enter your remarks..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Recommend</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="../vendor/jquery/jquery.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="../js/sb-admin-2.min.js"></script>
<script src="../vendor/datatables/jquery.dataTables.min.js"></script>
<script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    $('#recommendationTable').DataTable({
        "order": [[7, "desc"]] // Sort by applied date
    });
});

function recommendLeave(leaveId) {
    $('#leaveId').val(leaveId);
    $('#actionType').val('recommend');
    $('#remarks').val('');
    $('.modal-title').text('Recommend Leave');
    $('.btn-success').text('Recommend').removeClass('btn-danger').addClass('btn-success');
    $('#recommendationModal').modal('show');
}

function notRecommendLeave(leaveId) {
    $('#leaveId').val(leaveId);
    $('#actionType').val('reject');
    $('#remarks').val('');
    $('.modal-title').text('Not Recommend Leave');
    $('.btn-success').text('Not Recommend').removeClass('btn-success').addClass('btn-danger');
    $('#recommendationModal').modal('show');
}

$('#recommendationForm').on('submit', function(e) {
    e.preventDefault();
    
    var actionType = $('#actionType').val();
    var remarks = $('#remarks').val();
    
    if (actionType === 'reject' && !remarks.trim()) {
        alert('Please provide a reason for not recommending this leave.');
        $('#remarks').focus();
        return;
    }
    
    $.ajax({
        url: 'process/process_recommendation.php',
        type: 'POST',
        data: {
            leave_id: $('#leaveId').val(),
            action: actionType,
            remarks: remarks
        },
        success: function(response) {
            try {
                var result = JSON.parse(response);
                if (result.success) {
                    $('#recommendationModal').modal('hide');
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (e) {
                alert('An error occurred. Please try again.');
            }
        },
        error: function() {
            alert('An error occurred. Please try again.');
        }
    });
});
</script>
</body>
</html> 