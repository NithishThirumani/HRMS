$(document).ready(function() {
    // Initialize DataTable
    const appraisalsTable = $('#appraisalsTable').DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "columns": [
            { "data": null, render: function(data) {
                return data.first_name + ' ' + data.last_name;
            }},
            { "data": "department_name" },
            { "data": "position" },
            { "data": "status" },
            { "data": "hod_rating" },
            { "data": null, render: function(data) {
                return `<button class="btn btn-primary btn-sm review-appraisal" 
                        data-id="${data.appraisal_id}">Review</button>`;
            }}
        ]
    });

    // Load appraisals based on filters
    function loadAppraisals() {
        const period_id = $('#periodFilter').val();
        const department_id = $('#departmentFilter').val();

        $.ajax({
            url: 'appraisal/ajax/hr_actions.php',
            type: 'GET',
            data: { period_id, department_id },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    appraisalsTable.clear().rows.add(response.data).draw();
                }
            }
        });
    }

    // Filter change event handlers
    $('#periodFilter, #departmentFilter').on('change', loadAppraisals);

    // Initial load
    loadAppraisals();

    // Review Appraisal
    $(document).on('click', '.review-appraisal', function() {
        const appraisalId = $(this).data('id');
        window.location.href = `review_form.php?id=${appraisalId}`;
    });
});