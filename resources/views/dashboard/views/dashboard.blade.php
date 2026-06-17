@extends('layout.mainlayout')
@section('content')
    @include('dashboard.css.dashboard')
    @include('staffreport.modals.incidentmodal')
    @include('staffreport.modals.situationalmodal')
    @include('staffreport.modals.progressmodal')
    @include('staffreport.modals.inventorymodal')
    @php
        $countDash = $countDash->pluck('total', 'typeOfRecord')->toArray();
        $counts = [
            [
                'title' => 'Incident Report',
                'class' => 'bg-red',
                'style' => 'background-color: #19460E',
                'route' => '',
                'id' => 'incidentreportcount',
                'count' => $countDash['INCIDENTREPORT'] ?? 0,
            ],
            [
                'title' => 'Situational Report',
                'class' => 'bg-orange',
                'style' => 'background-color: #B3420E',
                'route' => '',
                'id' => 'situationalreportcount',
                'count' => $countDash['SITUATIONALREPORT'] ?? 0,
            ],
            [
                'title' => 'Progress Report',
                'class' => 'bg-blue',
                'style' => 'background-color: #110783',
                'route' => '',
                'id' => 'progressreportcount',
                'count' => $countDash['PROGRESSREPORT'] ?? 0,
            ],
            // [
            //     'title' => 'Inventory Report',
            //     'class' => 'bg-green',
            //     'route' => '',
            //     'id' => 'inventoryreportcount',
            //     'count' => $countDash['INVENTORYREPORT'] ?? 0,
            // ],
        ];
    @endphp
    <div class="row mx-auto">
        <div class="card-body px-2 py-1">
            <div class="row align-items-center">
                <div class="col-12">
                    <div
                        class="d-flex align-items-center mb-2 flex-wrap text-lg-start text-sm-center gap-2 title-tips-class">
                        <h4 class="fw-semibold mb-0 text-nowrap" style="font-size: 21px">
                            <i class="bi bi-microsoft"></i>
                            Dashboard
                        </h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-between px-0">
            @foreach ($counts as $count)
                <div class="p-3"
                    style="width: 32%; border-radius: 13px;  {{ $count['style'] }}">
                    <p class="mb-0 text-white text-center text-capitalized fw-semibold" style="font-size: 19px">{{ $count['title'] }} | Month</p>
                    <p class="mb-0 text-white text-center fw-semibold" style="font-size: 34px">
                        <i class="bi bi-journal-text text-white" style="font-size: 28px"></i>
                        {{ $count['count'] }}
                    </p>
                </div>
            @endforeach
        </div>
        <div class="card mt-3 mb-0">
            <div class="card-body p-3">
                <p class="mb-2 fw-semibold" style="font-size: 16px">REPORT | THIS MONTH</p>
                <table id="reportTable" class="data_table table table-bordered dataTable">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Staff</th>
                            <th>Designation</th>
                            <th>Report Type</th>
                            <th>Address</th>
                            <th>Contact</th>
                            <th>File Submitted</th>
                            <th>Date & Time Submitted</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
        @if (auth()->user()->usertype == 'ADMIN')
            <div class="card mt-3">
                <div class="card-body p-3">
                    <p class="mb-2 fw-semibold" style="font-size: 16px">STATISTICS DATA CHART</p>
                    <div class="px-4">
                        <div class="d-flex justify-content-end">
                            <div class="col-2">
                                <select id="yearSelect" class="form-select">
                                    @php $currentYear = now()->year; @endphp
                                    @for ($year = $currentYear; $year >= $currentYear - 5; $year--)
                                        <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div id="incidentreportChart">

                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@section('js')
    @include('dashboard.js.report')
    @include('dashboard.js.graph')
    @include('staffreport.js.reportformupdate')
@endsection
