@extends('layout.mainlayout')

@section('content')
    @include('home.css.home')

    <div class="d-flex flex-wrap justify-content-center align-items-center gap-5 bg-home"
        style="height: calc(100vh - 122px);">
        <div class="card glass-box" style="border-radius: 14px; width: 56vh">
            <div class="card-body">

                {{-- CLOSE BUTTON --}}
                <div class="d-flex justify-content-end">
                    <a href="{{ route('home') }}" type="button" class="btn btn-closing">
                        <i class="bi bi-x-lg text-white"></i>
                    </a>
                </div>

                {{-- LOGO + TITLE --}}
                <div class="mt-3 mb-4 text-center">
                    <div class="d-flex justify-content-center gap-2 align-items-center mb-2">
                        <img src="{{ asset('assets/images/logo2.png') }}" class="bg-white rounded-circle"
                            style="width: 78px; height: 78px" />
                        <img src="{{ asset('assets/images/logo1.png') }}" class="bg-white rounded-circle"
                            style="width: 78px; height: 78px" />
                    </div>
                    <p class="mb-3 fw-semibold text-white" style="font-size: 22px;">TIBIAO MDRRMO PORTAL</p>
                    <hr>
                </div>

                {{-- STEP 1: Enter Email --}}
                <div id="step1">
                    <p class="mb-3 text-center fw-semibold text-white" style="font-size: 22px;">Forgot Password</p>

                    <form id="forgotPasswordForm">
                        @csrf
                        <div class="form-group mb-3">
                            <label class="mb-1 text-white">Email Address</label>
                            <div class="position-relative">
                                <i class="bi bi-envelope-fill position-absolute text-prime"
                                    style="top: 50%; transform: translateY(-50%); left: 15px"></i>
                                <input type="email" name="email" id="email" class="form-control input-out"
                                    placeholder="Enter your email" style="text-indent: 20px" required>
                            </div>
                        </div>

                        <div id="fpAlert" class="alert d-none p-2"></div>

                        <button type="submit" class="btn btn-prime w-100" id="btnSendMail"
                            style="background-color: #630F0F !important">Send Reset Link</button>
                    </form>
                </div>

                {{-- STEP 2: Enter Verification Code --}}
                <div id="step2" class="d-none">
                    <p class="mb-3 text-center fw-semibold text-white" style="font-size: 22px;">Verification Code</p>
                    <p class="text-center text-white" style="font-size:14px;">A 6-digit code was sent to your email.</p>

                    <div class="form-group mb-3">
                        <label class="mb-1 text-white">Enter Code</label>
                        <input type="text" id="verifyCode" class="form-control input-out" placeholder="123456">
                    </div>

                    <div id="codeAlert" class="alert d-none p-2"></div>

                    <button class="btn btn-gray-new2 w-100 mb-2" id="backToStep1">Back</button>
                    <button class="btn btn-prime w-100" id="verifyCodeBtn"
                        style="background-color: #630F0F !important">Verify</button>
                </div>

                {{-- STEP 3: Reset Password --}}
                <div id="step3" class="d-none">
                    <p class="mb-3 text-center fw-semibold text-white" style="font-size: 22px;">Reset Password</p>

                    <div class="form-group mb-2">
                        <label class="mb-1 text-white">New Password</label>
                        <input type="password" id="newPassword" class="form-control input-out"
                            placeholder="Enter new password">
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-1 text-white">Confirm Password</label>
                        <input type="password" id="confirmPassword" class="form-control input-out"
                            placeholder="Confirm password">
                    </div>

                    <div id="resetAlert" class="alert d-none py-2 px-4"></div>

                    <button class="btn btn-gray-new2 w-100 mb-2" id="backToStep2">Back</button>
                    <button class="btn btn-prime w-100" id="finishReset">Reset Password</button>
                </div>

                {{-- STEP 4: Success --}}
                <div id="step4" class="d-none text-center">
                    <p class="mb-3 fw-semibold text-white" style="font-size: 22px;">Success!</p>
                    <p class="text-white">Your password has been reset.</p>
                    <a href="{{ route('login') }}" class="btn btn-prime w-100"
                        style="background-color: #630F0F !important">Go to Login</a>
                </div>

            </div>
        </div>
    </div>
@endsection


@section('js')
    @include('forgotpassword.js.forgotpasswordjs')
@endsection
