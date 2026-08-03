<div class="modal fade" id="addRental" tabindex="-1" aria-labelledby="addRentalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <form id="stallForm">

                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="addRentalLabel">
                        <i class="bi bi-shop"></i>
                        Stall
                    </h1>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <input type="hidden" id="id" name="id">

                    <div class="mb-3">
                        <label for="section" style="font-size: 14px;">
                            Section
                        </label>

                        <select class="form-select" id="section" name="section">
                            <option value="">Select Section</option>
                            <option value="Fish">Fish Section</option>
                            <option value="Pork">Pork Section</option>
                            <option value="Poultry">Poultry Section</option>
                            <option value="Beef">Beef Section</option>
                            <option value="Mixed">Mixed Section</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="stall_no" style="font-size: 14px;">
                            Stall No.
                        </label>

                        <input type="number" class="form-control" id="stall_no" name="stall_no"
                            placeholder="Enter stall number">
                    </div>

                    {{-- <div class="mb-3">
                        <label for="status" style="font-size: 14px;">
                            Status
                        </label>

                        <select class="form-select" id="status" name="status">
                            <option value="available">Available</option>
                            <option value="occupied">Occupied</option>
                        </select>
                    </div> --}}

                    {{-- <div class="mb-3">
                        <label for="tenant_name" style="font-size: 14px;">
                            Tenant Name
                        </label>

                        <input type="text" class="form-control" id="tenant_name" name="tenant_name"
                            placeholder="Enter tenant name">
                    </div> --}}

                    {{-- <div class="mb-3">
                        <label for="business_name" style="font-size: 14px;">
                            Business Name
                        </label>

                        <input type="text" class="form-control" id="business_name" name="business_name"
                            placeholder="Enter business name">
                    </div> --}}

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Close
                    </button>

                    <button type="submit" class="btn btn-success" id="saveStall">
                        Add Stall
                    </button>

                </div>

            </form>

        </div>
    </div>
</div>
