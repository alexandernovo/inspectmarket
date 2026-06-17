<script>
    let reportOptions;
    let reportTable;
    let selectedreportId = null;

    reportOptions = {
        processing: false,
        serverSide: true,
        scrollX: true,
        // data: [],
        ajax: {
            url: "{{ route('getreport') }}",
            type: 'POST',
            dataType: 'json',
            data: function(d) {
                d._token = '{{ csrf_token() }}';
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
                title: `Date & Time Submitted`,
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
                            <button class="btn btn-orange editRecord" data-table="dashboard" data-type="${cleanedType}" data-record_id="${row.record_id}">
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
            $('[data-bs-toggle="tooltip"]').tooltip();
        }
    };

    $(document).ready(function() {
        renderreportTable();
    })

    function renderreportTable() {
        if (reportTable) {
            reportTable.destroy();
        }
        reportTable = new DataTable('#reportTable', reportOptions)
    }

    $(document).on("click", "#reloadreportBtn", function() {
        reloadButtonLoading(true);
        reloadreportTable();
        setTimeout(() => {
            reloadButtonLoading(false);
        }, 500);
    });

    function reloadreportTable() {
        if (reportTable) {
            reportTable.ajax.reload(null, false);
        } else {
            renderreportTable();
        }
    }

    function reloadreportTableWithPagination() {
        if (reportTable) {
            reportTable.ajax.reload(null, true);
        } else {
            renderreportTable();
        }
    }

    function reloadButtonLoading(isLoading) {
        if (isLoading) {
            $("#reloadreportBtn").html(`
                    <div class="spinner-border text-white" role="status" style="width: 14px; height: 14px">
                </div>
                Reloading
            `);
        } else {
            $("#reloadreportBtn").html(`
                <i class="bi bi-arrow-clockwise"></i>
                Reload
            `);
        }
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
                        reloadreportTable();
                    }
                })
            }
        });
    });
</script>
