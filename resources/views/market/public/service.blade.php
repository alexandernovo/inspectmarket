@extends('market.layouts.public')

@section('title', 'E-Inspect | '.str($screen)->replace('-', ' ')->title())

@section('content')
    @php
        $activeNav = match ($screen) {
            'contact' => 'contact',
            'stall-rental', 'stall-location', 'stall-application' => 'stall-rental',
            'slaughtered-inspection', 'inspection-request' => 'slaughtered-inspection',
            default => 'announcements',
        };

        $inspectionMonth = now()->startOfMonth();
    @endphp

    @include('market.public.header', ['active' => $activeNav, 'announcementCount' => $announcements->count()])

    <main class="public-screen public-screen-{{ $screen }}">
        @if (session('success'))
            <div class="public-flash"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="public-flash error">{{ $errors->first() }}</div>
        @endif

        @if ($screen === 'contact')
            <section class="wireframe-contact">
                <div class="contact-visual">
                    <img class="contact-character-image" src="{{ asset('assets/einspect/USERS/G-Administrator.png') }}" alt="Market administrator">
                    <div class="contact-visual-card">
                        <h1>Let's Get in Touch!</h1>
                        <img src="{{ asset('assets/einspect/HOMEPAGE/Logo.png') }}" alt="Municipality of Pandan seal">
                        <strong>Connect with Us:</strong>
                        <i class="bi bi-facebook"></i>
                    </div>
                </div>
                <form action="{{ route('contact.store') }}" method="POST" class="wireframe-form">
                    @csrf
                    <h2>Contact Us</h2>
                    <label>Name:<span class="contact-input-icon"><i class="bi bi-person-circle"></i><input name="name" required></span></label>
                    <label>Address:<span class="contact-input-icon"><i class="bi bi-house-fill"></i><input name="address"></span></label>
                    <label>Contact Number:<span class="contact-input-icon"><i class="bi bi-telephone"></i><input name="contact_number" required></span></label>
                    <label>Email:<span class="contact-input-icon"><i class="bi bi-envelope"></i><input type="email" name="email"></span></label>
                    <label>Message:<span class="contact-input-icon textarea"><i class="bi bi-envelope-fill"></i><textarea name="message" rows="5" required></textarea></span></label>
                    <button class="button button-primary">Send</button>
                </form>
            </section>
        @elseif ($screen === 'stall-rental')
            <section class="public-feature-screen">
                <div class="stall-rental-stage">
                    <img class="public-feature-character" src="{{ asset('assets/einspect/USERS/H-Treasurer.png') }}" alt="Market treasurer">
                    <img class="public-feature-image" src="{{ asset('assets/einspect/HOMEPAGE/Stall.png') }}" alt="Market stall">
                    <div class="public-feature-actions">
                        <a class="button button-primary" href="{{ route('public.service', 'stall-application') }}"><i class="bi bi-file-earmark-text"></i> Application</a>
                        <a class="button button-primary" href="{{ route('public.service', 'stall-location') }}"><i class="bi bi-geo-alt"></i> Location</a>
                    </div>
                </div>
            </section>
        @elseif ($screen === 'stall-location')
            <section class="public-map-card stall-location-screen">
                @php
                    $sectionMeta = [
                        'FISH' => ['image' => 'Fish Section.png', 'class' => 'fish'],
                        'PORK' => ['image' => 'Pork Section.png', 'class' => 'pork'],
                        'POULTRY' => ['image' => 'Poultry Section.png', 'class' => 'poultry'],
                        'BEEF' => ['image' => 'Beef Section.png', 'class' => 'beef'],
                        'MIXED' => ['image' => 'Mixed Section.png', 'class' => 'mixed'],
                    ];
                @endphp
                <div class="stall-location-topbar">
                    <div class="stall-location-legend"><strong>Legend:</strong><span class="available">Available</span><span class="occupied">Occupied</span></div>
                    <button type="button" class="stall-detail-button" data-stall-details-open>Stall Details</button>
                </div>
                <div class="wireframe-location-people" aria-hidden="true">
                    <img src="{{ asset('assets/einspect/USERS/G-Administrator.png') }}" alt="">
                    <img src="{{ asset('assets/einspect/USERS/H-Treasurer.png') }}" alt="">
                    <img src="{{ asset('assets/einspect/USERS/I-Clerk.png') }}" alt="">
                    <img src="{{ asset('assets/einspect/USERS/J-Inspector.png') }}" alt="">
                </div>
                @foreach ($sectionMeta as $section => $meta)
                    <section class="wireframe-stall-section wireframe-stall-{{ $meta['class'] }}">
                        <h2><img src="{{ asset('assets/einspect/HOMEPAGE/'.$meta['image']) }}" alt="">{{ $section }} SECTION</h2>
                        <div class="wireframe-stall-row">
                            @forelse ($stalls->get($section, collect())->sortBy('stall_number') as $stall)
                                <button
                                    type="button"
                                    class="wireframe-stall-square {{ strtolower($stall->status) }}"
                                    data-section="{{ $stall->section }}"
                                    data-number="{{ $stall->stall_number }}"
                                    data-status="{{ $stall->status }}"
                                    data-rate="{{ number_format((float) $stall->monthly_rate, 2) }}"
                                    data-description="{{ $stall->description ?: 'No description provided.' }}"
                                    title="{{ $stall->section }} stall {{ $stall->stall_number }} - {{ $stall->status }}"
                                >{{ $stall->stall_number }}</button>
                            @empty
                                <p class="empty-state wireframe-empty">No stall available</p>
                            @endforelse
                        </div>
                    </section>
                @endforeach
                <dialog id="publicStallDetails" class="market-dialog compact-dialog public-stall-detail-dialog">
                    <div class="dialog-heading"><div><span>Public Market</span><h2>Stall Details</h2></div><button type="button" data-close-stall-details>&times;</button></div>
                    <dl class="record-details">
                        <dt>Section</dt><dd data-stall-section>Choose a stall</dd>
                        <dt>Stall Number</dt><dd data-stall-number>-</dd>
                        <dt>Status</dt><dd><span class="status" data-stall-status>-</span></dd>
                        <dt>Monthly Rate</dt><dd data-stall-rate>-</dd>
                        <dt>Description</dt><dd data-stall-description>-</dd>
                    </dl>
                    <div class="dialog-actions"><button type="button" class="button button-muted" data-close-stall-details>Close</button><a href="{{ route('public.service', 'stall-application') }}" class="button button-primary" data-apply-stall>Apply for Stall</a></div>
                </dialog>
                <script>
                    (() => {
                        const dialog = document.getElementById('publicStallDetails');
                        const section = dialog?.querySelector('[data-stall-section]');
                        const number = dialog?.querySelector('[data-stall-number]');
                        const status = dialog?.querySelector('[data-stall-status]');
                        const rate = dialog?.querySelector('[data-stall-rate]');
                        const description = dialog?.querySelector('[data-stall-description]');
                        const apply = dialog?.querySelector('[data-apply-stall]');

                        document.querySelectorAll('.wireframe-stall-square').forEach((stall) => {
                            stall.addEventListener('click', () => {
                                if (!dialog || !section || !number || !status || !rate || !description || !apply) return;

                                section.textContent = `${stall.dataset.section} SECTION`;
                                number.textContent = stall.dataset.number;
                                status.textContent = stall.dataset.status;
                                status.className = `status status-${stall.dataset.status.toLowerCase()}`;
                                rate.textContent = `PHP ${stall.dataset.rate}`;
                                description.textContent = stall.dataset.description;
                                apply.toggleAttribute('aria-disabled', stall.dataset.status !== 'AVAILABLE');
                                apply.textContent = stall.dataset.status === 'AVAILABLE' ? 'Apply for Stall' : 'Not Available';
                                dialog?.showModal();
                            });
                        });

                        document.querySelector('[data-stall-details-open]')?.addEventListener('click', () => dialog?.showModal());
                        dialog?.querySelectorAll('[data-close-stall-details]').forEach((button) => button.addEventListener('click', () => dialog.close()));
                    })();
                </script>
            </section>
        @elseif ($screen === 'stall-application')
            @guest
                <section class="tenant-login-required">
                    <img src="{{ asset('assets/einspect/USERS/5-Tenants.png') }}" alt="Tenant account">
                    <h1>Tenant login required</h1>
                    <p>Please log in as a tenant before submitting a stall rental application.</p>
                    <div>
                        <a class="button button-primary" href="{{ route('public.roles', 'login') }}#login-tenant">Log in as Tenant</a>
                        <a class="button button-muted" href="{{ route('public.roles', 'register') }}#register-tenant">Create Tenant Account</a>
                    </div>
                </section>
            @else
                @if (auth()->user()->isRole('TENANT'))

                @else
                    <section class="tenant-login-required">
                        <img src="{{ asset('assets/einspect/HOMEPAGE/Logo.png') }}" alt="Pandan market">
                        <h1>Tenant access only</h1>
                        <p>This request is available for tenant accounts. Please use the tenant login to continue.</p>
                        <div><a class="button button-primary" href="{{ route('public.roles', 'login') }}#login-tenant">Log in as Tenant</a></div>
                    </section>
                @endif
            @endguest

            @auth
                @unless (auth()->user()->isRole('TENANT'))
                @else
            <section class="public-document-form public-stall-application" hidden>
                <form action="{{ route('public.stall-application.store') }}" method="POST" enctype="multipart/form-data" class="wireframe-stall-form">
                    @csrf
                    <label class="document-upload">
                        <input type="file" name="documents[]" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" multiple required data-document-input>
                        <i class="bi bi-file-earmark-arrow-up"></i>
                        <strong>Upload Requirements</strong>
                        <span>Barangay business permit, valid ID, and supporting documents</span>
                        <small>PDF, JPG, JPEG, PNG, DOC, or DOCX &middot; up to 10 MB each</small>
                        <ul class="document-upload-list" data-document-list></ul>
                    </label>
                    <div class="stall-form-fields">
                        <div class="stall-form-heading"><img src="{{ asset('assets/einspect/HOMEPAGE/Logo.png') }}" alt=""><div><span>STALL RENTAL</span><h1>Application Request Form</h1></div></div>
                        <fieldset>
                            <legend>A. Requestor Information</legend>
                            <div class="form-grid">
                                <label>Complete Name<input name="business_owner" value="{{ old('business_owner') }}" required></label>
                                <label>TIN Number<input name="tin_number" value="{{ old('tin_number') }}" placeholder="000-000-000-000" maxlength="15" inputmode="numeric" required></label>
                                <label>Birth Date<input type="date" name="birth_date" value="{{ old('birth_date') }}" required></label>
                                <label>Address<input name="business_address" value="{{ old('business_address') }}" required></label>
                                <label>Civil Status<select name="civil_status" required><option>SINGLE</option><option>MARRIED</option><option>WIDOWED</option><option>SEPARATED</option></select></label>
                                <label>Email Address<input type="email" name="email" value="{{ old('email') }}" required></label>
                                <label>Contact Number<input name="contact_number" value="{{ old('contact_number') }}" required></label>
                                <label>Sex<select name="sex" required><option>MALE</option><option>FEMALE</option></select></label>
                            </div>
                        </fieldset>
                        <fieldset>
                            <legend>B. Business Information</legend>
                            <div class="form-grid">
                                <label>Type of Business<input name="business_name" value="{{ old('business_name') }}" required></label>
                                <label>Nature of Business<input name="business_nature" value="{{ old('business_nature') }}" required></label>
                                <label>Category<input name="business_category" value="{{ old('business_category') }}" required></label>
                                <label>Business Trade Name<input name="trade_name" value="{{ old('trade_name') }}" required></label>
                                <label>Business Permit Date Issued<input type="date" name="permit_issued_at" value="{{ old('permit_issued_at') }}"></label>
                                <label>Other Business<input name="other_business" value="{{ old('other_business') }}"></label>
                            </div>
                        </fieldset>
                        <fieldset>
                            <legend>C. Stall Preference</legend>
                            <div class="form-grid">
                                <label>Preferred Stall Section<select name="preferred_section" required>@foreach (['FISH','PORK','POULTRY','BEEF','MIXED'] as $section)<option>{{ $section }}</option>@endforeach</select></label>
                                <label>Preferred Stall Number<input type="number" min="1" name="preferred_stall_number" value="{{ old('preferred_stall_number') }}"></label>
                            </div>
                        </fieldset>
                        <div class="dialog-actions stall-application-actions"><button class="button button-primary">Submit</button><a href="{{ route('public.service', 'stall-rental') }}" class="button button-muted">Cancel</a></div>
                    </div>
                </form>
                <script>
                    (() => {
                        const input = document.querySelector('[data-document-input]');
                        const list = document.querySelector('[data-document-list]');
                        let files = [];

                        const renderFiles = () => {
                            if (!input || !list) return;

                            list.innerHTML = '';
                            files.forEach((file, index) => {
                                const item = document.createElement('li');
                                item.innerHTML = `<i class="bi bi-paperclip"></i><span>${file.name}</span><small>${Math.ceil(file.size / 1024)} KB</small><button type="button" aria-label="Remove ${file.name}" data-remove-document="${index}">&times;</button>`;
                                list.appendChild(item);
                            });
                        };

                        const syncInput = () => {
                            if (!input) return;

                            const transfer = new DataTransfer();
                            files.forEach((file) => transfer.items.add(file));
                            input.files = transfer.files;
                        };

                        input?.addEventListener('change', () => {
                            files = [...input.files];
                            renderFiles();
                        });

                        list?.addEventListener('click', (event) => {
                            const button = event.target.closest('[data-remove-document]');
                            if (!button) return;

                            event.preventDefault();
                            files.splice(Number(button.dataset.removeDocument), 1);
                            syncInput();
                            renderFiles();
                        });
                    })();
                </script>
            </section>
                @endunless
            @endauth
        @elseif ($screen === 'slaughtered-inspection')
            <section class="slaughter-house-screen">
                <img class="slaughter-inspector" src="{{ asset('assets/einspect/USERS/J-Inspector.png') }}" alt="Sanitary inspector">
                <img class="slaughter-house-image" src="{{ asset('assets/einspect/HOMEPAGE/Slaughtered House.png') }}" alt="Slaughtered livestock house">
                <a class="button button-primary slaughter-request-button" href="{{ route('public.service', 'inspection-request') }}"><i class="bi bi-house-heart"></i> Request Inspection</a>
            </section>
        @elseif ($screen === 'inspection-request')
            @php
                $livestockChoices = [
                    'BEEF' => ['label' => 'BEEF', 'image' => 'Beef Section.png', 'class' => 'beef', 'icon' => 'bi bi-cow'],
                    'POULTRY' => ['label' => 'POULTRY', 'image' => 'Poultry Section.png', 'class' => 'poultry', 'icon' => 'bi bi-egg-fried'],
                    'PORK' => ['label' => 'PORK', 'image' => 'Pork Section.png', 'class' => 'pork', 'icon' => 'bi bi-piggy-bank-fill'],
                ];
            @endphp
            @guest
                <section class="tenant-login-required">
                    <img src="{{ asset('assets/einspect/USERS/5-Tenants.png') }}" alt="Tenant account">
                    <h1>Tenant login required</h1>
                    <p>Please log in as a tenant before requesting a slaughtered livestock inspection.</p>
                    <div>
                        <a class="button button-primary" href="{{ route('public.roles', 'login') }}#login-tenant">Log in as Tenant</a>
                        <a class="button button-muted" href="{{ route('public.roles', 'register') }}#register-tenant">Create Tenant Account</a>
                    </div>
                </section>
            @else
                @if (auth()->user()->isRole('TENANT'))

                @else
                    <section class="tenant-login-required">
                        <img src="{{ asset('assets/einspect/HOMEPAGE/Logo.png') }}" alt="Pandan market">
                        <h1>Tenant access only</h1>
                        <p>This request is available for tenant accounts. Please use the tenant login to continue.</p>
                        <div><a class="button button-primary" href="{{ route('public.roles', 'login') }}#login-tenant">Log in as Tenant</a></div>
                    </section>
                @endif
            @endguest

            @auth
                @unless (auth()->user()->isRole('TENANT'))
                @else
            <section class="livestock-choice-screen">
                <div class="section-heading light livestock-choice-heading">
                    <img src="{{ asset('assets/einspect/HOMEPAGE/Logo.png') }}" alt="">
                    <h1>SLAUGHTERED LIVESTOCK INSPECTION</h1>
                    <p>Please select Slaughtered Livestock category for an area inspection.</p>
                </div>
                <div class="livestock-choice-grid">
                    @foreach ($livestockChoices as $type => $choice)
                        <a href="#inspection-{{ strtolower($type) }}" class="livestock-choice-card livestock-choice-{{ $choice['class'] }}">
                            <img src="{{ asset('assets/einspect/HOMEPAGE/'.$choice['image']) }}" alt="{{ $choice['label'] }}">
                            <span><i class="{{ $choice['icon'] }}"></i>{{ $choice['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </section>

            @foreach ($livestockChoices as $type => $choice)
                @php
                    $monthlyInspections = ($inspections->get($type, collect()))
                        ->filter(fn ($inspection) => $inspection->scheduled_at->isSameMonth($inspectionMonth));
                    $reservedDays = $monthlyInspections->pluck('scheduled_at')->map->day->unique()->values()->all();
                    $reservationDetails = $monthlyInspections
                        ->groupBy(fn ($inspection) => (string) $inspection->scheduled_at->day)
                        ->map(fn ($dayInspections) => $dayInspections->map(fn ($inspection) => [
                            'time' => $inspection->scheduled_at->format('g:i A'),
                            'request_number' => $inspection->request_number,
                            'owner_name' => $inspection->owner_name,
                            'status' => $inspection->status,
                        ])->values())
                        ->toArray();
                @endphp
                <section id="inspection-{{ strtolower($type) }}" class="inspection-request-modal">
                    <button type="button" class="modal-backdrop" aria-label="Close" data-close-public-modal></button>
                    <form action="{{ route('public.inspection.store') }}" method="POST" class="inspection-modal-card" data-inspection-form data-reservations='@json($reservationDetails)'>
                        @csrf
                        <button type="button" class="login-close" aria-label="Close" data-close-public-modal><i class="bi bi-x-circle-fill"></i></button>
                        <input type="hidden" name="livestock_type" value="{{ $type }}">
                        <input type="hidden" name="owner_name" data-owner-name>
                        <input type="hidden" name="address" data-owner-address>
                        <input type="hidden" name="scheduled_at" data-scheduled-at>
                        <input type="hidden" name="animal_count" value="1">
                        <input type="hidden" name="email">

                        <div class="inspection-calendar-panel">
                            <div class="inspection-calendar-header"><strong>{{ $inspectionMonth->format('n') }}</strong><span>{{ strtoupper($inspectionMonth->format('F')) }}</span><strong>{{ $inspectionMonth->format('Y') }}</strong></div>
                            <div class="inspection-calendar-weekdays">@foreach (['SUN','MON','TUE','WED','THU','FRI','SAT'] as $day)<span>{{ $day }}</span>@endforeach</div>
                            <div class="inspection-calendar-days">
                                @for ($blank = 0; $blank < $inspectionMonth->dayOfWeek; $blank++)<span class="blank"></span>@endfor
                                @for ($day = 1; $day <= $inspectionMonth->daysInMonth; $day++)
                                    @php
                                        $date = \Carbon\Carbon::create($inspectionMonth->year, $inspectionMonth->month, $day);
                                        $isBooked = in_array($day, $reservedDays);
                                        $isUnavailable = ! $isBooked && ($day < now()->day || $date->isWeekend());
                                    @endphp
                                    <button type="button" @class(['reserved' => $isBooked, 'unavailable' => $isUnavailable, 'available' => ! $isBooked && ! $isUnavailable]) data-inspection-day="{{ $day }}">{{ $day }}@if($isBooked)<small>RESERVED</small>@elseif($isUnavailable)<small>UNAVAILABLE</small>@endif</button>
                                @endfor
                            </div>
                            <div class="reserved-note" data-reserved-note hidden>
                                <button type="button" aria-label="Close reserved time" data-close-reserved-note>&times;</button>
                                <strong>RESERVED TIME</strong>
                                <div data-reserved-details></div>
                            </div>
                            <div class="calendar-status-legend"><span><i class="available"></i>Available</span><span><i class="reserved"></i>Reserved / unavailable</span></div>
                            <div class="inspector-contact"><img src="{{ asset('assets/einspect/USERS/J-Inspector.png') }}" alt=""><strong>EDWIN C. GREGORIO</strong><span>Rural Sanitary Inspector I</span><i class="bi bi-telephone-fill"></i><span>09679050621</span><i class="bi bi-envelope-fill"></i><span>edwingregorio@gmail.com</span></div>
                        </div>

                        <div class="inspection-form-panel">
                            <div class="inspection-form-heading">
                                <i class="{{ $choice['icon'] }}"></i>
                                <h1>REQUEST INSPECTION FORM</h1>
                                <p>({{ ucfirst(strtolower($type)) }} Slaughtered Livestock)</p>
                            </div>
                            <h2>REQUESTER INFORMATION:</h2>
                            <label>Complete Name:<b>*</b><span class="triple-input"><input name="first_name" placeholder="First Name" required><input name="middle_name" placeholder="Middle Name"><input name="last_name" placeholder="Last Name" required></span></label>
                            <label>Address:<b>*</b><span class="triple-input"><input name="barangay" placeholder="Barangay" required><input name="municipality" value="Pandan" required><input name="province" value="Antique" required></span></label>
                            <div class="form-grid two">
                                <label>Date of Inspection:<b>*</b><span><input type="date" data-date-input required><i class="bi bi-calendar3"></i></span></label>
                                <label>Time of Inspection:<b>*</b><span><input type="time" data-time-input required><i class="bi bi-alarm"></i></span></label>
                            </div>
                            <label>Contact Number:<b>*</b><input name="contact_number" required></label>
                            <button class="button button-primary"><i class="bi bi-send-fill"></i> Submit</button>
                        </div>
                    </form>
                </section>
            @endforeach
            </section>
                @endunless
            @endauth
        @else
            <section class="public-announcement-screen">
                <div class="announcement-summary">
                    <article><span>Total Tenants</span><div><i class="bi bi-people-fill"></i><strong>{{ \App\Models\User::where('usertype', 'TENANT')->count() }}</strong></div></article>
                    <article><span>Total Stalls</span><div><i class="bi bi-shop-window"></i><strong>{{ \App\Models\Stall::count() }}</strong></div></article>
                    <article><span>Total Stalls<br>( Available )</span><div><i class="bi bi-shop"></i><strong>{{ \App\Models\Stall::where('status', 'AVAILABLE')->count() }}</strong></div></article>
                    <article><span>Total Stalls<br>( Occupied )</span><div><i class="bi bi-shop"></i><strong>{{ \App\Models\Stall::where('status', 'OCCUPIED')->count() }}</strong></div></article>
                </div>
                <div class="wireframe-announcements">
                    <i class="bi bi-megaphone-fill announcement-megaphone"></i>
                    <nav class="announcement-tabs" aria-label="Announcement categories">
                        @foreach (['ALL' => 'All', 'OTHERS' => 'Others', 'MARKET ADVISORY' => 'Market Advisory', 'BIDDING' => 'Bidding', 'STALL RENTAL' => 'Stall Rental', 'SLAUGHTERED INSPECT' => 'Slaughtered Inspect'] as $category => $tab)
                            <button type="button" @class(['active' => $loop->first]) data-announcement-filter="{{ $category }}">{{ $tab }}</button>
                        @endforeach
                    </nav>
                    <div class="announcement-wire-list">
                        @forelse ($announcements as $announcement)
                            <article data-announcement-category="{{ $announcement->category }}">
                                <img src="{{ asset('assets/einspect/USERS/G-Administrator.png') }}" alt="Market administrator">
                                <div>
                                    <h2>{{ optional($announcement->published_at)->format('F j, Y | g:i A') ?? $announcement->created_at->format('F j, Y | g:i A') }}</h2>
                                    <strong>{{ $announcement->title }}</strong>
                                    <p>{{ $announcement->content }}</p>
                                </div>
                                <a href="#announcement-{{ $announcement->id }}">View Full Details <i class="bi bi-arrow-right"></i></a>
                            </article>
                        @empty
                            <p class="empty-state wireframe-empty announcement-empty">No announcement available</p>
                        @endforelse
                    </div>
                </div>
            </section>

            @foreach ($announcements as $announcement)
                <section id="announcement-{{ $announcement->id }}" class="announcement-detail-modal">
                    <button type="button" class="modal-backdrop" aria-label="Close" data-close-public-modal></button>
                    <article>
                        <button type="button" class="login-close" aria-label="Close" data-close-public-modal><i class="bi bi-x-circle-fill"></i></button>
                        <span>{{ $announcement->category }}</span>
                        <h1>{{ $announcement->title }}</h1>
                        <small>{{ optional($announcement->published_at)->format('F j, Y g:i A') ?? $announcement->created_at->format('F j, Y g:i A') }}</small>
                        <p>{{ $announcement->content }}</p>
                        @if ($announcement->attachment_path)
                            <a class="button button-primary" href="{{ route('announcements.attachment', $announcement) }}"><i class="bi bi-paperclip"></i> Download Attachment</a>
                        @endif
                    </article>
                </section>
            @endforeach
        @endif
    </main>

    @if ($screen === 'announcements')
        <script>
            document.querySelectorAll('[data-announcement-filter]').forEach((button) => {
                button.addEventListener('click', () => {
                    const selected = button.dataset.announcementFilter;
                    document.querySelectorAll('[data-announcement-filter]').forEach((tab) => tab.classList.toggle('active', tab === button));
                    document.querySelectorAll('[data-announcement-category]').forEach((article) => {
                        article.hidden = selected !== 'ALL' && article.dataset.announcementCategory !== selected;
                    });
                });
            });
        </script>
    @endif

    @if ($screen === 'inspection-request')
        <script>
            document.querySelectorAll('[data-inspection-form]').forEach((form) => {
                const dateInput = form.querySelector('[data-date-input]');
                const timeInput = form.querySelector('[data-time-input]');
                const reservedNote = form.querySelector('[data-reserved-note]');
                const reservedDetails = form.querySelector('[data-reserved-details]');
                const reservations = JSON.parse(form.dataset.reservations || '{}');

                form.querySelectorAll('[data-inspection-day]').forEach((dayButton) => {
                    dayButton.addEventListener('click', () => {
                        const day = dayButton.dataset.inspectionDay;

                        if (dayButton.classList.contains('reserved')) {
                            if (!reservedNote || !reservedDetails) return;

                            const details = reservations[day] || [];
                            reservedDetails.innerHTML = details.length
                                ? details.map((inspection) => `<article><b>${inspection.time}</b><span>${inspection.request_number}</span><small>${inspection.owner_name} &middot; ${inspection.status}</small></article>`).join('')
                                : '<p>No reservation details found.</p>';
                            reservedNote.hidden = false;
                            form.querySelectorAll('[data-inspection-day]').forEach((button) => button.classList.toggle('selected', button === dayButton));
                            return;
                        }

                        if (dayButton.classList.contains('unavailable')) return;

                        reservedNote?.setAttribute('hidden', '');

                        const paddedDay = day.padStart(2, '0');
                        const month = String({{ $inspectionMonth->month }}).padStart(2, '0');
                        dateInput.value = `{{ $inspectionMonth->year }}-${month}-${paddedDay}`;
                        form.querySelectorAll('[data-inspection-day]').forEach((button) => button.classList.toggle('selected', button === dayButton));
                    });
                });

                form.querySelector('[data-close-reserved-note]')?.addEventListener('click', () => {
                    reservedNote?.setAttribute('hidden', '');
                });

                form.addEventListener('submit', () => {
                    const firstName = form.querySelector('[name="first_name"]').value.trim();
                    const middleName = form.querySelector('[name="middle_name"]').value.trim();
                    const lastName = form.querySelector('[name="last_name"]').value.trim();
                    const barangay = form.querySelector('[name="barangay"]').value.trim();
                    const municipality = form.querySelector('[name="municipality"]').value.trim();
                    const province = form.querySelector('[name="province"]').value.trim();

                    form.querySelector('[data-owner-name]').value = [firstName, middleName, lastName].filter(Boolean).join(' ');
                    form.querySelector('[data-owner-address]').value = [barangay, municipality, province].filter(Boolean).join(', ');
                    form.querySelector('[data-scheduled-at]').value = `${dateInput.value} ${timeInput.value || '09:30'}:00`;
                });
            });
        </script>
    @endif

    <script>
        const closePublicModal = () => {
            if (!location.hash) return;

            location.hash = '_';
            history.replaceState(null, '', location.pathname + location.search);
        };

        document.querySelectorAll('[data-close-public-modal]').forEach((button) => {
            button.addEventListener('click', closePublicModal);
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') closePublicModal();
        });
    </script>

    <footer class="public-footer">&copy; Copyright {{ date('Y') }}. Developed by KAJS CODERS INVADER. All Rights Reserved</footer>
@endsection
