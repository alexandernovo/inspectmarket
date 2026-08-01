@extends('market.layouts.portal')

@section('content')
    @include('market.tenant.applications.css.header')
    <header class="tenant-page-title px-3 pt-3 pb-0">
        <div>
            <i class="bi bi-bell-fill"></i>
            <div>
                <h1>NOTIFICATIONS</h1>
                <p class="mb-0">Dashboard | Notifications</p>
            </div>
        </div>
    </header>
    <section class="panel mx-3 mb-3 mt-2 p-3">
        <div class="panel-heading px-0">
            <form action="{{ route('notifications.read-all') }}" method="POST">@csrf @method('PUT')<button
                    class="button button-outline">Mark all read</button></form>
        </div>
        <div class="notification-list">
            @forelse ($notifications as $notification)
                <a href="{{ route('notifications.read', $notification) }}"
                    class="{{ $notification->read_at ? '' : 'unread' }}">
                    <img class="notification-avatar" src="{{ market_notification_avatar($notification->type) }}" alt="">
                    <div><strong>{{ $notification->title }}</strong>
                        <p>{{ $notification->message }}</p><small>{{ $notification->created_at->diffForHumans() }}</small>
                    </div>
                </a>
            @empty
                <p class="empty-state">You have no notifications.</p>
            @endforelse
        </div>
        <div class="pagination-wrap">{{ $notifications->links() }}</div>
    </section>
@endsection
