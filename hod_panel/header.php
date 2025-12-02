<?php
$basePath = (strpos($_SERVER['REQUEST_URI'], 'documents/') !== false) ? '../' : '';
?>
<link href="<?php echo $basePath; ?>css/custom.css" rel="stylesheet">

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

                <!-- Nav Item - User Information -->
                <li class="nav-item dropdown no-arrow">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                        <?php
                        $un = $_SESSION['username'];
                        $q = "SELECT e.Full_name, e.role, e.profile_pic, el.* FROM emp_login el 
                             INNER JOIN employees e ON el.emp_id = e.id 
                             WHERE el.user_name='$un' AND UPPER(e.role)='HOD'";
                        $res = mysqli_query($con, $q);
                        
                        echo "<!-- Debug: Username = " . $un . " -->";
                        
                        if ($res && mysqli_num_rows($res) > 0) {
                            while ($row = mysqli_fetch_array($res)) { 
                                echo "<!-- Debug: Name = " . $row['Full_name'] . " -->";
                                echo "<!-- Debug: Profile Pic = " . $row['profile_pic'] . " -->";
                                ?>
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small" style="display: inline-block !important;"><b><?php echo $row['Full_name']; ?></b></span>
                                <img class="img-profile rounded-circle" style="width: 40px; height: 40px; object-fit: cover; border: 2px solid #4e73df; display: inline-block !important; visibility: visible;"
                                    src="<?php echo $basePath; ?>admin_panel/uploads/profile_pics/<?php echo $row['profile_pic']; ?>"
                                    onerror="this.src='<?php echo $basePath; ?>img/undraw_profile.svg'"
                                    alt="Profile Picture"><?php 
                            }
                        } else {
                            echo "<!-- Debug: No results found or query failed -->";
                            if (!$res) {
                                echo "<!-- Debug: SQL Error = " . mysqli_error($con) . " -->";
                            }
                        }
                        ?>
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
        <script src="<?php echo $basePath; ?>js/datetime.js"></script>