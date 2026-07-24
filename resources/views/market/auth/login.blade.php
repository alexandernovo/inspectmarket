@extends('market.layouts.public')

@section('title', ucfirst(strtolower($role)).' Login | E-Inspect')

@section('content')
    @php
        $roleSlug = strtolower($role);
        $avatar = match ($roleSlug) {
            'administrator' => 'A-Administrator.png',
            'treasurer' => 'B-Treasurer.png',
            'clerk' => 'C-Clerk.png',
            'inspector' => 'D-Inspector.png',
            default => 'E-Male Tenant.png',
        };
    @endphp
    <main class="login-page">
        <div class="login-shade"></div>
        <a href="{{ route('home') }}" class="login-back"><i class="bi bi-arrow-left"></i> Back to homepage</a>
        <section class="login-card">
            <img src="{{ asset('assets/einspect/USERS/'.$avatar) }}" alt="{{ ucfirst($roleSlug) }}">
            <h1>{{ $role }}</h1>
            <p>Enter your username and password to access your portal.</p>
            @if (session('success'))
                <div class="alert-success">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert-error">{{ $errors->first() }}</div>
            @endif
            <form action="{{ route('portal.login.store', ['role' => $roleSlug]) }}" method="POST">
                @csrf
                <label>
                    Username
                    <span><i class="bi bi-person-fill"></i><input type="text" name="username" value="{{ old('username') }}" required autofocus></span>
                </label>
                <label>
                    Password
                    <span><i class="bi bi-lock-fill"></i><input type="password" name="password" required></span>
                </label>
                <label class="remember"><input type="checkbox" name="remember" value="1"> Remember me</label>
                <button type="submit" class="button button-primary">Login</button>
            </form>
            <div class="login-links">
                <a href="{{ route('account.register', ['role' => $roleSlug]) }}">Create account</a>
                <a href="{{ route('account.forgot') }}">Forgot password?</a>
            </div>
            <small>Demo password: <strong>password</strong></small>
        </section>
    </main>
@endsection
