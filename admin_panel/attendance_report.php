<?php include('session.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Attendance Reports - Admin Panel</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="vendor/daterangepicker/daterangepicker.css" rel="stylesheet">
</head>

<body id="page-top">
    <?php include('sidebar.php'); ?>
    <?php include('header.php'); ?>

    <div class="container-fluid">
        <!-- Report Filters -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Attendance Reports</h6>
            </div>
            <div class="card-body">
                <form id="reportForm">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Date Range</label>
                                <input type="text" class="form-control" id="dateRange" name="dateRange">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Department</label>
                                <select class="form-control" name="department">
                                    <option value="">All Departments</option>
                                    <?php
                                    $dept_query = "SELECT DISTINCT department FROM employees ORDER BY department";
                                    $dept_result = mysqli_query($con, $dept_query);
                                    while($dept = mysqli_fetch_assoc($dept_result)) {
                                        echo "<option value='".$dept['department']."'>".$dept['department']."</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Employee</label>
                                <select class="form-control" name="employee">
                                    <option value="">All Employees</option>
                                    <?php
                                    $emp_query = "SELECT eid, full_name FROM employees ORDER BY full_name";
                                    $emp_result = mysqli_query($con, $emp_query);
                                    while($emp = mysqli_fetch_assoc($emp_result)) {
                                        echo "<option value='".$emp['eid']."'>".$emp['full_name']."</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary btn-block">Generate Report</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Analytics Cards -->
        <div class="row">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Present</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="presentCount">0</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user-check fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Absent</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="absentCount">0</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user-times fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Half Day</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="halfDayCount">0</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user-clock fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Average Hours</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="avgHours">0</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clock fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Table -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Detailed Report</h6>
                <button class="btn btn-success" onclick="exportToExcel()">
                    <i class="fas fa-file-excel"></i> Export to Excel
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="reportTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Department</th>
                                <th>Employee</th>
                                <th>First In</th>
                                <th>Last Out</th>
                                <th>Total Hours</th>
                                <th>Status</th>
                                <th>Type</th>
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

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="vendor/daterangepicker/moment.min.js"></script>
    <script src="vendor/daterangepicker/daterangepicker.js"></script>
    <!-- Replace the local xlsx script with CDN version -->
        <script src="https://cdn.sheetjs.com/xlsx-0.19.3/package/dist/xlsx.full.min.js"></script>
    <script>
    $(document).ready(function() {
        // Initialize date range picker
        $('#dateRange').daterangepicker({
            startDate: moment().startOf('month'),
            endDate: moment().endOf('month'),
            ranges: {
               'Today': [moment(), moment()],
               'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
               'Last 7 Days': [moment().subtract(6, 'days'), moment()],
               'This Month': [moment().startOf('month'), moment().endOf('month')],
               'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        });

        // Initialize DataTable
        var table = $('#reportTable').DataTable({
            "order": [[0, "desc"]],
            "pageLength": 25
        });

        // Handle report form submission
        $('#reportForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: 'get_attendance_report.php',
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    updateReport(response);
                }
            });
        });

        // Trigger initial report generation
        $('#reportForm').submit();
    });

    function updateReport(data) {
        // Update analytics cards
        $('#presentCount').text(data.analytics.present);
        $('#absentCount').text(data.analytics.absent);
        $('#halfDayCount').text(data.analytics.halfDay);
        $('#avgHours').text(data.analytics.avgHours.toFixed(2));

        // Update table
        var table = $('#reportTable').DataTable();
        table.clear();
        table.rows.add(data.records);
        table.draw();
    }

    function exportToExcel() {
        var table = document.getElementById("reportTable");
        var wb = XLSX.utils.table_to_book(table, {sheet: "Attendance Report"});
        var dateRange = $('#dateRange').val().replace(/\//g, '-');
        XLSX.writeFile(wb, `Attendance_Report_${dateRange}.xlsx`);
    }
    </script>
</body>
</html>