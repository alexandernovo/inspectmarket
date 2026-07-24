@extends('market.layouts.portal')

@section('content')
    <section class="panel">
            <div class="panel-heading"><div><span class="eyebrow">Public Market</span><h2>Announcements</h2></div><button type="button" class="button button-primary" data-open-dialog="announcementDialog"><i class="bi bi-plus-lg"></i> Add Announcement</button></div>
            <div class="announcement-tabs">
                @foreach (['ALL','OTHERS','MARKET ADVISORY','BIDDING','STALL RENTAL','SLAUGHTERED INSPECT'] as $category)
                    <button type="button" data-announcement-filter="{{ $category }}">{{ str($category)->title() }}</button>
                @endforeach
            </div>
            <div class="portal-announcement-list">
                @forelse ($announcements as $announcement)
                    <article data-announcement-category="{{ $announcement->category }}">
                        <span>{{ $announcement->category }}</span>
                        <h3>{{ $announcement->title }}</h3>
                        <p>{{ $announcement->content }}</p>
                        @if ($announcement->attachment_path)
                            <a href="{{ asset('storage/'.$announcement->attachment_path) }}" target="_blank"><i class="bi bi-paperclip"></i> {{ $announcement->attachment_name }}</a>
                        @endif
                        <small>{{ $announcement->author?->full_name }} · {{ optional($announcement->published_at)->format('M d, Y g:i A') }}</small>
                    </article>
                @empty
                    <p class="empty-state">No announcements published yet.</p>
                @endforelse
            </div>
            </div>
    </section>
    <dialog id="announcementDialog" class="market-dialog">
            <form action="{{ route('treasurer.announcements.store') }}" method="POST" enctype="multipart/form-data" class="stacked-form">
                @csrf
                <div class="dialog-heading"><div><span>Public Market</span><h2>Post Announcement</h2></div><button type="button" data-close-dialog>×</button></div>
                <label>Category<select name="category">@foreach (['SLAUGHTERED INSPECT','STALL RENTAL','BIDDING','MARKET ADVISORY','OTHERS'] as $category)<option>{{ $category }}</option>@endforeach</select></label>
                <label>Title<input name="title" required></label>
                <label>Publish date<input type="datetime-local" name="published_at"></label>
                <label>Contents<textarea name="content" rows="8" required></textarea></label>
                <label>Attachment<input type="file" name="attachment"></label>
                <div class="dialog-actions"><button type="button" class="button button-muted" data-close-dialog>Cancel</button><button class="button button-primary">Post</button></div>
            </form>
    </dialog>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('[data-announcement-filter]').forEach((button) => button.addEventListener('click', () => {
        document.querySelectorAll('[data-announcement-category]').forEach((article) => {
            article.hidden = button.dataset.announcementFilter !== 'ALL' && article.dataset.announcementCategory !== button.dataset.announcementFilter;
        });
    }));
</script>
@endpush
