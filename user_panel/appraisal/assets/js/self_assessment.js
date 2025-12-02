$(document).ready(function() {
    $('#selfAssessmentForm').on('submit', function(e) {
        e.preventDefault();
        
        // Disable submit button
        $('#submitAssessment').prop('disabled', true).html('Submitting...');
        
        $.ajax({
            type: 'POST',
            url: 'submit_assessment.php',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    alert('Assessment submitted successfully!');
                    window.location.reload();
                } else {
                    alert('Error: ' + (response.message || 'Unknown error occurred'));
                    $('#submitAssessment').prop('disabled', false).html('Submit Assessment');
                }
            },
            error: function(xhr, status, error) {
                console.error('Submission error:', error);
                console.error('Server response:', xhr.responseText);
                alert('An error occurred while submitting the assessment.');
                $('#submitAssessment').prop('disabled', false).html('Submit Assessment');
            }
        });
    });
});