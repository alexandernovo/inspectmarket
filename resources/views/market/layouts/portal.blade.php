@php
    $user = auth()->user();
    $role = $user->role_slug;
    $unreadNotifications = $user->marketNotifications()->whereNull('read_at')->count();
    $pendingInspectionCount = $role === 'inspector'
        ? \App\Models\LivestockInspection::where('status', 'PENDING')->where('request_source', '!=', 'INSPECTOR')->count()
        : 0;
    $inspectorChatContact = null;
    $inspectorChatMessages = collect();
    if ($role === 'inspector') {
        $inspectorChatContact = \App\Models\User::where('usertype', \App\Models\User::ROLE_TENANT)
            ->where('status', 'ACTIVE')
            ->orderBy('firstname')
            ->first();
        if ($inspectorChatContact) {
            $inspectorChatMessages = \App\Models\MarketMessage::query()
                ->where(fn ($query) => $query->where('sender_id', $user->id)->where('recipient_id', $inspectorChatContact->id))
                ->orWhere(fn ($query) => $query->where('sender_id', $inspectorChatContact->id)->where('recipient_id', $user->id))
                ->latest()
                ->limit(8)
                ->get()
                ->reverse()
                ->values();
        }
    }
    $avatar = match ($role) {
        'administrator' => 'A-Administrator.png',
        'treasurer' => 'B-Treasurer.png',
        'clerk' => 'C-Clerk.png',
        'inspector' => 'D-Inspector.png',
        default => '5-Tenants.png',
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
            ['label' => 'Dashboard', 'route' => 'inspector.dashboard', 'icon' => 'bi-grid-fill', 'group' => null],
            ['label' => 'Poultry', 'route' => 'inspector.inspections', 'params' => ['type' => 'poultry'], 'icon' => 'bi-egg-fried', 'group' => 'SLAUGHTERED INSPECT'],
            ['label' => 'Meat', 'route' => 'inspector.inspections', 'params' => ['type' => 'pork'], 'icon' => 'bi-piggy-bank-fill', 'group' => 'SLAUGHTERED INSPECT'],
            ['label' => 'Beef', 'route' => 'inspector.inspections', 'params' => ['type' => 'beef'], 'icon' => 'bi-heart-pulse-fill', 'group' => 'SLAUGHTERED INSPECT'],
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
    <link rel="stylesheet" href="{{ asset('assets/einspect/css/market.css') }}">
</head>
<body class="portal-page portal-role-{{ $role }}">
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
                @php $currentGroup = null; @endphp
                @foreach ($menus[$role] as $menu)
                    @if (($menu['group'] ?? null) && $currentGroup !== $menu['group'])
                        @php $currentGroup = $menu['group']; @endphp
                        <strong class="portal-menu-group">{{ $currentGroup }}</strong>
                    @endif
                    @php
                        $isActive = request()->routeIs($menu['route']);
                        if ($role === 'inspector' && $menu['route'] === 'inspector.inspections') {
                            $menuType = strtoupper($menu['params']['type'] ?? '');
                            $isActive = request()->routeIs('inspector.inspections')
                                && $menuType === strtoupper(request('type', ''));
                        }
                    @endphp
                    <a href="{{ route($menu['route'], $menu['params'] ?? []) }}" class="{{ $isActive ? 'active' : '' }}">
                        <i class="bi {{ $menu['icon'] }}"></i>
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
                    <a href="{{ route('home') }}" class="icon-button" title="Homepage"><i class="bi bi-house"></i></a>
                    <button type="button" class="icon-button message-button" title="Messages" data-toggle-drawer="messageDrawer">
                        <i class="bi bi-chat-dots"></i>
                        @if ($role === 'inspector' && $unreadNotifications)<b>{{ $unreadNotifications }}</b>@endif
                    </button>
                    <button type="button" class="icon-button notification-button" title="Notifications" data-toggle-drawer="notificationDrawer">
                        <i class="bi bi-bell"></i>
                        @if ($unreadNotifications)<b>{{ $unreadNotifications }}</b>@endif
                    </button>
                    <a href="{{ route('profile') }}" class="role-chip">{{ ucfirst($role) }}</a>
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
        @if ($role === 'inspector' && $inspectorChatContact)
            <div class="inspector-drawer-heading">
                <button type="button" data-toggle-drawer="messageDrawer" aria-label="Close messages"><i class="bi bi-arrow-left-circle-fill"></i></button>
                <img src="{{ $inspectorChatContact->profile ? asset('storage/'.$inspectorChatContact->profile) : asset('assets/einspect/USERS/E-Male Tenant.png') }}" alt="">
                <div><strong>{{ $inspectorChatContact->full_name }}</strong><span>Tenant</span></div>
                <a href="tel:{{ $inspectorChatContact->phone_num }}" title="Call tenant"><i class="bi bi-telephone-fill"></i></a>
            </div>
            <div class="inspector-drawer-messages">
                @forelse ($inspectorChatMessages as $message)
                    <article class="{{ $message->sender_id === $user->id ? 'mine' : 'theirs' }}">
                        <p>{{ $message->body }}</p>
                        @if ($message->attachment_path)<a href="{{ asset('storage/'.$message->attachment_path) }}" target="_blank"><i class="bi bi-paperclip"></i> {{ $message->attachment_name }}</a>@endif
                        <small>{{ $message->created_at->format('M d, g:i A') }}</small>
                    </article>
                @empty
                    <p class="empty-state">Start a conversation with {{ $inspectorChatContact->firstname }}.</p>
                @endforelse
            </div>
            <form action="{{ route('chat.store') }}" method="POST" class="inspector-drawer-compose">
                @csrf
                <input type="hidden" name="recipient_id" value="{{ $inspectorChatContact->id }}">
                <input name="body" aria-label="Message" required>
                <button type="submit" title="Send message"><i class="bi bi-send-fill"></i></button>
            </form>
        @else
            <div class="drawer-heading"><div><span>E-Inspect</span><h2>Messages</h2></div><button type="button" data-toggle-drawer="messageDrawer">&times;</button></div>
            <p>Open the secure conversation screen to message another market user and attach documents.</p>
            <a class="button button-primary" href="{{ route('chat.index') }}">Open messages</a>
        @endif
    </aside>
    <form id="logoutForm" action="{{ route('auth.logout') }}" method="POST" hidden>@csrf</form>
    <script src="{{ asset('assets/js/jquery.js') }}"></script>
    <script src="{{ asset('assets/js/datatables.js') }}"></script>
    <script src="{{ asset('assets/js/datatablesbootstrap.js') }}"></script>
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
