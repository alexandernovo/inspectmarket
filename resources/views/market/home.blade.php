@extends('market.layouts.public')

@section('title', 'E-Inspect | Pandan Public Market')

@section('content')
    <header class="public-header">
        <a href="#home" class="public-brand">
            <img src="{{ asset('assets/einspect/HOMEPAGE/Logo.png') }}" alt="Pandan Public Market logo">
            <span>PANDAN MARKET</span>
        </a>
        <button type="button" class="mobile-nav-toggle" aria-label="Toggle navigation"><i class="bi bi-list"></i></button>
        <nav>
            <a href="#home"><i class="bi bi-house-door-fill"></i> Home</a>
            <a href="{{ route('public.service', 'contact') }}"><i class="bi bi-envelope-fill"></i> Contact</a>
            <a href="{{ route('public.service', 'stall-rental') }}"><i class="bi bi-shop"></i> Stall Rental</a>
            <a href="{{ route('public.service', 'slaughtered-inspection') }}"><i class="bi bi-clipboard2-pulse"></i> Slaughtered Inspect</a>
            <a href="{{ route('public.service', 'announcements') }}"><i class="bi bi-megaphone-fill"></i> Announcements</a>
        </nav>
    </header>

    <main>
        <section class="hero" id="home">
            <div class="hero-overlay"></div>
            <div class="hero-content">
                <img src="{{ asset('assets/einspect/HOMEPAGE/Logo.png') }}" alt="Municipality of Pandan seal" class="hero-seal">
                <p>Welcome To</p>
                <h1>PANDAN PUBLIC MARKET</h1>
                <p class="hero-copy">An organized public market committed to food safety, fair trade, and sustainable local business.</p>
                <div class="hero-actions">
                    <a href="#roles" class="button button-primary" data-role-mode="register">Sign in</a>
                    <a href="#roles" class="button button-outline" data-role-mode="login">Log in</a>
                </div>
            </div>
        </section>

        <section class="roles-section" id="roles">
            <div class="section-heading">
                <span>E-INSPECT</span>
                <h2>Public Market Inspection Recording Management System</h2>
                <p id="roleSelectorMode">User-Login</p>
            </div>
            <div class="role-grid">
                @foreach ([
                    ['administrator', 'Administrator', '1-Administrator.png'],
                    ['treasurer', 'Treasurer', '2-Treasurer.png'],
                    ['inspector', 'Inspector', '4-Sanitary Inspector.png'],
                    ['clerk', 'Clerk', '3-Collector Clerk.png'],
                    ['tenant', 'Tenant', '5-Tenants.png'],
                ] as [$slug, $label, $image])
                    <a
                        href="{{ route('portal.login', ['role' => $slug]) }}"
                        class="role-card role-{{ $slug }}"
                        data-login-url="{{ route('portal.login', ['role' => $slug]) }}"
                        data-register-url="{{ route('account.register', ['role' => $slug]) }}"
                    >
                        <img src="{{ asset('assets/einspect/USERS/'.$image) }}" alt="{{ $label }}">
                        <span>{{ $label }}</span>
                    </a>
                @endforeach
            </div>
        </section>

        <section class="feature-section stall-feature" id="stall-rental">
            <div class="feature-visual">
                <img src="{{ asset('assets/einspect/HOMEPAGE/Stall.png') }}" alt="Public market stall">
            </div>
            <div class="feature-copy">
                <span class="eyebrow">Stall Rental</span>
                <h2>Find a place for your local business</h2>
                <p>View availability across fish, pork, poultry, beef, and mixed sections. Tenant applications and payment records stay in one traceable workflow.</p>
                <div class="mini-stat-grid">
                    @forelse ($stallCounts as $count)
                        <div><strong>{{ $count->available }}</strong><span>{{ ucfirst(strtolower($count->section)) }} available</span></div>
                    @empty
                        <div><strong>132</strong><span>Mapped market stalls</span></div>
                    @endforelse
                </div>
                <a href="{{ route('public.service', 'stall-rental') }}" class="button button-primary">View stall rental</a>
            </div>
        </section>

        <section class="feature-section inspection-feature" id="inspection">
            <div class="feature-copy">
                <span class="eyebrow">Slaughtered Livestock Inspection</span>
                <h2>Schedule poultry, pork, and beef inspections</h2>
                <p>Tenants submit a preferred inspection date. Inspectors approve the schedule, record findings, and prepare market health reports.</p>
                <div class="livestock-grid">
                    @foreach ([
                        ['POULTRY', 'Poultry Section.png'],
                        ['PORK', 'Pork Section.png'],
                        ['BEEF', 'Beef Section.png'],
                    ] as [$label, $image])
                        <div>
                            <img src="{{ asset('assets/einspect/HOMEPAGE/'.$image) }}" alt="{{ ucfirst(strtolower($label)) }}">
                            <span>{{ $label }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="feature-visual">
                <img src="{{ asset('assets/einspect/HOMEPAGE/Slaughtered House.png') }}" alt="Slaughtered livestock inspection facility">
            </div>
        </section>

        <section class="announcements-section" id="announcements">
            <div class="section-heading light">
                <span>Public Updates</span>
                <h2>Announcements</h2>
            </div>
            <div class="announcement-list">
                @forelse ($announcements as $announcement)
                    <article>
                        <span>{{ $announcement->category }}</span>
                        <h3>{{ $announcement->title }}</h3>
                        <p>{{ \Illuminate\Support\Str::limit($announcement->content, 180) }}</p>
                        <time>{{ optional($announcement->published_at)->format('F j, Y g:i A') }}</time>
                    </article>
                @empty
                    <article>
                        <span>MARKET ADVISORY</span>
                        <h3>Welcome to E-Inspect</h3>
                        <p>Public market announcements will appear here after the Treasurer publishes them.</p>
                        <time>{{ now()->format('F j, Y') }}</time>
                    </article>
                @endforelse
            </div>
        </section>

        <section class="contact-section" id="contact">
            <div>
                <span class="eyebrow">Contact Us</span>
                <h2>Let’s get in touch</h2>
                <p>{{ $settings['market_name'] ?? 'Pandan Public Market' }}, {{ $settings['market_address'] ?? 'Pandan, Antique' }}</p>
                <p><i class="bi bi-envelope"></i> {{ $settings['market_email'] ?? 'pandanpublicmarket@example.test' }}</p>
                <p><i class="bi bi-telephone"></i> {{ $settings['market_phone'] ?? '+63 900 000 0000' }}</p>
            </div>
            <form action="{{ route('contact.store') }}" method="POST" class="public-contact-form">
                @csrf
                @if (session('success'))<div class="alert-success">{{ session('success') }}</div>@endif
                <label>Name<input name="name" required></label>
                <label>Email<input type="email" name="email"></label>
                <label>Address<input name="address"></label>
                <label>Contact number<input name="contact_number" required></label>
                <label class="full">Message<textarea name="message" rows="5" required></textarea></label>
                <button class="button button-primary">Send message</button>
            </form>
        </section>
    </main>

    <footer class="public-footer">
        <img src="{{ asset('assets/einspect/HOMEPAGE/Logo.png') }}" alt="">
        <span>© {{ date('Y') }} Pandan Public Market. All rights reserved.</span>
    </footer>

    <script>
        document.querySelector('.mobile-nav-toggle').addEventListener('click', () => {
            document.querySelector('.public-header nav').classList.toggle('open');
        });
        document.querySelectorAll('[data-role-mode]').forEach((button) => {
            button.addEventListener('click', () => {
                const registerMode = button.dataset.roleMode === 'register';
                document.getElementById('roleSelectorMode').textContent = registerMode ? 'Create an Account' : 'User-Login';
                document.querySelectorAll('.role-card[data-login-url]').forEach((card) => {
                    card.href = registerMode ? card.dataset.registerUrl : card.dataset.loginUrl;
                });
            });
        });
    </script>
@endsection
