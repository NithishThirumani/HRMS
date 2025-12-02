<?php include('session.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Sync Logs - Admin Panel</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="vendor/daterangepicker/daterangepicker.css" rel="stylesheet">
</head>

<body id="page-top">
    <?php include('sidebar.php'); ?>
    <?php include('header.php'); ?>

    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Biometric Sync Logs</h6>
                <div>
                    <input type="text" class="form-control" id="dateRange" placeholder="Select Date Range">
                </div>
            </div>
            <div class="card-body">
                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Successful Syncs</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="successCount">0</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-check fa-2x text-gray-300"></i>
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
                                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Failed Syncs</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="errorCount">0</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-times fa-2x text-gray-300"></i>
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
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Records Synced</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalRecords">0</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-database fa-2x text-gray-300"></i>
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
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Last Sync</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="lastSync">-</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Logs Table -->
                <div class="table-responsive">
                    <table class="table table-bordered" id="logsTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Device ID</th>
                                <th>Sync Time</th>
                                <th>Records Count</th>
                                <th>Status</th>
                                <th>Error Message</th>
                            </tr>
                        </thead>
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

    <script>
    $(document).ready(function() {
        // Initialize date range picker
        $('#dateRange').daterangepicker({
            startDate: moment().subtract(7, 'days'),
            endDate: moment(),
            ranges: {
               'Today': [moment(), moment()],
               'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
               'Last 7 Days': [moment().subtract(6, 'days'), moment()],
               'Last 30 Days': [moment().subtract(29, 'days'), moment()],
               'This Month': [moment().startOf('month'), moment().endOf('month')],
               'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        });

        // Initialize DataTable
        var table = $('#logsTable').DataTable({
            "processing": true,
            "serverSide": false,
            "order": [[1, "desc"]],
            "ajax": {
                "url": "get_sync_logs.php",
                "type": "POST",
                "data": function(d) {
                    var dates = $('#dateRange').val().split(' - ');
                    d.start_date = dates[0];
                    d.end_date = dates[1];
                }
            },
            "columns": [
                {"data": "device_id"},
                {"data": "sync_time"},
                {"data": "records_count"},
                {
                    "data": "status",
                    "render": function(data) {
                        return data === 'success' ? 
                            '<span class="badge badge-success">Success</span>' : 
                            '<span class="badge badge-danger">Error</span>';
                    }
                },
                {"data": "error_message"}
            ]
        });

        // Refresh table when date range changes
        $('#dateRange').on('apply.daterangepicker', function() {
            table.ajax.reload();
            updateSummary();
        });

        function updateSummary() {
            var dates = $('#dateRange').val().split(' - ');
            $.post('get_sync_summary.php', {
                start_date: dates[0],
                end_date: dates[1]
            }, function(data) {
                $('#successCount').text(data.success_count);
                $('#errorCount').text(data.error_count);
                $('#totalRecords').text(data.total_records);
                $('#lastSync').text(data.last_sync);
            });
        }

        // Initial summary update
        updateSummary();
    });
    </script>
</body>
</html>