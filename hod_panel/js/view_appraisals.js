$(document).ready(function() {
    var table = $('#appraisalsTable').DataTable({
        "processing": true,
        "ajax": {
            "url": "fetch_appraisals.php",
            "type": "GET",
            "dataType": "json",
            "data": function(d) {
                return {
                    "period_id": $('#periodFilter').val(),
                    "department": $('#departmentFilter').val(),
                    "status": $('#statusFilter').val()
                };
            },
            "error": function(xhr, error, thrown) {
                console.log('Error:', error);
            }
        },
        "columns": [
            { "data": "employee" },
            { "data": "department" },
            { "data": "period" },
            { "data": "status" },
            { "data": "final_rating" },
            { "data": "actions" }
        ],
        "pageLength": 10
    });

    $('#periodFilter, #departmentFilter, #statusFilter').on('change', function() {
        table.ajax.reload();
    });
});