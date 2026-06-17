$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

function postRequest(
    route,
    data = {},
    callback = null,
    errorCallback = null,
    isFormData = false
) {
    $.ajax({
        url: route,
        type: "POST",
        data: isFormData ? data : JSON.stringify(data),
        contentType: isFormData ? false : "application/json",
        process: isFormData ? false : true,
        success: function (response) {
            if (callback) {
                callback(response);
            } else {
                console.log("success");
            }
        },
        error: function (xhr, status, error) {
            let errorMessageGlobal = "An unexpected error occurred.";
            errorMessageGlobal = errorGetter(xhr, status);
            Swal.fire({
                title: "Failed",
                text: errorMessageGlobal,
                icon: "error",
                showCancelButton: false,
            });
            if (errorCallback) {
                errorCallback(error);
            } else {
                console.error("Error:", status, error);
            }
        },
    });
}
$(document).ready(function () {
    const $toggleBtn = $("#drop2");

    $toggleBtn.on("click", function (e) {
        e.preventDefault();

        const dropdown = bootstrap.Dropdown.getOrCreateInstance(this); // Bootstrap 5 API

        if ($toggleBtn.hasClass("show")) {
            dropdown.hide();
        } else {
            dropdown.show();
        }
    });
});
$(document).ready(function () {
    const $toggleBtn = $("#dropnotif");

    $toggleBtn.on("click", function (e) {
        e.preventDefault();

        const dropdown = bootstrap.Dropdown.getOrCreateInstance(this); // Bootstrap 5 API

        if ($toggleBtn.hasClass("show")) {
            dropdown.hide();
        } else {
            dropdown.show();
        }
    });
});

function formatDateToStr(rawDateTime, withTime = true) {
    var months = [
        "Jan",
        "Feb",
        "Mar",
        "Apr",
        "May",
        "Jun",
        "Jul",
        "Aug",
        "Sep",
        "Oct",
        "Nov",
        "Dec",
    ];

    if (
        rawDateTime !== null &&
        typeof rawDateTime === "string" &&
        rawDateTime.trim() !== ""
    ) {
        var dateTimeParts = rawDateTime.split(" ");
        var dateParts = dateTimeParts[0].split("-");
        var year = dateParts[0];
        var month = months[parseInt(dateParts[1], 10) - 1];
        var day = parseInt(dateParts[2], 10);

        // If time exists and user wants it
        if (dateTimeParts.length > 1 && withTime) {
            var timeParts = dateTimeParts[1].split(":");
            var hour = parseInt(timeParts[0], 10);
            var minute = timeParts[1];
            var period = "AM";

            if (hour >= 12) {
                period = "PM";
                if (hour > 12) hour -= 12;
            } else if (hour === 0) {
                hour = 12; // midnight case
            }

            return `${month} ${day}, ${year} ${hour}:${minute} ${period}`;
        }

        // Only date
        return `${month} ${day}, ${year}`;
    }
    return "";
}

function formatDatetimeLocalToStr(rawDateTime) {
    const months = [
        "Jan",
        "Feb",
        "Mar",
        "Apr",
        "May",
        "Jun",
        "Jul",
        "Aug",
        "Sep",
        "Oct",
        "Nov",
        "Dec",
    ];

    if (
        rawDateTime !== null &&
        typeof rawDateTime === "string" &&
        rawDateTime.trim() !== ""
    ) {
        const [datePart, timePart] = rawDateTime.split("T");

        if (!datePart || !timePart) return "";

        const [year, month, day] = datePart.split("-");
        let [hour, minute] = timePart.split(":");

        hour = parseInt(hour);
        const period = hour >= 12 ? "PM" : "AM";
        hour = hour % 12 || 12; // Convert 0 to 12 for 12 AM

        return `${months[parseInt(month) - 1]} ${parseInt(
            day
        )}, ${year} ${hour}:${minute} ${period}`;
    }

    return "";
}

function populateForm(data, formId, alias = "", callback = null) {
    $.each(data, function (key, value) {
        if (value === null) {
            value = "";
        }

        let field = $(`#${formId} #${key}${alias}, .${formId} #${key}${alias}`);
        if (field.length > 0) {
            if (field.is("textarea")) {
                field.val(value);
            } else if (field.attr("type") === "time") {
                if (
                    typeof value === "string" &&
                    value.match(/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}(\.\d+)?$/)
                ) {
                    value = value.substring(11, 16);
                    field.val(value);
                }
            } else if (field.attr("type") === "date") {
                if (
                    typeof value === "string" &&
                    value.match(/^\d{4}-\d{2}-\d{2}/)
                ) {
                    value = value.substring(0, 10);
                    field.val(value);
                }
            } else if (field.attr("type") === "datetime-local") {
                if (
                    typeof value === "string" &&
                    value.match(/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}(\.\d+)?$/)
                ) {
                    value = value.substring(0, 16).replace(" ", "T");
                    field.val(value);
                }
            } else if (field.is("select")) {
                field.val(value);
                if (field.hasClass("select_class")) {
                    field.trigger("change.select2");
                } else {
                    field.trigger("change");
                }
            } else if (field.is(":checkbox")) {
                field.prop(
                    "checked",
                    value === "1" || value === true || value === "Y"
                );
            } else if (field.is(":radio")) {
                field.filter(`[value="${value}"]`).prop("checked", true);
            } else {
                field.val(value);
            }
        }
    });

    if (typeof callback === "function") {
        callback();
    }
}

let currentStep = 1;

