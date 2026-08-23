@extends('market.layouts.portal')

@section('content')
    @if (auth()->user()->isRole('ADMINISTRATOR'))
        <section class="administrator-dashboard">
            <header class="administrator-dashboard-title">
                <i class="bi bi-grid-fill"></i>
                <div>
                    <h1>DASHBOARD</h1>
                    <p>E-Inspect Public Market Inspection Recording Management System</p>
                </div>
            </header>

            <div class="administrator-dashboard-stats">
                @foreach ($stats as $stat)
                    @php $statImageClass = isset($stat['image']) ? str($stat['image'])->afterLast('/')->beforeLast('.')->slug() : null; @endphp
                    <article class="administrator-dashboard-stat {{ $stat['tone'] }}">
                        <div class="administrator-stat-icon {{ $statImageClass ? 'administrator-stat-icon-'.$statImageClass : '' }}">
                            @if (!empty($stat['image']))
                                <img src="{{ asset('assets/einspect/'.$stat['image']) }}" alt="">
                            @else
                                <i class="bi {{ $stat['icon'] }}"></i>
                            @endif
                        </div>
                        <div>
                            <span>{{ $stat['label'] }}</span>
                            @if (!empty($stat['sublabel']))<small>( {{ $stat['sublabel'] }} )</small>@endif
                            <strong>{{ $stat['value'] }}</strong>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="administrator-pie-grid">
                <article>
                    <h2>TOTAL COLLECTION</h2>
                    <div class="administrator-pie blue"></div>
                    <small>Cash Ticket / Stall Rental</small>
                </article>
                <article>
                    <h2>TOTAL INSPECTION</h2>
                    <div class="administrator-pie green"></div>
                    <small>Poultry / Pork / Beef</small>
                </article>
                <article>
                    <h2>TOTAL STALL RENTAL</h2>
                    <div class="administrator-pie gold"></div>
                    <small>Paid / Unpaid / Overdue</small>
                </article>
                <article>
                    <h2>TOTAL STALLS</h2>
                    <div class="administrator-pie red"></div>
                    <small>Available / Occupied</small>
                </article>
            </div>

            <section class="administrator-dashboard-chart">
                <header>
                    <h2>E-INSPECT PUBLIC MARKET DATA CHART</h2>
                    <div>
                        <select><option>Select Category</option></select>
                        <select><option>All Months</option></select>
                        <select><option>{{ now()->year }}</option></select>
                    </div>
                </header>
                <div class="administrator-chart-plot">
                    @foreach (($chartValues ?? collect(array_fill(0, 12, 8))) as $height)
                        <div>
                            <span style="height: {{ $height }}%"></span>
                            <small>{{ now()->startOfYear()->addMonths($loop->index)->format('M') }}</small>
                        </div>
                    @endforeach
                </div>
            </section>
        </section>
    @elseif (auth()->user()->isRole('TENANT'))
        <section class="tenant-dashboard">
            <header class="tenant-dashboard-title">
                <i class="bi bi-grid-fill"></i>
                <div>
                    <h1>DASHBOARD</h1>
                    <p>Stall Rental and Payment Record Management</p>
                </div>
            </header>

            <div class="tenant-dashboard-stats">
                @foreach ($stats as $stat)
                    <article class="tenant-dashboard-stat {{ $stat['tone'] }}">
                        <div class="tenant-stat-visual">
                            @if (!empty($stat['image']))
                                <img src="{{ asset('assets/einspect/'.$stat['image']) }}" alt="">
                            @else
                                <i class="bi {{ $stat['icon'] }}"></i>
                            @endif
                        </div>
                        <div>
                            <span>{{ $stat['label'] }}</span>
                            <small>( Stall Rental )</small>
                            <strong>{{ $stat['value'] }}</strong>
                        </div>
                    </article>
                @endforeach
            </div>

            <section class="tenant-dashboard-records">
                <div class="tenant-dashboard-ajax-toolbar"></div>
                <div class="table-wrap">
                    <table id="tenantDashboardTable" class="tenant-dashboard-table">
                        <thead>
                            <tr>
                                <th>NO.</th>
                                <th>OWNER</th>
                                <th>ADDRESS</th>
                                <th>CONTACT NUMBER</th>
                                <th>TYPE OF SLAUGHTERED INSPECT</th>
                                <th>DATE AND TIME OF INSPECTION</th>
                                <th>REQUEST STATUS</th>
                                <th>ACTION</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </section>

            <section class="tenant-dashboard-chart">
                <header>
                    <h2>{{ $chartTitle }}</h2>
                    <div>
                        <span>All Months <i class="bi bi-caret-down-fill"></i></span>
                        <span>{{ now()->year }} <i class="bi bi-caret-down-fill"></i></span>
                    </div>
                </header>
                <div class="tenant-chart-plot">
                    @foreach (($chartValues ?? collect(array_fill(0, 12, 4))) as $height)
                        <div>
                            <span style="height: {{ $height }}%" title="{{ ($chartTotals[$loop->index] ?? 0) }} rental application(s)"></span>
                            <small>{{ now()->startOfYear()->addMonths($loop->index)->format('F') }}</small>
                        </div>
                    @endforeach
                </div>
            </section>
        </section>
    @elseif (auth()->user()->isRole('CLERK'))
        <section class="clerk-dashboard">
            <header class="clerk-dashboard-title">
                <i class="bi bi-grid-fill"></i>
                <div>
                    <h1>DASHBOARD</h1>
                    <p>Cash Tickets and Stall Rental Collection Fee Recording Management System</p>
                </div>
            </header>

            <div class="clerk-dashboard-stats">
                @foreach ($stats as $stat)
                    @php
                        $statImageClass = str($stat['image'])->afterLast('/')->beforeLast('.')->slug();
                        $statDialog = match ((string) $statImageClass) {
                            'unpaid-tenant' => 'clerkUnpaidTenantsDialog',
                            'paid-tenant' => 'clerkPaidTenantsDialog',
                            default => null,
                        };
                    @endphp
                    <article class="clerk-dashboard-stat {{ $stat['tone'] }} {{ $statDialog ? 'clerk-dashboard-stat-clickable' : '' }}" @if($statDialog) role="button" tabindex="0" data-open-dialog="{{ $statDialog }}" @endif>
                        <div class="clerk-stat-visual clerk-stat-visual-{{ $statImageClass }}">
                            <img src="{{ asset('assets/einspect/'.$stat['image']) }}" alt="">
                        </div>
                        <div>
                            <span>{{ $stat['label'] }}</span>
                            <small>( {{ $stat['sublabel'] }} )</small>
                            <strong>{{ $stat['value'] }}</strong>
                        </div>
                    </article>
                @endforeach
            </div>

            <section class="clerk-dashboard-records">
                <div class="clerk-dashboard-filters">
                    <select id="clerkDashboardLength" aria-label="Rows per page"><option value="5">5 v</option><option value="10" selected>10 v</option><option value="25">25 v</option></select>
                    <label>From:<input type="date" id="clerkDashboardFrom"></label>
                    <label>To:<input type="date" id="clerkDashboardTo"></label>
                    <button type="button" id="clerkDashboardFilter"><i class="bi bi-funnel-fill"></i> Filter</button>
                    <button type="button" id="clerkDashboardReload"><i class="bi bi-arrow-clockwise"></i> Reload</button>
                    <label class="clerk-dashboard-search">Search:<input type="search" id="clerkDashboardSearch"></label>
                </div>
                <div class="table-wrap">
                    <table id="clerkDashboardTable" class="clerk-dashboard-table">
                        <thead>
                            <tr>
                                <th>NO.</th>
                                <th>TENANT</th>
                                <th>STALL NUMBER</th>
                                <th>STALL SECTION</th>
                                <th>STALL FEE</th>
                                <th>PAYMENT STATUS</th>
                                <th>DATE PAYMENT</th>
                                <th>SHORT CHARGE/S</th>
                                <th>ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $payment)
                                <tr data-payment-date="{{ $payment->paid_at?->toDateString() ?? $payment->due_date?->toDateString() ?? '' }}">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $payment->tenant?->full_name ?? 'Tenant' }}</td>
                                    <td>{{ $payment->stallApplication?->stall?->stall_number ?? 'Unassigned' }}</td>
                                    <td>{{ str($payment->stallApplication?->stall?->section ?? $payment->stallApplication?->preferred_section ?? 'Unassigned')->title() }} Section</td>
                                    <td>P{{ number_format($payment->amount, 2) }}</td>
                                    <td><span class="status status-{{ strtolower($payment->status) }}">{{ str($payment->status)->title() }}</span></td>
                                    <td>{{ ($payment->paid_at ?? $payment->due_date)?->format('F d, Y') ?? '-' }}</td>
                                    <td>{{ $payment->shortage_amount > 0 ? 'P'.number_format($payment->shortage_amount, 2) : 'None' }}</td>
                                    <td>
                                        <div class="inline-actions">
                                            <a class="table-action edit" href="{{ route('payments.receipt', $payment) }}" target="_blank" title="View OR"><i class="bi bi-pencil-fill"></i></a>
                                            @if ($payment->receipt_path)
                                                <a class="table-action reject" href="{{ route('payments.receipt-file', $payment) }}" target="_blank" title="View uploaded receipt"><i class="bi bi-receipt"></i></a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="empty-state">No stall rental payment records yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="clerk-dashboard-chart">
                <header>
                    <h2>{{ $chartTitle }}</h2>
                    <div>
                        <select id="clerkChartCategory" aria-label="Select category">
                            <option value="ALL">Select Category</option>
                            <option value="CASH_TICKET">Cash Ticket</option>
                            <option value="STALL_RENTAL">Stall Rental</option>
                        </select>
                        <select id="clerkChartMonth" aria-label="Select month">
                            <option value="ALL">All Months</option>
                            @foreach (range(1, 12) as $month)
                                <option value="{{ $month }}">{{ now()->startOfYear()->addMonths($month - 1)->format('F') }}</option>
                            @endforeach
                        </select>
                        <select id="clerkChartYear" aria-label="Select year">
                            @foreach (($chartYears ?? collect([now()->year])) as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                </header>
                <div class="clerk-chart-plot" data-clerk-chart-series='@json($chartSeries ?? [])'>
                    @foreach (($chartValues ?? collect(array_fill(0, 12, 6))) as $height)
                        <div>
                            <span style="height: {{ $height }}%" title="P{{ number_format((float) ($chartTotals[$loop->index] ?? 0), 2) }}"></span>
                            <small>{{ now()->startOfYear()->addMonths($loop->index)->format('F') }}</small>
                        </div>
                    @endforeach
                </div>
            </section>

            @php
                $clerkTenantDialogs = [
                    [
                        'id' => 'clerkUnpaidTenantsDialog',
                        'tone' => 'red',
                        'title' => 'STALL RENTAL',
                        'subtitle' => 'UNPAID TENANTS',
                        'date_label' => 'DUE DATE',
                        'rows' => $unpaidTenantPayments ?? collect(),
                    ],
                    [
                        'id' => 'clerkPaidTenantsDialog',
                        'tone' => 'green',
                        'title' => 'STALL RENTAL',
                        'subtitle' => 'PAID TENANTS',
                        'date_label' => 'DATE OF PAYMENT',
                        'rows' => $paidTenantPayments ?? collect(),
                    ],
                ];
            @endphp
            @foreach ($clerkTenantDialogs as $dialog)
                <dialog id="{{ $dialog['id'] }}" class="clerk-stat-dialog clerk-stat-dialog-{{ $dialog['tone'] }}">
                    <section>
                        <header>
                            <i class="bi bi-person-fill"></i>
                            <div><h2>{{ $dialog['title'] }}</h2><p>{{ $dialog['subtitle'] }}</p></div>
                            <button type="button" data-close-dialog aria-label="Close"><i class="bi bi-x-circle-fill"></i></button>
                        </header>
                        <div class="table-wrap">
                            <table class="clerk-stat-dialog-table">
                                <thead>
                                    <tr><th>NO.</th><th>TENANT</th><th>STALL NUMBER</th><th>STALL SECTION</th><th>STALL FEE</th><th>PAYMENT STATUS</th><th>{{ $dialog['date_label'] }}</th><th>SHORT CHARGE/S</th></tr>
                                </thead>
                                <tbody>
                                    @forelse ($dialog['rows'] as $payment)
                                        @php
                                            $displayStatus = $payment->status === 'PAID'
                                                ? 'PAID'
                                                : (($payment->due_date && $payment->due_date->isPast()) ? 'OVERDUE' : 'UNPAID');
                                            $displayDate = $payment->status === 'PAID'
                                                ? $payment->paid_at
                                                : $payment->due_date;
                                        @endphp
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $payment->tenant?->full_name ?? 'Tenant' }}</td>
                                            <td>{{ $payment->stallApplication?->stall?->stall_number ?? 'Unassigned' }}</td>
                                            <td>{{ str($payment->stallApplication?->stall?->section ?? $payment->stallApplication?->preferred_section ?? 'Unassigned')->title() }} Section</td>
                                            <td>P{{ number_format($payment->amount, 2) }}</td>
                                            <td><span class="status status-{{ strtolower($displayStatus) }}">{{ str($displayStatus)->title() }}</span></td>
                                            <td>{{ $displayDate?->format('F d, Y') ?? '-' }}</td>
                                            <td>{{ $payment->shortage_amount > 0 ? 'P'.number_format($payment->shortage_amount, 2) : 'None' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="8" class="empty-state">No {{ strtolower($dialog['subtitle']) }} records found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <footer>Showing 1 to {{ min(5, $dialog['rows']->count()) }} of {{ $dialog['rows']->count() }} entries <span>&lt; <b>1</b> &gt;</span></footer>
                    </section>
                </dialog>
            @endforeach
        </section>
    @elseif (auth()->user()->isRole('TREASURER'))
        <section class="treasurer-dashboard">
            <header class="treasurer-dashboard-title">
                <i class="bi bi-grid-fill"></i>
                <div>
                    <h1>DASHBOARD</h1>
                    <p>Cash Ticket and Stall Rental Revenue Management</p>
                </div>
            </header>

            <div class="treasurer-dashboard-stats">
                @foreach ($stats as $stat)
                    @php
                        $statImageClass = isset($stat['image']) ? str($stat['image'])->afterLast('/')->beforeLast('.')->slug() : null;
                        $statDialog = match ((string) $statImageClass) {
                            'unpaid-tenant' => 'treasurerUnpaidTenantsDialog',
                            'paid-tenant' => 'treasurerPaidTenantsDialog',
                            default => null,
                        };
                    @endphp
                    <article class="treasurer-dashboard-stat {{ $stat['tone'] }} {{ $statDialog ? 'treasurer-dashboard-stat-clickable' : '' }}" @if($statDialog) role="button" tabindex="0" data-open-dialog="{{ $statDialog }}" @endif>
                        <div class="treasurer-stat-visual {{ $statImageClass ? 'treasurer-stat-visual-'.$statImageClass : '' }}">
                            @if (!empty($stat['image']))
                                <img src="{{ asset('assets/einspect/'.$stat['image']) }}" alt="">
                            @else
                                <i class="bi {{ $stat['icon'] }}"></i>
                            @endif
                        </div>
                        <div>
                            <span>{{ $stat['label'] }}</span>
                            <small>( {{ $stat['sublabel'] }} )</small>
                            <strong>{{ $stat['value'] }}</strong>
                        </div>
                    </article>
                @endforeach
            </div>

            <section class="treasurer-dashboard-records">
                <div class="treasurer-dashboard-filters">
                    <select id="treasurerDashboardLength" aria-label="Rows per page"><option value="5">5 v</option><option value="10" selected>10 v</option><option value="25">25 v</option></select>
                    <label>From:<input type="date" id="treasurerDashboardFrom"></label>
                    <label>To:<input type="date" id="treasurerDashboardTo"></label>
                    <button type="button" id="treasurerDashboardFilter"><i class="bi bi-funnel-fill"></i> Filter</button>
                    <button type="button" id="treasurerDashboardReload"><i class="bi bi-arrow-clockwise"></i> Reload</button>
                    <label class="treasurer-dashboard-search">Search:<input type="search" id="treasurerDashboardSearch"></label>
                </div>
                <div class="table-wrap">
                    <table id="treasurerDashboardTable" class="treasurer-dashboard-table">
                        <thead>
                            <tr><th>NO.</th><th>APPLICATION</th><th>TENANT / BUSINESS</th><th>SECTION</th><th>STALL</th><th>STATUS</th><th>DATE SUBMITTED</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                <tr data-submitted-date="{{ $row->created_at->toDateString() }}">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $row->application_number }}</td>
                                    <td><strong>{{ $row->tenant?->full_name ?? $row->business_owner }}</strong><br><small>{{ $row->business_name }}</small></td>
                                    <td>{{ str($row->preferred_section)->title() }} Section</td>
                                    <td>{{ $row->stall?->stall_number ?? $row->preferred_stall_number ?? 'Unassigned' }}</td>
                                    <td><span class="status status-{{ strtolower($row->status) }}">{{ str($row->status)->title() }}</span></td>
                                    <td>{{ $row->created_at->format('F d, Y') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="empty-state">No stall rental applications yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="treasurer-dashboard-chart">
                <header>
                    <h2>{{ $chartTitle }}</h2>
                    <div>
                        <span>All Months <i class="bi bi-caret-down-fill"></i></span>
                        <span>{{ ($chartYears ?? collect([now()->year]))->first() }} <i class="bi bi-caret-down-fill"></i></span>
                    </div>
                </header>
                <div class="treasurer-chart-plot">
                    @foreach (($chartValues ?? collect(array_fill(0, 12, 6))) as $height)
                        <div>
                            <span style="height: {{ $height }}%" title="P{{ number_format((float) ($chartTotals[$loop->index] ?? 0), 2) }}"></span>
                            <small>{{ now()->startOfYear()->addMonths($loop->index)->format('F') }}</small>
                        </div>
                    @endforeach
                </div>
            </section>

            @php
                $treasurerTenantDialogs = [
                    [
                        'id' => 'treasurerUnpaidTenantsDialog',
                        'tone' => 'red',
                        'title' => 'STALL RENTAL',
                        'subtitle' => 'UNPAID TENANTS',
                        'date_label' => 'DUE DATE',
                        'rows' => $unpaidTenantPayments ?? collect(),
                    ],
                    [
                        'id' => 'treasurerPaidTenantsDialog',
                        'tone' => 'green',
                        'title' => 'STALL RENTAL',
                        'subtitle' => 'PAID TENANTS',
                        'date_label' => 'DATE OF PAYMENT',
                        'rows' => $paidTenantPayments ?? collect(),
                    ],
                ];
            @endphp
            @foreach ($treasurerTenantDialogs as $dialog)
                <dialog id="{{ $dialog['id'] }}" class="clerk-stat-dialog clerk-stat-dialog-{{ $dialog['tone'] }}">
                    <section>
                        <header>
                            <i class="bi bi-person-fill"></i>
                            <div><h2>{{ $dialog['title'] }}</h2><p>{{ $dialog['subtitle'] }}</p></div>
                            <button type="button" data-close-dialog aria-label="Close"><i class="bi bi-x-circle-fill"></i></button>
                        </header>
                        <div class="table-wrap">
                            <table class="clerk-stat-dialog-table">
                                <thead>
                                    <tr><th>NO.</th><th>TENANT</th><th>STALL NUMBER</th><th>STALL SECTION</th><th>STALL FEE</th><th>PAYMENT STATUS</th><th>{{ $dialog['date_label'] }}</th><th>SHORT CHARGE/S</th></tr>
                                </thead>
                                <tbody>
                                    @forelse ($dialog['rows'] as $payment)
                                        @php
                                            $displayStatus = $payment->status === 'PAID'
                                                ? 'PAID'
                                                : (($payment->due_date && $payment->due_date->isPast()) ? 'OVERDUE' : 'UNPAID');
                                            $displayDate = $payment->status === 'PAID'
                                                ? $payment->paid_at
                                                : $payment->due_date;
                                        @endphp
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $payment->tenant?->full_name ?? 'Tenant' }}</td>
                                            <td>{{ $payment->stallApplication?->stall?->stall_number ?? 'Unassigned' }}</td>
                                            <td>{{ str($payment->stallApplication?->stall?->section ?? $payment->stallApplication?->preferred_section ?? 'Unassigned')->title() }} Section</td>
                                            <td>P{{ number_format($payment->amount, 2) }}</td>
                                            <td><span class="status status-{{ strtolower($displayStatus) }}">{{ str($displayStatus)->title() }}</span></td>
                                            <td>{{ $displayDate?->format('F d, Y') ?? '-' }}</td>
                                            <td>{{ $payment->shortage_amount > 0 ? 'P'.number_format($payment->shortage_amount, 2) : 'None' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="8" class="empty-state">No {{ strtolower($dialog['subtitle']) }} records found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <footer>Showing 1 to {{ min(5, $dialog['rows']->count()) }} of {{ $dialog['rows']->count() }} entries <span>&lt; <b>1</b> &gt;</span></footer>
                    </section>
                </dialog>
            @endforeach
        </section>
    @else
        <div class="stat-grid">
            @foreach ($stats as $stat)
                <article class="stat-card">
                    <i class="bi {{ $stat['icon'] }}"></i>
                    <div><span>{{ $stat['label'] }}</span><strong>{{ $stat['value'] }}</strong></div>
                </article>
            @endforeach
        </div>

        <div class="dashboard-grid">
            <section class="panel">
                <div class="panel-heading">
                    <div><span class="eyebrow">Overview</span><h2>Recent Records</h2></div>
                    <span class="record-count">{{ $rows->count() }} shown</span>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            @if ($rowType === 'applications')
                                <tr><th>Reference</th><th>Tenant / Business</th><th>Section</th><th>Status</th><th>Date</th></tr>
                            @elseif ($rowType === 'inspections')
                                <tr><th>Reference</th><th>Owner</th><th>Livestock</th><th>Schedule</th><th>Status</th></tr>
                            @else
                                <tr><th>Reference</th><th>Collector</th><th>Section</th><th>Amount</th><th>Status</th></tr>
                            @endif
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                @if ($rowType === 'applications')
                                    <tr><td>{{ $row->application_number }}</td><td>{{ $row->tenant?->full_name ?? $row->business_name }}</td><td>{{ $row->preferred_section }}</td><td><span class="status status-{{ strtolower($row->status) }}">{{ $row->status }}</span></td><td>{{ $row->created_at->format('M d, Y') }}</td></tr>
                                @elseif ($rowType === 'inspections')
                                    <tr><td>{{ $row->request_number }}</td><td>{{ $row->owner_name }}</td><td>{{ $row->livestock_type }}</td><td>{{ $row->scheduled_at->format('M d, Y g:i A') }}</td><td><span class="status status-{{ strtolower($row->status) }}">{{ $row->status }}</span></td></tr>
                                @else
                                    <tr><td>{{ $row->collection_number }}</td><td>{{ $row->collector?->full_name }}</td><td>{{ $row->stall_section }}</td><td>P{{ number_format($row->amount, 2) }}</td><td><span class="status status-{{ strtolower($row->status) }}">{{ $row->status }}</span></td></tr>
                                @endif
                            @empty
                                <tr><td colspan="5" class="empty-state">No records yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <aside class="panel chart-panel">
                <div class="panel-heading"><div><span class="eyebrow">Monthly</span><h2>{{ $chartTitle }}</h2></div></div>
                <div class="bar-chart">
                    @foreach (($chartValues ?? collect(array_fill(0, 12, 4))) as $height)
                        <span style="height: {{ $height }}%"></span>
                    @endforeach
                </div>
                <div class="chart-months"><span>Jan</span><span>Apr</span><span>Jul</span><span>Oct</span><span>Dec</span></div>
            </aside>
        </div>
    @endif
@endsection

@push('scripts')
    @if (auth()->user()->isRole('TENANT'))
        @include('market.tenant.dashboard.js.dashboard')
    @elseif (auth()->user()->isRole('CLERK'))
        <script>
            $(function () {
                const table = $('#clerkDashboardTable').DataTable({
                    pageLength: 10,
                    lengthMenu: [5, 10, 25],
                    order: [],
                    dom: 'rtip'
                });

                $('#clerkDashboardLength').on('change', function () {
                    table.page.len(Number(this.value)).draw();
                });

                $('#clerkDashboardSearch').on('input', function () {
                    table.search(this.value).draw();
                });

                $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
                    if (settings.nTable.id !== 'clerkDashboardTable') return true;
                    const row = table.row(dataIndex).node();
                    const paymentDate = row?.dataset.paymentDate || '';
                    const from = $('#clerkDashboardFrom').val();
                    const to = $('#clerkDashboardTo').val();

                    return (!from || paymentDate >= from) && (!to || paymentDate <= to);
                });

                $('#clerkDashboardFilter').on('click', function () {
                    table.draw();
                });

                $('#clerkDashboardReload').on('click', function () {
                    $('#clerkDashboardFrom, #clerkDashboardTo, #clerkDashboardSearch').val('');
                    table.search('').draw();
                });

                const chart = document.querySelector('.clerk-chart-plot');
                const series = JSON.parse(chart?.dataset.clerkChartSeries || '{}');

                function renderClerkChart() {
                    if (!chart) return;
                    const category = $('#clerkChartCategory').val() || 'ALL';
                    const month = $('#clerkChartMonth').val() || 'ALL';
                    const values = series[category] || series.ALL || [];
                    const visibleValues = month === 'ALL'
                        ? values
                        : values.map((value, index) => index + 1 === Number(month) ? value : 0);
                    const maximum = Math.max(...visibleValues.map(Number), 1);

                    chart.querySelectorAll('span').forEach((bar, index) => {
                        const value = Number(visibleValues[index] || 0);
                        bar.style.height = Math.max(value > 0 ? 6 : 0, Math.round((value / maximum) * 100)) + '%';
                        bar.title = 'P' + value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    });
                }

                $('#clerkChartCategory, #clerkChartMonth, #clerkChartYear').on('change', renderClerkChart);
                renderClerkChart();
            });
        </script>
    @elseif (auth()->user()->isRole('TREASURER'))
        <script>
            $(function () {
                const table = $('#treasurerDashboardTable').DataTable({
                    pageLength: 10,
                    lengthMenu: [5, 10, 25],
                    order: [],
                    dom: 'rtip'
                });

                $('#treasurerDashboardLength').on('change', function () {
                    table.page.len(Number(this.value)).draw();
                });

                $('#treasurerDashboardSearch').on('input', function () {
                    table.search(this.value).draw();
                });

                $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
                    if (settings.nTable.id !== 'treasurerDashboardTable') return true;
                    const row = table.row(dataIndex).node();
                    const submittedDate = row?.dataset.submittedDate || '';
                    const from = $('#treasurerDashboardFrom').val();
                    const to = $('#treasurerDashboardTo').val();

                    return (!from || submittedDate >= from) && (!to || submittedDate <= to);
                });

                $('#treasurerDashboardFilter').on('click', function () {
                    table.draw();
                });

                $('#treasurerDashboardReload').on('click', function () {
                    $('#treasurerDashboardFrom, #treasurerDashboardTo, #treasurerDashboardSearch').val('');
                    table.search('').draw();
                });
            });
        </script>
    @endif
@endpush
