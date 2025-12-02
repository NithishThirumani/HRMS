$(document).ready(function() {
    // Initialize DataTable
    $('#criteriaTable').DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false
    });

    // Add Criteria Form Submission
    $('#addCriteriaForm').on('submit', function(e) {
        e.preventDefault();

        if (!validateWeightage($(this).find('[name="weightage"]').val())) {
            return;
        }

        $.ajax({
            url: 'appraisal/ajax/criteria_actions.php',
            type: 'POST',
            data: $(this).serialize() + '&action=add',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#addCriteriaModal').modal('hide');
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

    // Edit Criteria Button Click
    $('.edit-criteria').on('click', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const desc = $(this).data('desc');
        const weight = $(this).data('weight');

        Swal.fire({
            title: 'Edit Criteria',
            html: `
                <form id="editCriteriaForm">
                    <input type="hidden" name="criteria_id" value="${id}">
                    <div class="form-group">
                        <label>Criteria Name</label>
                        <input type="text" class="form-control" name="criteria_name" value="${name}" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" name="description" required>${desc}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Weightage (%)</label>
                        <input type="number" class="form-control" name="weightage" value="${weight}" min="0" max="100" required>
                    </div>
                </form>
            `,
            showCancelButton: true,
            confirmButtonText: 'Save Changes',
            preConfirm: () => {
                const form = document.getElementById('editCriteriaForm');
                const formData = new FormData(form);
                formData.append('action', 'edit');

                return $.ajax({
                    url: 'appraisal/ajax/criteria_actions.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json'
                });
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: result.value.message
                }).then(() => {
                    window.location.reload();
                });
            }
        });
    });

    // Toggle Status Button Click
    $('.toggle-status').on('click', function() {
        const id = $(this).data('id');
        const currentStatus = $(this).text().trim();
        const newStatus = currentStatus === 'Activate' ? 'activate' : 'deactivate';

        Swal.fire({
            title: `${newStatus} Criteria?`,
            text: `Are you sure you want to ${newStatus} this criteria?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'No'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'appraisal/ajax/criteria_actions.php',
                    type: 'POST',
                    data: {
                        action: 'toggle',
                        criteria_id: id
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message
                            }).then(() => {
                                window.location.reload();
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

    // Weightage validation helper function
    function validateWeightage(weightage) {
        if (isNaN(weightage) || weightage < 0 || weightage > 100) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Weightage',
                text: 'Weightage must be between 0 and 100'
            });
            return false;
        }
        return true;
    }
});