function goToStep(stepNumber) {
    const totalSteps = $(".steps-circle").length;
    currentStep = stepNumber;

    $(".steps-circle").each(function (index) {
        const button = $(this).find(".steps-btn");
        if (index < stepNumber - 1) {
            button.addClass("finished").removeClass("active");
        } else if (index === stepNumber - 1) {
            button.addClass("active").removeClass("finished");
        } else {
            button.removeClass("active finished");
        }
    });

    const percentage = ((stepNumber - 1) / (totalSteps - 1)) * 100;
    $(".progress-bar")
        .css("width", percentage + "%")
        .attr("aria-valuenow", percentage.toFixed(0));

    $(".step").addClass("d-none");
    $(".step-" + stepNumber).removeClass("d-none");
}

// $(".steps-btn").on("click", function () {
//     const stepNumber = $(this).closest(".steps-circle").index() + 1;
//     goToStep(stepNumber);
// });

// $(document).on("click", ".next-step", function () {
//     if (currentStep < $(".steps-circle").length) {
//         goToStep(currentStep + 1);
//     }
// });

// $(document).on("click", ".prev-step", function () {
//     if (currentStep > 1) {
//         goToStep(currentStep - 1);
//     }
// });

goToStep(currentStep);

function errorGetter(xhr, status) {
    try {
        const responseJson = JSON.parse(xhr.responseText);
        console.log(responseJson);

        // Prefer .message, fallback to .error
        if (responseJson.message) {
            return responseJson.message;
        }

        if (responseJson.error) {
            return responseJson.error;
        }

        return xhr.responseText;
    } catch (e) {
        if (xhr.responseText) {
            return xhr.responseText;
        } else {
            return status;
        }
    }
}

function getTimeAgo(datetime) {
    const now = new Date();
    const past = new Date(datetime);
    const diffInSeconds = Math.floor((now - past) / 1000);

    if (diffInSeconds < 60) {
        return diffInSeconds === 1
            ? "1 second ago"
            : `${diffInSeconds} seconds ago`;
    } else if (diffInSeconds < 3600) {
        const minutes = Math.floor(diffInSeconds / 60);
        return minutes === 1 ? "1 minute ago" : `${minutes} minutes ago`;
    } else if (diffInSeconds < 86400) {
        const hours = Math.floor(diffInSeconds / 3600);
        return hours === 1 ? "1 hour ago" : `${hours} hours ago`;
    } else {
        const days = Math.floor(diffInSeconds / 86400);
        return days === 1 ? "1 day ago" : `${days} days ago`;
    }
}

const tooltipTriggerList = document.querySelectorAll(
    '[data-bs-toggle="tooltip"]'
);
const tooltipList = [...tooltipTriggerList].map(
    (tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl)
);

const toastElList = document.querySelectorAll(".toast");
const toastList = [...toastElList].map(
    (toastEl) => new bootstrap.Toast(toastEl, option)
);

function updateTimeAgoBaseOnClass() {
    $(".datetime-update").each(function () {
        const date = $(this).data("date");
        if (date) {
            $(this).text(getTimeAgo(date));
        }
    });
}

function cloneObject(obj) {
    return JSON.parse(JSON.stringify(obj));
}
function statusBadgeRenderer(data) {
    if (data === "Paid") {
        return `<span class="badge bg-success text-white px-3 py-1 rounded-pill">Paid</span>`;
    } else if (data === "Pending") {
        return `<span class="badge bg-warning text-white px-3 py-1 rounded-pill">Pending</span>`;
    } else if (data) {
        return `<span class="badge bg-secondary text-white px-3 py-1 rounded-pill">${data}</span>`;
    } else {
        return `<span class="badge bg-light text-dark px-3 py-1 rounded-pill">N/A</span>`;
    }
}

function statusBadge(data) {
    if (data === "ACTIVE") {
        return `<span class="badge bg-green text-white px-3 py-1 rounded-pill" style="font-size: 12px; border-radius: 7px !important; padding: 7px 14px !important">Active</span>`;
    } else {
        return `<span class="badge bg-red text-white px-3 py-1 rounded-pill" style="font-size: 12px; border-radius: 7px !important; padding: 7px 14px !important">Inactive</span>`;
    }
}

function formReset(formId) {
    $(`#${formId}, .${formId}`)[0].reset();
    $(`#${formId} input[type='hidden'], .${formId} input[type='hidden']`).val(
        0
    );
}

function formatRecordType(type) {
    const map = {
        SITUATIONALREPORT: "Situational Report",
        INCIDENTREPORT: "Incident Report",
        INVENTORYREPORT: "Inventory Report",
        PROGRESSREPORT: "Progress Report",
    };

    return map[type] || type;
}

$(document).on("click", ".file-link", function (e) {
    e.preventDefault(); // prevent default navigation
    console.log("hello");
    const fileUrl = $(this).attr("href");
    const ext = fileUrl.split(".").pop().toLowerCase();

    if (ext === "pdf") {
        $("#filePreview").attr("src", fileUrl);
        const fileModal = new bootstrap.Modal($("#fileModal")[0]);
        fileModal.show();
    } else {
        // For other files, download
        const a = document.createElement("a");
        a.href = fileUrl;
        a.download = "";
        a.click();
    }
});

function togglePasswordVisibility(targetId, icon) {
    const input = document.getElementById(targetId);

    if (!input) return;

    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("bi-eye-fill");
        icon.classList.add("bi-eye-slash-fill");
    } else {
        input.type = "password";
        icon.classList.remove("bi-eye-slash-fill");
        icon.classList.add("bi-eye-fill");
    }
}

// Attach event listener for all icons with data-target
document.querySelectorAll(".toggle-password").forEach((icon) => {
    icon.addEventListener("click", () => {
        togglePasswordVisibility(icon.dataset.target, icon);
    });
});
