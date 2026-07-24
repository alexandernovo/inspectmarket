@extends('market.layouts.public')

@section('title', ($mode === 'register' ? 'Create Account' : 'Reset Password').' | E-Inspect')

@section('content')
    <main class="login-page">
        <div class="login-shade"></div>
        <a href="{{ route('home') }}" class="login-back"><i class="bi bi-arrow-left"></i> Back to homepage</a>
        <section class="login-card account-card">
            <img src="{{ asset('assets/einspect/HOMEPAGE/Logo.png') }}" alt="Pandan seal">
            @if ($step === 'phone')
                <h1>{{ $mode === 'register' ? 'SIGN IN' : 'RESET PASSWORD' }}</h1>
                <p>{{ $mode === 'register' ? 'Register your phone number to create an account.' : 'Enter the phone number registered to your account.' }}</p>
                <form action="{{ $mode === 'register' ? route('account.register.code', ['role' => strtolower($role)]) : route('account.reset.code') }}" method="POST">
                    @csrf
                    <label>Phone number<span><i class="bi bi-phone"></i><input name="phone_num" value="{{ old('phone_num') }}" required></span></label>
                    <button class="button button-primary">Next</button>
                </form>
            @elseif ($step === 'verify')
                <h1>VERIFICATION CODE</h1>
                <p>Your verification code was sent to {{ $phone }}.</p>
                @if (! empty($debugCode))
                    <div class="debug-code">Local verification code: <strong>{{ $debugCode }}</strong></div>
                @endif
                <form action="{{ route('account.verify') }}" method="POST">
                    @csrf
                    <label>Six-digit code<span><i class="bi bi-shield-lock"></i><input name="code" inputmode="numeric" maxlength="6" required autofocus></span></label>
                    <button class="button button-primary">Verify</button>
                </form>
            @else
                <h1>{{ $mode === 'register' ? 'SET YOUR PASSWORD' : 'NEW PASSWORD' }}</h1>
                <p>Use at least eight characters.</p>
                <form action="{{ $mode === 'register' ? route('account.store') : route('account.reset') }}" method="POST">
                    @csrf
                    @if ($mode === 'register')
                        <div class="form-grid account-form-grid">
                            <label>First name<span><input name="firstname" required></span></label>
                            <label>Middle name<span><input name="middlename"></span></label>
                            <label>Last name<span><input name="lastname" required></span></label>
                            <label>Username<span><input name="username" required></span></label>
                            <label>Email<span><input type="email" name="email"></span></label>
                            <label>Address<span><input name="address" required></span></label>
                        </div>
                    @endif
                    <label>Password<span><i class="bi bi-lock"></i><input type="password" name="password" required></span></label>
                    <label>Confirm password<span><i class="bi bi-lock-fill"></i><input type="password" name="password_confirmation" required></span></label>
                    <button class="button button-primary">{{ $mode === 'register' ? 'Create account' : 'Reset password' }}</button>
                </form>
            @endif
            @if ($errors->any())<div class="alert-error">{{ $errors->first() }}</div>@endif
        </section>
    </main>
@endsection
