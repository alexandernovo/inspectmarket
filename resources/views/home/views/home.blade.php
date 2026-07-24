@extends('layout.mainlayout')
@section('content')
    @include('home.css.home')
    @include('home.components.login')
    <div class="d-flex flex-nowrap justify-content-center align-items-center  bg-home"
        style="height: calc(100vh - 122px); gap: 100px">
        <div class="text-start text-white">
            <p class="mb-3" style="font-size: 25px">Welcome to</p>
            <p class="mb-1" style="font-size: 60px; font-weight: bold">E-INSPECT:</p>
            <p class="mb-2 mt-3" style="font-size: 25px;">
                Public Market Inspection Recording Management <br> System in Centro Norte, Panda Antique
            </p>
            <p class="mb-2 mt-4" style="font-size: 17px;">
                "An organized public market commited to food safety, fair trade, and<br>and sustainable economic development within the community."
            </p>
            <div class="d-flex gap-2 mt-5">
                <a href="{{ route('login') }}" class="btn-prime2 btn px-4" style="font-size: 18px; border-radius: 13px">Log
                    in</a>
                <a href="" class="btn-prime2 btn px-4" style="font-size: 18px; border-radius: 13px">Sign
                    up</a>
            </div>
        </div>
        <div class="ms-3 d-flex gap-3">
            <img src="{{ asset('assets/images/logo2.png') }}" alt="" style="width: 490px; height: 490px">
        </div>
    </div>
@endsection

@section('js')
    @include('home.js.login')
@endsection
