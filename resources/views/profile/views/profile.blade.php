@extends('layout.mainlayout')
@section('content')
    <h4 class="mb-3 fw-semibold">
        <i class="text-prime bi bi-person-circle"></i>
        Profile
    </h4>
    <div class="card">
        <div class="card-body p-0">
            <div class="row mx-auto">
                <div class="col-4 border-end p-4 pb-3 bg-prime" style="border-right: 1px solid #555554;">
                    <div class="d-flex justify-content-center">
                        <div class="border rounded-circle bg-white position-relative" style="width: 150px; height: 150px">
                            <img src="{{ asset(auth()->user()->profile) }}"
                                onerror="this.onerror=null;this.src='{{ asset('assets/images/default.png') }}';"
                                alt="Profile" class="w-100 h-100 object-fit-cover rounded-circle" id="profilePreview">
                            <input type="file" id="profileInput" class="d-none" accept="image/*">

                            <button type="button" onclick="document.getElementById('profileInput').click()"
                                class="bg-white rounded-circle d-flex justify-content-center align-items-center border position-absolute"
                                style="width: 30px; height:30px; bottom: 5px; right: 10px;">
                                <i class="text-prime bi bi-camera-fill" style="font-size: 20px"></i>
                            </button>
                        </div>
                    </div>
                    <p class="mb-2 text-center mt-1 text-white">
                        {{ '@' . auth()->user()->username }}
                    </p>
                    <p class="mb-1 text-center mt-1 fw-semibold text-white text-capitalize" style="font-size: 20px">
                        {{ auth()->user()->username }}
                    </p>
                    <div class="gap-3 p-2 px-3 text-white d-flex justify-content-between align-items-center"
                        style="border-radius: 20px; background-color: #630F0F">
                        <img src="{{ asset('assets/images/logo2.png') }}" alt="" style="height: 35px; width: 35px">
                        <p class="mb-0" style="font-size: 18px">
                            {{ auth()->user()->designation }}
                        </p>
                        <img src="{{ asset('assets/images/logo1.png') }}" alt="" class="rounded-circle"
                            style="height: 35px; width: 35px">
                    </div>
                    <div class="mt-3">
                        <form id="userUpdateForm">
                            <div class="form-group bg-white rounded position-relative mb-1">
                                <i class="text-prime bi bi-person-circle position-absolute"
                                    style="left: 15px; top: 50%; transform: translateY(-50%);"></i>
                                <input type="text" name="username" class="form-control" style="text-indent: 20px"
                                    value="{{ auth()->user()->username }}">
                            </div>
                            <div class="form-group bg-white rounded position-relative mb-1">
                                <i class="text-prime bi bi-eye-slash-fill position-absolute toggle-password"
                                    style="left: 15px; top: 50%; transform: translateY(-50%);"
                                    data-target="profile_pass"></i>
                                <input type="text" name="password" id="profile_pass" class="form-control"
                                    style="text-indent: 20px" placeholder="********">
                            </div>
                            <div class="form-group bg-white rounded position-relative mb-1">
                                <i class="text-prime bi bi-house-door-fill position-absolute"
                                    style="left: 15px; top: 50%; transform: translateY(-50%);"></i>
                                <input type="text" name="address" class="form-control" style="text-indent: 20px"
                                    value="{{ auth()->user()->address }}">
                            </div>
                            <div class="form-group bg-white rounded position-relative mb-1">
                                <i class="text-prime bi bi-telephone-fill position-absolute"
                                    style="left: 15px; top: 50%; transform: translateY(-50%);"></i>
                                <input type="text" name="phone_num" class="form-control" style="text-indent: 20px"
                                    value="{{ auth()->user()->phone_num }}">
                            </div>
                            <div class="form-group bg-white rounded position-relative mb-1">
                                <i class="text-prime bi bi-envelope-fill position-absolute"
                                    style="left: 15px; top: 50%; transform: translateY(-50%);"></i>
                                <input type="text" name="email" class="form-control" style="text-indent: 20px"
                                    value="{{ auth()->user()->email }}">
                            </div>

                            <div class="d-flex justify-content-between mt-2">
                                <button type="submit" class="btn btn-gray">
                                    <i class="bi bi-pencil"></i>
                                    Save
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-8 p-4 d-flex justify-content-between flex-column">
                    @if (auth()->user()->usertype == 'ADMIN')
                        <div class="border" style="height: 480px">
                            <input type="file" id="backgroundInput" class="d-none" accept="image/*">
                            <img src="{{ asset(auth()->user()->background) }}" id="backgroundPreview"
                                onerror="this.onerror=null;this.src='{{ asset('assets/images/placeholder.png') }}';"
                                class="" style="width: 100%; height: 100%; object-fit: cover" alt="" />
                        </div>
                        <div class="d-flex justify-content-end gap-3 mt-3">
                            <button class="btn btn-prime" onclick="document.getElementById('backgroundInput').click()">
                                <i class="bi bi-arrow-bar-up"></i>
                                Upload
                            </button>
                            <button class="btn btn-prime" id="deleteCover">
                                <i class="bi bi-trash-fill"></i>
                                Delete
                            </button>
                        </div>
                    @else
                        <div class="ms-3 justify-content-center align-items-center h-100 d-flex gap-3">
                            <img src="{{ asset('assets/images/logo2.png') }}" alt=""
                                style="width: 320px; height: 320px">
                            <img src="{{ asset('assets/images/logo1.png') }}" alt=""
                                style="width: 320px; height: 320px">
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
@endsection
@section('js')
    @include('profile.js.profile')
@endsection
