@php
    $barangayincident = config('barangay');
@endphp

<input type="hidden" name="record_id" id="record_id_incident" value="0">
<input type="hidden" name="typeOfRecord" id="typeOfRecord_incident" value="INCIDENTREPORT">
<input type="hidden" name="staff_id" id="staff_id_incident" value="{{ auth()->user()->id }}">
<div class="card-body pt-2">
    <div class="mt-0 mb-2">
        <div class="d-flex justify-content-center gap-2 align-items-center mb-2">
            <img src="{{ asset('assets/images/logo2.png') }}" class="bg-white rounded-circle" width=""
                alt="" style="width: 58px; height: 58px" />
            <img src="{{ asset('assets/images/logo1.png') }}" class="bg-white rounded-circle" width=""
                alt="" style="width: 58px; height: 58px" />
        </div>
        <p class="mb-3 text-center fw-semibold text-prime" style="font-size: 16px;">TIBIAO MDRRMO INCIDENT REPORT</p>
        <hr>
    </div>
    <div class="row mx-auto align-items-end">
        @if (auth()->user() && auth()->user()->usertype != 'STAFF')
            <div class="col-4">
                <div class="form-group">
                    <label for="" class="mb-1">First Name</label>
                    <input type="text" value="{{ auth()->user()->firstname }}" id="firstname_incident"
                        class="form-control input-out" readonly>
                </div>

            </div>
            <div class="col-4">
                <div class="form-group">
                    <label for="" class="mb-1">Middle Name</label>
                    <input type="text" id="middlename_incident" value="{{ auth()->user()->middlename }}"
                        class="form-control input-out" readonly>
                </div>
            </div>
            <div class="col-4">
                <div class="form-group">
                    <label for="" class="mb-1">Last Name</label>
                    <input type="text" id="lastname_incident" value="{{ auth()->user()->lastname }}"
                        class="form-control input-out" readonly>
                </div>
            </div>
            <div class="col-4">
                <div class="form-group">
                    <label for="" class="mb-1">Designation</label>
                    <input type="text" id="designation_incident" value="{{ auth()->user()->designation }}"
                        class="form-control input-out" readonly>
                </div>
            </div>
            <div class="col-4">
                <div class="form-group mt-1">
                    <label for="" class="mb-1">Contact</label>
                    <input type="text" id="phone_num_incident" value="{{ auth()->user()->phone_num }}"
                        class="form-control input-out" readonly>
                </div>
            </div>
        @endif
        <div class="col-4">
            <div class="form-group mt-1">
                <label for="" class="mb-1">Type of Incident</label>
                <input type="text" id="typeincident_incident" name="typeincident" class="form-control input-out"
                    required>
            </div>
        </div>
        <div class="col-4">
            <div class="form-group">
                <label for="" class="mb-1">Barangay</label>
                <input type="search" name="barangay" autocomplete="off" id="barangay_incident"
                    class="form-control input-out" required list="incidentbarangay">
                <datalist id="incidentbarangay">
                    @foreach ($barangayincident as $b)
                        <option>{{ $b }}</option>
                    @endforeach
                </datalist>
            </div>
        </div>
        <div class="col-4 mt-3">
            <div class="form-group mt-1">
                <label for="" class="mb-1">Date & Time of Occurence</label>
                <input type="datetime-local" name="datetimeoccurence" id="datetimeoccurence_incident" required
                    class="form-control input-out">
            </div>
        </div>
        <div class="col-4 mt-3">
            <div class="form-group">
                <label for="" class="mb-1">Specific Location</label>
                <input type="text" name="specificlocation" id="specificlocation_incident"
                    class="form-control input-out" required>
            </div>
        </div>
        <div class="col-6">
            <div class="row mx-auto">
                <div class="col-12 px-0">
                    <label for="" class="mb-0 mt-1 text-nowrap">(No. of Person Involved)</label>
                </div>
                <div class="col-6 ps-0">
                    <div class="form-group">
                        <label for="" class="mb-1">Injured</label>
                        <input type="number" name="involvedinjured" id="involvedinjured_incident" value="0"
                            class="form-control input-out" required>
                    </div>
                </div>
                <div class="col-6 pe-0">
                    <div class="form-group">
                        <label for="" class="mb-1">Dead</label>
                        <input type="number" name="involveddead" id="involveddead_incident" value="0"
                            class="form-control input-out" required>
                    </div>
                </div>
            </div>

        </div>
        <div class="col-6 mt-3">
            <div class="form-group">
                <label for="" class="mb-1">Cause of Incident</label>
                <input type="text" name="cause" id="cause_incident"
                    class="form-control input-out" required>
            </div>
        </div>
        <div class="col-6 mt-3">
            <div class="form-group">
                <label for="" class="mb-1">File Submitted</label>
                <input type="file" name="filesubmitted" class="form-control input-out">
            </div>
        </div>
        <div class="col-12">
            <div class="form-group mt-1">
                <label for="" class="mb-1">Detailed Description of Incident</label>
                <textarea rows="3" name="detaileddesc" id="detaileddesc_incident" required class="form-control input-out"></textarea>
            </div>
        </div>
        @if (env('SECOND_VERSION') == true)
            <div class="col-12 mt-3">
                <button type="button" class="btn btn-success" id="addMapDataBtn">
                    <i class="bi bi-plus-circle me-1"></i>
                    Add Data
                </button>
            </div>
            <div class="divDataClone px-0">

            </div>
        @endif
    </div>
</div>
