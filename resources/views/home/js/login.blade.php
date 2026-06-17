<script>
    $(document).on('submit', '#login_form, #login_form_staff', function(e) {
        e.preventDefault();

        $('#error_login, #error_login_staff').addClass('d-none').text('');
        let form = $(this);
        let typeLogin = form.find('input[name="typeLogin"]').val();

        $.ajax({
            type: 'POST',
            url: '{{ route('login') }}',
            data: $(this).serialize(),
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.status === 'success') {
                    window.location = "{{ route('dashboard') }}";
                } else {
                    if (typeLogin == "STAFF") {
                        $('#error_login_staff').removeClass('d-none').text(response.message);
                    } else {
                        $('#error_login').removeClass('d-none').text(response.message);
                    }
                }
            },
            error: function(xhr) {
                $('#error_login').removeClass('d-none').text(
                    xhr.responseJSON?.message ?? 'An unexpected error occurred.'
                );
            }
        });
    });
</script>
