@extends('layout.mainlayout')
@section('content')
    @include('tenant.css.tenantcss')
    @if (request()->query('application_id'))
        <style>
            input {
                pointer-events: none;
            }
        </style>
    @endif
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

        <div class="card border rounded-4 shadow-sm bidding-card" style="max-width: 700px !important">
            <form id="biddingForm">

                <input type="hidden" name="id" value="{{ $application->id ?? '' }}">

                <div class="card-body p-4">

                    <div class="d-flex justify-content-center gap-3 align-items-center position-relative">
                        <img src="{{ asset('assets/images/logo2.png') }}" class="bg-white rounded-circle"
                            style="width:84px;height:84px; position:absolute; left:120px;" alt="">

                        <div>
                            <p class="mb-1 text-center fw-semibold" style="font-size:16px;">
                                Republic of the Philippines
                            </p>

                            <p class="mb-1 text-center fw-semibold" style="font-size:16px;">
                                Province of Antique
                            </p>

                            <p class="mb-1 text-center fw-semibold" style="font-size:16px;">
                                Municipality of Pandan
                            </p>
                        </div>
                    </div>

                    <hr class="border-top border-dark my-3">

                    <p class="text-center fw-semibold mb-3" style="font-size:20px;">
                        OFFICE OF THE MUNICIPAL TREASURER APPLICATION FORM
                    </p>

                    <!-- Name Owner and TIN -->
                    <div class="row mb-2 align-items-center">
                        <div class="col-md-4">
                            <label>Name/Owner:</label>
                        </div>

                        <div class="col-md-3">
                            <input type="text" class="form-control custom-input" id="name_owner" name="name_owner"
                                value="{{ $application->name_owner ?? '' }}">
                        </div>

                        <div class="col-md-1 text-end">
                            <label>TIN:</label>
                        </div>

                        <div class="col-md-4">
                            <input type="text" class="form-control custom-input" id="tin" name="tin"
                                value="{{ $application->tin ?? '' }}">
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="row mb-2 align-items-center">
                        <div class="col-md-4">
                            <label>Address:</label>
                        </div>

                        <div class="col-md-8">
                            <input type="text" class="form-control custom-input" id="address" name="address"
                                value="{{ $application->address ?? '' }}">
                        </div>
                    </div>

                    <!-- Cellphone -->
                    <div class="row mb-2 align-items-center">
                        <div class="col-md-4">
                            <label>Cellphone No:</label>
                        </div>

                        <div class="col-md-8">
                            <input type="text" class="form-control custom-input" id="cellphone_no" name="cellphone_no"
                                value="{{ $application->cellphone_no ?? '' }}">
                        </div>
                    </div>

                    <!-- Business Type -->
                    <div class="row mb-2 align-items-center">
                        <div class="col-md-4">
                            <label>Type of Business:</label>
                        </div>

                        <div class="col-md-8">
                            <input type="text" class="form-control custom-input" id="business_type" name="business_type"
                                value="{{ $application->business_type ?? '' }}">
                        </div>
                    </div>

                    <!-- Nature -->
                    <div class="row mb-2 align-items-center">
                        <div class="col-md-4">
                            <label>Nature of Business:</label>
                        </div>

                        <div class="col-md-8">
                            <input type="text" class="form-control custom-input" id="nature_business"
                                name="nature_business" value="{{ $application->nature_business ?? '' }}">
                        </div>
                    </div>

                    <!-- Category -->
                    <div class="row mb-2 align-items-center">
                        <div class="col-md-4">
                            <label>Category:</label>
                        </div>

                        <div class="col-md-8">
                            <input type="text" class="form-control custom-input" id="category" name="category"
                                value="{{ $application->category ?? '' }}">
                        </div>
                    </div>

                    <!-- Trade Name -->
                    <div class="row mb-2 align-items-center">
                        <div class="col-md-4">
                            <label>Business Trade Name:</label>
                        </div>

                        <div class="col-md-8">
                            <input type="text" class="form-control custom-input" id="business_trade_name"
                                name="business_trade_name" value="{{ $application->business_trade_name ?? '' }}">
                        </div>
                    </div>

                    <!-- Stall Applied -->
                    <div class="row mb-2 align-items-center">
                        <div class="col-md-4">
                            <label>Stall # Applied:</label>
                        </div>

                        <div class="col-md-8">
                            <input type="text" class="form-control custom-input" id="stall_applied"
                                name="stall_applied" value="{{ $application->stall_applied ?? '' }}">
                        </div>
                    </div>

                    <!-- Alternative Stall -->
                    <div class="row mb-2 align-items-center">
                        <div class="col-md-4">
                            <label>Alternative Stall #:</label>
                        </div>

                        <div class="col-md-8">
                            <input type="text" class="form-control custom-input" id="alternative_stall"
                                name="alternative_stall" value="{{ $application->alternative_stall ?? '' }}">
                        </div>
                    </div>

                    <!-- Other Business -->
                    <div class="row mb-4 align-items-center">
                        <div class="col-md-4">
                            <label>Other Business:</label>
                        </div>

                        <div class="col-md-8">
                            <input type="text" class="form-control custom-input" id="other_business"
                                name="other_business" value="{{ $application->other_business ?? '' }}">
                        </div>
                    </div>

                    <div class="row mt-5">

                        <div class="col-md-6">

                            <div class="bottom-field mb-2">
                                <span>Date Filed:</span>
                                <p class="line-text mb-0">
                                    {{ $application->date_filed ?? '' }}
                                </p>
                            </div>

                            <div class="bottom-field mb-2">
                                <span>Received by:</span>
                                <p class="line-text mb-0">
                                    {{ $application->received_by ?? '' }}
                                </p>
                            </div>

                            <div class="bottom-field">
                                <span>Remarks:</span>
                                <p class="line-text mb-0">
                                    {{ $application->remarks ?? '' }}
                                </p>
                            </div>

                        </div>

                        <div class="col-md-6 text-center align-self-end">

                            <p class="signature-name mb-0">
                                {{ $application->signature_name ?? 'ROMEL V. DE JUAN' }}
                            </p>

                            <div class="signature-line"></div>

                            <p class="signature-label mb-0">
                                Signature over Printed Name
                            </p>

                        </div>

                    </div>
                    @if (!request()->query('application_id'))
                        <hr class="border-top border-dark my-3">

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-warning px-4">
                                Send Request
                            </button>

                            <button type="button" class="btn btn-danger px-4 ms-2">
                                Close
                            </button>
                        </div>
                    @endif
                </div>
            </form>
        </div>
    </div>
@endsection

@section('js')
    @include('tenant.js.bidding_application')
@endsection
