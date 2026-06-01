<?php include('session.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Manage Attendance - Admin Panel</title>
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
    <?php include('header.php'); ?>

    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Manage Employee Attendance</h6>
                <div>
                    <button class="btn btn-primary" data-toggle="modal" data-target="#bulkAttendanceModal">
                        Bulk Update
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Filters -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <select class="form-control" id="departmentFilter">
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
                    <div class="col-md-3">
                        <select class="form-control" id="shiftFilter">
                            <option value="">All Shifts</option>
                            <option value="day">Day Shift</option>
                            <option value="swing">Swing Shift</option>
                            <option value="night">Night Shift</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="date" class="form-control" id="dateFilter" 
                               value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="col-md-3">
                        <select class="form-control" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="Present">Present</option>
                            <option value="Absent">Absent</option>
                            <option value="Half Day">Half Day</option>
                            <option value="In Progress">In Progress</option>
                        </select>
                    </div>
                </div>

                <!-- Attendance Table -->
                <div class="table-responsive">
                    <table class="table table-bordered" id="attendanceTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAll"></th>
                                <th>Department</th>
                                <th>Name</th>
                                <th>Staff Code</th>
                                <th>Date</th>
                                <th>First In</th>
                                <th>Last Out</th>
                                <th>Total Hours</th>
                                <th>Status</th>
                                <th>Type</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT a.*, e.department, e.full_name, e.eid as staff_code 
                                     FROM attendance a 
                                     INNER JOIN employees e ON a.eid = e.eid 
                                     ORDER BY a.attendance_date DESC";
                            $result = mysqli_query($con, $query);
                            
                            while($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td><input type='checkbox' class='employee-checkbox' value='".htmlspecialchars($row['eid'])."'></td>";
                                echo "<td>".htmlspecialchars($row['department'])."</td>";
                                echo "<td>".htmlspecialchars($row['full_name'])."</td>";
                                echo "<td>".htmlspecialchars($row['staff_code'])."</td>";
                                echo "<td>".htmlspecialchars($row['attendance_date'])."</td>";
                                echo "<td>".htmlspecialchars($row['first_in'] ?? '')."</td>";
                                echo "<td>".htmlspecialchars($row['last_out'] ?? '')."</td>";
                                echo "<td>".htmlspecialchars($row['total_hours'] ?? '')."</td>";
                                echo "<td class='".strtolower(str_replace(' ', '-', $row['status'] ?? ''))."'>".htmlspecialchars($row['status'] ?? '')."</td>";
                                echo "<td>".htmlspecialchars($row['attendance_type'] ?? '')."</td>";
                                echo "<td>
                                        <button class='btn btn-sm btn-primary' onclick='editAttendance(".$row['id'].")'>
                                            <i class='fas fa-edit'></i>
                                        </button>
                                        <button class='btn btn-sm btn-danger' onclick='deleteAttendance(".$row['id'].")'>
                                            <i class='fas fa-trash'></i>
                                        </button>
                                    </td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Attendance Modal -->
    <div class="modal fade" id="attendanceModal" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Manage Attendance</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="attendanceForm">
                        <input type="hidden" id="aid" name="aid">
                        <div class="form-group">
                            <label>Employee</label>
                            <select class="form-control" id="employee" name="employee" required>
                                <?php
                                $emp_query = "SELECT eid, full_name FROM employees ORDER BY full_name";
                                $emp_result = mysqli_query($con, $emp_query);
                                while($emp = mysqli_fetch_assoc($emp_result)) {
                                    echo "<option value='".$emp['eid']."'>".$emp['full_name']."</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Date</label>
                            <input type="date" class="form-control" id="date" name="date" required>
                        </div>
                        <div class="form-group">
                            <label>First In</label>
                            <input type="time" class="form-control" id="first_in" name="first_in" required>
                        </div>
                        <div class="form-group">
                            <label>Last Out</label>
                            <input type="time" class="form-control" id="last_out" name="last_out">
                        </div>
                        <div class="form-group">
                            <label>Type</label>
                            <select class="form-control" id="type" name="type" required>
                                <option value="Regular">Regular</option>
                                <option value="Field">Field</option>
                                <option value="Remote">Remote</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Attendance Modal -->
    <div class="modal fade" id="bulkAttendanceModal" tabindex="-1" role="dialog" aria-labelledby="bulkAttendanceModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bulkAttendanceModalLabel">Bulk Update Attendance</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="bulkAttendanceForm">
                        <div class="form-group">
                            <label for="bulkStatus">Status</label>
                            <select class="form-control" id="bulkStatus" required>
                                <option value="">Select Status</option>
                                <option value="Present">Present</option>
                                <option value="Absent">Absent</option>
                                <option value="Half Day">Half Day</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="bulkDate">Date</label>
                            <input type="date" class="form-control" id="bulkDate" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveBulkAttendance">Save changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add this before closing body tag -->
    <script>
    $(document).ready(function() {
        // Initialize DataTable
        var table = $('#attendanceTable').DataTable();
        
        // Department filter
        $('#departmentFilter').on('change', function() {
            table.column(1) // Department is the second column (index 1)
                .search(this.value)
                .draw();
        });
        
        // Shift filter
        $('#shiftFilter').on('change', function() {
            table.column(9) // Type column
                .search(this.value)
                .draw();
        });
        
        // Date filter
        $('#dateFilter').on('change', function() {
            table.column(4) // Date column
                .search(this.value)
                .draw();
        });
        
        // Status filter
        $('#statusFilter').on('change', function() {
            table.column(8) // Status column
                .search(this.value)
                .draw();
        });
    });
    </script>
    </body>
</html>