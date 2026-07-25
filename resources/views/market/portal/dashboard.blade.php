@extends('market.layouts.portal')

@section('content')
    @if (auth()->user()->isRole('TENANT'))
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
    @endif
@endpush
