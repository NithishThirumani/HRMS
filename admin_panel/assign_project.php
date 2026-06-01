<?php  include('session.php');  ?>
<?php 
include('connection.php'); // Include your database connection file

if(isset($_POST['submit'])) {
    // #region agent log
    $debugLog = function ($message, $data, $hypothesisId) {
        $entry = json_encode([
            'sessionId' => 'a77d8c',
            'runId' => 'assign-project',
            'hypothesisId' => $hypothesisId,
            'location' => 'assign_project.php:POST',
            'message' => $message,
            'data' => $data,
            'timestamp' => round(microtime(true) * 1000),
        ]);
        @file_put_contents(__DIR__ . '/../debug-a77d8c.log', $entry . PHP_EOL, FILE_APPEND);
    };
    // #endregion

    // Retrieve and validate data from the form
    $leader_id = isset($_POST['nid']) ? intval($_POST['nid']) : 0;
    $project_name = isset($_POST['nm']) ? trim($_POST['nm']) : '';
    $description = isset($_POST['des']) ? trim($_POST['des']) : '';
    $due_date = isset($_POST['dt']) ? trim($_POST['dt']) : '';

    // #region agent log
    $debugLog('Form submitted', [
        'leader_id' => $leader_id,
        'project_name_len' => strlen($project_name),
        'due_date' => $due_date,
    ], 'D');
    // #endregion

    if ($leader_id <= 0 || $project_name === '') {
        echo "<script>alert('Please provide a valid employee and project name.');</script>";
    } else {
        // Fetch leader_name and leader_email using prepared statement
        $stmt = $con->prepare("SELECT full_name, email FROM employees WHERE id = ?");
        $stmt->bind_param('i', $leader_id);
        $stmt->execute();
        $res = $stmt->get_result();

        if($res && $res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $leader_name = $row['full_name'];
            $leader_email = $row['email'];

            // Insert data into projects table using prepared statement
            $sub_date = ($due_date !== '') ? $due_date : date('Y-m-d');
            $file_name = '';
            $points = 0;
            $status = 'pending';
            $insert_sql = "INSERT INTO projects (p_name, leader_id, leader_name, leader_email, p_description, due_date, sub_date, file_name, points, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $insert_stmt = $con->prepare($insert_sql);

            // #region agent log
            $debugLog('Prepared INSERT', [
                'columns' => ['p_name','leader_id','leader_name','leader_email','p_description','due_date','sub_date','file_name','points','status'],
                'placeholder_count' => 10,
                'file_name' => $file_name,
                'sub_date' => $sub_date,
            ], 'A');
            // #endregion

            if ($insert_stmt) {
                $insert_stmt->bind_param('sissssssis', $project_name, $leader_id, $leader_name, $leader_email, $description, $due_date, $sub_date, $file_name, $points, $status);
                try {
                    $executed = $insert_stmt->execute();
                    // #region agent log
                    $debugLog('INSERT execute result', [
                        'success' => $executed,
                        'insert_id' => $insert_stmt->insert_id,
                        'error' => $insert_stmt->error,
                    ], 'A');
                    // #endregion
                    if($executed) {
                        echo "<script>alert('Project assigned successfully!');</script>";
                        echo "<script>window.location.href='/emps/admin_panel/project_status.php';</script>";
                    } else {
                        error_log('Insert project failed: ' . $insert_stmt->error);
                        echo "<script>alert('Error assigning project.');</script>";
                    }
                } catch (mysqli_sql_exception $ex) {
                    // #region agent log
                    $debugLog('INSERT exception', [
                        'message' => $ex->getMessage(),
                        'code' => $ex->getCode(),
                    ], 'A');
                    // #endregion
                    error_log('Insert project exception: ' . $ex->getMessage());
                    echo "<script>alert('Error assigning project: " . addslashes($ex->getMessage()) . "');</script>";
                }
            } else {
                // #region agent log
                $debugLog('Prepare failed', ['error' => $con->error], 'C');
                // #endregion
                error_log('Prepare insert failed: ' . $con->error);
                echo "<script>alert('Error preparing project assignment.');</script>";
            }
        } else {
            // #region agent log
            $debugLog('Leader not found', ['leader_id' => $leader_id], 'D');
            // #endregion
            echo "<script>alert('Leader with ID $leader_id does not exist');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Assign Project</title>

    <link href="img/favicon.png" rel="icon">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.css" rel="stylesheet">
    <script src="js/jquery-3.6.4.min.js"></script>

    <link href="vendor/mdi-font/css/material-design-iconic-font.min.css" rel="stylesheet" media="all">
    <link href="vendor/font-awesome-4.7/css/font-awesome.min.css" rel="stylesheet" media="all">
    <link href="vendor/select2/select2.min.css" rel="stylesheet" media="all">
    <link href="vendor/datepicker/daterangepicker.css" rel="stylesheet" media="all">

    <link rel="stylesheet" href="css/main.css">

    <script src="js/jquery.min.js"></script>
    <script src="js/jquery.validate.min.js"></script>
    <script src="js/assign_task.js"></script>

</head>

<body id="page-top">

    <?php  include('sidebar.php'); ?>

    <?php  include('header.php'); ?>

    <!-- Begin Page Content -->
    <div class="container-fluid">

        <form id="registrationForm" action="assign_project.php" method="POST">

            <div class="page-wrapper bg-blue p-t-100 p-b-100 font-robo">
                <div class="wrapper wrapper--w680">
                    <div class="card card-1">
                        <div class="card-heading"></div>
                        <div class="card-body">
                            <h2 class="title">Assign Project</h2>

                            <div>
                                <label>Select Employee</label>
                            </div>
                            <div>
                                <select class="input--style-1" name="nid">
                                    <?php
            // Fetch data from the employees table
            $query = "SELECT id, full_name FROM employees";
            $result = mysqli_query($con, $query);

            // Check if query was successful
            if ($result) {
                // Loop through each row and display id and full_name as options in the dropdown
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<option value='" . $row['id'] . "'>" . $row['id'] . " - " . $row['full_name'] . "</option>";
                }
            } else {
                // Display an error message if the query fails
                echo "<option value=''>Error fetching data</option>";
            }
            ?>
                                </select>
                                <span id="nid_err" class="error1 p-1"></span>
                            </div>

                            <div>
                                <label>Project Name</label>
                            </div>
                            <div>
                                <input class="input--style-1" type="text" placeholder="Project Name" name="nm" />
                                <span id="nm_err" class="error1 p-1"></span>
                            </div>

                            <div>
                                <label>Description</label>
                            </div>
                            <div>
                                <textarea class="input--style-1" type="text" placeholder="Description"
                                    name="des"></textarea>
                                <span id="des_err" class="error1 p-1"></span>
                            </div>

                            <div>
                                <label>Due Date</label>
                            </div>
                            <div>
                                <input class="input--style-1" type="date" placeholder="Due date" name="dt" />
                                <span id="dt_err" class="error1 p-1"></span>
                            </div>

                            <div id="a1">
                                <div>
                                    <label>Starting time</label>
                                </div>
                                <div>
                                    <input id="st" class="input--style-1" type="time" placeholder="Starting Time"
                                        name="st" />
                                    <span id="st_err" class="error1 p-1"></span>
                                </div>

                                <div>
                                    <label>End time</label>
                                </div>
                                <div>
                                    <input id="et" class="input--style-1" type="time" placeholder="Ending Time"
                                        name="et" />
                                    <span id="et_err" class="error1 p-1"></span>
                                </div>
                            </div>

                            <div class="p-t-20">
                                <button class="btn btn--radius btn-success" name="submit" type="submit">Submit</button>
                            </div>


                        </div>
                    </div>
                </div>

        </form>

    </div>

    </div>
    <!-- /.container-fluid -->

    </div>
    <!-- End of Main Content -->

    <?php
    include_once('footer.php');
    ?>

    </div>
    <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-success"
                        href="/emps/admin_panel/logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <script src="js/sb-admin-2.min.js"></script>

    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <script src="js/demo/datatables-demo.js"></script>

</body>

</html>