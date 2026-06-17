<script>
    let barChartIncidentChart;

    function barchartIncident(data, year) {
        const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        const months = Array.from({
            length: 12
        }, (_, i) => i + 1);

        const types = [...new Set(data.map(x => x.typeincident))];

        const seriesData = types.map(type => {
            return {
                name: type,
                data: months.map(month => {
                    const found = data.find(x => Number(x.month) === month && x.typeincident === type);
                    return found ? Number(found.total) : 0;
                })
            };
        });

        const categories = months.map(m => monthNames[m - 1]);

        // 🔹 Find the highest value among all data
        const allValues = seriesData.flatMap(s => s.data);
        const maxValue = allValues.length ? Math.max(...allValues) : 0;
        const yMax = maxValue + 1; // leave headroom

        let options = {
            series: seriesData,
            chart: {
                type: "bar",
                height: 400,
                stacked: false
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: "55%",
                    borderRadius: 6,
                    dataLabels: {
                        position: "center" // ✅ display inside bar
                    }
                }
            },
            dataLabels: {
                enabled: true,
                formatter: function(val) {
                    return val > 0 ? val : "";
                },
                style: {
                    fontSize: '12px',
                    fontWeight: 'bold',
                    colors: ['#fff'] // good contrast
                },
                offsetY: 0
            },
            xaxis: {
                categories: categories,
                labels: {
                    rotate: -45,
                    style: {
                        fontSize: '12px'
                    }
                },
                title: {
                    text: `Incident Report - ${year}`
                }
            },
            yaxis: {
                min: 0,
                max: yMax,
                forceNiceScale: true,
                labels: {
                    formatter: function(val) {
                        return val.toFixed(0);
                    }
                }
            },
            tooltip: {
                shared: true,
                intersect: false
            },
            legend: {
                show: true,
                position: "top"
            }
        };


        // destroy previous chart before rendering new one
        if (barChartIncidentChart) {
            barChartIncidentChart.destroy();
        }

        barChartIncidentChart = new ApexCharts(document.querySelector("#incidentreportChart"), options);
        barChartIncidentChart.render();
    }

    function getIncidentReport(year) {
        postRequest("{{ route('dashboard.getIncidentReport') }}", {
            year: year
        }, (response) => {
            if (response.status == 'success') {
                barchartIncident(response.counts, year);
            }
        })
    }

    $(document).ready(function() {
        if ("{{ auth()->user()->usertype == 'ADMIN' }}") {
            let defaultYear = $("#yearSelect").val();
            getIncidentReport(defaultYear);

            $("#yearSelect").on("change", function() {
                let selectedYear = $(this).val();
                getIncidentReport(selectedYear);
            });
        }
    })
</script>
