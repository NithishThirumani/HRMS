$(document).ready(function() {
    function handleJsonResponse(response, onSuccess) {
        const result = typeof response === 'string' ? JSON.parse(response) : response;
        if (result.status === 'success' || result.success) {
            onSuccess(result.message || 'Saved successfully');
        } else {
            alert('Error: ' + (result.message || 'Request failed'));
        }
    }

    $('#addCriteriaForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'ajax/criteria_actions.php',
            type: 'POST',
            data: $(this).serialize() + '&action=add',
            dataType: 'json',
            success: function(response) {
                handleJsonResponse(response, function() {
                    $('#addCriteriaModal').modal('hide');
                    location.reload();
                });
            },
            error: function() {
                alert('Error occurred while saving criteria');
            }
        });
    });

    $('.edit-criteria').on('click', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var desc = $(this).data('desc');
        var weight = $(this).data('weight');

        if (!$('#editCriteriaModal').length) {
            $('body').append(`
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
                </div>`);
        }

        var modal = $('#editCriteriaModal');
        modal.find('[name="criteria_id"]').val(id);
        modal.find('[name="criteria_name"]').val(name);
        modal.find('[name="description"]').val(desc);
        modal.find('[name="weightage"]').val(weight);
        modal.modal('show');
    });

    $(document).on('submit', '#editCriteriaForm', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'ajax/criteria_actions.php',
            type: 'POST',
            data: $(this).serialize() + '&action=edit',
            dataType: 'json',
            success: function(response) {
                handleJsonResponse(response, function() {
                    location.reload();
                });
            },
            error: function() {
                alert('Error occurred while updating criteria');
            }
        });
    });

    $('.toggle-status').on('click', function() {
        var id = $(this).data('id');
        $.ajax({
            url: 'ajax/criteria_actions.php',
            type: 'POST',
            data: { criteria_id: id, action: 'toggle' },
            dataType: 'json',
            success: function(response) {
                handleJsonResponse(response, function() {
                    location.reload();
                });
            },
            error: function() {
                alert('Error occurred while toggling status');
            }
        });
    });
});
