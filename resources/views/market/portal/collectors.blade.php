@extends('market.layouts.portal')

@section('content')
    <section class="panel">
        <div class="panel-heading">
            <div><span class="eyebrow">Cash Ticket Collection</span><h2>Collectors</h2></div>
            <button type="button" class="button button-primary" data-open-dialog="collectorDialog"><i class="bi bi-person-plus"></i> Add Collector</button>
        </div>
        <div class="table-wrap">
            <table class="market-data-table">
                <thead><tr><th>Collector ID</th><th>Full Name</th><th>Contact Number</th><th>Designation</th><th>Status</th><th>Date Hired</th><th>Action</th></tr></thead>
                <tbody>
                    @foreach ($collectors as $collector)
                        <tr>
                            <td>COL-{{ str_pad($collector->id, 4, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ $collector->full_name }}</td>
                            <td>{{ $collector->phone_num }}</td>
                            <td>{{ $collector->designation }}</td>
                            <td><span class="status status-{{ strtolower($collector->status) }}">{{ $collector->status }}</span></td>
                            <td>{{ $collector->created_at->format('M d, Y') }}</td>
                            <td><button class="table-action" data-open-dialog="collector-{{ $collector->id }}"><i class="bi bi-pencil-square"></i></button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <dialog id="collectorDialog" class="market-dialog">
        <form action="{{ route('treasurer.collectors.store') }}" method="POST">
            @csrf
            <div class="dialog-heading"><div><span>Cash Ticket Collection</span><h2>Add Collector</h2></div><button type="button" data-close-dialog>×</button></div>
            <div class="form-grid">
                <label>First Name<input name="firstname" required></label>
                <label>Middle Name<input name="middlename"></label>
                <label>Last Name<input name="lastname" required></label>
                <label>Username<input name="username" required></label>
                <label>Date Hired<input type="date" value="{{ now()->toDateString() }}" disabled></label>
                <label>Designation<input name="designation" value="Revenue Collector Clerk" required></label>
                <label>Contact Number<input name="phone_num" required></label>
                <label>Email Address<input type="email" name="email"></label>
                <label class="full">Address<input name="address" required></label>
                <label>Password<input type="password" name="password" required></label>
            </div>
            <div class="dialog-actions"><button type="button" class="button button-muted" data-close-dialog>Cancel</button><button class="button button-primary">Save Collector</button></div>
        </form>
    </dialog>

    @foreach ($collectors as $collector)
        <dialog id="collector-{{ $collector->id }}" class="market-dialog compact-dialog">
            <form action="{{ route('treasurer.collectors.update', $collector) }}" method="POST">
                @csrf @method('PUT')
                <div class="dialog-heading"><div><span>Collector Record</span><h2>{{ $collector->full_name }}</h2></div><button type="button" data-close-dialog>×</button></div>
                <label>Designation<input name="designation" value="{{ $collector->designation }}" required></label>
                <label>Status<select name="status"><option @selected($collector->status === 'ACTIVE')>ACTIVE</option><option @selected($collector->status === 'INACTIVE')>INACTIVE</option></select></label>
                <div class="dialog-actions"><button type="button" class="button button-muted" data-close-dialog>Cancel</button><button class="button button-primary">Save</button></div>
            </form>
        </dialog>
    @endforeach
@endsection
