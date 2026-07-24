@extends('market.layouts.portal')

@section('content')
    <section class="panel profile-panel">
        <div class="profile-cover" style="{{ $user->background ? "background-image:url('".asset('storage/'.$user->background)."')" : '' }}">
            <img src="{{ $user->profile ? asset('storage/'.$user->profile) : asset('assets/einspect/HOMEPAGE/Logo.png') }}" alt="{{ $user->full_name }}">
            <div><h2>{{ $user->full_name }}</h2><p>{{ $user->designation }}</p></div>
        </div>
        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="profile-form">
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
                <label>Cover image<input type="file" name="background_image" accept="image/*"></label>
                <label>New password<input type="password" name="password"></label>
                <label>Confirm password<input type="password" name="password_confirmation"></label>
            </div>
            <button class="button button-primary">Save profile</button>
        </form>
    </section>
@endsection
