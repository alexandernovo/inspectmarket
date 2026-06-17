<script>
    $(document).on("click", "#newwastecollectBtn", function() {
        $("#newwastecollectModalLabel").text("New Waste Collection");
        resetWasteCollect();
        $("#newwastecollectModal").modal("show");
    });

    $(document).on("click", "#editwastecollectBtn", function() {
        $("#newwastecollectModalLabel").text("Edit Waste Collection");
        var selectedRow = wastecollectTable.row('.selected');
        resetWasteCollect();

        if (selectedRow.node()) {
            var data = selectedRow.data();
            if (data) {
                populateForm(data, "newwastecollectform");
                $("#newwastecollectModal").modal("show");
            }
        } else {
            Swal.fire({
                title: "Warning",
                text: "Please Select a Row First",
                icon: "warning",
            });
        }
    })

    $(document).on("submit", "#newwastecollectform", function(e) {
        e.preventDefault();

        let formData = {
            wastecollect_id: $("#wastecollect_id").val(),
            barangay: $("#barangay").val(),
            schedule_from: $("#schedule_from").val(),
            schedule_to: $("#schedule_to").val(),
            recyclable: $("#recyclable").val(),
            biodegradable: $("#biodegradable").val(),
            nonbio: $("#nonbio").val(),
            specialwaste: $("#specialwaste").val(),
        };

        postRequest("{{ route('save_new_wastecollect') }}", formData, (response) => {
            if (response.status == "success") {
                reloadwastecollectTable();
                $("#newwastecollectModal").modal("hide");
                Swal.fire({
                    title: "Success",
                    text: response.message,
                    icon: "success",
                    showCancelButton: false,
                })
            }
        });

    });

    function resetWasteCollect() {
        $("#newwastecollectform")[0].reset();
        $("#newwastecollectform input[type='hidden']").val(0);
    }
</script>
