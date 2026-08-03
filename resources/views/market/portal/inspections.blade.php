@extends('market.layouts.portal')

@section('content')
    @php
        $inspectionMonth = now()->startOfMonth();
        $livestockChoices = [
            'BEEF' => ['label' => 'BEEF', 'image' => 'Beef Section.png', 'class' => 'beef', 'icon' => 'bi bi-cow'],
            'POULTRY' => ['label' => 'POULTRY', 'image' => 'Poultry Section.png', 'class' => 'poultry', 'icon' => 'bi bi-egg-fried'],
            'PORK' => ['label' => 'PORK', 'image' => 'Pork Section.png', 'class' => 'pork', 'icon' => 'bi bi-piggy-bank-fill'],
        ];
    @endphp

    @if ($canRequest)
        <header class="tenant-request-page-heading">
            <i class="bi bi-person-workspace" aria-hidden="true"></i>
            <div>
                <h1>REQUEST</h1>
                <p>Dashboard | Request</p>
            </div>
        </header>
    @endif

    <section @class(['panel', 'm-3', 'p-3', 'tenant-request-panel' => $canRequest])>
        @unless ($canRequest)
            <div class="panel-heading">
                <div><span class="eyebrow">Poultry / Pork / Beef</span><h2>{{ $pageTitle }}</h2></div>
            </div>
        @endunless
        @if ($canRequest)
            <div class="tenant-request-toolbar">
                <select id="tenantRequestLength" aria-label="Rows per page"><option value="5">5 v</option><option value="10" selected>10 v</option><option value="25">25 v</option><option value="50">50 v</option></select>
                <label>From:<input type="date" id="tenantRequestDateFrom"></label>
                <label>To:<input type="date" id="tenantRequestDateTo"></label>
                <button type="button" id="filterTenantRequests"><i class="bi bi-funnel-fill"></i> Filter</button>
                <button type="button" id="reloadTenantRequests"><i class="bi bi-arrow-clockwise"></i> Reload</button>
                <label class="tenant-request-search">Search:<input type="search" id="tenantRequestSearch"></label>
                <span>Status:</span>
                <div class="tenant-request-statuses">
                    <button type="button" data-request-status="PENDING">Pending</button>
                    <button type="button" data-request-status="APPROVED">Approved</button>
                    <button type="button" data-request-status="DISAPPROVED">Disapproved</button>
                </div>
            </div>
        @endif
        <div class="table-wrap">
            <table id="{{ $canRequest ? 'tenantInspectionTable' : '' }}">
                @if ($canRequest)
                    <thead><tr><th>NO.</th><th>OWNER</th><th>ADDRESS</th><th>CONTACT NUMBER</th><th>TYPE OF SLAUGHTERED INSPECT</th><th>DATE AND TIME OF INSPECTION</th><th>REQUEST STATUS</th><th>ACTION</th></tr></thead>
                @else
                    <thead><tr><th>Request</th><th>Owner</th><th>Type</th><th>Animals</th><th>Schedule</th><th>Result</th><th>Status</th><th>Action</th></tr></thead>
                @endif
                <tbody>
                    @unless ($canRequest)
                        @forelse ($inspections as $inspection)
                            <tr>
                                <td>{{ $inspection->request_number }}</td>
                                <td><strong>{{ $inspection->owner_name }}</strong><br><small>{{ $inspection->contact_number }}</small></td>
                                <td>{{ $inspection->livestock_type }}</td>
                                <td>{{ $inspection->animal_count }}</td>
                                <td>{{ $inspection->scheduled_at->format('M d, Y g:i A') }}</td>
                                <td>{{ $inspection->inspection_result ?? '-' }}</td>
                                <td><span class="status status-{{ strtolower($inspection->status) }}">{{ $inspection->status }}</span></td>
                                <td>
                                    @if ($canReview)
                                        <button type="button" class="table-action" data-open-dialog="inspectionReview{{ $inspection->id }}" title="Review inspection"><i class="bi bi-pencil-square"></i></button>
                                    @elseif ($inspection->status === 'COMPLETED')
                                        <a href="{{ route('inspections.certificate', $inspection) }}" target="_blank" class="table-action" title="Certificate"><i class="bi bi-file-earmark-check"></i></a>
                                    @else
                                        <a href="{{ route('inspections.show', $inspection) }}" class="table-action" title="View"><i class="bi bi-eye-fill"></i></a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="empty-state">No inspection requests found.</td></tr>
                        @endforelse
                    @endunless
                </tbody>
            </table>
        </div>
    </section>

    @if ($canRequest)
        @foreach ($livestockChoices as $type => $choice)
            @php
                $typeInspections = $inspectionSchedule->get($type, collect());
                $reservationDetails = $typeInspections
                    ->groupBy(fn ($inspection) => $inspection->scheduled_at->format('Y-m'))
                    ->map(fn ($monthInspections) => $monthInspections
                        ->groupBy(fn ($inspection) => (string) $inspection->scheduled_at->day)
                        ->map(fn ($dayInspections) => $dayInspections->map(fn ($inspection) => [
                            'time' => $inspection->scheduled_at->format('g:i A'),
                            'request_number' => $inspection->request_number,
                            'owner_name' => $inspection->owner_name,
                            'status' => $inspection->status,
                        ])->values())
                    )
                    ->toArray();
                $calendarYears = collect(range(now()->year - 1, now()->year + 2))
                    ->merge($typeInspections->pluck('scheduled_at')->map->year)
                    ->unique()
                    ->sort()
                    ->values();
            @endphp
            <dialog id="tenant-inspection-{{ strtolower($type) }}" class="inspection-request-modal" data-inspection-dialog="{{ $type }}">
                <form action="{{ route('tenant.inspections.store') }}" method="POST" class="inspection-modal-card" data-tenant-inspection-form data-store-url="{{ route('tenant.inspections.store') }}" data-reservations='@json($reservationDetails)'>
                    @csrf
                    <button type="button" class="login-close" aria-label="Close" data-close-tenant-inspection><i class="bi bi-x-circle-fill"></i></button>
                    <span data-method-field></span>
                    <input type="hidden" name="livestock_type" value="{{ $type }}">
                    <input type="hidden" name="owner_name" data-owner-name value="{{ auth()->user()->full_name }}">
                    <input type="hidden" name="address" data-owner-address value="{{ auth()->user()->address }}">
                    <input type="hidden" name="scheduled_at" data-scheduled-at>
                    <input type="hidden" name="animal_count" value="1">

                    <div class="inspection-calendar-panel">
                        <div class="inspection-calendar-header">
                            <strong data-calendar-month-number>{{ $inspectionMonth->format('n') }}</strong>
                            <select data-calendar-month aria-label="Calendar month">
                                @foreach (range(1, 12) as $month)
                                    <option value="{{ $month }}" @selected($month === $inspectionMonth->month)>{{ strtoupper(\Carbon\Carbon::create()->month($month)->format('F')) }}</option>
                                @endforeach
                            </select>
                            <select data-calendar-year aria-label="Calendar year">
                                @foreach ($calendarYears as $year)
                                    <option value="{{ $year }}" @selected($year === $inspectionMonth->year)>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="inspection-calendar-weekdays">@foreach (['SUN','MON','TUE','WED','THU','FRI','SAT'] as $day)<span>{{ $day }}</span>@endforeach</div>
                        <div class="inspection-calendar-days" data-calendar-days></div>
                        <div class="reserved-note" data-reserved-note hidden><button type="button" aria-label="Close reserved time" data-close-reserved-note>&times;</button><strong>RESERVED TIME</strong><div data-reserved-details></div></div>
                        <div class="calendar-status-legend"><span><i class="available"></i>Available</span><span><i class="reserved"></i>Reserved / unavailable</span></div>
                        <div class="inspector-contact"><img src="{{ asset('assets/einspect/USERS/J-Inspector.png') }}" alt=""><strong>EDWIN C. GREGORIO</strong><span>Rural Sanitary Inspector I</span><i class="bi bi-telephone-fill"></i><span>09679050621</span><i class="bi bi-envelope-fill"></i><span>edwingregorio@gmail.com</span></div>
                    </div>

                    <div class="inspection-form-panel">
                        <div class="inspection-form-heading"><i class="{{ $choice['icon'] }}"></i><h1 data-inspection-form-title>REQUEST INSPECTION FORM</h1><p>({{ ucfirst(strtolower($type)) }} Slaughtered Livestock)</p></div>
                        <h2>REQUESTER INFORMATION:</h2>
                        <label>Complete Name:<b>*</b><span class="triple-input"><input name="first_name" value="{{ auth()->user()->firstname }}" required><input name="middle_name" value="{{ auth()->user()->middlename }}"><input name="last_name" value="{{ auth()->user()->lastname }}" required></span></label>
                        <label>Address:<b>*</b><span class="triple-input"><input name="barangay" placeholder="Barangay" required><input name="municipality" value="Pandan" required><input name="province" value="Antique" required></span></label>
                        <div class="form-grid two"><label>Date of Inspection:<b>*</b><span><input type="date" data-date-input required><i class="bi bi-calendar3"></i></span></label><label>Time of Inspection:<b>*</b><span><input type="time" data-time-input required><i class="bi bi-alarm"></i></span></label></div>
                        <label>Contact Number:<b>*</b><input name="contact_number" value="{{ auth()->user()->phone_num }}" required></label>
                        <button class="button button-primary" type="submit"><i class="bi bi-send-fill"></i> <span data-inspection-submit-label>Submit</span></button>
                    </div>
                </form>
            </dialog>
        @endforeach

    @endif
