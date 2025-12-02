<?php require APPROOT . '/views/inc/header.php'; ?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card card-body bg-light mt-5">
            <h2>Submit New Claim</h2>
            <form action="<?php echo URLROOT; ?>/claims/create" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Claim Type</label>
                    <select name="claim_type" class="form-control" required>
                        <option value="">Select Type</option>
                        <option value="travel">Travel Expense</option>
                        <option value="medical">Medical Insurance</option>
                        <option value="leave">Leave Encashment</option>
                        <option value="relocation">Relocation</option>
                        <option value="wfh">Work From Home Allowance</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Total Amount</label>
                    <input type="number" name="claim_amount" class="form-control" step="0.01" required>
                </div>

                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="claim_date" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="4" required></textarea>
                </div>

                <div id="documents-container">
                    <h4>Supporting Documents</h4>
                    <div class="document-entry mb-3">
                        <div class="row">
                            <div class="col-md-4">
                                <input type="file" name="documents[]" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="doc_type[]" class="form-control" placeholder="Document Type" required>
                            </div>
                            <div class="col-md-3">
                                <input type="number" name="doc_amount[]" class="form-control" placeholder="Amount" step="0.01" required>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-danger btn-sm remove-doc">×</button>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn btn-info mb-3" id="add-document">Add More Documents</button>

                <div class="form-group">
                    <input type="submit" class="btn btn-success" value="Submit Claim">
                    <a href="<?php echo URLROOT; ?>/claims" class="btn btn-light">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('add-document').addEventListener('click', function() {
    const container = document.getElementById('documents-container');
    const newEntry = document.querySelector('.document-entry').cloneNode(true);
    newEntry.querySelector('input[type="file"]').value = '';
    newEntry.querySelector('input[name="doc_type[]"]').value = '';
    newEntry.querySelector('input[name="doc_amount[]"]').value = '';
    container.appendChild(newEntry);
});

document.addEventListener('click', function(e) {
    if(e.target.classList.contains('remove-doc')) {
        const entries = document.querySelectorAll('.document-entry');
        if(entries.length > 1) {
            e.target.closest('.document-entry').remove();
        }
    }
});
</script>

<?php require APPROOT . '/views/inc/footer.php'; ?>