@extends('layout.mainlayout')
@section('content')
    @include('home.css.home')
    @include('home.components.login')
    <div class="d-flex flex-wrap justify-content-center align-items-center gap-5 bg-home"
        style="height: calc(100vh - 122px);">
        <div class="card col-6 text-dark" style="border-radius: 14px;">
            <div class="card-body p-0">

                <div class="row mx-auto">
                    <div class="col-6 p-4" style="border-right: 1px solid lightgray">
                        <p class="mb-0 text-center fw-semibold" style="font-size: 25px">Let's Get in Touch!</p>

                        <div class="ms-3 d-flex gap-3 justify-content-center mt-5">
                            <img src="{{ asset('assets/images/logo2.png') }}" alt=""
                                style="width: 150px; height: 150px">
                            <img src="{{ asset('assets/images/logo1.png') }}" alt=""
                                style="width: 150px; height: 150px">
                        </div>

                        <p class="mb-0 text-center fw-semibold mt-5" style="font-size: 18px">Connect with Us:</p>

                        <a href="https://www.facebook.com/mdrrmc.tibiao.7" target="_blank" class="d-flex justify-content-center mt-3">
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                height="60px" width="60px" version="1.1" id="Capa_1" viewBox="0 0 112.196 112.196"
                                xml:space="preserve">
                                <g>
                                    <circle style="fill:#3B5998;" cx="56.098" cy="56.098" r="56.098" />
                                    <path style="fill:#FFFFFF;"
                                        d="M70.201,58.294h-10.01v36.672H45.025V58.294h-7.213V45.406h7.213v-8.34   c0-5.964,2.833-15.303,15.301-15.303L71.56,21.81v12.51h-8.151c-1.337,0-3.217,0.668-3.217,3.513v7.585h11.334L70.201,58.294z" />
                                </g>
                            </svg>
                        </a>
                    </div>
                    <div class="col-6 p-4">

                        <div class="d-flex justify-content-between">
                            <p class="mb-0 text-start fw-semibold" style="font-size: 25px">Contact Us</p>

                            <a href="{{ route('home') }}" type="button" class="btn btn-closing">
                                <i class="bi bi-x-lg text-dark"></i>
                            </a>
                        </div>
                        <form id="contactForm">
                            <div class="form-group mb-2 mt-4">
                                <label for="" class="mb-1">Name</label>
                                <input type="text" name="name" class="form-control input-out" required>
                            </div>
                            <div class="form-group mb-2">
                                <label for="" class="mb-1">Address</label>
                                <input type="text" name="address" class="form-control input-out" required>
                            </div>
                            <div class="form-group mb-2">
                                <label for="" class="mb-1">Contact</label>
                                <input type="text" name="contact" class="form-control input-out" required>
                            </div>
                            <div class="form-group mb-2">
                                <label for="" class="mb-1">Message</label>
                                <textarea name="message" rows="4" class="form-control input-out" required></textarea>
                            </div>
                            <div class="form-group mt-1 d-flex justify-content-end">
                                <button type="submit" class="btn btn-prime">
                                    <i class="bi bi-send-fill me-1"></i>
                                    Send
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    @include('home.js.login')
    @include('home.js.contact')
@endsection
