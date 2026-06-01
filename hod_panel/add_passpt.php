<?php
include('session.php'); 
include('connection.php'); 

// Fetch employee list from employees table
$employees_query = "SELECT id, eid, first_name, last_name, full_name FROM employees";
$employees_result = mysqli_query($con, $employees_query);

$employee_name = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) { 
    $emp_id = $_POST['eid'] ?? ''; // Fetch selected employee ID
    $passport_number = $_POST['passport_number'] ?? '';
    $passport_type = $_POST['passport_type'] ?? '';
    $pissue_date = $_POST['pissue_date'] ?? '';
    $pvalidity = $_POST['pvalidity'] ?? '';
    $country = $_POST['country'] ?? '';
	$pissue_place = $_POST['pissue_place'] ?? '';
	$pissue_city = $_POST['pissue_city'] ?? '';
	$passport_add1 = $_POST['passport_add1'] ?? '';
	$passport_add2 = $_POST['passport_add2'] ?? '';
	$passport_held = $_POST['passport_held'] ?? '';
	$Remarks = $_POST['Remarks'] ?? '';
    
    // Insert into emp_passport including employee emp_id
    $sql_insert_employee = "INSERT INTO emp_passport (emp_id, passport_number, passport_type, pissue_date, pvalidity, country, pissue_place, pissue_city,
	passport_add1, passport_add2, passport_held, Remarks) 
                            VALUES ('$emp_id', '$passport_number', '$passport_type', '$pissue_date', '$pvalidity', '$country', '$pissue_place', '$pissue_city', 
    '$passport_add1', '$passport_add2', '$passport_held', '$Remarks')";

    if (mysqli_query($con, $sql_insert_employee)) {
        echo "Passport details added successfully.";
    } else {
        echo "Error: " . mysqli_error($con);
    }
}

// Check if an employee has been selected and fetch their name
if (isset($_POST['eid'])) {
    $emp_id = $_POST['eid'];
    $employee_query = "SELECT first_name, last_name FROM employees WHERE eid = '$emp_id'";
    $employee_result = mysqli_query($con, $employee_query);
    if ($employee_result && mysqli_num_rows($employee_result) > 0) {
        $employee_data = mysqli_fetch_assoc($employee_result);
        $employee_name = $employee_data['first_name'] . ' ' . $employee_data['last_name'];
    }
}

