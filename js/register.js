
//registration 
$(document).ready(function () {
    $.validator.addMethod("fnregex", function (value, element) {
        var regex = /^[a-zA-Z]+$/;
        return regex.test(value);
    }, "First name must contain only letters");

    $.validator.addMethod("lnregex", function (value, element) {
        var regex = /^[a-zA-Z]+$/;
        return regex.test(value);
    }, "Last name must contain only letters");

    $.validator.addMethod("emregex", function (value1, element1) {
        var regex1 = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
        return regex1.test(value1);
    }, "Enter a valid email");

    $.validator.addMethod("phoneregex", function (value, element) {
        var regex = /^[+\d]+$/;
        return regex.test(value);
    }, "Phone number must contain only digits");

    $.validator.addMethod("birthdayregex", function (value, element) {
        var selectedDate = new Date(value);
        var currentDate = new Date();
        return selectedDate <= currentDate;
    }, "Please select a valid birthday");

    $.validator.addMethod("psregex", function (value2, element2) {
        var psgex1 = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
        return psgex1.test(value2);
    }, "Password must contain capital letters, small letters, numbers, and special characters.");

    $('#form3').validate({
        rules: {
            fn: {
                required: true,
                minlength: 2,
                maxlength: 30,
                fnregex: true
            },
            ln: {
                required: true,
                minlength: 2,
                maxlength: 30,
                lnregex: true
            },
            em: {
                required: true,
                emregex: true
            },
            pn: {
                required: true,
                phoneregex: true,
                minlength: 10,
                maxlength:10
            },
            birthday: {
                required: true,
                birthdayregex: true
            },
            gender: {
                required: true,
            },
            address: {
                required: true,
            },
            department: {
                required: true,
            },
            degree: {
                required: true,
            },
            ps: {
                required: true,
                minlength: 5,
                maxlength: 10,
                psregex: true
            },
            cp: {
                required: true,
                equalTo: '#ps1'
            }
        },
        messages: {
            fn: {
                required: "First Name is a required field",
                minlength: "First Name must have at least two characters",
                maxlength: "First Name can have a maximum of 30 characters"
            },
            ln: {
                required: "Last Name is a required field",
                minlength: "Last Name must have at least two characters",
                maxlength: "Last Name can have a maximum of 30 characters"
            },
            
            em: {
                required: "Email is a required field",
            },

            pn: {
                required: "Phone number is a required field",
                minlength: "Phone number must have 10 digits"
            },
            birthday: {
                required: "Birthday is a required field",
                birthdayregex: "Ensure selected date is not after the current date"
            },
            gender: {
                required: "Gender is a required field"
            },
            address: {
                required: "Address is a required field"
            },
            department: {
                required: "Department is a required field"
            },
            degree: {
                required: "Degree is a required field"
            },
            ps: {
                required: "Password is a required field",
                minlength: "Password must have at least five characters",
                maxlength: "Password can have a maximum of ten characters"
            },
            cp: {
                required: "Confirm Password is a required field",
                equalTo: "Password doesn't match"
            }
        },
        errorPlacement: function (error, element) {
            if (element.attr('name') == "fn") {
                $('#fn_err').html(error);
            };
            if (element.attr('name') == "ln") {
                $('#ln_err').html(error);
            };
            if (element.attr('name') == "em") {
                $('#em1_err').html(error);
            }
            if (element.attr('name') == "pn") {
                $('#pn_err').html(error);
            }
            if (element.attr('name') == "birthday") {
                $('#birthday_err').html(error);
            }
            if (element.attr('name') == "gender") {
                $('#gender_err').html(error);
            }
            if (element.attr('name') == "address") {
                $('#address_err').html(error);
            }
            if (element.attr('name') == "department") {
                $('#department_err').html(error);
            }
            if (element.attr('name') == "degree") {
                $('#degree_err').html(error);
            }
            if (element.attr('name') == "ps") {
                $('#ps_err').html(error);
            }
            if (element.attr('name') == "cp") {
                $('#cp_err').html(error);
            }
        },
    });
});

function myFunction() {
    var x = document.getElementById("ps1");
    if (x.type === "password") {
        x.type = "text";
    } else {
        x.type = "password";
    }

    var y = document.getElementById("cp1");
    if (y.type === "password") {
        y.type = "text";
    } else {
        y.type = "password";
    }
}

document.getElementById('form3').addEventListener('submit', function(e) {
    let isValid = true;
    const errors = {
        fn: '',
        ln: '',
        em: '',
        pn: '',
        ps: '',
        cp: ''
    };

    // First Name validation
    const fn = document.getElementById('fn1').value.trim();
    if (fn.length < 2) {
        errors.fn = 'First name must be at least 2 characters';
        isValid = false;
    }

    // Last Name validation
    const ln = document.getElementById('ln1').value.trim();
    if (ln.length < 2) {
        errors.ln = 'Last name must be at least 2 characters';
        isValid = false;
    }

    // Email validation
    const em = document.getElementById('em3').value.trim();
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(em)) {
        errors.em = 'Please enter a valid email address';
        isValid = false;
    }

    // Phone validation
    const pn = document.getElementById('pn1').value.trim();
    const phonePattern = /^\d{10}$/;
    if (!phonePattern.test(pn)) {
        errors.pn = 'Please enter a valid 10-digit phone number';
        isValid = false;
    }

    // Password validation
    const ps = document.getElementById('ps1').value;
    if (ps.length < 6) {
        errors.ps = 'Password must be at least 6 characters';
        isValid = false;
    }

    // Confirm Password validation
    const cp = document.getElementById('cp1').value;
    if (ps !== cp) {
        errors.cp = 'Passwords do not match';
        isValid = false;
    }

    // Display errors if any
    document.getElementById('fn_err').textContent = errors.fn;
    document.getElementById('ln_err').textContent = errors.ln;
    document.getElementById('em1_err').textContent = errors.em;
    document.getElementById('pn_err').textContent = errors.pn;
    document.getElementById('ps_err').textContent = errors.ps;
    document.getElementById('cp_err').textContent = errors.cp;

    if (!isValid) {
        e.preventDefault();
    }
});
