@extends('market.layouts.public')

@section('title', 'User Login | E-Inspect')

@section('content')
    @php
        $roles = [
            ['administrator', 'ADMINISTRATOR', '1-Administrator.png'],
            ['treasurer', 'TREASURER', '2-Treasurer.png'],
            ['inspector', 'INSPECTOR', '4-Sanitary Inspector.png'],
            ['clerk', 'CLERK', '3-Collector Clerk.png'],
            ['tenant', 'TENANT', '5-Tenants.png'],
        ];
    @endphp

    @include('market.public.header', ['active' => '', 'announcementCount' => $announcementCount])
    <main class="role-selection-page">
        <div class="role-selection-heading">
            <img src="{{ asset('assets/einspect/HOMEPAGE/Logo.png') }}" alt="Municipality of Pandan seal">
            <h1>E-INSPECT:</h1>
            <p>Public Market Inspection Recording Management System</p>
            <span>{{ $mode === 'register' ? 'Create an Account' : 'User-Login' }}</span>
        </div>

        <div class="wireframe-role-grid">
            @foreach ($roles as [$slug, $label, $image])
                <a href="{{ $mode === 'register' ? route('account.register', $slug) : '#login-'.$slug }}" class="wireframe-role role-{{ $slug }}">
                    <img src="{{ asset('assets/einspect/USERS/'.$image) }}" alt="{{ $label }}">
                    <span>{{ $label }}</span>
                </a>
            @endforeach
        </div>

        @if ($mode === 'login')
            @foreach ($roles as [$slug, $label, $image])
                <section id="login-{{ $slug }}" class="role-login-modal" aria-label="{{ $label }} login modal">
                    <a href="{{ route('public.roles', 'login') }}" class="modal-backdrop" aria-label="Close"></a>
                    <div class="login-card role-modal-card role-modal-{{ $slug }}">
                        <a href="{{ route('public.roles', 'login') }}" class="login-close" aria-label="Close"><i class="bi bi-x-circle-fill"></i></a>
                        <div class="login-card-role">
                            <img src="{{ asset('assets/einspect/USERS/'.$image) }}" alt="{{ $label }}">
                            <h1>{{ $label }}</h1>
                            <p>Enter your username and password to log in your account</p>
                        </div>
                        <form action="{{ route('portal.login.store', ['role' => $slug]) }}" method="POST">
                            @csrf
                            <label>Username:
                                <span><i class="bi bi-person-fill"></i><input type="text" name="username" placeholder="Enter username" required></span>
                            </label>
                            <label>Password:
                                <span><i class="bi bi-lock-fill"></i><input type="password" name="password" placeholder="Enter password" required><button type="button" class="password-toggle" aria-label="Show password"><i class="bi bi-eye-fill"></i></button></span>
                            </label>
                            <a class="forgot-link" href="{{ route('account.forgot') }}">Forgot Password?</a>
                            <button type="submit" class="button button-primary">Login</button>
                        </form>
                        <p class="account-prompt">Don't have an account? <a href="{{ route('account.register', ['role' => $slug]) }}">Sign in</a></p>
                    </div>
                </section>
            @endforeach
        @endif
    </main>
    <footer class="public-footer">&copy; Copyright {{ date('Y') }}. Developed by KAJS CODERS INVADER. All Rights Reserved</footer>

    @if ($mode === 'login')
        <script>
            document.querySelectorAll('.password-toggle').forEach((toggle) => {
                toggle.addEventListener('click', () => {
                    const input = toggle.closest('span')?.querySelector('input');
                    if (input) input.type = input.type === 'password' ? 'text' : 'password';
                });
            });
        </script>
    @endif
@endsection
