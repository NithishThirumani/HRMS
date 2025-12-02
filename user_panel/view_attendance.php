<?php include('session.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>View Attendance History</title>
    <link href="img/favicon.png" rel="icon">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <style>
        .present { color: #28a745; font-weight: bold; }
        .absent { color: #dc3545; font-weight: bold; }
        .half-day { color: #ffc107; font-weight: bold; }
        .in-progress { color: #17a2b8; font-weight: bold; }
    </style>
</head>

<body id="page-top">
    <?php include('sidebar.php'); ?>
    <?php include('topbar.php'); ?>

    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-success">Attendance History</h6>
            </div>
            <div class="card-body">
                <!-- Date Range Filter -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <label>From Date:</label>
                        <input type="date" class="form-control" id="fromDate">
                    </div>
                    <div class="col-md-3">
                        <label>To Date:</label>
                        <input type="date" class="form-control" id="toDate">
                    </div>
                    <div class="col-md-2">
                        <label>&nbsp;</label>
                        <button class="btn btn-success form-control" onclick="filterAttendance()">Filter</button>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card border-left-success">
                            <div class="card-body">
                                <div class="text-xs font-weight-bold text-success mb-1">Present Days</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="presentCount">0</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-left-warning">
                            <div class="card-body">
                                <div class="text-xs font-weight-bold text-warning mb-1">Half Days</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="halfDayCount">0</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-left-danger">
                            <div class="card-body">
                                <div class="text-xs font-weight-bold text-danger mb-1">Absent Days</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="absentCount">0</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attendance Table -->
                <div class="table-responsive">
                    <table class="table table-bordered" id="historyTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Day</th>
                                <th>First In</th>
                                <th>Last Out</th>
                                <th>Total Hours</th>
                                <th>Status</th>
                                <th>Type</th>
                                <th>Location</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include('footer.php'); ?>

    <script>
    $(document).ready(function() {
        var table = $('#historyTable').DataTable({
            "order": [[0, "desc"]],
            "pageLength": 31
        });
        
        // Set default date range (current month)
        var date = new Date();
        var firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
        var lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);
        
        $('#fromDate').val(firstDay.toISOString().split('T')[0]);
        $('#toDate').val(lastDay.toISOString().split('T')[0]);
        
        filterAttendance();
    });

    function filterAttendance() {
        $.ajax({
            url: 'get_attendance_history.php',
            method: 'POST',
            data: {
                fromDate: $('#fromDate').val(),
                toDate: $('#toDate').val()
            },
            success: function(response) {
                var data = JSON.parse(response);
                updateTable(data.attendance);
                updateSummary(data.summary);
            }
        });
    }

    function updateTable(data) {
        var table = $('#historyTable').DataTable();
        table.clear();
        data.forEach(function(row) {
            table.row.add([
                row.attendance_date,
                row.week_day,
                row.first_in,
                row.last_out,
                row.total_hours,
                '<span class="' + row.status.toLowerCase() + '">' + row.status + '</span>',
                row.attendance_type,
                row.location_address
            ]);
        });
        table.draw();
    }

    function updateSummary(summary) {
        $('#presentCount').text(summary.present);
        $('#halfDayCount').text(summary.halfDay);
        $('#absentCount').text(summary.absent);
    }
    </script>
     <?php include_once('footer.php'); ?>

<!-- Scroll to Top Button-->
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="js/demo/datatables-demo.js"></script>
</body>
</html>