@extends('layout.mainlayout')
@section('content')
    @include('home.css.home')
    @include('home.components.login')
    <div class="d-flex flex-wrap justify-content-center align-items-center gap-5 bg-home"
        style="height: calc(100vh - 122px);">
        <div class="card glass-card" style="border-radius: 14px; width: 63vh">
            <div class="card-body">
                <div class="d-flex justify-content-end">
                    <a href="{{ route('home') }}" type="button" class="btn btn-closing">
                        <i class="bi bi-x-lg text-white"></i>
                    </a>
                </div>
                <div class="mt-3 mb-2">
                    <div class="d-flex justify-content-center gap-2 align-items-center mb-2">
                        <img src="{{ asset('assets/images/logo2.png') }}" class="bg-white rounded-circle" width=""
                            alt="" style="width: 110px; height: 110px" />
                    </div>
                    <p class="mb-2 text-center fw-semibold text-white" style="font-size: 22px;">PANDAN MARKET PORTAL</p>
                    <hr class="border-top border-white my-2">

                </div>
                <div>
                    <p class="mb-0 text-center text-white" style="font-size: 20px;">Log in to your Account</p>
                    <p class="mb-1 text-center text-white" style="font-size: 16px;">Enter your username and password to
                        login your<br>account</p>
                    {{-- <p class="text-center fw-semibold text-white" style="font-size: 15px;">Enter your username and password
                        to log in
                    </p> --}}
                    <form id="login_form_staff">
                        <input type="hidden"name="typeLogin" id="typeLogin" value="STAFF">
                        <div class="form-group mb-2">
                            <label class="mb-1 label-out">Username</label>
                            <div class="input-group">
                                <span class="input-group-text custom-icon-box">
                                    <i class="bi bi-person-circle" style="font-size: 17px; color: #905004"></i>
                                </span>
                                <input type="text" id="username" name="username" class="form-control input-out"
                                    placeholder="Username"
                                    style="border-top-right-radius: 0.36rem !important; border-bottom-right-radius: 0.36rem !important; border: 1px solid #905004 !important">
                            </div>
                        </div>

                        <div class="form-group mb-2">
                            <label class="mb-1 label-out">Password</label>
                            <div class="input-group">
                                <span class="input-group-text custom-icon-box">
                                    <i class="bi bi-lock-fill" style="font-size: 17px; color: #905004"></i>
                                </span>
                                <input type="password" name="password" id="password_staff" class="form-control input-out"
                                    style="border-top-right-radius: 0.36rem !important; border-bottom-right-radius: 0.36rem !important; border: 1px solid #905004 !important"
                                    placeholder="Password">

                                <!-- Eye icon WITHOUT background -->
                                <i class="bi bi-eye-fill toggle-password position-absolute" data-target="password_staff"
                                    style="right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer;"></i>
                            </div>
                            <p id="error_login_staff" class="text-danger mt-1 d-none mb-0 error-class"></p>
                        </div>
                        <a href="{{ route('forgot.password') }}" style="font-size: 11px" class="text-white">Forgot
                            Password?</a>
                        <div class="form-group mt-3">
                            <button type="submit" class="btn btn-prime2 w-100" style="height: 45px">Log in</button>
                            {{-- <p class="mb-0 text-center mt-2 text-white" style="font-size: 12px">No Account? <a
                                    href="{{ route('signup') }}" class="text-white">Sign up Here</a></p> --}}
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    @include('home.js.login')
@endsection
