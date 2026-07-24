@extends('market.layouts.portal')

@section('content')
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
                <div>
                    <span class="eyebrow">Overview</span>
                    <h2>Recent Records</h2>
                </div>
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
                                <tr>
                                    <td>{{ $row->application_number }}</td>
                                    <td>{{ $row->tenant?->full_name ?? $row->business_name }}</td>
                                    <td>{{ $row->preferred_section }}</td>
                                    <td><span class="status status-{{ strtolower($row->status) }}">{{ $row->status }}</span></td>
                                    <td>{{ $row->created_at->format('M d, Y') }}</td>
                                </tr>
                            @elseif ($rowType === 'inspections')
                                <tr>
                                    <td>{{ $row->request_number }}</td>
                                    <td>{{ $row->owner_name }}</td>
                                    <td>{{ $row->livestock_type }}</td>
                                    <td>{{ $row->scheduled_at->format('M d, Y g:i A') }}</td>
                                    <td><span class="status status-{{ strtolower($row->status) }}">{{ $row->status }}</span></td>
                                </tr>
                            @else
                                <tr>
                                    <td>{{ $row->collection_number }}</td>
                                    <td>{{ $row->collector?->full_name }}</td>
                                    <td>{{ $row->stall_section }}</td>
                                    <td>₱{{ number_format($row->amount, 2) }}</td>
                                    <td><span class="status status-{{ strtolower($row->status) }}">{{ $row->status }}</span></td>
                                </tr>
                            @endif
                        @empty
                            <tr><td colspan="5" class="empty-state">No records yet. Seed the database or add the first record.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <aside class="panel chart-panel">
            <div class="panel-heading"><div><span class="eyebrow">Monthly</span><h2>{{ $chartTitle }}</h2></div></div>
            <div class="bar-chart" aria-label="Decorative activity chart">
                @foreach ([46, 72, 38, 84, 58, 92, 67, 75, 52, 88, 64, 79] as $height)
                    <span style="height: {{ $height }}%"></span>
                @endforeach
            </div>
            <div class="chart-months"><span>Jan</span><span>Apr</span><span>Jul</span><span>Oct</span><span>Dec</span></div>
        </aside>
    </div>
@endsection
