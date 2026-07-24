<script>
    new DataTable('#biddingTable', {
        processing: true,
        serverSide: false,
        ordering: false,
        responsive: true,
        ajax: {
            url: "{{ route('tenant.bidding-applications.data') }}",
            dataSrc: ''
        },
        columns: [{
                data: null,
                className: 'text-center',
                render: function(data, type, row, meta) {
                    return meta.row + 1;
                }
            },
            {
                className: 'text-center',
                data: 'name_owner',
                render: function(data, type, row) {
                    return row.name_owner;
                }
            },
            {
                className: 'text-center',
                data: 'created_at',
                render: function(data, type, row) {

                    const date = new Date(row.created_at);

                    return date.toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });

                }
            },
            {
                data: null,
                className: 'text-center',
                render: function(data, type, row) {

                    return `
                    <a href="{{ route('tenant.bidding_request.view') }}?application_id=${row.id}"
                        class="btn btn-sm btn-view-details view-bidding"
                        data-id="${row.id}">
                        <i class="bi bi-eye-fill"></i>
                        View Details
                    </a>
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
                    placeholder: 'Search...'
                }
            },
            bottomStart: 'info',
            bottomEnd: 'paging'
        },
        language: {
            emptyTable: "No bidding requests found."
        }
    });
</script>
