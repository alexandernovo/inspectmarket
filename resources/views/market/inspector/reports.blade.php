@extends('market.layouts.portal')

@section('content')
    <section class="inspector-page inspector-report-page">
        <header class="inspector-page-title">
            <i class="bi bi-file-earmark-text-fill"></i>
            <div><h1>REPORT</h1><p>Dashboard | Report</p></div>
        </header>

        @if (!$selectedLivestock || !$selectedMonth)
            <form method="GET" class="inspector-report-picker">
                <header>
                    <img src="{{ asset('assets/einspect/HOMEPAGE/Logo.png') }}" alt="">
                    <h2>SLAUGHTERED LIVESTOCK</h2>
                    <p>Inspection Report</p>
                </header>
                <label>Slaughtered Livestock Type:
                    <span><i class="bi bi-file-earmark-text-fill"></i><select name="livestock" required><option value="">Please select</option><option>POULTRY</option><option>PORK</option><option>BEEF</option></select></span>
                </label>
                <label>Select Month:
                    <span><i class="bi bi-calendar3"></i><input type="month" name="month" required></span>
                </label>
                <button type="submit">View Report</button>
            </form>
        @else
            <section class="inspector-report-sheet">
                <header class="inspector-report-toolbar">
                    <label>Select Month and Year<input type="month" id="reportMonth" value="{{ $selectedMonth->format('Y-m') }}"></label>
                    <button type="button" id="reloadInspectorReport"><i class="bi bi-arrow-clockwise"></i> Reload</button>
                    <span>Download</span>
                    <button type="button" class="report-download pdf" onclick="window.print()" title="Print or save as PDF"><i class="bi bi-file-earmark-pdf-fill"></i></button>
                    <a class="report-download word" href="{{ route('inspector.reports.export', ['format' => 'doc', 'livestock' => $selectedLivestock, 'month' => $selectedMonth->format('Y-m')]) }}" title="Download Word"><i class="bi bi-file-earmark-word-fill"></i></a>
                    <a class="report-download excel" href="{{ route('inspector.reports.export', ['format' => 'xls', 'livestock' => $selectedLivestock, 'month' => $selectedMonth->format('Y-m')]) }}" title="Download Excel"><i class="bi bi-file-earmark-excel-fill"></i></a>
                    <button type="button" class="inspector-print-report" onclick="window.print()"><i class="bi bi-printer-fill"></i> Print Report</button>
                </header>
                <div class="inspector-report-letterhead">
                    <img src="{{ asset('assets/einspect/HOMEPAGE/Logo.png') }}" alt="">
                    <p>Department of Health<br>Office of the Municipal Health Officer<br><strong>MUNICIPALITY OF PANDAN</strong></p>
                    <h2>LIST OF {{ $selectedLivestock }} SLAUGHTERED INSPECTED REPORT OF {{ strtoupper($selectedMonth->format('F Y')) }}</h2>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            @if ($selectedLivestock === 'POULTRY')
                                <tr><th>NO.</th><th>OWNER</th><th>ADDRESS</th><th>TYPE OF POULTRY</th><th>NUMBER OF SLAUGHTERED</th><th>INSPECTION RESULT</th><th>DATE AND TIME OF INSPECTION</th></tr>
                            @else
                                <tr><th>NO.</th><th>OWNER</th><th>ADDRESS</th><th>AGE</th><th>WEIGHT</th><th>NUMBER OF SLAUGHTERED</th><th>INSPECTION RESULT</th><th>DATE AND TIME OF INSPECTION</th></tr>
                            @endif
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $row->owner_name }}</td>
                                    <td>{{ $row->address }}</td>
                                    @if ($selectedLivestock === 'POULTRY')
                                        <td>{{ $row->breed ?: 'Chicken' }}</td>
                                    @else
                                        <td>{{ $row->animal_age ?: '-' }}</td>
                                        <td>{{ $row->live_weight !== null ? number_format((float) $row->live_weight, 2).' kg' : '-' }}</td>
                                    @endif
                                    <td>{{ $row->animal_count }}</td>
                                    <td>{{ match($row->inspection_result) { 'PASSED' => 'Passed with Human Consumption', 'CONDEMNED' => 'Condemned', 'REINSPECTION' => 'For Further Examination', default => '-' } }}</td>
                                    <td>{{ $row->scheduled_at->format('F d, Y | g:i A') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="{{ $selectedLivestock === 'POULTRY' ? 7 : 8 }}" class="empty-state">No completed inspection records for this month.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <footer><strong>{{ strtoupper(auth()->user()->full_name) }}</strong><span>{{ auth()->user()->designation }}</span></footer>
            </section>
        @endif
    </section>
@endsection

@push('scripts')
    <script>
        $('#reloadInspectorReport').on('click', function () {
            window.location.href = "{{ route('inspector.reports') }}?livestock={{ $selectedLivestock }}&month=" + $('#reportMonth').val();
        });
    </script>
@endpush
