@extends('layout.mainlayout')
@section('content')
    @include('staffreport.css.staffreport')
    @php
        $reports = [
            [
                'title' => 'INCIDENT REPORT',
                'class' => 'bg-red',
                'route' => route('incidentreport_staff'),
                'style' => 'background-color: #19460E',
                'style1' => 'color: #19460E',
                'count' => $incidentReportCount,
            ],
            [
                'title' => 'SITUATIONAL REPORT',
                'class' => 'bg-orange',
                'route' => route('situationalreport_staff'),
                'style' => 'background-color: #B3420E',
                'style1' => 'color: #B3420E',
                'count' => $situationReportCount,
            ],
            [
                'title' => 'PROGRESS REPORT',
                'class' => 'bg-blue',
                'route' => route('progressreport_staff'),
                'style' => 'background-color: #110783',
                'style1' => 'color: #110783',
                'count' => $progressReportCount,
            ],
            // [
            //     'title' => 'INVENTORY REPORT',
            //     'class' => 'bg-green',
            //     'route' => route('inventoryreport_staff'),
            // ],
        ];
    @endphp
    <div class="row mx-auto">
        <div class="card-body px-2 py-1 mb-2">
            <div class="row align-items-center">
                <div class="col-12">
                    <div
                        class="d-flex align-items-center mb-2 flex-wrap text-lg-start text-sm-center gap-2 title-tips-class">
                        <h4 class="fw-semibold mb-0 text-nowrap">
                            <i class="bi bi-journals"></i>
                            Add Report
                        </h4>
                    </div>
                    <nav aria-label="breadcrumb" class="breadcrum-sm-class">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item">
                                <a class="text-decoration-none" href="{{ route('dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item" aria-current="page">Add Report</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-center gap-3 mt-4">
            @foreach ($reports as $report)
                <div class="d-flex flex-column justify-content-center align-items-center text-white p-4"
                    style="width: 24%; min-height: 300px; border-radius: 10px; {{ $report['style'] }}">
                    <i class="bi bi-journal-text" style="font-size: 100px"></i>
                    <p class="mb-2 fw-semibold text-center text-uppercase" style="font-size: 16px">{{ $report['title'] }}</p>
                    {{-- <div class="w-100 py-2 bg-white">
                        <p class="mb-0 fw-semibold text-center" style="font-size: 37px;{{ $report['style1'] }}">
                            {{ $report['count'] }}</p>
                    </div> --}}
                    <a href="{{ $report['route'] }}" class="btn w-100 mt-3 text-white"
                        style="border: 1px solid white">View</a>
                </div>
            @endforeach
        </div>
    </div>
@endsection

@section('js')
    @include('staffreport.js.staffreport')
@endsection
