@php
    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
@endphp
<div class="modal fade" id="newwastecollectModal" data-bs-backdrop="static" tabindex="-1"
    aria-labelledby="newwastecollectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 ms-2">
                    <span>
                        <i class="ti ti-user-plus" style="font-size: 20px"></i>
                    </span>
                    <span id="newwastecollectModalLabel">
                        New Waste Collection
                    </span>
                </h1>
                <button type="button" class="btn" data-bs-dismiss="modal" aria-label="Close">
                    <i class="bi bi-x-lg" style="font-size: 15px"></i>
                </button>
            </div>
            <form id="newwastecollectform">
                <input type="hidden" id="wastecollect_id" name="wastecollect_id" value="0">
                <div class="modal-body">
                    <div class="row mx-auto">
                        <div class="col-6">
                            <div class="form-group mb-1">
                                <label for="" class="mb-1">Barangay</label>
                                <input type="text" name="barangay" id="barangay" autocomplete="off" class="form-control" required>
                            </div>
                            <div class="form-group mb-1">
                                <label for="" class="mb-1">Schedule From</label>
                                <select name="schedule_from" id="schedule_from" class="form-select" required>
                                    @foreach ($days as $d)
                                        <option value="{{ $d }}">{{ $d }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group mb-1">
                                <label for="" class="mb-1">Schedule To</label>
                                <select name="schedule_to" id="schedule_to" class="form-select" required>
                                    @foreach ($days as $d)
                                        <option value="{{ $d }}">{{ $d }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group mb-1">
                                <label for="" class="mb-1">Recyclable</label>
                                <input type="number" name="recyclable" id="recyclable" class="form-control" required>
                            </div>
                            <div class="form-group mb-1">
                                <label for="" class="mb-1">Biodegradable</label>
                                <input type="number" name="biodegradable" id="biodegradable" class="form-control" required>
                            </div>
                            <div class="form-group mb-1">
                                <label for="" class="mb-1">Non-Bio</label>
                                <input type="number" name="nonbio" id="nonbio" class="form-control" required>
                            </div>
                            <div class="form-group mb-1">
                                <label for="" class="mb-1">Special Waste</label>
                                <input type="number" name="specialwaste" id="specialwaste" class="form-control" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
