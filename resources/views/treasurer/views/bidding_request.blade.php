@extends('layout.mainlayout')
@section('content')
    @include('treasurer.css.treasurercss')
    <div class="row mx-auto">
        <div class="card-body px-2 py-1">
            <div class="row align-items-center">
                <div class="col-12">
                    <div
                        class="d-flex align-items-center mb-2 flex-wrap text-lg-start text-sm-center gap-2 title-tips-class">
                        <h4 class="fw-semibold mb-0 text-nowrap">
                            <i class="bi bi-box2-fill"></i>
                            Bidding Request
                        </h4>
                    </div>
                    <nav aria-label="breadcrumb" class="breadcrum-sm-class">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item">
                                <a class="text-decoration-none" href="{{ route('dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item" aria-current="page">Bidding Request</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="card w-100 px-0 mb-0 mt-3">
            <div class="card-body p-3">
                
                <table id="biddingTable" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Name/Owner</th>
                            <th>TIN</th>
                            <th>Address</th>
                            <th>Cellphone No.</th>
                            <th>Date and Time of Request</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('js')
    @include('treasurer.js.biddingrequestjs')
@endsection
