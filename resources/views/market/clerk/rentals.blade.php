@extends('market.layouts.portal')

@section('content')
    @include('market.tenant.applications.css.header')

    @php
        $currentSection = strtoupper(request('section', 'FISH'));
        $currentStatus = strtoupper(request('status', 'ALL'));
        $startRow = $totalPayments ? (($page - 1) * $perPage) + 1 : 0;
        $endRow = min($page * $perPage, $totalPayments);
        $queryWithoutPage = request()->except('page');
        $sectionTabs = ['MIXED' => 'Mixed Section', 'BEEF' => 'Beef Section', 'PORK' => 'Pork Section', 'POULTRY' => 'Poultry Section', 'FISH' => 'Fish Section'];
        $rentalRoute = $rentalRoute ?? 'clerk.rentals';
        $rentalRouteParams = $rentalRouteParams ?? [];
        $rentalHistoryRoute = $rentalHistoryRoute ?? 'clerk.rentals.history.update';
        $rentalPageTitle = $rentalPageTitle ?? 'STALL RENTAL';
        $rentalBreadcrumb = $rentalBreadcrumb ?? 'Dashboard | Stall Rental';
        $rentalTitleAvatar = $rentalTitleAvatar ?? null;
        $rentalReadOnly = $rentalReadOnly ?? false;
        $showRentalActions = $showRentalActions ?? true;
        $showShortCharges = $showShortCharges ?? true;
        $rentalTableColumns = 8 + ($showShortCharges ? 1 : 0) + ($showRentalActions ? 1 : 0);
        $rentalUrl = fn (array $query = []) => route($rentalRoute, array_merge($rentalRouteParams, $query));
        $paymentHistories = \App\Models\Payment::with(['tenant', 'stallApplication.stall', 'stallApplication.documents'])
            ->whereIn('tenant_id', $payments->pluck('tenant_id')->filter()->unique())
            ->orderBy('period_month')
            ->get()
            ->groupBy(fn ($row) => $row->tenant_id.'-'.($row->stall_application_id ?: 0));
    @endphp

    <section class="clerk-rental-page">
        <header class="tenant-page-title clerk-rental-title">
            <div>
                @if ($rentalTitleAvatar)
                    <img class="administrator-role-title-avatar" src="{{ asset('assets/einspect/USERS/'.$rentalTitleAvatar) }}" alt="">
                @else
                    <i class="bi bi-shop-window"></i>
                @endif
                <div><h1>{{ $rentalPageTitle }}</h1><p>{{ $rentalBreadcrumb }}</p></div>
            </div>
        </header>

        @unless ($showTable)
            <section class="clerk-rental-entry">
                <div class="clerk-rental-card">
                    <div class="clerk-rental-banner">
                        <i class="bi bi-shop-window"></i>
                        <div><h2>STALL RENTAL</h2><p>TENANT'S MONTHLY PAYMENT</p></div>
                    </div>
                    <a class="button clerk-rental-view-button" href="{{ $rentalUrl(['view' => 1, 'section' => 'FISH']) }}">View all Tenants</a>
                </div>
            </section>
        @else
            <section class="clerk-rental-table-card">
                <form class="clerk-rental-filters" method="GET" action="{{ $rentalUrl() }}">
                    <input type="hidden" name="view" value="1">
                    <div class="clerk-rental-filter-row top">
                        <select name="per_page" aria-label="Rows per page">
                            @foreach ([5, 10, 25, 50] as $length)
                                <option value="{{ $length }}" @selected($perPage === $length)>{{ $length }} v</option>
                            @endforeach
                        </select>
                        <label>From:<input type="date" name="from" value="{{ request('from') }}"></label>
                        <label>To:<input type="date" name="to" value="{{ request('to') }}"></label>
                        <button type="submit" class="clerk-filter-button">Filter</button>

                        <nav class="clerk-section-tabs" aria-label="Stall section filter">
                            @foreach ($sectionTabs as $section => $label)
                                <a class="{{ $currentSection === $section ? 'active' : '' }}" href="{{ $rentalUrl(array_merge($queryWithoutPage, ['view' => 1, 'section' => $section])) }}">{{ $label }}</a>
                            @endforeach
                        </nav>
                    </div>

                    <div class="clerk-rental-filter-row bottom">
                        <a class="clerk-reload-button" href="{{ $rentalUrl(['view' => 1]) }}"><i class="bi bi-arrow-clockwise"></i> Reload</a>
                        <label class="clerk-rental-search"><input type="search" name="search" value="{{ request('search') }}" placeholder="Search"><i class="bi bi-search"></i></label>
                        <span class="clerk-status-label">Status:</span>
                        <div class="clerk-status-filter-group">
                            @foreach (['ALL' => 'All', 'OVERDUE' => 'Overdue', 'PAID' => 'Paid', 'UNPAID' => 'Unpaid'] as $status => $label)
                                <a class="clerk-status-filter {{ strtolower($status) }} {{ $currentStatus === $status ? 'active' : '' }}" href="{{ $rentalUrl(array_merge($queryWithoutPage, ['view' => 1, 'status' => $status])) }}">
                                    {{ $label }}
                                    <b>{{ $statusCounts[$status] ?? 0 }}</b>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </form>

                <div class="clerk-rental-table-wrap">
                    <table @class([
                        'clerk-rental-table',
                        'no-actions' => ! $showRentalActions,
                        'no-short-charges' => ! $showShortCharges,
                    ])>
                        <thead>
                            <tr>
                                <th>NO.</th>
                                <th>TENANT'S ID</th>
                                <th>TENANT</th>
                                <th>STALL<br>SECTION</th>
                                <th>STALL<br>NUMBER</th>
                                <th>STALL FEE</th>
                                <th>DATE OF<br>PAYMENT</th>
                                <th>PAYMENT<br>STATUS</th>
                                @if ($showShortCharges)
                                    <th>SHORT<br>CHARGE/S</th>
                                @endif
                                @if ($showRentalActions)
                                    <th>ACTION</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($payments as $payment)
                                @php
                                    $tenant = $payment->tenant;
                                    $application = $payment->stallApplication;
                                    $stall = $application?->stall;
                                    $status = $payment->status === 'PAID'
                                        ? 'PAID'
                                        : (($payment->status === 'OVERDUE' || $payment->due_date->isPast()) ? 'OVERDUE' : 'UNPAID');
                                    $historyKey = $payment->tenant_id.'-'.($payment->stall_application_id ?: 0);
                                    $historyRows = $paymentHistories->get($historyKey, collect());
                                @endphp
                                <tr>
                                    <td>{{ $startRow + $loop->index }}</td>
                                    <td>TEN-{{ $tenant?->created_at?->format('Y') ?? now()->year }}-{{ str_pad($tenant?->id ?? $payment->tenant_id, 5, '0', STR_PAD_LEFT) }}</td>
                                    <td>{{ $tenant?->full_name ?? $application?->business_owner ?? 'Tenant' }}</td>
                                    <td>{{ str($stall?->section ?? $application?->preferred_section ?? 'Unassigned')->title() }} Section</td>
                                    <td>{{ $stall?->stall_number ? str_pad($stall->stall_number, 3, '0', STR_PAD_LEFT) : ($application?->preferred_stall_number ? str_pad($application->preferred_stall_number, 3, '0', STR_PAD_LEFT) : '---') }}</td>
                                    <td>P{{ number_format((float) $payment->amount, 2) }}</td>
                                    <td>{{ ($payment->paid_at ?? $payment->due_date)->format('F d, Y') }}</td>
                                    <td><span class="clerk-payment-pill {{ strtolower($status) }}">{{ str($status)->title() }}</span></td>
                                    @if ($showShortCharges)
                                        <td>{{ (float) $payment->shortage_amount > 0 ? 'P'.number_format((float) $payment->shortage_amount, 2) : 'None' }}</td>
                                    @endif
                                    @if ($showRentalActions)
                                        <td><button type="button" class="table-action view" data-open-dialog="clerkRentalPayment{{ $payment->id }}" title="View"><i class="bi bi-eye-fill"></i></button></td>
                                    @endif
                                </tr>
                            @empty
                                <tr><td colspan="{{ $rentalTableColumns }}" class="empty-state">No tenant rental payments found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <footer class="clerk-rental-pagination">
                    <span>Showing {{ $startRow }} to {{ $endRow }} of {{ $totalPayments }} entries</span>
                    <nav>
                        <a class="{{ $page <= 1 ? 'disabled' : '' }}" href="{{ $page <= 1 ? '#' : $rentalUrl(array_merge($queryWithoutPage, ['view' => 1, 'page' => $page - 1])) }}">&lt;</a>
                        <strong>{{ $page }}</strong>
                        <a class="{{ $page >= $lastPage ? 'disabled' : '' }}" href="{{ $page >= $lastPage ? '#' : $rentalUrl(array_merge($queryWithoutPage, ['view' => 1, 'page' => $page + 1])) }}">&gt;</a>
                    </nav>
                </footer>
            </section>

            @if ($showRentalActions)
            @foreach ($payments as $payment)
                @php
                    $tenant = $payment->tenant;
                    $application = $payment->stallApplication;
                    $stall = $application?->stall;
                    $historyKey = $payment->tenant_id.'-'.($payment->stall_application_id ?: 0);
                    $historyRows = $paymentHistories->get($historyKey, collect());
                    $latestPaid = $historyRows->where('status', 'PAID')->sortByDesc('paid_at')->first();
                    $nextDue = $historyRows->where('status', '!=', 'PAID')->sortBy('due_date')->first()?->due_date ?? $payment->due_date;
                    $currentStatus = $payment->status === 'PAID' ? 'PAID' : (($payment->status === 'OVERDUE' || $payment->due_date->isPast()) ? 'OVERDUE' : 'UNPAID');
                    $tenantId = 'TEN-'.($tenant?->created_at?->format('Y') ?? now()->year).'-'.str_pad($tenant?->id ?? $payment->tenant_id, 5, '0', STR_PAD_LEFT);
                    $document = $application?->documents?->first(fn ($file) => str($file->original_name)->lower()->contains(['permit', 'business']))
                        ?? $application?->documents?->firstWhere('document_type', 'APPLICATION_REQUIREMENT')
                        ?? $application?->documents?->firstWhere('document_type', 'PUBLIC_REQUIREMENT')
                        ?? $application?->documents?->first();
                    $historyYears = $historyRows->pluck('period_month')->filter()->map(fn ($date) => $date->format('Y'))->unique()->values();
                @endphp
                <dialog id="clerkRentalPayment{{ $payment->id }}" class="market-dialog clerk-rental-detail-dialog">
                    <form class="clerk-rental-detail" method="POST" action="{{ route($rentalHistoryRoute, $payment) }}">
                        @csrf
                        @method('PUT')
                        <header class="clerk-rental-detail-hero">
                            <button type="button" data-close-dialog><i class="bi bi-arrow-left-circle-fill"></i></button>
                            <img src="{{ asset('assets/einspect/USERS/5-Tenants.png') }}" alt="">
                            <div>
                                <h2>{{ strtoupper($tenant?->full_name ?? $application?->business_owner ?? 'Tenant') }}</h2>
                                <p>{{ strtoupper($tenant?->address ?? $application?->business_address ?? 'Pandan, Antique') }}</p>
                            </div>
                            <span><i class="bi bi-telephone"></i>{{ $tenant?->phone_num ?? $application?->contact_number ?? '-' }}</span>
                            <span><i class="bi bi-envelope-fill"></i>{{ $tenant?->email ?? $application?->email ?? '-' }}</span>
                            <aside>
                                <button type="button" class="tenant-dialog-close" data-close-dialog aria-label="Close"><i class="bi bi-x-circle-fill"></i></button>
                                <small>Tenant ID</small>
                                <strong>{{ $tenantId }}</strong>
                                <small>Date Started:</small>
                                <b>{{ ($tenant?->created_at ?? $application?->created_at ?? $payment->created_at)->format('F d, Y') }}</b>
                            </aside>
                        </header>

                        <div class="clerk-rental-detail-panels">
                            <article class="stall">
                                <h3><i class="bi bi-shop-window"></i> STALL INFORMATION</h3>
                                <p>Stall Section: <strong>{{ str($stall?->section ?? $application?->preferred_section ?? 'Unassigned')->title() }} Section</strong></p>
                                <p>Stall Number: <strong>{{ $stall?->stall_number ? str_pad($stall->stall_number, 3, '0', STR_PAD_LEFT) : ($application?->preferred_stall_number ? str_pad($application->preferred_stall_number, 3, '0', STR_PAD_LEFT) : '---') }}</strong></p>
                                <p>Stall Status: <span class="clerk-payment-pill unpaid">{{ str($stall?->status ?? 'Occupied')->title() }}</span></p>
                                <p>Business Permit:
                                    @if ($document)
                                        <a href="{{ route('stall-applications.documents.preview', $document) }}" target="_blank"><i class="bi bi-file-earmark-image-fill"></i>{{ $document->original_name }}</a>
                                    @else
                                        <strong>None</strong>
                                    @endif
                                </p>
                                <img src="{{ asset('assets/einspect/HOMEPAGE/'.str($stall?->section ?? $application?->preferred_section ?? 'Fish')->title().' Section.png') }}" alt="">
                            </article>
                            <article class="payment">
                                <h3><i class="bi bi-wallet2"></i> PAYMENT INFORMATION</h3>
                                <p>Monthly Rental Fee: <strong>P{{ number_format((float) $payment->amount, 2) }}</strong></p>
                                <p>Payment Day: <strong>Every {{ $payment->due_date->day }}th</strong></p>
                                <p>Total Payment: <strong>P{{ number_format((float) $historyRows->where('status', 'PAID')->sum('amount'), 2) }}</strong></p>
                                <p>Short Charge/s: <strong class="danger">P{{ number_format((float) $historyRows->sum('shortage_amount'), 2) }}</strong></p>
                                <i class="bi bi-wallet-fill panel-illustration"></i>
                            </article>
                            <article class="status">
                                <h3><i class="bi bi-clipboard2-check"></i> STATUS INFORMATION</h3>
                                <p>Tenant Status: <span class="clerk-payment-pill paid">{{ str($tenant?->status ?? 'Active')->title() }}</span></p>
                                <p>Payment Status: <span class="clerk-payment-pill {{ strtolower($currentStatus) }}">{{ str($currentStatus)->title() }}</span></p>
                                <p>Next Due Date: <strong>{{ $nextDue->format('F d, Y') }}</strong></p>
                                <p>Day/s Remaining: <strong>{{ max(0, now()->startOfDay()->diffInDays($nextDue->copy()->startOfDay(), false)) }} days</strong></p>
                                <i class="bi bi-clipboard2-check panel-illustration"></i>
                            </article>
                        </div>

                        <table class="clerk-rental-detail-table">
                            <thead>
                                <tr>
                                    <th>YEAR</th>
                                    <th>MONTH</th><th>STALL RENTAL FEE</th><th>STATUS</th><th>DATE RENEWED</th><th>DATE EXPIRED</th><th>SHORT CHARGE/S</th><th>OR RECEIPT</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($historyRows as $history)
                                    @php $historyStatus = $history->status === 'PAID' ? 'PAID' : (($history->status === 'OVERDUE' || $history->due_date->isPast()) ? 'OVERDUE' : 'UNPAID'); @endphp
                                    <tr data-rental-year="{{ $history->period_month->format('Y') }}">
                                        @if ($loop->first)
                                            <td class="clerk-rental-year-cell" rowspan="{{ $historyRows->filter(fn ($row) => $row->period_month->format('Y') === $historyYears->first())->count() }}">
                                                <span class="clerk-year-box">
                                                    <select class="clerk-rental-year-filter" aria-label="Filter payment history by year">
                                                        @foreach ($historyYears as $year)
                                                            <option value="{{ $year }}" @selected($year === $historyYears->first())>{{ $year }}</option>
                                                        @endforeach
                                                    </select>
                                                </span>
                                            </td>
                                        @endif
                                        <td>{{ $history->period_month->format('F') }}</td>
                                        <td>P{{ number_format((float) $history->amount, 2) }}</td>
                                        <td>
                                            <span class="clerk-edit-select status-{{ strtolower($historyStatus) }}">
                                                <select name="payments[{{ $history->id }}][status]" @disabled($rentalReadOnly)>
                                                    @foreach (['PAID' => 'Paid', 'OVERDUE' => 'Overdue', 'UNPAID' => 'Unpaid'] as $value => $label)
                                                        <option value="{{ $value }}" @selected(($historyStatus === $value) || ($value === 'UNPAID' && $history->status === 'PENDING'))>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </span>
                                        </td>
                                        <td>{{ ($history->paid_at ?? $history->period_month)->format('F d, Y') }}</td>
                                        <td>{{ $history->due_date->format('F d, Y') }}</td>
                                        <td>
                                            <span class="clerk-edit-select short-charge">
                                                <select name="payments[{{ $history->id }}][shortage_amount]" @disabled($rentalReadOnly)>
                                                    <option value="0" @selected((float) $history->shortage_amount <= 0)>None</option>
                                                    @foreach ([70, 140, 210, 280, 350] as $charge)
                                                        <option value="{{ $charge }}" @selected((float) $history->shortage_amount === (float) $charge)>P{{ number_format($charge, 2) }}</option>
                                                    @endforeach
                                                </select>
                                            </span>
                                        </td>
                                        <td><a class="clerk-receipt-icon" href="{{ route('payments.receipt', $history) }}" target="_blank"><i class="bi bi-receipt"></i></a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="empty-state">No payment history available.</td></tr>
                                @endforelse
                            </tbody>
                        </table>

                        <footer class="clerk-rental-detail-actions">
                            @unless ($rentalReadOnly)
                                <button class="button button-primary">Save</button>
                            @endunless
                            <button type="button" class="button button-muted" data-close-dialog>{{ $rentalReadOnly ? 'Close' : 'Cancel' }}</button>
                        </footer>
                    </form>
                </dialog>
            @endforeach
            @endif
        @endunless
    </section>
