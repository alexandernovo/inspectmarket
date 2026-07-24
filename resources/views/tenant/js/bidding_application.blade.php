<script>
    $(document).on('submit', '#biddingForm', function(e) {
        e.preventDefault();

        Swal.fire({
            title: 'Submit Application?',
            text: 'Are you sure you want to submit this bidding application?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Submit',
            cancelButtonText: 'Cancel'
        }).then((result) => {

            if (result.isConfirmed) {

                $.ajax({
                    url: "{{ route('tenant.storeBiddingApplication') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        Swal.fire({
                            title: 'Saving...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    },
                    success: function(response) {

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message
                        });

                        $('#biddingForm')[0].reset();

                    },
                    error: function(xhr) {

                        let message = 'Something went wrong.';

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: message
                        });

                    }
                });

            }

        });

    });
</script>
