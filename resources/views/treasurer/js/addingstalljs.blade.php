<script>
    $(document).on('submit', "#stallForm", function(e) {
        e.preventDefault();

        $.ajax({
            url: "{{ route('treasurer.storeStall') }}",
            type: "POST",
            data: $(this).serialize(),
            beforeSend: function() {
                $('#saveStall')
                    .prop('disabled', true)
                    .text('Saving...');
            },
            success: function(response) {

                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.message
                }).then(() => {
                    window.location.reload();
                });

            },
            error: function(xhr) {

                $('#saveStall')
                    .prop('disabled', false)
                    .text('Add Stall');

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message ?? 'Something went wrong.'
                });

            }
        });

    });
</script>
