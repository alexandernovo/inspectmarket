@extends('market.layouts.portal')

@section('content')
    @if ($area === null)
        <section class="record-selector panel">
            <div class="workflow-title"><i class="bi bi-folder2-open"></i><div><span>PUBLIC MARKET PANDAN, ANTIQUE</span><h2>{{ strtoupper($roleRecord) }} RECORDS</h2></div></div>
            <p>Select a record type to inspect the same operational records shown in that role's portal.</p>
            <div class="record-selector-grid">
                @foreach ($areas as $recordArea)
                    <a href="{{ route('administrator.records', ['role' => $roleRecord, 'area' => $recordArea]) }}">
                        <i class="bi {{ $recordArea === 'cash-ticket' ? 'bi-ticket-perforated' : ($recordArea === 'inspection' ? 'bi-clipboard2-pulse' : ($recordArea === 'report' ? 'bi-file-earmark-bar-graph' : 'bi-shop')) }}"></i>
                        <strong>{{ str($recordArea)->replace('-', ' ')->title() }}</strong>
                        <span>View</span>
                    </a>
                @endforeach
                <a href="{{ route('administrator.members', $roleRecord) }}"><i class="bi bi-people"></i><strong>Accounts</strong><span>View</span></a>
            </div>
        </section>
    @elseif ($area === 'report')
        <section class="record-selector panel">
            <div class="workflow-title"><i class="bi bi-file-earmark-bar-graph"></i><div><span>REPORT</span><h2>{{ strtoupper($roleRecord) }} REPORTS</h2></div></div>
            <div class="record-selector-grid">
                @foreach ($roleRecord === 'inspector' ? ['inspection'] : ['cash-ticket', 'stall-rental'] as $report)
                    <a href="{{ route('administrator.reports', ['report' => $report]) }}"><i class="bi bi-file-earmark-text"></i><strong>{{ str($report)->replace('-', ' ')->title() }}</strong><span>View Report</span></a>
                @endforeach
            </div>
        </section>
    @else
        <section class="panel">
            <div class="panel-heading">
                <div><span class="eyebrow">{{ ucfirst($roleRecord) }} Records</span><h2>{{ str($area)->replace('-', ' ')->title() }}</h2></div>
                <a href="{{ route('administrator.records', $roleRecord) }}" class="button button-outline">Record Types</a>
            </div>
            <div class="table-wrap">
                <table class="market-data-table">
                    @if ($area === 'cash-ticket')
                        <thead><tr><th>Reference</th><th>Collector</th><th>Section</th><th>Tickets</th><th>Amount</th><th>Date</th><th>Status</th></tr></thead>
                        <tbody>@foreach ($rows as $row)<tr><td>{{ $row->collection_number }}</td><td>{{ $row->collector?->full_name }}</td><td>{{ $row->stall_section }}</td><td>{{ $row->ticket_quantity }}</td><td>₱{{ number_format($row->amount, 2) }}</td><td>{{ $row->collection_date->format('M d, Y') }}</td><td><span class="status status-{{ strtolower($row->status) }}">{{ $row->status }}</span></td></tr>@endforeach</tbody>
                    @elseif ($area === 'inspection')
                        <thead><tr><th>Request</th><th>Owner</th><th>Address</th><th>Livestock</th><th>Schedule</th><th>Status</th><th>Action</th></tr></thead>
                        <tbody>@foreach ($rows as $row)<tr><td>{{ $row->request_number }}</td><td>{{ $row->owner_name }}</td><td>{{ $row->address }}</td><td>{{ $row->livestock_type }}</td><td>{{ $row->scheduled_at->format('M d, Y g:i A') }}</td><td><span class="status status-{{ strtolower($row->status) }}">{{ $row->status }}</span></td><td><a class="table-action" href="{{ route('inspections.show', $row) }}"><i class="bi bi-eye"></i></a></td></tr>@endforeach</tbody>
                    @elseif ($area === 'payment')
                        <thead><tr><th>Reference</th><th>Tenant</th><th>Period</th><th>Amount</th><th>Due Date</th><th>Status</th><th>Receipt</th></tr></thead>
                        <tbody>@foreach ($rows as $row)<tr><td>{{ $row->reference_number }}</td><td>{{ $row->tenant?->full_name }}</td><td>{{ $row->period_month->format('F Y') }}</td><td>₱{{ number_format($row->amount, 2) }}</td><td>{{ $row->due_date->format('M d, Y') }}</td><td><span class="status status-{{ strtolower($row->status) }}">{{ $row->status }}</span></td><td><a class="table-action" href="{{ route('payments.receipt', $row) }}"><i class="bi bi-receipt"></i></a></td></tr>@endforeach</tbody>
                    @else
                        <thead><tr><th>Tenant ID</th><th>Tenant</th><th>Section</th><th>Stall</th><th>Fee</th><th>Status</th><th>Action</th></tr></thead>
                        <tbody>@foreach ($rows as $row)<tr><td>{{ $row->application_number }}</td><td>{{ $row->tenant?->full_name }}</td><td>{{ $row->preferred_section }}</td><td>{{ $row->stall?->stall_number ?? '—' }}</td><td>₱{{ number_format($row->stall?->monthly_rate ?? 0, 2) }}</td><td><span class="status status-{{ strtolower($row->status) }}">{{ $row->status }}</span></td><td><a class="table-action" href="{{ route('stall-applications.show', $row) }}"><i class="bi bi-eye"></i></a></td></tr>@endforeach</tbody>
                    @endif
                </table>
            </div>
        </section>
    @endif
@endsection
