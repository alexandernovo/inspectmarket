@extends('market.layouts.portal')

@section('content')
    @include('market.tenant.applications.css.header')
    <header class="tenant-page-title px-3 pt-3 pb-0">
        <div>
            <i class="bi bi-megaphone-fill"></i>
            <div>
                <h1>ANNOUNCEMENT</h1>
                <p class="mb-0">Dashboard | Announcement</p>
            </div>
        </div>
        <button type="button" class="treasurer-add-announcement" data-open-dialog="announcementDialog"><i class="bi bi-plus-circle"></i> Add Announcement</button>
    </header>

    <section class="treasurer-announcement-board">
        <div class="treasurer-announcement-top">
            <div class="treasurer-announcement-date-filter">
                <select data-announcement-page-size><option>10</option></select>
                <label>From:<input type="date" data-announcement-from></label>
                <label>To:<input type="date" data-announcement-to></label>
                <button type="button" data-announcement-date-filter>Filter</button>
            </div>
            <nav class="announcement-tabs" aria-label="Announcement categories">
                @foreach (['ALL' => 'All', 'OTHERS' => 'Others', 'MARKET ADVISORY' => 'Market Advisory', 'BIDDING' => 'Bidding', 'STALL RENTAL' => 'Stall Rental', 'SLAUGHTERED INSPECT' => 'Slaughtered Inspect'] as $category => $tab)
                    <button type="button" data-announcement-filter="{{ $category }}" @class(['active' => $category === 'OTHERS', 'd-none' => $category === 'ALL'])>{{ $tab }}</button>
                @endforeach
            </nav>
        </div>

        <div class="treasurer-announcement-heading">
            <div><i class="bi bi-megaphone-fill"></i><h2>ANNOUNCEMENTS</h2></div>
            <label>Search:<input type="search" data-announcement-search></label>
        </div>

        <div class="treasurer-announcement-list">
            <div class="treasurer-announcement-rows">
                @forelse ($announcements as $announcement)
                    <article data-announcement-category="{{ $announcement->category }}" data-announcement-date="{{ optional($announcement->published_at ?? $announcement->created_at)->format('Y-m-d') }}" data-announcement-text="{{ strtolower($announcement->title.' '.$announcement->content.' '.$announcement->category) }}">
                        <img src="{{ market_role_avatar($announcement->author?->usertype, $announcement->author?->profile) }}" alt="">
                        <div>
                            <h2>{{ optional($announcement->published_at)->format('F j, Y | g:i A') ?? $announcement->created_at->format('F j, Y | g:i A') }}</h2>
                            <p>{{ $announcement->content }}</p>
                        </div>
                        <div class="treasurer-announcement-actions">
                            <button type="button" data-open-dialog="announcementView{{ $announcement->id }}">View Full Details <i class="bi bi-arrow-right"></i></button>
                            <button type="button" data-open-dialog="announcementEdit{{ $announcement->id }}" title="Edit announcement"><i class="bi bi-pencil-fill"></i></button>
                        </div>
                    </article>
                @empty
                    <p class="empty-state announcement-empty">No announcement available</p>
                @endforelse
            </div>
            <footer>
                <span data-announcement-showing>Showing 1 to {{ min($announcements->count(), 5) }} of {{ $announcements->count() }} entries</span>
                <div><button type="button">&lt;</button><b>1</b><button type="button">&gt;</button></div>
            </footer>
        </div>
    </section>

    <dialog id="announcementDialog" class="market-dialog announcement-form-dialog">
        <form action="{{ route('treasurer.announcements.store') }}" method="POST" enctype="multipart/form-data" class="announcement-wire-form">
            @csrf
            <input type="hidden" name="category" value="OTHERS" data-announcement-hidden-category>
            <div class="announcement-wire-heading">
                <button type="button" data-close-dialog><i class="bi bi-arrow-left-circle-fill"></i></button>
                <i class="bi bi-megaphone-fill"></i>
                <div><h2 data-announcement-modal-title>OTHERS</h2><p>ANNOUNCEMENT</p></div>
                <button type="button" data-close-dialog><i class="bi bi-x-circle-fill"></i></button>
            </div>
            <div class="announcement-wire-body">
                <label class="announcement-date-row">Date &amp; Time:<input type="text" value="Auto-generated" readonly><i class="bi bi-calendar-date-fill"></i></label>
                <label>Title<input name="title" required></label>
                <label class="announcement-editor-row">Contents:
                    <div class="announcement-editor-shell">
                        <div class="announcement-editor-toolbar">
                            <select aria-label="Paragraph style"><option>Paragraph</option></select>
                            <button type="button"><b>B</b></button>
                            <button type="button"><i>I</i></button>
                            <button type="button"><u>U</u></button>
                            <button type="button"><i class="bi bi-list-ul"></i></button>
                            <button type="button"><i class="bi bi-list-ol"></i></button>
                            <button type="button"><i class="bi bi-text-left"></i></button>
                            <button type="button"><i class="bi bi-link-45deg"></i></button>
                            <button type="button"><i class="bi bi-image"></i></button>
                            <button type="button"><i class="bi bi-arrows-fullscreen"></i></button>
                        </div>
                        <textarea name="content" rows="6" required></textarea>
                    </div>
                </label>
                <label class="announcement-upload-row">Attachment:
                    <span class="announcement-dropzone"><i class="bi bi-cloud-arrow-up"></i><b>Drag and drop files here or click to browse</b><small>Supports: JPG, PNG, PDF (Max. 5MB)</small><input type="file" name="attachment" data-announcement-upload></span>
                    <span class="announcement-file-list" data-announcement-file-list></span>
                </label>
            </div>
            <div class="dialog-actions announcement-wire-actions"><button class="button button-primary">Post</button><button type="button" class="button button-muted" data-close-dialog>Cancel</button></div>
        </form>
    </dialog>

    @foreach ($announcements as $announcement)
        <dialog id="announcementView{{ $announcement->id }}" class="market-dialog announcement-detail-dialog">
            <article>
                <button type="button" class="login-close" aria-label="Close" data-close-dialog><i class="bi bi-x-circle-fill"></i></button>
                <span>{{ $announcement->category }}</span>
                <h1>{{ $announcement->title }}</h1>
                <small>{{ optional($announcement->published_at)->format('F j, Y g:i A') ?? $announcement->created_at->format('F j, Y g:i A') }}</small>
                <p>{{ $announcement->content }}</p>
                <div class="dialog-actions">
                    @if ($announcement->attachment_path)
                        <a class="button button-primary" href="{{ route('announcements.attachment', $announcement) }}" target="_blank"><i class="bi bi-paperclip"></i> Download Attachment</a>
                    @endif
                    <button type="button" class="button button-muted" data-close-dialog>Close</button>
                </div>
            </article>
        </dialog>

        <dialog id="announcementEdit{{ $announcement->id }}" class="market-dialog announcement-form-dialog">
            <form action="{{ route('treasurer.announcements.update', $announcement) }}" method="POST" enctype="multipart/form-data" class="announcement-wire-form">
                @csrf
                @method('PUT')
                <input type="hidden" name="category" value="{{ $announcement->category }}">
                <div class="announcement-wire-heading">
                    <button type="button" data-close-dialog><i class="bi bi-arrow-left-circle-fill"></i></button>
                    <i class="bi bi-megaphone-fill"></i>
                    <div><h2>{{ $announcement->category }}</h2><p>ANNOUNCEMENT</p></div>
                    <button type="button" data-close-dialog><i class="bi bi-x-circle-fill"></i></button>
                </div>
                <div class="announcement-wire-body">
                    <label class="announcement-date-row">Date &amp; Time:<input type="text" value="{{ optional($announcement->published_at)->format('F j, Y | g:i A') ?? 'Auto-generated' }}" readonly><i class="bi bi-calendar-date-fill"></i></label>
                    <label>Title<input name="title" value="{{ $announcement->title }}" required></label>
                    <label class="announcement-editor-row">Contents:
                        <div class="announcement-editor-shell">
                            <div class="announcement-editor-toolbar">
                                <select aria-label="Paragraph style"><option>Paragraph</option></select>
                                <button type="button"><b>B</b></button>
                                <button type="button"><i>I</i></button>
                                <button type="button"><u>U</u></button>
                                <button type="button"><i class="bi bi-list-ul"></i></button>
                                <button type="button"><i class="bi bi-list-ol"></i></button>
                                <button type="button"><i class="bi bi-text-left"></i></button>
                                <button type="button"><i class="bi bi-link-45deg"></i></button>
                                <button type="button"><i class="bi bi-image"></i></button>
                                <button type="button"><i class="bi bi-arrows-fullscreen"></i></button>
                            </div>
                            <textarea name="content" rows="6" required>{{ $announcement->content }}</textarea>
                        </div>
                    </label>
                    <label class="announcement-upload-row">Attachment:
                        <span class="announcement-dropzone"><i class="bi bi-cloud-arrow-up"></i><b>Drag and drop files here or click to browse</b><small>Supports: JPG, PNG, PDF (Max. 5MB)</small><input type="file" name="attachment" data-announcement-upload></span>
                        <span class="announcement-file-list" data-announcement-file-list>
                            @if ($announcement->attachment_path)
                                <span><i class="bi bi-paperclip"></i><b>{{ $announcement->attachment_name }}</b><small>Current file</small></span>
                            @endif
                        </span>
                    </label>
                    @if ($announcement->attachment_path)
                        <a class="announcement-current-attachment" href="{{ route('announcements.attachment', $announcement) }}" target="_blank"><i class="bi bi-paperclip"></i> {{ $announcement->attachment_name }}</a>
                    @endif
                </div>
                <div class="dialog-actions announcement-wire-actions"><button class="button button-primary">Post</button><button type="button" class="button button-muted" data-close-dialog>Cancel</button></div>
            </form>
        </dialog>
    @endforeach
