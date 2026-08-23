@php
    $user = auth()->user();
    $role = $user->role_slug;
    $unreadNotifications = $user->marketNotifications()->whereNull('read_at')->count();
    $unreadMessages = \App\Models\MarketMessage::where('recipient_id', $user->id)->whereNull('read_at')->count();
    $messagePreviews = \App\Models\User::whereKeyNot($user->id)
        ->where('status', 'ACTIVE')
        ->orderBy('usertype')
        ->orderBy('firstname')
        ->get()
        ->map(function ($contact) use ($user) {
            $latestMessage = \App\Models\MarketMessage::query()
                ->where(fn ($query) => $query->where('sender_id', $user->id)->where('recipient_id', $contact->id))
                ->orWhere(fn ($query) => $query->where('sender_id', $contact->id)->where('recipient_id', $user->id))
                ->latest()
                ->first();
            $contact->latest_market_message = $latestMessage;
            $contact->unread_market_messages = \App\Models\MarketMessage::where('sender_id', $contact->id)
                ->where('recipient_id', $user->id)
                ->whereNull('read_at')
                ->count();

            return $contact;
        })
        ->sortByDesc(fn ($contact) => optional($contact->latest_market_message)->created_at?->timestamp ?? 0)
        ->values();
    $pendingInspectionCount = $role === 'inspector'
        ? \App\Models\LivestockInspection::where('status', 'PENDING')->where('request_source', '!=', 'INSPECTOR')->count()
        : 0;
    $tenantStallApplicationCount = $role === 'treasurer'
        ? \App\Models\StallApplication::count()
        : 0;
    $portalAvatar = $role === 'clerk'
        ? asset('assets/einspect/USERS/3-Collector Clerk.png')
        : market_role_avatar($user->usertype, $user->profile);
    $portalRoleLabel = $role === 'clerk' ? 'RC Clerk' : ucfirst($role);
    $portalWelcomeLabel = $role === 'administrator' ? 'Admin' : ucfirst($role);
    $menus = [
        'administrator' => [
            ['label' => 'Dashboard', 'route' => 'administrator.dashboard', 'icon' => 'bi-grid'],
            ['label' => 'Treasurer', 'route' => 'administrator.records', 'params' => ['role' => 'treasurer'], 'icon' => 'bi-person-badge', 'image' => 'assets/einspect/USERS/2-Treasurer.png', 'group' => 'MEMBERS'],
            ['label' => 'Clerk', 'route' => 'administrator.records', 'params' => ['role' => 'clerk'], 'icon' => 'bi-person-vcard', 'image' => 'assets/einspect/USERS/3-Collector Clerk.png', 'group' => 'MEMBERS'],
            ['label' => 'Inspector', 'route' => 'administrator.records', 'params' => ['role' => 'inspector'], 'icon' => 'bi-clipboard2-pulse', 'image' => 'assets/einspect/USERS/4-Sanitary Inspector.png', 'group' => 'MEMBERS'],
            ['label' => 'Tenant', 'route' => 'administrator.records', 'params' => ['role' => 'tenant'], 'icon' => 'bi-people', 'image' => 'assets/einspect/USERS/5-Tenants.png', 'group' => 'MEMBERS'],
            ['label' => 'Report', 'route' => 'administrator.reports', 'icon' => 'bi-file-earmark-bar-graph'],
            ['label' => 'Settings', 'route' => 'administrator.settings', 'icon' => 'bi-gear'],
        ],
        'treasurer' => [
            ['label' => 'Dashboard', 'route' => 'treasurer.dashboard', 'icon' => 'bi-grid'],
            ['label' => 'Announcement', 'route' => 'treasurer.announcements', 'icon' => 'bi-megaphone'],
            ['label' => 'Cash Ticket', 'route' => 'treasurer.assignments', 'icon' => 'bi-ticket-perforated-fill', 'group' => 'CLERK'],
            ['label' => 'Stall Rental', 'route' => 'treasurer.rentals', 'icon' => 'bi-shop-window', 'group' => 'CLERK'],
            ['label' => 'Stall Rental', 'route' => 'treasurer.payments', 'icon' => 'bi-shop-window', 'group' => 'TENANT', 'badge' => $tenantStallApplicationCount],
            ['label' => 'Report', 'route' => 'treasurer.reports', 'icon' => 'bi-file-earmark-text'],
            ['label' => 'Settings', 'route' => 'profile', 'icon' => 'bi-gear-fill'],
        ],
        'clerk' => [
            ['label' => 'Dashboard', 'route' => 'clerk.dashboard', 'icon' => 'bi-grid-fill', 'group' => null],
            ['label' => 'Cash Ticket', 'route' => 'clerk.collections', 'icon' => 'bi-ticket-perforated-fill', 'group' => 'COLLECTOR'],
            ['label' => 'Stall Rental', 'route' => 'clerk.rentals', 'icon' => 'bi-shop-window', 'group' => 'TENANT'],
            ['label' => 'Report', 'route' => 'clerk.reports', 'icon' => 'bi-file-earmark-text', 'group' => 'TENANT'],
        ],
        'inspector' => [
            ['label' => 'Dashboard', 'route' => 'inspector.dashboard', 'icon' => 'bi-grid-fill', 'group' => null],
            ['label' => 'Poultry', 'route' => 'inspector.inspections', 'params' => ['type' => 'poultry'], 'icon' => 'bi-egg-fried', 'image' => 'assets/einspect/INSPECTOR/ICONS/Poultry.png', 'group' => 'SLAUGHTERED INSPECT'],
            ['label' => 'Meat', 'route' => 'inspector.inspections', 'params' => ['type' => 'pork'], 'icon' => 'bi-piggy-bank-fill', 'image' => 'assets/einspect/INSPECTOR/ICONS/Pork.png', 'group' => 'SLAUGHTERED INSPECT'],
            ['label' => 'Beef', 'route' => 'inspector.inspections', 'params' => ['type' => 'beef'], 'icon' => 'bi-heart-pulse-fill', 'image' => 'assets/einspect/INSPECTOR/ICONS/Beef.png', 'group' => 'SLAUGHTERED INSPECT'],
            ['label' => 'Request Inspection', 'route' => 'inspector.inspections', 'icon' => 'bi-people-fill', 'badge' => $pendingInspectionCount],
            ['label' => 'Report', 'route' => 'inspector.reports', 'icon' => 'bi-file-earmark-bar-graph'],
        ],
        'tenant' => [
            ['label' => 'Dashboard', 'route' => 'tenant.dashboard', 'icon' => 'bi-grid', 'group' => null],
            ['label' => 'Location', 'route' => 'tenant.stall-map', 'icon' => 'bi-geo-alt', 'group' => 'STALL RENTAL'],
            ['label' => 'Application', 'route' => 'tenant.applications', 'icon' => 'bi-shop-window', 'group' => 'STALL RENTAL'],
            ['label' => 'Payment', 'route' => 'tenant.payments', 'icon' => 'bi-currency-exchange', 'group' => 'STALL RENTAL'],
            ['label' => 'Request', 'route' => 'tenant.inspections', 'icon' => 'bi-person-arms-up', 'group' => 'SLAUGHTERED INSPECT'],
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
    <link rel="stylesheet" href="{{ asset('assets/css/select2.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/einspect/css/market.css') }}?v={{ filemtime(public_path('assets/einspect/css/market.css')) }}">
</head>
<body class="portal-page portal-role-{{ $role }}">
    <div class="portal-shell">
        <aside class="portal-sidebar" id="portalSidebar">
            <a class="portal-brand" href="{{ route($role.'.dashboard') }}">
                <img src="{{ asset('assets/einspect/HOMEPAGE/Logo.png') }}" alt="Pandan seal">
                <span>E-INSPECT</span>
            </a>
            <div class="portal-user">
                <img src="{{ $portalAvatar }}" alt="{{ $user->full_name }}">
                <span>Welcome {{ $portalWelcomeLabel }}!</span>
            </div>
            <nav class="portal-menu" aria-label="Portal navigation">
                @php $currentGroup = null; @endphp
                @foreach ($menus[$role] as $menu)
                    @if (($menu['group'] ?? null) && $currentGroup !== $menu['group'])
                        @php $currentGroup = $menu['group']; @endphp
                        <strong class="portal-menu-group">{{ $currentGroup }}</strong>
                    @endif
                    @php
                        $menuGroup = $menu['group'] ?? null;
                        $nextGroup = $menus[$role][$loop->index + 1]['group'] ?? null;
                        $isActive = request()->routeIs($menu['route']);
                        if ($menu['route'] === 'profile') {
                            $isActive = request()->routeIs('profile*');
                        }
                        if ($role === 'administrator' && $menu['route'] === 'administrator.records') {
                            $isActive = request()->routeIs('administrator.records*')
                                && strtolower((string) request()->route('role')) === strtolower((string) ($menu['params']['role'] ?? ''));
                        }
                        if ($role === 'inspector' && $menu['route'] === 'inspector.inspections') {
                            $menuType = strtoupper($menu['params']['type'] ?? '');
                            $isActive = request()->routeIs('inspector.inspections')
                                && $menuType === strtoupper(request('type', ''));
                        }
                    @endphp
                    <a href="{{ route($menu['route'], $menu['params'] ?? []) }}" @class([
                        'active' => $isActive,
                        'portal-menu-item-grouped' => $menuGroup,
                        'portal-menu-item-group-end' => $menuGroup && $menuGroup !== $nextGroup,
                    ])>
                        @if (!empty($menu['image']))
                            <img class="portal-menu-icon-image" src="{{ asset($menu['image']) }}" alt="">
                        @else
                            <i class="bi {{ $menu['icon'] }}"></i>
                        @endif
                        <span>{{ $menu['label'] }}</span>
                        @if (!empty($menu['badge']))<b class="portal-menu-badge">{{ $menu['badge'] }}</b>@endif
                    </a>
                @endforeach
            </nav>
            <nav class="portal-menu portal-menu-secondary" aria-label="Account navigation">
                <a href="{{ route('chat.index') }}" class="{{ request()->routeIs('chat.*') ? 'active' : '' }}"><i class="bi bi-chat-dots"></i><span>Messages</span></a>
                <a href="{{ route('notifications.index') }}" class="{{ request()->routeIs('notifications.*') ? 'active' : '' }}"><i class="bi bi-bell"></i><span>Notifications</span></a>
                <a href="{{ route('profile') }}" class="{{ request()->routeIs('profile*') ? 'active' : '' }}"><i class="bi bi-person-circle"></i><span>Profile</span></a>
            </nav>
            <div class="portal-logout">
                <button type="button" data-swal-logout style="font-size: 17px"><i class="bi bi-box-arrow-left"></i> Logout</button>
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
                    @if ($role === 'tenant')
                        <a href="{{ route('home') }}" class="icon-button" title="Homepage"><i class="bi bi-house"></i></a>
                    @endif
                    <button type="button" class="icon-button message-button" title="Messages" data-toggle-drawer="messageDrawer">
                        <i class="bi bi-chat-dots"></i>
                        @if ($unreadMessages)<b>{{ $unreadMessages }}</b>@endif
                    </button>
                    <button type="button" class="icon-button notification-button" title="Notifications" data-toggle-drawer="notificationDrawer">
                        <i class="bi bi-bell"></i>
                        @if ($unreadNotifications)<b>{{ $unreadNotifications }}</b>@endif
                    </button>
                    <a href="{{ route('profile') }}" class="role-chip">{{ $portalRoleLabel }}</a>
                </div>
            </header>

            <section class="portal-content">
                @if (session('success'))
                    <div class="alert-success" data-swal-success="{{ session('success') }}"><i class="bi bi-check-circle"></i> {{ session('success') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert-error" data-swal-error="{{ $errors->first() }}">
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
                &copy; Copyright {{ date('Y') }}. Pandan Public Market. All Rights Reserved
            </footer>
        </main>
    </div>
    <aside class="portal-drawer" id="notificationDrawer">
        <div class="drawer-heading"><div><span>Market Updates</span><h2>Notifications</h2></div><button type="button" data-toggle-drawer="notificationDrawer">×</button></div>
        @forelse ($user->marketNotifications()->latest()->limit(8)->get() as $notification)
            <a href="{{ route('notifications.read', $notification) }}" class="drawer-preview">
                <img src="{{ market_notification_avatar($notification->type) }}" alt="">
                <span><strong>{{ $notification->title }}</strong><em>{{ $notification->message }}</em><small>{{ $notification->created_at->diffForHumans() }}</small></span>
            </a>
        @empty
            <p class="empty-state">No notifications.</p>
        @endforelse
        <a class="button button-primary" href="{{ route('notifications.index') }}">View all notifications</a>
    </aside>
    <aside class="portal-drawer message-drawer" id="messageDrawer">
        <div class="drawer-heading"><div><span>E-Inspect</span><h2>Messages</h2></div><button type="button" data-toggle-drawer="messageDrawer">&times;</button></div>
        <div class="drawer-preview-list">
            @forelse ($messagePreviews as $contact)
                @php $latestMessage = $contact->latest_market_message; @endphp
                <a href="{{ route('chat.index', ['user' => $contact->id]) }}" class="drawer-preview {{ $contact->unread_market_messages ? 'unread' : '' }}">
                    <img src="{{ market_role_avatar($contact->usertype, $contact->profile) }}" alt="">
                    <span>
                        <strong>{{ $contact->full_name }}</strong>
                        <em>
                            @if ($latestMessage)
                                {{ $latestMessage->sender_id === $user->id ? 'You: ' : '' }}{{ \Illuminate\Support\Str::limit($latestMessage->body ?: 'Sent an attachment.', 54) }}
                            @else
                                No messages yet.
                            @endif
                        </em>
                        <small>{{ $latestMessage?->created_at?->diffForHumans() ?? ucfirst($contact->role_slug) }}</small>
                    </span>
                    @if ($contact->unread_market_messages)<b>{{ $contact->unread_market_messages }}</b>@endif
                </a>
            @empty
                <p class="empty-state">No contacts are available.</p>
            @endforelse
        </div>
        <a class="button button-primary" href="{{ route('chat.index') }}">Open messages</a>
    </aside>
    <form id="logoutForm" action="{{ route('auth.logout') }}" method="POST" hidden>@csrf</form>
    <script src="{{ asset('assets/js/jquery.js') }}"></script>
    <script src="{{ asset('assets/js/datatables.js') }}"></script>
    <script src="{{ asset('assets/js/datatablesbootstrap.js') }}"></script>
    <script src="{{ asset('assets/js/select2.js') }}"></script>
    <script src="{{ asset('assets/js/sweetalert2.js') }}"></script>
    <script>
        document.querySelector('.sidebar-toggle')?.addEventListener('click', () => {
            if (window.innerWidth <= 780) {
                document.getElementById('portalSidebar').classList.toggle('open');
            } else {
                document.body.classList.toggle('sidebar-collapsed');
            }
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
        window.einspectConfirm = function ({
            title,
            message,
            confirmText = 'Yes, Continue',
            cancelText = 'No, Go Back'
        }) {
            const escapeHtml = (value) => $('<div>').text(value).html();

            return Swal.fire({
                html: `
                    <div class="logout-swal-hero">
                        <span class="logout-question-mark">?</span>
                        <h2>${escapeHtml(title)}</h2>
                    </div>
                    <p class="logout-swal-question">${escapeHtml(message)}</p>
                `,
                showCancelButton: true,
                reverseButtons: true,
                confirmButtonText: confirmText,
                cancelButtonText: cancelText,
                confirmButtonColor: '#760008',
                cancelButtonColor: '#a45c00',
                background: '#fff',
                backdrop: 'rgba(25, 0, 0, .78)',
                customClass: { popup: 'einspect-swal einspect-logout-swal einspect-confirm-swal' }
            });
        };
        window.einspectSuccess = function (message) {
            return Swal.fire({
                title: 'Success',
                text: message,
                icon: 'success',
                confirmButtonText: 'OK',
                confirmButtonColor: '#198754',
                backdrop: 'rgba(25, 0, 0, .45)'
            });
        };
        document.querySelector('[data-swal-logout]')?.addEventListener('click', () => {
            einspectConfirm({
                title: 'Logout ?',
                message: 'Are you sure you want to logout?',
                confirmText: 'Yes, Logout',
                cancelText: 'No, Stay Login'
            }).then((result) => {
                if (result.isConfirmed) document.getElementById('logoutForm')?.submit();
            });
        });
        const successAlert = document.querySelector('[data-swal-success]');
        if (successAlert) {
            einspectSuccess(successAlert.dataset.swalSuccess);
        }
        const errorAlert = document.querySelector('[data-swal-error]');
        if (errorAlert) {
            Swal.fire({
                title: 'Please check your entry',
                text: errorAlert.dataset.swalError,
                icon: 'error',
                confirmButtonColor: '#760008',
                backdrop: 'rgba(25, 0, 0, .65)',
                customClass: { popup: 'einspect-swal' }
            });
        }
        document.querySelectorAll('[data-confirm-delete]').forEach((button) => {
            button.addEventListener('click', () => {
                einspectConfirm({
                    title: 'Delete Application?',
                    message: 'This pending application and its uploaded documents will be removed.',
                    confirmText: 'Yes, Delete',
                    cancelText: 'No, Keep It'
                }).then((result) => {
                    if (result.isConfirmed) button.closest('form')?.submit();
                });
            });
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
