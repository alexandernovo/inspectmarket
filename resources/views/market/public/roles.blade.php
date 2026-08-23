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
        $displayRoles = $mode === 'register'
            ? collect($roles)->reject(fn ($role) => $role[0] === 'administrator')->values()
            : collect($roles);
    @endphp

    @include('market.public.header', ['active' => '', 'announcementCount' => $announcementCount])
    <main @class(['role-selection-page', 'role-selection-register' => $mode === 'register'])>
        <div class="role-selection-heading">
            <img src="{{ asset('assets/einspect/HOMEPAGE/Logo.png') }}" alt="Municipality of Pandan seal">
            <h1>E-INSPECT:</h1>
            <p>Public Market Inspection Recording Management System</p>
            <span>{{ $mode === 'register' ? 'Create an Account' : 'User-Login' }}</span>
        </div>

        <div class="wireframe-role-grid">
            @foreach ($displayRoles as [$slug, $label, $image])
                @php
                    $roleHref = auth()->check() && auth()->user()->isRole(strtoupper($slug))
                        ? route($slug.'.dashboard')
                        : '#'.($mode === 'register' ? 'register' : 'login').'-'.$slug;
                @endphp
                <div class="wireframe-role-column role-{{ $slug }}">
                    <a href="{{ $roleHref }}" class="wireframe-role">
                        <img src="{{ asset('assets/einspect/USERS/'.$image) }}" alt="{{ $label }}">
                        <span>{{ $label }}</span>
                    </a>
                </div>
            @endforeach
        </div>

        @foreach ($displayRoles as [$slug, $label, $image])
            @if ($mode === 'login')
                <section id="login-{{ $slug }}" class="role-login-modal js-role-modal" aria-label="{{ $label }} login modal">
                    <button type="button" class="modal-backdrop" aria-label="Close" data-close-role-modal></button>
                    <div class="login-card role-modal-card role-modal-{{ $slug }}">
                        <button type="button" class="login-close" aria-label="Close" data-close-role-modal><i class="bi bi-x-circle-fill"></i></button>
                        <div class="login-card-role">
                            <img src="{{ asset('assets/einspect/USERS/'.$image) }}" alt="{{ $label }}">
                            <h1>{{ $label }}</h1>
                            <p>Enter your username and password to log in your account</p>
                        </div>
                        <form action="{{ route('portal.login.store', ['role' => $slug]) }}" method="POST" @class(['has-login-error' => $errors->has('login_'.$slug)])>
                            @csrf
                            <label>Username:
                                <span><i class="bi bi-person-fill"></i><input type="text" name="username" value="{{ old('username') }}" placeholder="Enter username" required></span>
                            </label>
                            <label>Password:
                                <span><i class="bi bi-lock-fill"></i><input type="password" name="password" placeholder="Enter password" required><button type="button" class="password-toggle" aria-label="Show password"><i class="bi bi-eye-fill"></i></button></span>
                                @error('login_'.$slug)
                                    <small class="login-field-error">{{ $message }}</small>
                                @enderror
                            </label>
                            <a class="forgot-link" href="{{ route('account.forgot') }}">Forgot Password?</a>
                            <button type="submit" class="button button-primary">Login</button>
                        </form>
                        @if ($slug !== 'administrator')
                            <p class="account-prompt">Don't have an account? <a href="{{ route('public.roles', 'register') }}#register-{{ $slug }}">Sign in</a></p>
                        @endif
                    </div>
                </section>
            @else
                <section id="register-{{ $slug }}" class="role-login-modal js-role-modal" aria-label="{{ $label }} sign in modal">
                    <button type="button" class="modal-backdrop" aria-label="Close" data-close-role-modal></button>
                    <div class="login-card account-card role-modal-card role-modal-{{ $slug }}">
                        <button type="button" class="login-close" aria-label="Close" data-close-role-modal><i class="bi bi-x-circle-fill"></i></button>
                        <div class="account-card-role">
                            <img src="{{ asset('assets/einspect/USERS/'.$image) }}" alt="{{ $label }}">
                            <h1>SIGN IN</h1>
                            <p data-register-instruction>Register your phone number to create your {{ strtolower($label) }} account</p>
                        </div>
                        <form action="{{ route('account.register.code', ['role' => $slug]) }}" method="POST" class="js-home-register-form" data-role="{{ $slug }}" data-verify-url="{{ route('account.verify') }}" data-create-url="{{ route('account.store') }}">
                            @csrf
                            <div data-register-step="phone">
                                <label class="phone-wireframe-input"><span><b>PH</b><input name="phone_num" placeholder="Enter phone number" required><i class="bi bi-person-fill"></i></span></label>
                                <button class="button button-primary">Next</button>
                            </div>
                            <div data-register-step="verify" hidden>
                                <p class="account-step-note">Enter the 6-digit verification code sent by SMS.</p>
                                <input type="hidden" name="code">
                                <div class="otp-inputs">
                                    @for ($digit = 0; $digit < 6; $digit++)<input type="text" inputmode="numeric" maxlength="1">@endfor
                                </div>
                                <button class="button button-primary">Verify</button>
                            </div>
                            <div data-register-step="password" hidden>
                                <label>First Name<span><i class="bi bi-person-fill"></i><input type="text" name="firstname" placeholder="Enter first name"></span></label>
                                <label>Last Name<span><i class="bi bi-person-fill"></i><input type="text" name="lastname" placeholder="Enter last name"></span></label>
                                <label>Username<span><i class="bi bi-person-fill"></i><input type="text" name="username" placeholder="Enter username"></span></label>
                                <label>Password<span><i class="bi bi-lock-fill"></i><input type="password" name="password" placeholder="Enter password" required><button type="button" class="password-toggle" aria-label="Show password"><i class="bi bi-eye-fill"></i></button></span></label>
                                <label>Confirm Password<span><i class="bi bi-lock-fill"></i><input type="password" name="password_confirmation" placeholder="Confirm password" required><button type="button" class="password-toggle" aria-label="Show password"><i class="bi bi-eye-fill"></i></button></span></label>
                                <button class="button button-primary">Create Account</button>
                            </div>
                        </form>
                        <p class="account-prompt">Already have an account? <a href="{{ route('public.roles', 'login') }}#login-{{ $slug }}">Log in</a></p>
                    </div>
                </section>
            @endif
        @endforeach
    </main>
    <footer class="public-footer">&copy; Copyright {{ date('Y') }}. Developed by KAJS CODERS INVADER. All Rights Reserved</footer>

    <script src="{{ asset('assets/js/sweetalert2.js') }}"></script>
    <script>
        document.addEventListener('click', (event) => {
            const toggle = event.target.closest('.password-toggle');
            if (toggle) {
                const input = toggle.closest('span')?.querySelector('input');
                if (input) input.type = input.type === 'password' ? 'text' : 'password';
            }
        });

        const closeRoleModal = () => {
            if (!location.hash) return;

            location.hash = '_';
            history.replaceState(null, '', location.pathname + location.search);
        };

        document.querySelectorAll('[data-close-role-modal]').forEach((button) => {
            button.addEventListener('click', closeRoleModal);
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') closeRoleModal();
        });

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const stepText = {
            phone: 'Register your phone number to create your account',
            verify: 'Enter the verification code sent to your phone',
            password: 'Create your username and password'
        };
        const showRegisterStep = (form, step) => {
            form.querySelectorAll('[data-register-step]').forEach((pane) => {
                const active = pane.dataset.registerStep === step;
                pane.hidden = !active;
                pane.querySelectorAll('input, select, textarea, button').forEach((field) => {
                    field.disabled = !active;
                });
            });
            const instruction = form.closest('.role-modal-card')?.querySelector('[data-register-instruction]');
            if (instruction) instruction.textContent = stepText[step] || stepText.phone;
            form.dataset.step = step;
        };
        const formMessage = (xhr) => {
            const errors = xhr?.errors || {};
            const first = Object.values(errors).flat()[0];
            return first || xhr?.message || 'Please check your entry.';
        };
        const swalPopup = (title, text, icon = 'success') => Swal.fire({
            title,
            text,
            icon,
            confirmButtonColor: icon === 'success' ? '#00620f' : '#760008',
            customClass: { popup: 'einspect-swal' }
        });
        const postRegisterForm = async (url, form, extra = {}) => {
            const data = new FormData(form);
            Object.entries(extra).forEach(([key, value]) => data.set(key, value));

            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: data
            });
            const payload = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw payload;
            }

            return payload;
        };

        document.querySelectorAll('.js-home-register-form').forEach((form) => {
            showRegisterStep(form, 'phone');

            form.querySelectorAll('.otp-inputs input').forEach((input, index, inputs) => {
                input.addEventListener('input', () => {
                    input.value = input.value.replace(/\D/g, '');
                    if (input.value && inputs[index + 1]) inputs[index + 1].focus();
                    form.querySelector('input[name="code"]').value = [...inputs].map((item) => item.value).join('');
                });
                input.addEventListener('keydown', (event) => {
                    if (event.key === 'Backspace' && !input.value && inputs[index - 1]) inputs[index - 1].focus();
                });
            });

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                const step = form.dataset.step || 'phone';
                const submit = form.querySelector(`[data-register-step="${step}"] button`);
                submit.disabled = true;

                try {
                    if (step === 'phone') {
                        const payload = await postRegisterForm(form.action, form);
                        showRegisterStep(form, 'verify');
                        const debug = payload.debug_code ? ` Local code: ${payload.debug_code}` : '';
                        await swalPopup('Verification Sent', `${payload.message}${debug}`);
                        form.querySelector('.otp-inputs input')?.focus();
                    } else if (step === 'verify') {
                        await postRegisterForm(form.dataset.verifyUrl, form);
                        showRegisterStep(form, 'password');
                        await swalPopup('Verified', 'Your phone number was verified.');
                    } else {
                        const payload = await postRegisterForm(form.dataset.createUrl, form);
                        await swalPopup('Success', payload.message);
                        if (payload.redirect) {
                            window.location.href = payload.redirect;
                        }
                    }
                } catch (xhr) {
                    await swalPopup('Please Check Your Entry', formMessage(xhr), 'error');
                } finally {
                    submit.disabled = false;
                }
            });
        });
    </script>
@endsection
