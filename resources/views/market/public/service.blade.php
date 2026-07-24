@extends('market.layouts.public')

@section('title', 'E-Inspect | '.str($screen)->replace('-', ' ')->title())

@section('content')
    <header class="public-header">
        <a href="{{ route('home') }}" class="public-brand">
            <img src="{{ asset('assets/einspect/HOMEPAGE/Logo.png') }}" alt="Pandan Public Market">
            <span>PANDAN MARKET</span>
        </a>
        <nav>
            <a href="{{ route('home') }}"><i class="bi bi-house-door-fill"></i> Home</a>
            <a href="{{ route('public.service', 'contact') }}"><i class="bi bi-envelope-fill"></i> Contact</a>
            <a href="{{ route('public.service', 'stall-rental') }}"><i class="bi bi-shop"></i> Stall Rental</a>
            <a href="{{ route('public.service', 'slaughtered-inspection') }}"><i class="bi bi-clipboard2-pulse"></i> Slaughtered Inspect</a>
            <a href="{{ route('public.service', 'announcements') }}"><i class="bi bi-megaphone-fill"></i> Announcements</a>
        </nav>
    </header>

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
                <img class="public-feature-character" src="{{ asset('assets/einspect/USERS/H-Treasurer.png') }}" alt="Market treasurer">
                <img class="public-feature-image" src="{{ asset('assets/einspect/HOMEPAGE/Stall.png') }}" alt="Market stall">
                <div class="public-feature-actions">
                    <a class="button button-primary" href="{{ route('public.service', 'stall-application') }}"><i class="bi bi-file-earmark-text"></i> Application</a>
                    <a class="button button-primary" href="{{ route('public.service', 'stall-location') }}"><i class="bi bi-geo-alt"></i> Location</a>
                </div>
            </section>
        @elseif ($screen === 'stall-location')
            <section class="public-map-card">
                <div class="panel-heading"><div><span class="eyebrow">Public Market Pandan, Antique</span><h1>Stall Location</h1></div></div>
                <div class="stall-map-layout">
                    @foreach (['FISH', 'PORK', 'POULTRY', 'BEEF', 'MIXED'] as $section)
                        <section class="stall-section section-{{ strtolower($section) }}">
                            <div class="section-title"><span>{{ $section }} SECTION</span></div>
                            <div class="stall-grid">
                                @foreach ($stalls->get($section, collect()) as $stall)
                                    <span class="stall-box {{ strtolower($stall->status) }}" title="{{ $stall->status }}">{{ $stall->stall_number }}</span>
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                </div>
                <div class="map-legend"><span><i class="available"></i> Available</span><span><i class="occupied"></i> Occupied</span><span><i class="maintenance"></i> Maintenance</span></div>
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
                        <small>PDF, JPG, JPEG, or PNG · up to 10 MB each</small>
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
                        <div class="dialog-actions"><a href="{{ route('home') }}" class="button button-muted">Cancel</a><button class="button button-primary">Submit</button></div>
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
                        @for ($blank = 0; $blank < now()->startOfMonth()->dayOfWeek; $blank++)<span class="blank"></span>@endfor
                        @for ($day = 1; $day <= now()->daysInMonth; $day++)<span class="{{ $day >= now()->day ? 'available' : 'occupied' }}">{{ $day }}</span>@endfor
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
                    <article><strong>{{ \App\Models\User::where('usertype', 'TENANT')->count() }}</strong><span>Total Tenants</span></article>
                    <article><strong>{{ \App\Models\Stall::count() }}</strong><span>Total Stalls</span></article>
                    <article><strong>{{ \App\Models\Stall::where('status', 'AVAILABLE')->count() }}</strong><span>Available</span></article>
                    <article><strong>{{ \App\Models\Stall::where('status', 'OCCUPIED')->count() }}</strong><span>Occupied</span></article>
                </div>
                <div class="portal-announcement-list">
                    @forelse ($announcements as $announcement)
                        <article><span>{{ $announcement->category }}</span><h2>{{ $announcement->title }}</h2><p>{{ $announcement->content }}</p><small>{{ optional($announcement->published_at)->format('F j, Y g:i A') }}</small></article>
                    @empty
                        <p class="empty-state">No announcements published.</p>
                    @endforelse
                </div>
            </section>
        @endif
    </main>

    <footer class="public-footer">© {{ date('Y') }} Pandan Public Market. All rights reserved.</footer>
@endsection
