@extends('layout.mainlayout')
@section('content')
    @include('home.css.home')
    @include('home.components.login')
    <div class="d-flex flex-wrap justify-content-center align-items-center gap-5 bg-home"
        style="height: calc(100vh - 122px);">
        <div class="card " style="border-radius: 14px; width: 56vh">
            <div class="card-body">
                <div class="d-flex justify-content-end">
                    <a href="{{ route('home') }}" type="button" class="btn btn-closing">
                        <i class="bi bi-x-lg text-dark"></i>
                    </a>
                </div>
                <div class="mt-3 mb-2">
                    <div class="d-flex justify-content-center gap-2 align-items-center mb-2">
                        <img src="{{ asset('assets/images/logo2.png') }}" class="bg-white rounded-circle" width=""
                            alt="" style="width: 78px; height: 78px" />
                        <img src="{{ asset('assets/images/logo1.png') }}" class="bg-white rounded-circle" width=""
                            alt="" style="width: 78px; height: 78px" />
                    </div>
                    <p class="mb-2 text-center fw-semibold text-dark" style="font-size: 22px;">TIBIAO MDRRMO PORTAL</p>
                    <hr class="my-1" style="border-top: 2px solid #630F0F !important">
                </div>
                <div>
                    <p class="mb-0 text-center text-dark" style="font-size: 18px;">Log in to your Account</p>
                    <p class="mb-1 text-center text-dark" style="font-size: 16px;">Enter your username and password to login</p>
                    {{-- <p class="text-center fw-semibold text-dark" style="font-size: 15px;">Enter your username and password
                        to log in
                    </p> --}}
                    <form id="login_form_staff">
                        <input type="hidden"name="typeLogin" id="typeLogin" value="STAFF">
                        <div class="form-group mb-2">
                            <label class="mb-1 label-out">Username</label>
                            <div class="input-group">
                                <span class="input-group-text custom-icon-box">
                                    <i class="bi bi-person-circle text-white" style="font-size: 17px"></i>
                                </span>
                                <input type="text" id="username" name="username" class="form-control input-out"
                                    placeholder="Username">
                            </div>
                        </div>

                        <div class="form-group mb-2">
                            <label class="mb-1 label-out">Password</label>
                            <div class="input-group">
                                <span class="input-group-text custom-icon-box">
                                    <i class="bi bi-lock-fill text-white" style="font-size: 17px"></i>
                                </span>
                                <input type="password" name="password" id="password_staff" class="form-control input-out"  style="border-top-right-radius: 0.36rem !important; border-bottom-right-radius: 0.36rem !important"
                                    placeholder="Password">

                                <!-- Eye icon WITHOUT background -->
                                <i class="bi bi-eye-fill toggle-password position-absolute" data-target="password_staff"
                                    style="right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer;"></i>
                            </div>
                            <p id="error_login_staff" class="text-danger mt-1 d-none mb-0 error-class"></p>
                        </div>
                        <a href="{{ route('forgot.password') }}" style="font-size: 11px" class="text-dark">Forgot
                            Password?</a>
                        <div class="form-group mt-3">
                            <button type="submit" class="btn btn-prime w-100"
                                style="background-color: #630F0F !important">Log in</button>
                            <p class="mb-0 text-center mt-2 text-dark" style="font-size: 12px">No Account? <a
                                    href="{{ route('signup') }}" class="text-dark">Sign up Here</a></p>
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
