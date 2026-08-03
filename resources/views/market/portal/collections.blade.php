@extends('market.layouts.portal')

@section('content')
    @php
        $selectedCalendarMonth = (int) request('collection_month', now()->month);
        $selectedCalendarYear = (int) request('collection_year', now()->year);
        $calendarMonth = \Carbon\Carbon::create($selectedCalendarYear, $selectedCalendarMonth, 1)->startOfMonth();
        $collectedDays = $collections->filter(fn ($item) => $item->collection_date->isSameMonth($calendarMonth))->pluck('collection_date')->map->day->all();
        $sections = [
            'FISH' => ['label' => 'Fish Section', 'image' => 'Fish Section.png', 'collector_image' => 'I-Clerk.png'],
            'POULTRY' => ['label' => 'Poultry Section', 'image' => 'Poultry Section.png', 'collector_image' => '3-Collector Clerk.png'],
            'PORK' => ['label' => 'Pork Section', 'image' => 'Pork Section.png', 'collector_image' => 'C-Clerk.png'],
            'BEEF' => ['label' => 'Beef Section', 'image' => 'Beef Section.png', 'collector_image' => '3-Collector Clerk.png'],
            'MIXED' => ['label' => 'Mixed Section', 'image' => 'Mixed Section.png', 'collector_image' => 'C-Clerk.png'],
        ];
        $workflowAssignments = ($assignments ?? collect())->filter(fn ($item) => !str_starts_with($item->assignment_number ?? '', 'CTA-DEMO-')
            && $item->assigned_date
            && $item->assigned_date->isSameMonth($calendarMonth));
        $workflowCollections = $collections->filter(fn ($item) => !str_starts_with($item->collection_number ?? '', 'CT-DEMO-')
            && $item->collection_date
            && $item->collection_date->isSameMonth($calendarMonth));
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
        $requestedAssignments = $workflowAssignments->where('status', 'REQUESTED');
        $assignedTickets = $workflowAssignments->whereIn('status', ['ASSIGNED', 'COMPLETED']);
        $reportedCollections = $workflowCollections->whereIn('status', ['REPORTED', 'SUBMITTED']);
        $assignmentInputIndex = 0;
        $slipRowCount = 8;
        $slipAssignments = $requestedAssignments->sortByDesc('id')->take($slipRowCount)->reverse()->values();
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
        $sectionOptions = collect($sections)->map(fn ($details, $section) => [
            'key' => $section,
            'label' => $details['label'],
            'image' => asset('assets/einspect/HOMEPAGE/'.$details['image']),
            'collector_image' => asset('assets/einspect/USERS/'.$details['collector_image']),
        ])->values();
        $step3Done = $assignedTickets->where('status', 'COMPLETED')->isNotEmpty() || $workflowCollections->whereIn('status', ['REPORTED', 'SUBMITTED'])->isNotEmpty();
        $workflowSteps = [
            ['label' => 'Request Requisition and Issue Slip', 'icon' => 'bi-card-checklist', 'done' => $requestedAssignments->isNotEmpty() || $assignedTickets->isNotEmpty(), 'dialog' => 'cashTicketStep1'],
            ['label' => 'Assign and Distribute Cash Tickets', 'icon' => 'bi-people-fill', 'done' => $assignedTickets->isNotEmpty(), 'dialog' => 'cashTicketStep2'],
            ['label' => 'Collect and Record Fees', 'icon' => 'bi-cash-stack', 'done' => $step3Done, 'dialog' => 'cashTicketStep3'],
            ['label' => 'Create Collection Report', 'icon' => 'bi-pencil-fill', 'done' => $reportedCollections->isNotEmpty(), 'dialog' => 'cashTicketStep4'],
            ['label' => 'Submit to Treasurer', 'icon' => 'bi-person-fill', 'done' => $workflowCollections->where('status', 'SUBMITTED')->isNotEmpty(), 'dialog' => 'cashTicketStep5'],
        ];
    @endphp

    @include('market.tenant.applications.css.header')
    <header class="tenant-page-title px-3 pt-3 pb-0">
        <div>
            <i class="bi bi-card-checklist"></i>
            <div>
                <h1>CASH TICKET</h1>
                <p class="mb-0">Dashboard | Cash Ticket</p>
            </div>
        </div>
    </header>

    <section class="cash-ticket-workflow-shell">
        <div class="workflow-title">
            <i class="bi bi-ticket-perforated-fill"></i>
            <div><h2>CASH TICKET</h2><span>PUBLIC MARKET PANDAN, ANTIQUE</span></div>
        </div>
        <div class="workflow-steps">
            @foreach ($workflowSteps as $index => $step)
                <div class="workflow-step-item {{ $step['done'] ? 'complete' : '' }}" style="--step-line-color: {{ $step['done'] ? '#075d16' : '#760008' }};">
                    <div class="workflow-step-card {{ $step['done'] ? 'complete' : '' }}">
                        <b>{{ $index + 1 }}</b>
                        <button type="button" class="workflow-step-main" data-open-dialog="{{ $step['dialog'] }}">
                            <i class="bi {{ $step['icon'] }}"></i>
                            <span>{{ $step['label'] }}</span>
                        </button>
                        @if ($step['done'])
                            <button type="button" class="workflow-view-request" data-open-dialog="{{ $step['dialog'] }}">View Request</button>
                            <form class="cash-ticket-reset-form" action="{{ route('clerk.cash-ticket.reset') }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="month" value="{{ $calendarMonth->toDateString() }}">
                                <input type="hidden" name="step" value="{{ $index + 1 }}">
                                <button type="button" data-reset-cash-ticket><i class="bi bi-trash-fill"></i> Delete Request</button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <dialog id="cashTicketStep1" class="market-dialog cash-ticket-step-dialog cash-ticket-slip-dialog">
        <form action="{{ route('clerk.assignments.store') }}" method="POST" data-cash-ticket-assignment-form>
            @csrf
            <input type="hidden" name="workflow_status" value="REQUESTED">
            <input type="hidden" name="open_dialog" value="cashTicketStep1">
            <div class="cash-ticket-dialog-heading">
                <i class="bi bi-card-checklist"></i>
                <div><h2>CASH TICKET</h2><p>REQUISITION AND ISSUE SLIP</p></div>
                <button type="button" data-close-dialog><i class="bi bi-x-circle"></i></button>
            </div>
            <table class="cash-slip-table">
                <thead>
                    <tr class="cash-slip-meta-row">
                        <th colspan="2">Office: <input value="Municipal Treasurer's Office" readonly></th>
                        <th>RIS No. <input value="{{ 'RIS-'.now()->format('Ymd') }}" readonly></th>
                        <th colspan="3">Date: <input type="date" name="assignments[0][assigned_date]" value="{{ old('assignments.0.assigned_date', $slipDate) }}" data-slip-date-source required></th>
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
                            $rowIndex = $assignmentInputIndex++;
                            $savedSlip = $slipAssignments->get($slipRow);
                            $savedRemarks = $savedSlip?->remarks;
                        @endphp
                        <tr>
                            <td>
                                <input type="hidden" name="assignments[{{ $rowIndex }}][collector_id]" value="{{ auth()->id() }}">
                                <input type="hidden" name="assignments[{{ $rowIndex }}][stall_section]" value="MIXED">
                                <input type="hidden" name="assignments[{{ $rowIndex }}][assigned_date]" value="{{ old('assignments.'.$rowIndex.'.assigned_date', optional($savedSlip?->assigned_date)->toDateString() ?: $slipDate) }}" data-slip-date-target>
                                <input name="cash_slip[{{ $rowIndex }}][unit]" value="{{ old('cash_slip.'.$rowIndex.'.unit', $readSlipRemark($savedRemarks, 'Unit')) }}">
                            </td>
                            <td><input name="cash_slip[{{ $rowIndex }}][description]" value="{{ old('cash_slip.'.$rowIndex.'.description', $readSlipRemark($savedRemarks, 'Description')) }}"></td>
                            <td><input type="number" name="cash_slip[{{ $rowIndex }}][stub]" value="{{ old('cash_slip.'.$rowIndex.'.stub', $readSlipRemark($savedRemarks, 'Stub')) }}" min="1"></td>
                            <td><input type="number" name="cash_slip[{{ $rowIndex }}][pcs]" value="{{ old('cash_slip.'.$rowIndex.'.pcs', $readSlipRemark($savedRemarks, 'Pcs')) }}" min="1"></td>
                            <td><input type="number" name="assignments[{{ $rowIndex }}][ticket_start]" value="{{ old('assignments.'.$rowIndex.'.ticket_start', $savedSlip?->ticket_start ?: '') }}" min="1"></td>
                            <td><input type="number" name="assignments[{{ $rowIndex }}][ticket_end]" value="{{ old('assignments.'.$rowIndex.'.ticket_end', $savedSlip?->ticket_end ?: '') }}" min="1"></td>
                        </tr>
                    @endfor
                    <tr>
                        <td><strong>Purpose:</strong></td>
                        <td colspan="5"><input name="assignments[0][remarks]" value="For use of the Office of the Municipal Treasurer"></td>
                    </tr>
                    <tr class="cash-slip-signature-row">
                        <td rowspan="4" class="signature-labels">Signature:<br>Printed Name:<br>Designation:<br>Date:</td>
                        <td>Requested by:</td>
                        <td>Approved by:</td>
                        <td colspan="2">Issued by:</td>
                        <td>Received by:</td>
                    </tr>
                    <tr class="cash-slip-signature-row">
                        <td>{{ strtoupper(auth()->user()->full_name) }}</td>
                        <td>EDSEL J. AMBUIBUYOG</td>
                        <td colspan="2">EDSEL J. AMBUIBUYOG</td>
                        <td>{{ strtoupper(auth()->user()->full_name) }}</td>
                    </tr>
                    <tr class="cash-slip-signature-row">
                        <td>{{ auth()->user()->designation ?: 'RCC II' }}</td>
                        <td>Acting Municipal Treasurer</td>
                        <td colspan="2">Acting Municipal Treasurer</td>
                        <td>{{ auth()->user()->designation ?: 'RCC II' }}</td>
                    </tr>
                    <tr class="cash-slip-signature-row">
                        <td>{{ now()->format('m/d/Y') }}</td>
                        <td></td>
                        <td colspan="2"></td>
                        <td>{{ now()->format('m/d/Y') }}</td>
                    </tr>
                </tbody>
            </table>
            <div class="cash-slip-remarks"><label>Remarks:<input name="assignments[0][remarks]" value="{{ old('assignments.0.remarks', $readSlipUserRemark($slipAssignments->first()?->remarks)) }}" placeholder="Enter remarks (Optional)"></label></div>
            <div class="dialog-actions"><button class="button button-primary">Submit</button><button type="button" class="button button-muted" data-close-dialog>Cancel</button></div>
        </form>
    </dialog>

    <dialog id="cashTicketStep2" class="market-dialog cash-ticket-step-dialog">
        <form action="{{ route('clerk.assignments.store') }}" method="POST" data-cash-ticket-assignment-form>
            @csrf
            <input type="hidden" name="workflow_status" value="ASSIGNED">
            <input type="hidden" name="open_dialog" value="cashTicketStep2">
            <input type="hidden" name="rcc_id" value="{{ auth()->id() }}">
            <div class="cash-ticket-dialog-heading">
                <i class="bi bi-people-fill"></i>
                <div><h2>CASH TICKET</h2><p>ASSIGN COLLECTORS</p></div>
                <button type="button" data-close-dialog><i class="bi bi-x-circle"></i></button>
            </div>
            <div class="cash-assign-meta"><strong>RCC II:</strong><span>{{ strtoupper(auth()->user()->full_name) }}</span><input type="date" value="{{ now()->toDateString() }}" data-assign-date-source></div>
            <table class="cash-assign-table">
                <thead>
                    <tr>
                        <th colspan="2">STALL SECTION</th>
                        <th colspan="3">ASSIGN COLLECTOR/S</th>
                        <th colspan="2">NO. OF TICKETS DISTRIBUTED</th>
                    </tr>
                </thead>
                <tbody data-assignment-table-body>
                    @foreach ($sections as $section => $details)
                        @php $rowIndex = $assignmentInputIndex++; @endphp
                        <tr data-section-row="{{ $section }}">
                            <td><img class="section-image" src="{{ asset('assets/einspect/HOMEPAGE/'.$details['image']) }}" alt=""></td>
                            <td>
                                <select name="assignments[{{ $rowIndex }}][stall_section]" required>
                                    <option value="{{ $section }}">{{ $details['label'] }}</option>
                                </select>
                            </td>
                            <td><img class="collector-image" src="{{ asset('assets/einspect/USERS/'.$details['collector_image']) }}" alt=""></td>
                            <td><input type="number" class="collector-count" value="0" min="0" data-collector-count readonly></td>
                            <td>
                                <select name="assignments[{{ $rowIndex }}][collector_id][]" class="cash-collector-select" data-cash-collector-select multiple>
                                    @foreach ($collectors as $collector)
                                        <option value="{{ $collector->id }}" @selected(in_array($collector->id, \Illuminate\Support\Arr::wrap(old('assignments.'.$rowIndex.'.collector_id', $assignedTickets->where('stall_section', $section)->pluck('collector_id')->all()))))>{{ $collector->full_name }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="assignments[{{ $rowIndex }}][assigned_date]" value="{{ now()->toDateString() }}" data-assign-date-target>
                                <input type="hidden" name="assignments[{{ $rowIndex }}][ticket_start]" value="1">
                                <input type="hidden" name="assignments[{{ $rowIndex }}][remarks]" value="Distributed to {{ $details['label'] }} collector">
                            </td>
                            <td><i class="bi bi-ticket-perforated-fill cash-ticket-sample"></i></td>
                            <td>
                                <input type="number" name="assignments[{{ $rowIndex }}][ticket_end]" value="20" min="1" required>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="dialog-actions cash-ticket-sticky-actions"><button class="button button-primary">Save</button><button type="button" class="button button-muted" data-close-dialog>Cancel</button></div>
        </form>
    </dialog>

    <dialog id="cashTicketStep3" class="market-dialog cash-ticket-step-dialog cash-ticket-collection-dialog">
        <form action="{{ route('clerk.collections.store') }}" method="POST">
            @csrf
            <input type="hidden" name="open_dialog" value="cashTicketStep3">
            <input type="hidden" name="update_progress" value="1">
            <div class="cash-ticket-dialog-heading">
                <i class="bi bi-cash-stack"></i>
                <div><h2>CASH TICKET</h2><p>COLLECTION OF FEES</p></div>
                <button type="button" data-close-dialog><i class="bi bi-x-circle"></i></button>
            </div>
            <div class="cash-collection-meta">
                <div><strong>RCC II:</strong><span>{{ strtoupper(auth()->user()->full_name) }}</span></div>
                <input type="hidden" name="collection_date" value="{{ $calendarMonth->toDateString() }}" data-collection-date-source required>
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
                                $firstAssignment = $sectionAssignments->first();
                                $collectorNames = $sectionAssignments->map(fn ($assignment) => $assignment->collector?->firstname ?: $assignment->collector?->full_name)->filter()->values();
                                $rowQuantity = $sectionAssignments->sum('ticket_quantity') ?: 20;
                            @endphp
                            <tr>
                                <td><img class="section-image" src="{{ asset('assets/einspect/HOMEPAGE/'.$details['image']) }}" alt=""></td>
                                <td>{{ $details['label'] }}</td>
                                <td><img class="collector-image" src="{{ asset('assets/einspect/USERS/'.$details['collector_image']) }}" alt=""></td>
                                <td>{{ $collectorNames->count() }}</td>
                                <td>{{ $collectorNames->isNotEmpty() ? $collectorNames->join(', ', ' & ') : 'No collector assigned' }}</td>
                                <td><i class="bi bi-ticket-perforated-fill cash-ticket-sample"></i></td>
                                <td>
                                    <input type="number" name="collections[{{ $section }}][ticket_quantity]" value="{{ $rowQuantity }}" min="1">
                                    <input type="hidden" name="collections[{{ $section }}][collector_id]" value="{{ $firstAssignment?->collector_id }}">
                                    <input type="hidden" name="collections[{{ $section }}][stall_section]" value="{{ $section }}">
                                    <input type="hidden" name="collections[{{ $section }}][cash_ticket_assignment_id]" value="{{ $firstAssignment?->id }}">
                                    <input type="hidden" name="collections[{{ $section }}][ticket_start]" value="{{ $firstAssignment?->ticket_start }}">
                                    <input type="hidden" name="collections[{{ $section }}][ticket_end]" value="{{ $firstAssignment?->ticket_end }}">
                                    <input type="hidden" name="collections[{{ $section }}][amount]" value="{{ $rowQuantity * 10 }}">
                                    <input type="hidden" name="collections[{{ $section }}][remarks]" value="Collection of fees for {{ $details['label'] }}">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <section class="cash-fee-calendar">
                    <header>
                        <select name="collection_month" data-collection-month>
                            @foreach (range(1, 12) as $month)
                                <option value="{{ $month }}" @selected($month === $selectedCalendarMonth)>{{ strtoupper(\Carbon\Carbon::create(2000, $month, 1)->format('F')) }}</option>
                            @endforeach
                        </select>
                        <strong data-collection-month-title>{{ strtoupper($calendarMonth->format('F')) }}</strong>
                        <select name="collection_year" data-collection-year>
                            @foreach (range(now()->year - 1, now()->year + 1) as $year)
                                <option value="{{ $year }}" @selected($year === $selectedCalendarYear)>{{ $year }}</option>
                            @endforeach
                        </select>
                    </header>
                    <div class="cash-fee-weekdays">@foreach (['SUN','MON','TUE','WED','THU','FRI','SAT'] as $day)<span>{{ $day }}</span>@endforeach</div>
                    <div class="cash-fee-days" data-collection-calendar-days>
                        @for ($blank = 0; $blank < $calendarMonth->dayOfWeek; $blank++)<button type="button" class="blank"></button>@endfor
                        @for ($day = 1; $day <= $calendarMonth->daysInMonth; $day++)
                            <button type="button" class="{{ in_array($day, $collectedDays) ? 'collected' : '' }}" data-collection-day="{{ $day }}">{{ $day }}</button>
                        @endfor
                    </div>
                    <div class="cash-date-total">
                        <span data-collection-total-date>{{ now()->format('F d, Y') }}</span>
                        <strong data-collection-total-amount>PHP 0.00</strong>
                        <small><b data-collection-total-tickets>0</b> tickets collected by <b data-collection-total-collectors>0</b> collector/s</small>
                    </div>
                </section>
            </div>
            <div class="dialog-actions cash-ticket-sticky-actions"><button class="button button-primary">Save</button><button type="button" class="button button-muted" data-close-dialog>Cancel</button></div>
        </form>
    </dialog>

    <dialog id="cashTicketDateCollection" class="market-dialog cash-ticket-step-dialog cash-ticket-date-dialog">
        <form action="{{ route('clerk.collections.store') }}" method="POST">
            @csrf
            <input type="hidden" name="open_dialog" value="cashTicketStep3">
            <input type="hidden" name="collection_date" value="{{ now()->toDateString() }}" data-date-detail-input>
            <div class="cash-date-dialog-heading">
                <button type="button" data-return-step3><i class="bi bi-arrow-left-circle-fill"></i></button>
                <i class="bi bi-calendar3"></i>
                <div><h2 data-date-detail-title>{{ strtoupper(now()->format('F d, Y')) }}</h2><p data-date-detail-weekday>{{ strtoupper(now()->format('l')) }}</p></div>
                <button type="button" data-return-step3><i class="bi bi-x-circle"></i></button>
            </div>
            <table class="cash-date-table">
                <thead>
                    <tr><th colspan="2">STALL SECTION</th><th colspan="3">ASSIGN COLLECTOR</th><th colspan="2">NO. OF CASH TICKETS</th><th>TOTAL COLLECTED</th></tr>
                </thead>
                <tbody>
                    @foreach ($sections as $section => $details)
                        @php
                            $sectionAssignments = $assignedTickets->where('stall_section', $section)->values();
                            $firstAssignment = $sectionAssignments->first();
                            $collectorNames = $sectionAssignments->map(fn ($assignment) => $assignment->collector?->firstname ?: $assignment->collector?->full_name)->filter()->values();
                            $rowQuantity = $sectionAssignments->sum('ticket_quantity') ?: 20;
                        @endphp
                        <tr data-date-section="{{ $section }}">
                            <td><img class="section-image" src="{{ asset('assets/einspect/HOMEPAGE/'.$details['image']) }}" alt=""></td>
                            <td>{{ $details['label'] }}</td>
                            <td><img class="collector-image" src="{{ asset('assets/einspect/USERS/'.$details['collector_image']) }}" alt=""></td>
                            <td>{{ $collectorNames->count() }}</td>
                            <td>{{ $collectorNames->isNotEmpty() ? $collectorNames->join(', ', ' & ') : 'No collector assigned' }}</td>
                            <td><i class="bi bi-ticket-perforated-fill cash-ticket-sample"></i></td>
                            <td><input type="number" name="collections[{{ $section }}][ticket_quantity]" value="{{ $rowQuantity }}" min="1" data-date-ticket-quantity></td>
                            <td>
                                <input type="number" name="collections[{{ $section }}][amount]" value="0" min="0" step="0.01" data-date-row-amount>
                                <input type="hidden" name="collections[{{ $section }}][collector_id]" value="{{ $firstAssignment?->collector_id ?? auth()->id() }}">
                                <input type="hidden" name="collections[{{ $section }}][stall_section]" value="{{ $section }}">
                                <input type="hidden" name="collections[{{ $section }}][cash_ticket_assignment_id]" value="{{ $firstAssignment?->id }}">
                                <input type="hidden" name="collections[{{ $section }}][ticket_start]" value="{{ $firstAssignment?->ticket_start }}">
                                <input type="hidden" name="collections[{{ $section }}][ticket_end]" value="{{ $firstAssignment?->ticket_end }}">
                                <input type="hidden" name="collections[{{ $section }}][remarks]" value="Collection of fees for {{ $details['label'] }}">
                            </td>
                        </tr>
                    @endforeach
                    <tr class="cash-date-total-row">
                        <td colspan="7">TOTAL FEE:</td>
                        <td><span>&#8369;</span><strong data-date-detail-total>0.00</strong></td>
                    </tr>
                </tbody>
            </table>
            <div class="dialog-actions cash-ticket-sticky-actions"><button class="button button-primary">Save</button><button type="button" class="button button-muted" data-return-step3>Cancel</button></div>
        </form>
    </dialog>

    <dialog id="cashTicketStep4" class="market-dialog cash-ticket-step-dialog cash-ticket-report-dialog">
        <form action="{{ route('clerk.cash-ticket.progress') }}" method="POST">
            @csrf
            <input type="hidden" name="open_dialog" value="cashTicketStep4">
            <input type="hidden" name="step" value="REPORT">
            <input type="hidden" name="month" value="{{ $calendarMonth->toDateString() }}">
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
                            <td>{{ strtoupper(auth()->user()->full_name) }}</td>
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
            <div class="dialog-actions cash-ticket-sticky-actions"><button class="button button-primary">Save</button><button type="button" class="button button-muted" data-close-dialog>Cancel</button></div>
        </form>
    </dialog>

    <dialog id="cashTicketStep5" class="market-dialog cash-ticket-step-dialog cash-ticket-final-dialog">
        <form action="{{ route('clerk.cash-ticket.progress') }}" method="POST">
            @csrf
            <input type="hidden" name="open_dialog" value="cashTicketStep5">
            <input type="hidden" name="step" value="FINAL">
            <input type="hidden" name="month" value="{{ $calendarMonth->toDateString() }}">
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
                            <td>{{ strtoupper(auth()->user()->full_name) }}</td>
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
            <div class="dialog-actions cash-final-actions"><button class="button button-primary">Submit</button><button type="button" class="button button-muted" data-close-dialog>Cancel</button></div>
        </form>
    </dialog>
@endsection

@push('scripts')
    <script>
        $('[data-assign-date-source]').on('change', function () {
            $('[data-assign-date-target]').val(this.value);
        });
        $('[data-slip-date-source]').on('change', function () {
            $('[data-slip-date-target]').val(this.value);
        });

        const cashTicketQuery = new URLSearchParams(window.location.search);
        const cashTicketOpenDialog = @json(session('open_dialog')) || cashTicketQuery.get('open_dialog');
        if (cashTicketOpenDialog) {
            document.getElementById(cashTicketOpenDialog)?.showModal();
        }

        $('[data-cash-ticket-assignment-form]').on('submit', function (event) {
            let message = '';

            $(this).find('input[name$="[ticket_start]"]').each(function () {
                if (message) return;

                const startInput = $(this);
                const start = startInput.val();
                const assignmentIndex = startInput.attr('name').match(/assignments\[(\d+)]/)?.[1];
                const endInput = assignmentIndex
                    ? $(this).closest('form').find(`input[name="assignments[${assignmentIndex}][ticket_end]"]`).first()
                    : startInput.closest('tr').find('input[name$="[ticket_end]"]').first();
                const end = endInput.val();

                if (!start && !end) return;

                if (!start || !end) {
                    message = 'Please complete both From and To ticket numbers.';
                    return;
                }

                if (Number(end) < Number(start)) {
                    message = 'Ending ticket must be greater than or equal to the starting ticket.';
                }
            });

            if (message) {
                event.preventDefault();
                const activeDialog = this.closest('dialog');
                Swal.fire({
                    target: activeDialog || document.body,
                    title: 'Please check your entry',
                    text: message,
                    icon: 'warning',
                    confirmButtonColor: '#760008',
                    backdrop: 'rgba(25, 0, 0, .45)',
                    customClass: { popup: 'einspect-swal' }
                });
            }
        });

        $('[data-reset-cash-ticket]').on('click', function () {
            const form = this.closest('form');

            einspectConfirm({
                title: 'Delete Request?',
                message: 'Delete this cash ticket request and reset its submitted progress?',
                confirmText: 'Yes, Delete',
                cancelText: 'No, Keep It'
            }).then(function (result) {
                if (result.isConfirmed) form?.submit();
            });
        });

        let cashTicketAssignmentIndex = {{ $assignmentInputIndex }};
        const monthNames = ['JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE', 'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER'];
        const collectionTotalsByDate = @json($collectionTotalsByDate);
        const collectionRowsByDateSection = @json($collectionRowsByDateSection);
        $('[data-cash-collector-select]').select2({
            placeholder: 'Please select collector/s',
            width: '100%',
            closeOnSelect: false,
            dropdownParent: $('#cashTicketStep2')
        }).on('change', function () {
            $(this).closest('tr').find('[data-collector-count]').val($(this).val()?.length || 0);
        }).trigger('change');

        $('[data-collection-month], [data-collection-year]').on('change', function () {
            renderCollectionCalendar();
        });

        function renderCollectionCalendar() {
            const month = Number($('[data-collection-month]').val());
            const year = Number($('[data-collection-year]').val());
            const firstDay = new Date(year, month - 1, 1);
            const daysInMonth = new Date(year, month, 0).getDate();
            const calendar = $('[data-collection-calendar-days]').empty();

            $('[data-collection-month-title]').text(monthNames[month - 1]);
            $('[data-collection-date-source]').val(`${year}-${String(month).padStart(2, '0')}-01`);
            $('.cash-selected-month span').text(`${monthNames[month - 1]} ${year}`);

            for (let blank = 0; blank < firstDay.getDay(); blank += 1) {
                calendar.append('<button type="button" class="blank"></button>');
            }

            for (let day = 1; day <= daysInMonth; day += 1) {
                const selectedDate = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const dayButton = $('<button type="button"></button>')
                    .attr('data-collection-day', day)
                    .text(day);
                if (collectionTotalsByDate[selectedDate]) {
                    dayButton.addClass('collected');
                }
                calendar.append(dayButton);
            }
        }

        $(document).on('click', '[data-collection-day]', function () {
            const month = String($('[data-collection-month]').val()).padStart(2, '0');
            const year = $('[data-collection-year]').val();
            const day = String($(this).data('collection-day')).padStart(2, '0');
            const selectedDate = `${year}-${month}-${day}`;
            const totals = collectionTotalsByDate[selectedDate] || { amount: 0, tickets: 0, collectors: 0 };
            const rows = collectionRowsByDateSection[selectedDate] || {};
            const date = new Date(`${selectedDate}T00:00:00`);

            $('[data-collection-date-source]').val(selectedDate);
            $('[data-date-detail-input]').val(selectedDate);
            $('[data-date-detail-title]').text(date.toLocaleDateString('en-US', { month: 'long', day: '2-digit', year: 'numeric' }).toUpperCase());
            $('[data-date-detail-weekday]').text(date.toLocaleDateString('en-US', { weekday: 'long' }).toUpperCase());
            $('[data-collection-total-date]').text(date.toLocaleDateString('en-US', { month: 'long', day: '2-digit', year: 'numeric' }));
            $('[data-collection-total-amount]').text(`PHP ${Number(totals.amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`);
            $('[data-collection-total-tickets]').text(Number(totals.tickets || 0).toLocaleString('en-US'));
            $('[data-collection-total-collectors]').text(Number(totals.collectors || 0).toLocaleString('en-US'));
            $('#cashTicketDateCollection [data-date-section]').each(function () {
                const section = $(this).data('date-section');
                const row = rows[section] || { amount: 0 };
                $(this).find('[data-date-row-amount]').val(Number(row.amount || 0).toFixed(2));
                if (row.tickets) {
                    $(this).find('[data-date-ticket-quantity]').val(row.tickets);
                }
            });
            $('[data-collection-day]').removeClass('selected');
            $(this).addClass('selected');
            refreshDateDetailTotal();

            document.getElementById('cashTicketStep3')?.close();
            document.getElementById('cashTicketDateCollection')?.showModal();
        });

        $('#cashTicketDateCollection form').on('submit', function (event) {
            event.preventDefault();
            const form = this;
            const submitButton = $(form).find('button[type="submit"], button:not([type])').first();
            submitButton.prop('disabled', true);

            $.ajax({
                url: form.action,
                method: form.method,
                data: new FormData(form),
                processData: false,
                contentType: false,
                headers: { Accept: 'application/json' },
                success(response) {
                    if (response.date) {
                        collectionTotalsByDate[response.date] = response.totals || { amount: 0, tickets: 0, collectors: 0 };
                        collectionRowsByDateSection[response.date] = response.rows || {};
                    }

                    renderCollectionCalendar();
                    document.getElementById('cashTicketDateCollection')?.close();
                    Swal.fire({
                        text: response.message || 'Cash ticket collections saved.',
                        icon: 'success',
                        confirmButtonColor: '#760008',
                        customClass: { popup: 'einspect-swal' }
                    }).then(() => {
                        document.getElementById('cashTicketStep3')?.showModal();
                    });
                },
                error(xhr) {
                    const errors = xhr.responseJSON?.errors || {};
                    const message = Object.values(errors).flat()[0] || xhr.responseJSON?.message || 'Unable to save collection.';
                    alert(message);
                },
                complete() {
                    submitButton.prop('disabled', false);
                }
            });
        });

        function refreshDateDetailTotal() {
            let total = 0;
            $('#cashTicketDateCollection [data-date-row-amount]').each(function () {
                total += Number($(this).val() || 0);
            });
            $('[data-date-detail-total]').text(total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        }

        $('#cashTicketDateCollection [data-date-row-amount]').on('input', refreshDateDetailTotal);
        refreshDateDetailTotal();

        $('[data-return-step3]').on('click', function () {
            document.getElementById('cashTicketDateCollection')?.close();
            document.getElementById('cashTicketStep3')?.showModal();
        });

        $(document).on('change', '[data-dynamic-section]', function () {
            const selected = $(this).find(':selected');
            const row = $(this).closest('tr');
            row.find('.section-image').attr('src', selected.data('image'));
            row.find('.collector-image').attr('src', selected.data('collector-image'));
        });
    </script>
@endpush
