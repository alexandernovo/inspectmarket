@extends('layout.mainlayout')
@section('content')
    @include('treasurer.css.treasurercss')
    @include('treasurer.modals.addrentalmodal')
    <div class="row mx-auto">
        <div class="card-body px-2 py-1">
            <div class="row align-items-center">
                <div class="d-flex justify-content-between align-items-end">
                    <div>
                        <div
                            class="d-flex align-items-center mb-2 flex-wrap text-lg-start text-sm-center gap-2 title-tips-class">
                            <h4 class="fw-semibold mb-0 text-nowrap">
                                <i class="bi bi-shop"></i>
                                Stall Rental
                            </h4>
                        </div>
                        <nav aria-label="breadcrumb" class="breadcrum-sm-class">
                            <ol class="breadcrumb mb-1">
                                <li class="breadcrumb-item">
                                    <a class="text-decoration-none" href="{{ route('dashboard') }}">Dashboard</a>
                                </li>
                                <li class="breadcrumb-item" aria-current="page">Stall Rental</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mb-0 px-0">
                        <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#addRental"><i
                                class="bi bi-plus-circle"></i> Add</button>
                        <button class="btn btn-success"><i class="bi bi-shop"></i> Stall Rental</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card w-100 px-0 mb-0 mt-3">
            <div class="card-body p-2">
                <div class="card stall-card">
                    <div class="card-body">

                        <div class="row">

                            <!-- Fish -->
                            <div class="col-lg-6 section-block">

                                <div class="section-title">
                                    <img src="{{ asset('assets/images/fish.png') }}">
                                    <span>FISH SECTION</span>
                                </div>

                                <div class="section-line"></div>

                                <div class="stall-container">
                                    @foreach ($fishsection as $f)
                                        <button class="stall {{ $f->status }}">{{ $f->stall_no }}</button>
                                    @endforeach
                                </div>
                            </div>


                            <!-- Pork -->
                            <div class="col-lg-6 section-block">

                                <div class="section-title">
                                    <img src="{{ asset('assets/images/pork.png') }}">
                                    <span>PORK SECTION</span>
                                </div>

                                <div class="section-line"></div>

                                <div class="stall-container">

                                    @foreach ($porksection as $p)
                                        <button class="stall {{ $p->status }}">{{ $p->stall_no }}</button>
                                    @endforeach
                                </div>

                            </div>

                        </div>

                        <div class="row">

                            <!-- Fish -->
                            <div class="col-lg-6 section-block">

                                <div class="section-title">
                                    <img src="{{ asset('assets/images/fish.png') }}">
                                    <span>POULTRY SECTION</span>
                                </div>

                                <div class="section-line"></div>

                                <div class="stall-container">
                                    @foreach ($poultrysection as $p)
                                        <button class="stall {{ $p->status }}">{{ $p->stall_no }}</button>
                                    @endforeach
                                </div>
                            </div>


                            <!-- Pork -->
                            <div class="col-lg-6 section-block">

                                <div class="section-title">
                                    <img src="{{ asset('assets/images/pork.png') }}">
                                    <span>BEEF SECTION</span>
                                </div>

                                <div class="section-line"></div>

                                <div class="stall-container">

                                    @foreach ($beefsection as $b)
                                        <button class="stall {{ $b->status }}">{{ $b->stall_no }}</button>
                                    @endforeach
                                </div>

                            </div>

                        </div>
                        <!-- Mixed Section -->

                        <div class="section-block mt-4">

                            <div class="section-title text-center">
                                <img src="{{ asset('assets/images/mixed.png') }}">
                                <span>MIXED SECTION</span>
                            </div>

                            <div class="section-line"></div>

                            <div class="stall-container">

                                @foreach ($mixedsection as $m)
                                    <button class="stall {{ $m->status }}">{{ $m->stall_no }}</button>
                                @endforeach

                            </div>

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    @include('treasurer.js.addingstalljs')
@endsection
