<script>
    let typeFormReport = "";
    let typeTable = "";

    $(document).on("click", ".openNewReport", function() {
        typeFormReport = $(this).data("type");
        typeTable = $(this).data("table") ?? "";
        $(".reportform")[0].reset();
        $(".reportform input[name='report_id']").val(0);
        $(`#${typeFormReport}Modal`).modal("show");
        $(".btn-update-button").html("Submit");
    })

    $(document).on('submit', '.reportform', function(e) {
        e.preventDefault();
        Swal.fire({
            title: `Saving..`,
            text: `Please wait...`,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        const formData = new FormData(this);
        let form = $(this);
        let typeLogin = form.find('input[name="typeLogin"]').val();
        saveupdateForm(formData);
    });

    $(document).on("click", ".editRecord", function() {
        formReset('reportform');
        console.log(typeFormReport);
        typeFormReport = $(this).data("type");

        if (!isStaff && typeFormReport != 'inventory') {
            $(".reportform").find("input, select, textarea").prop("disabled", true);
        }
        $(".btn-update-button").html("Submit");
        let record_id = $(this).data("record_id");
        typeTable = $(this).data("table") ?? "";

        $(`#${typeFormReport}Modal`).modal("show");

        let findData = reportFormData.find(x => x.record_id == record_id);
        populateForm(findData, "reportform", `_${typeFormReport}`)
    })

    function saveupdateForm(formData) {
        $.ajax({
            url: "{{ route('save_new_staffreport') }}",
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.status == "success") {
                    formReset('reportform');
                    $(`#${typeFormReport}Modal`).modal("hide");

                    if (typeTable == "") {
                        const fnName = `reload${typeFormReport}reportTable`;

                        // check if the function exists in the global scope
                        if (typeof window[fnName] === "function") {
                            window[fnName]();
                        }
                    } else if (typeTable == "staff") {
                        reloadstaffreportTable();
                    } else if (typeTable == "archive") {
                        reloadarchivedTable();
                    } else if (typeTable == "dashboard") {
                        reloadreportTable();
                    } else if (typeTable == "incident") {
                        reloadincidentreportTable();
                    } else if (typeTable == "situational") {
                        reloadsituationalreportTable();
                    } else if (typeTable == "progress") {
                        reloadprogressreportTable();
                    }
                    setTimeout(() => {
                        Swal.fire({
                            title: "Success",
                            text: response.message,
                            icon: "success",
                            allowOutsideClick: false
                        });
                    }, 500);

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
