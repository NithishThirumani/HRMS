<?php include('session.php'); ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>View Payslips</title>
    <link href="img/favicon.png" rel="icon">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.css" rel="stylesheet">
</head>

<body id="page-top">
    <?php include('sidebar.php'); ?>
    <?php include('topbar.php'); ?>

    <div class="container-fluid"><br>
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-success">My Payslips</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th style="background-color: #2c3e50; color: white;">Slip No</th>
                                <th style="background-color: #2c3e50; color: white;">Month</th>
                                <th style="background-color: #2c3e50; color: white;">Base Salary</th>
                                <th style="background-color: #2c3e50; color: white;">Total Salary</th>
                                <th style="background-color: #2c3e50; color: white;">Present Days</th>
                                <th style="background-color: #2c3e50; color: white;">Leave Days</th>
                                <th style="background-color: #2c3e50; color: white;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $emp_id = $_SESSION['eid']; // Employee ID from session
                            error_log('Session eid: ' . $_SESSION['eid']);
                            // Query salary records for this employee
                             $emp_id1 = $emp_id;
                            $emp_id2 = $emp_id;
                            $query = "SELECT sal.*, salary_slips.slip_no
                                      FROM sal
                                      INNER JOIN salary_slips ON salary_slips.salary_id = sal.id
                                      INNER JOIN (
                                          SELECT MAX(salary_slips.slip_no) as max_slip_no, YEAR(sal.salary_date) as y, MONTH(sal.salary_date) as m
                                          FROM sal
                                          INNER JOIN salary_slips ON salary_slips.salary_id = sal.id
                                          WHERE sal.emp_id = ?
                                          GROUP BY y, m
                                      ) latest ON salary_slips.slip_no = latest.max_slip_no
                                              AND YEAR(sal.salary_date) = latest.y
                                              AND MONTH(sal.salary_date) = latest.m
                                      WHERE sal.emp_id = ?
                                      ORDER BY sal.salary_date DESC";
                            $stmt = $con->prepare($query);
                            $stmt->bind_param("ss", $emp_id1, $emp_id2);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>";
                                    echo "<td>HRMS-" . str_pad($row['slip_no'] ?? '', 6, "0", STR_PAD_LEFT) . "</td>";
                                    echo "<td>" . date('F', strtotime($row['salary_date'])) . "</td>";
                                    echo "<td>AED " . number_format($row['base_salary'], 2) . "</td>";
                                    echo "<td>AED " . number_format($row['total_salary'], 2) . "</td>";
                                    echo "<td>" . $row['present_days'] . "</td>";
                                    echo "<td>" . $row['leaves'] . "</td>";
                                    echo "<td>
                                            <a href='../admin_panel/generate_payslip.php?id=" . $row['id'] . "' 
                                               class='btn btn-primary btn-sm' target='_blank'>
                                                <i class='fas fa-download'></i> Download
                                            </a>
                                          </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7'>No payslip records found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include_once('footer.php'); ?>


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
                    <a class="btn btn-success" href="/emps/user_panel/logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>

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