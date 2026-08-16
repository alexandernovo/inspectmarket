@extends('market.layouts.portal')

@section('content')
    @include('market.tenant.applications.css.header')
    @include('market.tenant.applications.css.detail')

    @php
        $currentSection = strtoupper(request('section', 'FISH'));
        $startRow = $totalApplications ? (($page - 1) * $perPage) + 1 : 0;
        $endRow = min($page * $perPage, $totalApplications);
        $queryWithoutPage = request()->except('page');
        $sectionTabs = ['MIXED' => 'Mixed Section', 'BEEF' => 'Beef Section', 'PORK' => 'Pork Section', 'POULTRY' => 'Poultry Section', 'FISH' => 'Fish Section'];
        $currentStatus = strtoupper(request('status', 'ALL'));
        $tenantStallsRoute = $tenantStallsRoute ?? 'treasurer.payments';
        $tenantStallsRouteParams = $tenantStallsRouteParams ?? [];
        $tenantStallsReadOnly = $tenantStallsReadOnly ?? false;
        $tenantStallsTitle = $tenantStallsTitle ?? 'STALL RENTAL';
        $tenantStallsBreadcrumb = $tenantStallsBreadcrumb ?? 'Dashboard | Stall Rental';
        $tenantStallsTitleAvatar = $tenantStallsTitleAvatar ?? null;
        $tenantStallsShowTopMap = $tenantStallsShowTopMap ?? false;
        $tenantStallMapEditable = $tenantStallMapEditable ?? ! $tenantStallsReadOnly;
        $tenantStallSectionUpdateRoute = $tenantStallSectionUpdateRoute ?? 'treasurer.stall-map.sections.update';
        $tenantApplicationShowStallButton = $tenantApplicationShowStallButton ?? true;
        $tenantRentalPayments = $tenantRentalPayments ?? collect();
        $tenantRentalHistoryRoute = $tenantRentalHistoryRoute ?? 'treasurer.rentals.history.update';
        $tenantRentalPaymentHistories = $tenantRentalPayments->isNotEmpty()
            ? \App\Models\Payment::with(['tenant', 'stallApplication.stall', 'stallApplication.documents'])
                ->whereIn('tenant_id', $tenantRentalPayments->pluck('tenant_id')->filter()->unique())
                ->orderBy('period_month')
                ->get()
                ->groupBy(fn ($row) => $row->tenant_id.'-'.($row->stall_application_id ?: 0))
            : collect();
        $firstTenantRentalPayment = $tenantRentalPayments->first();
        $tenantStallsUrl = fn (array $query = []) => route($tenantStallsRoute, array_merge($tenantStallsRouteParams, $query));
        $stallSections = [
            'FISH' => ['label' => 'Fish Section', 'image' => 'Fish Section.png'],
            'POULTRY' => ['label' => 'Poultry Section', 'image' => 'Poultry Section.png'],
            'PORK' => ['label' => 'Pork Section', 'image' => 'Pork Section.png'],
            'BEEF' => ['label' => 'Beef Section', 'image' => 'Beef Section.png'],
            'MIXED' => ['label' => 'Mixed Section', 'image' => 'Mixed Section.png'],
        ];
    @endphp

    <section class="clerk-rental-page treasurer-tenant-stalls-page">
        <header class="tenant-page-title clerk-rental-title">
            <div>
                @if ($tenantStallsTitleAvatar)
                    <img class="administrator-role-title-avatar" src="{{ asset('assets/einspect/USERS/'.$tenantStallsTitleAvatar) }}" alt="">
                @else
                    <i class="bi bi-shop-window"></i>
                @endif
                <div><h1>{{ $tenantStallsTitle }}</h1><p>{{ $tenantStallsBreadcrumb }}</p></div>
            </div>
            @if ($tenantStallsShowTopMap)
                <nav class="administrator-treasurer-title-actions">
                    <button type="button" class="stall-map" data-open-dialog="tenantStallMapGlobal"><i class="bi bi-map"></i> Stall Map</button>
                    <button type="button" class="all-tenants" data-open-dialog="{{ $firstTenantRentalPayment ? 'adminTenantRental'.$firstTenantRentalPayment->id : 'adminTenantRentalEmpty' }}"><i class="bi bi-people-fill"></i> All Tenants</button>
                </nav>
            @endif
        </header>

        <section class="clerk-rental-table-card treasurer-tenant-stalls-card">
                <form class="clerk-rental-filters" method="GET" action="{{ $tenantStallsUrl() }}">
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
                                <a class="{{ $currentSection === $section ? 'active' : '' }}" href="{{ $tenantStallsUrl(array_merge($queryWithoutPage, ['view' => 1, 'section' => $section])) }}">
                                    {{ $label }}
                                    @if ($tenantStallsShowTopMap)
                                        <b>{{ $sectionCounts[$section] ?? 0 }}</b>
                                    @endif
                                </a>
                            @endforeach
                        </nav>
                    </div>

                    <div class="clerk-rental-filter-row bottom">
                        <a class="clerk-reload-button" href="{{ $tenantStallsUrl(['view' => 1]) }}"><i class="bi bi-arrow-clockwise"></i> Reload</a>
                        <label class="clerk-rental-search"><input type="search" name="search" value="{{ request('search') }}" placeholder="Search"><i class="bi bi-search"></i></label>
                        <span class="clerk-status-label">Status:</span>
                        <div class="clerk-status-filter-group treasurer-application-status-filters">
                            @foreach (['ALL' => 'All', 'PENDING' => 'Pending', 'APPROVED' => 'Approved', 'DISAPPROVED' => 'Disapproved'] as $status => $label)
                                <a class="clerk-status-filter {{ strtolower($status) }} {{ $currentStatus === $status ? 'active' : '' }}" href="{{ $tenantStallsUrl(array_merge($queryWithoutPage, ['view' => 1, 'status' => $status])) }}">
                                    {{ $label }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </form>

                <div class="clerk-rental-table-wrap">
                    <table class="clerk-rental-table treasurer-tenant-stalls-table">
                        <thead>
                            <tr>
                                <th>NO.</th>
                                <th>TENANT</th>
                                <th>ADDRESS</th>
                                <th>CONTACT<br>NUMBER</th>
                                <th>STALL<br>NUMBER</th>
                                <th>STALL<br>SECTION</th>
                                @if ($tenantStallsShowTopMap)
                                    <th>DATE & TIME OF<br>REQUEST</th>
                                    <th>REQUEST<br>STATUS</th>
                                @else
                                    <th>STALL<br>STATUS</th>
                                @endif
                                <th>ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($applications as $application)
                                @php
                                    $tenant = $application->tenant;
                                    $stall = $application->stall;
                                    $tenantId = 'TEN-'.($tenant?->created_at?->format('Y') ?? now()->year).'-'.str_pad($tenant?->id ?? $application->tenant_id, 5, '0', STR_PAD_LEFT);
                                    $stallStatus = $application->status;
                                @endphp
                                <tr>
                                    <td>{{ $startRow + $loop->index }}</td>
                                    <td>{{ $tenant?->full_name ?? $application->business_owner ?? 'Tenant' }}</td>
                                    <td>{{ $tenant?->address ?? $application->business_address ?? 'Pandan, Antique' }}</td>
                                    <td>{{ $tenant?->phone_num ?? $application->contact_number ?? '-' }}</td>
                                    <td>{{ $stall?->stall_number ? str_pad($stall->stall_number, 3, '0', STR_PAD_LEFT) : ($application->preferred_stall_number ? str_pad($application->preferred_stall_number, 3, '0', STR_PAD_LEFT) : '---') }}</td>
                                    <td>{{ str($stall?->section ?? $application->preferred_section ?? 'Unassigned')->title() }} Section</td>
                                    @if ($tenantStallsShowTopMap)
                                        <td>{{ $application->created_at?->format('F d, Y | g:i A') }}</td>
                                    @endif
                                    <td><span class="clerk-payment-pill {{ $stallStatus === 'APPROVED' ? 'paid' : ($stallStatus === 'PENDING' ? 'overdue' : 'unpaid') }}">{{ str($stallStatus)->replace('_', ' ')->title() }}</span></td>
                                    <td>
                                        <div class="inline-actions">
                                            <button type="button" class="table-action view" data-open-dialog="tenantStall{{ $application->id }}" title="View"><i class="bi bi-eye-fill"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="{{ $tenantStallsShowTopMap ? 9 : 8 }}" class="empty-state">No tenant stall rental records found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <footer class="clerk-rental-pagination">
                    <span>Showing {{ $startRow }} to {{ $endRow }} of {{ $totalApplications }} entries</span>
                    <nav>
                        <a class="{{ $page <= 1 ? 'disabled' : '' }}" href="{{ $page <= 1 ? '#' : $tenantStallsUrl(array_merge($queryWithoutPage, ['view' => 1, 'page' => $page - 1])) }}">&lt;</a>
                        <strong>{{ $page }}</strong>
                        <a class="{{ $page >= $lastPage ? 'disabled' : '' }}" href="{{ $page >= $lastPage ? '#' : $tenantStallsUrl(array_merge($queryWithoutPage, ['view' => 1, 'page' => $page + 1])) }}">&gt;</a>
                    </nav>
                </footer>
            </section>

            @if ($tenantStallsShowTopMap)
                <dialog id="tenantStallMapGlobal" class="market-dialog tenant-stall-map-dialog">
                    <div>
                        <header>
                            <div class="tenant-stall-map-header-actions">
                                <button type="button" data-close-dialog><i class="bi bi-arrow-left"></i></button>
                                @if ($tenantStallMapEditable)
                                    <button type="button" class="tenant-stall-add-button" data-open-dialog="tenantStallSectionGlobal"><i class="bi bi-plus-circle"></i> Add Stall</button>
                                    <button type="button" class="tenant-stall-save-button" data-open-dialog="tenantStallSectionGlobal">Save</button>
                                @endif
                            </div>
                            <i class="bi bi-map"></i><div><h2>STALL MAP</h2><p>PUBLIC MARKET PANDAN, ANTIQUE</p></div><button type="button" data-close-dialog><i class="bi bi-x-circle-fill"></i></button>
                        </header>
                        <div class="tenant-stall-map-legend">Legend:<span class="available">Available</span><span class="occupied">Occupied</span></div>
                        <div class="tenant-stall-map-people" aria-hidden="true">
                            <img src="{{ asset('assets/einspect/USERS/3-Collector Clerk.png') }}" alt="">
                            <img src="{{ asset('assets/einspect/USERS/2-Treasurer.png') }}" alt="">
                            <img src="{{ asset('assets/einspect/USERS/4-Sanitary Inspector.png') }}" alt="">
                        </div>
                        <div class="tenant-stall-map-grid">
                            @foreach (['FISH', 'PORK', 'POULTRY', 'BEEF', 'MIXED'] as $section)
                                @php $sectionStalls = $stalls->where('section', $section); @endphp
                                <section class="section-{{ strtolower($section) }}" data-stall-map-section="Global-{{ $section }}">
                                    <h3><img src="{{ asset('assets/einspect/HOMEPAGE/'.str($section)->title().' Section.png') }}" alt="">{{ str($section)->title() }} Section</h3>
                                    <div data-stall-map-boxes="Global-{{ $section }}">
                                        @foreach ($sectionStalls as $stall)
                                            <button type="button" class="{{ $stall->status === 'AVAILABLE' ? 'available' : 'occupied' }}">{{ $stall->stall_number }}</button>
                                        @endforeach
                                    </div>
                                </section>
                            @endforeach
                        </div>
                    </div>
                </dialog>

                @if ($tenantStallMapEditable)
                    <dialog id="tenantStallSectionGlobal" class="market-dialog tenant-stall-section-dialog">
                        <form id="tenantStallSectionFormGlobal" method="POST" action="{{ route($tenantStallSectionUpdateRoute) }}" data-stall-section-form="Global" data-stall-map-selectable="0">
                            @csrf
                            @method('PUT')
                            <header><button type="button" data-close-dialog><i class="bi bi-arrow-left"></i></button><i class="bi bi-shop-window"></i><div><h2>STALL SECTION</h2><p>PUBLIC MAKET, PANDAN, ANTIQUE</p></div><button type="button" data-close-dialog><i class="bi bi-x-circle-fill"></i></button></header>
                            <table class="tenant-stall-section-table">
                                <thead><tr><th>STALL SECTION</th><th>NO. OF STALL</th><th>AVAILABLE</th><th>OCCUPIED</th><th>ACTION</th></tr></thead>
                                <tbody>
                                    @php
                                        $totalStalls = 0;
                                        $totalAvailable = 0;
                                        $totalOccupied = 0;
                                    @endphp
                                    @foreach ($stallSections as $section => $meta)
                                        @php
                                            $sectionStalls = $stalls->where('section', $section);
                                            $sectionTotal = $sectionStalls->count();
                                            $sectionAvailable = $sectionStalls->where('status', 'AVAILABLE')->count();
                                            $sectionOccupied = $sectionStalls->where('status', 'OCCUPIED')->count();
                                            $totalStalls += $sectionTotal;
                                            $totalAvailable += $sectionAvailable;
                                            $totalOccupied += $sectionOccupied;
                                        @endphp
                                        <tr data-stall-section-row="Global-{{ $section }}">
                                            <td><img src="{{ asset('assets/einspect/HOMEPAGE/'.$meta['image']) }}" alt="">{{ $meta['label'] }}</td>
                                            <td>
                                                <div class="tenant-stall-count-control">
                                                    <button type="button" data-stall-count-minus="Global-{{ $section }}">-</button>
                                                    <input id="tenantStallCountGlobal{{ $section }}" name="sections[{{ $section }}]" value="{{ $sectionTotal }}" min="{{ $sectionOccupied }}" max="300" type="number" data-stall-count-input>
                                                    <button type="button" data-stall-count-plus="Global-{{ $section }}">+</button>
                                                </div>
                                            </td>
                                            <td class="available" data-stall-section-available>{{ $sectionAvailable }}</td>
                                            <td class="occupied" data-stall-section-occupied>{{ $sectionOccupied }}</td>
                                            <td><button type="button" class="tenant-stall-section-delete" data-stall-section-reset="Global-{{ $section }}"><i class="bi bi-trash3-fill"></i></button></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot><tr><th>TOTAL STALL:</th><th data-stall-total-count>{{ $totalStalls }}</th><th data-stall-total-available>{{ $totalAvailable }}</th><th data-stall-total-occupied>{{ $totalOccupied }}</th><th></th></tr></tfoot>
                            </table>
                            <footer><button class="button button-primary">Save</button><button type="button" class="button button-muted" data-close-dialog>Cancel</button></footer>
                        </form>
                    </dialog>
                @endif

                @forelse ($tenantRentalPayments as $payment)
                    @php
                        $tenant = $payment->tenant;
                        $application = $payment->stallApplication;
                        $stall = $application?->stall;
                        $historyKey = $payment->tenant_id.'-'.($payment->stall_application_id ?: 0);
                        $historyRows = $tenantRentalPaymentHistories->get($historyKey, collect());
                        $nextDue = $historyRows->where('status', '!=', 'PAID')->sortBy('due_date')->first()?->due_date ?? $payment->due_date;
                        $currentPaymentStatus = $payment->status === 'PAID' ? 'PAID' : (($payment->status === 'OVERDUE' || $payment->due_date->isPast()) ? 'OVERDUE' : 'UNPAID');
                        $tenantId = 'TEN - '.($tenant?->created_at?->format('Y') ?? now()->year).' - '.str_pad($tenant?->id ?? $payment->tenant_id, 5, '0', STR_PAD_LEFT);
                        $document = $application?->documents?->first(fn ($file) => str($file->original_name)->lower()->contains(['permit', 'business']))
                            ?? $application?->documents?->firstWhere('document_type', 'APPLICATION_REQUIREMENT')
                            ?? $application?->documents?->firstWhere('document_type', 'PUBLIC_REQUIREMENT')
                            ?? $application?->documents?->first();
                        $historyYears = $historyRows->pluck('period_month')->filter()->map(fn ($date) => $date->format('Y'))->unique()->values();
                    @endphp
                    <dialog id="adminTenantRental{{ $payment->id }}" class="market-dialog clerk-rental-detail-dialog">
                        <form class="clerk-rental-detail" method="POST" action="{{ route($tenantRentalHistoryRoute, $payment) }}">
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
                                    <p>Payment Status: <span class="clerk-payment-pill {{ strtolower($currentPaymentStatus) }}">{{ str($currentPaymentStatus)->title() }}</span></p>
                                    <p>Next Due Date: <strong>{{ $nextDue->format('F d, Y') }}</strong></p>
                                    <p>Day/s Remaining: <strong>{{ max(0, now()->startOfDay()->diffInDays($nextDue->copy()->startOfDay(), false)) }} days</strong></p>
                                    <i class="bi bi-clipboard2-check panel-illustration"></i>
                                </article>
                            </div>

                            <table class="clerk-rental-detail-table">
                                <thead><tr><th>YEAR</th><th>MONTH</th><th>STALL RENTAL FEE</th><th>STATUS</th><th>DATE RENEWED</th><th>DATE EXPIRED</th><th>SHORT CHARGE/S</th><th>OR RECEIPT</th></tr></thead>
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
                                            <td><span class="clerk-payment-pill {{ strtolower($historyStatus) }}">{{ str($historyStatus)->title() }}</span></td>
                                            <td>{{ ($history->paid_at ?? $history->period_month)->format('F d, Y') }}</td>
                                            <td>{{ $history->due_date->format('F d, Y') }}</td>
                                            <td>{{ (float) $history->shortage_amount > 0 ? 'P'.number_format((float) $history->shortage_amount, 2) : 'None' }}</td>
                                            <td><a class="clerk-receipt-icon" href="{{ route('payments.receipt', $history) }}" target="_blank"><i class="bi bi-receipt"></i></a></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="8" class="empty-state">No payment history available.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>

                            <footer class="clerk-rental-detail-actions">
                                <button class="button button-primary">Save</button>
                                <button type="button" class="button button-muted" data-close-dialog>Cancel</button>
                            </footer>
                        </form>
                    </dialog>
                @empty
                    <dialog id="adminTenantRentalEmpty" class="market-dialog clerk-rental-detail-dialog">
                        <div class="clerk-rental-detail">
                            <header class="clerk-rental-detail-hero">
                                <button type="button" data-close-dialog><i class="bi bi-arrow-left-circle-fill"></i></button>
                                <img src="{{ asset('assets/einspect/USERS/5-Tenants.png') }}" alt="">
                                <div>
                                    <h2>NO TENANT RECORD</h2>
                                    <p>{{ strtoupper(str($currentSection)->title()) }} SECTION</p>
                                </div>
                                <span><i class="bi bi-telephone"></i>-</span>
                                <span><i class="bi bi-envelope-fill"></i>-</span>
                                <aside>
                                    <button type="button" class="tenant-dialog-close" data-close-dialog aria-label="Close"><i class="bi bi-x-circle-fill"></i></button>
                                    <small>Tenant ID</small>
                                    <strong>TEN - {{ now()->year }} - -----</strong>
                                    <small>Date Started:</small>
                                    <b>-</b>
                                </aside>
                            </header>

                            <div class="clerk-rental-detail-panels">
                                <article class="stall">
                                    <h3><i class="bi bi-shop-window"></i> STALL INFORMATION</h3>
                                    <p>Stall Section: <strong>{{ str($currentSection)->title() }} Section</strong></p>
                                    <p>Stall Number: <strong>---</strong></p>
                                    <p>Stall Status: <span class="clerk-payment-pill unpaid">No Record</span></p>
                                    <p>Business Permit: <strong>None</strong></p>
                                    <img src="{{ asset('assets/einspect/HOMEPAGE/'.str($currentSection)->title().' Section.png') }}" alt="">
                                </article>
                                <article class="payment">
                                    <h3><i class="bi bi-wallet2"></i> PAYMENT INFORMATION</h3>
                                    <p>Monthly Rental Fee: <strong>P0.00</strong></p>
                                    <p>Payment Day: <strong>-</strong></p>
                                    <p>Total Payment: <strong>P0.00</strong></p>
                                    <p>Short Charge/s: <strong class="danger">P0.00</strong></p>
                                    <i class="bi bi-wallet-fill panel-illustration"></i>
                                </article>
                                <article class="status">
                                    <h3><i class="bi bi-clipboard2-check"></i> STATUS INFORMATION</h3>
                                    <p>Tenant Status: <span class="clerk-payment-pill unpaid">No Record</span></p>
                                    <p>Payment Status: <span class="clerk-payment-pill unpaid">No Record</span></p>
                                    <p>Next Due Date: <strong>-</strong></p>
                                    <p>Day/s Remaining: <strong>-</strong></p>
                                    <i class="bi bi-clipboard2-check panel-illustration"></i>
                                </article>
                            </div>

                            <table class="clerk-rental-detail-table">
                                <thead><tr><th>YEAR</th><th>MONTH</th><th>STALL RENTAL FEE</th><th>STATUS</th><th>DATE RENEWED</th><th>DATE EXPIRED</th><th>SHORT CHARGE/S</th><th>OR RECEIPT</th></tr></thead>
                                <tbody>
                                    <tr><td colspan="8" class="empty-state">No tenant payment record found for this section.</td></tr>
                                </tbody>
                            </table>

                            <footer class="clerk-rental-detail-actions">
                                <button type="button" class="button button-muted" data-close-dialog>Cancel</button>
                            </footer>
                        </div>
                    </dialog>
                @endforelse
            @endif

            @foreach ($applications as $application)
                <dialog id="tenantStall{{ $application->id }}" class="market-dialog treasurer-tenant-application-dialog">
                    <div class="treasurer-tenant-application-form" data-tenant-stall-main="{{ $application->id }}">
                        <div class="tenant-application-detail-grid">
                            <aside class="tenant-document-viewer">
                                <button type="button" class="tenant-detail-back" data-close-dialog title="Back"><i class="bi bi-arrow-left"></i></button>
                                <div class="tenant-document-stage">
                                    @forelse ($application->documents as $document)
                                        @php
                                            $extension = strtolower(pathinfo($document->original_name, PATHINFO_EXTENSION));
                                            $isImage = str_starts_with($document->mime_type ?? '', 'image/') || in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
                                            $isPdf = ($document->mime_type ?? '') === 'application/pdf' || $extension === 'pdf';
                                            $isWord = in_array($extension, ['doc', 'docx'], true);
                                        @endphp
                                        <div id="treasurerTenantDocument{{ $document->id }}" class="tenant-document-frame {{ $loop->first ? 'active' : '' }}">
                                            @if ($isImage)
                                                <img src="{{ route('stall-applications.documents.preview', $document) }}" alt="{{ $document->original_name }}">
                                            @elseif ($isPdf)
                                                <iframe src="{{ route('stall-applications.documents.preview', $document) }}" title="{{ $document->original_name }}"></iframe>
                                            @else
                                                <div class="tenant-file-fallback">
                                                    <i class="bi {{ $isWord ? 'bi-file-earmark-word-fill' : 'bi-file-earmark-fill' }}"></i>
                                                    <strong>{{ $document->original_name }}</strong>
                                                    <span>{{ strtoupper($extension ?: 'FILE') }} document - {{ number_format($document->size / 1024, 1) }} KB</span>
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
                                                $icon = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)
                                                    ? 'bi-file-earmark-image-fill'
                                                    : ($extension === 'pdf' ? 'bi-file-earmark-pdf-fill' : (in_array($extension, ['doc', 'docx'], true) ? 'bi-file-earmark-word-fill' : 'bi-file-earmark-fill'));
                                            @endphp
                                            <button type="button" class="tenant-document-tab {{ $loop->first ? 'active' : '' }}" data-document-target="treasurerTenantDocument{{ $document->id }}">
                                                <i class="bi {{ $icon }}"></i>
                                                <span>{{ $document->original_name }}<small>{{ strtoupper($extension ?: 'FILE') }} - {{ number_format($document->size / 1024, 1) }} KB</small></span>
                                            </button>
                                        @endforeach
                                    </nav>
                                @endif
                            </aside>

                            <section class="tenant-application-readonly tenant-detail-form">
                                <button type="button" class="tenant-dialog-close" data-close-dialog><i class="bi bi-x-circle-fill"></i></button>
                                <div class="application-form-banner">
                                    <img src="{{ asset('assets/einspect/HOMEPAGE/Logo.png') }}" alt="">
                                    <div><span>STALL RENTAL</span><h2>APPLICATION REQUEST FORM</h2></div>
                                    <span class="status status-{{ strtolower($application->status) }}">{{ $application->status }}</span>
                                </div>

                                <div class="tenant-readonly-grid">
                                    <h3>A. REQUESTER INFORMATION</h3>
                                    <label><span>Name/Owner:</span><input readonly value="{{ $application->business_owner }}"></label>
                                    <label><span>TIN Number:</span><input readonly value="{{ $application->tin_number ?? '' }}"></label>
                                    <label><span>Address:</span><input readonly value="{{ $application->business_address }}"></label>
                                    <label><span>Civil Status:</span><input readonly value="{{ $application->civil_status ?? '' }}"></label>
                                    <label><span>Age:</span><input readonly value="{{ $application->birth_date ? $application->birth_date->age : '' }}"></label>
                                    <label><span>Email Address:</span><input readonly value="{{ $application->email ?? $application->tenant?->email ?? '' }}"></label>
                                    <label><span>Contact Number:</span><input readonly value="{{ $application->contact_number }}"></label>
                                    <label><span>Sex:</span><input readonly value="{{ $application->sex ?? '' }}"></label>

                                    <h3>B. BUSINESS INFORMATION</h3>
                                    <label><span>Type of Business:</span><input readonly value="{{ $application->business_category }}"></label>
                                    <label><span>Nature of Business:</span><input readonly value="{{ $application->business_nature ?? '' }}"></label>
                                    <label><span>Category:</span><input readonly value="{{ $application->business_name }}"></label>
                                    <label><span>Business Trade Name:</span><input readonly value="{{ $application->trade_name ?? $application->business_name }}"></label>
                                    <label><span>Business Permit Date Issued:</span><input readonly value="{{ $application->permit_issued_at?->format('F d, Y') ?? '' }}"></label>
                                    <label><span>Other Business:</span><input readonly value="{{ $application->other_business ?? '' }}"></label>

                                    <h3>C. STALL PREFERENCE</h3>
                                    <label><span>Preferred Stall Section:</span><input readonly value="{{ $application->preferred_section }}"></label>
                                    <label><span>Preferred Stall Number:</span><input readonly value="{{ $application->preferred_stall_number ?? '' }}"></label>
                                    <label><span>Assigned Stall</span>
                                        <select name="stall_id" data-tenant-stall-select="{{ $application->id }}" @disabled($tenantStallsReadOnly)>
                                            <option value="">Unassigned</option>
                                            @foreach ($stalls as $availableStall)
                                                <option value="{{ $availableStall->id }}" @selected($application->stall_id === $availableStall->id)>{{ str($availableStall->section)->title() }} Section #{{ str_pad($availableStall->stall_number, 3, '0', STR_PAD_LEFT) }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                </div>

                                <div class="tenant-response-actions">
                                    @unless ($tenantStallsReadOnly)
                                        <label>Response:
                                            <select data-tenant-response="{{ $application->id }}">
                                                <option value="">Select response</option>
                                                <option value="APPROVED" @selected($application->status === 'APPROVED')>Approved</option>
                                                <option value="DISAPPROVED" @selected($application->status === 'DISAPPROVED')>Disapproved</option>
                                            </select>
                                        </label>
                                        <span class="tenant-response-legend">Approved<br>Disapproved</span>
                                    @endunless
                                    @if ($tenantApplicationShowStallButton)
                                        <button type="button" class="button tenant-stall-map-button" data-open-dialog="tenantStallMap{{ $application->id }}">Stall</button>
                                    @endif
                                    @unless ($tenantStallsReadOnly)
                                        <button type="button" class="button button-primary" data-submit-tenant-response="{{ $application->id }}">Submit</button>
                                    @endunless
                                    <button type="button" class="button button-muted" data-close-dialog>Cancel</button>
                                </div>
                            </section>
                        </div>
                    </div>
                </dialog>

                @unless ($tenantStallsReadOnly)
                <dialog id="tenantApproved{{ $application->id }}" class="market-dialog tenant-response-dialog approved">
                    <form method="POST" action="{{ route('treasurer.tenant-stalls.update', $application) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="APPROVED">
                        <input type="hidden" name="stall_id" data-response-stall="{{ $application->id }}" value="{{ $application->stall_id }}">
                        <header><i class="bi bi-check-circle"></i><div><h2>APPROVED REQUEST</h2><p>STALL RENTAL REQUEST FORM</p></div><button type="button" data-close-dialog><i class="bi bi-x-circle-fill"></i></button></header>
                        <label>Contact Number:<span><input readonly value="{{ $application->contact_number }}"><i class="bi bi-telephone"></i></span></label>
                        <label>Email Address:<span><input readonly value="{{ $application->email ?? $application->tenant?->email }}"><i class="bi bi-envelope-fill"></i></span></label>
                        <div class="tenant-response-two"><label>Date:<span><input type="date" name="response_date" value="{{ now()->format('Y-m-d') }}"><i class="bi bi-calendar3"></i></span></label><label>Time:<span><input type="time" name="response_time" value="{{ now()->format('H:i') }}"><i class="bi bi-clock"></i></span></label></div>
                        <label>Remarks:<textarea name="remarks" rows="4" placeholder="Enter remarks (optional)">{{ $application->remarks }}</textarea></label>
                        <footer><button class="button button-primary">Submit</button><button type="button" class="button button-muted" data-close-dialog>Cancel</button></footer>
                    </form>
                </dialog>

                <dialog id="tenantDisapproved{{ $application->id }}" class="market-dialog tenant-response-dialog disapproved">
                    <form method="POST" action="{{ route('treasurer.tenant-stalls.update', $application) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="DISAPPROVED">
                        <input type="hidden" name="stall_id" data-response-stall="{{ $application->id }}" value="{{ $application->stall_id }}">
                        <header><i class="bi bi-x-circle"></i><div><h2>DISAPPROVED REQUEST</h2><p>STALL RENTAL REQUEST FORM</p></div><button type="button" data-close-dialog><i class="bi bi-x-circle-fill"></i></button></header>
                        <label>Contact Number:<span><input readonly value="{{ $application->contact_number }}"><i class="bi bi-telephone"></i></span></label>
                        <label>Email Address:<span><input readonly value="{{ $application->email ?? $application->tenant?->email }}"><i class="bi bi-envelope-fill"></i></span></label>
                        <div class="tenant-response-two"><label>Date:<span><input type="date" name="response_date" value="{{ now()->format('Y-m-d') }}"><i class="bi bi-calendar3"></i></span></label><label>Time:<span><input type="time" name="response_time" value="{{ now()->format('H:i') }}"><i class="bi bi-clock"></i></span></label></div>
                        <label>State the Reason of Request Denial:<textarea name="remarks" rows="4" placeholder="Specify the details here...">{{ $application->remarks }}</textarea></label>
                        <footer><button class="button button-primary">Submit</button><button type="button" class="button button-muted" data-close-dialog>Cancel</button></footer>
                    </form>
                </dialog>
                @endunless

                <dialog id="tenantStallMap{{ $application->id }}" class="market-dialog tenant-stall-map-dialog">
                    <div>
                        <header>
                            <div class="tenant-stall-map-header-actions">
                                <button type="button" data-close-dialog><i class="bi bi-arrow-left"></i></button>
                                @unless ($tenantStallsReadOnly)
                                    <button type="button" class="tenant-stall-add-button" data-open-dialog="tenantStallSection{{ $application->id }}"><i class="bi bi-plus-circle"></i> Add Stall</button>
                                    <button type="button" class="tenant-stall-save-button" data-open-dialog="tenantStallSection{{ $application->id }}">Save</button>
                                @endunless
                            </div>
                            <i class="bi bi-map"></i><div><h2>STALL MAP</h2><p>PUBLIC MARKET PANDAN, ANTIQUE</p></div><button type="button" data-close-dialog><i class="bi bi-x-circle-fill"></i></button>
                        </header>
                        <div class="tenant-stall-map-legend">Legend:<span class="available">Available</span><span class="occupied">Occupied</span></div>
                        <div class="tenant-stall-map-people" aria-hidden="true">
                            <img src="{{ asset('assets/einspect/USERS/3-Collector Clerk.png') }}" alt="">
                            <img src="{{ asset('assets/einspect/USERS/2-Treasurer.png') }}" alt="">
                            <img src="{{ asset('assets/einspect/USERS/4-Sanitary Inspector.png') }}" alt="">
                        </div>
                        <div class="tenant-stall-map-grid">
                            @foreach (['FISH', 'PORK', 'POULTRY', 'BEEF', 'MIXED'] as $section)
                                @php $sectionStalls = $stalls->where('section', $section); @endphp
                                <section class="section-{{ strtolower($section) }}" data-stall-map-section="{{ $application->id }}-{{ $section }}">
                                    <h3><img src="{{ asset('assets/einspect/HOMEPAGE/'.str($section)->title().' Section.png') }}" alt="">{{ str($section)->title() }} Section</h3>
                                    <div data-stall-map-boxes="{{ $application->id }}-{{ $section }}">
                                    @foreach ($sectionStalls as $stall)
                                        <button type="button" class="{{ $stall->status === 'AVAILABLE' ? 'available' : 'occupied' }}" @unless($tenantStallsReadOnly) data-pick-stall="{{ $application->id }}" data-stall-id="{{ $stall->id }}" @endunless>{{ $stall->stall_number }}</button>
                                    @endforeach
                                    </div>
                                </section>
                            @endforeach
                        </div>
                    </div>
                </dialog>
                @unless ($tenantStallsReadOnly)
                <dialog id="tenantStallSection{{ $application->id }}" class="market-dialog tenant-stall-section-dialog">
                    <form id="tenantStallSectionForm{{ $application->id }}" method="POST" action="{{ route('treasurer.stall-map.sections.update') }}" data-stall-section-form="{{ $application->id }}">
                        @csrf
                        @method('PUT')
                        <header><button type="button" data-close-dialog><i class="bi bi-arrow-left"></i></button><i class="bi bi-shop-window"></i><div><h2>STALL SECTION</h2><p>PUBLIC MAKET, PANDAN, ANTIQUE</p></div><button type="button" data-close-dialog><i class="bi bi-x-circle-fill"></i></button></header>
                        <table class="tenant-stall-section-table">
                            <thead><tr><th>STALL SECTION</th><th>NO. OF STALL</th><th>AVAILABLE</th><th>OCCUPIED</th><th>ACTION</th></tr></thead>
                            <tbody>
                                @php
                                    $stallSections = [
                                        'FISH' => ['label' => 'Fish Section', 'image' => 'Fish Section.png'],
                                        'POULTRY' => ['label' => 'Poultry Section', 'image' => 'Poultry Section.png'],
                                        'PORK' => ['label' => 'Pork Section', 'image' => 'Pork Section.png'],
                                        'BEEF' => ['label' => 'Beef Section', 'image' => 'Beef Section.png'],
                                        'MIXED' => ['label' => 'Mixed Section', 'image' => 'Mixed Section.png'],
                                    ];
                                    $totalStalls = 0;
                                    $totalAvailable = 0;
                                    $totalOccupied = 0;
                                @endphp
                                @foreach ($stallSections as $section => $meta)
                                    @php
                                        $sectionStalls = $stalls->where('section', $section);
                                        $sectionTotal = $sectionStalls->count();
                                        $sectionAvailable = $sectionStalls->where('status', 'AVAILABLE')->count();
                                        $sectionOccupied = $sectionStalls->where('status', 'OCCUPIED')->count();
                                        $totalStalls += $sectionTotal;
                                        $totalAvailable += $sectionAvailable;
                                        $totalOccupied += $sectionOccupied;
                                    @endphp
                                    <tr data-stall-section-row="{{ $application->id }}-{{ $section }}">
                                        <td><img src="{{ asset('assets/einspect/HOMEPAGE/'.$meta['image']) }}" alt="">{{ $meta['label'] }}</td>
                                        <td>
                                            <div class="tenant-stall-count-control">
                                                <button type="button" data-stall-count-minus="{{ $application->id }}-{{ $section }}">-</button>
                                                <input id="tenantStallCount{{ $application->id }}{{ $section }}" name="sections[{{ $section }}]" value="{{ $sectionTotal }}" min="{{ $sectionOccupied }}" max="300" type="number" data-stall-count-input>
                                                <button type="button" data-stall-count-plus="{{ $application->id }}-{{ $section }}">+</button>
                                            </div>
                                        </td>
                                        <td class="available" data-stall-section-available>{{ $sectionAvailable }}</td>
                                        <td class="occupied" data-stall-section-occupied>{{ $sectionOccupied }}</td>
                                        <td><button type="button" class="tenant-stall-section-delete" data-stall-section-reset="{{ $application->id }}-{{ $section }}"><i class="bi bi-trash3-fill"></i></button></td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot><tr><th>TOTAL STALL:</th><th data-stall-total-count>{{ $totalStalls }}</th><th data-stall-total-available>{{ $totalAvailable }}</th><th data-stall-total-occupied>{{ $totalOccupied }}</th><th></th></tr></tfoot>
                        </table>
                        <footer><button class="button button-primary">Save</button><button type="button" class="button button-muted" data-close-dialog>Cancel</button></footer>
                    </form>
                </dialog>
                @endunless
            @endforeach
    </section>
