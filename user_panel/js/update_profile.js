// Initialize jQuery Validate plugin for form validation
    $('#registrationForm').validate({
        rules: {
           
            fn: {
                required: true
            },
            ln: {
                required: true
            },
            email: {
                required: true
            },
            dob: {
                required: true,
            },
            gender: {
                required: true,
            },
            contactNumber: {
                required: true,
            },
            dob: {
                required: true,
            },
            address: {
                required: true,
            },
            department: {
                required:  true,
            },
            degree: {
                required:  true,
            },
          
        },
        messages: {
            
            fn: {
                required: "First Name is required field"
            },
            ln: {
                required: "Last Name is required field"
            },
            email: {
                required: "Email is required field"
            },
            dob: {
                required: "Date of birth is required field",
            },
            gender: {
                required: "Gender is required field",
            },
            contactNumber: {
                required: "Contact is required field",
            },
            dob: {
                required: "Date of birth is required field",
            },
            department: {
                required: "Department is required field",
            },
            degree: {
                required: "Degree is required field",
            },

        },
       
    });
$(document).ready(function() {
    $("#registrationForm").validate({
        rules: {
            fn: {
                required: true,
                minlength: 2
            },
            ln: {
                required: true,
                minlength: 2
            },
            contactNumber: {
                required: true,
                minlength: 10,
                maxlength: 15,
                digits: true
            },
            dob: "required",
            address: "required",
            f1: {
                extension: "jpg|jpeg|png"
            }
        },
        messages: {
            fn: {
                required: "Please enter your first name",
                minlength: "Name must be at least 2 characters long"
            },
            ln: {
                required: "Please enter your last name",
                minlength: "Name must be at least 2 characters long"
            },
            contactNumber: {
                required: "Please enter your contact number",
                digits: "Please enter only digits",
                minlength: "Contact number must be at least 10 digits",
                maxlength: "Contact number cannot exceed 15 digits"
            },
            dob: "Please select your date of birth",
            address: "Please enter your address",
            f1: {
                extension: "Please upload only jpg, jpeg or png files"
            }
        },
        errorElement: "span",
        errorClass: "error-msg"
    });
});