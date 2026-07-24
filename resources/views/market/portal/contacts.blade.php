@extends('market.layouts.portal')

@section('content')
    <section class="panel">
        <div class="panel-heading"><div><span class="eyebrow">Public Homepage</span><h2>Contact Messages</h2></div></div>
        <div class="portal-announcement-list contact-message-list">
            @forelse ($messages as $message)
                <article class="{{ $message->read_at ? '' : 'unread' }}">
                    <span>{{ $message->created_at->format('M d, Y g:i A') }}</span>
                    <h3>{{ $message->name }} · {{ $message->contact_number }}</h3>
                    <p>{{ $message->message }}</p>
                    <small>{{ $message->email }} {{ $message->address ? ' · '.$message->address : '' }}</small>
                    @unless ($message->read_at)
                        <form action="{{ route('administrator.contacts.read', $message) }}" method="POST">@csrf @method('PUT')<button class="button button-outline">Mark read</button></form>
                    @endunless
                </article>
            @empty
                <p class="empty-state">No public contact messages.</p>
            @endforelse
        </div>
        <div class="pagination-wrap">{{ $messages->links() }}</div>
    </section>
@endsection
