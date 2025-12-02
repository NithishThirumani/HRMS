$(document).ready(function() {
    // Initialize DataTable
    $('#periodsTable').DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false
    });

    // Add Period Form Submission
    $('#addPeriodForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'appraisal/ajax/period_actions.php',
            type: 'POST',
            data: $(this).serialize() + '&action=add',
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

    // Edit Period
    $('.edit-period').on('click', function() {
        const id = $(this).data('id');
        const start = $(this).data('start');
        const end = $(this).data('end');

        Swal.fire({
            title: 'Edit Period',
            html: `
                <input type="hidden" id="edit_period_id" value="${id}">
                <div class="form-group">
                    <label>Start Date</label>
                    <input type="date" id="edit_start_date" class="form-control" value="${start}" required>
                </div>
                <div class="form-group">
                    <label>End Date</label>
                    <input type="date" id="edit_end_date" class="form-control" value="${end}" required>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Update',
            preConfirm: () => {
                return {
                    period_id: document.getElementById('edit_period_id').value,
                    start_date: document.getElementById('edit_start_date').value,
                    end_date: document.getElementById('edit_end_date').value
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'appraisal/ajax/period_actions.php',
                    type: 'POST',
                    data: {
                        action: 'update',
                        ...result.value
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            window.location.reload();
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    }
                });
            }
        });
    });

    // Delete Period
    $('.delete-period').on('click', function() {
        const periodId = $(this).data('id');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'appraisal/ajax/period_actions.php',
                    type: 'POST',
                    data: {
                        action: 'delete',
                        period_id: periodId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            window.location.reload();
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    }
                });
            }
        });
    });
});