@extends('market.layouts.portal')

@section('content')
    @php
        $typeLabel = $livestockType === 'PORK' ? 'Pork' : ucfirst(strtolower($livestockType));
        $typeIcon = match ($livestockType) {
            'POULTRY' => 'bi-egg-fried',
            'PORK' => 'bi-piggy-bank-fill',
            default => 'bi-heart-pulse-fill',
        };
    @endphp

    <section class="inspector-page inspector-records-page">
        <header class="inspector-page-title">
            <i class="bi {{ $typeIcon }}"></i>
            <div><h1>{{ strtoupper($typeLabel) }}</h1><p>Dashboard | {{ $typeLabel }}</p></div>
        </header>

        <section class="inspector-table-panel">
            <div class="inspector-record-toolbar">
                <select id="recordLength" aria-label="Rows per page"><option value="5">5 v</option><option value="10" selected>10 v</option><option value="25">25 v</option></select>
                <label>From:<input type="date" id="recordFrom"></label>
                <label>To:<input type="date" id="recordTo"></label>
                <button type="button" id="recordFilter"><i class="bi bi-funnel-fill"></i> Filter</button>
                <div class="inspector-record-title"><i class="bi {{ $typeIcon }}"></i><strong>{{ strtoupper($typeLabel) }} SLAUGHTERED</strong></div>
                <label class="inspector-record-search"><input type="search" id="recordSearch" placeholder="Search"><i class="bi bi-search"></i></label>
                <button type="button" class="inspector-add-button" id="addInspection"><i class="bi bi-plus-circle"></i> Add Inspect</button>
            </div>
            <div class="table-wrap">
                <table id="inspectorRecordsTable" class="inspector-data-table">
                    @if ($livestockType === 'POULTRY')
                        <thead><tr><th>NO.</th><th>OWNER</th><th>ADDRESS</th><th>TYPE OF POULTRY</th><th>DATE OF INSPECTION</th><th>INSPECTION RESULT</th><th>ACTION</th></tr></thead>
                    @else
                        <thead><tr><th>NO.</th><th>OWNER</th><th>ADDRESS</th><th>AGE</th><th>WEIGHT</th><th>DATE OF INSPECTION</th><th>INSPECTION RESULT</th><th>ACTION</th></tr></thead>
                    @endif
                    <tbody></tbody>
                </table>
            </div>
        </section>
    </section>

    <dialog id="inspectorRecordDialog" class="inspector-record-dialog">
        <form id="inspectorRecordForm" action="{{ route('inspector.inspections.store') }}" method="POST" data-store-url="{{ route('inspector.inspections.store') }}">
            @csrf
            <input type="hidden" name="_method" value="POST" data-method>
            <input type="hidden" name="livestock_type" value="{{ $livestockType }}">
            <input type="hidden" name="status" value="COMPLETED">

            <section class="inspector-form-column">
                <header class="inspector-form-hero">
                    <i class="bi {{ $typeIcon }}"></i>
                    <div><h2>{{ strtoupper($typeLabel) }}</h2><p>SLAUGHTERED INSPECTION FORM</p></div>
                    <button type="button" data-close-inspector-record aria-label="Close"><i class="bi bi-x-circle-fill"></i></button>
                </header>

                <fieldset>
                    <legend>1. GENERAL INFORMATION</legend>
                    <div class="inspector-form-grid">
                        <label>Inspection No:<input data-request-number placeholder="Auto-generated" readonly></label>
                        <label>Date of Inspection:<input type="datetime-local" name="scheduled_at" required></label>
                        <label>Name of Owner:<input name="owner_name" placeholder="Enter owner name" required></label>
                        <label>Source/Farm:<input name="source_location" placeholder="Enter source/farm"></label>
                        <label>Inspector:<input value="{{ auth()->user()->full_name }}" readonly></label>
                        <label>Address:<input name="address" placeholder="Enter address" required></label>
                        <label>Contact Number:<input name="contact_number" placeholder="Enter contact number" required></label>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>2. {{ strtoupper($typeLabel) }} INFORMATION</legend>
                    <div class="inspector-form-grid">
                        @if ($livestockType === 'POULTRY')
                            <label>Type of Poultry:
                                <select name="breed"><option value="">Select Type</option><option>Chicken (Native)</option><option>Chicken (Layer)</option><option>Chicken (Broiler)</option><option>Duck</option></select>
                            </label>
                        @else
                            <label>Age:<input name="animal_age" placeholder="Enter age"></label>
                            <label>Weight (kg):<input type="number" step="0.01" min="0" name="live_weight" placeholder="Enter weight"></label>
                        @endif
                        <label>Number Slaughtered:<input type="number" min="1" name="animal_count" required></label>
                        <label class="wide">Purpose:
                            <select name="purpose"><option value="">Select Purpose</option><option>Human Consumption</option><option>Commercial Sale</option><option>Further Processing</option></select>
                        </label>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>3. ANTE-MORTEM FINDINGS</legend>
                    <div class="inspector-radio-line">
                        <span>General Condition:</span>
                        @foreach (['HEALTHY' => 'Healthy', 'SICK' => 'Sick', 'WEAK' => 'Weak', 'OTHER' => 'Other'] as $value => $label)
                            <label><input type="radio" name="ante_mortem_findings[general_condition]" value="{{ $value }}"> {{ $label }}</label>
                        @endforeach
                    </div>
                    <label>Remarks:<textarea name="ante_mortem_findings[remarks]" placeholder="Enter remarks (optional)"></textarea></label>
                </fieldset>
            </section>

            <section class="inspector-form-column inspector-form-results">
                <fieldset>
                    <legend>4. POST-MORTEM FINDINGS</legend>
                    @foreach (['general_condition' => 'General Condition', 'organs_examination' => 'Organs Examination'] as $name => $label)
                        <div class="inspector-radio-line">
                            <span>{{ $label }}:</span>
                            @foreach (['PASSED' => 'Passed', 'CONDEMNED' => 'Condemned', 'REINSPECTION' => 'For Further Exam'] as $value => $text)
                                <label><input type="radio" name="post_mortem_findings[{{ $name }}]" value="{{ $value }}"> {{ $text }}</label>
                            @endforeach
                        </div>
                    @endforeach
                    <label>Others (Specify):<textarea name="post_mortem_findings[remarks]" placeholder="Enter remarks (optional)"></textarea></label>
                </fieldset>
                <fieldset>
                    <legend>5. INSPECTION RESULT</legend>
                    <label>Final Judgment:
                        <select name="inspection_result" required>
                            <option value="">Select Result</option>
                            <option value="PASSED">Passed with Human Consumption</option>
                            <option value="CONDEMNED">Condemned</option>
                            <option value="REINSPECTION">For Further Examination</option>
                        </select>
                    </label>
                    <label>Remarks:<textarea name="remarks" placeholder="Enter remarks (optional)"></textarea></label>
                </fieldset>
                <footer>
                    <button type="submit" class="inspector-save-button">Save</button>
                    <button type="button" class="inspector-cancel-button" data-close-inspector-record>Cancel</button>
                </footer>
            </section>
        </form>
    </dialog>
