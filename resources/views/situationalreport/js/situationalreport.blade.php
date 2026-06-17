<script>
    let situationalreportOptions;
    let situationalreportTable;
    let reportFormData = [];
    let selectedsituationalreportId = null;
    let dateFrom = "";
    let dateTo = "";
    let record_id_remarks = null;

    situationalreportOptions = {
        processing: false,
        serverSide: true,
        // data: [],
        ajax: {
            url: "{{ route('getstaffreports') }}",
            type: 'POST',
            dataType: 'json',
            data: function(d) {
                d._token = '{{ csrf_token() }}';
                d.typeOfRecord = "SITUATIONALREPORT";
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
                title: 'Staff<br>Name',
                visible: !isStaff,
                className: 'text-nowrap p-3 align-middle text-center',
                render: function(data, type, row) {
                    return row.fullname;
                }
            },
            {
                title: 'Designation',
                visible: !isStaff,
                className: 'text-nowrap p-3 align-middle text-center',
                render: function(data, type, row) {
                    return row.designation;
                }
            },
            {
                title: 'Barangay',
                className: 'text-nowrap p-3 align-middle text-center',
                render: function(data, type, row) {
                    return row.barangay;
                }
            },
            {
                title: 'Affected Families',
                className: 'text-nowrap p-3 align-middle text-center',
                render: function(data, type, row) {
                    return row.affectedfamilies;
                }
            },
            {
                title: 'Person/Individuals',
                className: 'text-nowrap p-3 align-middle text-center',
                render: function(data, type, row) {
                    return row.individuals;
                }
            },
            {
                title: `
                    <div class="p-2" style="border-bottom: 1px solid #EBF1F6">Evacuation Center/Outside</div>
                    <div class="d-flex">
                        <div class="w-50 p-2" style="border-right: 1px solid #EBF1F6">Families</div>
                        <div class="w-50 p-2">Individuals</div>
                    </div>
                `,
                orderable: false,
                className: 'text-nowrap p-3 align-middle text-center p-0',
                render: function(data, type, row) {
                    return `
                        <div class="d-flex">
                           <div class="w-50 p-2" style="border-right: 1px solid #EBF1F6">${row.evacuationfamilies}</div>
                           <div class="w-50 p-2">${row.evacuationindividuals}</div>
                        <div>
                    `;
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
                title: 'File<br>Submitted',
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
                title: 'Date<br>Submitted',
                className: 'text-nowrap p-3 align-middle text-center',
                render: function(data, type, row) {
                    return formatDateToStr(row.created_at);
                }
            },
            {
                title: 'Remarks',
                className: 'text-nowrap px-4 align-middle text-center',
                render: function(data, type, row) {
                    return `
                        <div class="d-flex gap-2 justify-content-center">
                            ${!isStaff ? `
                                <button class="btn btn-red remarksBtn" data-record_id="${row.record_id}">
                                    <i class="bi bi-envelope-fill"></i>
                                </button>
                            `: `
                                ${row.remarksAdmin ? `
                                    <button class="btn btn-red remarksBtn" data-record_id="${row.record_id}">
                                        <i class="bi bi-envelope-exclamation-fill"></i>
                                    </button>
                                ` : ``}
                            `}
                        <div>
                    `;
                }
            },
            {
                title: 'Action',
                className: 'text-nowrap p-3 align-middle text-center sticky-action',
                render: function(data, type, row) {
                    return `
                        <div class="d-flex gap-2">
                            <button class="btn btn-orange editRecord" data-type="situational" data-record_id="${row.record_id}">
                                <i class="bi ${isStaff ? 'bi-pencil-square' : 'bi bi-eye-fill'}"></i>
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
            appendButtonssituationalreport();
            $('[data-bs-toggle="tooltip"]').tooltip();
        }
    };

    $(document).ready(function() {
        rendersituationalreportTable();
    })

    function rendersituationalreportTable() {
        if (situationalreportTable) {
            situationalreportTable.destroy();
        }
        situationalreportTable = new DataTable('#situationalreportTable', situationalreportOptions)
    }

    $(document).on("click", "#reloadsituationalreportBtn", function() {
        resetDate();
        reloadButtonLoading(true);
        reloadsituationalreportTable();
        setTimeout(() => {
            reloadButtonLoading(false);
        }, 500);
    });

    function reloadsituationalreportTable() {
        if (situationalreportTable) {
            situationalreportTable.ajax.reload(null, false);
        } else {
            rendersituationalreportTable();
        }
    }

    function reloadsituationalreportTableWithPagination() {
        if (situationalreportTable) {
            situationalreportTable.ajax.reload(null, true);
        } else {
            rendersituationalreportTable();
        }
    }

    function appendButtonssituationalreport() {
        $('#situationalreportTable_wrapper .row .dt-length').append(`
            <div class="d-flex gap-2 ms-2 align-items-center situationalreportBtnSm">
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
                <button class="btn btn-gray-new d-flex flex-nowrap align-items-center gap-2" id="reloadsituationalreportBtn">
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
            $("#reloadsituationalreportBtn").html(`
                    <div class="spinner-border text-white" role="status" style="width: 14px; height: 14px">
                </div>
                Reloading
            `);
        } else {
            $("#reloadsituationalreportBtn").html(`
                <i class="bi bi-arrow-clockwise"></i>
                Reload
            `);
        }
    }

    $(document).on('click', '#filterDateBtn', function() {
        dateFrom = $("#dateFromFilter").val();
        dateTo = $("#dateToFilter").val();
        situationalreportOptions.ajax.data.dateFrom = dateFrom;
        situationalreportOptions.ajax.data.dateTo = dateTo;
        reloadsituationalreportTable();
    });

    function resetDate() {
        dateFrom = "";
        dateTo = "";

        situationalreportOptions.ajax.data.dateFrom = dateFrom;
        situationalreportOptions.ajax.data.dateTo = dateTo;
    }

    $(document).on('click', '.deleteRecord', function() {
        let record_id = $(this).data("record_id");
        Swal.fire({
            title: `Delete this Situational Report?`,
            text: `Are you sure you want to delete this Situational Report?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: "Delete"
        }).then((result) => {
            if (result.isConfirmed) {
                postRequest("{{ route('deleteRecord') }}", {
                    record_id: record_id,
                }, (response) => {
                    if (response.status == "success") {
                        reloadsituationalreportTable();
                    }
                })
            }
        });
    });

    $(document).on("click", ".remarksBtn", function() {
        record_id_remarks = $(this).data("record_id");

        $("#remarksAdminTitle").text("TIBIAO MDRRMO SITUATIONAL REPORT REMARKS");
        if (isStaff) {
            $("#remarksAdmin").attr('readonly', true);
            $("#submitRemarks").addClass('d-none');
        }
        let remarks = reportFormData.find(x => x.record_id == record_id_remarks);

        if (remarks) {
            $("#remarksAdmin").val(remarks.remarksAdmin ?? '');
        }

        $("#remarksModal").modal("show");
    })

    $(document).on("click", "#submitRemarks", function() {
        postRequest("{{ route('submitRemarks') }}", {
            record_id: record_id_remarks,
            remarksAdmin: $("#remarksAdmin").val()
        }, (response) => {
            if (response.status == "success") {
                Swal.fire({
                    title: `Success`,
                    text: `Remarks Submitted Successfully`,
                    icon: 'success',
                    confirmButtonText: "Okay"
                })
                $("#remarksModal").modal("hide");
                reloadsituationalreportTable();
            }
        })
    })
</script>
