@extends('market.layouts.portal')

@section('content')
    <section class="panel">
        <div class="panel-heading">
            <div><span class="eyebrow">Administrator Records</span><h2>{{ $pageTitle }}</h2></div>
            <div class="panel-heading-actions">
                <form method="GET"><input name="search" value="{{ request('search') }}" placeholder="Search members"><button class="table-action"><i class="bi bi-search"></i></button></form>
                <button type="button" class="button button-primary" data-open-dialog="memberDialog"><i class="bi bi-person-plus"></i> Add member</button>
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Name</th><th>Username</th><th>Designation</th><th>Contact</th><th>Address</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                    @forelse ($members as $member)
                        <tr>
                            <td><strong>{{ $member->full_name }}</strong><br><small>{{ $member->email }}</small></td>
                            <td>{{ $member->username }}</td>
                            <td>{{ $member->designation }}</td>
                            <td>{{ $member->phone_num }}</td>
                            <td>{{ $member->address }}</td>
                            <td><span class="status status-{{ strtolower($member->status) }}">{{ $member->status }}</span></td>
                            <td>
                                <form action="{{ route('administrator.members.update', $member) }}" method="POST">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="status" value="{{ $member->status === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE' }}">
                                    <button class="table-action" title="Toggle account status"><i class="bi bi-power"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="empty-state">No {{ strtolower($role) }} records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-wrap">{{ $members->links() }}</div>
    </section>

    <dialog id="memberDialog" class="market-dialog">
        <form action="{{ route('administrator.members.store', ['role' => strtolower($role)]) }}" method="POST">
            @csrf
            <div class="dialog-heading"><div><span>Administrator</span><h2>Add {{ ucfirst(strtolower($role)) }}</h2></div><button type="button" data-close-dialog>×</button></div>
            <div class="form-grid dialog-form">
                <label>First name<input name="firstname" required></label>
                <label>Middle name<input name="middlename"></label>
                <label>Last name<input name="lastname" required></label>
                <label>Username<input name="username" required></label>
                <label>Email<input type="email" name="email"></label>
                <label>Phone number<input name="phone_num" required></label>
                <label>Designation<input name="designation" value="{{ ucfirst(strtolower($role)) }}" required></label>
                <label>Temporary password<input type="password" name="password" required></label>
                <label class="full">Address<input name="address" required></label>
            </div>
            <div class="dialog-actions"><button type="button" class="button button-muted" data-close-dialog>Cancel</button><button class="button button-primary">Create account</button></div>
        </form>
    </dialog>
@endsection
