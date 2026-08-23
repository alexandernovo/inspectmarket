@extends('market.layouts.portal')

@section('content')

    @if (auth()->user()->isRole('TENANT'))
        @include('market.tenant.applications.css.header')
        @php
            $sectionMeta = [
                'FISH' => ['image' => 'Fish Section.png', 'class' => 'fish'],
                'PORK' => ['image' => 'Pork Section.png', 'class' => 'pork'],
                'POULTRY' => ['image' => 'Poultry Section.png', 'class' => 'poultry'],
                'BEEF' => ['image' => 'Beef Section.png', 'class' => 'beef'],
                'MIXED' => ['image' => 'Mixed Section.png', 'class' => 'mixed'],
            ];
        @endphp
        <header class="tenant-page-title px-3 pt-3 pb-0">
            <div>
                <i class="bi bi-geo-alt-fill"></i>
                <div>
                    <h1>LOCATION</h1>
                    <p class="mb-0">Dashboard | Location</p>
                </div>
            </div>

        </header>
        <section class="mx-3 mb-3 mt-2 public-map-card stall-location-screen tenant-location-screen">
            <div class="stall-location-topbar">
                <div class="stall-location-legend"><strong>Legend:</strong><span class="available">Available</span><span
                        class="occupied">Occupied</span></div>
                <button type="button" class="stall-detail-button" data-stall-details-open>Stall Details</button>
            </div>
            {{-- <div class="wireframe-location-people" aria-hidden="true">
                <img src="{{ asset('assets/einspect/USERS/G-Administrator.png') }}" alt="">
                <img src="{{ asset('assets/einspect/USERS/H-Treasurer.png') }}" alt="">
                <img src="{{ asset('assets/einspect/USERS/I-Clerk.png') }}" alt="">
                <img src="{{ asset('assets/einspect/USERS/J-Inspector.png') }}" alt="">
            </div> --}}
            @foreach ($sectionMeta as $section => $meta)
                <section style="{{ $section == 'MIXED' ? 'bottom: 20px !important' : '' }}"
                    class="wireframe-stall-section wireframe-stall-{{ $meta['class'] }}">
                    <h2 class="{{ $section == 'MIXED' ? 'd-flex justify-content-center' : '' }}"><img
                            src="{{ market_section_image($section, 'TENANT') }}"
                            alt="">{{ $section }}
                        SECTION</h2>
                    <div class="wireframe-stall-row">
                        @forelse ($stalls->get($section, collect())->sortBy('stall_number') as $stall)
                            <button type="button" class="wireframe-stall-square {{ strtolower($stall->status) }}"
                                data-section="{{ $stall->section }}" data-number="{{ $stall->stall_number }}"
                                data-status="{{ $stall->status }}"
                                data-rate="{{ number_format((float) $stall->monthly_rate, 2) }}"
                                data-description="{{ $stall->description ?: 'No description provided.' }}"
                                title="{{ $stall->section }} stall {{ $stall->stall_number }} - {{ $stall->status }}">{{ $stall->stall_number }}</button>
                        @empty
                            <p class="empty-state wireframe-empty">No stall available</p>
                        @endforelse
                    </div>
                </section>
            @endforeach
        </section>
        <dialog id="tenantStallDetails" class="market-dialog compact-dialog public-stall-detail-dialog">
            <div class="dialog-heading">
                <div><span>Pandan Public Market</span>
                    <h2>Stall Details</h2>
                </div><button type="button" data-close-stall-details>&times;</button>
            </div>
            <dl class="record-details">
                <dt>Section</dt>
                <dd data-stall-section>Choose a stall</dd>
                <dt>Stall Number</dt>
                <dd data-stall-number>-</dd>
                <dt>Status</dt>
                <dd><span class="status" data-stall-status>-</span></dd>
                <dt>Monthly Rate</dt>
                <dd data-stall-rate>-</dd>
                <dt>Description</dt>
                <dd data-stall-description>-</dd>
            </dl>
            <div class="dialog-actions"><button type="button" class="button button-muted"
                    data-close-stall-details>Close</button><a href="{{ route('tenant.applications') }}"
                    class="button button-primary" data-apply-stall>Apply for Stall</a></div>
        </dialog>
    @else
        <section class="panel">
            <div class="panel-heading">
                <div><span class="eyebrow">Pandan Public Market</span>
                    <h2>Stall Map</h2>
                </div><span class="legend"><i class="available"></i> Available <i class="occupied"></i> Occupied</span>
            </div>
            <div class="market-map">
                @foreach (['FISH', 'PORK', 'POULTRY', 'BEEF', 'MIXED'] as $section)
                    <section class="market-section market-section-{{ strtolower($section) }}">
                        <h3>{{ $section }} SECTION</h3>
                        <div class="stall-grid">
                            @forelse ($stalls->get($section, collect()) as $stall)
                                @if ($canEdit ?? false)
                                    <button type="button" class="stall-button {{ strtolower($stall->status) }}"
                                        data-open-dialog="stallDialog{{ $stall->id }}"
                                        title="{{ $section }} stall {{ $stall->stall_number }} — {{ $stall->status }}">{{ $stall->stall_number }}</button>
                                @else
                                    <span class="{{ strtolower($stall->status) }}"
                                        title="{{ $section }} stall {{ $stall->stall_number }} — {{ $stall->status }}">{{ $stall->stall_number }}</span>
                                @endif
                            @empty
                                <p>No stalls configured.</p>
                            @endforelse
                        </div>
                    </section>
                @endforeach
            </div>
        </section>
        @if ($canEdit ?? false)
            @foreach ($stalls->flatten() as $stall)
                <dialog id="stallDialog{{ $stall->id }}" class="market-dialog compact-dialog">
                    <form
                        action="{{ route(auth()->user()->isRole('ADMINISTRATOR') ? 'administrator.stall-map.update' : 'treasurer.stall-map.update', $stall) }}"
                        method="POST">
                        @csrf @method('PUT')
                        <div class="dialog-heading">
                            <div><span>{{ $stall->section }} Section</span>
                                <h2>Stall #{{ $stall->stall_number }}</h2>
                            </div><button type="button" data-close-dialog>×</button>
                        </div>
                        <div class="form-grid dialog-form">
                            <label>Status<select name="status">
                                    <option @selected($stall->status === 'AVAILABLE')>AVAILABLE</option>
                                    <option @selected($stall->status === 'OCCUPIED')>OCCUPIED</option>
                                    <option @selected($stall->status === 'MAINTENANCE')>MAINTENANCE</option>
                                </select></label>
                            <label>Monthly rate<input type="number" step="0.01" min="0" name="monthly_rate"
                                    value="{{ $stall->monthly_rate }}" required></label>
                        </div>
                        <div class="dialog-actions"><button type="button" class="button button-muted"
                                data-close-dialog>Cancel</button><button class="button button-primary">Save stall</button>
                        </div>
                    </form>
                </dialog>
            @endforeach
        @endif
    @endif
