$(document).ready(function() {
    // Handle form submission
    $('#hrReviewForm').on('submit', function(e) {
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

        if (!isValid) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Ratings',
                text: 'Please ensure all ratings are between 1 and 5'
            });
            return;
        }

        // Confirm submission
        Swal.fire({
            title: 'Submit Review?',
            text: "Please ensure all ratings and comments are final",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, submit'
        }).then((result) => {
            if (result.isConfirmed) {
                // Submit form via AJAX
                $.ajax({
                    url: 'appraisal/ajax/hr_actions.php',
                    type: 'POST',
                    data: $(this).serialize() + '&action=submit_review',
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                                allowOutsideClick: false
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = 'review_appraisals.php';
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
        });
    });

    // Real-time rating validation
    $('input[type="number"]').on('input', function() {
        const value = parseFloat($(this).val());
        if (isNaN(value) || value < 1 || value > 5) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
});