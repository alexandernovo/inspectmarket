@extends('market.layouts.portal')

@section('content')
    <div class="two-column assignments-layout">
        <section class="panel">
            <div class="panel-heading"><div><span class="eyebrow">Cash Ticket</span><h2>Collector Assignments</h2></div></div>
            <div class="table-wrap">
                <table class="market-data-table">
                    <thead><tr><th>Assignment</th><th>Collector</th><th>Section</th><th>Ticket Range</th><th>Quantity</th><th>Date</th><th>Status</th><th>Slip</th></tr></thead>
                    <tbody>
                        @forelse ($assignments as $assignment)
                            <tr>
                                <td>{{ $assignment->assignment_number }}</td>
                                <td>{{ $assignment->collector?->full_name }}</td>
                                <td>{{ $assignment->stall_section }}</td>
                                <td>{{ $assignment->ticket_start }}–{{ $assignment->ticket_end }}</td>
                                <td>{{ $assignment->ticket_quantity }}</td>
                                <td>{{ $assignment->assigned_date->format('M d, Y') }}</td>
                                <td><span class="status status-{{ strtolower($assignment->status) }}">{{ $assignment->status }}</span></td>
                                <td><a class="table-action" target="_blank" href="{{ route('assignments.slip', $assignment) }}" title="Requisition and issue slip"><i class="bi bi-file-earmark-text"></i></a></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="empty-state">No assignments yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
        <section class="panel">
            <div class="panel-heading"><div><span class="eyebrow">New Assignment</span><h2>Assign Collector</h2></div></div>
            <form action="{{ route('treasurer.assignments.store') }}" method="POST" class="stacked-form">
                @csrf
                <label>Collector<select name="collector_id" required>@foreach ($collectors as $collector)<option value="{{ $collector->id }}">{{ $collector->full_name }}</option>@endforeach</select></label>
                <label>Stall section<select name="stall_section">@foreach (['FISH','POULTRY','PORK','BEEF','MIXED'] as $section)<option>{{ $section }}</option>@endforeach</select></label>
                <div class="form-grid"><label>Starting ticket<input type="number" name="ticket_start" min="1" required></label><label>Ending ticket<input type="number" name="ticket_end" min="1" required></label></div>
                <label>Assignment date<input type="date" name="assigned_date" value="{{ now()->toDateString() }}" required></label>
                <label>Remarks<textarea name="remarks" rows="4"></textarea></label>
                <button class="button button-primary">Save assignment</button>
            </form>
        </section>
    </div>
@endsection
