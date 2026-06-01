$(document).ready(function() {
    var table = $('#appraisalsTable').DataTable({
        processing: true,
        ajax: {
            url: 'fetch_appraisals.php',
            type: 'GET',
            dataType: 'json',
            data: function() {
                return {
                    period_id: $('#periodFilter').val(),
                    department_id: $('#departmentFilter').val(),
                    status: $('#statusFilter').val()
                };
            },
            dataSrc: 'data',
            error: function(xhr) {
                console.error('Appraisal load error:', xhr.responseText);
            }
        },
        columns: [
            { data: 'employee' },
            { data: 'department' },
            { data: 'period' },
            { data: 'status' },
            { data: 'final_rating' },
            { data: 'actions', orderable: false }
        ],
        pageLength: 10
    });

    $('#periodFilter, #departmentFilter, #statusFilter').on('change', function() {
        table.ajax.reload();
    });
});
