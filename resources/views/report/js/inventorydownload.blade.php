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
            ["No", "Quantity", "Unit", "Description", "Property Number", "Date Acquired", "Amount"]
        ];

        data.forEach((row, i) => {
            sheetData.push([
                i + 1,
                row.quantity,
                row.unit,
                row.description,
                row.propertyno,
                row.dateacquired, // already formatted in Blade if needed
                row.amount
            ]);
        });

        let wb = XLSX.utils.book_new();
        let ws = XLSX.utils.aoa_to_sheet(sheetData);
        XLSX.utils.book_append_sheet(wb, ws, "Inventory Report");
        XLSX.writeFile(wb, "inventory_report.xlsx");
    }

    // ✅ Word Export
    function downloadWord(data) {
        let content =
            "<h3>Inventory Report</h3><table border='1' cellspacing='0' cellpadding='5'>" +
            "<tr><th>No</th><th>Quantity</th><th>Unit</th><th>Description</th><th>Property Number</th><th>Date Acquired</th><th>Amount</th></tr>";

        data.forEach((row, i) => {
            content += `<tr>
                <td>${i + 1}</td>
                <td>${row.quantity}</td>
                <td>${row.unit}</td>
                <td>${row.description}</td>
                <td>${row.propertyno}</td>
                <td>${row.dateacquired}</td>
                <td>${row.amount}</td>
            </tr>`;
        });

        content += "</table>";

        let blob = new Blob(['\ufeff', content], { type: 'application/msword' });
        let url = URL.createObjectURL(blob);
        let a = document.createElement("a");
        a.href = url;
        a.download = "inventory_report.doc";
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }

    // ✅ PDF Export
    function downloadPDF() {
        const element = document.getElementById("printable");
        const opt = {
            margin: 0.5,
            filename: "inventory_report.pdf",
            image: { type: "jpeg", quality: 0.98 },
            html2canvas: { scale: 2 },
            jsPDF: { unit: "in", format: "a4", orientation: "portrait" }
        };
        html2pdf().set(opt).from(element).save();
    }
</script>
