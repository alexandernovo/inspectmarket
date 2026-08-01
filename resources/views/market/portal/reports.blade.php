@extends('market.layouts.portal')

@section('content')
    @if (auth()->user()->isRole(\App\Models\User::ROLE_CLERK))
        @include('market.tenant.applications.css.header')
        @php
            $showReport = request()->boolean('view');
            $selectedReport = $report;
            $selectedMonthValue = request('month') ?: now()->format('Y-m');
            $reportMonth = $selectedMonth ?? \Carbon\Carbon::createFromFormat('Y-m', $selectedMonthValue)->startOfMonth();
            $currentSection = strtoupper(request('section', ''));
            $sectionTabs = ['' => 'All', 'MIXED' => 'Mixed Section', 'BEEF' => 'Beef Section', 'PORK' => 'Pork Section', 'POULTRY' => 'Poultry Section', 'FISH' => 'Fish Section'];
            $baseQuery = ['view' => 1, 'report' => $selectedReport, 'month' => $selectedMonthValue];
        @endphp

        <section class="clerk-report-page">
            <header class="tenant-page-title clerk-report-title">
                <div>
                    <i class="bi bi-file-earmark-text"></i>
                    <div><h1>REPORT</h1><p>Dashboard | Report</p></div>
                </div>
            </header>

            @unless ($showReport)
                <section class="clerk-report-entry">
                    <form class="clerk-report-card" method="GET" action="{{ route('clerk.reports') }}">
                        <div class="clerk-report-banner">
                            <img src="{{ asset('assets/einspect/HOMEPAGE/Logo.png') }}" alt="">
                            <h2>CASH TICKET &amp; STALL RENTAL</h2>
                            <p>Collection Report</p>
                        </div>
                        <label>Report Type:
                            <span><i class="bi bi-file-earmark-text"></i><select name="report" required>
                                <option value="" disabled selected>Please select</option>
                                <option value="stall-rental">Stall Rental</option>
                                <option value="cash-ticket">Cash Ticket</option>
                            </select></span>
                        </label>
                        <label>Select Month:
                            <span><i class="bi bi-calendar3"></i><input type="month" name="month" placeholder="Select Month" required></span>
                        </label>
                        <input type="hidden" name="view" value="1">
                        <button class="button clerk-report-view-button">View Report</button>
                    </form>
                </section>
            @else
                <section class="clerk-report-sheet">
                    <form class="clerk-report-toolbar" method="GET" action="{{ route('clerk.reports') }}">
                        <input type="hidden" name="view" value="1">
                        <input type="hidden" name="report" value="{{ $selectedReport }}">
                        <label>Select Month and Year
                            <span><i class="bi bi-calendar3"></i><input type="month" name="month" value="{{ $selectedMonthValue }}"></span>
                        </label>
                        <button class="clerk-report-reload" type="submit"><i class="bi bi-arrow-clockwise"></i> Reload</button>
                        <div class="clerk-report-downloads">
                            <span>Download</span>
                            <a class="pdf" target="_blank" href="{{ route('reports.print', ['view' => 1, 'report' => $selectedReport, 'month' => $selectedMonthValue, 'section' => $currentSection]) }}" title="Print or save as PDF"><i class="bi bi-file-earmark-pdf-fill"></i></a>
                            <a class="word" href="{{ route('reports.office', ['report' => $selectedReport, 'format' => 'doc', 'month' => $selectedMonthValue, 'section' => $currentSection]) }}" title="Download Word"><i class="bi bi-file-earmark-word-fill"></i></a>
                            <a class="excel" href="{{ route('reports.office', ['report' => $selectedReport, 'format' => 'xls', 'month' => $selectedMonthValue, 'section' => $currentSection]) }}" title="Download Excel"><i class="bi bi-file-earmark-excel-fill"></i></a>
                            <button type="button" title="Print" onclick="window.print()"><i class="bi bi-printer-fill"></i> Print Report</button>
                        </div>
                    </form>
                    @if ($selectedReport === 'stall-rental')
                        <nav class="clerk-report-tabs">
                            <span>TOTAL:<strong>{{ $rows->count() }}</strong></span>
                            @foreach ($sectionTabs as $section => $label)
                                <a class="{{ $currentSection === $section ? 'active' : '' }}" href="{{ route('clerk.reports', array_merge($baseQuery, ['section' => $section])) }}">{{ $label }}</a>
                            @endforeach
                        </nav>
                    @endif

                    <article class="clerk-official-report">
                        <header>
                            <img src="{{ asset('assets/einspect/HOMEPAGE/Logo.png') }}" alt="">
                            <p>Republic of the Philippines<br>Office of the Municipal Treasurer<br><strong>MUNICIPALITY OF PANDAN</strong></p>
                        </header>
                        <h2>{{ $selectedReport === 'cash-ticket' ? 'LIST OF CASH TICKET COLLECTION OF ' : "LIST OF TENANT'S FEE IN STALL RENTAL OF " }}{{ strtoupper($reportMonth->format('F Y')) }}</h2>

                        <div class="clerk-report-table-wrap">
                            <table class="clerk-report-table">
                                <thead>
                                    @if ($selectedReport === 'cash-ticket')
                                        <tr><th>NO.</th><th>REFERENCE</th><th>COLLECTOR</th><th>STALL SECTION</th><th>NO. OF TICKETS</th><th>TOTAL COLLECTED</th><th>DATE COLLECTED</th><th>STATUS</th></tr>
                                    @else
                                        <tr><th>NO.</th><th>TENANT'S ID</th><th>TENANT</th><th>STALL<br>SECTION</th><th>STALL<br>NUMBER</th><th>STALL FEE</th><th>DATE OF<br>PAYMENT</th><th>PAYMENT<br>STATUS</th><th>SHORT<br>CHARGE/S</th></tr>
                                    @endif
                                </thead>
                                <tbody>
                                    @forelse ($rows as $row)
                                        @if ($selectedReport === 'cash-ticket')
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $row->collection_number }}</td>
                                                <td>{{ $row->collector?->full_name }}</td>
                                                <td>{{ str($row->stall_section)->title() }} Section</td>
                                                <td>{{ number_format($row->ticket_quantity) }}</td>
                                                <td>P{{ number_format((float) $row->amount, 2) }}</td>
                                                <td>{{ $row->collection_date->format('F d, Y') }}</td>
                                                <td><span class="clerk-payment-pill paid">{{ str($row->status)->title() }}</span></td>
                                            </tr>
                                        @else
                                            @php
                                                $tenant = $row->tenant;
                                                $application = $row->stallApplication;
                                                $stall = $application?->stall;
                                                $paymentStatus = $row->status === 'PAID'
                                                    ? 'PAID'
                                                    : (($row->status === 'OVERDUE' || $row->due_date->isPast()) ? 'OVERDUE' : 'UNPAID');
                                            @endphp
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>TEN-{{ $tenant?->created_at?->format('Y') ?? now()->year }}-{{ str_pad($tenant?->id ?? $row->tenant_id, 5, '0', STR_PAD_LEFT) }}</td>
                                                <td>{{ $tenant?->full_name ?? $application?->business_owner ?? 'Tenant' }}</td>
                                                <td>{{ str($stall?->section ?? $application?->preferred_section ?? 'Unassigned')->title() }} Section</td>
                                                <td>{{ $stall?->stall_number ? str_pad($stall->stall_number, 3, '0', STR_PAD_LEFT) : ($application?->preferred_stall_number ? str_pad($application->preferred_stall_number, 3, '0', STR_PAD_LEFT) : '---') }}</td>
                                                <td>P{{ number_format((float) $row->amount, 2) }}</td>
                                                <td>{{ ($row->paid_at ?? $row->due_date)->format('F d, Y') }}</td>
                                                <td><span class="clerk-payment-pill {{ strtolower($paymentStatus) }}">{{ str($paymentStatus)->title() }}</span></td>
                                                <td>{{ (float) $row->shortage_amount > 0 ? 'P'.number_format((float) $row->shortage_amount, 2) : 'None' }}</td>
                                            </tr>
                                        @endif
                                    @empty
                                        <tr><td colspan="{{ $selectedReport === 'cash-ticket' ? 8 : 9 }}" class="empty-state">No report data available.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <footer>
                            <strong>{{ strtoupper(auth()->user()->full_name) }}</strong>
                            <span>{{ auth()->user()->designation ?: 'Revenue Collector Clerk II' }}</span>
                        </footer>
                    </article>
                </section>
            @endunless
        </section>
    @else
    <section class="panel report-sheet">
        <div class="panel-heading">
            <div><span class="eyebrow">Municipality of Pandan</span><h2>{{ $pageTitle }}</h2></div>
            <form method="GET" class="report-picker">
                <select name="report" onchange="this.form.submit()">
                    @foreach (($allowedReports ?? ['stall-rental', 'cash-ticket', 'inspection', 'payments']) as $availableReport)
                        <option value="{{ $availableReport }}" @selected($report === $availableReport)>{{ str($availableReport)->replace('-', ' ')->title() }}</option>
                    @endforeach
                </select>
                <input type="month" name="month" value="{{ request('month') }}">
                @if ($report === 'inspection')
                    <select name="livestock"><option value="">All Livestock</option>@foreach (['POULTRY','PORK','BEEF'] as $type)<option @selected(request('livestock') === $type)>{{ $type }}</option>@endforeach</select>
                @else
                    <select name="section"><option value="">All Sections</option>@foreach (['MIXED','BEEF','PORK','POULTRY','FISH'] as $section)<option @selected(request('section') === $section)>{{ $section }}</option>@endforeach</select>
                @endif
                <button class="button button-outline"><i class="bi bi-arrow-clockwise"></i> Reload</button>
                <a href="{{ route('reports.csv', ['report' => $report]) }}" class="button button-outline"><i class="bi bi-file-earmark-spreadsheet"></i> CSV</a>
                <button type="button" class="button button-outline" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
            </form>
        </div>
        <div class="report-title">
            <img src="{{ asset('assets/einspect/HOMEPAGE/Logo.png') }}" alt="">
            <p>Republic of the Philippines<br>Province of Antique<br><strong>Municipality of Pandan</strong></p>
            <h3>{{ strtoupper(str_replace('-', ' ', $report)) }} REPORT OF {{ now()->format('F Y') }}</h3>
        </div>
        <div class="table-wrap">
            <table class="market-data-table">
                <thead>
                    @if ($report === 'cash-ticket')
                        <tr><th>Reference</th><th>Collector</th><th>Section</th><th>Tickets</th><th>Amount</th><th>Date</th></tr>
                    @elseif ($report === 'inspection')
                        <tr><th>Request</th><th>Owner</th><th>Livestock</th><th>Count</th><th>Result</th><th>Status</th></tr>
                    @elseif ($report === 'payments')
                        <tr><th>Reference</th><th>Tenant</th><th>Period</th><th>Amount</th><th>Due date</th><th>Status</th></tr>
                    @else
                        <tr><th>Application</th><th>Tenant</th><th>Business</th><th>Section</th><th>Stall</th><th>Status</th></tr>
                    @endif
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        @if ($report === 'cash-ticket')
                            <tr><td>{{ $row->collection_number }}</td><td>{{ $row->collector?->full_name }}</td><td>{{ $row->stall_section }}</td><td>{{ $row->ticket_quantity }}</td><td>₱{{ number_format($row->amount, 2) }}</td><td>{{ $row->collection_date->format('M d, Y') }}</td></tr>
                        @elseif ($report === 'inspection')
                            <tr><td>{{ $row->request_number }}</td><td>{{ $row->owner_name }}</td><td>{{ $row->livestock_type }}</td><td>{{ $row->animal_count }}</td><td>{{ $row->inspection_result ?? '—' }}</td><td>{{ $row->status }}</td></tr>
                        @elseif ($report === 'payments')
                            <tr><td>{{ $row->reference_number }}</td><td>{{ $row->tenant?->full_name }}</td><td>{{ $row->period_month->format('F Y') }}</td><td>₱{{ number_format($row->amount, 2) }}</td><td>{{ $row->due_date->format('M d, Y') }}</td><td>{{ $row->status }}</td></tr>
                        @else
                            <tr><td>{{ $row->application_number }}</td><td>{{ $row->tenant?->full_name }}</td><td>{{ $row->business_name }}</td><td>{{ $row->preferred_section }}</td><td>{{ $row->stall?->stall_number ?? '—' }}</td><td>{{ $row->status }}</td></tr>
                        @endif
                    @empty
                        <tr><td colspan="6" class="empty-state">No report data available.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
    @endif
@endsection
