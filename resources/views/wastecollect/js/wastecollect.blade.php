<script>
    let wastecollectOptions;
    let wastecollectTable;
    let wastecollectData = [];
    let selectedwastecollectId = null;

    wastecollectOptions = {
        processing: false,
        serverSide: true,
        // data:[],
        ajax: {
            url: "{{ route('getwastecollect') }}",
            type: 'POST',
            dataType: 'json',
            data: function(d) {
                d._token = '{{ csrf_token() }}';
            },
            dataSrc: function(json) {
                wastecollectData = json.data;
                return json.data;
            }
        },
        columns: [{
                title: 'No.',
                className: 'text-nowrap p-3 text-center',
                render: function(data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            {
                title: 'Date Created',
                className: 'text-nowrap p-3',
                render: function(data, type, row) {
                    return formatDateToStr(row.created_at);
                }
            },
            {
                title: 'Barangay',
                className: 'text-nowrap p-3',
                render: function(data, type, row) {
                    return row.barangay;
                }
            },
            {
                title: 'Schedule',
                className: 'text-nowrap p-3',
                render: function(data, type, row) {
                    return `<span class="border-end border-dark pe-1 me-1">${row.schedule_from}</span>${row.schedule_to}`;
                }
            },
            {
                title: 'Recyclable',
                className: 'text-nowrap p-3 text-center',
                render: function(data, type, row) {
                    return row.recyclable;
                }
            },
            {
                title: 'Biodegradable',
                className: 'text-nowrap p-3 text-center',
                render: function(data, type, row) {
                    return row.biodegradable
                }
            },
            {
                title: 'Non-Bio',
                className: 'text-nowrap p-3 text-center',
                render: function(data, type, row) {
                    return row.nonbio
                }
            },
            {
                title: 'Special Waste',
                className: 'text-nowrap p-3 text-center',
                render: function(data, type, row) {
                    return row.specialwaste
                }
            },
            {
                title: 'Total',
                className: 'text-nowrap p-3 text-center',
                render: function(data, type, row) {
                    return row.total
                }
            }
        ],
        initComplete: function(settings, json) {
            appendButtonswastecollect();
            $('[data-bs-toggle="tooltip"]').tooltip();
        }
    };

    $(document).ready(function() {
        renderwastecollectTable();
    })

    function renderwastecollectTable() {
        if (wastecollectTable) {
            wastecollectTable.destroy();
        }
        wastecollectTable = new DataTable('#wastecollectTable', wastecollectOptions)
    }

    $(document).on("click", "#reloadwastecollectBtn", function() {
        reloadButtonLoading(true);
        reloadwastecollectTable();
        setTimeout(() => {
            reloadButtonLoading(false);
        }, 500);
    });

    function reloadwastecollectTable() {
        if (wastecollectTable) {
            wastecollectTable.ajax.reload(null, false);
        } else {
            renderwastecollectTable();
        }
    }

    function reloadwastecollectTableWithPagination() {
        if (wastecollectTable) {
            wastecollectTable.ajax.reload(null, true);
        } else {
            renderwastecollectTable();
        }
    }

    function appendButtonswastecollect() {
        $('#wastecollectTable_wrapper .row .dt-length').append(`
            <div class="d-flex gap-2 ms-2 align-items-center wastecollectBtnSm">
                <button class="btn btn-success d-flex flex-nowrap align-items-center gap-2" id="newwastecollectBtn">
                    <span>
                        <i class="bi bi-clipboard-plus"></i>
                    </span>
                    Add New
                </button>
                <button class="btn btn-info d-flex flex-nowrap align-items-center gap-2" id="editwastecollectBtn">
                    <span>
                        <i class="bi bi-pencil-square"></i>
                    </span>
                    Edit
                </button>
                <button class="btn btn-danger d-flex flex-nowrap align-items-center gap-2" id="deletewastecollectBtn">
                    <span>
                        <i class="ti ti-trash"></i>
                    </span>
                    Delete
                </button>
                <button class="btn btn-primary d-flex flex-nowrap align-items-center gap-2" id="">
                    <span>
                        <i class="bi bi-share"></i>
                    </span>
                    Share
                </button>
                <button class="btn btn-gray-new d-flex flex-nowrap align-items-center gap-2" id="reloadwastecollectBtn">
                    <span>
                        <i class="bi bi-arrow-clockwise"></i>
                    </span>
                    Reload
                </button>
               
            </div>
        `);
    }

    function reloadButtonLoading(isLoading) {
        if (isLoading) {
            $("#reloadwastecollectBtn").html(`
                    <div class="spinner-border text-white" role="status" style="width: 14px; height: 14px">
                </div>
                Reloading
            `);
        } else {
            $("#reloadwastecollectBtn").html(`
                <i class="bi bi-arrow-clockwise"></i>
                Reload
            `);
        }
    }

    $(document).on('click', '#wastecollectTable tbody tr', function() {
        let data = wastecollectTable.row(this).data();
        if (!data) return;

        if ($(this).hasClass('selected')) {
            $(this).removeClass('selected');
            selectedwastecollectId = null;
        } else {
            $('tr.selected').removeClass('selected');
            $(this).addClass('selected');
            selectedwastecollectId = data.record_id; // wastecollect the ID
        }
    });

    // Rewastecollect selection after reload
    wastecollectOptions.drawCallback = function(settings) {
        wastecollectTable.rows().every(function() {
            let data = this.data();
            if (data.record_id === selectedwastecollectId) {
                $(this.node()).addClass('selected');
            }
        });
    };
</script>
