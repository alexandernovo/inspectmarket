<script>
    $(document).on('submit', '#contactForm', function(e) {
        e.preventDefault();
        Swal.fire({
            title: `Sending`,
            text: `Sending Message to Tibiao MDRRMO`,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        const formData = new FormData(this);
        sendContactMessage(formData);
    });

    function sendContactMessage(formData) {
        $.ajax({
            url: "{{ route('contact_message') }}",
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.status == "success") {
                    $(`#contactForm`)[0].reset();
                    Swal.fire({
                        title: "Success",
                        text: "Message Sent Successfully!",
                        icon: "success",
                        allowOutsideClick: false
                    });
                }
                if (response.status == "error") {
                    Swal.fire({
                        title: "Failed",
                        text: error.error,
                        icon: "error",
                        showCancelButton: false,
                    })
                }
            },
            error: function(xhr, status, error) {
                let errorMessage = "An unexpected error occurred.";
                errorMessage = errorGetter(xhr, status);
                Swal.fire({
                    title: "Failed",
                    text: errorMessage,
                    icon: "error",
                    showCancelButton: false,
                });
            }
        });
    }
</script>
