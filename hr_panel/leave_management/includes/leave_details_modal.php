<div class="modal fade" id="leaveDetailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Leave Request Details</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Employee:</strong> <span id="modalEmployeeName"></span></p>
                        <p><strong>Department:</strong> <span id="modalDepartment"></span></p>
                        <p><strong>Leave Type:</strong> <span id="modalLeaveType"></span></p>
                        <p><strong>Duration:</strong> <span id="modalDuration"></span></p>
                        <p><strong>Total Days:</strong> <span id="modalTotalDays"></span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Status:</strong> <span id="modalStatus"></span></p>
                        <p><strong>Applied On:</strong> <span id="modalAppliedDate"></span></p>
                        <p><strong>Current Approver:</strong> <span id="modalCurrentApprover"></span></p>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>Reason for Leave:</h6>
                        <p id="modalReason"></p>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>Approval History:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Role</th>
                                        <th>Action</th>
                                        <th>Date</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody id="modalApprovalHistory">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>