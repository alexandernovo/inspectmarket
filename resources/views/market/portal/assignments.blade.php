@extends('market.layouts.portal')

@section('content')
    @php
        $collections = $collections ?? collect();
        $assignments = $assignments ?? collect();
        $collectorRecords = ($collectorRecords ?? collect())->values();
        $showCollectorAccess = $showCollectorAccess ?? true;
        $showCollectors = $showCollectorAccess && request()->boolean('collectors');
        $cashTicketTitle = $cashTicketTitle ?? 'CASH TICKET';
        $cashTicketBreadcrumb = $cashTicketBreadcrumb ?? 'Dashboard | Cash Ticket';
        $cashTicketTitleAvatar = $cashTicketTitleAvatar ?? null;
        $collectorFrom = request('collector_from');
        $collectorTo = request('collector_to');
        $collectorPerPage = in_array((int) request('collector_per_page', 10), [5, 10, 25, 50], true) ? (int) request('collector_per_page', 10) : 10;
        $filteredCollectorRecords = $collectorRecords
            ->when($collectorFrom, fn ($rows) => $rows->filter(fn ($collector) => $collector->created_at && $collector->created_at->toDateString() >= $collectorFrom))
            ->when($collectorTo, fn ($rows) => $rows->filter(fn ($collector) => $collector->created_at && $collector->created_at->toDateString() <= $collectorTo))
            ->values();
        $visibleCollectorRecords = $filteredCollectorRecords->take($collectorPerPage);
        $selectedCalendarMonth = (int) request('collection_month', now()->month);
        $selectedCalendarYear = (int) request('collection_year', now()->year);
        $calendarMonth = \Carbon\Carbon::create($selectedCalendarYear, $selectedCalendarMonth, 1)->startOfMonth();
        $sections = [
            'FISH' => ['label' => 'Fish Section', 'image' => 'Fish Section.png', 'collector_image' => 'I-Clerk.png'],
            'POULTRY' => ['label' => 'Poultry Section', 'image' => 'Poultry Section.png', 'collector_image' => '3-Collector Clerk.png'],
            'PORK' => ['label' => 'Pork Section', 'image' => 'Pork Section.png', 'collector_image' => 'C-Clerk.png'],
            'BEEF' => ['label' => 'Beef Section', 'image' => 'Beef Section.png', 'collector_image' => '3-Collector Clerk.png'],
            'MIXED' => ['label' => 'Mixed Section', 'image' => 'Mixed Section.png', 'collector_image' => 'C-Clerk.png'],
        ];
        $workflowAssignments = $assignments->filter(fn ($item) => !str_starts_with($item->assignment_number ?? '', 'CTA-DEMO-')
            && $item->assigned_date
            && $item->assigned_date->isSameMonth($calendarMonth));
        $workflowCollections = $collections->filter(fn ($item) => !str_starts_with($item->collection_number ?? '', 'CT-DEMO-')
            && $item->collection_date
            && $item->collection_date->isSameMonth($calendarMonth));
        $requestedAssignments = $workflowAssignments->where('status', 'REQUESTED');
        $assignedTickets = $workflowAssignments->whereIn('status', ['ASSIGNED', 'COMPLETED']);
        $reportedCollections = $workflowCollections->whereIn('status', ['REPORTED', 'SUBMITTED']);
        $submittedCollections = $workflowCollections->where('status', 'SUBMITTED');
        $step3Done = $assignedTickets->where('status', 'COMPLETED')->isNotEmpty() || $workflowCollections->whereIn('status', ['RECORDED', 'REPORTED', 'SUBMITTED'])->isNotEmpty();
        $sectionTotals = $workflowCollections
            ->groupBy('stall_section')
            ->map(fn ($rows) => [
                'tickets' => (int) $rows->sum('ticket_quantity'),
                'amount' => (float) $rows->sum('amount'),
                'shortage' => (float) $rows->sum('shortage_amount'),
            ]);
        $collectionRowsByDateSection = $collections
            ->filter(fn ($item) => !str_starts_with($item->collection_number ?? '', 'CT-DEMO-') && $item->collection_date)
            ->groupBy(fn ($item) => $item->collection_date->toDateString())
            ->map(fn ($items) => $items->sortByDesc('id')->groupBy('stall_section')->map(function ($sectionItems) {
                $latestCollection = $sectionItems->first();

                return [
                    'amount' => (float) $latestCollection->amount,
                    'tickets' => (int) $latestCollection->ticket_quantity,
                    'collector_id' => $latestCollection->collector_id,
                ];
            }));
        $collectionTotalsByDate = $collectionRowsByDateSection
            ->map(fn ($rows) => [
                'amount' => (float) $rows->sum('amount'),
                'tickets' => (int) $rows->sum('tickets'),
                'collectors' => $rows->pluck('collector_id')->filter()->unique()->count(),
            ]);
        $reportDates = collect();
        $reportCursor = $calendarMonth->copy();
        while ($reportCursor->isSameMonth($calendarMonth)) {
            if ($reportCursor->isWeekday()) {
                $reportDates->push($reportCursor->copy());
            }
            $reportCursor->addDay();
        }
        $reportWeeks = $reportDates->chunk(5)->values();
        $reportNumber = 'RCC-'.now()->format('Ymd');
        $slipRowCount = 8;
        $slipAssignments = $workflowAssignments->sortByDesc('id')->take($slipRowCount)->reverse()->values();
        $slipDate = optional($slipAssignments->first()?->assigned_date)->toDateString() ?: now()->toDateString();
        $readSlipRemark = function ($remarks, $label) {
            if (! $remarks || ! preg_match('/(?:^|\|\s*)'.preg_quote($label, '/').':\s*([^|]+)/', $remarks, $matches)) {
                return '';
            }

            return trim($matches[1]);
        };
        $readSlipUserRemark = function ($remarks) {
            if (! $remarks) {
                return '';
            }

            if (preg_match('/(?:^|\|\s*)Remarks:\s*([^|]+)/', $remarks, $matches)) {
                return trim($matches[1]);
            }

            return collect(preg_split('/\s*\|\s*/', $remarks))
                ->map(fn ($part) => trim($part))
                ->filter()
                ->reject(fn ($part) => collect(['RCC II:', 'Unit:', 'Description:', 'Stub:', 'Pcs:'])->contains(fn ($prefix) => str_starts_with($part, $prefix)))
                ->last() ?: '';
        };
        $workflowSteps = [
            ['label' => 'Request Requisition and Issue Slip', 'icon' => 'bi-card-checklist', 'done' => $requestedAssignments->isNotEmpty() || $assignedTickets->isNotEmpty(), 'dialog' => 'treasurerCashStep1'],
            ['label' => 'Assign and Distribute Cash Tickets', 'icon' => 'bi-people-fill', 'done' => $assignedTickets->isNotEmpty(), 'dialog' => 'treasurerCashStep2'],
            ['label' => 'Collect and Record Fees', 'icon' => 'bi-cash-stack', 'done' => $step3Done, 'dialog' => 'treasurerCashStep3'],
            ['label' => 'Create Collection Report', 'icon' => 'bi-pencil-fill', 'done' => $reportedCollections->isNotEmpty(), 'dialog' => 'treasurerCashStep4'],
            ['label' => 'Submit to Treasurer', 'icon' => 'bi-person-fill', 'done' => $submittedCollections->isNotEmpty(), 'dialog' => 'treasurerCashStep5'],
        ];
    @endphp

    @include('market.tenant.applications.css.header')
    <header class="tenant-page-title treasurer-cash-page-title px-3 pt-3 pb-0">
        <div>
            @if ($cashTicketTitleAvatar)
                <img class="administrator-role-title-avatar" src="{{ asset('assets/einspect/USERS/'.$cashTicketTitleAvatar) }}" alt="">
            @else
                <i class="bi bi-card-checklist"></i>
            @endif
            <div>
                <h1>{{ $cashTicketTitle }}</h1>
                <p class="mb-0">{{ $cashTicketBreadcrumb }}</p>
            </div>
        </div>
        @if ($showCollectorAccess && ! $showCollectors)
            <a class="treasurer-cash-collectors-button" href="{{ route('treasurer.assignments', ['collectors' => 1]) }}">
                <i class="bi bi-people-fill"></i>
                <span>Collectors</span>
            </a>
        @endif
    </header>

    @if ($showCollectors)
        <section class="treasurer-cash-collectors-shell">
            <form method="GET" action="{{ route('treasurer.assignments') }}" class="treasurer-cash-collectors-toolbar">
                <input type="hidden" name="collectors" value="1">
                <div class="collector-toolbar-filters">
                    <select name="collector_per_page">
                        @foreach ([5, 10, 25, 50] as $option)
                            <option value="{{ $option }}" @selected($collectorPerPage === $option)>{{ $option }} v</option>
                        @endforeach
                    </select>
                    <label>From:<input type="date" name="collector_from" value="{{ $collectorFrom }}"></label>
                    <label>To:<input type="date" name="collector_to" value="{{ $collectorTo }}"></label>
                    <button type="submit">Filter</button>
                </div>
                <div class="collector-toolbar-title">
                    <i class="bi bi-people-fill"></i>
                    <span>CASH TICKET COLLECTORS</span>
                </div>
                <div class="collector-toolbar-actions">
                    <a href="{{ route('treasurer.assignments', ['collectors' => 1]) }}"><i class="bi bi-arrow-clockwise"></i> Reload</a>
                    <button type="button" data-open-dialog="treasurerAddCollector"><i class="bi bi-plus-circle"></i> Add Collector</button>
                </div>
            </form>
            <div class="treasurer-cash-collectors-table-wrap">
                <table class="treasurer-cash-collectors-table">
                    <thead>
                        <tr>
                            <th>NO.</th>
                            <th>COLLECTOR ID</th>
                            <th>FULL NAME</th>
                            <th>CONTACT NUMBER</th>
                            <th>STATUS</th>
                            <th>DATE HIRED</th>
                            <th>ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($visibleCollectorRecords as $collector)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>COL-{{ str_pad($collector->id, 4, '0', STR_PAD_LEFT) }}</td>
                                <td class="collector-name-cell">
                                    <span>
                                        <img src="{{ market_role_avatar($collector->usertype, $collector->profile) }}" alt="">
                                        <b>{{ $collector->full_name }}</b>
                                    </span>
                                </td>
                                <td>{{ $collector->phone_num }}</td>
                                <td><span class="collector-status-pill {{ strtolower($collector->status) }}">{{ str($collector->status)->title() }}</span></td>
                                <td>{{ $collector->created_at?->format('F d, Y') }}</td>
                                <td>
                                    <div class="collector-action-stack">
                                        <button type="button" class="view" title="View" data-open-dialog="treasurerViewCollector{{ $collector->id }}"><i class="bi bi-eye-fill"></i></button>
                                        <button type="button" class="edit" title="Edit" data-open-dialog="treasurerEditCollector{{ $collector->id }}"><i class="bi bi-pencil-fill"></i></button>
                                        <form method="POST" action="{{ route('treasurer.collectors.destroy', $collector) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="delete" title="Delete" data-confirm-delete><i class="bi bi-trash-fill"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7">No collectors found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="treasurer-cash-collectors-footer">
                <span>Showing 1 to {{ $visibleCollectorRecords->count() }} of {{ $filteredCollectorRecords->count() }} entries</span>
                <div><a href="{{ route('treasurer.assignments', ['collectors' => 1]) }}">&lt;</a><b>1</b><a href="{{ route('treasurer.assignments', ['collectors' => 1]) }}">&gt;</a></div>
            </div>
        </section>

        <dialog id="treasurerAddCollector" class="market-dialog treasurer-add-collector-dialog">
            <form method="POST" action="{{ route('treasurer.collectors.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="treasurer-add-collector-heading">
                    <button type="button" data-close-dialog><i class="bi bi-arrow-left-circle-fill"></i></button>
                    <i class="bi bi-person-fill"></i>
                    <div><h2>ADD COLLECTOR</h2><p>CASH TICKET COLLECTION</p></div>
                    <button type="button" data-close-dialog><i class="bi bi-x-circle"></i></button>
                </div>
                <div class="treasurer-add-collector-body">
                    <section class="collector-form-panel">
                        <h3><i class="bi bi-clipboard2-check"></i> COLLECTOR INFORMATION</h3>
                        <div class="collector-form-grid">
                            <label>Full Name:<input name="full_name" value="{{ old('full_name') }}" placeholder="Enter full name" required></label>
                            <label>Collector ID:<input value="Auto-generated" readonly></label>
                            <label>Date of Birth:<input type="date" name="birth_date" value="{{ old('birth_date') }}"></label>
                            <label>Sex:<select name="sex"><option value="">Select gender</option><option>MALE</option><option>FEMALE</option></select></label>
                            <label>Contact Number:<input name="phone_num" value="{{ old('phone_num') }}" placeholder="09xxxxxxxxx" required></label>
                            <label>Email Address:<input type="email" name="email" value="{{ old('email') }}" placeholder="Enter email (optional)"></label>
                            <label>Date Hired:<input type="date" name="date_hired" value="{{ old('date_hired', now()->toDateString()) }}"></label>
                            <label>Status:<select name="status" required><option value="">Select status</option><option value="ACTIVE">Active</option><option value="INACTIVE">Inactive</option></select></label>
                            <label class="collector-address-field">Address:<input name="address" value="{{ old('address') }}" placeholder="Enter complete address" required></label>
                            <input type="hidden" name="designation" value="Revenue Collector Clerk">
                        </div>
                    </section>
                    <section class="collector-profile-panel">
                        <h3><i class="bi bi-eye-fill"></i> COLLECTOR PROFILE</h3>
                        <div class="collector-profile-preview">
                            <img src="{{ asset('assets/einspect/USERS/3-Collector Clerk.png') }}" alt="" data-collector-photo-preview>
                        </div>
                        <label class="collector-upload-button">
                            <i class="bi bi-upload"></i>
                            <span>Upload Photo</span>
                            <input type="file" name="profile_image" accept="image/*" hidden data-collector-photo-input>
                        </label>
                        <small class="collector-upload-file" data-collector-photo-file></small>
                    </section>
                </div>
                <footer class="dialog-actions treasurer-add-collector-actions">
                    <button type="submit" class="button button-primary">Save</button>
                    <button type="button" class="button button-muted" data-close-dialog>Cancel</button>
                </footer>
            </form>
        </dialog>

        @foreach ($visibleCollectorRecords as $collector)
            <dialog id="treasurerViewCollector{{ $collector->id }}" class="market-dialog treasurer-add-collector-dialog treasurer-view-collector-dialog">
                <section>
                    <div class="treasurer-add-collector-heading">
                        <button type="button" data-close-dialog><i class="bi bi-arrow-left-circle-fill"></i></button>
                        <i class="bi bi-person-vcard-fill"></i>
                        <div><h2>COLLECTOR</h2><p>CASH TICKET COLLECTION</p></div>
                        <button type="button" data-close-dialog><i class="bi bi-x-circle"></i></button>
                    </div>
                    <div class="treasurer-add-collector-body">
                        <section class="collector-form-panel">
                            <h3><i class="bi bi-clipboard2-check"></i> COLLECTOR INFORMATION</h3>
                            <div class="collector-form-grid">
                                <label>Full Name:<input value="{{ $collector->full_name }}" readonly></label>
                                <label>Collector ID:<input value="COL-{{ str_pad($collector->id, 4, '0', STR_PAD_LEFT) }}" readonly></label>
                                <label>Contact Number:<input value="{{ $collector->phone_num }}" readonly></label>
                                <label>Email Address:<input value="{{ $collector->email }}" readonly></label>
                                <label>Date Hired:<input value="{{ $collector->created_at?->format('m/d/Y') }}" readonly></label>
                                <label>Status:<input value="{{ str($collector->status)->title() }}" readonly></label>
                                <label class="collector-address-field">Address:<input value="{{ $collector->address }}" readonly></label>
                                <label class="collector-address-field">Designation:<input value="{{ $collector->designation }}" readonly></label>
                            </div>
                        </section>
                        <section class="collector-profile-panel">
                            <h3><i class="bi bi-eye-fill"></i> COLLECTOR PROFILE</h3>
                            <div class="collector-profile-preview">
                                <img src="{{ market_role_avatar($collector->usertype, $collector->profile) }}" alt="">
                            </div>
                        </section>
                    </div>
                    <footer class="dialog-actions treasurer-add-collector-actions">
                        <button type="button" class="button button-muted" data-close-dialog>Cancel</button>
                    </footer>
                </section>
            </dialog>

            <dialog id="treasurerEditCollector{{ $collector->id }}" class="market-dialog treasurer-add-collector-dialog">
                <form method="POST" action="{{ route('treasurer.collectors.update', $collector) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="treasurer-add-collector-heading">
                        <button type="button" data-close-dialog><i class="bi bi-arrow-left-circle-fill"></i></button>
                        <i class="bi bi-person-fill"></i>
                        <div><h2>EDIT COLLECTOR</h2><p>CASH TICKET COLLECTION</p></div>
                        <button type="button" data-close-dialog><i class="bi bi-x-circle"></i></button>
                    </div>
                    <div class="treasurer-add-collector-body">
                        <section class="collector-form-panel">
                            <h3><i class="bi bi-clipboard2-check"></i> COLLECTOR INFORMATION</h3>
                            <div class="collector-form-grid">
                                <label>First Name:<input name="firstname" value="{{ old('firstname', $collector->firstname) }}" required></label>
                                <label>Collector ID:<input value="COL-{{ str_pad($collector->id, 4, '0', STR_PAD_LEFT) }}" readonly></label>
                                <label>Middle Name:<input name="middlename" value="{{ old('middlename', $collector->middlename) }}"></label>
                                <label>Last Name:<input name="lastname" value="{{ old('lastname', $collector->lastname) }}" required></label>
                                <label>Contact Number:<input name="phone_num" value="{{ old('phone_num', $collector->phone_num) }}" required></label>
                                <label>Email Address:<input type="email" name="email" value="{{ old('email', $collector->email) }}"></label>
                                <label>Date Hired:<input value="{{ $collector->created_at?->format('Y-m-d') }}" readonly></label>
                                <label>Status:<select name="status" required><option value="ACTIVE" @selected($collector->status === 'ACTIVE')>Active</option><option value="INACTIVE" @selected($collector->status === 'INACTIVE')>Inactive</option></select></label>
                                <label class="collector-address-field">Address:<input name="address" value="{{ old('address', $collector->address) }}" required></label>
                                <input type="hidden" name="designation" value="{{ $collector->designation ?: 'Revenue Collector Clerk' }}">
                            </div>
                        </section>
                        <section class="collector-profile-panel">
                            <h3><i class="bi bi-eye-fill"></i> COLLECTOR PROFILE</h3>
                            <div class="collector-profile-preview">
                                <img src="{{ market_role_avatar($collector->usertype, $collector->profile) }}" alt="" data-collector-photo-preview="collector{{ $collector->id }}">
                            </div>
                            <label class="collector-upload-button">
                                <i class="bi bi-upload"></i>
                                <span>Upload Photo</span>
                                <input type="file" name="profile_image" accept="image/*" hidden data-collector-photo-input="collector{{ $collector->id }}">
                            </label>
                            <small class="collector-upload-file" data-collector-photo-file="collector{{ $collector->id }}"></small>
                        </section>
                    </div>
                    <footer class="dialog-actions treasurer-add-collector-actions">
                        <button type="submit" class="button button-primary">Save</button>
                        <button type="button" class="button button-muted" data-close-dialog>Cancel</button>
                    </footer>
                </form>
            </dialog>
        @endforeach
    @else
    <section class="cash-ticket-workflow-shell treasurer-cash-workflow">
        <div class="workflow-title">
            <i class="bi bi-ticket-perforated-fill"></i>
            <div><h2>CASH TICKET</h2><span>PUBLIC MARKET PANDAN, ANTIQUE</span></div>
        </div>
        <div class="workflow-steps">
            @foreach ($workflowSteps as $index => $step)
                <div class="workflow-step-item {{ $step['done'] ? 'complete' : '' }}" style="--step-line-color: {{ $step['done'] ? '#075d16' : '#760008' }};">
                    <button type="button" class="workflow-step-card {{ $step['done'] ? 'complete' : '' }}" data-open-dialog="{{ $step['dialog'] }}">
                        <b>{{ $index + 1 }}</b>
                        <i class="bi {{ $step['icon'] }}"></i>
                        <span>{{ $step['label'] }}</span>
                        @if ($step['done'])
                            <em>View Request</em>
                        @endif
                    </button>
                </div>
            @endforeach
        </div>
    </section>

    <dialog id="treasurerCashStep1" class="market-dialog cash-ticket-step-dialog cash-ticket-slip-dialog treasurer-cash-readonly-dialog">
        <section>
            <div class="cash-ticket-dialog-heading">
                <i class="bi bi-card-checklist"></i>
                <div><h2>CASH TICKET</h2><p>REQUISITION AND ISSUE SLIP</p></div>
                <button type="button" data-close-dialog><i class="bi bi-x-circle"></i></button>
            </div>
            <table class="cash-slip-table">
                <thead>
                    <tr class="cash-slip-meta-row">
                        <th colspan="2">Office: <input value="Municipal Treasurer's Office" readonly></th>
                        <th>RIS No. <input value="{{ $slipAssignments->first()?->assignment_number ?: $workflowAssignments->first()?->assignment_number ?: 'RIS-'.now()->format('Ymd') }}" readonly></th>
                        <th colspan="3">Date: <input type="date" value="{{ $slipDate }}" readonly></th>
                    </tr>
                    <tr>
                        <th colspan="2">Requisition</th>
                        <th colspan="4">Issuance</th>
                    </tr>
                    <tr>
                        <th>Unit</th>
                        <th>Description</th>
                        <th colspan="2">Quantity</th>
                        <th colspan="2">Serial No.</th>
                    </tr>
                    <tr>
                        <th></th>
                        <th></th>
                        <th>Stub</th>
                        <th>Pcs</th>
                        <th>From</th>
                        <th>To</th>
                    </tr>
                </thead>
                <tbody>
                    @for ($slipRow = 0; $slipRow < $slipRowCount; $slipRow++)
                        @php
                            $savedSlip = $slipAssignments->get($slipRow);
                            $savedRemarks = $savedSlip?->remarks;
                        @endphp
                        <tr>
                            <td><input value="{{ $readSlipRemark($savedRemarks, 'Unit') ?: ($savedSlip ? 'Cash Ticket' : '') }}" readonly></td>
                            <td><input value="{{ $readSlipRemark($savedRemarks, 'Description') ?: ($savedSlip ? 'Cash Ticket for Public Market Collection' : '') }}" readonly></td>
                            <td><input value="{{ $readSlipRemark($savedRemarks, 'Stub') ?: ($savedSlip ? '1' : '') }}" readonly></td>
                            <td><input value="{{ $readSlipRemark($savedRemarks, 'Pcs') ?: ($savedSlip?->ticket_quantity ?: '') }}" readonly></td>
                            <td><input value="{{ $savedSlip?->ticket_start ?: '' }}" readonly></td>
                            <td><input value="{{ $savedSlip?->ticket_end ?: '' }}" readonly></td>
                        </tr>
                    @endfor
                    <tr>
                        <td><strong>Purpose:</strong></td>
                        <td colspan="5"><input value="For use of the Office of the Municipal Treasurer" readonly></td>
                    </tr>
                    <tr class="cash-slip-signature-row">
                        <td rowspan="4" class="signature-labels">Signature:<br>Printed Name:<br>Designation:<br>Date:</td>
                        <td>Requested by:</td>
                        <td>Approved by:</td>
                        <td colspan="2">Issued by:</td>
                        <td>Received by:</td>
                    </tr>
                    <tr class="cash-slip-signature-row">
                        <td>{{ strtoupper($workflowAssignments->first()?->collector?->full_name ?? 'RCC II') }}</td>
                        <td>EDSEL J. AMBUIBUYOG</td>
                        <td colspan="2">EDSEL J. AMBUIBUYOG</td>
                        <td>{{ strtoupper($workflowAssignments->first()?->collector?->full_name ?? 'RCC II') }}</td>
                    </tr>
                    <tr class="cash-slip-signature-row">
                        <td>RCC II</td>
                        <td>Acting Municipal Treasurer</td>
                        <td colspan="2">Acting Municipal Treasurer</td>
                        <td>RCC II</td>
                    </tr>
                    <tr class="cash-slip-signature-row">
                        <td>{{ \Carbon\Carbon::parse($slipDate)->format('m/d/Y') }}</td>
                        <td></td>
                        <td colspan="2"></td>
                        <td>{{ \Carbon\Carbon::parse($slipDate)->format('m/d/Y') }}</td>
                    </tr>
                </tbody>
            </table>
            <div class="cash-slip-remarks"><label>Remarks:<input value="{{ $readSlipUserRemark($workflowAssignments->first()?->remarks) ?: 'No remarks' }}" readonly></label></div>
            <footer class="dialog-actions cash-ticket-sticky-actions"><button type="button" class="button button-muted" data-close-dialog>Close</button></footer>
        </section>
    </dialog>

    <dialog id="treasurerCashStep2" class="market-dialog cash-ticket-step-dialog treasurer-cash-readonly-dialog">
        <section>
            <div class="cash-ticket-dialog-heading">
                <i class="bi bi-people-fill"></i>
                <div><h2>CASH TICKET</h2><p>ASSIGN COLLECTORS</p></div>
                <button type="button" data-close-dialog><i class="bi bi-x-circle"></i></button>
            </div>
            <div class="cash-assign-meta"><strong>RCC II:</strong><span>{{ strtoupper($assignedTickets->first()?->collector?->full_name ?? auth()->user()->full_name) }}</span><input type="date" value="{{ optional($assignedTickets->first()?->assigned_date)->toDateString() ?: now()->toDateString() }}" readonly></div>
            <table class="cash-assign-table">
                <thead>
                    <tr>
                        <th colspan="2">STALL SECTION</th>
                        <th colspan="3">ASSIGN COLLECTOR/S</th>
                        <th colspan="2">NO. OF TICKETS DISTRIBUTED</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sections as $section => $details)
                        @php
                            $sectionAssignments = $assignedTickets->where('stall_section', $section)->values();
                            $collectorNames = $sectionAssignments->map(fn ($assignment) => $assignment->collector?->full_name)->filter()->values();
                            $rowQuantity = $sectionAssignments->sum('ticket_quantity');
                        @endphp
                        <tr>
                            <td><img class="section-image" src="{{ asset('assets/einspect/HOMEPAGE/'.$details['image']) }}" alt=""></td>
                            <td><select disabled><option>{{ $details['label'] }}</option></select></td>
                            <td><img class="collector-image" src="{{ asset('assets/einspect/USERS/'.$details['collector_image']) }}" alt=""></td>
                            <td><input type="number" value="{{ $collectorNames->count() }}" readonly></td>
                            <td><input value="{{ $collectorNames->isNotEmpty() ? $collectorNames->join(', ', ' & ') : 'No collector assigned' }}" readonly></td>
                            <td><i class="bi bi-ticket-perforated-fill cash-ticket-sample"></i></td>
                            <td><input type="number" value="{{ $rowQuantity ?: 0 }}" readonly></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <footer class="dialog-actions cash-ticket-sticky-actions"><button type="button" class="button button-muted" data-close-dialog>Close</button></footer>
        </section>
    </dialog>

    <dialog id="treasurerCashStep3" class="market-dialog cash-ticket-step-dialog cash-ticket-collection-dialog treasurer-cash-readonly-dialog">
        <section>
            <div class="cash-ticket-dialog-heading">
                <i class="bi bi-cash-stack"></i>
                <div><h2>CASH TICKET</h2><p>COLLECTION OF FEES</p></div>
                <button type="button" data-close-dialog><i class="bi bi-x-circle"></i></button>
            </div>
            <div class="cash-collection-meta">
                <div><strong>RCC II:</strong><span>{{ strtoupper($assignedTickets->first()?->collector?->full_name ?? auth()->user()->full_name) }}</span></div>
                <div class="cash-selected-month"><strong>MONTH:</strong><span>{{ strtoupper($calendarMonth->format('F Y')) }}</span></div>
            </div>
            <div class="cash-collection-wireframe">
                <table class="cash-collection-table">
                    <thead>
                        <tr>
                            <th colspan="2"><i class="bi bi-shop-window"></i><span>STALL SECTION</span></th>
                            <th colspan="3"><i class="bi bi-people-fill"></i><span>ASSIGN<br>COLLECTOR/S</span></th>
                            <th colspan="2"><i class="bi bi-ticket-perforated-fill"></i><span>NO. OF TICKETS<br>DISTRIBUTED</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sections as $section => $details)
                            @php
                                $sectionAssignments = $assignedTickets->where('stall_section', $section)->values();
                                $collectorNames = $sectionAssignments->map(fn ($assignment) => $assignment->collector?->firstname ?: $assignment->collector?->full_name)->filter()->values();
                                $rowQuantity = $sectionAssignments->sum('ticket_quantity');
                            @endphp
                            <tr>
                                <td><img class="section-image" src="{{ asset('assets/einspect/HOMEPAGE/'.$details['image']) }}" alt=""></td>
                                <td>{{ $details['label'] }}</td>
                                <td><img class="collector-image" src="{{ asset('assets/einspect/USERS/'.$details['collector_image']) }}" alt=""></td>
                                <td>{{ $collectorNames->count() }}</td>
                                <td>{{ $collectorNames->isNotEmpty() ? $collectorNames->join(', ', ' & ') : 'No collector assigned' }}</td>
                                <td><i class="bi bi-ticket-perforated-fill cash-ticket-sample"></i></td>
                                <td><input type="number" value="{{ $rowQuantity ?: 0 }}" readonly></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="cash-fee-calendar">
                    <header>
                        <select name="collection_month" data-treasurer-collection-month>
                            @foreach (range(1, 12) as $month)
                                <option value="{{ $month }}" @selected($month === $selectedCalendarMonth)>{{ strtoupper(\Carbon\Carbon::create(2000, $month, 1)->format('F')) }}</option>
                            @endforeach
                        </select>
                        <strong>{{ strtoupper($calendarMonth->format('F')) }}</strong>
                        <select name="collection_year" data-treasurer-collection-year>
                            @foreach (range(now()->year - 1, now()->year + 1) as $year)
                                <option value="{{ $year }}" @selected($year === $selectedCalendarYear)>{{ $year }}</option>
                            @endforeach
                        </select>
                    </header>
                    <div class="cash-fee-weekdays">
                        @foreach (['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'] as $weekday)
                            <span>{{ $weekday }}</span>
                        @endforeach
                    </div>
                    <div class="cash-fee-days">
                        @for ($blank = 0; $blank < $calendarMonth->dayOfWeek; $blank++)
                            <button type="button" class="blank" disabled></button>
                        @endfor
                        @for ($day = 1; $day <= $calendarMonth->daysInMonth; $day++)
                            @php $dayKey = $calendarMonth->copy()->day($day)->toDateString(); @endphp
                            <button type="button" class="{{ data_get($collectionTotalsByDate, $dayKey.'.amount', 0) > 0 ? 'collected' : '' }}" data-treasurer-collection-day="{{ $day }}">{{ $day }}</button>
                        @endfor
                    </div>
                    <div class="cash-date-total">
                        <span data-treasurer-total-date>{{ $calendarMonth->format('F d, Y') }}</span>
                        <strong data-treasurer-total-amount>PHP {{ number_format((float) $workflowCollections->sum('amount'), 2) }}</strong>
                        <small><b data-treasurer-total-tickets>{{ number_format((int) $workflowCollections->sum('ticket_quantity')) }}</b> tickets collected by <b data-treasurer-total-collectors>{{ $workflowCollections->pluck('collector_id')->filter()->unique()->count() }}</b> collector/s</small>
                    </div>
                </div>
            </div>
            <footer class="dialog-actions cash-ticket-sticky-actions"><button type="button" class="button button-muted" data-close-dialog>Close</button></footer>
        </section>
    </dialog>

    <dialog id="treasurerCashTicketDateCollection" class="market-dialog cash-ticket-step-dialog cash-ticket-date-dialog treasurer-cash-readonly-dialog">
        <section>
            <div class="cash-date-dialog-heading">
                <button type="button" data-return-treasurer-step3><i class="bi bi-arrow-left-circle-fill"></i></button>
                <i class="bi bi-calendar3"></i>
                <div><h2 data-treasurer-date-detail-title>{{ strtoupper($calendarMonth->format('F d, Y')) }}</h2><p data-treasurer-date-detail-weekday>{{ strtoupper($calendarMonth->format('l')) }}</p></div>
                <button type="button" data-return-treasurer-step3><i class="bi bi-x-circle"></i></button>
            </div>
            <table class="cash-date-table">
                <thead>
                    <tr><th colspan="2">STALL SECTION</th><th colspan="3">ASSIGN COLLECTOR</th><th colspan="2">NO. OF CASH TICKETS</th><th>TOTAL COLLECTED</th></tr>
                </thead>
                <tbody>
                    @foreach ($sections as $section => $details)
                        @php
                            $sectionAssignments = $assignedTickets->where('stall_section', $section)->values();
                            $collectorNames = $sectionAssignments->map(fn ($assignment) => $assignment->collector?->firstname ?: $assignment->collector?->full_name)->filter()->values();
                            $rowQuantity = $sectionAssignments->sum('ticket_quantity');
                        @endphp
                        <tr data-treasurer-date-section="{{ $section }}">
                            <td><img class="section-image" src="{{ asset('assets/einspect/HOMEPAGE/'.$details['image']) }}" alt=""></td>
                            <td>{{ $details['label'] }}</td>
                            <td><img class="collector-image" src="{{ asset('assets/einspect/USERS/'.$details['collector_image']) }}" alt=""></td>
                            <td>{{ $collectorNames->count() }}</td>
                            <td>{{ $collectorNames->isNotEmpty() ? $collectorNames->join(', ', ' & ') : 'No collector assigned' }}</td>
                            <td><i class="bi bi-ticket-perforated-fill cash-ticket-sample"></i></td>
                            <td><input type="number" value="{{ $rowQuantity ?: 0 }}" readonly data-treasurer-date-ticket-quantity></td>
                            <td><input type="number" value="0.00" readonly data-treasurer-date-row-amount></td>
                        </tr>
                    @endforeach
                    <tr class="cash-date-total-row">
                        <td colspan="7">TOTAL FEE:</td>
                        <td><span>&#8369;</span><strong data-treasurer-date-detail-total>0.00</strong></td>
                    </tr>
                </tbody>
            </table>
            <div class="dialog-actions cash-ticket-sticky-actions"><button type="button" class="button button-muted" data-return-treasurer-step3>Close</button></div>
        </section>
    </dialog>

    <dialog id="treasurerCashStep4" class="market-dialog cash-ticket-step-dialog cash-ticket-report-dialog treasurer-cash-readonly-dialog">
        <section>
            <div class="cash-report-heading">
                <button type="button" data-close-dialog><i class="bi bi-arrow-left-circle-fill"></i></button>
                <i class="bi bi-file-earmark-text"></i>
                <div><h2>CASH TICKET</h2><p>REPORT OF COLLECTION</p></div>
                <button type="button" data-close-dialog><i class="bi bi-x-circle"></i></button>
            </div>
            <div class="cash-report-body">
                <table class="cash-report-meta">
                    <tbody>
                        <tr>
                            <th>REPORT NO.</th>
                            <td>{{ $reportNumber }}</td>
                            <th>DATE:</th>
                            <td>{{ now()->format('F d, Y') }}</td>
                        </tr>
                        <tr>
                            <th>RCC II.</th>
                            <td>{{ strtoupper($assignedTickets->first()?->collector?->full_name ?? auth()->user()->full_name) }}</td>
                            <th>MONTH &amp; YEAR</th>
                            <td>{{ strtoupper($calendarMonth->format('F Y')) }}</td>
                        </tr>
                    </tbody>
                </table>
                <table class="cash-report-table">
                    <thead>
                        <tr>
                            <th rowspan="3">STALL<br>SECTION</th>
                            <th rowspan="3">ASSIGNED<br>COLLECTOR</th>
                            <th rowspan="3">NO. OF CASH<br>TICKET<br>DISTRIBUTED</th>
                            <th colspan="{{ $reportDates->count() + $reportWeeks->count() }}" class="cash-report-month"><i class="bi bi-calendar3"></i> {{ strtoupper($calendarMonth->format('m  F  Y')) }}</th>
                            <th rowspan="3">TOTAL<br>COLLECTED<br>AMOUNT</th>
                        </tr>
                        <tr>
                            @foreach ($reportWeeks as $weekDates)
                                @foreach ($weekDates as $date)
                                    <th>{{ $date->format('D') }}</th>
                                @endforeach
                                <th rowspan="2">WEEK TOTAL</th>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach ($reportWeeks as $weekDates)
                                @foreach ($weekDates as $date)
                                    <th>{{ $date->day }}</th>
                                @endforeach
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sections as $section => $details)
                            @php
                                $sectionAssignments = $assignedTickets->where('stall_section', $section)->values();
                                $collectorNames = $sectionAssignments->map(fn ($assignment) => $assignment->collector?->firstname ?: $assignment->collector?->full_name)->filter()->values();
                                $sectionTicketTotal = $reportDates->sum(fn ($date) => (int) data_get($collectionRowsByDateSection, $date->toDateString().'.'.$section.'.tickets', 0));
                                $sectionAmountTotal = $reportDates->sum(fn ($date) => (float) data_get($collectionRowsByDateSection, $date->toDateString().'.'.$section.'.amount', 0));
                            @endphp
                            <tr>
                                <td>{{ $details['label'] }}</td>
                                <td>{{ $collectorNames->isNotEmpty() ? $collectorNames->join(', ', ' & ') : 'No collector assigned' }}</td>
                                <td>{{ number_format($sectionTicketTotal) }}</td>
                                @foreach ($reportWeeks as $weekDates)
                                    @foreach ($weekDates as $date)
                                        <td>{{ number_format((float) data_get($collectionRowsByDateSection, $date->toDateString().'.'.$section.'.amount', 0)) }}</td>
                                    @endforeach
                                    <td class="report-strong">{{ number_format($weekDates->sum(fn ($date) => (float) data_get($collectionRowsByDateSection, $date->toDateString().'.'.$section.'.amount', 0))) }}</td>
                                @endforeach
                                <td class="report-strong">{{ number_format($sectionAmountTotal) }}</td>
                            </tr>
                        @endforeach
                        <tr class="cash-report-grand-total">
                            <td colspan="3">GRAND TOTAL:</td>
                            @foreach ($reportWeeks as $weekDates)
                                @foreach ($weekDates as $date)
                                    <td>{{ number_format((float) data_get($collectionTotalsByDate, $date->toDateString().'.amount', 0)) }}</td>
                                @endforeach
                                <td>{{ number_format($weekDates->sum(fn ($date) => (float) data_get($collectionTotalsByDate, $date->toDateString().'.amount', 0))) }}</td>
                            @endforeach
                            <td>{{ number_format($reportDates->sum(fn ($date) => (float) data_get($collectionTotalsByDate, $date->toDateString().'.amount', 0))) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <footer class="dialog-actions cash-ticket-sticky-actions"><button type="button" class="button button-muted" data-close-dialog>Close</button></footer>
        </section>
    </dialog>

    <dialog id="treasurerCashStep5" class="market-dialog cash-ticket-step-dialog cash-ticket-final-dialog treasurer-cash-readonly-dialog">
        <section>
            <div class="cash-final-heading">
                <button type="button" data-close-dialog><i class="bi bi-arrow-left-circle-fill"></i></button>
                <img src="{{ asset('assets/einspect/HOMEPAGE/Logo.png') }}" alt="Pandan official seal">
                <div>
                    <h2>REPORT OF COLLECTION AND CASH TICKET</h2>
                    <p>Pandan, Antique</p>
                </div>
                <button type="button" data-close-dialog><i class="bi bi-x-circle"></i></button>
            </div>
            <div class="cash-final-body">
                <table class="cash-final-meta">
                    <tbody>
                        <tr>
                            <th>REPORT NO.</th>
                            <td>{{ $reportNumber }}</td>
                            <th>DATE:</th>
                            <td>{{ now()->format('F d, Y') }}</td>
                        </tr>
                        <tr>
                            <th>RCC II.</th>
                            <td>{{ strtoupper($assignedTickets->first()?->collector?->full_name ?? auth()->user()->full_name) }}</td>
                            <th>MONTH &amp; YEAR</th>
                            <td>{{ strtoupper($calendarMonth->format('F Y')) }}</td>
                        </tr>
                    </tbody>
                </table>
                <table class="cash-final-table">
                    <thead>
                        <tr>
                            <th rowspan="3">STALL<br>SECTION</th>
                            <th rowspan="3">ASSIGNED<br>COLLECTOR</th>
                            <th rowspan="3">NO. OF CASH<br>TICKET DISTRIBUTED</th>
                            <th colspan="{{ $reportDates->count() + $reportWeeks->count() }}" class="cash-final-month">{{ strtoupper($calendarMonth->format('m  F  Y')) }}</th>
                            <th rowspan="3">TOTAL<br>COLLECTED<br>AMOUNT</th>
                        </tr>
                        <tr>
                            @foreach ($reportWeeks as $weekDates)
                                @foreach ($weekDates as $date)
                                    <th>{{ $date->format('D') }}</th>
                                @endforeach
                                <th rowspan="2">WEEK TOTAL</th>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach ($reportWeeks as $weekDates)
                                @foreach ($weekDates as $date)
                                    <th>{{ $date->day }}</th>
                                @endforeach
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sections as $section => $details)
                            @php
                                $sectionAssignments = $assignedTickets->where('stall_section', $section)->values();
                                $collectorNames = $sectionAssignments->map(fn ($assignment) => $assignment->collector?->firstname ?: $assignment->collector?->full_name)->filter()->values();
                                $sectionTicketTotal = $reportDates->sum(fn ($date) => (int) data_get($collectionRowsByDateSection, $date->toDateString().'.'.$section.'.tickets', 0));
                                $sectionAmountTotal = $reportDates->sum(fn ($date) => (float) data_get($collectionRowsByDateSection, $date->toDateString().'.'.$section.'.amount', 0));
                            @endphp
                            <tr>
                                <td>{{ $details['label'] }}</td>
                                <td>{{ $collectorNames->isNotEmpty() ? $collectorNames->join(', ', ' & ') : 'No collector assigned' }}</td>
                                <td>{{ number_format($sectionTicketTotal) }}</td>
                                @foreach ($reportWeeks as $weekDates)
                                    @foreach ($weekDates as $date)
                                        <td>{{ number_format((float) data_get($collectionRowsByDateSection, $date->toDateString().'.'.$section.'.amount', 0)) }}</td>
                                    @endforeach
                                    <td class="report-strong">{{ number_format($weekDates->sum(fn ($date) => (float) data_get($collectionRowsByDateSection, $date->toDateString().'.'.$section.'.amount', 0))) }}</td>
                                @endforeach
                                <td class="report-strong">{{ number_format($sectionAmountTotal) }}</td>
                            </tr>
                        @endforeach
                        <tr class="cash-final-grand-total">
                            <td colspan="3">GRAND TOTAL:</td>
                            @foreach ($reportWeeks as $weekDates)
                                @foreach ($weekDates as $date)
                                    <td>{{ number_format((float) data_get($collectionTotalsByDate, $date->toDateString().'.amount', 0)) }}</td>
                                @endforeach
                                <td>{{ number_format($weekDates->sum(fn ($date) => (float) data_get($collectionTotalsByDate, $date->toDateString().'.amount', 0))) }}</td>
                            @endforeach
                            <td>{{ number_format($reportDates->sum(fn ($date) => (float) data_get($collectionTotalsByDate, $date->toDateString().'.amount', 0))) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <footer class="dialog-actions cash-ticket-sticky-actions"><button type="button" class="button button-muted" data-close-dialog>Close</button></footer>
        </section>
    </dialog>
    @endif
@endsection

@push('scripts')
    <script>
        $('[data-collector-photo-input]').on('change', function () {
            const file = this.files?.[0];
            if (!file) return;
            const previewKey = $(this).attr('data-collector-photo-input');
            const selector = previewKey ? `[data-collector-photo-preview="${previewKey}"]` : '[data-collector-photo-preview]';
            const preview = $(selector).get(0);
            if (preview) preview.src = URL.createObjectURL(file);
            const fileLabel = $(previewKey ? `[data-collector-photo-file="${previewKey}"]` : '[data-collector-photo-file]').get(0);
            if (fileLabel) fileLabel.textContent = file.name;
        });

        const treasurerCollectionRowsByDateSection = @json($collectionRowsByDateSection);
        const treasurerCollectionTotalsByDate = @json($collectionTotalsByDate);
        const treasurerCollectionMonth = @json($calendarMonth->format('m'));
        const treasurerCollectionYear = @json($calendarMonth->format('Y'));

        $('[data-treasurer-collection-month], [data-treasurer-collection-year]').on('change', function () {
            const url = new URL(window.location.href);
            url.searchParams.set('collection_month', $('[data-treasurer-collection-month]').val());
            url.searchParams.set('collection_year', $('[data-treasurer-collection-year]').val());
            window.location.href = url.toString();
        });

        $(document).on('click', '[data-treasurer-collection-day]', function () {
            const day = String($(this).data('treasurer-collection-day')).padStart(2, '0');
            const selectedDate = `${treasurerCollectionYear}-${treasurerCollectionMonth}-${day}`;
            const totals = treasurerCollectionTotalsByDate[selectedDate] || { amount: 0, tickets: 0, collectors: 0 };
            const rows = treasurerCollectionRowsByDateSection[selectedDate] || {};
            const date = new Date(`${selectedDate}T00:00:00`);

            $('[data-treasurer-date-detail-title]').text(date.toLocaleDateString('en-US', { month: 'long', day: '2-digit', year: 'numeric' }).toUpperCase());
            $('[data-treasurer-date-detail-weekday]').text(date.toLocaleDateString('en-US', { weekday: 'long' }).toUpperCase());
            $('[data-treasurer-total-date]').text(date.toLocaleDateString('en-US', { month: 'long', day: '2-digit', year: 'numeric' }));
            $('[data-treasurer-total-amount]').text(`PHP ${Number(totals.amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`);
            $('[data-treasurer-total-tickets]').text(Number(totals.tickets || 0).toLocaleString('en-US'));
            $('[data-treasurer-total-collectors]').text(Number(totals.collectors || 0).toLocaleString('en-US'));

            let detailTotal = 0;
            $('#treasurerCashTicketDateCollection [data-treasurer-date-section]').each(function () {
                const section = $(this).data('treasurer-date-section');
                const row = rows[section] || { amount: 0, tickets: 0 };
                const amount = Number(row.amount || 0);
                $(this).find('[data-treasurer-date-row-amount]').val(amount.toFixed(2));
                $(this).find('[data-treasurer-date-ticket-quantity]').val(Number(row.tickets || 0));
                detailTotal += amount;
            });

            $('[data-treasurer-date-detail-total]').text(detailTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            $('[data-treasurer-collection-day]').removeClass('selected');
            $(this).addClass('selected');

            document.getElementById('treasurerCashStep3')?.close();
            document.getElementById('treasurerCashTicketDateCollection')?.showModal();
        });

        $('[data-return-treasurer-step3]').on('click', function () {
            document.getElementById('treasurerCashTicketDateCollection')?.close();
            document.getElementById('treasurerCashStep3')?.showModal();
        });
    </script>
@endpush
