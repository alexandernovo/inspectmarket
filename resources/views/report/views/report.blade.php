@extends('layout.mainlayout')
@section('content')
    @php
        $reports = [
            [
                'title' => 'INCIDENT REPORT',
                'class' => 'bg-red',
                'route' => route('incidentreportPrint'),
                'style' => 'background-color: #19460E',
            ],
            [
                'title' => 'SITUATIONAL REPORT',
                'class' => 'bg-orange',
                'route' => route('situationalreportPrint'),
                'style' => 'background-color: #B3420E',
            ],
            [
                'title' => 'PROGRESS REPORT',
                'class' => 'bg-blue',
                'route' => route('progressreportPrint'),
                'style' => 'background-color: #110783',
            ],
            // [
            //     'title' => 'INVENTORY RECORD REPORT',
            //     'class' => 'bg-green',
            //     'route' => route('inventoryreportPrint'),
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
                            <i class="bi bi-folder-fill"></i>
                            Monthly Report
                        </h4>
                    </div>
                    <nav aria-label="breadcrumb" class="breadcrum-sm-class">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item">
                                <a class="text-decoration-none" href="{{ route('dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item" aria-current="page">Monthly Report</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-center gap-4 mt-4">
            @foreach ($reports as $report)
                <div class="d-flex flex-column justify-content-center align-items-center text-white p-4"
                    style="width: 23%; min-height: 300px; border-radius: 10px; {{ $report['style'] }}">
                    <i class="bi bi-journal-text" style="font-size: 100px"></i>
                    <p class="mb-2 fw-semibold text-center" style="font-size: 16px">{{ $report['title'] }}</p>
                    <a href="{{ $report['route'] }}" class="btn w-100 mt-3 text-white"
                        style="border: 1px solid white">View</a>
                </div>
            @endforeach
        </div>
    </div>
@endsection
