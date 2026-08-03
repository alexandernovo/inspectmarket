<script>
    function clearBiddingValidation() {
        $('#biddingForm [aria-invalid="true"]')
            .removeAttr('aria-invalid')
            .removeAttr('aria-describedby');
        $('#biddingForm .tenant-field-error').remove();
    }

    function showBiddingValidation(errors) {
        clearBiddingValidation();

        let firstInvalidField = null;

        $.each(errors, function(fieldName, messages) {
            const inputName = fieldName.replace(/\.(\d+)(?=\.|$)/g, '[$1]');
            const $field = $('#biddingForm [name="' + inputName + '"]').first();

            if (!$field.length) {
                return;
            }

            const errorId = 'bidding-error-' + fieldName.replace(/[^a-zA-Z0-9_-]/g, '-');
            const message = Array.isArray(messages) ? messages[0] : messages;

            $('<div>', {
                id: errorId,
                class: 'tenant-field-error',
                text: message
            }).insertAfter($field);

            $field.attr({
                'aria-invalid': 'true',
                'aria-describedby': errorId
            });

            if (!firstInvalidField) {
                firstInvalidField = $field;
            }
        });

        if (firstInvalidField) {
            firstInvalidField.trigger('focus');
        }
    }

    $(document).on('input change', '#biddingForm :input', function() {
        const errorId = $(this).attr('aria-describedby');

        if (errorId) {
            $('#' + errorId).remove();
            $(this).removeAttr('aria-invalid').removeAttr('aria-describedby');
        }
    });

    $(document).on('submit', '#biddingForm', function(e) {
        e.preventDefault();

        clearBiddingValidation();

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

                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            Swal.close();
                            showBiddingValidation(xhr.responseJSON.errors);
                            return;
                        }

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
