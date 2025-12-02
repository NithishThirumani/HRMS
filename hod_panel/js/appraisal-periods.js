$(document).ready(function() {
    // Handle Add Period Form Submission
    $('#addPeriodForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            start_date: $(this).find('input[name="start_date"]').val(),
            end_date: $(this).find('input[name="end_date"]').val()
        };

        $.ajax({
            url: 'ajax/save_period.php',
            type: 'POST',
            data: formData,
            success: function(response) {
                const result = JSON.parse(response);
                if (result.success) {
                    alert('Period added successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            },
            error: function() {
                alert('An error occurred while saving the period.');
            }
        });
    });
});