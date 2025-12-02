$(document).ready(function() {
    // Initialize select2
    $('.select2').select2();

    // Handle appraisal initiation form submission
    $('#initiateAppraisalForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'appraisal/ajax/initiate_actions.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message
                    }).then((result) => {
                        if (result.isConfirmed) {
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
    });
});