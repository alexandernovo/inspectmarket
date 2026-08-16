@extends('market.layouts.portal')

@section('content')
    @php
        $roleTitle = strtoupper($roleRecord);
        $roleSubtitle = match ($roleRecord) {
            'treasurer' => 'STALL MAP & LIST OF TENANTS',
            'clerk' => 'CASH TICKET AND STALL RENTAL COLLECTION FEE',
            'inspector' => 'SLAUGHTERED LIVESTOCK',
            'tenant' => "TENANT'S MONTHLY PAYMENT",
            default => 'RECORDS',
        };
        $roleImage = match ($roleRecord) {
            'treasurer' => '2-Treasurer.png',
            'clerk' => '3-Collector Clerk.png',
            'inspector' => '4-Sanitary Inspector.png',
            'tenant' => '5-Tenants.png',
            default => '1-Administrator.png',
        };
        $directRecordArea = $directRecordArea ?? null;
    @endphp

    @if ($area === null)
        <section class="administrator-record-entry">
            <header class="administrator-page-heading">
                <i class="bi bi-person-badge-fill"></i>
                <div><h1>{{ $roleTitle }}</h1><p>Dashboard | {{ str($roleRecord)->title() }}</p></div>
            </header>
            <form class="administrator-record-card {{ $directRecordArea ? 'administrator-direct-record-card' : '' }}" method="GET" action="{{ route('administrator.records', ['role' => $roleRecord, 'area' => $directRecordArea ?? $areas[0]]) }}">
                <div class="administrator-record-banner">
                    <img src="{{ asset('assets/einspect/USERS/'.$roleImage) }}" alt="">
                    <div><h2>{{ $roleTitle }} RECORDS</h2><p>{{ $roleSubtitle }}</p></div>
                </div>
                @unless ($directRecordArea)
                    <label>Record Type:
                        <span>
                            <i class="bi bi-file-earmark-text"></i>
                            <select name="area" onchange="this.form.action='{{ route('administrator.records', $roleRecord) }}/'+this.value">
                                @foreach ($areas as $recordArea)
                                    <option value="{{ $recordArea }}">{{ str($recordArea)->replace('-', ' ')->title() }}</option>
                                @endforeach
                            </select>
                        </span>
                    </label>
                @endunless
                <button class="administrator-record-view-button">View</button>
            </form>
        </section>
    @elseif ($area === 'report')
        <section class="administrator-record-entry">
            <header class="administrator-page-heading">
                <i class="bi bi-file-earmark-text-fill"></i>
                <div><h1>REPORT</h1><p>Dashboard | Report</p></div>
            </header>
            <div class="administrator-record-card administrator-report-role-card">
                <div class="administrator-record-banner">
                    <i class="bi bi-file-earmark-text-fill"></i>
                    <div><h2>REPORT</h2><p>PUBLIC MARKET PANDAN, ANTIQUE</p></div>
                </div>
                <div class="administrator-report-role-grid">
                    @foreach ($roleRecord === 'inspector' ? ['inspection'] : ['cash-ticket', 'stall-rental'] as $report)
                        <a href="{{ route('administrator.reports', ['report' => $report]) }}">
                            <i class="bi bi-file-earmark-text"></i>
                            <span>{{ str($report)->replace('-', ' ')->title() }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @else
        <section class="administrator-record-table-page">
            <header class="administrator-page-heading">
                <i class="bi bi-person-badge-fill"></i>
                <div><h1>{{ $roleTitle }}</h1><p>Dashboard | {{ str($roleRecord)->title() }}</p></div>
            </header>

            <div class="administrator-table-panel">
                <div class="administrator-table-toolbar">
                    <select><option>10 v</option></select>
                    <label>From:<input type="date"></label>
                    <label>To:<input type="date"></label>
                    <button type="button"><i class="bi bi-funnel-fill"></i> Filter</button>
                    <button type="button"><i class="bi bi-arrow-clockwise"></i> Reload</button>
                    <nav>
                        @foreach ($areas as $recordArea)
                            <a class="{{ $area === $recordArea ? 'active' : '' }}" href="{{ route('administrator.records', ['role' => $roleRecord, 'area' => $recordArea]) }}">{{ str($recordArea)->replace('-', ' ')->title() }}</a>
                        @endforeach
                        <a href="{{ route('administrator.members', $roleRecord) }}">Accounts</a>
                    </nav>
                </div>
                <label class="administrator-table-search">Search:<input type="search"></label>
                <div class="table-wrap">
                    <table class="administrator-record-table">
                        @if ($area === 'cash-ticket')
                            <thead><tr><th>NO.</th><th>REFERENCE</th><th>COLLECTOR</th><th>STALL SECTION</th><th>NO. OF TICKETS</th><th>TOTAL COLLECTED</th><th>DATE COLLECTED</th><th>STATUS</th><th>ACTION</th></tr></thead>
                            <tbody>
                                @forelse ($rows as $row)
                                    <tr><td>{{ $loop->iteration }}</td><td>{{ $row->collection_number }}</td><td>{{ $row->collector?->full_name }}</td><td>{{ str($row->stall_section)->title() }} Section</td><td>{{ number_format($row->ticket_quantity) }}</td><td>P{{ number_format($row->amount, 2) }}</td><td>{{ $row->collection_date->format('F d, Y') }}</td><td><span class="clerk-payment-pill paid">{{ str($row->status)->title() }}</span></td><td><a class="table-action" href="{{ route('collections.report', $row) }}"><i class="bi bi-eye-fill"></i></a></td></tr>
                                @empty
                                    <tr><td colspan="9" class="empty-state">No records found.</td></tr>
                                @endforelse
                            </tbody>
                        @elseif ($area === 'inspection')
                            <thead><tr><th>NO.</th><th>OWNER</th><th>ADDRESS</th><th>CONTACT NUMBER</th><th>TYPE OF SLAUGHTERED INSPECT</th><th>DATE AND TIME OF INSPECTION</th><th>INSPECTION STATUS</th><th>ACTION</th></tr></thead>
                            <tbody>
                                @forelse ($rows as $row)
                                    <tr><td>{{ $loop->iteration }}</td><td>{{ $row->owner_name }}</td><td>{{ $row->address }}</td><td>{{ $row->contact_number }}</td><td>{{ $row->livestock_type }}</td><td>{{ $row->scheduled_at->format('M d, Y g:i A') }}</td><td><span class="status status-{{ strtolower($row->status) }}">{{ str($row->status)->title() }}</span></td><td><a class="table-action" href="{{ route('inspections.show', $row) }}"><i class="bi bi-eye-fill"></i></a></td></tr>
                                @empty
                                    <tr><td colspan="8" class="empty-state">No records found.</td></tr>
                                @endforelse
                            </tbody>
                        @elseif ($area === 'payment')
                            <thead><tr><th>NO.</th><th>REFERENCE</th><th>TENANT</th><th>PERIOD</th><th>AMOUNT</th><th>DUE DATE</th><th>STATUS</th><th>ACTION</th></tr></thead>
                            <tbody>
                                @forelse ($rows as $row)
                                    <tr><td>{{ $loop->iteration }}</td><td>{{ $row->reference_number }}</td><td>{{ $row->tenant?->full_name }}</td><td>{{ $row->period_month->format('F Y') }}</td><td>P{{ number_format($row->amount, 2) }}</td><td>{{ $row->due_date->format('M d, Y') }}</td><td><span class="status status-{{ strtolower($row->status) }}">{{ str($row->status)->title() }}</span></td><td><a class="table-action" href="{{ route('payments.receipt', $row) }}"><i class="bi bi-eye-fill"></i></a></td></tr>
                                @empty
                                    <tr><td colspan="8" class="empty-state">No records found.</td></tr>
                                @endforelse
                            </tbody>
                        @else
                            <thead><tr><th>NO.</th><th>TENANT'S ID</th><th>TENANT</th><th>CONTACT NUMBER</th><th>STALL NUMBER</th><th>STALL SECTION</th><th>STALL STATUS</th><th>ACTION</th></tr></thead>
                            <tbody>
                                @forelse ($rows as $row)
                                    @php $tenant = $row->tenant; @endphp
                                    <tr><td>{{ $loop->iteration }}</td><td>TEN-{{ $tenant?->created_at?->format('Y') ?? now()->year }}-{{ str_pad($tenant?->id ?? $row->tenant_id, 5, '0', STR_PAD_LEFT) }}</td><td>{{ $tenant?->full_name ?? $row->business_owner }}</td><td>{{ $tenant?->phone_num ?? $row->contact_number }}</td><td>{{ $row->stall?->stall_number ?? $row->preferred_stall_number ?? '---' }}</td><td>{{ str($row->stall?->section ?? $row->preferred_section)->title() }} Section</td><td><span class="status status-{{ strtolower($row->status) }}">{{ str($row->status)->title() }}</span></td><td><a class="table-action" href="{{ route('stall-applications.show', $row) }}"><i class="bi bi-eye-fill"></i></a></td></tr>
                                @empty
                                    <tr><td colspan="8" class="empty-state">No records found.</td></tr>
                                @endforelse
                            </tbody>
                        @endif
                    </table>
                </div>
                <footer class="administrator-table-footer">Showing 1 to {{ $rows->count() }} of {{ $rows->count() }} entries <span>&lt; <b>1</b> &gt;</span></footer>
            </div>
        </section>
    @endif
@endsection
