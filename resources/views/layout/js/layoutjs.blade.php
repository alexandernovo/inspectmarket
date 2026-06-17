<script>
    $(document).on("click", ".logout-btn", function() {
        Swal.fire({
            title: "Logout?",
            text: "Are you sure you want to logout?",
            icon: 'question',
            imageWidth: 128,
            imageHeight: 128,
            showCancelButton: true,
            confirmButtonText: "Yes, Logout",
            cancelButtonText: "No, Stay Login",
            reverseButtons: false,
            backdrop: true,
            allowOutsideClick: false,
            customClass: {
                confirmButton: 'btn btn-prime me-2',
                cancelButton: 'btn btn-gray-new'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                let timerInterval;
                return Swal.fire({
                    title: "Log out",
                    html: "Logging out in <b></b> seconds",
                    timer: 2000,
                    timerProgressBar: true,
                    didOpen: () => {
                        Swal.showLoading();
                        const timer = Swal.getHtmlContainer().querySelector('b');
                        timerInterval = setInterval(() => {
                            const secondsLeft = Math.ceil(Swal.getTimerLeft() /
                                1000);
                            timer.textContent = `${secondsLeft}`;
                        }, 100);
                    },
                    willClose: () => {
                        clearInterval(timerInterval);
                    }
                }).then((result) => {
                    if (result.dismiss === Swal.DismissReason.timer) {
                        postRequest("{{ route('auth.logout') }}", {}, (response) => {
                            if (response.status == "success") {
                                window.location.href = "{{ route('home') }}";
                            }
                        })
                    }
                });
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                console.log("Logout canceled");
            }
        });
    });

    $(document).ready(function() {
        $('.toast').each(function() {
            var toast = new bootstrap.Toast(this);
            toast.show();
        });
    });

    function getCountIncident() {
        if (!isStaff) {

            postRequest("{{ route('sidebarCounts') }}", {}, (response) => {
                if (response.status == "success") {
                    $("#incidentCountId").append(
                        `<p id="incidentCountIdBadge" class="notifCount mb-0 position-absolute d-flex justify-content-center align-items-center rounded-circle">${response.incidentReport}</p>`
                    );

                    $("#situationalCountId").append(`
                            <p id="situationalCountIdBadge" class="notifCount mb-0 position-absolute d-flex justify-content-center align-items-center rounded-circle">${response.situationReport}</p>
                    `);

                    $("#progressCountId").append(`
                            <p id="progressCountIdBadge" class="notifCount mb-0 position-absolute d-flex justify-content-center align-items-center rounded-circle">${response.progressReport}</p>
                    `);
                }
            })
        }
    }

    $(document).ready(function() {
        if (!isStaff) {
            updateCount0(() => {
                getCountIncident(); // load counts AFTER updating DB
            });
        }
        if ({{ !in_array(Route::currentRouteName(), $excludedRoutes) }}) {
            getCountIncidentFunc(); // start the loop
        }
    });



    function getCountIncidentFunc() {
        if (!isStaff) {
            // setInterval(() => {
                getCountIncident();
            // }, 3000);
        }
    }

    function updateCount0(callback) {
        let routeName = "{{ Route::currentRouteName() }}";

        if (!isStaff) {

            let type = null;

            if (routeName === "progressreport_view") type = "PROGRESSREPORT";
            if (routeName === "incidentreport_view") type = "INCIDENTREPORT";
            if (routeName === "situationalreport_view") type = "SITUATIONALREPORT";

            if (type) {
                postRequest("{{ route('updateCountsActive') }}", {
                    type
                }, (response) => {
                    if (response.status == "success") {
                        if (typeof callback == "function") callback();
                    }
                });
            }
        }
    }
</script>
