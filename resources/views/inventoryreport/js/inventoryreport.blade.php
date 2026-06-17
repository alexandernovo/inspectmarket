<script>
    let inventoryreportOptions;
    let inventoryreportTable;
    let reportFormData = [];
    let selectedinventoryreportId = null;
    let dateFrom = "";
    let dateTo = "";

    inventoryreportOptions = {
        processing: false,
        serverSide: true,
        // data: [],
        ajax: {
            url: "{{ route('getstaffreports') }}",
            type: 'POST',
            dataType: 'json',
            data: function(d) {
                d._token = '{{ csrf_token() }}';
                d.typeOfRecord = "INVENTORYREPORT";
                d.dateFrom = dateFrom;
                d.dateTo = dateTo;
            },
            dataSrc: function(json) {
                reportFormData = json.data;
                return json.data;
            }
        },
        columns: [{
                title: 'No.',
                className: 'text-nowrap p-3 text-center align-middle',
                render: function(data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            {
                title: 'Description',
                className: 'text-nowrap p-3 align-middle text-center',
                render: function(data, type, row) {
                    return row.description;
                }
            },
            {
                title: 'Unit',
                className: 'text-nowrap p-3 align-middle text-center',
                render: function(data, type, row) {
                    return row.unit;
                }
            },
            {
                title: 'Property No.',
                className: 'text-nowrap p-3 align-middle text-center',
                render: function(data, type, row) {
                    return row.propertyno;
                }
            },
            {
                title: 'Date Acquired',
                className: 'text-nowrap p-3 align-middle text-center',
                render: function(data, type, row) {
                    return formatDateToStr(row.dateacquired, false);
                }
            },
            {
                title: 'Remarks',
                className: 'text-nowrap p-3 align-middle text-center',
                render: function(data, type, row) {
                    return row.remarks;
                }
            },
            {
                title: 'Amount',
                className: 'text-nowrap p-3 align-middle text-center',
                render: function(data, type, row) {
                    return "₱" + row.amount;
                }
            },
            // {
            //     title: 'Quantity',
            //     className: 'text-nowrap p-3 align-middle text-center',
            //     render: function(data, type, row) {
            //         return row.quantity;
            //     }
            // },
            // {
            //     title: 'File<br>Submitted',
            //     className: 'text-nowrap p-3 align-middle text-center',
            //     render: function(data, type, row) {
            //         if (row.filesubmitted) {
            //             let fileUrl = "{{ asset('') }}" + row
            //                 .filesubmitted;
            //             return `<a href="${fileUrl}" class="file-link"><i style="font-size: 18px" class="bi bi-file-earmark-break"></i></a>`;
            //         }
            //         return 'N/A';
            //     }
            // },
            // {
            //     title: 'Date<br>Submitted',
            //     className: 'text-nowrap p-3 align-middle text-center',
            //     render: function(data, type, row) {
            //         return formatDateToStr(row.created_at);
            //     }
            // },
            {
                title: 'Action',
                className: 'text-nowrap p-3 align-middle text-center sticky-action',
                render: function(data, type, row) {
                    return `
                        <div class="d-flex gap-2">
                            <button class="btn btn-orange editRecord" data-type="inventory" data-record_id="${row.record_id}">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button class="btn btn-red deleteRecord" data-record_id="${row.record_id}">
                                <i class="bi bi-trash3-fill"></i>
                            </button>
                        <div>
                    `;
                }
            }
        ],
        initComplete: function(settings, json) {
            appendButtonsinventoryreport();
            $('[data-bs-toggle="tooltip"]').tooltip();
        }
    };

    $(document).ready(function() {
        renderinventoryreportTable();
    })

    function renderinventoryreportTable() {
        if (inventoryreportTable) {
            inventoryreportTable.destroy();
        }
        inventoryreportTable = new DataTable('#inventoryreportTable', inventoryreportOptions)
    }

    $(document).on("click", "#reloadinventoryreportBtn", function() {
        resetDate();
        reloadButtonLoading(true);
        reloadinventoryreportTable();
        setTimeout(() => {
            reloadButtonLoading(false);
        }, 500);
    });

    function reloadinventoryreportTable() {
        if (inventoryreportTable) {
            inventoryreportTable.ajax.reload(null, false);
        } else {
            renderinventoryreportTable();
        }
    }

    function reloadinventoryreportTableWithPagination() {
        if (inventoryreportTable) {
            inventoryreportTable.ajax.reload(null, true);
        } else {
            renderinventoryreportTable();
        }
    }

    function appendButtonsinventoryreport() {
        $('#inventoryreportTable_wrapper .row .dt-length').append(`
            <div class="d-flex gap-2 ms-2 align-items-center inventoryreportBtnSm">
                <div class="d-flex">
                    <div class="input-group" style="width: 120%">
                        <span  style="border: 1px solid #EAEFF4 !important" class="input-group-text filter-padding">From:</span>
                        <input type="date" id="dateFromFilter" value="{{ date('Y-m-d') }}" class="form-control filter-padding rounded-end-0 border-end-0">
                    </div>
                    <div class="input-group" style="width: 110%">
                        <span  style="border: 1px solid #EAEFF4 !important" class="input-group-text rounded-start-0 filter-padding">To:</span>
                        <input type="date" id="dateToFilter" value="{{ date('Y-m-d') }}" class="form-control filter-padding rounded-end-0 border-end-0">
                    </div>
                    <button data-bs-toggle="tooltip" data-bs-title="Filter by Date & Time of Incident" type="button" id="filterDateBtn" class="btn btn-prime filter-padding d-flex gap-1 align-items-center border-1 rounded-start-0 position-relative">
                        <i class="bi bi-funnel-fill"></i>
                        Filter
                    </button>
                </div>
                <button class="btn btn-gray-new d-flex flex-nowrap align-items-center gap-2" id="reloadinventoryreportBtn">
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
            $("#reloadinventoryreportBtn").html(`
                    <div class="spinner-border text-white" role="status" style="width: 14px; height: 14px">
                </div>
                Reloading
            `);
        } else {
            $("#reloadinventoryreportBtn").html(`
                <i class="bi bi-arrow-clockwise"></i>
                Reload
            `);
        }
    }

    $(document).on('click', '#filterDateBtn', function() {
        dateFrom = $("#dateFromFilter").val();
        dateTo = $("#dateToFilter").val();
        inventoryreportOptions.ajax.data.dateFrom = dateFrom;
        inventoryreportOptions.ajax.data.dateTo = dateTo;
        reloadinventoryreportTable();
    });

    function resetDate() {
        dateFrom = "";
        dateTo = "";

        inventoryreportOptions.ajax.data.dateFrom = dateFrom;
        inventoryreportOptions.ajax.data.dateTo = dateTo;
    }

    $(document).on('click', '.deleteRecord', function() {
        let record_id = $(this).data("record_id");
        Swal.fire({
            title: `Delete this Inventory Report?`,
            text: `Are you sure you want to delete this Inventory Report?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: "Delete"
        }).then((result) => {
            if (result.isConfirmed) {
                postRequest("{{ route('deleteRecord') }}", {
                    record_id: record_id,
                }, (response) => {
                    reloadinventoryreportTable();
                })
            }
        });
    });
</script>
