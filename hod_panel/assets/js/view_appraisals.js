$(document).ready(function() {
    var table = $('#appraisalsTable').DataTable({
        "processing": true,
        "serverSide": false,
        "ajax": {
            "url": "get_appraisals.php",
            "type": "GET",
            "data": function(d) {
                return {
                    "period_id": $('#periodFilter').val(),
                    "department": $('#departmentFilter').val(),
                    "status": $('#statusFilter').val()
                };
            }
        },
        "columns": [
            { "data": "employee" },
            { "data": "department" },
            { "data": "period" },
            { "data": "status" },
            { "data": "final_rating" },
            { "data": "actions" }
        ]
    });

    // Refresh table when filters change
    $('#periodFilter, #departmentFilter, #statusFilter').change(function() {
        table.ajax.reload();
    });
});