@endsection

@push('scripts')
    <script>
        $(function () {
            let dateFrom = '';
            let dateTo = '';
            const livestockType = @json($livestockType);
            const columns = livestockType === 'POULTRY'
                ? [
                    { data: null, orderable: false, searchable: false, render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1 },
                    { data: 'owner', orderable: false },
                    { data: 'address', orderable: false },
                    { data: 'breed', orderable: false },
                    { data: 'schedule', orderable: false },
                    { data: 'result', orderable: false },
                    { data: 'action', orderable: false, searchable: false }
                ]
                : [
                    { data: null, orderable: false, searchable: false, render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1 },
                    { data: 'owner', orderable: false },
                    { data: 'address', orderable: false },
                    { data: 'age', orderable: false },
                    { data: 'weight', orderable: false },
                    { data: 'schedule', orderable: false },
                    { data: 'result', orderable: false },
                    { data: 'action', orderable: false, searchable: false }
                ];

            const table = $('#inspectorRecordsTable').DataTable({
                processing: true,
                serverSide: true,
                dom: 'rtip',
                order: [],
                pageLength: 10,
                ajax: {
                    url: "{{ route('inspector.datatable.inspections') }}",
                    data: function (data) {
                        data.mode = 'records';
                        data.type = livestockType;
                        data.dateFrom = dateFrom;
                        data.dateTo = dateTo;
                    }
                },
                columns
            });

            const dialog = document.getElementById('inspectorRecordDialog');
            const form = document.getElementById('inspectorRecordForm');

            function currentDateTimeLocal() {
                const now = new Date();
                now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
                return now.toISOString().slice(0, 16);
            }

            function previewInspectionNumber() {
                const now = new Date();
                const date = [
                    now.getFullYear(),
                    String(now.getMonth() + 1).padStart(2, '0'),
                    String(now.getDate()).padStart(2, '0')
                ].join('');
                const suffix = Math.random().toString(36).slice(2, 8).toUpperCase().padEnd(6, '0');

                return `INSP-${date}-${suffix}`;
            }

            function resetForm() {
                form.reset();
                form.action = form.dataset.storeUrl;
                form.querySelector('[data-method]').value = 'POST';
                form.querySelector('[data-request-number]').value = previewInspectionNumber();
                form.querySelector('[name="scheduled_at"]').value = currentDateTimeLocal();
                form.querySelectorAll('input, select, textarea').forEach((field) => field.disabled = false);
                form.querySelectorAll('input[readonly]').forEach((field) => field.readOnly = true);
                form.querySelector('[type="submit"]').hidden = false;
                form.classList.remove('readonly');
            }

            function setNested(name, values) {
                Object.entries(values || {}).forEach(([key, value]) => {
                    const fields = form.querySelectorAll(`[name="${name}[${key}]"]`);
                    fields.forEach((field) => {
                        if (field.type === 'radio') field.checked = field.value === value;
                        else field.value = value || '';
                    });
                });
            }

            function fillForm(record, readonly) {
                resetForm();
                form.action = record.update_url;
                form.querySelector('[data-method]').value = 'PUT';
                form.querySelector('[data-request-number]').value = record.request_number;
                const values = {
                    owner_name: record.owner_name,
                    address: record.address,
                    contact_number: record.contact_number,
                    scheduled_at: record.scheduled_date + 'T' + record.scheduled_time,
                    animal_count: record.animal_count,
                    inspection_result: record.inspection_result,
                    remarks: record.remarks,
                    breed: record.breed,
                    animal_age: record.animal_age,
                    live_weight: record.live_weight,
                    source_location: record.source_location,
                    purpose: record.purpose
                };
                Object.entries(values).forEach(([name, value]) => {
                    const field = form.querySelector(`[name="${name}"]`);
                    if (field) field.value = value || '';
                });
                setNested('ante_mortem_findings', record.ante_mortem_findings);
                setNested('post_mortem_findings', record.post_mortem_findings);
                if (readonly) {
                    form.querySelectorAll('input, select, textarea').forEach((field) => field.disabled = true);
                    form.querySelector('[type="submit"]').hidden = true;
                    form.classList.add('readonly');
                }
                dialog.showModal();
            }

            $('#addInspection').on('click', function () { resetForm(); dialog.showModal(); });
            $(document).on('click', '[data-close-inspector-record]', function () { dialog.close(); });
            $(document).on('click', '.js-inspector-view', function () { fillForm(JSON.parse($(this).attr('data-record')), true); });
            $(document).on('click', '.js-inspector-edit', function () { fillForm(JSON.parse($(this).attr('data-record')), false); });

            $('#recordLength').on('change', function () { table.page.len(Number(this.value)).draw(); });
            $('#recordSearch').on('input', function () { table.search(this.value).draw(); });
            $('#recordFilter').on('click', function () {
                dateFrom = $('#recordFrom').val();
                dateTo = $('#recordTo').val();
                table.ajax.reload();
            });

            $(form).on('submit', function (event) {
                event.preventDefault();
                const button = $(form).find('[type="submit"]').prop('disabled', true);
                $.ajax({
                    url: form.action,
                    method: 'POST',
                    data: new FormData(form),
                    processData: false,
                    contentType: false,
                    headers: { Accept: 'application/json' },
                    success: function (response) {
                        dialog.close();
                        table.ajax.reload(null, false);
                        einspectSuccess(response.message);
                    },
                    error: function (xhr) {
                        const errors = xhr.responseJSON?.errors;
                        Swal.fire({ title: 'Please Check the Form', text: errors ? Object.values(errors).flat().join(' ') : xhr.responseJSON?.message, icon: 'error', confirmButtonColor: '#760008' });
                    },
                    complete: function () { button.prop('disabled', false); }
                });
            });

            $(document).on('click', '.js-inspector-delete', function () {
                const button = $(this);
                einspectConfirm({
                    title: 'Delete Inspection?',
                    message: `Are you sure you want to delete ${button.data('reference')}?`,
                    confirmText: 'Yes, Delete',
                    cancelText: 'No, Keep It'
                }).then(function (result) {
                    if (!result.isConfirmed) return;
                    $.ajax({
                        url: button.data('url'),
                        method: 'POST',
                        data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
                        headers: { Accept: 'application/json' },
                        success: function (response) {
                            table.ajax.reload(null, false);
                            einspectSuccess(response.message);
                        }
                    });
                });
            });
        });
    </script>
@endpush
