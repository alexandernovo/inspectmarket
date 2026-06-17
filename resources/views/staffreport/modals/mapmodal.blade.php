<div class="modal fade mapModal" id="mapModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="mapModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="mapModalLabel">
                    <i class="bi bi-geo-alt-fill"></i>
                    Map
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="mapIncident" class="mapLeaflet"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" id="incidentMapSelect" class="btn btn-primary">Select</button>
            </div>
        </div>
    </div>
</div>
