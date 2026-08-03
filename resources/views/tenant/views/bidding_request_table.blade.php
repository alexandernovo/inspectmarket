@extends('layout.mainlayout')
@section('content')
    @include('tenant.css.tenantcss')
    <div class="row mx-auto">
        <div class="card-body px-2 py-1">
            <div class="row align-items-center">
                <div class="col-12">
                    <div
                        class="d-flex align-items-center mb-2 flex-wrap text-lg-start text-sm-center gap-2 title-tips-class">
                        <h4 class="fw-semibold mb-0 text-nowrap">
                            <i class="bi bi-box2-fill"></i>
                            Bidding Application
                        </h4>
                    </div>
                    <nav aria-label="breadcrumb" class="breadcrum-sm-class">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item">
                                <a class="text-decoration-none" href="{{ route('dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item" aria-current="page">Bidding Application</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>


        <div class="card bidding-card mt-3 p-2 shadow-sm border-0 rounded-4 w-100 mx-0" style="max-width: unset !important;">
            <div class="card-body p-3">
                <p class="mb-2" style="font-size: 16px">BIDDING REQUEST</p>
                <table class="table table-bordered align-middle w-100" id="biddingTable">
                    <thead>
                        <tr>
                            <th width="5%">No.</th>
                            <th class="text-center">Name/Owner</th>
                            <th width="20%">Date of Request</th>
                            <th width="15%">Action</th>
                        </tr>
                    </thead>
                </table>

            </div>
        </div>

    </div>
@endsection

@section('js')
    @include('tenant.js.bidding_application_table')
@endsection
