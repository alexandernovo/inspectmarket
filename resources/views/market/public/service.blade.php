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
                <div class="contact-character"><img src="{{ asset('assets/einspect/USERS/G-Administrator.png') }}" alt="Market administrator"><h1>Let's Get in Touch!</h1></div>
                <div class="contact-seal"><img src="{{ asset('assets/einspect/HOMEPAGE/Logo.png') }}" alt=""><strong>Connect with Us</strong><i class="bi bi-facebook"></i></div>
                <form action="{{ route('contact.store') }}" method="POST" class="wireframe-form">
                    @csrf
                    <h2>Contact Us</h2>
                    <label>Name<input name="name" required></label>
                    <label>Address<input name="address"></label>
                    <label>Contact Number<input name="contact_number" required></label>
                    <label>Email<input type="email" name="email"></label>
                    <label>Message<textarea name="message" rows="5" required></textarea></label>
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
            <section class="public-document-form public-stall-application">
                <form action="{{ route('public.stall-application.store') }}" method="POST" enctype="multipart/form-data" class="wireframe-stall-form">
                    @csrf
                    <label class="document-upload">
                        <input type="file" name="documents[]" accept=".pdf,.jpg,.jpeg,.png" multiple required>
                        <i class="bi bi-file-earmark-arrow-up"></i>
                        <strong>Upload Requirements</strong>
                        <span>Barangay business permit, valid ID, and supporting documents</span>
                        <small>PDF, JPG, JPEG, or PNG &middot; up to 10 MB each</small>
                    </label>
                    <div class="stall-form-fields">
                        <div class="stall-form-heading"><img src="{{ asset('assets/einspect/HOMEPAGE/Logo.png') }}" alt=""><div><span>STALL RENTAL</span><h1>Application Request Form</h1></div></div>
                        <fieldset>
                            <legend>A. Requestor Information</legend>
                            <div class="form-grid">
                                <label>Complete Name<input name="business_owner" value="{{ old('business_owner') }}" required></label>
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
                        <div class="dialog-actions"><a href="{{ route('public.service', 'stall-rental') }}" class="button button-muted">Cancel</a><button class="button button-primary">Submit</button></div>
                    </div>
                </form>
            </section>
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
                    $reservedDays = ($inspections->get($type, collect()))
                        ->filter(fn ($inspection) => $inspection->scheduled_at->isSameMonth($inspectionMonth))
                        ->pluck('scheduled_at')
                        ->map->day
                        ->all();
                @endphp
                <section id="inspection-{{ strtolower($type) }}" class="inspection-request-modal">
                    <a href="{{ route('public.service', 'inspection-request') }}" class="modal-backdrop" aria-label="Close"></a>
                    <form action="{{ route('public.inspection.store') }}" method="POST" class="inspection-modal-card" data-inspection-form>
                        @csrf
                        <a href="{{ route('public.service', 'inspection-request') }}" class="login-close" aria-label="Close"><i class="bi bi-x-circle-fill"></i></a>
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
                                    <button type="button" @class(['reserved' => in_array($day, $reservedDays) || $day < now()->day || \Carbon\Carbon::create($inspectionMonth->year, $inspectionMonth->month, $day)->isWeekend(), 'available' => ! in_array($day, $reservedDays) && $day >= now()->day && ! \Carbon\Carbon::create($inspectionMonth->year, $inspectionMonth->month, $day)->isWeekend()]) data-inspection-day="{{ $day }}">{{ $day }}@if(in_array($day, $reservedDays))<small>RESERVED</small>@endif</button>
                                @endfor
                            </div>
                            <div class="reserved-note"><strong>RESERVED TIME</strong><span>A.M<br>9:30 - 10:30 AM<br>11:00 - 12:00 PM</span><span>P.M</span></div>
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
                                <img src="{{ asset('assets/einspect/USERS/J-Inspector.png') }}" alt="Market staff">
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
                    <a href="{{ route('public.service', 'announcements') }}" class="modal-backdrop" aria-label="Close"></a>
                    <article>
                        <a href="{{ route('public.service', 'announcements') }}" class="login-close" aria-label="Close"><i class="bi bi-x-circle-fill"></i></a>
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

                form.querySelectorAll('[data-inspection-day]').forEach((dayButton) => {
                    dayButton.addEventListener('click', () => {
                        if (dayButton.classList.contains('reserved')) return;

                        const day = dayButton.dataset.inspectionDay.padStart(2, '0');
                        const month = String({{ $inspectionMonth->month }}).padStart(2, '0');
                        dateInput.value = `{{ $inspectionMonth->year }}-${month}-${day}`;
                        form.querySelectorAll('[data-inspection-day]').forEach((button) => button.classList.toggle('selected', button === dayButton));
                    });
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

    <footer class="public-footer">&copy; {{ date('Y') }} Pandan Public Market. All rights reserved.</footer>
@endsection