@endsection

@push('scripts')
    <script>
        $(document).on('change', '.clerk-edit-select select[name$="[status]"]', function () {
            const wrapper = $(this).closest('.clerk-edit-select');
            wrapper.removeClass('status-paid status-overdue status-unpaid status-pending')
                .addClass(`status-${String(this.value).toLowerCase()}`);
        });

        $(document).on('change', '.clerk-rental-year-filter', function () {
            const selectedYear = String(this.value);
            const table = $(this).closest('table');
            const yearCell = $(this).closest('.clerk-rental-year-cell');
            const matchingRows = table.find(`tbody tr[data-rental-year="${selectedYear}"]`);

            if (!matchingRows.length) {
                return;
            }

            table.find('tbody tr[data-rental-year]').each(function () {
                $(this).toggle(String($(this).data('rental-year')) === selectedYear);
            });

            yearCell.detach().attr('rowspan', matchingRows.length).prependTo(matchingRows.first());
        });

        $(document).on('click', '[data-open-dialog]', function () {
            const dialog = document.getElementById($(this).data('open-dialog'));
            if (!dialog) {
                return;
            }

            const yearFilter = $(dialog).find('.clerk-rental-year-filter');

            if (yearFilter.length) {
                yearFilter.trigger('change');
            }
        });
    </script>
@endpush
