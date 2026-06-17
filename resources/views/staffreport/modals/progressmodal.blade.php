<div class="modal fade reportFormModal" id="progressModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="progressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" style="max-width: 40vw">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="progressModalLabel">
                    <i class="bi bi-folder-fill text-prime"></i>
                    <span class="text-dark-icon-title">
                        Progress Report
                    </span>
                </h1>
                <button type="button" class="btn-close dark-gray" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form class="reportform" enctype="multipart/form-data">
                <div class="modal-body">
                    @include('staffreport.forms.progressform')
                </div>
                <div class="modal-footer">
                    @if (auth()->user() && auth()->user()->usertype == 'STAFF')
                        <button type="submit" class="btn btn-primary btn-update-button btn-gray-new">Submit</button>
                    @endif
                    <button type="button" class="btn btn-secondary btn-prime" data-bs-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>
