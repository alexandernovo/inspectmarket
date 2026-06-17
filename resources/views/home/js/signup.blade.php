<script>
    $(document).on('submit', '#signup_form_staff', function(e) {
        e.preventDefault();

        if ($("#password_var").val() != $("#confirm_password").val()) {
            $("#errorPassword").removeClass("d-none");
            return;
        } else {
            $("#errorPassword").addClass("d-none");
        }

        const formData = new FormData(this);
        saveUpdateUser(formData);
    });

    function saveUpdateUser(formData) {
        $.ajax({
            url: "{{ route('save_new_user') }}",
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.status == "success") {
                    $(`#signup_form_staff`)[0].reset();
                    Swal.fire({
                        title: "Success",
                        text: "Sign-up successful! Please wait for an admin to activate your account. You will be notified via email.",
                        icon: "success",
                        allowOutsideClick: false
                    });
                }
                if (response.status == "failed") {
                    Swal.fire({
                        title: "Failed",
                        text: response.message,
                        icon: "error",
                        showCancelButton: false,
                    })
                }
                if (response.status == "error") {
                    Swal.fire({
                        title: "Failed",
                        text: response.error,
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
