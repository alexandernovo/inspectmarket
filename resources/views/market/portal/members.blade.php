@extends('market.layouts.portal')

@section('content')
    @php
        $roleName = ucfirst(strtolower($role));
        $roleIcon = match ($role) {
            'TREASURER' => 'bi-person-badge-fill',
            'CLERK' => 'bi-person-vcard-fill',
            'INSPECTOR' => 'bi-clipboard2-pulse-fill',
            default => 'bi-people-fill',
        };
    @endphp

    <section class="administrator-record-table-page">
        <header class="administrator-page-heading">
            <i class="bi {{ $roleIcon }}"></i>
            <div><h1>{{ strtoupper($roleName) }}</h1><p>Dashboard | {{ $roleName }}</p></div>
        </header>

        <div class="administrator-table-panel">
            <div class="administrator-table-toolbar">
                <select><option>10 v</option></select>
                <form method="GET" class="administrator-member-search-form">
                    <input name="search" value="{{ request('search') }}" placeholder="Search">
                    <button><i class="bi bi-search"></i></button>
                </form>
                <button type="button" data-open-dialog="memberDialog"><i class="bi bi-person-plus-fill"></i> Add {{ $roleName }}</button>
                <nav>
                    <a href="{{ route('administrator.records', strtolower($role)) }}">Records</a>
                    <a class="active" href="{{ route('administrator.members', strtolower($role)) }}">Accounts</a>
                </nav>
            </div>
            <div class="table-wrap">
                <table class="administrator-record-table">
                    <thead>
                        <tr><th>NO.</th><th>FULL NAME</th><th>USERNAME</th><th>CONTACT NUMBER</th><th>ADDRESS</th><th>DESIGNATION</th><th>STATUS</th><th>ACTION</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($members as $member)
                            <tr>
                                <td>{{ ($members->currentPage() - 1) * $members->perPage() + $loop->iteration }}</td>
                                <td>{{ $member->full_name }}</td>
                                <td>{{ $member->username }}</td>
                                <td>{{ $member->phone_num }}</td>
                                <td>{{ $member->address }}</td>
                                <td>{{ $member->designation }}</td>
                                <td><span class="status status-{{ strtolower($member->status) }}">{{ str($member->status)->title() }}</span></td>
                                <td>
                                    <form action="{{ route('administrator.members.update', $member) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="{{ $member->status === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE' }}">
                                        <button type="submit" class="table-action edit" title="Toggle account status"><i class="bi bi-power"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="empty-state">No {{ strtolower($roleName) }} records found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <footer class="administrator-table-footer">Showing {{ $members->firstItem() ?? 0 }} to {{ $members->lastItem() ?? 0 }} of {{ $members->total() }} entries <span>{{ $members->links() }}</span></footer>
        </div>
    </section>

    <dialog id="memberDialog" class="market-dialog administrator-member-dialog">
        <form action="{{ route('administrator.members.store', ['role' => strtolower($role)]) }}" method="POST">
            @csrf
            <div class="administrator-member-heading">
                <button type="button" data-close-dialog><i class="bi bi-arrow-left-circle-fill"></i></button>
                <i class="bi {{ $roleIcon }}"></i>
                <div><h2>ADD {{ strtoupper($roleName) }}</h2><p>ACCOUNT INFORMATION</p></div>
                <button type="button" data-close-dialog><i class="bi bi-x-circle"></i></button>
            </div>
            <div class="administrator-member-grid">
                <label>First Name:<input name="firstname" required></label>
                <label>Middle Name:<input name="middlename"></label>
                <label>Last Name:<input name="lastname" required></label>
                <label>Username:<input name="username" required></label>
                <label>Email Address:<input type="email" name="email"></label>
                <label>Contact Number:<input name="phone_num" required></label>
                <label>Designation:<input name="designation" value="{{ $roleName }}" required></label>
                <label>Password:<input type="password" name="password" required></label>
                <label class="full">Address:<input name="address" required></label>
            </div>
            <footer class="dialog-actions administrator-member-actions">
                <button type="submit" class="button button-primary">Save</button>
                <button type="button" class="button button-muted" data-close-dialog>Cancel</button>
            </footer>
        </form>
    </dialog>
@endsection