$countries = array(
    "Afghanistan", "Albania", "Algeria", "Andorra", "Angola", "Antigua and Barbuda", "Argentina", "Armenia", "Australia", "Austria",
    "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bhutan",
    "Bolivia", "Bosnia and Herzegovina", "Botswana", "Brazil", "Brunei", "Bulgaria", "Burkina Faso", "Burundi", "Cabo Verde", "Cambodia",
    "Cameroon", "Canada", "Central African Republic", "Chad", "Chile", "China", "Colombia", "Comoros", "Congo (Congo-Brazzaville)", "Costa Rica",
    "Croatia", "Cuba", "Cyprus", "Czechia", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "Ecuador", "Egypt",
    "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Eswatini", "Ethiopia", "Fiji", "Finland", "France", "Gabon",
    "Gambia", "Georgia", "Germany", "Ghana", "Greece", "Grenada", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana",
    "Haiti", "Honduras", "Hungary", "Iceland", "India", "Indonesia", "Iran", "Iraq", "Ireland", "Israel",
    "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Kuwait", "Kyrgyzstan", "Laos",
    "Latvia", "Lebanon", "Lesotho", "Liberia", "Libya", "Liechtenstein", "Lithuania", "Luxembourg", "Madagascar", "Malawi",
    "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Mauritania", "Mauritius", "Mexico", "Micronesia", "Moldova",
    "Monaco", "Mongolia", "Montenegro", "Morocco", "Mozambique", "Myanmar (Burma)", "Namibia", "Nauru", "Nepal", "Netherlands",
    "New Zealand", "Nicaragua", "Niger", "Nigeria", "North Korea", "North Macedonia", "Norway", "Oman", "Pakistan", "Palau",
    "Palestine State", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Poland", "Portugal", "Qatar", "Romania",
    "Russia", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent and the Grenadines", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal",
    "Serbia", "Seychelles", "Sierra Leone", "Singapore", "Slovakia", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Korea",
    "South Sudan", "Spain", "Sri Lanka", "Sudan", "Suriname", "Sweden", "Switzerland", "Syria", "Taiwan", "Tajikistan",
    "Tanzania", "Thailand", "Timor-Leste", "Togo", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Tuvalu",
    "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "Uruguay", "Uzbekistan", "Vanuatu", "Vatican City", "Venezuela",
    "Vietnam", "Yemen", "Zambia", "Zimbabwe"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Passport Details</title>
    <link href="img/favicon.png" rel="icon">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.css" rel="stylesheet">
    <link href="vendor/mdi-font/css/material-design-iconic-font.min.css" rel="stylesheet" media="all">
    <link href="vendor/font-awesome-4.7/css/font-awesome.min.css" rel="stylesheet" media="all">
    <link href="vendor/select2/select2.min.css" rel="stylesheet" media="all">
    <link href="vendor/datepicker/daterangepicker.css" rel="stylesheet" media="all">
    <link rel="stylesheet" href="css/main.css">
    <script src="js/jquery.min.js"></script>
    <script src="js/jquery.validate.min.js"></script>
    <script src="js/reg_emp.js"></script>
</head>
<body id="page-top">
    <?php include('sidebar.php'); ?>
    <?php include('header.php'); ?>
    <div class="container-fluid">
        <form id="registrationForm" action="add_passpt.php" method="POST">
            <div class="page-wrapper bg-blue p-t-100 p-b-100 font-robo">
                <div class="wrapper wrapper--w680">
                    <div class="card card-1">
                        <div class="card-heading"></div>
<style>
    .error-message {
        color: red;
        font-size: 12px;
        margin-top: 5px;
        display: block;
    }
</style>

						
<script>
document.getElementById("registrationForm").addEventListener("submit", function(event) {
    let isValid = true;
    let inputs = document.querySelectorAll("input[required], select[required]");
    
    inputs.forEach(input => {
        let errorSpan = input.nextElementSibling;
        
        // Create an error span if not already present
        if (!errorSpan || !errorSpan.classList.contains("error-message")) {
            errorSpan = document.createElement("span");
            errorSpan.classList.add("error-message");
            errorSpan.style.color = "red"; // Apply red color to error message
            errorSpan.style.fontSize = "12px"; // Make it slightly smaller
            errorSpan.style.display = "block"; // Ensure it appears properly
            errorSpan.style.marginTop = "5px"; // Add some spacing
            input.parentNode.appendChild(errorSpan);
        }

        if (!input.value.trim()) {
            errorSpan.innerText = "Required Field";
            isValid = false;
        } else {
            errorSpan.innerText = ""; // Clear error message
        }
    });

    if (!isValid) {
        event.preventDefault(); // Stop form submission if invalid
    }
});
</script>
						
						
                        <div class="card-body">
                            <h2 class="title">Employee Passport Details</h2>
                            <div class="col-6">
                                <p>Select Employee</p>
                                <div class="input-group1">
                                    <select class="input--style-1" name="eid" onchange="this.form.submit()">
                                        <option value="">Select Employee</option>
                                        <?php while ($row = mysqli_fetch_assoc($employees_result)) { ?>
                                            <option value="<?php echo $row['eid']; ?>"><?php echo $row['eid']; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>  


                            <!-- Display Employee Name (Read Only) -->
                            <div>
                                <p>Employee Name</p>
                                <div class="input-group1">
                                    <input class="input--style-1" type="text" value="<?php echo $employee_name; ?>" readonly />
                                </div>
                            </div>  

                            <div class="input-group1">
                            <label>Passport Number</label>
                            <input class="input--style-1"type="text" name="passport_number" required />
							<span class="error-message"></span> <!-- Error message will appear here -->
                        </div>

                        <div class="input-group1">
                            <label>Passport Type</label>
                            <select class="input--style-1" name="passport_type">
                                <option value="">Select Passport Type</option>
                                <option value="Regular">Regular</option>
                                <option value="Diplomatic">Diplomatic</option>
                                <option value="Official">Official</option>
                            </select>
                        </div>
                        
                        <div class="input-group1">
                            <label>Issue Date</label>
                            <input class="input--style-1" type="date" name="pissue_date" required />
                        </div>

                        <div class="input-group1">
                            <label>Validity</label>
                            <input class="input--style-1" type="date" name="pvalidity" required />
                        </div>

                            <!-- Country -->
                            <div class="col-6">
                                <p>Country</p>
                                <div class="input-group1">
                                    <select class="input--style-1" name="country">
                                        <option value="">Select Country</option>
                                        <?php foreach ($countries as $country) { ?>
                                            <option value="<?php echo $country; ?>"><?php echo $country; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div> 
							
							 <div class="input-group1">
                            <label>Issue Place</label>
                            <input class="input--style-1" type="text" name="pissue_place" required />
                        </div>

                        <div class="input-group1">
                            <label>Issue City</label>
                            <input class="input--style-1" type="text" name="pissue_city" required />
                        </div>

                        <div class="input-group1">
                            <label>Address Line 1</label>
                            <input class="input--style-1" type="text" name="passport_add1" required />
                        </div>

                        <div class="input-group1">
                            <label>Address Line 2</label>
                            <input class="input--style-1" type="text" name="passport_add2" />
                        </div>

                        <div class="input-group1">
                            <label>Passport Held</label>
                            <input class="input--style-1" type="text" name="passport_held"  />
                        </div>

                        <div class="input-group1">
                            <label>Remarks</label>
                            <textarea class="input--style-1" name="remarks"></textarea>
                        </div>
							
							
							
							

                            <div class="p-t-20 p-2">
                                <button class="btn btn--radius btn-success" name="register" type="submit">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
        </form>
    </div>

    <?php include_once('footer.php'); ?>

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

    <script src="js/show_password1.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script> 
    <script src="js/sb-admin-2.min.js"></script>
</body>
</html>
