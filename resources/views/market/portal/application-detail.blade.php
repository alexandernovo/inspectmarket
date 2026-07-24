@extends('market.layouts.portal')

@section('content')
    <div class="detail-grid">
        <section class="panel detail-card">
            <div class="panel-heading"><div><span class="eyebrow">{{ $application->application_number }}</span><h2>{{ $application->business_name }}</h2></div><span class="status status-{{ strtolower($application->status) }}">{{ $application->status }}</span></div>
            <dl class="record-details">
                <dt>Tenant</dt><dd>{{ $application->tenant?->full_name }}</dd>
                <dt>Business owner</dt><dd>{{ $application->business_owner }}</dd>
                <dt>Category</dt><dd>{{ $application->business_category }}</dd>
                <dt>Address</dt><dd>{{ $application->business_address }}</dd>
                <dt>Contact</dt><dd>{{ $application->contact_number }}</dd>
                <dt>Preferred section</dt><dd>{{ $application->preferred_section }}</dd>
                <dt>Assigned stall</dt><dd>{{ $application->stall ? $application->stall->section.' #'.$application->stall->stall_number : 'Not assigned' }}</dd>
                <dt>Reviewed by</dt><dd>{{ $application->reviewer?->full_name ?? 'Pending review' }}</dd>
                <dt>Remarks</dt><dd>{{ $application->remarks ?? '—' }}</dd>
            </dl>
        </section>
        <aside>
            <section class="panel">
                <div class="panel-heading"><div><span class="eyebrow">Requirements</span><h2>Documents</h2></div></div>
                <div class="document-list">
                    @forelse ($application->documents as $document)
                        <a href="{{ route('stall-applications.documents', $document) }}"><i class="bi bi-file-earmark-arrow-down"></i><span>{{ $document->original_name }}<small>{{ number_format($document->size / 1024, 1) }} KB</small></span></a>
                    @empty
                        <p class="empty-state">No documents uploaded.</p>
                    @endforelse
                </div>
            </section>
            <section class="panel detail-payments">
                <div class="panel-heading"><div><span class="eyebrow">Rental</span><h2>Payments</h2></div></div>
                @forelse ($application->payments as $payment)
                    <a href="{{ route('payments.receipt', $payment) }}" target="_blank">{{ $payment->period_month->format('F Y') }} <strong>₱{{ number_format($payment->amount, 2) }}</strong> <span class="status status-{{ strtolower($payment->status) }}">{{ $payment->status }}</span></a>
                @empty
                    <p class="empty-state">No payments recorded.</p>
                @endforelse
            </section>
        </aside>
    </div>
@endsection
