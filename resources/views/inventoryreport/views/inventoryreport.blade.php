@extends('layout.mainlayout')
@section('content')
    @include('inventoryreport.css.inventoryreport')
    @include('staffreport.modals.inventorymodal')

    <div class="row mx-auto">
        <div class="card-body px-2 py-1">
            <div class="row align-items-center">
                <div class="col-12">
                    <div
                        class="d-flex align-items-center mb-2 flex-wrap text-lg-start text-sm-center gap-2 title-tips-class">
                        <h4 class="fw-semibold mb-0 text-nowrap">
                            <i class="bi bi-box2-fill"></i>
                            Inventory | Equipment
                        </h4>
                    </div>
                    <nav aria-label="breadcrumb" class="breadcrum-sm-class">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item">
                                <a class="text-decoration-none" href="{{ route('dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item" aria-current="page">Inventory Report</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-end gap-2 mb-2 px-0">
            <a href="{{ route('inventoryreportPrint') }}" class="btn btn-red fw-semibold">
                <i class="bi bi-journals"></i>
                Report
            </a>
            <button class="btn btn-red openNewReport fw-semibold" data-type="inventory" data-table="inventory">
                <i class="bi bi-plus-circle"></i>
                Add Item
            </button>
            {{-- <a href="{{ route('inventoryreport_staff') }}" class="btn btn-prime">
                <i class="bi bi-plus-circle"></i>
                Add Item
            </a> --}}
        </div>
        <div class="card w-100 px-0 mb-0">
            <div class="card-body p-3">
                <table id="inventoryreportTable" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Description</th>
                            <th>Unit</th>
                            <th>Property No.</th>
                            <th>Date Acquired</th>
                            <th>Remarks</th>
                            <th>Amount</th>
                            {{-- <th>Quantity</th> --}}
                            {{-- <th>Date Submitted</th> --}}
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('js')
    @include('inventoryreport.js.inventoryreport')
    @include('staffreport.js.reportformupdate')
@endsection
