$(document).ready(function() {
    // Add new criteria
    $('#addCriteriaForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: '../api/add_criteria.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    alert('Criteria added successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Error occurred while saving criteria');
            }
        });
    });

    // Edit criteria
    $('.edit-criteria').on('click', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var desc = $(this).data('desc');
        var weight = $(this).data('weight');

        // Create edit modal if it doesn't exist
        if (!$('#editCriteriaModal').length) {
            var modalHtml = `
                <div class="modal fade" id="editCriteriaModal">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Edit Criteria</h4>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <form id="editCriteriaForm">
                                <input type="hidden" name="criteria_id">
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label>Criteria Name</label>
                                        <input type="text" class="form-control" name="criteria_name" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Description</label>
                                        <textarea class="form-control" name="description" required></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label>Weightage (%)</label>
                                        <input type="number" class="form-control" name="weightage" min="0" max="100" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>`;
            $('body').append(modalHtml);
        }

        // Populate modal with data
        var modal = $('#editCriteriaModal');
        modal.find('[name="criteria_id"]').val(id);
        modal.find('[name="criteria_name"]').val(name);
        modal.find('[name="description"]').val(desc);
        modal.find('[name="weightage"]').val(weight);
        modal.modal('show');
    });

    // Handle edit form submission
    $(document).on('submit', '#editCriteriaForm', function(e) {
        e.preventDefault();
        $.ajax({
            url: '../api/update_criteria.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    alert('Criteria updated successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Error occurred while updating criteria');
            }
        });
    });

    // Toggle status
    $('.toggle-status').on('click', function() {
        var id = $(this).data('id');
        $.ajax({
            url: '../api/toggle_criteria_status.php',
            type: 'POST',
            data: { criteria_id: id },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Error occurred while toggling status');
            }
        });
    });
});