@php
    $barangaysituational = config('barangay');
@endphp

<input type="hidden" name="record_id" id="record_id_situational" value="0">
<input type="hidden" name="typeOfRecord" id="typeOfRecord_situational" value="SITUATIONALREPORT">
<input type="hidden" name="staff_id" id="staff_id_situational" value="{{ auth()->user()->id }}">
<div class="card-body pt-2">
    <div class="mt-0 mb-2">
        <div class="d-flex justify-content-center gap-2 align-items-center mb-2">
            <img src="{{ asset('assets/images/logo2.png') }}" class="bg-white rounded-circle" width=""
                alt="" style="width: 58px; height: 58px" />
            <img src="{{ asset('assets/images/logo1.png') }}" class="bg-white rounded-circle" width=""
                alt="" style="width: 58px; height: 58px" />
        </div>
        <p class="mb-3 text-center fw-semibold" style="font-size: 16px;">TIBIAO MDRRMO SITUATIONAL REPORT</p>
        <hr>
    </div>
    <div class="row mx-auto align-items-end">
        @if (auth()->user() && auth()->user()->usertype != 'STAFF')
            <div class="col-3">
                <div class="form-group">
                    <label for="" class="mb-1">First Name</label>
                    <input type="text" value="{{ auth()->user()->firstname }}" id="firstname_situational"
                        class="form-control input-out" readonly>
                </div>

            </div>
            <div class="col-3">
                <div class="form-group">
                    <label for="" class="mb-1">Middle Name</label>
                    <input type="text" id="middlename_situational" value="{{ auth()->user()->middlename }}"
                        class="form-control input-out" readonly>
                </div>
            </div>
            <div class="col-3">
                <div class="form-group">
                    <label for="" class="mb-1">Last Name</label>
                    <input type="text" id="lastname_situational" value="{{ auth()->user()->lastname }}"
                        class="form-control input-out" readonly>
                </div>
            </div>
            <div class="col-3">
                <div class="form-group">
                    <label for="" class="mb-1">Designation</label>
                    <input type="text" id="designation_situational" value="{{ auth()->user()->designation }}"
                        class="form-control input-out" readonly>
                </div>
            </div>
            <div class="col-3">
                <div class="form-group mt-1">
                    <label for="" class="mb-1">Contact</label>
                    <input type="text" id="phone_num_situational" value="{{ auth()->user()->phone_num }}"
                        class="form-control input-out" readonly>
                </div>
            </div>
        @endif
        <div class="col-6">
            <div class="form-group">
                <label for="" class="mb-1">Barangay</label>
                <input type="search" name="barangay" id="barangay_situational" autocomplete="off"
                    class="form-control input-out" required list="situationalbarangay">
                <datalist id="situationalbarangay">
                    @foreach ($barangaysituational as $b)
                        <option>{{ $b }}</option>
                    @endforeach
                </datalist>
            </div>
        </div>
        <div class="col-3">
            <div class="form-group mt-1">
                <label for="" class="mb-1">Affected Families</label>
                <input type="number" id="affectedfamilies_situational" name="affectedfamilies"
                    class="form-control input-out" required>
            </div>
        </div>
        <div class="col-3 mt-3">
            <div class="form-group mt-1">
                <label for="" class="mb-1">Person/Individuals</label>
                <input type="number" name="individuals" id="individuals_situational" required
                    class="form-control input-out">
            </div>
        </div>
        <div class="col-4">
            <div class="row mx-auto px-0">
                <div class="col-12 px-0">
                    <label for="" class="mb-0 mt-1 text-nowrap">(Evacuation Centers/Outside)</label>
                </div>
                <div class="col-6 ps-0">
                    <div class="form-group">
                        <label for="" class="mb-1">Families</label>
                        <input type="number" name="evacuationfamilies" id="evacuationfamilies_situational"
                            value="0" class="form-control input-out" required>
                    </div>
                </div>
                <div class="col-6 pe-0">
                    <div class="form-group">
                        <label for="" class="mb-1">Individuals</label>
                        <input type="number" name="evacuationindividuals" id="evacuationindividuals_situational"
                            value="0" class="form-control input-out" required>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-5 mt-3">
            <div class="form-group">
                <label for="" class="mb-1">File Submitted</label>
                <input type="file" name="filesubmitted" class="form-control input-out">
            </div>
        </div>
        <div class="col-12">
            <div class="form-group mt-1">
                <label for="" class="mb-1">Remarks</label>
                <textarea rows="3" name="remarks" id="remarks_situational" required class="form-control input-out"></textarea>
            </div>
        </div>
    </div>
</div>
