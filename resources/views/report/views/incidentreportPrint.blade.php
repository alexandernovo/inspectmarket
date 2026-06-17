@extends('layout.mainlayout')
@section('content')
    <div class="row mx-auto">
        <div class="card-body px-2 py-1">
            <div class="row align-items-center">
                <div class="col-12">
                    <div
                        class="d-flex align-items-center mb-2 flex-wrap text-lg-start text-sm-center gap-2 title-tips-class">
                        <h4 class="fw-semibold mb-0 text-nowrap">
                            <i class="bi bi-journals"></i>
                            Monthly Report | Incident
                        </h4>
                    </div>
                    <nav aria-label="breadcrumb" class="breadcrum-sm-class">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item">
                                <a class="text-decoration-none" href="{{ route('dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item" aria-current="page">Monthly Report | Incident</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body p-2">
                <div class="d-flex justify-content-between align-items-end">
                    <form method="GET" class=" w-25 mb-0">
                        <div class="form-group">
                            <label for="" class="mb-1">Select Month and Year</label>
                            <input type="month" class="form-control" name="monthyear"
                                value="{{ request('monthyear', date('Y-m')) }}" onchange="this.form.submit()">
                        </div>
                    </form>
                    <div class="d-flex gap-2 align-items-center">
                        <button class="btn btn-gray" id="printBtn">
                            <i class="bi bi-printer"></i>
                            Print Report
                        </button>
                        <i class="bi bi-file-earmark-pdf-fill text-danger download-btn pdf-button" data-type="pdf"></i>
                        <i class="bi bi-file-earmark-word-fill text-primary download-btn word-button" data-type="word"></i>
                        <i class="bi bi-file-earmark-excel-fill text-success download-btn excel-button"
                            data-type="excel"></i>
                    </div>
                </div>
                <hr class="my-2">
                <div id="printable">
                    <div class="d-flex justify-content-center gap-3 align-items-center">
                        <img src="{{ asset('assets/images/logo2.png') }}" class="bg-white rounded-circle" width=""
                            alt="" style="width: 78px; height: 78px" />

                        <div class="d-flex justify-content-center align-items-center flex-column mt-4">
                            <p class="mb-1" style="color: black; font-size: 15px; line-height: 18px">Republic of the
                                Philippines</p>
                            <p class="mb-1" style="color: black; font-size: 15px; line-height: 18px">Province of Antique
                            </p>
                            <p class="mb-1 fw-semibold" style="color: black; font-size: 17px; line-height: 18px">
                                MUNICIPALITY OF
                                TIBIAO</p>
                            <p class="mb-1" style="color: black; font-size: 15px; line-height: 18px">Municipal Disaster
                                Risk
                                Reduction Office</p>
                            <p class="mb-1" style="color: black; font-size: 15px; line-height: 18px">Hotline No:
                                09778035582
                            </p>
                            <p class="mb-1" style="color: black; font-size: 15px; line-height: 18px">Email:
                                mdrrmotibiao@gmail.com</p>
                        </div>
                        <img src="{{ asset('assets/images/logo1.png') }}" class="bg-white rounded-circle" width=""
                            alt="" style="width: 78px; height: 78px" />
                    </div>

                    <p class="mb-2 mt-4 text-center fw-semibold text-uppercase" style="font-size: 20px; color: black">
                        INCIDENT REPORT AS OF
                        {{ date('F Y', strtotime(request('monthyear', date('Y-m')) . '-01')) }}
                    </p>
                    <table class="table-bordered border-dark table mt-3 table-report">
                        <thead>
                            <tr>
                                <th class="text-center p-1 align-middle" style="font-size: 12px" style="font-size: 12px">No.
                                </th>
                                <th class="text-center p-1 align-middle" style="font-size: 12px" style="font-size: 12px">
                                    Staff</th>
                                <th class="text-center p-1 align-middle" style="font-size: 12px" style="font-size: 12px">
                                    Date & Time of Occurence</th>
                                <th class="text-center p-1 align-middle" style="font-size: 12px" style="font-size: 12px">
                                    Barangay</th>
                                <th class="text-center p-1 align-middle" style="font-size: 12px" style="font-size: 12px">
                                    Specific Location</th>
                                <th class="text-center p-1 align-middle" style="font-size: 12px" style="font-size: 12px">
                                    Detailed Description<br>of
                                    Incident</th>
                                <th class="text-center p-0 align-middle" style="font-size: 12px" style="font-size: 12px">
                                    <div class="d-flex flex-column h-100">
                                        <div class="p-1 border-inside-btm">
                                            No. of Person Involved
                                        </div>
                                        <div class="d-flex flex-1">
                                            <div class="w-50 h-100 p-1 border-inside-rt">Injured</div>
                                            <div class="w-50 h-100 p-1">Dead</div>
                                        </div>
                                    </div>
                                </th>
                                <th class="text-center p-1 align-middle" style="font-size: 12px" style="font-size: 12px">
                                    Date Submitted</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data as $d)
                                <tr>
                                    <td class="text-center px-2 py-1 align-middle" style="font-size: 12px">
                                        {{ $loop->iteration }}</td>
                                    <td class="text-center px-2 py-1 align-middle" style="font-size: 12px">
                                        {{ $d->fullname }}</td>
                                    <td class="text-center px-2 py-1 align-middle" style="font-size: 12px">
                                        {{ date('F d, Y h:i a', strtotime($d->datetimeoccurence)) }}
                                    </td>
                                    <td class="text-center px-2 py-1 align-middle" style="font-size: 12px">
                                        {{ $d->barangay }}</td>
                                    <td class="text-center px-2 py-1 align-middle" style="font-size: 12px">
                                        {{ $d->specificlocation }}</td>
                                    <td class="text-center px-2 py-1 align-middle" style="font-size: 12px">
                                        {{ $d->detaileddesc }}</td>
                                    <td class="text-center p-0 align-middle" style="font-size: 12px">
                                        <div class="d-flex h-100">
                                            <div style="border-right: 1px solid black;" class="w-50 h-100 p-1">
                                                {{ $d->involvedinjured }}</div>
                                            <div class="w-50 h-100 p-1">{{ $d->involveddead }}</div>
                                        </div>
                                    </td>
                                    <td class="text-center px-2 py-1 align-middle" style="font-size: 12px">
                                        {{ date('F d, Y h:i a', strtotime($d->created_at)) }}
                                    </td>
                                </tr>
                            @endforeach

                            @if (empty($data) || count($data) == 0)
                                <tr>
                                    <td colspan="8" class="text-center py-1" style="font-size: 12px">No Data</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-end">
                        <div class="text-end mt-2 d-flex justify-content-center text-dark align-items-center flex-column">
                            <p class="fw-semibold mb-0">NORMAN L. ALABADO</p>
                            <p class="mb-0">MDRRMO</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    @include('report.js.printing')
    <script>
        const reportData = @json($data);
    </script>
    @include('report.js.incidentdownload')
@endsection
