<script>
    let staffreportOptions;
    let staffreportTable;
    let reportFormData = [];
    let selectedstaffreportId = null;
    let dateFrom = "";
    let dateTo = "";
    let typeReportParams = "{{ request('from') }}";

    let typesReports = {
        'Incident': 'INCIDENTREPORT',
        'Situational': 'SITUATIONALREPORT',
        'Progress': 'PROGRESSREPORT'
    };

    function getReportType(key) {
        return typesReports[key] ?? null;
    }
    console.log("hello");
    staffreportOptions = {
        processing: false,
        serverSide: true,
        // data: [],
        ajax: {
            url: "{{ route('getstaffreports') }}",
            type: 'POST',
            dataType: 'json',
            data: function(d) {
                typeReport = getReportType(typeReportParams);
                d._token = '{{ csrf_token() }}';
                d.dateFrom = dateFrom;
                d.dateTo = dateTo;
                d.typeOfRecord = typeReport;
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
                title: 'Staff',
                className: 'text-nowrap p-3 align-middle text-center',
                render: function(data, type, row) {
                    return row.fullname;
                }
            },
            // {
            //     title: 'Brgy Test',
            //     className: 'text-nowrap p-3 align-middle text-center',
            //     render: function(data, type, row) {
            //         return row.barangay;
            //     }
            // },
            {
                title: 'Designation',
                className: 'text-nowrap p-3 align-middle text-center',
                render: function(data, type, row) {
                    return row.designation;
                }
            },
            {
                title: 'Report Type',
                className: 'text-nowrap p-3 align-middle text-center',
                render: function(data, type, row) {
                    return formatRecordType(row.typeOfRecord);
                }
            },
            {
                title: 'Address',
                className: 'text-nowrap p-3 align-middle text-center',
                render: function(data, type, row) {
                    return row.address;
                }
            },
            {
                title: 'Contact',
                className: 'text-nowrap p-3 align-middle text-center',
                render: function(data, type, row) {
                    return row.phone_num;
                }
            },
            {
                title: 'File Submitted',
                className: 'text-nowrap p-3 align-middle text-center',
                render: function(data, type, row) {
                    if (row.filesubmitted) {

                        let fileUrl = "{{ asset('') }}" + row.filesubmitted;

                        let ext = row.filesubmitted.split('.').pop().toLowerCase();

                        if (ext === 'pdf') {
                            return `
                                <a href="${fileUrl}" class="file-link">
                                    <img src="{{ asset('assets/images/pdf.png') }}" 
                                        style="width: 20px; height: auto;">
                                </a>
                            `;
                        }

                        // Otherwise → show normal icon
                        return `
                            <a href="${fileUrl}" class="file-link">
                                <i style="font-size: 18px" class="bi bi-file-earmark-break"></i>
                            </a>
                        `;
                    }

                    return 'N/A';
                }
            },
            {
                title: 'Date & Time Submitted',
                className: 'text-nowrap p-3 align-middle text-center',
                render: function(data, type, row) {
                    return formatDateToStr(row.created_at);
                }
            },
            {
                title: 'Action',
                className: 'text-nowrap p-3 align-middle text-center sticky-action',
                render: function(data, type, row) {
                    let cleanedType = row.typeOfRecord.replace(/report/i, "").trim().toLowerCase();
                    return `
                        <div class="d-flex gap-2">
                            <button class="btn btn-orange editRecord" data-table="staff" data-type="${cleanedType}" data-record_id="${row.record_id}">
                                <i class="bi bi-eye-fill"></i>
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
            appendButtonsstaffreport();
            $('[data-bs-toggle="tooltip"]').tooltip();
        }
    };

    $(document).ready(function() {
        renderstaffreportTable();
    })

    function renderstaffreportTable() {
        if (staffreportTable) {
            staffreportTable.destroy();
        }
        staffreportTable = new DataTable('#staffreportTable', staffreportOptions)
    }

    $(document).on("click", "#reloadstaffreportBtn", function() {
        resetDate();
        reloadButtonLoading(true);
        reloadstaffreportTable();
        setTimeout(() => {
            reloadButtonLoading(false);
        }, 500);
    });

    function reloadstaffreportTable() {
        if (staffreportTable) {
            staffreportTable.ajax.reload(null, false);
        } else {
            renderstaffreportTable();
        }
    }

    function reloadstaffreportTableWithPagination() {
        if (staffreportTable) {
            staffreportTable.ajax.reload(null, true);
        } else {
            renderstaffreportTable();
        }
    }

    function appendButtonsstaffreport() {
        $('#staffreportTable_wrapper .row .dt-length').append(`
            <div class="d-flex gap-2 ms-2 align-items-center staffreportBtnSm">
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
                <button class="btn btn-gray-new d-flex flex-nowrap align-items-center gap-2" id="reloadstaffreportBtn">
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
            $("#reloadstaffreportBtn").html(`
                    <div class="spinner-border text-white" role="status" style="width: 14px; height: 14px">
                </div>
                Reloading
            `);
        } else {
            $("#reloadstaffreportBtn").html(`
                <i class="bi bi-arrow-clockwise"></i>
                Reload
            `);
        }
    }

    $(document).on('click', '#filterDateBtn', function() {
        dateFrom = $("#dateFromFilter").val();
        dateTo = $("#dateToFilter").val();
        staffreportOptions.ajax.data.dateFrom = dateFrom;
        staffreportOptions.ajax.data.dateTo = dateTo;
        reloadstaffreportTable();
    });

    function resetDate() {
        dateFrom = "";
        dateTo = "";

        staffreportOptions.ajax.data.dateFrom = dateFrom;
        staffreportOptions.ajax.data.dateTo = dateTo;
    }

    $(document).on('click', '.deleteRecord', function() {
        let record_id = $(this).data("record_id");
        Swal.fire({
            title: `Delete this Staff Report?`,
            text: `Are you sure you want to delete this Staff Report?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: "Delete"
        }).then((result) => {
            if (result.isConfirmed) {
                postRequest("{{ route('deleteRecord') }}", {
                    record_id: record_id,
                }, (response) => {
                    if (response.status == "success") {
                        reloadstaffreportTable();
                    }
                })
            }
        });
    });
</script>
