@extends('layout.mainlayout')
@section('content')
    @include('progressreport.css.progressreport')
    @include('staffreport.modals.progressmodal')
    @include('components.remarks')

    <div class="row mx-auto">
        <div class="card-body px-2 py-1">
            <div class="row align-items-center">
                <div class="col-12">
                    <div
                        class="d-flex align-items-center mb-2 flex-wrap text-lg-start text-sm-center gap-2 title-tips-class">
                        <h4 class="fw-semibold mb-0 text-nowrap">
                            <i class="bi bi-journals"></i>
                            Progress Report
                        </h4>
                    </div>
                    <nav aria-label="breadcrumb" class="breadcrum-sm-class">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item">
                                <a class="text-decoration-none" href="{{ route('dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item" aria-current="page">Progress Report</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        @if (auth()->user() && auth()->user()->usertype == 'STAFF')
            <div class="d-flex justify-content-end mb-2 px-0">
                <button class="btn btn-red fw-semibold openNewReport" data-type="progress" data-table="progress">
                    <i class="bi bi-plus-circle"></i>
                    Add Report
                </button>
            </div>
        @endif
        <div class="card w-100 px-0 mb-0">
            <div class="card-body p-3">
                <table id="progressreportTable" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Staff</th>
                            <th>Designation</th>
                            <th>Barangay</th>
                            <th>Affected Families</th>
                            <th>Person/Individuals</th>
                            <th>Evacuation Center/Outside</th>
                            <th>Remarks</th>
                            <th>Clearing Operations</th>
                            <th>File Submitted</th>
                            <th>Date Submitted</th>
                            <th>Remarks</th>
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
    @include('progressreport.js.progressreport')
    @include('staffreport.js.reportformupdate')
@endsection
