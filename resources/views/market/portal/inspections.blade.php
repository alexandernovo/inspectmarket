@extends('market.layouts.portal')

@section('content')
    <section class="panel">
        <div class="panel-heading">
            <div><span class="eyebrow">Poultry · Pork · Beef</span><h2>{{ $pageTitle }}</h2></div>
            @if ($canRequest)
                <button class="button button-primary" type="button" data-open-dialog="inspectionDialog"><i class="bi bi-calendar-plus"></i> Request inspection</button>
            @endif
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Request</th><th>Owner</th><th>Type</th><th>Animals</th><th>Schedule</th><th>Result</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                    @forelse ($inspections as $inspection)
                        <tr>
                            <td>{{ $inspection->request_number }}</td>
                            <td><strong>{{ $inspection->owner_name }}</strong><br><small>{{ $inspection->contact_number }}</small></td>
                            <td>{{ $inspection->livestock_type }}</td>
                            <td>{{ $inspection->animal_count }}</td>
                            <td>{{ $inspection->scheduled_at->format('M d, Y g:i A') }}</td>
                            <td>{{ $inspection->inspection_result ?? '—' }}</td>
                            <td><span class="status status-{{ strtolower($inspection->status) }}">{{ $inspection->status }}</span></td>
                            <td>
                                @if ($canReview)
                                    <button type="button" class="table-action" data-open-dialog="inspectionReview{{ $inspection->id }}" title="Review inspection"><i class="bi bi-pencil-square"></i></button>
                                @else
                                    @if ($inspection->status === 'COMPLETED')
                                        <a href="{{ route('inspections.certificate', $inspection) }}" target="_blank" class="table-action" title="Certificate"><i class="bi bi-file-earmark-check"></i></a>
                                    @else
                                        <span class="muted">View</span>
                                    @endif
                                @endif
                            </td>
                        </tr>
                        @if ($canReview)
                            <dialog id="inspectionReview{{ $inspection->id }}" class="market-dialog wide-dialog">
                                <form action="{{ route('inspector.inspections.update', $inspection) }}" method="POST">
                                    @csrf @method('PUT')
                                    <div class="dialog-heading"><div><span>{{ $inspection->request_number }}</span><h2>{{ $inspection->livestock_type }} Inspection Form</h2></div><button type="button" data-close-dialog>×</button></div>
                                    <div class="form-grid dialog-form">
                                        <label>Status<select name="status"><option @selected($inspection->status === 'APPROVED')>APPROVED</option><option>DISAPPROVED</option><option>COMPLETED</option></select></label>
                                        <label>Final judgment<select name="inspection_result"><option value="">Pending result</option><option @selected($inspection->inspection_result === 'PASSED')>PASSED</option><option>CONDEMNED</option><option>REINSPECTION</option></select></label>
                                        <label>Breed<input name="breed" value="{{ $inspection->breed }}"></label>
                                        <label>Sex<select name="sex"><option value="">Not recorded</option><option>MALE</option><option>FEMALE</option><option>MIXED</option></select></label>
                                        <label>Animal age<input name="animal_age" value="{{ $inspection->animal_age }}"></label>
                                        <label>Source location<input name="source_location" value="{{ $inspection->source_location }}"></label>
                                        <label>Live weight (kg)<input type="number" step="0.01" min="0" name="live_weight" value="{{ $inspection->live_weight }}"></label>
                                        <label>Carcass weight (kg)<input type="number" step="0.01" min="0" name="carcass_weight" value="{{ $inspection->carcass_weight }}"></label>
                                        <label>Purpose<input name="purpose" value="{{ $inspection->purpose }}"></label>
                                        <fieldset class="full findings-fieldset"><legend>Ante-mortem findings</legend>@foreach (['NORMAL_CONDITION','FEVER','LAMENESS','RESPIRATORY_SIGNS','SKIN_LESIONS'] as $finding)<label><input type="checkbox" name="ante_mortem_findings[]" value="{{ $finding }}" @checked(in_array($finding, $inspection->ante_mortem_findings ?? []))> {{ str_replace('_', ' ', $finding) }}</label>@endforeach</fieldset>
                                        <fieldset class="full findings-fieldset"><legend>Post-mortem findings</legend>@foreach (['NORMAL_CARCASS','HEAD_NECK_LESION','THORACIC_ORGAN_LESION','ABDOMINAL_ORGAN_LESION','PARASITES'] as $finding)<label><input type="checkbox" name="post_mortem_findings[]" value="{{ $finding }}" @checked(in_array($finding, $inspection->post_mortem_findings ?? []))> {{ str_replace('_', ' ', $finding) }}</label>@endforeach</fieldset>
                                        <label class="full">Detailed findings<textarea name="findings" rows="4">{{ $inspection->findings }}</textarea></label>
                                        <label class="full">Remarks<textarea name="remarks" rows="3">{{ $inspection->remarks }}</textarea></label>
                                    </div>
                                    <div class="dialog-actions"><button type="button" class="button button-muted" data-close-dialog>Cancel</button><button class="button button-primary">Save inspection</button></div>
                                </form>
                            </dialog>
                        @endif
                    @empty
                        <tr><td colspan="8" class="empty-state">No inspection requests found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if ($canRequest)
        <dialog id="inspectionDialog" class="market-dialog">
            <form action="{{ route('tenant.inspections.store') }}" method="POST">
                @csrf
                <div class="dialog-heading"><div><span>Request Inspection Form</span><h2>Slaughtered Livestock</h2></div><button type="button" data-close-dialog>×</button></div>
                <div class="form-grid">
                    <label>Livestock<select name="livestock_type" required><option>POULTRY</option><option>PORK</option><option>BEEF</option></select></label>
                    <label>Number of animals<input type="number" name="animal_count" min="1" value="1" required></label>
                    <label>Owner name<input name="owner_name" value="{{ auth()->user()->full_name }}" required></label>
                    <label>Contact number<input name="contact_number" value="{{ auth()->user()->phone_num }}" required></label>
                    <label class="full">Address<input name="address" value="{{ auth()->user()->address }}" required></label>
                    <label class="full">Preferred date and time<input type="datetime-local" name="scheduled_at" required></label>
                    <label>Breed<input name="breed"></label>
                    <label>Sex<select name="sex"><option value="">Select sex</option><option>MALE</option><option>FEMALE</option><option>MIXED</option></select></label>
                    <label>Animal age<input name="animal_age" placeholder="e.g. 8 months"></label>
                    <label>Estimated live weight (kg)<input type="number" step="0.01" min="0" name="live_weight"></label>
                    <label>Source location<input name="source_location"></label>
                    <label>Purpose<input name="purpose" value="Public market sale"></label>
                </div>
                <div class="dialog-actions"><button type="button" class="button button-muted" data-close-dialog>Cancel</button><button class="button button-primary">Submit request</button></div>
            </form>
        </dialog>
    @endif
@endsection
