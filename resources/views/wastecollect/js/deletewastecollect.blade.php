<script>
    $(document).on("click", "#deletewastecollectBtn", function() {
        var selectedRow = wastecollectTable.row('.selected');

        if (selectedRow.node()) {
            var data = selectedRow.data();
            if (data) {
                Swal.fire({
                    title: "Warning",
                    text: "Delete this Waste Collection Record?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: 'Delete'
                }).then((result) => {
                    if (result.isConfirmed) {
                        postRequest("{{ route('deletewastecollect') }}", {
                            wastecollect_id: data.wastecollect_id
                        }, (response) => {
                            if (response.status == "success") {
                                reloadwastecollectTable();
                                Swal.fire({
                                    title: "Success",
                                    text: response.message,
                                    icon: "success",
                                    showCancelButton: false,
                                })
                            }
                        })
                    }
                });
            }
        } else {
            Swal.fire({
                title: "Warning",
                text: "Please Select a Row First",
                icon: "warning",
            });
        }
    })
</script>
