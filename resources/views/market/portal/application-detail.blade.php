@extends('market.layouts.portal')

@section('content')
    @include('market.tenant.applications.css.detail')

    <section class="tenant-application-detail-page">
        <div class="tenant-application-detail-grid">
            <aside class="tenant-document-viewer">
                <a href="{{ url()->previous() }}" class="tenant-detail-back" title="Back"><i class="bi bi-arrow-left"></i></a>

                <div class="tenant-document-stage">
                    @forelse ($application->documents as $document)
                        @php
                            $extension = strtolower(pathinfo($document->original_name, PATHINFO_EXTENSION));
                            $isImage = str_starts_with($document->mime_type ?? '', 'image/') || in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                            $isPdf = ($document->mime_type ?? '') === 'application/pdf' || $extension === 'pdf';
                            $isWord = in_array($extension, ['doc', 'docx']);
                        @endphp
                        <div id="tenantDocument{{ $document->id }}" class="tenant-document-frame {{ $loop->first ? 'active' : '' }}">
                            @if ($isImage)
                                <img src="{{ route('stall-applications.documents.preview', $document) }}" alt="{{ $document->original_name }}">
                            @elseif ($isPdf)
                                <iframe src="{{ route('stall-applications.documents.preview', $document) }}" title="{{ $document->original_name }}"></iframe>
                            @else
                                <div class="tenant-file-fallback">
                                    <i class="bi {{ $isWord ? 'bi-file-earmark-word-fill' : 'bi-file-earmark-fill' }}"></i>
                                    <strong>{{ $document->original_name }}</strong>
                                    <span>{{ strtoupper($extension ?: 'FILE') }} document - {{ number_format($document->size / 1024, 1) }} KB</span>
                                    <p>This file type is available through the authorized download.</p>
                                    <a href="{{ route('stall-applications.documents', $document) }}" class="button"><i class="bi bi-download"></i> Download File</a>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="tenant-file-fallback active">
                            <i class="bi bi-file-earmark-x"></i>
                            <strong>No uploaded requirements</strong>
                            <p>This application does not have an attached document.</p>
                        </div>
                    @endforelse
                </div>

                @if ($application->documents->isNotEmpty())
                    <nav class="tenant-document-tabs" aria-label="Uploaded application requirements">
                        @foreach ($application->documents as $document)
                            @php
                                $extension = strtolower(pathinfo($document->original_name, PATHINFO_EXTENSION));
                                $icon = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])
                                    ? 'bi-file-earmark-image-fill'
                                    : ($extension === 'pdf' ? 'bi-file-earmark-pdf-fill' : (in_array($extension, ['doc', 'docx']) ? 'bi-file-earmark-word-fill' : 'bi-file-earmark-fill'));
                            @endphp
                            <button type="button" class="tenant-document-tab {{ $loop->first ? 'active' : '' }}" data-document-target="tenantDocument{{ $document->id }}">
                                <i class="bi {{ $icon }}"></i>
                                <span>{{ $document->original_name }}<small>{{ strtoupper($extension ?: 'FILE') }} - {{ number_format($document->size / 1024, 1) }} KB</small></span>
                            </button>
                        @endforeach
                    </nav>
                @endif
            </aside>

            <section class="tenant-application-readonly tenant-detail-form">
                <div class="application-form-banner">
                    <img src="{{ asset('assets/einspect/HOMEPAGE/Logo.png') }}" alt="">
                    <div><span>STALL RENTAL</span><h2>APPLICATION REQUEST FORM</h2></div>
                    <span class="status status-{{ strtolower($application->status) }}">{{ $application->status }}</span>
                </div>

                <div class="tenant-readonly-grid">
                    <h3>A. REQUESTER INFORMATION</h3>
                    <p><span>Complete Name</span><strong>{{ $application->business_owner }}</strong></p>
                    <p><span>Birth Date</span><strong>{{ $application->birth_date?->format('F d, Y') ?? 'Not provided' }}</strong></p>
                    <p><span>Address</span><strong>{{ $application->business_address }}</strong></p>
                    <p><span>Civil Status</span><strong>{{ $application->civil_status ?? 'Not provided' }}</strong></p>
                    <p><span>Email Address</span><strong>{{ $application->email ?? $application->tenant?->email ?? 'Not provided' }}</strong></p>
                    <p><span>Contact Number</span><strong>{{ $application->contact_number }}</strong></p>
                    <p><span>Sex</span><strong>{{ $application->sex ?? 'Not provided' }}</strong></p>
                    <p><span>Application Number</span><strong>{{ $application->application_number }}</strong></p>
                    <p><span>TIN Number</span><strong>{{ $application->tin_number ?? 'Not provided' }}</strong></p>

                    <h3>B. BUSINESS INFORMATION</h3>
                    <p><span>Type of Business</span><strong>{{ $application->business_category }}</strong></p>
                    <p><span>Nature of Business</span><strong>{{ $application->business_nature ?? 'Not provided' }}</strong></p>
                    <p><span>Category</span><strong>{{ $application->business_name }}</strong></p>
                    <p><span>Business Trade Name</span><strong>{{ $application->trade_name ?? $application->business_name }}</strong></p>
                    <p><span>Business Permit Date Issued</span><strong>{{ $application->permit_issued_at?->format('F d, Y') ?? 'Not provided' }}</strong></p>
                    <p><span>Other Business</span><strong>{{ $application->other_business ?? 'None' }}</strong></p>

                    <h3>C. STALL PREFERENCE</h3>
                    <p><span>Preferred Stall Section</span><strong>{{ $application->preferred_section }}</strong></p>
                    <p><span>Preferred Stall Number</span><strong>{{ $application->stall?->stall_number ?? $application->preferred_stall_number ?? 'Any available stall' }}</strong></p>
                    <p><span>Assigned Stall</span><strong>{{ $application->stall ? $application->stall->section.' #'.$application->stall->stall_number : 'Not assigned' }}</strong></p>
                    <p><span>Reviewed By</span><strong>{{ $application->reviewer?->full_name ?? 'Pending review' }}</strong></p>
                    <p class="tenant-detail-wide"><span>Remarks</span><strong>{{ $application->remarks ?? 'No remarks' }}</strong></p>
                </div>

                <div class="tenant-detail-actions">
                    <a href="{{ url()->previous() }}" class="button"><i class="bi bi-arrow-left"></i> Back to Applications</a>
                </div>
            </section>
        </div>
    </section>
@endsection

@push('scripts')
    @include('market.tenant.applications.js.detail')
@endpush
