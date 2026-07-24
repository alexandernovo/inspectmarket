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
                        'FISH' => ['count' => 20, 'image' => 'Fish Section.png', 'class' => 'fish'],
                        'PORK' => ['count' => 14, 'image' => 'Pork Section.png', 'class' => 'pork'],
                        'POULTRY' => ['count' => 2, 'image' => 'Poultry Section.png', 'class' => 'poultry'],
                        'BEEF' => ['count' => 12, 'image' => 'Beef Section.png', 'class' => 'beef'],
                        'MIXED' => ['count' => 84, 'image' => 'Mixed Section.png', 'class' => 'mixed'],
                    ];

                    $statusFor = function (string $section, int $number) use ($stalls) {
                        $stall = $stalls->get($section, collect())->firstWhere('stall_number', $number);

                        return $stall?->status ?? ($number % 3 === 1 ? 'OCCUPIED' : 'AVAILABLE');
                    };
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
                            @for ($number = 1; $number <= $meta['count']; $number++)
                                @php $status = $statusFor($section, $number); @endphp
                                <button type="button" class="wireframe-stall-square {{ strtolower($status) }}" data-section="{{ $section }}" data-number="{{ $number }}" data-status="{{ $status }}">{{ $number }}</button>
                            @endfor
                        </div>
                    </section>
                @endforeach
                <dialog id="publicStallDetails" class="market-dialog compact-dialog public-stall-detail-dialog">
                    <div class="dialog-heading"><div><span>Public Market</span><h2>Stall Details</h2></div><button type="button" data-close-stall-details>&times;</button></div>
                    <dl class="record-details">
                        <dt>Section</dt><dd data-stall-section>Choose a stall</dd>
                        <dt>Stall Number</dt><dd data-stall-number>-</dd>
                        <dt>Status</dt><dd><span class="status" data-stall-status>-</span></dd>
                    </dl>
                    <div class="dialog-actions"><button type="button" class="button button-muted" data-close-stall-details>Close</button><a href="{{ route('public.service', 'stall-application') }}" class="button button-primary">Apply for Stall</a></div>
                </dialog>
                <script>
                    (() => {
                        const dialog = document.getElementById('publicStallDetails');
                        const section = dialog?.querySelector('[data-stall-section]');
                        const number = dialog?.querySelector('[data-stall-number]');
                        const status = dialog?.querySelector('[data-stall-status]');

                        document.querySelectorAll('.wireframe-stall-square').forEach((stall) => {
                            stall.addEventListener('click', () => {
                                if (!dialog || !section || !number || !status) return;

                                section.textContent = `${stall.dataset.section} SECTION`;
                                number.textContent = stall.dataset.number;
                                status.textContent = stall.dataset.status;
                                status.className = `status status-${stall.dataset.status.toLowerCase()}`;
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
            <section class="public-feature-screen livestock-selection">
                <img class="public-feature-character" src="{{ asset('assets/einspect/USERS/J-Inspector.png') }}" alt="">
                <div class="section-heading light"><img src="{{ asset('assets/einspect/HOMEPAGE/Logo.png') }}" alt=""><h1>SLAUGHTERED LIVESTOCK INSPECTION</h1><p>Please select slaughtered livestock category for an inspection.</p></div>
                <div class="livestock-grid">
                    @foreach ([['BEEF','Beef Section.png'],['POULTRY','Poultry Section.png'],['PORK','Pork Section.png']] as [$type, $image])
                        <a href="{{ route('public.service', 'inspection-request') }}?type={{ $type }}"><img src="{{ asset('assets/einspect/HOMEPAGE/'.$image) }}" alt=""><span>{{ $type }}</span></a>
                    @endforeach
                </div>
            </section>
        @elseif ($screen === 'inspection-request')
            @php $selectedType = in_array(request('type'), ['POULTRY','PORK','BEEF']) ? request('type') : 'POULTRY'; @endphp
            <section class="public-inspection-request">
                <div class="request-calendar">
                    <div class="calendar-heading"><strong>{{ now()->format('m') }}</strong><span>{{ now()->format('F') }}</span><strong>{{ now()->format('Y') }}</strong></div>
                    <div class="calendar-weekdays">@foreach (['SUN','MON','TUE','WED','THU','FRI','SAT'] as $day)<span>{{ $day }}</span>@endforeach</div>
                    <div class="calendar-days">
                        @for ($blank = 0; $blank < $inspectionMonth->dayOfWeek; $blank++)<span class="blank"></span>@endfor
                        @for ($day = 1; $day <= $inspectionMonth->daysInMonth; $day++)<span class="{{ $day >= now()->day ? 'available' : 'occupied' }}">{{ $day }}</span>@endfor
                    </div>
                </div>
                <form action="{{ route('public.inspection.store') }}" method="POST" class="wireframe-form">
                    @csrf
                    <input type="hidden" name="livestock_type" value="{{ $selectedType }}">
                    <h1><i class="bi bi-clipboard2-pulse"></i> Request Inspection Form</h1>
                    <p>{{ ucfirst(strtolower($selectedType)) }} slaughtered livestock</p>
                    <label>Complete Name<input name="owner_name" required></label>
                    <label>Address<input name="address" required></label>
                    <label>Date and Time of Inspection<input type="datetime-local" name="scheduled_at" required></label>
                    <label>Contact Number<input name="contact_number" required></label>
                    <label>Email Address<input type="email" name="email"></label>
                    <label>Number of Animals<input type="number" name="animal_count" value="1" min="1" required></label>
                    <button class="button button-primary"><i class="bi bi-send-fill"></i> Submit</button>
                </form>
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
                            <p class="empty-state">No announcements published.</p>
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

    <footer class="public-footer">&copy; {{ date('Y') }} Pandan Public Market. All rights reserved.</footer>
@endsection
