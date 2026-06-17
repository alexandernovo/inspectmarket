@extends('layout.mainlayout')
@section('content')
    @include('staffreport.css.staffreport')
    <style>
        label,
        p {
            color: black !important;
        }
    </style>
    <div class="row mx-auto">
        <div class="card-body px-2 py-1">
            <div class="row align-items-center">
                <div class="col-12">
                    <div
                        class="d-flex align-items-center mb-2 flex-wrap text-lg-start text-sm-center gap-2 title-tips-class">
                        <h4 class="fw-semibold mb-0 text-nowrap">Submit Report | Situational</h4>
                    </div>
                    <nav aria-label="breadcrumb" class="breadcrum-sm-class">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item">
                                <a class="text-decoration-none"
                                    href="{{ route('submitreportdashboard') }}">Submit Report</a>
                            </li>
                            <li class="breadcrumb-item" aria-current="page">Submit Report | Situational</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-center">
            <div class="card px-0 mb-0 pt-2" style="width: 40vw !important;">
                <div class="d-flex justify-content-end pe-3 pt-1">
                    <a href="{{ route('submitreportdashboard') }}" type="button" class="btn btn-closing2">
                        <i class="bi bi-x-lg text-prime"></i>
                    </a>
                </div>
                <form class="reportform" enctype="multipart/form-data">
                    @include('staffreport.forms.situationalform')
                    <hr>
                    <div class="d-flex justify-content-end gap-2 p-3">
                        <button class="btn btn-success">
                            <i class="bi bi-send-fill"></i>
                            Send
                        </button>
                        <button class="btn btn-danger">
                            <i class="bi bi-ban"></i>
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('js')
    @include('staffreport.js.reportform')
@endsection