@endsection

@push('scripts')
    @include('market.tenant.applications.js.detail')
    <script>
        $(document).on('click', '[data-submit-tenant-response]', function () {
            const id = $(this).data('submit-tenant-response');
            const response = $(`[data-tenant-response="${id}"]`).val();
            const target = response === 'APPROVED' ? `tenantApproved${id}` : (response === 'DISAPPROVED' ? `tenantDisapproved${id}` : null);

            if (!target) {
                const activeDialog = this.closest('dialog');
                Swal.fire({
                    target: activeDialog || document.body,
                    icon: 'warning',
                    title: 'Please select a response.',
                });
                return;
            }

            const selectedStall = $(`[data-tenant-stall-select="${id}"]`).val();
            $(`[data-response-stall="${id}"]`).val(selectedStall);
            document.getElementById(target)?.showModal();
        });

        $(document).on('click', '[data-pick-stall]', function () {
            const id = $(this).data('pick-stall');
            const stallId = $(this).data('stall-id');
            $(`[data-tenant-stall-select="${id}"]`).val(stallId);
            $(`[data-response-stall="${id}"]`).val(stallId);
            this.closest('dialog')?.close();
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

        $(document).on('click', '[data-stall-count-plus]', function () {
            const input = $(this).siblings('[data-stall-count-input]');
            const max = parseInt(input.attr('max') || '300', 10);
            input.val(Math.min(max, (parseInt(input.val() || '0', 10) + 1)));
        });

        $(document).on('click', '[data-stall-count-minus]', function () {
            const input = $(this).siblings('[data-stall-count-input]');
            const min = parseInt(input.attr('min') || '0', 10);
            input.val(Math.max(min, (parseInt(input.val() || '0', 10) - 1)));
        });

        $(document).on('click', '[data-stall-section-reset]', function () {
            const input = $(this).closest('tr').find('[data-stall-count-input]');
            input.val(input.attr('min') || 0);
        });

        $(document).on('submit', '[data-stall-section-form]', function (event) {
            event.preventDefault();

            const form = this;
            const applicationId = $(form).data('stall-section-form');
            const canPickStall = String($(form).data('stall-map-selectable') ?? '1') !== '0';
            const dialog = form.closest('dialog');
            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
            })
                .then(async (response) => {
                    const payload = await response.json();
                    if (!response.ok) {
                        const message = payload.message || Object.values(payload.errors || {})[0]?.[0] || 'Unable to update stalls.';
                        throw new Error(message);
                    }
                    return payload;
                })
                .then((payload) => {
                    let totalCount = 0;
                    let totalAvailable = 0;
                    let totalOccupied = 0;

                    Object.entries(payload.sections || {}).forEach(([section, data]) => {
                        totalCount += data.total;
                        totalAvailable += data.available;
                        totalOccupied += data.occupied;

                        const row = $(`[data-stall-section-row="${applicationId}-${section}"]`);
                        row.find('[data-stall-count-input]').val(data.total).attr('min', data.occupied);
                        row.find('[data-stall-section-available]').text(data.available);
                        row.find('[data-stall-section-occupied]').text(data.occupied);

                        const boxes = $(`[data-stall-map-boxes="${applicationId}-${section}"]`);
                        boxes.empty();
                        data.stalls.forEach((stall) => {
                            const statusClass = stall.status === 'AVAILABLE' ? 'available' : 'occupied';
                            const pickAttrs = canPickStall ? ` data-pick-stall="${applicationId}" data-stall-id="${stall.id}"` : '';
                            boxes.append(`<button type="button" class="${statusClass}"${pickAttrs}>${stall.number}</button>`);
                        });
                    });

                    $(form).find('[data-stall-total-count]').text(totalCount);
                    $(form).find('[data-stall-total-available]').text(totalAvailable);
                    $(form).find('[data-stall-total-occupied]').text(totalOccupied);

                    Swal.fire({
                        target: dialog || document.body,
                        icon: 'success',
                        title: payload.message,
                        timer: 1200,
                        showConfirmButton: false,
                    });
                })
                .catch((error) => {
                    Swal.fire({
                        target: dialog || document.body,
                        icon: 'error',
                        title: error.message,
                    });
                });
        });
    </script>
@endpush
