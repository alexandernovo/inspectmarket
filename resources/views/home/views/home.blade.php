@extends('layout.mainlayout')
@section('content')
    @include('home.css.home')
    @include('home.components.login')
    <div class="d-flex flex-nowrap justify-content-center align-items-center gap-5 bg-home"
        style="height: calc(100vh - 122px);">
        <div class="text-start text-white">
            <p class="mb-3" style="font-size: 25px">Welcome to</p>
            <p class="mb-1" style="font-size: 51px; font-weight: bold">TIBIAO MDRRMO Portal</p>
            <p class="mb-2 mt-3" style="font-size: 25px;">
                Prepare Today, Protected Tommorow - Municipal<br>Disaster Risk Reduction and Management Office<br>
                (MDRRMO) at the Frontline of Safety and Resilience
            </p>
            <div class="d-flex gap-2 mt-5">
                <a href="{{ route('login') }}" class="btn-prime btn px-4" style="font-size: 18px; border-radius: 13px">Log in</a>
                <a href="{{ route('signup') }}" class="btn-prime btn px-4" style="font-size: 18px; border-radius: 13px">Sign up</a>
            </div>
        </div>
        <div class="ms-3 d-flex gap-3">
            <img src="{{ asset('assets/images/logo2.png') }}" alt="" style="width: 290px; height: 290px">
            <img src="{{ asset('assets/images/logo1.png') }}" alt="" style="width: 290px; height: 290px">
        </div>
    </div>
@endsection

@section('js')
    @include('home.js.login')
@endsection
