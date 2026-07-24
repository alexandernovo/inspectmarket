@extends('market.layouts.portal')

@section('content')
    <section class="panel">
        <div class="panel-heading">
            <div><span class="eyebrow">Account Activity</span><h2>Notifications</h2></div>
            <form action="{{ route('notifications.read-all') }}" method="POST">@csrf @method('PUT')<button class="button button-outline">Mark all read</button></form>
        </div>
        <div class="notification-list">
            @forelse ($notifications as $notification)
                <a href="{{ route('notifications.read', $notification) }}" class="{{ $notification->read_at ? '' : 'unread' }}">
                    <i class="bi bi-bell-fill"></i>
                    <div><strong>{{ $notification->title }}</strong><p>{{ $notification->message }}</p><small>{{ $notification->created_at->diffForHumans() }}</small></div>
                </a>
            @empty
                <p class="empty-state">You have no notifications.</p>
            @endforelse
        </div>
        <div class="pagination-wrap">{{ $notifications->links() }}</div>
    </section>
@endsection
