<header class="public-header {{ $headerClass ?? '' }}">
    <a href="{{ route('home') }}" class="public-brand">
        <img src="{{ asset('assets/einspect/HOMEPAGE/Logo.png') }}" alt="Municipality of Pandan seal">
        <span>PANDAN MARKET</span>
    </a>
    <button type="button" class="mobile-nav-toggle" aria-label="Toggle navigation"><i class="bi bi-list"></i></button>
    <nav>
        <a href="{{ route('home') }}" @class(['active' => ($active ?? '') === 'home'])><i class="bi bi-house-fill"></i> Home</a>
        <a href="{{ route('public.service', 'contact') }}" @class(['active' => ($active ?? '') === 'contact'])><i class="bi bi-envelope-paper-fill"></i> Contact</a>
        <a href="{{ route('public.service', 'stall-rental') }}" @class(['active' => ($active ?? '') === 'stall-rental'])><i class="bi bi-shop-window"></i> Stall Rental</a>
        <a href="{{ route('public.service', 'slaughtered-inspection') }}" @class(['active' => ($active ?? '') === 'slaughtered-inspection'])>Slaughtered Inspect</a>
        <a href="{{ route('public.service', 'announcements') }}" @class(['active' => ($active ?? '') === 'announcements'])>
            <i class="bi bi-megaphone-fill"></i> Announcements
            @if (($announcementCount ?? 0) > 0)<b>{{ $announcementCount }}</b>@endif
        </a>
    </nav>
</header>
<script>
    document.querySelector('.mobile-nav-toggle')?.addEventListener('click', () => {
        document.querySelector('.public-header nav')?.classList.toggle('open');
    });
</script>
