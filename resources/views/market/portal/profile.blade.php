@extends('market.layouts.portal')

@section('content')
    @if ($user->isRole('INSPECTOR') || $user->isRole('TREASURER') || $user->isRole('CLERK') || $user->isRole('TENANT'))
        @php
            $profileDefaultAvatar = match (true) {
                $user->isRole('TREASURER') => asset('assets/einspect/USERS/B-Treasurer.png'),
                $user->isRole('CLERK') => asset('assets/einspect/USERS/C-Clerk.png'),
                $user->isRole('TENANT') => asset('assets/einspect/USERS/5-Tenants.png'),
                default => asset('assets/einspect/USERS/D-Inspector.png'),
            };
            $profileRoleTitle = $user->designation ?: match (true) {
                $user->isRole('TREASURER') => 'Municipal Treasurer',
                $user->isRole('CLERK') => 'Revenue Collector Clerk',
                $user->isRole('TENANT') => 'Tenant',
                default => 'Inspector',
            };
        @endphp
        <section class="inspector-page inspector-profile-page">
            <header class="inspector-page-title">
                <i class="bi bi-person-circle"></i>
                <div><h1>PROFILE</h1><p>Dashboard | Profile</p></div>
            </header>
            <div class="inspector-profile-layout">
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="inspector-profile-card">
                    @csrf @method('PUT')
                    <input type="hidden" name="firstname" value="{{ $user->firstname }}">
                    <input type="hidden" name="middlename" value="{{ $user->middlename }}">
                    <input type="hidden" name="lastname" value="{{ $user->lastname }}">
                    <input type="hidden" name="email" value="{{ $user->email }}">
                    <input type="hidden" name="phone_num" value="{{ $user->phone_num }}">
                    <input type="hidden" name="address" value="{{ $user->address }}">
                    <input type="hidden" name="password_confirmation" data-inspector-password-confirmation>
                    <label class="inspector-profile-avatar" title="Change profile image">
                        <img src="{{ $user->profile ? asset('storage/'.$user->profile) : $profileDefaultAvatar }}" alt="{{ $user->full_name }}">
                        <input type="file" name="profile_image" accept="image/*" hidden>
                    </label>
                    <h2>{{ strtoupper($user->full_name) }}</h2>
                    <p>{{ $profileRoleTitle }}</p>
                    @if ($user->isRole('TENANT'))
                        <div class="tenant-profile-details">
                            <span><b>Tenant ID</b><em>TEN - {{ $user->created_at->format('Y') }} - {{ str_pad($user->id, 5, '0', STR_PAD_LEFT) }}</em></span>
                            <span><b>Email</b><em>{{ $user->email ?: 'Not provided' }}</em></span>
                            <span><b>Contact No.</b><em>{{ $user->phone_num }}</em></span>
                            <span><b>Address</b><em>{{ $user->address }}</em></span>
                        </div>
                    @endif
                    <label><i class="bi bi-person-circle"></i><input name="username" value="{{ old('username', $user->username) }}" required></label>
                    <label><i class="bi bi-lock-fill"></i><input type="password" name="password" placeholder="New password"></label>
                    <button type="submit">Save</button>
                </form>
                <div class="inspector-profile-seal"><img src="{{ asset('assets/einspect/HOMEPAGE/Logo.png') }}" alt="Municipality of Pandan official seal"></div>
            </div>
        </section>
    @else
    <section class="panel profile-panel {{ $user->isRole('TENANT') ? 'tenant-profile-panel' : '' }}">
        @if ($user->isRole('TENANT'))
            <div class="tenant-profile-layout">
                <div class="tenant-profile-card">
                    <div class="tenant-profile-heading">
                        <img src="{{ $user->profile ? asset('storage/'.$user->profile) : asset('assets/einspect/USERS/5-Tenants.png') }}" alt="{{ $user->full_name }}">
                        <div>
                            <h2>{{ $user->full_name }}</h2>
                            <p>Tenant</p>
                        </div>
                    </div>
                    <dl>
                        <div><dt>Tenant ID</dt><dd>TEN - {{ $user->created_at->format('Y') }} - {{ str_pad($user->id, 5, '0', STR_PAD_LEFT) }}</dd></div>
                        <div><dt>Contact Number</dt><dd>{{ $user->phone_num }}</dd></div>
                        <div><dt>Email Address</dt><dd>{{ $user->email ?: 'Not provided' }}</dd></div>
                        <div><dt>Address</dt><dd>{{ $user->address }}</dd></div>
                    </dl>
                </div>
                <div class="tenant-profile-seal">
                    <img src="{{ asset('assets/einspect/HOMEPAGE/Logo.png') }}" alt="Municipality of Pandan official seal">
                </div>
            </div>
        @else
            <div class="profile-cover" style="{{ $user->background ? "background-image:url('".asset('storage/'.$user->background)."')" : '' }}">
                <img src="{{ $user->profile ? asset('storage/'.$user->profile) : asset('assets/einspect/HOMEPAGE/Logo.png') }}" alt="{{ $user->full_name }}">
                <div><h2>{{ $user->full_name }}</h2><p>{{ $user->designation }}</p></div>
            </div>
        @endif
        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="profile-form {{ $user->isRole('TENANT') ? 'tenant-profile-form' : '' }}">
            @csrf @method('PUT')
            <div class="form-grid">
                <label>First name<input name="firstname" value="{{ old('firstname', $user->firstname) }}" required></label>
                <label>Middle name<input name="middlename" value="{{ old('middlename', $user->middlename) }}"></label>
                <label>Last name<input name="lastname" value="{{ old('lastname', $user->lastname) }}" required></label>
                <label>Username<input name="username" value="{{ old('username', $user->username) }}" required></label>
                <label>Email<input type="email" name="email" value="{{ old('email', $user->email) }}"></label>
                <label>Phone number<input name="phone_num" value="{{ old('phone_num', $user->phone_num) }}" required></label>
                <label class="full">Address<input name="address" value="{{ old('address', $user->address) }}" required></label>
                <label>Profile image<input type="file" name="profile_image" accept="image/*"></label>
                @unless ($user->isRole('TENANT'))
                    <label>Cover image<input type="file" name="background_image" accept="image/*"></label>
                @endunless
                <label>New password<input type="password" name="password"></label>
                <label>Confirm password<input type="password" name="password_confirmation"></label>
            </div>
            <button class="button button-primary">Save profile</button>
        </form>
    </section>
    @endif
@endsection

@push('scripts')
    @if ($user->isRole('INSPECTOR') || $user->isRole('TREASURER') || $user->isRole('CLERK') || $user->isRole('TENANT'))
        <script>
            document.querySelector('.inspector-profile-card [name="password"]')?.addEventListener('input', function () {
                document.querySelector('[data-inspector-password-confirmation]').value = this.value;
            });
        </script>
    @endif
@endpush
