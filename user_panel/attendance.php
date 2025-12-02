<?php include('session.php'); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Attendance Management</title>
    <link href="img/favicon.png" rel="icon">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <!-- Add Geolocation CSS -->
    <link href="https://cdn.jsdelivr.net/npm/leaflet@1.7.1/dist/leaflet.css" rel="stylesheet">
    <style>
        .present {
            color: #2ecc71;
            font-weight: bold;
        }

        .absent {
            color: #e74c3c;
            font-weight: bold;
        }

        .half-day {
            color: #f39c12;
            font-weight: bold;
        }

        .in-progress {
            color: #3498db;
            font-weight: bold;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background: linear-gradient(45deg, #2ecc71, #27ae60);
            color: white;
            border-radius: 10px 10px 0 0 !important;
        }

        .btn-success {
            background: linear-gradient(45deg, #2ecc71, #27ae60);
            border: none;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .btn-success:hover {
            background: linear-gradient(45deg, #27ae60, #219a52);
            transform: translateY(-1px);
        }

        .border-left-success {
            border-left: 4px solid #2ecc71 !important;
        }

        .border-left-warning {
            border-left: 4px solid #f39c12 !important;
        }

        .border-left-info {
            border-left: 4px solid #3498db !important;
        }

        .progress {
            height: 8px;
            border-radius: 4px;
        }

        .progress-bar {
            background: linear-gradient(45deg, #3498db, #2980b9);
        }

        .table thead th {
            background: #f8f9fa;
            border-bottom: 2px solid #2ecc71;
        }

        .table-bordered td {
            border: 1px solid #e9ecef;
        }

        select.form-control,
        input.form-control {
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        select.form-control:focus,
        input.form-control:focus {
            border-color: #2ecc71;
            box-shadow: 0 0 0 0.2rem rgba(46, 204, 113, 0.25);
        }

        .modal-content {
            border-radius: 15px;
            border: none;
        }

        .modal-header {
            background: linear-gradient(45deg, #2ecc71, #27ae60);
            color: white;
            border-radius: 15px 15px 0 0;
        }

        .modal-header .close {
            color: white;
        }

        #map {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body id="page-top">
    <?php include('sidebar.php'); ?>
    <?php include('topbar.php'); ?>

    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-success">Attendance Management</h6>
                <div>
                    <?php
                    // Get employee details and attendance method
                    $emp_query = "SELECT e.eid, e.is_field_staff, e.role 
                                FROM employees e 
                                JOIN emp_login el ON e.eid = el.emp_id 
                                WHERE el.user_name = ?";
                    $stmt = mysqli_prepare($con, $emp_query);
                    mysqli_stmt_bind_param($stmt, "s", $_SESSION['user_name']);
                    mysqli_stmt_execute($stmt);
                    $emp_result = mysqli_stmt_get_result($stmt);
                    $emp_data = mysqli_fetch_assoc($emp_result);

                    // Check if already marked attendance today
                    $check_query = "SELECT * FROM attendance 
                                  WHERE eid = ? AND attendance_date = CURRENT_DATE()";
                    $stmt = mysqli_prepare($con, $check_query);
                    mysqli_stmt_bind_param($stmt, "s", $emp_data['eid']);
                    mysqli_stmt_execute($stmt);
                    $today_attendance = mysqli_stmt_get_result($stmt)->fetch_assoc();

                    // Show import button only for HR/Admin
                    if ($emp_data['role'] == 'hr' || $emp_data['role'] == 'admin'): ?>
                        <button class="btn btn-primary mr-2" data-toggle="modal" data-target="#importModal">
                            <i class="fas fa-file-import"></i> Import Excel
                        </button>
                    <?php endif;

                    // Show appropriate attendance button if not marked today
                    if (!$today_attendance || !$today_attendance['last_out']) {
                        if ($emp_data['is_field_staff'] == 'yes') {
                            echo '<button class="btn btn-info" onclick="markAttendance(\'field\')">
                                <i class="fas fa-map-marker-alt"></i> Field Attendance
                            </button>';
                        } else {
                            echo '<button class="btn btn-success" onclick="markAttendance(\'regular\')">
                                <i class="fas fa-clock"></i> Mark Attendance
                            </button>';
                        }
                    }
                    ?>
                </div>
            </div>
            <div class="card-body">
                <!-- Shift Selection -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <select class="form-control" id="shiftFilter">
                            <option value="">All Shifts</option>
                            <option value="day">Day Shift (9:00 AM - 6:00 PM)</option>
                            <option value="swing">Swing Shift (4:00 PM - 12:00 AM)</option>
                            <option value="night">Night Shift (12:00 AM - 8:00 AM)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="date" class="form-control" id="dateFilter" value="<?php echo date('Y-m-d'); ?>"
                            readonly>
                    </div>
                </div>

                <!-- Attendance Table -->
                <div class="table-responsive">
                    <table class="table table-bordered" id="attendanceTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Department</th>
                                <th>Name</th>
                                <th>Staff Code</th>
                                <th>Date</th>
                                <th>Week</th>
                                <th>First In</th>
                                <th>Last Out</th>
                                <th>Total Hours</th>
                                <th>Status</th>
                                <th>Method</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $user_name = $_SESSION['user_name'];

                            // Different queries based on user role
                            if ($emp_data['role'] == 'hr' || $emp_data['role'] == 'admin') {
                                $query = "SELECT a.*, e.full_name, d.name as department 
                                        FROM attendance a 
                                        JOIN employees e ON a.eid = e.eid 
                                        JOIN departments d ON e.department_id = d.id
                                        WHERE a.attendance_date = CURRENT_DATE()
                                        ORDER BY a.first_in DESC";
                                $stmt = mysqli_prepare($con, $query);
                            } else {
                                $query = "SELECT a.*, e.full_name, d.name as department 
                                        FROM attendance a 
                                        JOIN employees e ON a.eid = e.eid 
                                        JOIN emp_login el ON e.eid = el.emp_id 
                                        JOIN departments d ON e.department_id = d.id
                                        WHERE el.user_name = ?
                                        AND a.attendance_date = CURRENT_DATE()
                                        ORDER BY a.first_in DESC";
                                $stmt = mysqli_prepare($con, $query);
                                mysqli_stmt_bind_param($stmt, "s", $user_name);
                            }

                            mysqli_stmt_execute($stmt);
                            $result = mysqli_stmt_get_result($stmt);

                            if (mysqli_num_rows($result) == 0) {
                                echo "<tr><td colspan='11' class='text-center'>No attendance record found for today</td></tr>";
                            }
                            while ($row = mysqli_fetch_assoc($result)) {
                                if ($row['last_out'] && $row['first_in']) {
                                    $total_hours = round((strtotime($row['last_out']) - strtotime($row['first_in'])) / 3600, 2);

                                    // Determine attendance status based on hours
                                    if ($total_hours >= 8) {
                                        $status = 'Present';
                                    } elseif ($total_hours >= 4) {
                                        $status = 'Half Day';
                                    } else {
                                        $status = 'Absent';
                                    }
                                } else {
                                    $total_hours = 0;
                                    $status = 'In Progress';
                                }

                                echo "<tr>";
                                echo "<td>" . $row['department'] . "</td>";
                                echo "<td>" . $row['full_name'] . "</td>";
                                echo "<td>" . $row['eid'] . "</td>";
                                echo "<td>" . date('Y-m-d', strtotime($row['attendance_date'])) . "</td>";
                                echo "<td>" . date('l', strtotime($row['attendance_date'])) . "</td>";
                                echo "<td>" . $row['first_in'] . "</td>";
                                echo "<td>" . $row['last_out'] . "</td>";
                                echo "<td>" . $total_hours . "</td>";
                                echo "<td class='" . strtolower($status) . "'>" . $status . "</td>";
                                echo "<td>" . $row['attendance_type'] . "</td>";
                                echo "<td>" . ($row['attendance_type'] == 'field' ? 'Field Work' : 'Office') . "</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Field Staff Location Modal -->
        <div class="modal fade" id="locationModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Mark Field Attendance</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div id="map" style="height: 300px;"></div>
                        <form id="fieldAttendanceForm" class="mt-3">
                            <input type="hidden" id="latitude" name="latitude">
                            <input type="hidden" id="longitude" name="longitude">
                            <input type="hidden" id="location_address" name="location_address">
                            <button type="submit" class="btn btn-success">Confirm Location & Mark Attendance</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Import Modal -->
        <div class="modal fade" id="importModal" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Import Attendance Data</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <form id="importForm" enctype="multipart/form-data">
                            <div class="form-group">
                                <label>Upload Excel File</label>
                                <input type="file" class="form-control" name="attendance_file" accept=".xlsx,.xls,.csv" required>
                            </div>
                            <div class="alert alert-info">
                                <small>Excel columns should be: EID, Date, First In, Last Out, Type</small>
                            </div>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-upload"></i> Upload
                            </button>
                            <a href="templates/attendance_template.xlsx" class="btn btn-info">
                                <i class="fas fa-download"></i> Template
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/leaflet@1.7.1/dist/leaflet.js"></script>
    
    <script>
        $(document).ready(function () {
            var table = $('#attendanceTable').DataTable({
                "order": [[3, "desc"]],
                "pageLength": 25,
                dom: '<"row"<"col-md-6"B><"col-md-6"f>>rtip',
                buttons: [
                    {
                        extend: 'collection',
                        text: '<i class="fas fa-download"></i> Export',
                        buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
                        className: 'btn-success'
                    }
                ],
                "language": {
                    "search": "<i class='fas fa-search'></i> Search:",
                    "paginate": {
                        "next": "<i class='fas fa-chevron-right'></i>",
                        "previous": "<i class='fas fa-chevron-left'></i>"
                    }
                }
            });
        });

        function markAttendance(type) {
            // Disable the button to prevent multiple clicks
            $('.btn').prop('disabled', true);

            if (type === 'field') {
                $('#locationModal').modal('show');
                $('.btn').prop('disabled', false);
                if ("geolocation" in navigator) {
                    navigator.geolocation.getCurrentPosition(function (position) {
                        $('#latitude').val(position.coords.latitude);
                        $('#longitude').val(position.coords.longitude);

                        // Initialize map
                        var map = L.map('map').setView([position.coords.latitude, position.coords.longitude], 15);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
                        L.marker([position.coords.latitude, position.coords.longitude]).addTo(map);
                    });
                }
            } else {
                // Regular attendance
                $.ajax({
                    url: 'process_attendance.php',
                    method: 'POST',
                    data: {
                        type: 'regular',
                        attendance_date: new Date().toISOString().split('T')[0]
                    },
                    success: function (response) {
                        $('.btn').prop('disabled', false);
                        handleAttendanceResponse(response);
                    },
                    error: function () {
                        $('.btn').prop('disabled', false);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to connect to server'
                        });
                    }
                });
            }
        }

        // Handle field attendance form submission
        $('#fieldAttendanceForm').on('submit', function (e) {
            e.preventDefault();
            $('.btn').prop('disabled', true);

            $.ajax({
                url: 'process_attendance.php',
                method: 'POST',
                data: {
                    type: 'field',
                    latitude: $('#latitude').val(),
                    longitude: $('#longitude').val(),
                    attendance_date: new Date().toISOString().split('T')[0]
                },
                success: function (response) {
                    $('.btn').prop('disabled', false);
                    $('#locationModal').modal('hide');
                    handleAttendanceResponse(response);
                },
                error: function () {
                    $('.btn').prop('disabled', false);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to connect to server'
                    });
                }
            });
        });
        
        // Handle import form submission
        $('#importForm').on('submit', function (e) {
            e.preventDefault();
            var formData = new FormData(this);

            $.ajax({
                url: 'process_excel_import.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message
                        }).then(() => {
                            $('#importModal').modal('hide');
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message
                        });
                    }
                }
            });
        });
        
        function handleAttendanceResponse(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: response.message
                });
            }
        }
    </script>
</body>
</html>