@extends('market.layouts.portal')

@section('content')
    <section class="panel detail-card">
        <div class="panel-heading">
            <div><span class="eyebrow">{{ $inspection->request_number }}</span><h2>{{ $inspection->livestock_type }} Slaughtered Inspection</h2></div>
            <div class="inline-actions"><span class="status status-{{ strtolower($inspection->status) }}">{{ $inspection->status }}</span>@if ($inspection->status === 'COMPLETED')<a href="{{ route('inspections.certificate', $inspection) }}" target="_blank" class="button button-primary"><i class="bi bi-printer"></i> Certificate</a>@endif</div>
        </div>
        <div class="inspection-detail-grid">
            <dl class="record-details">
                <dt>Owner</dt><dd>{{ $inspection->owner_name }}</dd>
                <dt>Address</dt><dd>{{ $inspection->address }}</dd>
                <dt>Contact</dt><dd>{{ $inspection->contact_number }}</dd>
                <dt>Scheduled</dt><dd>{{ $inspection->scheduled_at->format('F d, Y g:i A') }}</dd>
                <dt>Animal count</dt><dd>{{ $inspection->animal_count }}</dd>
                <dt>Breed / Sex</dt><dd>{{ $inspection->breed }} / {{ $inspection->sex }}</dd>
                <dt>Animal age</dt><dd>{{ $inspection->animal_age }}</dd>
                <dt>Source</dt><dd>{{ $inspection->source_location }}</dd>
            </dl>
            <dl class="record-details">
                <dt>Live weight</dt><dd>{{ $inspection->live_weight }} kg</dd>
                <dt>Carcass weight</dt><dd>{{ $inspection->carcass_weight }} kg</dd>
                <dt>Purpose</dt><dd>{{ $inspection->purpose }}</dd>
                <dt>Final result</dt><dd>{{ $inspection->inspection_result ?? 'Pending' }}</dd>
                <dt>Findings</dt><dd>{{ $inspection->findings ?? '—' }}</dd>
                <dt>Remarks</dt><dd>{{ $inspection->remarks ?? '—' }}</dd>
                <dt>Inspector</dt><dd>{{ $inspection->inspector?->full_name ?? 'Not assigned' }}</dd>
                <dt>Certificate</dt><dd>{{ $inspection->certificate_number ?? 'Not issued' }}</dd>
            </dl>
        </div>
        <div class="finding-groups">
            <section><h3>Ante-mortem findings</h3>@forelse (($inspection->ante_mortem_findings ?? []) as $finding)<span>{{ str_replace('_', ' ', $finding) }}</span>@empty<p>None recorded.</p>@endforelse</section>
            <section><h3>Post-mortem findings</h3>@forelse (($inspection->post_mortem_findings ?? []) as $finding)<span>{{ str_replace('_', ' ', $finding) }}</span>@empty<p>None recorded.</p>@endforelse</section>
        </div>
    </section>
@endsection