@endsection

@push('scripts')
<script>
    let announcementCategory = 'OTHERS';

    function filterTreasurerAnnouncements() {
        const search = (document.querySelector('[data-announcement-search]')?.value || '').toLowerCase();
        const from = document.querySelector('[data-announcement-from]')?.value || '';
        const to = document.querySelector('[data-announcement-to]')?.value || '';
        let visible = 0;

        document.querySelectorAll('[data-announcement-category]').forEach((article) => {
            const matchesCategory = announcementCategory === 'ALL' || article.dataset.announcementCategory === announcementCategory;
            const matchesSearch = !search || article.dataset.announcementText.includes(search);
            const date = article.dataset.announcementDate || '';
            const matchesFrom = !from || date >= from;
            const matchesTo = !to || date <= to;
            const show = matchesCategory && matchesSearch && matchesFrom && matchesTo;
            article.hidden = !show;
            if (show) visible += 1;
        });

        const showing = document.querySelector('[data-announcement-showing]');
        if (showing) {
            showing.textContent = `Showing ${visible ? 1 : 0} to ${visible} of ${visible} entries`;
        }
    }

    document.querySelectorAll('[data-announcement-filter]').forEach((button) => button.addEventListener('click', () => {
        announcementCategory = button.dataset.announcementFilter;
        document.querySelectorAll('[data-announcement-filter]').forEach((tab) => tab.classList.toggle('active', tab === button));
        filterTreasurerAnnouncements();
    }));

    document.querySelector('[data-open-dialog="announcementDialog"]')?.addEventListener('click', () => {
        const activeTab = document.querySelector('[data-announcement-filter].active:not(.d-none)');
        const category = activeTab?.dataset.announcementFilter || announcementCategory || 'OTHERS';
        announcementCategory = category;
        document.querySelector('[data-announcement-hidden-category]').value = category;
        document.querySelector('[data-announcement-modal-title]').textContent = category;
    });

    filterTreasurerAnnouncements();

    document.querySelector('[data-announcement-search]')?.addEventListener('input', filterTreasurerAnnouncements);
    document.querySelector('[data-announcement-date-filter]')?.addEventListener('click', filterTreasurerAnnouncements);

    document.querySelectorAll('[data-announcement-upload]').forEach((input) => {
        input.addEventListener('change', () => {
            const list = input.closest('.announcement-upload-row')?.querySelector('[data-announcement-file-list]');
            if (!list) return;

            list.innerHTML = '';
            Array.from(input.files || []).forEach((file) => {
                const row = document.createElement('span');
                row.innerHTML = `<i class="bi bi-paperclip"></i><b>${file.name}</b><small>${Math.ceil(file.size / 1024)} KB</small>`;
                list.appendChild(row);
            });
        });
    });
</script>
@endpush
