<?php include('session.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Same header content as user_panel/attendance.php -->
    <title>Attendance Monitoring - HR Panel</title>
    <!-- ... existing style imports ... -->
</head>
<body id="page-top">
    <?php include('sidebar.php'); ?>
    <?php include('topbar.php'); ?>

    <div class="container-fluid">
        <!-- Department-wise Analytics -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Employees Present Today</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalPresent">Loading...</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Late Arrivals Today</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="lateArrivals">Loading...</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clock fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Field Staff Active</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="fieldStaffActive">Loading...</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-map-marker-alt fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Absent Today</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="absentToday">Loading...</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user-times fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Advanced Filters -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Advanced Filters</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2">
                        <label>Department</label>
                        <select class="form-control" id="departmentFilter">
                            <option value="">All Departments</option>
                            <?php
                            $dept_query = "SELECT * FROM departments";
                            $dept_result = mysqli_query($con, $dept_query);
                            while($dept = mysqli_fetch_assoc($dept_result)) {
                                echo "<option value='".$dept['id']."'>".$dept['name']."</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>Date Range</label>
                        <input type="date" class="form-control" id="startDate" value="<?php echo date('Y-m-01'); ?>">
                    </div>
                    <div class="col-md-2">
                        <label>&nbsp;</label>
                        <input type="date" class="form-control" id="endDate" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="col-md-2">
                        <label>Status</label>
                        <select class="form-control" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="Present">Present</option>
                            <option value="Late">Late</option>
                            <option value="Half Day">Half Day</option>
                            <option value="Absent">Absent</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>Type</label>
                        <select class="form-control" id="typeFilter">
                            <option value="">All Types</option>
                            <option value="regular">Office</option>
                            <option value="field">Field</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>&nbsp;</label>
                        <button class="btn btn-primary btn-block" onclick="refreshMonitoringData()">
                            <i class="fas fa-sync-alt"></i> Update
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Table -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="monitoringTable" width="100%" cellspacing="0">
                        <!-- Similar table structure as user_panel but with additional columns -->
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Include necessary scripts and create monitoring.js -->
</body>
</html>