@endsection

@push('scripts')
    @if (auth()->user()->isRole('TENANT'))
        <script>
            (() => {
                const dialog = document.getElementById('tenantStallDetails');
                const section = dialog?.querySelector('[data-stall-section]');
                const number = dialog?.querySelector('[data-stall-number]');
                const status = dialog?.querySelector('[data-stall-status]');
                const rate = dialog?.querySelector('[data-stall-rate]');
                const description = dialog?.querySelector('[data-stall-description]');
                const apply = dialog?.querySelector('[data-apply-stall]');

                document.querySelectorAll('.tenant-location-screen .wireframe-stall-square').forEach((stall) => {
                    stall.addEventListener('click', () => {
                        if (!dialog || !section || !number || !status || !rate || !description || !apply)
                            return;

                        section.textContent = `${stall.dataset.section} SECTION`;
                        number.textContent = stall.dataset.number;
                        status.textContent = stall.dataset.status;
                        status.className = `status status-${stall.dataset.status.toLowerCase()}`;
                        rate.textContent = `PHP ${stall.dataset.rate}`;
                        description.textContent = stall.dataset.description;
                        apply.toggleAttribute('aria-disabled', stall.dataset.status !== 'AVAILABLE');
                        apply.textContent = stall.dataset.status === 'AVAILABLE' ? 'Apply for Stall' :
                            'Not Available';
                        dialog.showModal();
                    });
                });

                document.querySelector('[data-stall-details-open]')?.addEventListener('click', () => dialog?.showModal());
                dialog?.querySelectorAll('[data-close-stall-details]').forEach((button) => button.addEventListener('click',
                    () => dialog.close()));
            })();
        </script>
    @endif
@endpush
