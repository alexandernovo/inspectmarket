@extends('market.layouts.portal')

@section('content')
    @php
        $chartSeries = collect(['ALL', 'POULTRY', 'PORK', 'BEEF'])->mapWithKeys(function ($type) use ($monthlyTotals) {
            $values = collect(range(1, 12))->map(function ($month) use ($type, $monthlyTotals) {
                if ($type === 'ALL') {
                    return collect(['POULTRY', 'PORK', 'BEEF'])->sum(fn ($livestock) => (int) ($monthlyTotals->get($livestock)?->get($month) ?? 0));
                }
                return (int) ($monthlyTotals->get($type)?->get($month) ?? 0);
            });
            return [$type => $values];
        });
    @endphp

    <section class="inspector-page inspector-dashboard">
        <header class="inspector-page-title">
            <i class="bi bi-grid-fill"></i>
            <div><h1>DASHBOARD</h1><p>Slaughtered Livestock Inspection Recording Management System</p></div>
        </header>

        <div class="inspector-stat-grid">
            @foreach ($stats as $stat)
                <article class="inspector-stat {{ $stat['tone'] }}">
                    <div class="inspector-stat-image">
                        <img src="{{ asset('assets/einspect/INSPECTOR/IMAGES/'.$stat['image']) }}" alt="{{ $stat['sublabel'] }}">
                    </div>
                    <div>
                        <span>{{ $stat['label'] }}</span>
                        <small>( {{ $stat['sublabel'] }} )</small>
                        <strong>{{ $stat['value'] }}</strong>
                    </div>
                </article>
            @endforeach
        </div>

        <section class="inspector-table-panel">
            <div class="inspector-table-toolbar">
                <select id="dashboardLength" aria-label="Rows per page"><option value="5">5 v</option><option value="10" selected>10 v</option><option value="25">25 v</option></select>
                <label>From:<input type="date" id="dashboardFrom"></label>
                <label>To:<input type="date" id="dashboardTo"></label>
                <button type="button" id="dashboardFilter"><i class="bi bi-funnel-fill"></i> Filter</button>
                <button type="button" id="dashboardReload"><i class="bi bi-arrow-clockwise"></i> Reload</button>
                <label class="inspector-toolbar-search">Search:<input type="search" id="dashboardSearch"></label>
            </div>
            <div class="table-wrap">
                <table id="inspectorDashboardTable" class="inspector-data-table">
                    <thead><tr><th>NO.</th><th>OWNER</th><th>ADDRESS</th><th>CONTACT NUMBER</th><th>TYPE OF SLAUGHTERED INSPECT</th><th>REQUEST STATUS</th><th>ACTION</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>

        <section class="inspector-chart-panel">
            <header>
                <h2>SLAUGHTERED INSPECT DATA CHART</h2>
                <div>
                    <select id="inspectorChartType"><option value="ALL">Select Category</option><option>POULTRY</option><option>PORK</option><option>BEEF</option></select>
                    <select aria-label="Month"><option>All Months</option></select>
                    <select aria-label="Year">@forelse($chartYears as $year)<option>{{ $year }}</option>@empty<option>{{ now()->year }}</option>@endforelse</select>
                </div>
            </header>
            <div class="inspector-chart" data-chart-series='@json($chartSeries)'>
                @foreach (range(1, 12) as $month)
                    <div><span data-chart-bar="{{ $month }}"></span><small>{{ now()->startOfYear()->addMonths($month - 1)->format('F') }}</small></div>
                @endforeach
            </div>
        </section>
    </section>
@endsection

@push('scripts')
    <script>
        $(function () {
            let dateFrom = '';
            let dateTo = '';
            const table = $('#inspectorDashboardTable').DataTable({
                processing: true,
                serverSide: true,
                dom: 'rtip',
                order: [],
                pageLength: 10,
                ajax: {
                    url: "{{ route('inspector.datatable.inspections') }}",
                    data: function (data) {
                        data.mode = 'requests';
                        data.dateFrom = dateFrom;
                        data.dateTo = dateTo;
                    }
                },
                columns: [
                    { data: null, orderable: false, searchable: false, render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1 },
                    { data: 'owner', orderable: false },
                    { data: 'address', orderable: false },
                    { data: 'contact', orderable: false },
                    { data: 'type', orderable: false },
                    { data: 'status', orderable: false },
                    { data: 'action', orderable: false, searchable: false }
                ]
            });

            $('#dashboardLength').on('change', function () { table.page.len(Number(this.value)).draw(); });
            $('#dashboardSearch').on('input', function () { table.search(this.value).draw(); });
            $('#dashboardFilter').on('click', function () {
                dateFrom = $('#dashboardFrom').val();
                dateTo = $('#dashboardTo').val();
                table.ajax.reload();
            });
            $('#dashboardReload').on('click', function () {
                dateFrom = '';
                dateTo = '';
                $('#dashboardFrom, #dashboardTo, #dashboardSearch').val('');
                table.search('').ajax.reload();
            });
            $(document).on('click', '.js-request-view', function () {
                const record = JSON.parse($(this).attr('data-record'));
                window.location.href = "{{ route('inspector.inspections') }}?focus=" + record.id;
            });

            const chart = document.querySelector('.inspector-chart');
            const series = JSON.parse(chart.dataset.chartSeries);
            function renderChart(type) {
                const values = series[type] || series.ALL;
                const maximum = Math.max(...values, 1);
                chart.querySelectorAll('[data-chart-bar]').forEach((bar, index) => {
                    bar.style.height = Math.max(4, Math.round((values[index] / maximum) * 100)) + '%';
                    bar.title = values[index] + ' animal(s)';
                });
            }
            $('#inspectorChartType').on('change', function () { renderChart(this.value); });
            renderChart('ALL');
        });
    </script>
@endpush
