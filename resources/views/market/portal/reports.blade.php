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
                            <button type="button" title="Print" data-print-report><i class="bi bi-printer-fill"></i> Print Report</button>
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

                    <article class="clerk-official-report print-report-area">
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
                                                    : (($row->status === 'OVERDUE' || ($row->due_date?->isPast() ?? false)) ? 'OVERDUE' : 'UNPAID');
                                            @endphp
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>TEN-{{ $tenant?->created_at?->format('Y') ?? now()->year }}-{{ str_pad($tenant?->id ?? $row->tenant_id, 5, '0', STR_PAD_LEFT) }}</td>
                                                <td>{{ $tenant?->full_name ?? $application?->business_owner ?? 'Tenant' }}</td>
                                                <td>{{ str($stall?->section ?? $application?->preferred_section ?? 'Unassigned')->title() }} Section</td>
                                                <td>{{ $stall?->stall_number ? str_pad($stall->stall_number, 3, '0', STR_PAD_LEFT) : ($application?->preferred_stall_number ? str_pad($application->preferred_stall_number, 3, '0', STR_PAD_LEFT) : '---') }}</td>
                                                <td>P{{ number_format((float) $row->amount, 2) }}</td>
                                                <td>{{ ($row->paid_at ?? $row->due_date)?->format('F d, Y') ?? '-' }}</td>
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
    @elseif (auth()->user()->isRole(\App\Models\User::ROLE_TREASURER) || auth()->user()->isRole(\App\Models\User::ROLE_ADMINISTRATOR))
        @include('market.tenant.applications.css.header')
        @php
            $isAdminReport = auth()->user()->isRole(\App\Models\User::ROLE_ADMINISTRATOR);
            $showReport = request()->boolean('view');
            $selectedReport = $report;
            $selectedMonthValue = request('month') ?: now()->format('Y-m');
            $reportMonth = $selectedMonth ?? \Carbon\Carbon::createFromFormat('Y-m', $selectedMonthValue)->startOfMonth();
            $currentSection = strtoupper(request('section', ''));
            $selectedLivestock = strtoupper(request('livestock', ''));
            $sectionTabs = ['' => 'All', 'MIXED' => 'Mixed Section', 'BEEF' => 'Beef Section', 'PORK' => 'Pork Section', 'POULTRY' => 'Poultry Section', 'FISH' => 'Fish Section'];
            $baseQuery = ['view' => 1, 'report' => $selectedReport, 'month' => $selectedMonthValue];
            $reportScope = request('scope', in_array($selectedReport, ['cash-ticket', 'payments'], true) ? 'clerk' : 'treasurer');
            $reportRoute = $isAdminReport ? 'administrator.reports' : 'treasurer.reports';
            $isClerkStallReport = $reportScope === 'clerk' && $selectedReport === 'stall-rental';
            $isPaymentReport = $selectedReport === 'payments' || $isClerkStallReport;
            $isInspectionReport = $selectedReport === 'inspection';
            $reportQuery = array_filter([
                'view' => 1,
                'scope' => $reportScope,
                'report' => $selectedReport,
                'month' => $selectedMonthValue,
                'section' => $currentSection,
                'livestock' => $selectedLivestock,
            ], fn ($value) => filled($value));
        @endphp

        <section class="clerk-report-page treasurer-report-page {{ $isAdminReport ? 'administrator-report-page' : '' }}">
            <header class="tenant-page-title clerk-report-title">
                <div>
                    <i class="bi bi-file-earmark-text"></i>
                    <div><h1>REPORT</h1><p>Dashboard | Report</p></div>
                </div>
            </header>

            @unless ($showReport)
                <section class="treasurer-report-entry">
                    <div class="treasurer-report-card-shell">
                        <div class="treasurer-report-banner">
                            <i class="bi bi-file-earmark-text-fill"></i>
                            <div><h2>REPORT</h2><p>PUBLIC MARKET PANDAN, ANTIQUE</p></div>
                        </div>
                        <div class="treasurer-report-choice-grid">
                            <button type="button" class="treasurer-report-choice treasurer" data-open-dialog="treasurerReportDialog">
                                <img src="{{ asset('assets/einspect/USERS/2-Treasurer.png') }}" alt="">
                                <span>TREASURER</span>
                            </button>
                            <button type="button" class="treasurer-report-choice clerk" data-open-dialog="clerkReportDialog">
                                <img src="{{ asset('assets/einspect/USERS/3-Collector Clerk.png') }}" alt="">
                                <span>CLERK</span>
                            </button>
                            @if ($isAdminReport)
                                <button type="button" class="treasurer-report-choice inspector" data-open-dialog="inspectorReportDialog">
                                    <img src="{{ asset('assets/einspect/USERS/4-Sanitary Inspector.png') }}" alt="">
                                    <span>INSPECTOR</span>
                                </button>
                            @endif
                        </div>
                    </div>
                </section>

                <dialog id="treasurerReportDialog" class="market-dialog treasurer-report-selector-dialog">
                    <form method="GET" action="{{ route($reportRoute) }}">
                        <button type="button" class="tenant-dialog-close" data-close-dialog><i class="bi bi-x-circle-fill"></i></button>
                        <div class="treasurer-report-selector-hero">
                            <img src="{{ asset('assets/einspect/USERS/2-Treasurer.png') }}" alt="">
                            <div><h2>TREASURER</h2><p>LIST OF TENANTS AND REQUISITION AND COLLECTION OF DEPOSIT</p></div>
                        </div>
                        <label>Report Type:
                            <span><i class="bi bi-file-earmark-text-fill"></i><select name="report" required>
                                <option value="" disabled selected>Select Report Type</option>
                                <option value="stall-rental">List of Tenants</option>
                                <option value="payments">Requisition and Collection of Deposit</option>
                            </select></span>
                        </label>
                        <label>Select Date:
                            <span><i class="bi bi-calendar3"></i><input type="month" name="month" value="{{ now()->format('Y-m') }}" required></span>
                        </label>
                        <input type="hidden" name="scope" value="treasurer">
                        <input type="hidden" name="view" value="1">
                        <button class="button clerk-report-view-button">View Report</button>
                    </form>
                </dialog>

                <dialog id="clerkReportDialog" class="market-dialog treasurer-report-selector-dialog">
                    <form method="GET" action="{{ route($reportRoute) }}">
                        <button type="button" class="tenant-dialog-close" data-close-dialog><i class="bi bi-x-circle-fill"></i></button>
                        <div class="treasurer-report-selector-hero clerk">
                            <img src="{{ asset('assets/einspect/USERS/3-Collector Clerk.png') }}" alt="">
                            <div><h2>CLERK</h2><p>CASH TICKET AND STALL RENTAL COLLECTION REPORT</p></div>
                        </div>
                        <label>Report Type:
                            <span><i class="bi bi-file-earmark-text-fill"></i><select name="report" required>
                                <option value="" disabled selected>Select Report Type</option>
                                <option value="stall-rental">Stall Rental</option>
                                <option value="cash-ticket">Cash Ticket</option>
                            </select></span>
                        </label>
                        <label>Select Date:
                            <span><i class="bi bi-calendar3"></i><input type="month" name="month" value="{{ now()->format('Y-m') }}" required></span>
                        </label>
                        <input type="hidden" name="scope" value="clerk">
                        <input type="hidden" name="view" value="1">
                        <button class="button clerk-report-view-button">View Report</button>
                    </form>
                </dialog>

                @if ($isAdminReport)
                    <dialog id="inspectorReportDialog" class="market-dialog treasurer-report-selector-dialog">
                        <form method="GET" action="{{ route($reportRoute) }}">
                            <button type="button" class="tenant-dialog-close" data-close-dialog><i class="bi bi-x-circle-fill"></i></button>
                            <div class="treasurer-report-selector-hero inspector">
                                <img src="{{ asset('assets/einspect/USERS/4-Sanitary Inspector.png') }}" alt="">
                                <div><h2>INSPECTOR</h2><p>SLAUGHTERED LIVESTOCK INSPECTION REPORT</p></div>
                            </div>
                            <label>Slaughtered Livestock Report Type:
                                <span><i class="bi bi-file-earmark-text-fill"></i><select name="livestock" required>
                                    <option value="" disabled selected>Select Report Type</option>
                                    <option value="POULTRY">Poultry Slaughtered</option>
                                    <option value="PORK">Pork Slaughtered</option>
                                    <option value="BEEF">Beef Slaughtered</option>
                                </select></span>
                            </label>
                            <label>Month &amp; Year:
                                <span><i class="bi bi-calendar3"></i><input type="month" name="month" value="{{ now()->format('Y-m') }}" required></span>
                            </label>
                            <input type="hidden" name="report" value="inspection">
                            <input type="hidden" name="scope" value="inspector">
                            <input type="hidden" name="view" value="1">
                            <button class="button clerk-report-view-button">View Report</button>
                        </form>
                    </dialog>
                @endif
            @else
                <section class="clerk-report-sheet treasurer-report-sheet">
                    <form class="clerk-report-toolbar" method="GET" action="{{ route($reportRoute) }}">
                        <input type="hidden" name="view" value="1">
                        <input type="hidden" name="scope" value="{{ $reportScope }}">
                        <input type="hidden" name="report" value="{{ $selectedReport }}">
                        @if ($isInspectionReport)
                            <input type="hidden" name="livestock" value="{{ $selectedLivestock }}">
                        @endif
                        <label>Select Month and Year
                            <span><i class="bi bi-calendar3"></i><input type="month" name="month" value="{{ $selectedMonthValue }}"></span>
                        </label>
                        <button class="clerk-report-reload" type="submit"><i class="bi bi-arrow-clockwise"></i> Reload</button>
                        <div class="clerk-report-downloads">
                            <span>Download</span>
                            <a class="pdf" target="_blank" href="{{ route('reports.print', $reportQuery) }}" title="Print or save as PDF"><i class="bi bi-file-earmark-pdf-fill"></i></a>
                            <a class="word" href="{{ route('reports.office', array_merge($reportQuery, ['format' => 'doc'])) }}" title="Download Word"><i class="bi bi-file-earmark-word-fill"></i></a>
                            <a class="excel" href="{{ route('reports.office', array_merge($reportQuery, ['format' => 'xls'])) }}" title="Download Excel"><i class="bi bi-file-earmark-excel-fill"></i></a>
                            <button type="button" title="Print" data-print-report><i class="bi bi-printer-fill"></i> Print Report</button>
                        </div>
                    </form>
                    @if (! $isInspectionReport && in_array($selectedReport, ['stall-rental', 'payments'], true))
                        <nav class="clerk-report-tabs">
                            <span>TOTAL:<strong>{{ $rows->count() }}</strong></span>
                            @foreach ($sectionTabs as $section => $label)
                                <a class="{{ $currentSection === $section ? 'active' : '' }}" href="{{ route($reportRoute, array_merge($baseQuery, ['scope' => $reportScope, 'section' => $section])) }}">{{ $label }}</a>
                            @endforeach
                        </nav>
                    @endif

                    <article class="clerk-official-report print-report-area">
                        <header>
                            <img src="{{ asset('assets/einspect/HOMEPAGE/Logo.png') }}" alt="">
                            @if ($isInspectionReport)
                                <p>Department of Health<br>Office of the Municipal Health Officer<br><strong>MUNICIPALITY OF PANDAN</strong></p>
                            @else
                                <p>Republic of the Philippines<br>Office of the Municipal Treasurer<br><strong>MUNICIPALITY OF PANDAN</strong></p>
                            @endif
                        </header>
                        <h2>
                            @if ($selectedReport === 'cash-ticket')
                                LIST OF CASH TICKET COLLECTION OF {{ strtoupper($reportMonth->format('F Y')) }}
                            @elseif ($isInspectionReport)
                                LIST OF {{ $selectedLivestock ?: 'LIVESTOCK' }} SLAUGHTERED INSPECTED REPORT OF {{ strtoupper($reportMonth->format('F Y')) }}
                            @elseif ($selectedReport === 'payments')
                                REPORT OF COLLECTIONS AND DEPOSITS OF {{ strtoupper($reportMonth->format('F Y')) }}
                            @elseif ($isPaymentReport)
                                LIST OF TENANT'S FEE IN STALL RENTAL OF {{ strtoupper($reportMonth->format('F Y')) }}
                            @else
                                LIST OF TENANTS {{ $currentSection ? 'IN '.strtoupper(str($currentSection)->title()).' SECTION ' : '' }}REPORT OF {{ strtoupper($reportMonth->format('F Y')) }}
                            @endif
                        </h2>

                        <div class="clerk-report-table-wrap">
                            <table class="clerk-report-table">
                                <thead>
                                    @if ($selectedReport === 'cash-ticket')
                                        <tr><th>NO.</th><th>REFERENCE</th><th>COLLECTOR</th><th>STALL SECTION</th><th>NO. OF TICKETS</th><th>TOTAL COLLECTED</th><th>DATE COLLECTED</th><th>STATUS</th></tr>
                                    @elseif ($isInspectionReport)
                                        @if ($selectedLivestock === 'POULTRY')
                                            <tr><th>NO.</th><th>OWNER</th><th>ADDRESS</th><th>TYPE OF POULTRY</th><th>NUMBER OF SLAUGHTERED</th><th>INSPECTION RESULT</th><th>DATE AND TIME OF INSPECTION</th></tr>
                                        @else
                                            <tr><th>NO.</th><th>OWNER</th><th>ADDRESS</th><th>AGE</th><th>WEIGHT</th><th>NUMBER OF SLAUGHTERED</th><th>INSPECTION RESULT</th><th>DATE AND TIME OF INSPECTION</th></tr>
                                        @endif
                                    @elseif ($isPaymentReport)
                                        <tr><th>NO.</th><th>TENANT'S ID</th><th>TENANT</th><th>STALL<br>SECTION</th><th>STALL<br>NUMBER</th><th>STALL FEE</th><th>DATE OF<br>PAYMENT</th><th>PAYMENT<br>STATUS</th><th>SHORT<br>CHARGE/S</th></tr>
                                    @else
                                        <tr><th>NO.</th><th>TENANT'S ID</th><th>TENANT</th><th>ADDRESS</th><th>CONTACT<br>NUMBER</th><th>STALL<br>SECTION</th><th>STALL<br>NUMBER</th><th>STATUS</th></tr>
                                    @endif
                                </thead>
                                <tbody>
                                    @forelse ($rows as $row)
                                        @if ($selectedReport === 'cash-ticket')
                                            <tr><td>{{ $loop->iteration }}</td><td>{{ $row->collection_number }}</td><td>{{ $row->collector?->full_name }}</td><td>{{ str($row->stall_section)->title() }} Section</td><td>{{ number_format($row->ticket_quantity) }}</td><td>P{{ number_format((float) $row->amount, 2) }}</td><td>{{ $row->collection_date->format('F d, Y') }}</td><td><span class="clerk-payment-pill paid">{{ str($row->status)->title() }}</span></td></tr>
                                        @elseif ($isInspectionReport)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $row->owner_name }}</td>
                                                <td>{{ $row->address }}</td>
                                                @if ($selectedLivestock === 'POULTRY')
                                                    <td>{{ $row->breed ?: 'Chicken' }}</td>
                                                @else
                                                    <td>{{ $row->animal_age ?: '-' }}</td>
                                                    <td>{{ $row->live_weight !== null ? number_format((float) $row->live_weight, 2).' kg' : '-' }}</td>
                                                @endif
                                                <td>{{ $row->animal_count }}</td>
                                                <td>{{ match($row->inspection_result) { 'PASSED' => 'Passed with Human Consumption', 'CONDEMNED' => 'Condemned', 'REINSPECTION' => 'For Further Examination', default => '-' } }}</td>
                                                <td>{{ $row->scheduled_at?->format('F d, Y | g:i A') ?? '-' }}</td>
                                            </tr>
                                        @elseif ($isPaymentReport)
                                            @php
                                                $tenant = $row->tenant;
                                                $application = $row->stallApplication;
                                                $stall = $application?->stall;
                                                $paymentStatus = $row->status === 'PAID' ? 'PAID' : (($row->status === 'OVERDUE' || ($row->due_date?->isPast() ?? false)) ? 'OVERDUE' : 'UNPAID');
                                            @endphp
                                            <tr><td>{{ $loop->iteration }}</td><td>TEN-{{ $tenant?->created_at?->format('Y') ?? now()->year }}-{{ str_pad($tenant?->id ?? $row->tenant_id, 5, '0', STR_PAD_LEFT) }}</td><td>{{ $tenant?->full_name ?? $application?->business_owner ?? 'Tenant' }}</td><td>{{ str($stall?->section ?? $application?->preferred_section ?? 'Unassigned')->title() }} Section</td><td>{{ $stall?->stall_number ? str_pad($stall->stall_number, 3, '0', STR_PAD_LEFT) : ($application?->preferred_stall_number ? str_pad($application->preferred_stall_number, 3, '0', STR_PAD_LEFT) : '---') }}</td><td>P{{ number_format((float) $row->amount, 2) }}</td><td>{{ ($row->paid_at ?? $row->due_date)?->format('F d, Y') ?? '-' }}</td><td><span class="clerk-payment-pill {{ strtolower($paymentStatus) }}">{{ str($paymentStatus)->title() }}</span></td><td>{{ (float) $row->shortage_amount > 0 ? 'P'.number_format((float) $row->shortage_amount, 2) : 'None' }}</td></tr>
                                        @else
                                            @php $tenant = $row->tenant; $stall = $row->stall; @endphp
                                            <tr><td>{{ $loop->iteration }}</td><td>TEN-{{ $tenant?->created_at?->format('Y') ?? now()->year }}-{{ str_pad($tenant?->id ?? $row->tenant_id, 5, '0', STR_PAD_LEFT) }}</td><td>{{ $tenant?->full_name ?? $row->business_owner ?? 'Tenant' }}</td><td>{{ $tenant?->address ?? $row->business_address ?? 'Pandan, Antique' }}</td><td>{{ $tenant?->phone_num ?? $row->contact_number ?? '-' }}</td><td>{{ str($stall?->section ?? $row->preferred_section ?? 'Unassigned')->title() }} Section</td><td>{{ $stall?->stall_number ? str_pad($stall->stall_number, 3, '0', STR_PAD_LEFT) : ($row->preferred_stall_number ? str_pad($row->preferred_stall_number, 3, '0', STR_PAD_LEFT) : '---') }}</td><td><span class="clerk-payment-pill paid">{{ str($tenant?->status ?? $row->status)->title() }}</span></td></tr>
                                        @endif
                                    @empty
                                        <tr><td colspan="{{ $isInspectionReport && $selectedLivestock === 'POULTRY' ? 7 : ($isPaymentReport ? 9 : 8) }}" class="empty-state">No report data available.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <footer>
                            <strong>{{ strtoupper(auth()->user()->full_name) }}</strong>
                            <span>{{ $isInspectionReport ? 'Rural Sanitary Inspector I' : ($isClerkStallReport || $selectedReport === 'cash-ticket' ? 'Revenue Collector Clerk II' : 'Municipal Treasurer') }}</span>
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
                            <tr><td>{{ $row->reference_number }}</td><td>{{ $row->tenant?->full_name }}</td><td>{{ $row->period_month?->format('F Y') ?? '-' }}</td><td>₱{{ number_format($row->amount, 2) }}</td><td>{{ $row->due_date?->format('M d, Y') ?? '-' }}</td><td>{{ $row->status }}</td></tr>
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

@push('scripts')
    <script>
        document.querySelectorAll('[data-print-report]').forEach((button) => {
            button.addEventListener('click', () => {
                document.body.classList.add('print-report-only');
                window.print();
            });
        });

        window.addEventListener('afterprint', () => {
            document.body.classList.remove('print-report-only');
        });
    </script>
@endpush
