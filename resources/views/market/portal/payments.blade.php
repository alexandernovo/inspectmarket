@extends('market.layouts.portal')

@section('content')
    @php
        $application = $application ?? null;
        $latestPayment = $payments->first();
        $paidTotal = $payments->where('status', 'PAID')->sum('amount');
        $shortageTotal = $payments->sum('shortage_amount');
        $nextDue = $payments->whereIn('status', ['PENDING', 'OVERDUE', 'DISAPPROVED', 'SUBMITTED'])->sortBy('due_date')->first();
        $remainingDays = $nextDue
            ? (int) round(now()->startOfDay()->diffInDays($nextDue->due_date->copy()->startOfDay(), false))
            : null;
        $tenant = auth()->user();
    @endphp

    @if ($tenant->isRole('TENANT'))
    <section class="tenant-payment-entry">
        <div class="tenant-payment-card">
            <div class="tenant-payment-banner">
                <i class="bi bi-shop-window"></i>
                <div>
                    <h2>STALL RENTAL</h2>
                    <p>TENANT'S MONTHLY PAYMENT</p>
                </div>
            </div>
            <button class="button button-primary" type="button" data-open-dialog="tenantPaymentDialog">View</button>
        </div>
    </section>

    <dialog id="tenantPaymentDialog" class="market-dialog tenant-payment-dialog">
        <div class="tenant-payment-details">
            <button type="button" class="tenant-dialog-close" data-close-dialog><i class="bi bi-x-circle"></i></button>
            <header class="tenant-detail-hero">
                <button type="button" data-close-dialog><i class="bi bi-arrow-left"></i></button>
                <img src="{{ asset('assets/einspect/USERS/5-Tenants.png') }}" alt="">
                <div>
                    <h1>{{ $tenant->full_name }}</h1>
                    <p>{{ $tenant->address ?: 'Pandan, Antique' }}</p>
                </div>
                <span><i class="bi bi-telephone"></i>{{ $tenant->phone_num ?: 'No contact number' }}</span>
                <span><i class="bi bi-envelope"></i>{{ $tenant->email ?: 'No email address' }}</span>
                <aside>
                    <small>Tenant ID</small>
                    <strong>TEN - {{ $tenant->created_at->format('Y') }} - {{ str_pad($tenant->id, 5, '0', STR_PAD_LEFT) }}</strong>
                    <small>Date Started:</small>
                    <b>{{ $tenant->created_at->format('F d, Y') }}</b>
                </aside>
            </header>

            <div class="tenant-payment-info-grid">
                <article class="tenant-info-box stall">
                    <h3><i class="bi bi-shop-window"></i> Stall Information</h3>
                    <p>Stall Section: <strong>{{ $application?->stall?->section ?? $application?->preferred_section ?? 'Unassigned' }}</strong></p>
                    <p>Stall Number: <strong>{{ $application?->stall?->stall_number ?? $application?->preferred_stall_number ?? 'Unassigned' }}</strong></p>
                    <p>Stall Status: <span class="status status-{{ strtolower($application?->stall?->status ?? 'pending') }}">{{ $application?->stall?->status ?? $application?->status ?? 'PENDING' }}</span></p>
                    @if ($application?->documents?->isNotEmpty())
                        <p>Business Permit:
                            <a href="{{ asset('storage/'.$application->documents->first()->path) }}" target="_blank">
                                <i class="bi bi-image"></i>{{ $application->documents->first()->original_name }}
                            </a>
                        </p>
                    @endif
                    <i class="bi bi-shop-window tenant-info-illustration"></i>
                </article>
                <article class="tenant-info-box payment">
                    <h3><i class="bi bi-wallet2"></i> Payment Information</h3>
                    <p>Monthly Rental Fee: <strong>P{{ number_format($latestPayment?->amount ?? 0, 2) }}</strong></p>
                    <p>Payment Day: <strong>{{ $latestPayment?->due_date?->format('jS') ?? 'Not set' }}</strong></p>
                    <p>Total Payment: <strong>P{{ number_format($paidTotal, 2) }}</strong></p>
                    <p>Short Charge/s: <strong class="danger">P{{ number_format($shortageTotal, 2) }}</strong></p>
                    <i class="bi bi-wallet2 tenant-info-illustration"></i>
                </article>
                <article class="tenant-info-box status-box">
                    <h3><i class="bi bi-clipboard-check"></i> Status Information</h3>
                    <p>Tenant Status: <span class="status status-active">{{ $tenant->status }}</span></p>
                    <p>Payment Status: <span class="status status-{{ strtolower($latestPayment?->status ?? 'pending') }}">{{ $latestPayment?->status ?? 'PENDING' }}</span></p>
                    <p>Next Due Date: <strong>{{ $nextDue?->due_date?->format('F d, Y') ?? 'No due date' }}</strong></p>
                    <p>Day's Remaining:
                        <strong>
                            @if ($remainingDays === null)
                                No due date
                            @elseif ($remainingDays < 0)
                                {{ abs($remainingDays) }} days overdue
                            @else
                                {{ $remainingDays }} days
                            @endif
                        </strong>
                    </p>
                    <i class="bi bi-clipboard2-check tenant-info-illustration"></i>
                </article>
            </div>

            <div class="table-wrap tenant-payment-table">
                <table>
                    <thead><tr><th>Year</th><th>Month</th><th>Stall Rental Fee</th><th>Status</th><th>Date Renewed</th><th>Date Expired</th><th>Short Charge/s</th><th>OR Receipt</th></tr></thead>
                    <tbody>
                        @forelse ($payments as $payment)
                            <tr>
                                <td>{{ $payment->period_month->format('Y') }}</td>
                                <td>{{ $payment->period_month->format('F') }}</td>
                                <td>P{{ number_format($payment->amount, 2) }}</td>
                                <td><span class="status status-{{ strtolower($payment->status) }}">{{ $payment->status }}</span></td>
                                <td>{{ $payment->paid_at?->format('F d, Y') ?? '-' }}</td>
                                <td>{{ $payment->due_date->format('F d, Y') }}</td>
                                <td>{{ $payment->shortage_amount > 0 ? 'P'.number_format($payment->shortage_amount, 2) : 'None' }}</td>
                                <td>
                                    <div class="inline-actions">
                                        @if ($payment->status === 'PAID')
                                            <a href="{{ route('payments.receipt', $payment) }}" target="_blank" class="table-action"><i class="bi bi-receipt"></i></a>
                                        @endif
                                        @if ($payment->receipt_path)
                                            <a href="{{ asset('storage/'.$payment->receipt_path) }}" target="_blank" class="table-action"><i class="bi bi-paperclip"></i></a>
                                        @endif
                                        @if (in_array($payment->status, ['PENDING','OVERDUE','DISAPPROVED']))
                                            <button type="button" class="table-action" data-open-dialog="receiptDialog{{ $payment->id }}"><i class="bi bi-upload"></i></button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="empty-state">No payment records found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </dialog>

    @foreach ($payments as $payment)
        @if (in_array($payment->status, ['PENDING','OVERDUE','DISAPPROVED']))
            <dialog id="receiptDialog{{ $payment->id }}" class="market-dialog">
                <form action="{{ route('tenant.payments.receipt', $payment) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="dialog-heading"><div><span>{{ $payment->reference_number }}</span><h2>Submit Payment Receipt</h2></div><button type="button" data-close-dialog>&times;</button></div>
                    <div class="form-grid dialog-form">
                        <label>Payment method<select name="payment_method"><option>GCASH</option><option>BANK</option><option>CASH</option></select></label>
                        <label>Receipt image or PDF<input type="file" name="receipt" accept=".pdf,image/*" required></label>
                    </div>
                    <div class="dialog-actions"><button type="button" class="button button-muted" data-close-dialog>Cancel</button><button class="button button-primary">Submit</button></div>
                </form>
            </dialog>
        @endif
    @endforeach
    @else
        <section class="panel">
            <div class="panel-heading">
                <div><span class="eyebrow">Payment Management</span><h2>{{ $pageTitle }}</h2></div>
                <span class="record-count">{{ $payments->count() }} records</span>
            </div>
            <div class="table-wrap">
                <table class="market-data-table">
                    <thead>
                        <tr><th>Reference</th><th>Tenant</th><th>Period</th><th>Amount</th><th>Due Date</th><th>Status</th><th>Receipt</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($payments as $payment)
                            <tr>
                                <td>{{ $payment->reference_number }}</td>
                                <td>{{ $payment->tenant?->full_name ?? 'Tenant' }}</td>
                                <td>{{ $payment->period_month->format('F Y') }}</td>
                                <td>P{{ number_format($payment->amount, 2) }}</td>
                                <td>{{ $payment->due_date->format('M d, Y') }}</td>
                                <td><span class="status status-{{ strtolower($payment->status) }}">{{ $payment->status }}</span></td>
                                <td>
                                    @if ($payment->receipt_path)
                                        <a href="{{ asset('storage/'.$payment->receipt_path) }}" target="_blank" class="table-action" title="View receipt"><i class="bi bi-paperclip"></i></a>
                                    @else
                                        <span class="muted">None</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="empty-state">No payment records found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif
@endsection
