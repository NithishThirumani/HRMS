<?php
include('session.php'); 
include('connection.php'); 



if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) { 
    $visa_number = $_POST['visa_number'] ?? '';
    $visa_type = $_POST['visa_type'] ?? '';
    $issue_date = $_POST['issue_date'] ?? '';
    $validity = $_POST['validity'] ?? '';
    $country = $_POST['country'] ?? '';
    
    // Insert into emps_visa
    $sql_insert_employee = "INSERT INTO emps_visa (visa_number, visa_type, issue_date, validity, country) 
                            VALUES ('$visa_number', '$visa_type', '$issue_date', '$validity', '$country')";

    if (mysqli_query($con, $sql_insert_employee)) {
        echo "Visa details added successfully.";
    } else {
        echo "Error: " . mysqli_error($con);
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

    <title>Visa Details</title>

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
    <?php  include('sidebar.php'); ?>

    <?php  include('header.php'); ?>

    <div class="container-fluid">
        
        <form id="registrationForm" action="add_visa.php" method="POST">

            <div class="page-wrapper bg-blue p-t-100 p-b-100 font-robo">
                <div class="wrapper wrapper--w680">
                    <div class="card card-1">
                        <div class="card-heading"></div>
                        <div class="card-body">
                            <h2 class="title">Employee Visa Details</h2>

                                                      
                            <div>
                                <p>Visa Number</p>
                                <div class="input-group1">
                                    <input class="input--style-1" type="text" placeholder="Visa Number"
                                        name="visa_number" />
                                   
                                </div>
                            </div>

							<div class="field-column">
                                <div>
                                    <label for="Visa Type">
                                        Type of Visa
                                    </label>
                                </div>
                                <div>
                                    <select name="visa_type" class="input--style-1" id="visa_type"
                                        class="demo-input-box">
                                        <option value="Emp Visa Change Status">Emp Change</option>
                                        <option value="Employement Visa">Employement Visa</option>
                                        <option value="Entry Permit Visa">Entry Permit Visa</option>
                                        <option value="Family Sponsor">Family Sponsor</option>
                                        
                                    </select>
                                  
                                </div>
                            </div>
                         
                           
                           
                            
  <!-- Date of issue (DOI) -->
<div class="col-6">
    <p>Visa Issue Date</p>
    <div class="input-group1">
        <input class="input--style-1" type="date" name="issue_date" />
        
    </div>
</div>

<!-- Visa Validity -->

<div class="col-6">
    <p>Visa validity</p>
    <div class="input-group1">
        <input class="input--style-1" type="date" name="validity" />
        
    </div>
</div>

<!-- Reporting Manager -->
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
                            <div class="p-t-20 p-2">
                                <button class="btn btn--radius btn-success" name="register" type="submit">Submit</button>
                            </div>

                        </div>
                    </div>
                </div>
        </form>

    </div>


    <?php
          include_once('footer.php');
          ?>

    </div>

    </div>

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

    <!-- <script src="vendor/jquery/jquery.min.js"></script> -->
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <script src="vendor/jquery-easing/jquery.easing.min.js"></script> 

    <script src="js/sb-admin-2.min.js"></script>

    <!-- <script src="vendor/datatables/jquery.dataTables.min.js"></script> -->
    <!-- <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script> -->
    <!-- <script src="js/demo/datatables-demo.js"></script> -->


</body>

</html>