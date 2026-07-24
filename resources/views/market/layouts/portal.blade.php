@php
    $user = auth()->user();
    $role = $user->role_slug;
    $unreadNotifications = $user->marketNotifications()->whereNull('read_at')->count();
    $avatar = match ($role) {
        'administrator' => 'A-Administrator.png',
        'treasurer' => 'B-Treasurer.png',
        'clerk' => 'C-Clerk.png',
        'inspector' => 'D-Inspector.png',
        default => 'E-Male Tenant.png',
    };
    $menus = [
        'administrator' => [
            ['label' => 'Dashboard', 'route' => 'administrator.dashboard', 'icon' => 'bi-grid'],
            ['label' => 'Treasurer', 'route' => 'administrator.records', 'params' => ['role' => 'treasurer'], 'icon' => 'bi-person-badge'],
            ['label' => 'Clerk', 'route' => 'administrator.records', 'params' => ['role' => 'clerk'], 'icon' => 'bi-person-vcard'],
            ['label' => 'Inspector', 'route' => 'administrator.records', 'params' => ['role' => 'inspector'], 'icon' => 'bi-clipboard2-pulse'],
            ['label' => 'Tenant', 'route' => 'administrator.records', 'params' => ['role' => 'tenant'], 'icon' => 'bi-people'],
            ['label' => 'Stall Map', 'route' => 'administrator.stall-map', 'icon' => 'bi-pin-map'],
            ['label' => 'Report', 'route' => 'administrator.reports', 'icon' => 'bi-file-earmark-bar-graph'],
            ['label' => 'Contact Messages', 'route' => 'administrator.contacts', 'icon' => 'bi-envelope'],
            ['label' => 'Settings', 'route' => 'administrator.settings', 'icon' => 'bi-gear'],
        ],
        'treasurer' => [
            ['label' => 'Dashboard', 'route' => 'treasurer.dashboard', 'icon' => 'bi-grid'],
            ['label' => 'Announcement', 'route' => 'treasurer.announcements', 'icon' => 'bi-megaphone'],
            ['label' => 'Cash Ticket', 'route' => 'treasurer.assignments', 'icon' => 'bi-ticket-perforated'],
            ['label' => 'Collectors', 'route' => 'treasurer.collectors', 'icon' => 'bi-people'],
            ['label' => 'Assign Collectors', 'route' => 'treasurer.assignments', 'icon' => 'bi-person-check'],
            ['label' => 'Stall Rental', 'route' => 'treasurer.rentals', 'icon' => 'bi-shop'],
            ['label' => 'Stall Map', 'route' => 'treasurer.stall-map', 'icon' => 'bi-pin-map'],
            ['label' => 'Report', 'route' => 'treasurer.reports', 'icon' => 'bi-file-earmark-bar-graph'],
            ['label' => 'Settings', 'route' => 'profile', 'icon' => 'bi-gear'],
        ],
        'clerk' => [
            ['label' => 'Dashboard', 'route' => 'clerk.dashboard', 'icon' => 'bi-grid'],
            ['label' => 'Cash Ticket', 'route' => 'clerk.collections', 'icon' => 'bi-ticket-perforated'],
            ['label' => 'Stall Rental', 'route' => 'clerk.rentals', 'icon' => 'bi-shop'],
            ['label' => 'Report', 'route' => 'clerk.reports', 'icon' => 'bi-file-earmark-bar-graph'],
        ],
        'inspector' => [
            ['label' => 'Dashboard', 'route' => 'inspector.dashboard', 'icon' => 'bi-grid'],
            ['label' => 'Poultry', 'route' => 'inspector.inspections', 'params' => ['type' => 'poultry'], 'icon' => 'bi-clipboard2-pulse'],
            ['label' => 'Pork', 'route' => 'inspector.inspections', 'params' => ['type' => 'pork'], 'icon' => 'bi-clipboard2-pulse'],
            ['label' => 'Beef', 'route' => 'inspector.inspections', 'params' => ['type' => 'beef'], 'icon' => 'bi-clipboard2-pulse'],
            ['label' => 'All Requests', 'route' => 'inspector.inspections', 'icon' => 'bi-calendar2-check'],
            ['label' => 'Report', 'route' => 'inspector.reports', 'icon' => 'bi-file-earmark-bar-graph'],
        ],
        'tenant' => [
            ['label' => 'Dashboard', 'route' => 'tenant.dashboard', 'icon' => 'bi-grid'],
            ['label' => 'Location', 'route' => 'tenant.stall-map', 'icon' => 'bi-geo-alt'],
            ['label' => 'Application', 'route' => 'tenant.applications', 'icon' => 'bi-file-earmark-text'],
            ['label' => 'Payment', 'route' => 'tenant.payments', 'icon' => 'bi-wallet2'],
            ['label' => 'Inspection Request', 'route' => 'tenant.inspections', 'icon' => 'bi-clipboard2-pulse'],
        ],
    ];
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $pageTitle ?? 'E-Inspect')</title>
    <link rel="icon" href="{{ asset('assets/einspect/HOMEPAGE/Logo.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/bootstrap-icons/font/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/twitterbootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/datatablesbootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/einspect/css/market.css') }}">
