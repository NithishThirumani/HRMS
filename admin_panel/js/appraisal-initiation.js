$(document).ready(function() {
    $('#initiateAppraisalForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            start_date: $('input[name="start_date"]').val(),
            end_date: $('input[name="end_date"]').val(),
            departments: $('select[name="departments[]"]').val()
        };

        $.ajax({
            url: 'ajax/save_appraisal.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(result) {
                if (result.success) {
                    alert('Appraisal initiated successfully!');
                    window.location.href = 'manage_periods.php';
                } else {
                    alert('Error: ' + (result.message || 'Failed to initiate appraisal'));
                }
            },
            error: function(xhr) {
                let msg = 'An error occurred while initiating the appraisal.';
                try {
                    const r = JSON.parse(xhr.responseText);
                    if (r.message) msg = r.message;
                } catch (e) {}
                alert(msg);
            }
        });
    });

    // Initialize Select2 for better dropdown experience
    if ($.fn.select2) {
        $('.select2').select2({
            placeholder: 'Select departments',
            width: '100%'
        });
    }
});