@extends('market.layouts.public')

@section('title', ucfirst(strtolower($role)).' Login | E-Inspect')

@section('content')
    @php
        $roleSlug = strtolower($role);
        $avatar = match ($roleSlug) {
            'administrator' => '1-Administrator.png',
            'treasurer' => '2-Treasurer.png',
            'clerk' => '3-Collector Clerk.png',
            'inspector' => '4-Sanitary Inspector.png',
            default => '5-Tenants.png',
        };
    @endphp
    @include('market.public.header', ['active' => '', 'announcementCount' => \App\Models\Announcement::where('is_published', true)->count()])
    <main class="login-page">
        <div class="login-role-background" aria-hidden="true">
            @foreach ([
                ['administrator', '1-Administrator.png'],
                ['treasurer', '2-Treasurer.png'],
                ['inspector', '4-Sanitary Inspector.png'],
                ['clerk', '3-Collector Clerk.png'],
                ['tenant', '5-Tenants.png'],
            ] as [$backgroundRole, $backgroundAvatar])
                <div class="wireframe-role role-{{ $backgroundRole }}"><img src="{{ asset('assets/einspect/USERS/'.$backgroundAvatar) }}" alt=""><span>{{ strtoupper($backgroundRole) }}</span></div>
            @endforeach
        </div>
        <div class="login-shade"></div>
        <section class="login-card">
            <a href="{{ route('public.roles', 'login') }}" class="login-close" aria-label="Close"><i class="bi bi-x-circle-fill"></i></a>
            <div class="login-card-role">
                <img src="{{ asset('assets/einspect/USERS/'.$avatar) }}" alt="{{ ucfirst($roleSlug) }}">
                <h1>{{ $role }}</h1>
                <p>Enter your username and password to log in your account</p>
            </div>
            @if (session('success'))<div class="alert-success">{{ session('success') }}</div>@endif
            @if ($errors->any())<div class="alert-error">{{ $errors->first() }}</div>@endif
            <form action="{{ route('portal.login.store', ['role' => $roleSlug]) }}" method="POST">
                @csrf
                <label>Username:
                    <span><i class="bi bi-person-fill"></i><input type="text" name="username" value="{{ old('username') }}" placeholder="Enter username" required autofocus></span>
                </label>
                <label>Password:
                    <span><i class="bi bi-lock-fill"></i><input id="loginPassword" type="password" name="password" placeholder="Enter password" required><button type="button" class="password-toggle" aria-label="Show password"><i class="bi bi-eye-fill"></i></button></span>
                </label>
                <a class="forgot-link" href="{{ route('account.forgot') }}">Forgot Password?</a>
                <button type="submit" class="button button-primary">Login</button>
            </form>
            <p class="account-prompt">Don’t have an account? <a href="{{ route('account.register', ['role' => $roleSlug]) }}">Sign in</a></p>
        </section>
    </main>
    <footer class="public-footer">© Copyright {{ date('Y') }}. Developed by KAJS CODERS INVADER. All Rights Reserved</footer>
    <script>
        document.querySelector('.password-toggle')?.addEventListener('click', () => {
            const password = document.getElementById('loginPassword');
            password.type = password.type === 'password' ? 'text' : 'password';
        });
    </script>
@endsection
