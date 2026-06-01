<?php
include('session.php'); 
include('connection.php'); // Ensure this file correctly initializes $conn


// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $visa_number = mysqli_real_escape_string($con, $_POST['visa_number']);
    $visa_type = mysqli_real_escape_string($con, $_POST['visa_type']);
    $issue_date = mysqli_real_escape_string($con, $_POST['issue_date']);
    $validity = mysqli_real_escape_string($con, $_POST['validity']);
    $country = mysqli_real_escape_string($con, $_POST['country']);

    // Insert visa details into database
    $sql_insert = "INSERT INTO emps_visa ( visa_number, visa_type, issue_date, validity, country) 
                   VALUES ('$visa_number', '$visa_type', '$issue_date', '$validity', '$country')";

    if (mysqli_query($con, $sql_insert)) {
        echo "<p style='color:green;'>Visa details added successfully.</p>";
    } else {
        echo "<p style='color:red;'>Error: " . mysqli_error($con) . "</p>";
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Visa Details</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        form { max-width: 400px; margin: auto; padding: 20px; border: 1px solid #ccc; }
        input, select { width: 100%; padding: 8px; margin: 5px 0; }
        button { background: blue; color: white; padding: 10px; border: none; }
    </style>
</head>
<body>

<h2>Add Visa Details</h2>
<form method="POST" action="">
    <label for="visa_number">Visa Number:</label>
    <input type="text" id="visa_number" name="visa_number" required>

    <label for="visa_type">Type of Visa:</label>
    <select name="visa_type" id="visa_type" required>
        <option value="Emp Visa Change Status">Emp Visa Change Status</option>
        <option value="Employment Visa">Employment Visa</option>
        <option value="Entry Permit Visa">Entry Permit Visa</option>
        <option value="Family Sponsor">Family Sponsor</option>
    </select>

    <label for="issue_date">Issue Date:</label>
    <input type="date" id="issue_date" name="issue_date" required>

    <label for="validity">Validity (End Date):</label>
    <input type="date" id="validity" name="validity" required>

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

    <button type="submit">Add Visa</button>
</form>

</body>
</html>




                        
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