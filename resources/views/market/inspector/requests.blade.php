@extends('market.layouts.portal')

@section('content')
    <section class="inspector-page inspector-requests-page">
        <header class="inspector-page-title">
            <i class="bi bi-people-fill"></i>
            <div><h1>REQUEST INSPECTION</h1><p>Dashboard | Request Inspection</p></div>
        </header>

        <section class="inspector-table-panel">
            <div class="inspector-request-toolbar">
                <select id="requestLength" aria-label="Rows per page"><option value="5">5 v</option><option value="10" selected>10 v</option><option value="25">25 v</option></select>
                <label>From:<input type="date" id="requestFrom"></label>
                <label>To:<input type="date" id="requestTo"></label>
                <button type="button" id="requestFilter"><i class="bi bi-funnel-fill"></i> Filter</button>
                <button type="button" id="requestReload"><i class="bi bi-arrow-clockwise"></i> Reload</button>
                <label class="inspector-request-search"><input type="search" id="requestSearch" placeholder="Search"><i class="bi bi-search"></i></label>
                <span>Status:</span>
                <div class="inspector-request-statuses">
                    <button type="button" data-inspector-status="PENDING">Pending</button>
                    <button type="button" data-inspector-status="APPROVED">Approved</button>
                    <button type="button" data-inspector-status="DISAPPROVED">Disapproved</button>
                </div>
            </div>
            <div class="table-wrap">
                <table id="inspectorRequestsTable" class="inspector-data-table">
                    <thead><tr><th>NO.</th><th>OWNER</th><th>ADDRESS</th><th>CONTACT NUMBER</th><th>TYPE OF SLAUGHTERED INSPECT</th><th>DATE AND TIME OF INSPECTION</th><th>REQUEST STATUS</th><th>ACTION</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>
    </section>

    <dialog id="inspectorRequestDialog" class="inspector-request-dialog">
        <form id="inspectorRequestForm">
            <button type="button" class="inspector-dialog-close" data-close-inspector-request aria-label="Close"><i class="bi bi-x-circle-fill"></i></button>
            <section class="inspector-request-calendar">
                <header>
                    <strong>{{ $inspectionMonth->format('n') }}</strong>
                    <span>{{ strtoupper($inspectionMonth->format('F')) }}</span>
                    <strong>{{ $inspectionMonth->format('Y') }}</strong>
                </header>
                <div class="inspector-calendar-week">@foreach(['SUN','MON','TUE','WED','THU','FRI','SAT'] as $day)<span>{{ $day }}</span>@endforeach</div>
                <div class="inspector-calendar-days">
                    @for ($blank = 0; $blank < $inspectionMonth->dayOfWeek; $blank++)<span class="blank"></span>@endfor
                    @for ($day = 1; $day <= $inspectionMonth->daysInMonth; $day++)
                        @php
                            $date = $inspectionMonth->copy()->day($day);
                            $dayReservations = $reservations->get((string) $day, collect());
                            $reserved = $dayReservations->isNotEmpty();
                            $weekend = $date->isWeekend();
                        @endphp
                        <button type="button" @class(['reserved' => $reserved, 'weekend' => $weekend, 'available' => ! $weekend]) data-calendar-day="{{ $day }}" data-reservations='@json($dayReservations)'>
                            {{ $day }}@if($reserved)<small>RESERVED</small>@endif
                        </button>
                    @endfor
                </div>
                <aside class="inspector-reserved-time" hidden>
                    <strong>RESERVED TIME</strong>
                    <div data-reserved-list></div>
                </aside>
                <footer>
                    <img src="{{ asset('assets/einspect/USERS/D-Inspector.png') }}" alt="">
                    <div><strong>{{ strtoupper(auth()->user()->full_name) }}</strong><span>{{ auth()->user()->designation }}</span></div>
                    <i class="bi bi-telephone-fill"></i><span>{{ auth()->user()->phone_num }}</span>
                    <i class="bi bi-envelope-fill"></i><span>{{ auth()->user()->email }}</span>
                </footer>
            </section>

            <section class="inspector-request-details">
                <header>
                    <i data-request-type-icon class="bi bi-egg-fried"></i>
                    <h2>REQUEST INSPECTION FORM</h2>
                    <p data-request-type-label>(Poultry Slaughtered Livestock)</p>
                </header>
                <h3>REQUESTER INFORMATION:</h3>
                <label>Complete Name:<span class="request-name-grid"><input data-first-name readonly><input data-middle-name readonly><input data-last-name readonly></span></label>
                <label>Address:<span class="request-address-grid"><input data-barangay readonly><input data-municipality readonly><input data-province readonly></span></label>
                <div class="request-two-column">
                    <label>Date of Inspection:<input type="date" data-request-date readonly></label>
                    <label>Time of Inspection:<input type="time" data-request-time readonly></label>
                </div>
                <label>Contact Number:<input data-request-contact readonly></label>
                <label>Email Address:<input data-request-email readonly></label>
                <label>Select Status:
                    <select data-request-status-select>
                        <option value="">Please Select</option>
                        <option value="APPROVED">Approved</option>
                        <option value="DISAPPROVED">Disapproved</option>
                    </select>
                </label>
            </section>
        </form>
    </dialog>

    <dialog id="inspectorDecisionDialog" class="inspector-decision-dialog">
        <form id="inspectorDecisionForm" method="POST">
            @csrf
            <input type="hidden" name="_method" value="PUT">
            <input type="hidden" name="status" data-decision-status>
            <header>
                <i data-decision-icon class="bi bi-check-circle"></i>
                <div><h2 data-decision-title>APPROVED REQUEST</h2><p data-decision-type>POULTRY SLAUGHTERED INSPECTION</p></div>
                <button type="button" data-close-decision aria-label="Close"><i class="bi bi-x-circle-fill"></i></button>
            </header>
            <label>Contact Number:<input data-decision-contact readonly></label>
            <label>Email Address:<input data-decision-email readonly></label>
            <div class="request-two-column">
                <label>Date:<input type="date" data-decision-date required></label>
                <label>Time:<input type="time" data-decision-time required></label>
            </div>
            <label><span data-decision-remarks-label>Remarks:</span><textarea name="remarks" placeholder="Enter remarks (optional)"></textarea></label>
            <input type="hidden" name="scheduled_at" data-decision-schedule>
            <footer>
                <button type="submit" class="inspector-save-button">Submit</button>
                <button type="button" class="inspector-cancel-button" data-close-decision>Cancel</button>
            </footer>
        </form>
    </dialog>
