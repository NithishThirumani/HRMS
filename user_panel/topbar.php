<link href="/img/favicon.png" rel="icon">
<link href="css/datetime.css" rel="stylesheet">.


<!-- Content Wrapper -->
<div id="content-wrapper" class="d-flex flex-column">

    <!-- Main Content -->
    <div id="content">

        <!-- Topbar -->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

            <!-- Sidebar Toggle (Topbar) -->
            <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                <i class="fa fa-bars"></i>
            </button>
            <!-- Date Time Display -->
            <div class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0">
                <div class="datetime-container">
                    <span id="gregorianDateTime" class="datetime-text gregorian"></span> |
                    <span id="hijriDateTime" class="datetime-text hijri"></span>
                </div>
            </div>

            <form id="searchForm"
                class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                <div class="input-group">
                    <input type="text" id="searchInput" class="form-control bg-light border-0 small"
                        placeholder="Search for..." aria-label="Search" aria-describedby="basic-addon2"
                        onkeypress="handleKeyPress(event)">

                    <div class="input-group-append">
                        <button class="btn btn-success" type="button" onclick="performSearch()">
                            <i class="fas fa-search fa-sm"></i>
                        </button>
                    </div>
                </div>
            </form>

            <!-- Topbar Navbar -->
            <ul class="navbar-nav ml-auto">

                <!-- Nav Item - Search Dropdown (Visible Only XS) -->
                <li class="nav-item dropdown no-arrow d-sm-none">
                    <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-search fa-fw"></i>
                    </a>
                    <!-- Dropdown - Messages -->
                    <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in"
                        aria-labelledby="searchDropdown">
                        <form class="form-inline mr-auto w-100 navbar-search">
                            <div class="input-group">
                                <input type="text" class="form-control bg-light border-0 small"
                                    placeholder="Search for..." aria-label="Search" aria-describedby="basic-addon2">
                                <div class="input-group-append">
                                    <button class="btn btn-success" type="button">
                                        <i class="fas fa-search fa-sm"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </li>


                <div class="topbar-divider d-none d-sm-block"></div>
                <!-- Notifications Dropdown -->
                <li class="nav-item dropdown no-arrow mx-1">
                    <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-bell fa-fw"></i>
                        <?php
                        // Check for new appraisals
                        $emp_id = $_SESSION['eid'];
                        $notify_query = "SELECT COUNT(*) as count 
                        FROM appraisal_assignments aa 
                        JOIN appraisal_periods ap ON aa.period_id = ap.period_id 
                        WHERE aa.employee_id = ? 
                        AND aa.status = 'Pending'
                        AND ap.start_date <= CURDATE()";
                        $stmt = $con->prepare($notify_query);
                        $stmt->bind_param("s", $_SESSION['eid']);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $count = $result->fetch_assoc()['count'];

                        if ($count > 0): ?>
                            <span class="badge badge-danger badge-counter"><?php echo $count; ?>+</span>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in">
                        <h6 class="dropdown-header bg-primary">
                            Appraisal Alerts
                        </h6>
                        <?php
                        $appraisal_query = "SELECT aa.id, ap.start_date, ap.end_date 
                           FROM appraisal_assignments aa 
                           JOIN appraisal_periods ap ON aa.period_id = ap.period_id 
                           WHERE aa.employee_id = ? 
                           AND aa.status = 'Pending'
                           AND ap.start_date <= CURDATE()
                           ORDER BY ap.start_date DESC
                           LIMIT 5";
                        $stmt = $con->prepare($appraisal_query);
                        $stmt->bind_param("s", $_SESSION['eid']); // Change from user_id to eid
                        $stmt->execute();
                        $appraisals = $stmt->get_result();

                        if ($appraisals->num_rows > 0):
                            while ($row = $appraisals->fetch_assoc()): ?>
                                <a class="dropdown-item d-flex align-items-center"
                                    href="appraisal/self_assessment.php?assignment_id=<?php echo $row['id']; ?>">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-primary">
                                            <i class="fas fa-file-alt text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500">Period:
                                            <?php echo date('d M', strtotime($row['start_date'])); ?> -
                                            <?php echo date('d M Y', strtotime($row['end_date'])); ?>
                                        </div>
                                        <span class="font-weight-bold">Complete Self Assessment</span>
                                    </div>
                                </a>
                            <?php endwhile;
                        else: ?>
                            <div class="dropdown-item text-center small text-gray-500">No pending appraisals</div>
                        <?php endif; ?>

                        <a class="dropdown-item text-center small text-gray-500" href="index.php#appraisals"
                            onclick="window.location.reload();">Show All
                            Appraisals</a>
                    </div>
                </li>





                <!-- Nav Item - User Information -->
                <li class="nav-item dropdown no-arrow">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                        <?php
                        if (isset($_SESSION['username']) && isset($con)) {
                            $un = $_SESSION['username'];
                            $q = "select * from employees where user_name='$un'";
                            $res = mysqli_query($con, $q);
                            if ($res && mysqli_num_rows($res) > 0) {
                                while ($row = mysqli_fetch_array($res)) { ?>
                                    <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                                        <b><?php echo $row['first_name']; ?></b>
                                    </span>
                                    <?php $firstLetter = strtoupper(substr($row['first_name'], 0, 1)); ?>
                                    <div class="img-profile rounded-circle d-flex align-items-center justify-content-center"
                                        style="width: 40px; height: 40px; background-color: #4e73df; color: white; font-weight: bold; font-size: 16px; border: 2px solid #4e73df;">
                                        <?php echo $firstLetter; ?>
                                    </div>
                                <?php }
                            } else {
                                echo '<span class="mr-2 d-none d-lg-inline text-gray-600 small"><b>User</b></span>';
                            }
                        } ?>
                    </a>
                    <!-- Dropdown - User Information -->
                    <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                        aria-labelledby="userDropdown">

                        <a class="dropdown-item" href="Manage_profile.php">
                            <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                            Profile
                        </a>

                        <a class="dropdown-item" href="change_password.php">
                            <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                            Change Password
                        </a>
                        <!--
                    <a class="dropdown-item" href="#">
                        <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                        Activity Log
                    </a> -->
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                            <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                            Logout
                        </a>
                    </div>
                </li>
            </ul>

        </nav>
        <!-- End of Topbar -->
        <script src="vendor/jquery/jquery.min.js"></script>
        <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="js/datetime.js"></script>
        <script>
            // Initialize Bootstrap dropdowns
            $(document).ready(function () {
                $('.dropdown-toggle').dropdown();
            });
        </script>