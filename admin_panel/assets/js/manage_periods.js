$(document).ready(function() {
    // Initialize DataTable
    $('#periodsTable').DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "order": [[0, "desc"]]
    });

    // Add Period Form Submission
    $('#addPeriodForm').on('submit', function(e) {
        e.preventDefault();

        if (!validateDates($(this).find('[name="start_date"]').val(), 
                          $(this).find('[name="end_date"]').val())) {
            return;
        }

        $.ajax({
            url: 'ajax/period_actions.php',
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
            }
        });
    });

    // Edit Period Button Click
    $('.edit-period').on('click', function() {
        const id = $(this).data('id');
        const start = $(this).data('start');
        const end = $(this).data('end');

        $('#editPeriodId').val(id);
        $('#editStartDate').val(start);
        $('#editEndDate').val(end);
        $('#editPeriodModal').modal('show');
    });

    // Edit Period Form Submission
    $('#editPeriodForm').on('submit', function(e) {
        e.preventDefault();

        if (!validateDates($('#editStartDate').val(), $('#editEndDate').val())) {
            return;
        }

        $.ajax({
            url: 'ajax/period_actions.php',
            type: 'POST',
            data: $(this).serialize() + '&action=edit',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#editPeriodModal').modal('hide');
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
            }
        });
    });

    // Delete Period Button Click
    $('.delete-period').on('click', function() {
        const id = $(this).data('id');

        Swal.fire({
            title: 'Delete Period?',
            text: "This action cannot be undone",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'ajax/period_actions.php',
                    type: 'POST',
                    data: {
                        action: 'delete',
                        period_id: id
                    },
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
                    }
                });
            }
        });
    });

    // Date validation helper function
    function validateDates(start, end) {
        if (new Date(start) >= new Date(end)) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Dates',
                text: 'End date must be after start date'
            });
            return false;
        }
        return true;
    }
});