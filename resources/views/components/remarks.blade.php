<div class="modal fade" id="remarksModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="remarksModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" style="max-width: 30vw">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close dark-gray" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mt-0 mb-2">
                    <div class="d-flex justify-content-center gap-2 align-items-center mb-2">
                        <img src="{{ asset('assets/images/logo2.png') }}" class="bg-white rounded-circle" width=""
                            alt="" style="width: 58px; height: 58px" />
                        <img src="{{ asset('assets/images/logo1.png') }}" class="bg-white rounded-circle" width=""
                            alt="" style="width: 58px; height: 58px" />
                    </div>
                    <p class="mb-3 text-center fw-semibold text-prime" id="remarksAdminTitle" style="font-size: 16px;">TIBIAO MDRRMO REMARKS</p>
                    <hr>

                    <div class="form-group">
                        <label for="">Admin Remarks:</label>
                        <textarea id="remarksAdmin" rows="4" class="form-control"></textarea>
                    </div>
                    <button type="button" id="submitRemarks" class="btn btn-secondary btn-prime w-100 mt-3">
                        Submit Remarks
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
