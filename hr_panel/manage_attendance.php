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
                                <th>
                                    Actions
                                    <button class="btn btn-sm btn-success float-right" onclick="openAddAttendanceModal()">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT a.id as aid, a.*, e.full_name, e.department 
                                    FROM attendance a 
                                    JOIN employees e ON a.eid = e.eid 
                                    WHERE a.attendance_date = CURRENT_DATE()
                                    ORDER BY e.department, e.full_name";
                            
                            $result = mysqli_query($con, $query);
                            while($row = mysqli_fetch_assoc($result)) {
                                $total_hours = 0;
                                if ($row['last_out'] && $row['first_in']) {
                                    $total_hours = round((strtotime($row['last_out']) - strtotime($row['first_in']))/3600, 2);
                                    if ($total_hours >= 8) {
                                        $status = 'Present';
                                    } elseif ($total_hours >= 4) {
                                        $status = 'Half Day';
                                    } else {
                                        $status = 'Absent';
                                    }
                                } else {
                                    $status = 'In Progress';
                                }
                                
                                echo "<tr>";
                                echo "<td><input type='checkbox' class='attendance-checkbox' value='".$row['aid']."'></td>";
                                echo "<td>".$row['department']."</td>";
                                echo "<td>".$row['full_name']."</td>";
                                echo "<td>".$row['eid']."</td>";
                                echo "<td>".date('Y-m-d', strtotime($row['attendance_date']))."</td>";
                                echo "<td>".$row['first_in']."</td>";
                                echo "<td>".$row['last_out']."</td>";
                                echo "<td>".$total_hours."</td>";
                                echo "<td class='".strtolower($status)."'>".$status."</td>";
                                echo "<td>".$row['attendance_type']."</td>";
                                echo "<td>
                                        <button class='btn btn-sm btn-primary edit-btn' data-id='".$row['aid']."'>
                                            <i class='fas fa-edit'></i>
                                        </button>
                                        <button class='btn btn-sm btn-danger delete-btn' data-id='".$row['aid']."'>
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

    <!-- Bulk Update Modal -->
        <div class="modal fade" id="bulkAttendanceModal" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Bulk Update Attendance</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <form id="bulkAttendanceForm">
                            <div class="form-group">
                                <label>Status</label>
                                <select class="form-control" id="bulkStatus" name="status" required>
                                    <option value="Present">Present</option>
                                    <option value="Absent">Absent</option>
                                    <option value="Half Day">Half Day</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Type</label>
                                <select class="form-control" id="bulkType" name="type" required>
                                    <option value="Regular">Regular</option>
                                    <option value="Field">Field</option>
                                    <option value="Remote">Remote</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Update Selected Records</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('footer.php'); ?>

    <!-- Scripts -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <script>
    // Keep this global function
    function openAddAttendanceModal() {
        $('#attendanceForm')[0].reset();
        $('#aid').val('');
        $('#date').val(new Date().toISOString().split('T')[0]);
        $('#attendanceModal').modal('show');
    }

    $(document).ready(function() {
        var table = $('#attendanceTable').DataTable({
            "order": [[4, "desc"], [2, "asc"]],
            "pageLength": 25
        });

        // Filter handlers
        $('#departmentFilter, #shiftFilter, #dateFilter, #statusFilter').on('change', function() {
            table.draw();
        });

        // Custom filtering function
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            var department = $('#departmentFilter').val();
            var shift = $('#shiftFilter').val();
            var date = $('#dateFilter').val();
            var status = $('#statusFilter').val();

            if (
                (department === "" || data[1] === department) &&
                (shift === "" || data[9].includes(shift)) &&
                (date === "" || data[4] === date) &&
                (status === "" || data[8] === status)
            ) {
                return true;
            }
            return false;
        });

        // Handle attendance form submission
        $('#attendanceForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: 'update_attendance.php',
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        $('#attendanceModal').modal('hide');
                        location.reload();
                    } else {
                        alert('Failed to update attendance. Please try again.');
                    }
                },
                error: function() {
                    alert('An error occurred while saving the attendance.');
                }
            });
        });
        function openAddAttendanceModal() {
                    // Reset form
                    $('#attendanceForm')[0].reset();
                    $('#aid').val(''); // Clear ID for new entry
                    $('#date').val(new Date().toISOString().split('T')[0]); // Set current date
                    $('#attendanceModal').modal('show');
                }
        // Update edit button handler
        $('.edit-btn').on('click', function() {
            var aid = $(this).data('id');
            $.ajax({
                url: 'get_attendance.php',
                method: 'GET',
                data: { aid: aid },
                dataType: 'json',
                success: function(data) {
                    $('#aid').val(data.id);
                    $('#employee').val(data.eid);
                    $('#date').val(data.attendance_date);
                    $('#first_in').val(data.first_in ? data.first_in.substr(0, 5) : '');
                    $('#last_out').val(data.last_out ? data.last_out.substr(0, 5) : '');
                    $('#type').val(data.attendance_type);
                    $('#attendanceModal').modal('show');
                },
                error: function() {
                    alert('Failed to fetch attendance details.');
                }
            });
        });
        // Delete button handler
        $('.delete-btn').on('click', function() {
            if(confirm('Are you sure you want to delete this attendance record?')) {
                var aid = $(this).data('id');
                $.post('delete_attendance.php', {aid: aid}, function(response) {
                    if(response.success) {
                        location.reload();
                    } else {
                        alert('Failed to delete attendance record.');
                    }
                });
            }
        });
    });
    </script>
</body>
</html>