<script>
    let tenantDashboardTable;
    let tenantDashboardDateFrom = '';
    let tenantDashboardDateTo = '';

    $(document).ready(function () {
        tenantDashboardTable = $('#tenantDashboardTable').DataTable({
            processing: true,
            serverSide: true,
            order: [],
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50],
            ajax: {
                url: "{{ route('tenant.datatable.inspections') }}",
                type: 'GET',
                data: function (data) {
                    data.dateFrom = tenantDashboardDateFrom;
                    data.dateTo = tenantDashboardDateTo;
                }
            },
            columns: [
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                { data: 'owner_name', orderable: false },
                { data: 'address', orderable: false },
                { data: 'contact', orderable: false },
                { data: 'type', orderable: false },
                { data: 'schedule', orderable: false },
                { data: 'status', orderable: false },
                { data: 'action', orderable: false, searchable: false }
            ],
            initComplete: function () {
                renderTenantDashboardToolbar();
            }
        });
    });

    function renderTenantDashboardToolbar() {
        $('.tenant-dashboard-ajax-toolbar').html(`
            <div class="tenant-dashboard-filters">
                <select id="tenantDashboardLength" aria-label="Rows per page">
                    <option value="5">5 v</option>
                    <option value="10" selected>10 v</option>
                    <option value="25">25 v</option>
                    <option value="50">50 v</option>
                </select>
                <label>From:<input type="date" id="tenantDashboardDateFrom"></label>
                <label>To:<input type="date" id="tenantDashboardDateTo"></label>
                <button class="button" type="button" id="filterTenantDashboard"><i class="bi bi-funnel-fill"></i> Filter</button>
                <button class="button" type="button" id="reloadTenantDashboard"><i class="bi bi-arrow-clockwise"></i> Reload</button>
                <label class="tenant-dashboard-search">Search:<input type="search" id="tenantDashboardSearch"></label>
            </div>
        `);
    }

    $(document).on('change', '#tenantDashboardLength', function () {
        tenantDashboardTable.page.len(Number($(this).val())).draw();
    });

    $(document).on('input', '#tenantDashboardSearch', function () {
        tenantDashboardTable.search($(this).val()).draw();
    });

    $(document).on('click', '#filterTenantDashboard', function () {
        tenantDashboardDateFrom = $('#tenantDashboardDateFrom').val();
        tenantDashboardDateTo = $('#tenantDashboardDateTo').val();
        tenantDashboardTable.ajax.reload();
    });

    $(document).on('click', '#reloadTenantDashboard', function () {
        tenantDashboardDateFrom = '';
        tenantDashboardDateTo = '';
        $('#tenantDashboardDateFrom, #tenantDashboardDateTo, #tenantDashboardSearch').val('');
        tenantDashboardTable.search('').ajax.reload();
    });
</script>
