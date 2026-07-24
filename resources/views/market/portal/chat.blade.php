@extends('market.layouts.portal')

@section('content')
    <section class="panel chat-shell">
        <aside class="chat-contacts">
            <h2>Messages</h2>
            @foreach ($contacts as $contact)
                <a href="{{ route('chat.index', ['user' => $contact->id]) }}" class="{{ $selected?->id === $contact->id ? 'active' : '' }}">
                    <span class="contact-avatar">{{ strtoupper(substr($contact->firstname, 0, 1).substr($contact->lastname, 0, 1)) }}</span>
                    <span><strong>{{ $contact->full_name }}</strong><small>{{ ucfirst($contact->role_slug) }}</small></span>
                </a>
            @endforeach
        </aside>
        <div class="chat-conversation">
            @if ($selected)
                <header><strong>{{ $selected->full_name }}</strong><span>{{ $selected->designation }}</span></header>
                <div class="chat-messages">
                    @forelse ($messages as $message)
                        <article class="{{ $message->sender_id === auth()->id() ? 'mine' : 'theirs' }}">
                            <p>{{ $message->body }}</p>
                            @if ($message->attachment_path)<a href="{{ asset('storage/'.$message->attachment_path) }}" target="_blank"><i class="bi bi-paperclip"></i> {{ $message->attachment_name }}</a>@endif
                            <small>{{ $message->created_at->format('M d, g:i A') }}</small>
                        </article>
                    @empty
                        <p class="empty-state">Start your conversation with {{ $selected->firstname }}.</p>
                    @endforelse
                </div>
                <form action="{{ route('chat.store') }}" method="POST" enctype="multipart/form-data" class="chat-compose">
                    @csrf
                    <input type="hidden" name="recipient_id" value="{{ $selected->id }}">
                    <input name="body" placeholder="Write a message…">
                    <label title="Attach file"><i class="bi bi-paperclip"></i><input type="file" name="attachment" hidden></label>
                    <button><i class="bi bi-send-fill"></i></button>
                </form>
            @else
                <p class="empty-state">No contacts are available.</p>
            @endif
        </div>
    </section>
@endsection
