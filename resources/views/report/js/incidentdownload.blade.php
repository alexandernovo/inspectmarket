<script>
    $(document).on("click", ".download-btn", function() {
        let type = $(this).data("type");

        if (reportData.length === 0) {
            alert("No data available to download.");
            return;
        }

        if (type === "excel") {
            downloadExcel(reportData);
        } else if (type === "word") {
            downloadWord(reportData);
        } else if (type === "pdf") {
            downloadPDF();
        }
    });

    function downloadExcel(data) {
        // Define headers
        let sheetData = [
            ["No", "Type of Incident", "Date & Time of Occurrence", "Barangay", "Specific Location",
                "Detailed Description", "Injured", "Dead", "Date Submitted"
            ]
        ];

        // Push rows
        data.forEach((row, i) => {
            sheetData.push([
                i + 1,
                row.fullname, // Assuming fullname = type of incident (adjust if different)
                row.datetimeoccurence,
                row.barangay,
                row.specificlocation,
                row.detaileddesc,
                row.involvedinjured,
                row.involveddead,
                row.created_at
            ]);
        });

        // Create workbook
        let wb = XLSX.utils.book_new();
        let ws = XLSX.utils.aoa_to_sheet(sheetData);
        XLSX.utils.book_append_sheet(wb, ws, "Incident Report");

        // Download
        XLSX.writeFile(wb, "incident_report.xlsx");
    }

    function downloadWord(data) {
        let content = `
            <h3>Incident Report</h3>
            <table border='1' cellspacing='0' cellpadding='5'>
                <tr>
                    <th>No</th>
                    <th>Type of Incident</th>
                    <th>Date & Time of Occurrence</th>
                    <th>Barangay</th>
                    <th>Specific Location</th>
                    <th>Detailed Description</th>
                    <th>Injured</th>
                    <th>Dead</th>
                    <th>Date Submitted</th>
                </tr>
        `;

        data.forEach((row, i) => {
            content += `
                <tr>
                    <td>${i + 1}</td>
                    <td>${row.fullname}</td>
                    <td>${row.datetimeoccurence}</td>
                    <td>${row.barangay}</td>
                    <td>${row.specificlocation}</td>
                    <td>${row.detaileddesc}</td>
                    <td>${row.involvedinjured}</td>
                    <td>${row.involveddead}</td>
                    <td>${row.created_at}</td>
                </tr>`;
        });

        content += "</table>";

        let blob = new Blob(['\ufeff', content], {
            type: 'application/msword'
        });
        let url = URL.createObjectURL(blob);
        let a = document.createElement("a");
        a.href = url;
        a.download = "incident_report.doc";
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }

    function downloadPDF() {
        const element = document.getElementById("printable");
        const opt = {
            margin: 0.5,
            filename: "incident_report.pdf",
            image: {
                type: "jpeg",
                quality: 0.98
            },
            html2canvas: {
                scale: 2
            },
            jsPDF: {
                unit: "in",
                format: "a4",
                orientation: "portrait"
            }
        };
        html2pdf().set(opt).from(element).save();
    }
</script>