@endsection

@push('scripts')
    <script>
        $(function () {
            let dateFrom = '';
            let dateTo = '';
            let status = 'ALL';
            let activeRecord = null;
            const requestDialog = document.getElementById('inspectorRequestDialog');
            const decisionDialog = document.getElementById('inspectorDecisionDialog');
            const decisionForm = document.getElementById('inspectorDecisionForm');

            const table = $('#inspectorRequestsTable').DataTable({
                processing: true,
                serverSide: true,
                dom: 'rtip',
                order: [],
                pageLength: 10,
                ajax: {
                    url: "{{ route('inspector.datatable.inspections') }}",
                    data: function (data) {
                        data.mode = 'requests';
                        data.dateFrom = dateFrom;
                        data.dateTo = dateTo;
                        data.status = status;
                    }
                },
                columns: [
                    { data: null, orderable: false, searchable: false, render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1 },
                    { data: 'owner', orderable: false },
                    { data: 'address', orderable: false },
                    { data: 'contact', orderable: false },
                    { data: 'type', orderable: false },
                    { data: 'schedule', orderable: false },
                    { data: 'status', orderable: false },
                    { data: 'action', orderable: false, searchable: false }
                ],
                drawCallback: function () {
                    const focus = new URLSearchParams(window.location.search).get('focus');
                    if (!focus) return;
                    $('.js-request-view').each(function () {
                        const record = JSON.parse($(this).attr('data-record'));
                        if (String(record.id) === focus) {
                            $(this).trigger('click');
                            history.replaceState({}, '', "{{ route('inspector.inspections') }}");
                            return false;
                        }
                    });
                }
            });

            function splitName(name) {
                const parts = name.trim().split(/\s+/);
                return [parts.shift() || '', parts.length > 1 ? parts.shift() : '', parts.join(' ')];
            }

            function splitAddress(address) {
                const parts = address.split(',').map((part) => part.trim());
                return [parts[0] || '', parts[1] || 'Pandan', parts.slice(2).join(', ') || 'Antique'];
            }

            function openRequest(record) {
                activeRecord = record;
                const names = splitName(record.owner_name);
                const address = splitAddress(record.address);
                $('[data-first-name]').val(names[0]);
                $('[data-middle-name]').val(names[1]);
                $('[data-last-name]').val(names[2]);
                $('[data-barangay]').val(address[0]);
                $('[data-municipality]').val(address[1]);
                $('[data-province]').val(address[2]);
                $('[data-request-date]').val(record.scheduled_date);
                $('[data-request-time]').val(record.scheduled_time);
                $('[data-request-contact]').val(record.contact_number);
                $('[data-request-email]').val(record.email || '');
                $('[data-request-status-select]').val('');
                $('[data-request-type-label]').text('(' + record.livestock_type.charAt(0) + record.livestock_type.slice(1).toLowerCase() + ' Slaughtered Livestock)');
                $('[data-request-type-icon]').attr('class', 'bi ' + (record.livestock_type === 'POULTRY' ? 'bi-egg-fried' : record.livestock_type === 'PORK' ? 'bi-piggy-bank-fill' : 'bi-heart-pulse-fill'));
                $('[data-calendar-day]').removeClass('selected');
                $('[data-calendar-day="' + Number(record.scheduled_date.slice(-2)) + '"]').addClass('selected');
                requestDialog.showModal();
            }

            $(document).on('click', '.js-request-view', function () { openRequest(JSON.parse($(this).attr('data-record'))); });
            $('[data-close-inspector-request]').on('click', function () { requestDialog.close(); });
            $('[data-close-decision]').on('click', function () { decisionDialog.close(); });

            $('[data-calendar-day]').on('click', function () {
                const reservations = JSON.parse(this.dataset.reservations || '[]');
                const note = document.querySelector('.inspector-reserved-time');
                if (reservations.length) {
                    note.querySelector('[data-reserved-list]').innerHTML = reservations.map((item) => `<p><b>${item.time}</b><span>${item.owner} - ${item.type}</span></p>`).join('');
                    note.hidden = false;
                } else {
                    note.hidden = true;
                }
            });

            $('[data-request-status-select]').on('change', function () {
                if (!this.value || !activeRecord) return;
                const approved = this.value === 'APPROVED';
                decisionForm.action = activeRecord.update_url;
                $('[data-decision-status]').val(this.value);
                $('[data-decision-title]').text(approved ? 'APPROVED REQUEST' : 'DISAPPROVED REQUEST');
                $('[data-decision-type]').text(activeRecord.livestock_type + ' SLAUGHTERED INSPECTION');
                $('[data-decision-icon]').attr('class', 'bi ' + (approved ? 'bi-check-circle' : 'bi-x-circle'));
                $('[data-decision-contact]').val(activeRecord.contact_number);
                $('[data-decision-email]').val(activeRecord.email || '');
                $('[data-decision-date]').val(activeRecord.scheduled_date);
                $('[data-decision-time]').val(activeRecord.scheduled_time);
                $('[data-decision-remarks-label]').text(approved ? 'Remarks:' : 'State the Reason of Request Denial:');
                decisionForm.classList.toggle('disapproved', !approved);
                decisionDialog.showModal();
            });

            $(decisionForm).on('submit', function (event) {
                event.preventDefault();
                $('[data-decision-schedule]').val($('[data-decision-date]').val() + ' ' + $('[data-decision-time]').val() + ':00');
                $.ajax({
                    url: decisionForm.action,
                    method: 'POST',
                    data: $(decisionForm).serialize(),
                    headers: { Accept: 'application/json' },
                    success: function (response) {
                        decisionDialog.close();
                        requestDialog.close();
                        table.ajax.reload(null, false);
                        einspectSuccess(response.message);
                    },
                    error: function (xhr) {
                        const errors = xhr.responseJSON?.errors;
                        Swal.fire({ title: 'Please Check the Form', text: errors ? Object.values(errors).flat().join(' ') : xhr.responseJSON?.message, icon: 'error', confirmButtonColor: '#760008' });
                    }
                });
            });

            $('#requestLength').on('change', function () { table.page.len(Number(this.value)).draw(); });
            $('#requestSearch').on('input', function () { table.search(this.value).draw(); });
            $('#requestFilter').on('click', function () {
                dateFrom = $('#requestFrom').val();
                dateTo = $('#requestTo').val();
                table.ajax.reload();
            });
            $('#requestReload').on('click', function () {
                dateFrom = '';
                dateTo = '';
                status = 'ALL';
                $('#requestFrom, #requestTo, #requestSearch').val('');
                $('[data-inspector-status]').removeClass('active');
                table.search('').ajax.reload();
            });
            $('[data-inspector-status]').on('click', function () {
                status = status === this.dataset.inspectorStatus ? 'ALL' : this.dataset.inspectorStatus;
                $('[data-inspector-status]').toggleClass('active', status !== 'ALL' && this.dataset.inspectorStatus === status);
                table.ajax.reload();
            });
        });
    </script>
@endpush
