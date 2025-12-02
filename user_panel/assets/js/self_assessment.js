$(document).ready(function() {
    // Handle form submission
    $('#selfAssessmentForm').on('submit', function(e) {
        e.preventDefault();

        // Validate ratings
        let isValid = true;
        $('input[type="number"]').each(function() {
            const value = parseFloat($(this).val());
            if (isNaN(value) || value < 1 || value > 5) {
                isValid = false;
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        // Validate comments
        $('textarea').each(function() {
            if (!$(this).val().trim()) {
                isValid = false;
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        if (!isValid) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Input',
                text: 'Please ensure all ratings are between 1 and 5, and all comments are filled'
            });
            return;
        }

        // Confirm submission
        Swal.fire({
            title: 'Submit Assessment?',
            text: "You won't be able to modify your assessment after submission",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, submit'
        }).then((result) => {
            if (result.isConfirmed) {
                submitForm('submit_assessment');
            }
        });
    });

    // Handle draft saving
    $('#saveDraft').on('click', function() {
        submitForm('save_draft');
    });

    function submitForm(action) {
        const formData = new FormData($('#selfAssessmentForm')[0]);
        formData.append('action', action);

        $.ajax({
            url: 'appraisal/ajax/employee_actions.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        allowOutsideClick: false
                    }).then((result) => {
                        if (result.isConfirmed && action === 'submit_assessment') {
                            window.location.reload();
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while processing your request'
                });
            }
        });
    }

    // Real-time rating validation
    $('input[type="number"]').on('input', function() {
        const value = parseFloat($(this).val());
        if (isNaN(value) || value < 1 || value > 5) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    // Real-time comment validation
    $('textarea').on('input', function() {
        if (!$(this).val().trim()) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
});