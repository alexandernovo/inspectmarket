@extends('market.layouts.public')

@section('title', ($mode === 'register' ? 'Sign In' : 'Reset Password').' | E-Inspect')

@section('content')
    @include('market.public.header', ['active' => '', 'announcementCount' => \App\Models\Announcement::where('is_published', true)->count()])
    <main class="login-page account-page">
        <div class="login-shade"></div>
        <section class="login-card account-card">
            <a href="{{ route('home') }}" class="login-close" aria-label="Close"><i class="bi bi-x-circle-fill"></i></a>
            <div class="account-card-role">
                <img src="{{ asset('assets/einspect/HOMEPAGE/Logo.png') }}" alt="Pandan seal">
                @if ($step === 'phone')
                    <h1>{{ $mode === 'register' ? 'SIGN IN' : 'RESET PASSWORD' }}</h1>
                    <p>{{ $mode === 'register' ? 'Register your phone number to create an account' : 'Enter the phone number registered to your account' }}</p>
                @elseif ($step === 'verify')
                    <h1>VERIFICATION CODE</h1>
                    <p>Your verification code is sent by SMS to<br>{{ $phone }}</p>
                @else
                    <h1>{{ $mode === 'register' ? 'SET YOUR PASSWORD' : 'NEW PASSWORD' }}</h1>
                    <p>Password must be 8–16 characters long and contain one uppercase and lowercase letter</p>
                @endif
            </div>

            @if ($step === 'phone')
                <form action="{{ $mode === 'register' ? route('account.register.code', ['role' => strtolower($role)]) : route('account.reset.code') }}" method="POST">
                    @csrf
                    <label class="phone-wireframe-input"><span><b>🇵🇭</b><input name="phone_num" value="{{ old('phone_num') }}" placeholder="Enter phone number" required><i class="bi bi-person-fill"></i></span></label>
                    <button class="button button-primary">Next</button>
                </form>
            @elseif ($step === 'verify')
                @if (! empty($debugCode))<div class="debug-code">Local verification code: <strong>{{ $debugCode }}</strong></div>@endif
                <form action="{{ route('account.verify') }}" method="POST" id="verificationForm">
                    @csrf
                    <input type="hidden" name="code" id="verificationCode">
                    <div class="otp-inputs">
                        @for ($digit = 0; $digit < 6; $digit++)<input type="text" inputmode="numeric" maxlength="1" required>@endfor
                    </div>
                    <button class="button button-primary">Next</button>
                </form>
            @else
                <form action="{{ $mode === 'register' ? route('account.store') : route('account.reset') }}" method="POST">
                    @csrf
                    <label>Password<span><i class="bi bi-lock-fill"></i><input type="password" name="password" placeholder="Enter password" required></span></label>
                    <label>Confirm Password<span><i class="bi bi-lock-fill"></i><input type="password" name="password_confirmation" placeholder="Confirm password" required></span></label>
                    <button class="button button-primary">Next</button>
                </form>
            @endif
            @if ($errors->any())<div class="alert-error">{{ $errors->first() }}</div>@endif
        </section>
    </main>
    <footer class="public-footer">© Copyright {{ date('Y') }}. Developed by KAJS CODERS INVADER. All Rights Reserved</footer>
    @if ($step === 'verify')
        <script>
            const otpInputs = [...document.querySelectorAll('.otp-inputs input')];
            otpInputs.forEach((input, index) => input.addEventListener('input', () => {
                input.value = input.value.replace(/\D/g, '');
                if (input.value && otpInputs[index + 1]) otpInputs[index + 1].focus();
            }));
            document.getElementById('verificationForm').addEventListener('submit', () => {
                document.getElementById('verificationCode').value = otpInputs.map(input => input.value).join('');
            });
        </script>
    @endif
@endsection
