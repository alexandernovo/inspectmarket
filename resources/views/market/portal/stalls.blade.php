@extends('market.layouts.portal')

@section('content')
    <section class="panel">
        <div class="panel-heading">
            <div><span class="eyebrow">Management</span><h2>{{ $pageTitle }}</h2></div>
            @if ($canApply ?? false)
                <button class="button button-primary" type="button" data-open-dialog="applicationDialog"><i class="bi bi-plus-lg"></i> New application</button>
            @endif
        </div>
        <div class="filter-row">
            @foreach (['ALL', 'MIXED', 'BEEF', 'PORK', 'POULTRY', 'FISH'] as $section)
                <span>{{ $section }}</span>
            @endforeach
        </div>
        <div class="table-wrap">
            <table class="market-data-table">
                <thead><tr><th>Application</th><th>Tenant / Business</th><th>Section</th><th>Stall</th><th>Status</th><th>Submitted</th><th>Action</th></tr></thead>
                <tbody>
                    @forelse ($applications as $application)
                        <tr>
                            <td>{{ $application->application_number }}</td>
                            <td><strong>{{ $application->tenant?->full_name ?? $application->business_owner }}</strong><br><small>{{ $application->business_name }}</small></td>
                            <td>{{ $application->preferred_section }}</td>
                            <td>{{ $application->stall?->stall_number ?? $application->preferred_stall_number ?? 'Unassigned' }}</td>
                            <td><span class="status status-{{ strtolower($application->status) }}">{{ $application->status }}</span></td>
                            <td>{{ $application->created_at->format('M d, Y') }}</td>
                            <td>
                                @if ($canReview ?? false)
                                    <form action="{{ route('treasurer.rentals.review', $application) }}" method="POST" class="review-application-form">
                                        @csrf @method('PUT')
                                        <select name="stall_id" aria-label="Assign stall">
                                            <option value="">Select stall</option>
                                            @foreach ($stalls as $stall)
                                                <option value="{{ $stall->id }}">{{ $stall->section }} #{{ $stall->stall_number }}</option>
                                            @endforeach
                                        </select>
                                        <input name="remarks" placeholder="Remarks">
                                        <div class="inline-actions">
                                            <button name="status" value="APPROVED" class="table-action approve" title="Approve"><i class="bi bi-check-lg"></i></button>
                                            <button name="status" value="DISAPPROVED" class="table-action reject" title="Disapprove"><i class="bi bi-x-lg"></i></button>
                                        </div>
                                    </form>
                                @else
                                    <span class="muted">{{ $application->remarks ?: 'View' }}</span>
                                @endif
                            </td>
                        </tr>
                        @if ($application->documents->isNotEmpty())
                            <tr class="document-row">
                                <td colspan="7">
                                    <strong>Documents:</strong>
                                    @foreach ($application->documents as $document)
                                        <a href="{{ asset('storage/'.$document->path) }}" target="_blank"><i class="bi bi-paperclip"></i> {{ $document->original_name }}</a>
                                    @endforeach
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr><td colspan="7" class="empty-state">No stall applications found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if ($canApply ?? false)
        <dialog id="applicationDialog" class="market-dialog">
            <form action="{{ route('tenant.applications.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="dialog-heading"><div><span>Application Request Form</span><h2>Stall Rental</h2></div><button type="button" data-close-dialog>×</button></div>
                <div class="form-grid">
                    <label>Business name<input name="business_name" value="{{ old('business_name') }}" required></label>
                    <label>Business category<input name="business_category" value="{{ old('business_category') }}" required></label>
                    <label>Business owner<input name="business_owner" value="{{ old('business_owner', auth()->user()->full_name) }}" required></label>
                    <label>Contact number<input name="contact_number" value="{{ old('contact_number', auth()->user()->phone_num) }}" required></label>
                    <label class="full">Business address<input name="business_address" value="{{ old('business_address') }}" required></label>
                    <label>Birth date<input type="date" name="birth_date" value="{{ old('birth_date') }}"></label>
                    <label>Civil status<select name="civil_status"><option value="">Select status</option>@foreach (['SINGLE','MARRIED','WIDOWED','SEPARATED'] as $status)<option>{{ $status }}</option>@endforeach</select></label>
                    <label>Preferred section<select name="preferred_section" required>@foreach (['FISH','PORK','POULTRY','BEEF','MIXED'] as $section)<option>{{ $section }}</option>@endforeach</select></label>
                    <label>Preferred stall<select name="preferred_stall_number"><option value="">Any available stall</option>@foreach ($stalls as $stall)<option value="{{ $stall->stall_number }}">{{ $stall->section }} #{{ $stall->stall_number }}</option>@endforeach</select></label>
                    <label class="full">Application requirements (PDF or image, maximum five files)<input type="file" name="documents[]" accept=".pdf,image/*" multiple></label>
                </div>
                <div class="dialog-actions"><button type="button" class="button button-muted" data-close-dialog>Cancel</button><button class="button button-primary">Submit application</button></div>
            </form>
        </dialog>
    @endif
@endsection
