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

    // ✅ Excel Export
    function downloadExcel(data) {
        let sheetData = [
            ["No", "Barangay", "Affected Families", "Individuals", "Evac Families", "Evac Individuals", "Remarks", "Clearing Operations", "Date Submitted"]
        ];

        data.forEach((row, i) => {
            sheetData.push([
                i + 1,
                row.barangay,
                row.affectedfamilies,
                row.individuals,
                row.evacuationfamilies,
                row.evacuationindividuals,
                row.remarks,
                row.clearingoperations,
                row.created_at
            ]);
        });

        let wb = XLSX.utils.book_new();
        let ws = XLSX.utils.aoa_to_sheet(sheetData);
        XLSX.utils.book_append_sheet(wb, ws, "Progress Report");
        XLSX.writeFile(wb, "progress_report.xlsx");
    }

    // ✅ Word Export
    function downloadWord(data) {
        let content =
            "<h3>Progress Report</h3><table border='1' cellspacing='0' cellpadding='5'>" +
            "<tr><th>No</th><th>Barangay</th><th>Affected Families</th><th>Individuals</th><th>Evac Families</th><th>Evac Individuals</th><th>Remarks</th><th>Clearing Operations</th><th>Date Submitted</th></tr>";

        data.forEach((row, i) => {
            content += `<tr>
                <td>${i + 1}</td>
                <td>${row.barangay}</td>
                <td>${row.affectedfamilies}</td>
                <td>${row.individuals}</td>
                <td>${row.evacuationfamilies}</td>
                <td>${row.evacuationindividuals}</td>
                <td>${row.remarks}</td>
                <td>${row.clearingoperations}</td>
                <td>${row.created_at}</td>
            </tr>`;
        });

        content += "</table>";

        let blob = new Blob(['\ufeff', content], { type: 'application/msword' });
        let url = URL.createObjectURL(blob);
        let a = document.createElement("a");
        a.href = url;
        a.download = "progress_report.doc";
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }

    // ✅ PDF Export
    function downloadPDF() {
        const element = document.getElementById("printable");
        const opt = {
            margin: 0.5,
            filename: "progress_report.pdf",
            image: { type: "jpeg", quality: 0.98 },
            html2canvas: { scale: 2 },
            jsPDF: { unit: "in", format: "a4", orientation: "landscape" } // landscape better for wide table
        };
        html2pdf().set(opt).from(element).save();
    }
</script>
