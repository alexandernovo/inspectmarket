<script>
    $(document).on("submit", "#userUpdateForm", function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        saveUpdateProfile(formData);
    })

    function saveUpdateProfile(formData) {
        $.ajax({
            url: "{{ route('updateProfile') }}",
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.status == "success") {
                    Swal.fire({
                        title: "Success",
                        text: response.message,
                        icon: "success",
                        allowOutsideClick: false,
                        confirmButtonText: "OK"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            location.reload(); // refresh the page
                        }
                    });
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

    $(document).on("change", "#profileInput", function(event) {
        const file = event.target.files[0];
        if (!file) return;

        // Prepare form data for upload
        let formData = new FormData();
        formData.append("profile", file);
        formData.append("_token", "{{ csrf_token() }}");

        // Upload asynchronously
        $.ajax({
            url: "{{ route('profileUpload') }}",
            method: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status === "success") {
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            $("#profilePreview").attr("src", e.target.result);
                        };
                        reader.readAsDataURL(file);
                    } else {
                        $("#profilePreview").attr("src", "");
                    }

                    Swal.fire({
                        title: "Success",
                        text: response.message,
                        icon: "success",
                        allowOutsideClick: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            location.reload(); // refresh the page
                        }
                    });
                } else if (response.status === "error") {
                    Swal.fire({
                        title: "Failed",
                        text: response.error,
                        icon: "error",
                        showCancelButton: false,
                    });
                }
            },
            error: function(xhr) {
                let errorMessage = "An unexpected error occurred.";
                Swal.fire({
                    title: "Failed",
                    text: errorMessage,
                    icon: "error",
                    showCancelButton: false,
                });
            }
        });
    });


    $(document).on("change", "#backgroundInput", function(event) {
        const file = event.target.files[0];
        if (!file) return;

        // Prepare form data for upload
        let formData = new FormData();
        formData.append("background", file);
        formData.append("_token", "{{ csrf_token() }}");

        // Upload asynchronously
        $.ajax({
            url: "{{ route('backgroundUpload') }}",
            method: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status === "success") {
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            $("#backgroundPreview").attr("src", e.target.result);
                        };
                        reader.readAsDataURL(file);
                    } else {
                        $("#backgroundPreview").attr("src", "");
                    }

                    Swal.fire({
                        title: "Success",
                        text: response.message,
                        icon: "success",
                        allowOutsideClick: false
                    });
                } else if (response.status === "error") {
                    Swal.fire({
                        title: "Failed",
                        text: response.error,
                        icon: "error",
                        showCancelButton: false,
                    });
                }
            },
            error: function(xhr) {
                let errorMessage = "An unexpected error occurred.";
                Swal.fire({
                    title: "Failed",
                    text: errorMessage,
                    icon: "error",
                    showCancelButton: false,
                });
            }
        });
    });

    $(document).on("click", "#deleteCover", function(event) {
        Swal.fire({
            title: `Delete this Cover Photo?`,
            text: `Are you sure you want to delete this?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: "Delete"
        }).then((result) => {
            if (result.isConfirmed) {
                postRequest("{{ route('deleteCover') }}", {}, (response) => {
                    if (response.status == "success") {
                        $("#backgroundPreview").attr("src",
                            "{{ asset('assets/images/placeholder.png') }}");
                        Swal.fire({
                            title: "Success",
                            text: response.message,
                            icon: "success",
                            allowOutsideClick: false
                        });
                    }
                })
            }
        });
    });
</script>
