<script>
    $(document).on("click", "#viewMonthlyReportBtn", function() {
        let typeReport = $("#typeMonthlyReport").val();
        if (!typeReport) {
            Swal.fire({
                title: `Warning`,
                text: `Please Select Document Report Type!`,
                icon: 'warning',
                showCancelButton: false,
            })

            return;
        }

        if (!$("#monthMonthlyReport").val()) {
            Swal.fire({
                title: `Warning`,
                text: `Please Select Month!`,
                icon: 'warning',
                showCancelButton: false,
            })

            return;
        }

        let route = "";

        if (typeReport == "INCIDENT REPORT") {
            route = "{{ route('incidentreportPrint') }}";
        }
        if (typeReport == "SITUATIONAL REPORT") {
            route = "{{ route('situationalreportPrint') }}";
        }
        if (typeReport == "PROGRESS REPORT") {
            route = "{{ route('progressreportPrint') }}";
        }

        window.location.href = `${route}?monthyear=${$("#monthMonthlyReport").val()}`
    })
</script>
