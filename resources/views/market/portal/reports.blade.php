@extends('market.layouts.portal')

@section('content')
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
@endsection