@endsection

@push('scripts')
    <script>
        @if ($canRequest)
        let tenantInspectionTable;
        let tenantRequestDateFrom = '';
        let tenantRequestDateTo = '';
        let tenantRequestStatus = 'ALL';

        $(document).ready(function () {
            tenantInspectionTable = $('#tenantInspectionTable').DataTable({
                processing: true,
                serverSide: true,
                dom: 'rtip',
                order: [],
                pageLength: 10,
                ajax: {
                    url: "{{ route('tenant.datatable.inspections') }}",
                    type: 'GET',
                    data: function (data) {
                        data.dateFrom = tenantRequestDateFrom;
                        data.dateTo = tenantRequestDateTo;
                        data.status = tenantRequestStatus;
                    }
                },
                columns: [
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    { data: 'owner_name', orderable: false },
                    { data: 'address', orderable: false },
                    { data: 'contact', orderable: false },
                    { data: 'type', orderable: false },
                    { data: 'schedule', orderable: false },
                    { data: 'status', orderable: false },
                    { data: 'action', orderable: false, searchable: false }
                ]
            });
        });

        $(document).on('change', '#tenantRequestLength', function () {
            tenantInspectionTable.page.len(Number($(this).val())).draw();
        });

        $(document).on('input', '#tenantRequestSearch', function () {
            tenantInspectionTable.search($(this).val()).draw();
        });

        $(document).on('click', '#filterTenantRequests', function () {
            tenantRequestDateFrom = $('#tenantRequestDateFrom').val();
            tenantRequestDateTo = $('#tenantRequestDateTo').val();
            tenantInspectionTable.ajax.reload();
        });

        $(document).on('click', '#reloadTenantRequests', function () {
            tenantRequestDateFrom = '';
            tenantRequestDateTo = '';
            tenantRequestStatus = 'ALL';
            $('#tenantRequestDateFrom, #tenantRequestDateTo, #tenantRequestSearch').val('');
            $('[data-request-status]').removeClass('active');
            tenantInspectionTable.search('').ajax.reload();
        });

        $(document).on('click', '[data-request-status]', function () {
            const selected = $(this).data('request-status');
            tenantRequestStatus = tenantRequestStatus === selected ? 'ALL' : selected;
            $('[data-request-status]').toggleClass('active', tenantRequestStatus !== 'ALL' && $(this).data('request-status') === tenantRequestStatus);
            tenantInspectionTable.ajax.reload();
        });

        function resetInspectionForm(form) {
            form.reset();
            form.action = form.dataset.storeUrl;
            form.querySelector('[data-method-field]').innerHTML = '';
            form.querySelector('[data-inspection-form-title]').textContent = 'REQUEST INSPECTION FORM';
            form.querySelector('[data-inspection-submit-label]').textContent = 'Submit';
            form.querySelector('[type="submit"]').hidden = false;
            form.querySelectorAll('input').forEach((input) => {
                input.readOnly = false;
            });
            form.querySelectorAll('[data-inspection-day]').forEach((button) => {
                button.disabled = false;
            });
            form.querySelectorAll('[data-inspection-day]').forEach((button) => button.classList.remove('selected'));
            form.querySelector('[data-reserved-note]')?.setAttribute('hidden', '');
            form.classList.remove('inspection-form-readonly');
            renderTenantCalendar(form);
        }

        function tenantCalendarKey(year, month) {
            return `${year}-${String(month).padStart(2, '0')}`;
        }

        function renderTenantCalendar(form) {
            const monthSelect = form.querySelector('[data-calendar-month]');
            const yearSelect = form.querySelector('[data-calendar-year]');
            const monthNumber = form.querySelector('[data-calendar-month-number]');
            const daysContainer = form.querySelector('[data-calendar-days]');
            if (!monthSelect || !yearSelect || !daysContainer) return;

            const month = Number(monthSelect.value);
            const year = Number(yearSelect.value);
            const reservations = JSON.parse(form.dataset.reservations || '{}');
            const monthReservations = reservations[tenantCalendarKey(year, month)] || {};
            const firstDay = new Date(year, month - 1, 1).getDay();
            const daysInMonth = new Date(year, month, 0).getDate();
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            monthNumber.textContent = month;
            daysContainer.innerHTML = '';

            for (let blank = 0; blank < firstDay; blank += 1) {
                const spacer = document.createElement('span');
                spacer.className = 'blank';
                daysContainer.appendChild(spacer);
            }

            for (let day = 1; day <= daysInMonth; day += 1) {
                const date = new Date(year, month - 1, day);
                const isReserved = Boolean(monthReservations[String(day)]);
                const isUnavailable = !isReserved && (date < today || date.getDay() === 0 || date.getDay() === 6);
                const button = document.createElement('button');
                button.type = 'button';
                button.dataset.inspectionDay = String(day);
                button.className = isReserved ? 'reserved' : (isUnavailable ? 'unavailable' : 'available');
                button.innerHTML = `<span>${day}</span>${isReserved ? '<small>RESERVED</small>' : (isUnavailable ? '<small>UNAVAILABLE</small>' : '')}`;
                daysContainer.appendChild(button);
            }
        }

        function openInspectionDialog(type, record = null, mode = 'create') {
            const categoryDialog = document.getElementById('tenantInspectionCategoryDialog');
            if (categoryDialog?.open) categoryDialog.close();

            const dialog = document.querySelector(`[data-inspection-dialog="${type}"]`);
            const form = dialog.querySelector('[data-tenant-inspection-form]');
            resetInspectionForm(form);

            if (record) {
                const nameParts = record.owner_name.trim().split(/\s+/);
                const firstName = nameParts.shift() || '';
                const lastName = nameParts.pop() || '';
                const addressParts = record.address.split(',').map((part) => part.trim());

                form.querySelector('[name="first_name"]').value = firstName;
                form.querySelector('[name="middle_name"]').value = nameParts.join(' ');
                form.querySelector('[name="last_name"]').value = lastName;
                form.querySelector('[name="barangay"]').value = addressParts[0] || '';
                form.querySelector('[name="municipality"]').value = addressParts.length >= 3 ? addressParts[1] : 'Pandan';
                form.querySelector('[name="province"]').value = addressParts.length >= 3
                    ? addressParts.slice(2).join(', ')
                    : (addressParts[1] || 'Antique');
                form.querySelector('[name="contact_number"]').value = record.contact_number;
                form.querySelector('[name="animal_count"]').value = record.animal_count;
                form.querySelector('[data-date-input]').value = record.scheduled_date;
                form.querySelector('[data-time-input]').value = record.scheduled_time;

                const [year, month] = record.scheduled_date.split('-').map(Number);
                form.querySelector('[data-calendar-month]').value = String(month);
                const yearSelect = form.querySelector('[data-calendar-year]');
                if (!yearSelect.querySelector(`option[value="${year}"]`)) {
                    yearSelect.add(new Option(String(year), String(year)));
                }
                yearSelect.value = String(year);
                renderTenantCalendar(form);
                const day = String(Number(record.scheduled_date.slice(-2)));
                form.querySelector(`[data-inspection-day="${day}"]`)?.classList.add('selected');

                if (mode === 'view') {
                    form.querySelector('[data-inspection-form-title]').textContent = 'VIEW INSPECTION REQUEST';
                    form.querySelector('[type="submit"]').hidden = true;
                    form.querySelectorAll('input').forEach((input) => {
                        input.readOnly = true;
                    });
                    form.querySelectorAll('[data-inspection-day]').forEach((button) => {
                        button.disabled = true;
                    });
                    form.classList.add('inspection-form-readonly');
                } else {
                    form.action = record.update_url;
                    form.querySelector('[data-method-field]').innerHTML = '<input type="hidden" name="_method" value="PUT">';
                    form.querySelector('[data-inspection-form-title]').textContent = 'EDIT INSPECTION REQUEST';
                    form.querySelector('[data-inspection-submit-label]').textContent = 'Save Changes';
                }
            }

            dialog.showModal();
        }

        $(document).on('click', '[data-open-inspection-request]', function () {
            openInspectionDialog($(this).data('open-inspection-request'));
        });

        $(document).on('click', '[data-close-inspection-dialog], [data-close-tenant-inspection]', function () {
            const dialog = $(this).closest('dialog').get(0);
            if (dialog?.open) dialog.close();
        });

        $(document).on('click', '.js-edit-inspection', function () {
            const record = JSON.parse($(this).attr('data-record'));
            openInspectionDialog(record.livestock_type, record, 'edit');
        });

        $(document).on('click', '.js-view-inspection', function () {
            const record = JSON.parse($(this).attr('data-record'));
            openInspectionDialog(record.livestock_type, record, 'view');
        });

        document.querySelectorAll('[data-tenant-inspection-form]').forEach((form) => {
            const dateInput = form.querySelector('[data-date-input]');
            const reservedNote = form.querySelector('[data-reserved-note]');
            const reservedDetails = form.querySelector('[data-reserved-details]');

            renderTenantCalendar(form);

            form.querySelectorAll('[data-calendar-month], [data-calendar-year]').forEach((select) => {
                select.addEventListener('change', () => {
                    dateInput.value = '';
                    reservedNote?.setAttribute('hidden', '');
                    renderTenantCalendar(form);
                });
            });

            form.querySelector('[data-calendar-days]').addEventListener('click', (event) => {
                const dayButton = event.target.closest('[data-inspection-day]');
                if (!dayButton || dayButton.disabled) return;

                const day = dayButton.dataset.inspectionDay;
                const month = Number(form.querySelector('[data-calendar-month]').value);
                const year = Number(form.querySelector('[data-calendar-year]').value);
                const reservations = JSON.parse(form.dataset.reservations || '{}');
                const details = reservations[tenantCalendarKey(year, month)]?.[day] || [];

                if (dayButton.classList.contains('reserved')) {
                    reservedDetails.innerHTML = details.length
                        ? details.map((inspection) => `<article><b>${inspection.time}</b><span>${inspection.request_number}</span><small>${inspection.owner_name} &middot; ${inspection.status}</small></article>`).join('')
                        : '<p>No reservation details found.</p>';
                    reservedNote.hidden = false;
                    return;
                }
                if (dayButton.classList.contains('unavailable')) return;

                reservedNote?.setAttribute('hidden', '');
                dateInput.value = `${year}-${String(month).padStart(2, '0')}-${day.padStart(2, '0')}`;
                form.querySelectorAll('[data-inspection-day]').forEach((button) => button.classList.toggle('selected', button === dayButton));
            });

            form.querySelector('[data-close-reserved-note]')?.addEventListener('click', () => reservedNote?.setAttribute('hidden', ''));
        });

        $(document).on('submit', '[data-tenant-inspection-form]', function (event) {
            event.preventDefault();
            const form = this;
            const firstName = form.querySelector('[name="first_name"]').value.trim();
            const middleName = form.querySelector('[name="middle_name"]').value.trim();
            const lastName = form.querySelector('[name="last_name"]').value.trim();
            const barangay = form.querySelector('[name="barangay"]').value.trim();
            const municipality = form.querySelector('[name="municipality"]').value.trim();
            const province = form.querySelector('[name="province"]').value.trim();
            const dateInput = form.querySelector('[data-date-input]');
            const timeInput = form.querySelector('[data-time-input]');

            form.querySelector('[data-owner-name]').value = [firstName, middleName, lastName].filter(Boolean).join(' ');
            form.querySelector('[data-owner-address]').value = [barangay, municipality, province].filter(Boolean).join(', ');
            form.querySelector('[data-scheduled-at]').value = `${dateInput.value} ${timeInput.value || '09:30'}:00`;

            const submitButton = $(form).find('[type="submit"]');
            submitButton.prop('disabled', true);

            $.ajax({
                url: form.action,
                type: 'POST',
                data: new FormData(form),
                processData: false,
                contentType: false,
                headers: { Accept: 'application/json' },
                success: function (response) {
                    form.closest('dialog').close();
                    tenantInspectionTable.ajax.reload(null, false);
                    einspectSuccess(response.message);
                },
                error: function (xhr) {
                    const errors = xhr.responseJSON?.errors;
                    const message = errors ? Object.values(errors).flat().join(' ') : (xhr.responseJSON?.message || 'The inspection request could not be saved.');
                    Swal.fire({ title: 'Please Check the Form', text: message, icon: 'error', confirmButtonColor: '#760008', customClass: { popup: 'einspect-swal' } });
                },
                complete: function () {
                    submitButton.prop('disabled', false);
                }
            });
        });

        $(document).on('click', '.js-delete-inspection', function () {
            const button = $(this);
            einspectConfirm({
                title: 'Delete Inspection Request?',
                message: `Remove ${button.data('reference')}?`,
                confirmText: 'Yes, Delete',
                cancelText: 'No, Keep It'
            }).then(function (result) {
                if (!result.isConfirmed) return;
                $.ajax({
                    url: button.data('url'),
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
                    headers: { Accept: 'application/json' },
                    success: function (response) {
                        tenantInspectionTable.ajax.reload(null, false);
                        einspectSuccess(response.message);
                    },
                    error: function (xhr) {
                        Swal.fire({ title: 'Unable to Delete', text: xhr.responseJSON?.message || 'The inspection request could not be deleted.', icon: 'error', confirmButtonColor: '#760008', customClass: { popup: 'einspect-swal' } });
                    }
                });
            });
        });
        @endif
    </script>
@endpush
