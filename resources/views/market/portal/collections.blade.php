@extends('market.layouts.portal')

@section('content')
    @php
        $calendarMonth = now()->startOfMonth();
        $collectedDays = $collections->filter(fn ($item) => $item->collection_date->isSameMonth($calendarMonth))->pluck('collection_date')->map->day->all();
    @endphp
    <section class="panel cash-ticket-workflow">
        <div class="workflow-title"><i class="bi bi-ticket-perforated-fill"></i><div><span>PUBLIC MARKET PANDAN, ANTIQUE</span><h2>CASH TICKET</h2></div></div>
        <div class="workflow-steps">
            @foreach ([['Record Receipt','bi-receipt'],['Assign Collectors','bi-people'],['Collect and Record','bi-cash-stack'],['Complete Report','bi-file-earmark-check'],['Submit to Treasurer','bi-send-check']] as $index => [$label, $icon])
                <div class="{{ $index < 2 ? 'complete' : '' }}"><b>{{ $index + 1 }}</b><i class="bi {{ $icon }}"></i><span>{{ $label }}</span></div>
            @endforeach
        </div>
    </section>
    <div class="two-column collection-calendar-layout">
        <section class="panel mini-calendar">
            <div class="panel-heading"><div><span class="eyebrow">Collection Calendar</span><h2>{{ $calendarMonth->format('F Y') }}</h2></div></div>
            <div class="calendar-weekdays">@foreach (['SUN','MON','TUE','WED','THU','FRI','SAT'] as $day)<span>{{ $day }}</span>@endforeach</div>
            <div class="calendar-days">
                @for ($blank = 0; $blank < $calendarMonth->dayOfWeek; $blank++)<span class="blank"></span>@endfor
                @for ($day = 1; $day <= $calendarMonth->daysInMonth; $day++)<span class="{{ in_array($day, $collectedDays) ? 'collected' : '' }}">{{ $day }}</span>@endfor
            </div>
        </section>
        <section class="panel collection-totals">
            <div class="panel-heading"><div><span class="eyebrow">Monthly Summary</span><h2>Collection of Fees</h2></div></div>
            <div><span>Tickets distributed</span><strong>{{ number_format($collections->sum('ticket_quantity')) }}</strong></div>
            <div><span>Total collected</span><strong>₱{{ number_format($collections->sum('amount'), 2) }}</strong></div>
            <div><span>Shortages</span><strong>₱{{ number_format($collections->sum('shortage_amount'), 2) }}</strong></div>
        </section>
    </div>
    <section class="panel">
        <div class="panel-heading">
            <div><span class="eyebrow">Collection of Fees</span><h2>{{ $pageTitle }}</h2></div>
            @if ($canCreate)
                <button class="button button-primary" type="button" data-open-dialog="collectionDialog"><i class="bi bi-plus-lg"></i> Add collection</button>
            @endif
        </div>
        <div class="table-wrap">
            <table class="market-data-table">
                <thead><tr><th>Reference</th><th>Collector</th><th>Section</th><th>Tickets</th><th>Amount</th><th>Date</th><th>Status</th><th>Report</th></tr></thead>
                <tbody>
                    @forelse ($collections as $collection)
                        <tr>
                            <td>{{ $collection->collection_number }}</td>
                            <td>{{ $collection->collector?->full_name }}</td>
                            <td>{{ $collection->stall_section }}</td>
                            <td>{{ number_format($collection->ticket_quantity) }}</td>
                            <td>₱{{ number_format($collection->amount, 2) }}</td>
                            <td>{{ $collection->collection_date->format('M d, Y') }}</td>
                            <td><span class="status status-{{ strtolower($collection->status) }}">{{ $collection->status }}</span></td>
                            <td><a class="table-action" target="_blank" href="{{ route('collections.report', $collection) }}" title="Report of collection"><i class="bi bi-printer"></i></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="empty-state">No cash ticket collections found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if (($assignments ?? collect())->isNotEmpty())
        <section class="panel assignments-summary">
            <div class="panel-heading"><div><span class="eyebrow">Assigned by Treasurer</span><h2>Ticket Distribution</h2></div></div>
            <div class="assignment-cards">
                @foreach ($assignments as $assignment)
                    <article><strong>{{ $assignment->stall_section }}</strong><span>{{ $assignment->ticket_start }}–{{ $assignment->ticket_end }}</span><small>{{ $assignment->collector?->full_name }} · {{ $assignment->assigned_date->format('M d, Y') }}</small></article>
                @endforeach
            </div>
        </section>
    @endif

    <dialog id="collectionDialog" class="market-dialog">
        <form action="{{ route('clerk.collections.store') }}" method="POST">
            @csrf
            <div class="dialog-heading"><div><span>Cash Ticket</span><h2>Record Collection</h2></div><button type="button" data-close-dialog>×</button></div>
            <div class="form-grid">
                <label>Collector<select name="collector_id" required>@foreach ($collectors as $collector)<option value="{{ $collector->id }}">{{ $collector->full_name }}</option>@endforeach</select></label>
                <label>Assignment<select name="cash_ticket_assignment_id"><option value="">Manual entry</option>@foreach (($assignments ?? collect()) as $assignment)<option value="{{ $assignment->id }}">{{ $assignment->assignment_number }} · {{ $assignment->stall_section }} {{ $assignment->ticket_start }}–{{ $assignment->ticket_end }}</option>@endforeach</select></label>
                <label>Stall section<select name="stall_section" required>@foreach (['FISH','PORK','POULTRY','BEEF','MIXED'] as $section)<option>{{ $section }}</option>@endforeach</select></label>
                <label>Starting ticket<input type="number" name="ticket_start" min="1"></label>
                <label>Ending ticket<input type="number" name="ticket_end" min="1"></label>
                <label>Tickets distributed<input type="number" name="ticket_quantity" min="1" required></label>
                <label>Total collected<input type="number" name="amount" min="0" step="0.01" required></label>
                <label>Shortage amount<input type="number" name="shortage_amount" min="0" step="0.01" value="0"></label>
                <label>Collection date<input type="date" name="collection_date" value="{{ now()->toDateString() }}" required></label>
                <label>Remarks<input name="remarks"></label>
            </div>
            <div class="dialog-actions"><button type="button" class="button button-muted" data-close-dialog>Cancel</button><button class="button button-primary">Save collection</button></div>
        </form>
    </dialog>
@endsection
