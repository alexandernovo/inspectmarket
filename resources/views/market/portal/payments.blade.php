@extends('market.layouts.portal')

@section('content')
    <section class="panel">
        <div class="panel-heading">
            <div><span class="eyebrow">Tenant Monthly Payment</span><h2>{{ $pageTitle }}</h2></div>
            @if ($canRecord ?? false)
                <button class="button button-primary" type="button" data-open-dialog="paymentDialog"><i class="bi bi-plus-lg"></i> Record payment</button>
            @endif
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Reference</th><th>Tenant</th><th>Stall</th><th>Period</th><th>Amount</th><th>Due date</th><th>Paid</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr>
                            <td>{{ $payment->reference_number }}</td>
                            <td>{{ $payment->tenant?->full_name ?? auth()->user()->full_name }}</td>
                            <td>{{ $payment->stallApplication?->stall?->section }} {{ $payment->stallApplication?->stall?->stall_number }}</td>
                            <td>{{ $payment->period_month->format('F Y') }}</td>
                            <td>₱{{ number_format($payment->amount, 2) }}</td>
                            <td>{{ $payment->due_date->format('M d, Y') }}</td>
                            <td>{{ $payment->paid_at?->format('M d, Y') ?? '—' }}</td>
                            <td><span class="status status-{{ strtolower($payment->status) }}">{{ $payment->status }}</span></td>
                            <td>
                                <div class="inline-actions">
                                    @if ($payment->receipt_path)
                                        <a href="{{ asset('storage/'.$payment->receipt_path) }}" target="_blank" class="table-action" title="Uploaded proof"><i class="bi bi-paperclip"></i></a>
                                    @endif
                                    @if ($payment->status === 'PAID')
                                        <a href="{{ route('payments.receipt', $payment) }}" target="_blank" class="table-action" title="Receipt"><i class="bi bi-receipt"></i></a>
                                    @endif
                                    @if (auth()->user()->isRole('TENANT') && in_array($payment->status, ['PENDING','OVERDUE','DISAPPROVED']))
                                        <button type="button" class="table-action" data-open-dialog="receiptDialog{{ $payment->id }}" title="Submit receipt"><i class="bi bi-upload"></i></button>
                                    @endif
                                    @if (($canRecord ?? false) && $payment->status === 'SUBMITTED')
                                        <form action="{{ route('treasurer.payments.verify', $payment) }}" method="POST">@csrf @method('PUT')
                                            <button name="status" value="PAID" class="table-action approve" title="Verify"><i class="bi bi-check-lg"></i></button>
                                            <button name="status" value="DISAPPROVED" class="table-action reject" title="Reject"><i class="bi bi-x-lg"></i></button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @if (auth()->user()->isRole('TENANT') && in_array($payment->status, ['PENDING','OVERDUE','DISAPPROVED']))
                            <dialog id="receiptDialog{{ $payment->id }}" class="market-dialog">
                                <form action="{{ route('tenant.payments.receipt', $payment) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="dialog-heading"><div><span>{{ $payment->reference_number }}</span><h2>Submit Payment Receipt</h2></div><button type="button" data-close-dialog>×</button></div>
                                    <div class="form-grid dialog-form">
                                        <label>Payment method<select name="payment_method"><option>GCASH</option><option>BANK</option><option>CASH</option></select></label>
                                        <label>Receipt image or PDF<input type="file" name="receipt" accept=".pdf,image/*" required></label>
                                    </div>
                                    <div class="dialog-actions"><button type="button" class="button button-muted" data-close-dialog>Cancel</button><button class="button button-primary">Submit receipt</button></div>
                                </form>
                            </dialog>
                        @endif
                    @empty
                        <tr><td colspan="9" class="empty-state">No payment records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if ($canRecord ?? false)
        <dialog id="paymentDialog" class="market-dialog">
            <form action="{{ route('treasurer.payments.store') }}" method="POST">
                @csrf
                <div class="dialog-heading"><div><span>Stall Rental</span><h2>Record Tenant Payment</h2></div><button type="button" data-close-dialog>×</button></div>
                <div class="form-grid">
                    <label>Tenant<select name="tenant_id" required>@foreach ($tenants as $tenant)<option value="{{ $tenant->id }}">{{ $tenant->full_name }}</option>@endforeach</select></label>
                    <label>Approved application<select name="stall_application_id"><option value="">Select application</option>@foreach ($applications as $application)<option value="{{ $application->id }}">{{ $application->application_number }} · {{ $application->tenant?->full_name }}</option>@endforeach</select></label>
                    <label>Amount<input type="number" name="amount" min="0" step="0.01" required></label>
                    <label>Period month<input type="month" name="period_month" value="{{ now()->format('Y-m') }}" required></label>
                    <label>Due date<input type="date" name="due_date" value="{{ now()->addDays(10)->toDateString() }}" required></label>
                    <label>Payment method<select name="payment_method"><option>CASH</option><option>GCASH</option><option>BANK</option></select></label>
                    <label>Status<select name="status"><option>PAID</option><option>PENDING</option><option>OVERDUE</option></select></label>
                    <label>O.R. number<input name="or_number"></label>
                    <label>Shortage amount<input type="number" name="shortage_amount" value="0" min="0" step="0.01"></label>
                </div>
                <div class="dialog-actions"><button type="button" class="button button-muted" data-close-dialog>Cancel</button><button class="button button-primary">Save payment</button></div>
            </form>
        </dialog>
    @endif
@endsection
