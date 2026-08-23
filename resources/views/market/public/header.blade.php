<header class="public-header {{ $headerClass ?? '' }}">
    <a href="{{ route('home') }}" class="public-brand">
        <img src="{{ asset('assets/einspect/HOMEPAGE/Logo.png') }}" alt="Municipality of Pandan seal">
        <span>PANDAN MARKET</span>
    </a>
    <button type="button" class="mobile-nav-toggle" aria-label="Toggle navigation"><i class="bi bi-list"></i></button>
    <nav>
        <a href="{{ route('home') }}" @class(['active' => ($active ?? '') === 'home'])><img src="{{ asset('assets/einspect/HOMEPAGE/ICONS/Home.png') }}" alt=""> Home</a>
        <a href="{{ route('public.service', 'contact') }}" @class(['active' => ($active ?? '') === 'contact'])><img src="{{ asset('assets/einspect/HOMEPAGE/ICONS/Contact.png') }}" alt=""> Contact</a>
        <a href="{{ route('public.service', 'stall-rental') }}" @class(['active' => ($active ?? '') === 'stall-rental'])><img src="{{ asset('assets/einspect/HOMEPAGE/ICONS/Stall Rental.png') }}" alt=""> Stall Rental</a>
        <a href="{{ route('public.service', 'slaughtered-inspection') }}" @class(['active' => ($active ?? '') === 'slaughtered-inspection'])><img src="{{ asset('assets/einspect/HOMEPAGE/ICONS/Slaughtered Inspect.png') }}" alt=""> Slaughtered Inspect</a>
        <a href="{{ route('public.service', 'announcements') }}" @class(['active' => ($active ?? '') === 'announcements'])>
            <img src="{{ asset('assets/einspect/HOMEPAGE/ICONS/Announcement.png') }}" alt=""> Announcements
            @if (($announcementCount ?? 0) > 0)<b>{{ $announcementCount }}</b>@endif
        </a>
        @auth
            @if (auth()->user()->isRole('TENANT'))
                <a href="{{ route('tenant.dashboard') }}" class="dashboard-link"><i class="bi bi-speedometer2"></i> Go to Dashboard</a>
            @endif
        @endauth
    </nav>
</header>
<script>
    document.addEventListener('click', (event) => {
        const toggle = event.target.closest('.mobile-nav-toggle');
        if (toggle) {
            document.querySelector('.public-header nav')?.classList.toggle('open');
            return;
        }

        const navLink = event.target.closest('.public-header nav a');
        if (navLink) {
            document.querySelector('.public-header nav')?.classList.remove('open');
            document.querySelectorAll('.role-login-modal:target, .inspection-request-modal:target, .announcement-detail-modal:target')
                .forEach(() => history.replaceState(null, '', location.pathname + location.search));
        }
    });
</script>
