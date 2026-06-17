@php
    $designations = [
        'Research and Planning',
        'Administration and Training',
        'Operations and Warning',
        'Team Alpha Driver',
        'Team Brave Driver',
        'Team Charlie Driver',
        'Team Leader',
        'Responder',
    ];
@endphp
@extends('layout.mainlayout')
@section('content')
    @include('home.css.home')
    @include('home.components.login')
    <div class="d-flex flex-wrap justify-content-center align-items-center gap-5 bg-home overflow-y-auto"
        style="height: calc(100vh - 122px);">
        <div class="card col-4 mt-3 p-0" style="border-radius: 14px;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-end">
                    <a href="{{ route('home') }}" type="button" class="btn btn-closing">
                        <i class="bi bi-x-lg text-dark"></i>
                    </a>
                </div>
                <div class="mt-2 mb-1">
                    <div class="d-flex justify-content-center gap-2 align-items-center mb-2">
                        <img src="{{ asset('assets/images/logo2.png') }}" class="bg-white rounded-circle" width=""
                            alt="" style="width: 78px; height: 78px" />
                        <img src="{{ asset('assets/images/logo1.png') }}" class="bg-white rounded-circle" width=""
                            alt="" style="width: 78px; height: 78px" />
                    </div>
                    <p class="mb-2 text-center fw-semibold text-dark" style="font-size: 19px;">TIBIAO MDRRMO PORTAL</p>
                    <hr class="my-1"  style="border-top: 2px solid #630F0F !important">
                </div>
                <div>
                    <p class="mb-2 mt-0 text-center fw-semibold text-dark" style="font-size: 19px;">Create an Account</p>
                    <form id="signup_form_staff">
                        <input type="hidden" name="id" id="id" value="0">
                        <input type="hidden" name="status" id="status" value="INACTIVE">
                        <div class="row">
                            <div class="col-4">
                                <div class="form-group mb-1">
                                    <label for="" class="mb-1 label-out text-dark">First Name</label>
                                    <input type="text" id="firstname" name="firstname" class="form-control input-out"
                                        required>
                                </div>
                                <div class="form-group mb-1">
                                    <label for="" class="mb-1 label-out text-dark">Username</label>
                                    <input type="text" id="username" name="username" class="form-control input-out"
                                        required>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group mb-1">
                                    <label for="" class="mb-1 label-out text-dark">Middle Name</label>
                                    <input type="text" id="middlename" name="middlename" class="form-control input-out">
                                </div>
                                <div class="form-group mb-1">
                                    <label for="" class="mb-1 label-out text-dark">Email</label>
                                    <input type="text" id="email" name="email" class="form-control input-out"
                                        required>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group mb-1">
                                    <label for="" class="mb-1 label-out text-dark">Last Name</label>
                                    <input type="text" id="lastname" name="lastname" class="form-control input-out"
                                        required>
                                </div>
                                <div class="form-group mb-1">
                                    <label for="" class="mb-1 label-out text-dark">Contact No.</label>
                                    <input type="text" id="phone_num" name="phone_num" class="form-control input-out"
                                        required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group mb-1">
                                    <label for="" class="mb-1 label-out text-dark">Address</label>
                                    <input type="text" id="address" name="address" class="form-control input-out"
                                        required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group mb-1">
                                    <label for="" class="mb-1 label-out text-dark">Designation</label>
                                    <select type="text" id="designation" name="designation"
                                        class="form-select input-out">
                                        @foreach ($designations as $designation)
                                            <option>{{ $designation }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group mb-1">
                                    <label for="" class="mb-1 label-out text-dark">Password</label>
                                    <div class="position-relative">
                                        <input type="password" name="password" id="password_var"
                                            class="form-control bg-white" placeholder="Password" required>
                                        <i class="bi bi-eye-fill position-absolute toggle-password text-dark"
                                            data-target="password_var"
                                            style="top: 50%; cursor: pointer; transform: translateY(-50%); right: 20px"></i>
                                    </div>
                                    <p class="text-danger mb-0 d-none" id="errorPassword" style="font-size: 12px">
                                        Password do not match!
                                    </p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group mb-1">
                                    <label for="" class="mb-1 label-out text-dark">Confirm Password</label>
                                    <div class="position-relative">
                                        <input type="password" id="confirm_password" class="form-control bg-white"
                                            placeholder="Password" required>
                                        <i class="bi bi-eye-fill position-absolute toggle-password text-dark"
                                            data-target="confirm_password"
                                            style="top: 50%; cursor: pointer; transform: translateY(-50%); right: 20px"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mt-3">
                                <button type="submit" class="btn btn-prime w-100"
                                    style="background-color: #630F0F !important">Sign up</button>
                                <p class="mb-0 text-center mt-2 text-dark" style="font-size: 12px">
                                    Already have an account?
                                    <a href="{{ route('login') }}" class="text-dark">Log in Here</a>
                                </p>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    @include('home.js.login')
    @include('home.js.signup')
@endsection
