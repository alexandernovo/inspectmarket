@extends('market.layouts.portal')

@section('content')
    @include('market.tenant.applications.css.header')
    <header class="tenant-page-title px-3 pt-3 pb-0">
        <div>
            <i class="bi bi-chat-fill"></i>
            <div>
                <h1>MESSAGES</h1>
                <p class="mb-0">Dashboard | Messages</p>
            </div>
        </div>
    </header>
    <section class="panel chat-shell mx-3 mb-3 mt-2  p-3">
        <aside class="chat-contacts">
            <h2>Messages</h2>
            @foreach ($contacts as $contact)
                <a href="{{ route('chat.index', ['user' => $contact->id]) }}"
                    class="{{ $selected?->id === $contact->id ? 'active' : '' }}">
                    <img class="contact-avatar" src="{{ market_role_avatar($contact->usertype, $contact->profile) }}" alt="{{ $contact->full_name }}">
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
                            @if ($message->attachment_path)
                                <a href="{{ route('chat.attachment', $message) }}" target="_blank"><i
                                        class="bi bi-paperclip"></i> {{ $message->attachment_name }}</a>
                            @endif
                            <small>{{ $message->created_at->format('M d, g:i A') }}</small>
                        </article>
                    @empty
                        <p class="empty-state">Start your conversation with {{ $selected->firstname }}.</p>
                    @endforelse
                </div>
                <form action="{{ route('chat.store') }}" method="POST" enctype="multipart/form-data" class="chat-compose" data-chat-compose>
                    @csrf
                    <input type="hidden" name="recipient_id" value="{{ $selected->id }}">
                    <input name="body" placeholder="Write a message…">
                    <label title="Attach file"><i class="bi bi-paperclip"></i><input type="file" name="attachment"
                            hidden data-chat-attachment></label>
                    <button data-chat-send><i class="bi bi-send-fill"></i></button>
                    <div class="chat-compose-files" data-chat-file-list></div>
                </form>
            @else
                <p class="empty-state">No contacts are available.</p>
            @endif
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-chat-attachment]').forEach((input) => {
            input.addEventListener('change', () => {
                const form = input.closest('[data-chat-compose]');
                const list = form?.querySelector('[data-chat-file-list]');
                if (!list || !form) return;

                list.innerHTML = '';
                Array.from(input.files || []).forEach((file) => {
                    const item = document.createElement('span');
                    const typeIcon = file.type.startsWith('image/') ? 'bi-image-fill' : 'bi-paperclip';
                    item.innerHTML = `<i class="bi ${typeIcon}"></i><b>${file.name}</b><small>${Math.ceil(file.size / 1024)} KB - click Send to upload</small>`;
                    list.appendChild(item);
                });

                form.classList.toggle('has-file', (input.files || []).length > 0);
            });
        });
    </script>
@endpush
