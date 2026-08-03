<script>
    const table = new DataTable('#biddingTable', {
        processing: true,
        serverSide: false,
        responsive: true,
        ordering: false,
        ajax: {
            url: "{{ route('biddingrequest.data') }}",
            type: "GET",
            dataSrc: ""
        },
        columns: [{
                data: null,
                className: "text-center",
                render: function(data, type, row, meta) {
                    return meta.row + 1;
                }
            },
            {
                data: "name_owner",
                className: "text-center",
                render: function(data) {
                    return data ?? '';
                }
            },
            {
                data: "tin",
                className: "text-center",
                render: function(data) {
                    return data ?? '';
                }
            },
            {
                data: "address",
                className: "text-center",
                render: function(data) {
                    return data ?? '';
                }
            },
            {
                data: "cellphone_no",
                className: "text-center",
                render: function(data) {
                    return data ?? '';
                }
            },
            {
                data: "created_at",
                className: "text-center",
                className: "text-center",
                render: function(data) {

                    const date = new Date(data);

                    return date.toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: '2-digit',
                            day: '2-digit'
                        }) + ' | ' +
                        date.toLocaleTimeString('en-US', {
                            hour: 'numeric',
                            minute: '2-digit',
                            hour12: true
                        });

                }
            },
            {
                data: null,
                className: "text-center",
                render: function(data, type, row) {

                    return `
                        <a href="{{ route('tenant.bidding_request.view') }}?application_id=${row.id}" class="btn btn-dark btn-sm viewApplication"
                            data-id="${row.id}">
                            <i class="bi bi-eye-fill"></i>
                        </a>

                        <button class="btn btn-danger btn-sm deleteApplication"
                            data-id="${row.id}">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    `;
                }
            }
        ],
        layout: {
            topStart: {
                pageLength: true
            },
            topEnd: {
                search: {
                    placeholder: ""
                }
            },
            bottomStart: {
                info: true
            },
            bottomEnd: {
                paging: true
            }
        },
        initComplete: function() {

            $('.dt-length').addClass('d-flex align-items-center gap-2');

            $('.dt-length').append(`
                <div class="d-flex align-items-center">
                    <div class="input-group input-group-sm" style="width:160px">
                        <span class="input-group-text bg-secondary text-white border-0 rounded-0">
                            From:
                        </span>
                        <input type="date"
                            value="{{ date('Y-m-d') }}"
                            class="form-control rounded-0"
                            id="date_from">
                    </div>

                    <div class="input-group input-group-sm" style="width:160px">
                        <span class="input-group-text bg-secondary text-white border-0 rounded-0">
                            To:
                        </span>
                        <input type="date"
                            value="{{ date('Y-m-d') }}"
                            class="form-control rounded-0"
                            id="date_to">
                    </div>

                    <button class="btn btn-secondary btn-sm rounded-0 px-4" style="height: 38px"
                        id="filterDate">
                    Filter
                </button>

                </div>
            `);

        }
    });

    DataTable.ext.search.push(function(settings, data, dataIndex) {

        if (settings.nTable.id !== 'biddingTable') {
            return true;
        }

        let from = $('#date_from').val();
        let to = $('#date_to').val();

        if (!from && !to) {
            return true;
        }

        // created_at column is the 6th column (index 5)
        let dateString = data[5];

        // extract the MM/DD/YYYY part
        dateString = dateString.split('|')[0].trim();

        let rowDate = new Date(dateString);

        if (from) {
            from = new Date(from);
            from.setHours(0, 0, 0, 0);
        }

        if (to) {
            to = new Date(to);
            to.setHours(23, 59, 59, 999);
        }

        return (!from || rowDate >= from) &&
            (!to || rowDate <= to);
    });

    $(document).on('click', '#filterDate', function() {
        table.draw();
    });

    $(document).on('click', ".deleteApplication", function() {

        let id = $(this).attr('data-id');

        Swal.fire({
            title: 'Delete this Record?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {

            if (result.isConfirmed) {

                $.ajax({
                    url: "{{ route('treasurer.deleteBilling') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: id
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: "Deleted Successfully"
                        }).then(() => {
                            table.draw();
                        });
                    },
                    error: function(xhr) {

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message ??
                                'Something went wrong.'
                        });

                    }
                });

            }

        });

    });
</script>