</head>
<body class="portal-page">
    <div class="portal-shell">
        <aside class="portal-sidebar" id="portalSidebar">
            <a class="portal-brand" href="{{ route($role.'.dashboard') }}">
                <img src="{{ asset('assets/einspect/HOMEPAGE/Logo.png') }}" alt="Pandan seal">
                <span>E-INSPECT</span>
            </a>
            <div class="portal-user">
                <img src="{{ $user->profile ? asset('storage/'.$user->profile) : asset('assets/einspect/USERS/'.$avatar) }}" alt="{{ $user->full_name }}">
                <span>Welcome {{ ucfirst($role) }}!</span>
            </div>
            <nav class="portal-menu" aria-label="Portal navigation">
                @foreach ($menus[$role] as $menu)
                    @php $isActive = request()->routeIs($menu['route']); @endphp
                    <a href="{{ route($menu['route'], $menu['params'] ?? []) }}" class="{{ $isActive ? 'active' : '' }}">
                        <i class="bi {{ $menu['icon'] }}"></i>
                        <span>{{ $menu['label'] }}</span>
                    </a>
                @endforeach
            </nav>
            <nav class="portal-menu portal-menu-secondary" aria-label="Account navigation">
                <a href="{{ route('chat.index') }}" class="{{ request()->routeIs('chat.*') ? 'active' : '' }}"><i class="bi bi-chat-dots"></i><span>Messages</span></a>
                <a href="{{ route('notifications.index') }}" class="{{ request()->routeIs('notifications.*') ? 'active' : '' }}"><i class="bi bi-bell"></i><span>Notifications</span></a>
                <a href="{{ route('profile') }}" class="{{ request()->routeIs('profile*') ? 'active' : '' }}"><i class="bi bi-person-circle"></i><span>Profile</span></a>
            </nav>
            <div class="portal-logout">
                <button type="button" data-open-dialog="logoutDialog"><i class="bi bi-box-arrow-left"></i> Log out</button>
            </div>
        </aside>

        <main class="portal-main">
            <header class="portal-topbar">
                <button type="button" class="icon-button sidebar-toggle" aria-label="Toggle menu">
                    <i class="bi bi-list"></i>
                </button>
                <div>
                    <strong>{{ $pageTitle ?? 'Dashboard' }}</strong>
                    <span class="breadcrumb">Dashboard / {{ $pageTitle ?? 'Overview' }}</span>
                </div>
                <div class="topbar-actions">
                    <a href="{{ route('home') }}" class="icon-button" title="Homepage"><i class="bi bi-house"></i></a>
                    <button type="button" class="icon-button" title="Messages" data-toggle-drawer="messageDrawer"><i class="bi bi-chat-dots"></i></button>
                    <button type="button" class="icon-button notification-button" title="Notifications" data-toggle-drawer="notificationDrawer">
                        <i class="bi bi-bell"></i>
                        @if ($unreadNotifications)<b>{{ $unreadNotifications }}</b>@endif
                    </button>
                    <span class="role-chip">{{ ucfirst($role) }}</span>
                </div>
            </header>

            <section class="portal-content">
                @if (session('success'))
                    <div class="alert-success"><i class="bi bi-check-circle"></i> {{ session('success') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert-error">
                        <strong>Please correct the following:</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @yield('content')
            </section>

            <footer class="portal-footer">
                © {{ date('Y') }} Pandan Public Market. All rights reserved.
            </footer>
        </main>
    </div>
    <aside class="portal-drawer" id="notificationDrawer">
        <div class="drawer-heading"><div><span>Market Updates</span><h2>Notifications</h2></div><button type="button" data-toggle-drawer="notificationDrawer">×</button></div>
        @forelse ($user->marketNotifications()->latest()->limit(8)->get() as $notification)
            <a href="{{ route('notifications.read', $notification) }}"><strong>{{ $notification->title }}</strong><span>{{ $notification->message }}</span><small>{{ $notification->created_at->diffForHumans() }}</small></a>
        @empty
            <p class="empty-state">No notifications.</p>
        @endforelse
        <a class="button button-primary" href="{{ route('notifications.index') }}">View all notifications</a>
    </aside>
    <aside class="portal-drawer message-drawer" id="messageDrawer">
        <div class="drawer-heading"><div><span>E-Inspect</span><h2>Messages</h2></div><button type="button" data-toggle-drawer="messageDrawer">×</button></div>
        <p>Open the secure conversation screen to message another market user and attach documents.</p>
        <a class="button button-primary" href="{{ route('chat.index') }}">Open messages</a>
    </aside>
    <dialog id="logoutDialog" class="market-dialog compact-dialog logout-dialog">
        <form action="{{ route('auth.logout') }}" method="POST">
            @csrf
            <div class="logout-dialog-icon"><i class="bi bi-question-circle"></i><strong>Logout?</strong></div>
            <p>Are you sure you want to log out?</p>
            <div class="dialog-actions"><button type="button" class="button button-muted" data-close-dialog>No, stay logged in</button><button class="button button-primary">Yes, log out</button></div>
        </form>
    </dialog>
    <script src="{{ asset('assets/js/jquery.js') }}"></script>
    <script src="{{ asset('assets/js/datatables.js') }}"></script>
    <script src="{{ asset('assets/js/datatablesbootstrap.js') }}"></script>
    <script>
        document.querySelector('.sidebar-toggle')?.addEventListener('click', () => {
            document.getElementById('portalSidebar').classList.toggle('open');
        });
        document.querySelectorAll('[data-open-dialog]').forEach((button) => {
            button.addEventListener('click', () => {
                document.getElementById(button.dataset.openDialog)?.showModal();
            });
        });
        document.querySelectorAll('dialog [data-close-dialog]').forEach((button) => {
            button.addEventListener('click', () => button.closest('dialog').close());
        });
        document.querySelectorAll('[data-toggle-drawer]').forEach((button) => {
            button.addEventListener('click', () => document.getElementById(button.dataset.toggleDrawer)?.classList.toggle('open'));
        });
        if (window.jQuery && $.fn.DataTable) {
            $('.market-data-table').DataTable({
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50],
                order: [],
                language: { search: 'Search records:' }
            });
        }
    </script>
    @stack('scripts')
</body>
</html>
