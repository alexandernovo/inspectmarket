@extends('market.layouts.portal')

@section('content')
    @include('market.tenant.applications.css.applications')

    @php
        $application = $application ?? null;
        $editing = $application !== null;
        $modal = $modal ?? false;
    @endphp

    <section class="tenant-application-create">
        <form id="tenantApplicationForm" action="{{ $editing ? route('tenant.applications.update', $application) : route('tenant.applications.store') }}" method="POST" enctype="multipart/form-data" @if ($modal) novalidate @endif>
            @csrf
            @if ($editing)
                @method('PUT')
            @endif
            <div class="tenant-create-grid">
                <aside class="tenant-upload-panel">
                    <label for="tenantApplicationDocuments" class="tenant-upload-trigger">
                        <i class="bi bi-file-earmark-arrow-up"></i>
                        <strong>Upload Requirements</strong>
                        <span>Barangay business permit, valid ID, and supporting documents</span>
                        <small>PDF, JPG, JPEG, PNG, DOC, or DOCX - up to 10 MB each</small>
                    </label>
                    <input id="tenantApplicationDocuments" type="file" name="documents[]" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" multiple hidden>
                    @if ($editing && $application->documents->isNotEmpty())
                        <div class="tenant-existing-files">
                            <strong>Uploaded Requirements</strong>
                            @foreach ($application->documents as $document)
                                @php
                                    $extension = strtolower(pathinfo($document->original_name, PATHINFO_EXTENSION));
                                    $icon = in_array($extension, ['jpg', 'jpeg', 'png'])
                                        ? 'bi-file-earmark-image-fill'
                                        : ($extension === 'pdf' ? 'bi-file-earmark-pdf-fill' : 'bi-file-earmark-word-fill');
                                @endphp
                                <a href="{{ route('stall-applications.documents', $document) }}" target="_blank">
                                    <i class="bi {{ $icon }}"></i>
                                    <span>{{ $document->original_name }}<small>{{ strtoupper($extension) }} - {{ number_format($document->size / 1024, 1) }} KB</small></span>
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            @endforeach
                        </div>
                    @endif
                    <div id="tenantApplicationFiles" class="tenant-upload-files"></div>
                </aside>

                <section class="tenant-create-form">
                    <div class="application-form-banner">
                        <img src="{{ asset('assets/einspect/HOMEPAGE/Logo.png') }}" alt="">
                        <div><span>STALL RENTAL</span><h2>{{ $editing ? 'EDIT APPLICATION REQUEST' : 'APPLICATION REQUEST FORM' }}</h2></div>
                        @if ($modal)
                            <button type="button" class="tenant-management-close" data-close-management-dialog aria-label="Close"><i class="bi bi-x-circle-fill"></i></button>
                        @endif
                    </div>

                    <fieldset>
                        <legend>A. REQUESTER INFORMATION</legend>
                        <div class="tenant-form-grid">
                            <label>Complete Name<input name="business_owner" value="{{ old('business_owner', $application?->business_owner ?? auth()->user()->full_name) }}" required></label>
                            <label>TIN Number<input name="tin_number" value="{{ old('tin_number', $application?->tin_number) }}" placeholder="000-000-000-000" maxlength="15" inputmode="numeric" required></label>
                            <label>Birth Date<input type="date" name="birth_date" value="{{ old('birth_date', $application?->birth_date?->format('Y-m-d')) }}"></label>
                            <label>Address<input name="business_address" value="{{ old('business_address', $application?->business_address ?? auth()->user()->address) }}" required></label>
                            <label>Civil Status<select name="civil_status"><option value="">Select status</option>@foreach (['SINGLE','MARRIED','WIDOWED','SEPARATED'] as $status)<option value="{{ $status }}" @selected(old('civil_status', $application?->civil_status) === $status)>{{ $status }}</option>@endforeach</select></label>
                            <label>Email Address<input type="email" name="email" value="{{ old('email', $application?->email ?? auth()->user()->email) }}"></label>
                            <label>Contact Number<input name="contact_number" value="{{ old('contact_number', $application?->contact_number ?? auth()->user()->phone_num) }}" required></label>
                            <label>Sex<select name="sex"><option value="">Select sex</option><option value="MALE" @selected(old('sex', $application?->sex) === 'MALE')>MALE</option><option value="FEMALE" @selected(old('sex', $application?->sex) === 'FEMALE')>FEMALE</option></select></label>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>B. BUSINESS INFORMATION</legend>
                        <div class="tenant-form-grid">
                            <label>Type of Business<input name="business_category" value="{{ old('business_category', $application?->business_category) }}" required></label>
                            <label>Nature of Business<input name="business_nature" value="{{ old('business_nature', $application?->business_nature) }}"></label>
                            <label>Category<input name="business_name" value="{{ old('business_name', $application?->business_name) }}" required></label>
                            <label>Business Trade Name<input name="trade_name" value="{{ old('trade_name', $application?->trade_name) }}"></label>
                            <label>Business Permit Date Issued<input type="date" name="permit_issued_at" value="{{ old('permit_issued_at', $application?->permit_issued_at?->format('Y-m-d')) }}"></label>
                            <label>Other Business<input name="other_business" value="{{ old('other_business', $application?->other_business) }}"></label>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>C. STALL PREFERENCE</legend>
                        <div class="tenant-form-grid">
                            <label>Preferred Stall Section<select name="preferred_section" required>@foreach (['FISH','PORK','POULTRY','BEEF','MIXED'] as $section)<option value="{{ $section }}" @selected(old('preferred_section', $application?->preferred_section) === $section)>{{ $section }}</option>@endforeach</select></label>
                            <label>Preferred Stall Number<select name="preferred_stall_number"><option value="">Any available stall</option>@foreach ($stalls as $stall)<option value="{{ $stall->stall_number }}" @selected((string) old('preferred_stall_number', $application?->preferred_stall_number) === (string) $stall->stall_number)>{{ $stall->section }} #{{ $stall->stall_number }}</option>@endforeach</select></label>
                        </div>
                    </fieldset>

                    <div class="tenant-create-actions">
                        <button class="button tenant-submit-application" type="submit">{{ $editing ? 'Save Changes' : 'Submit' }}</button>
                        @if ($modal)
                            <button type="button" class="button tenant-cancel-application" data-close-management-dialog>Cancel</button>
                        @else
                            <a class="button tenant-cancel-application" href="{{ route('tenant.applications') }}">Cancel</a>
                        @endif
                    </div>
                </section>
            </div>
        </form>
    </section>
@endsection

@push('scripts')
    @include('market.tenant.applications.js.create')
@endpush
