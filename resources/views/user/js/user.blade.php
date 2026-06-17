<script>
    let userOptions;
    let userTable;
    let userData = [];
    let selecteduserId = null;

    userOptions = {
        processing: false,
        serverSide: true,
        ajax: {
            url: "{{ route('getuser') }}",
            type: 'POST',
            dataType: 'json',
            data: function(d) {
                d._token = '{{ csrf_token() }}';
            },
            dataSrc: function(json) {
                userData = json.data;
                return json.data;
            }
        },
        columns: [{
                title: 'No.',
                className: 'text-nowrap p-3 text-center align-middle',
                render: function(data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            {
                title: 'Name',
                className: 'text-nowrap p-3 align-middle text-center',
                render: function(data, type, row) {
                    return row.fullname;
                }
            },
            {
                title: 'Username',
                className: 'text-nowrap p-3 align-middle text-center',
                render: function(data, type, row) {
                    return row.username;
                }
            },
            {
                title: 'Designation',
                className: 'text-nowrap p-3 align-middle text-center',
                render: function(data, type, row) {
                    return row.designation;
                }
            },
            {
                title: 'Email',
                className: 'text-nowrap p-3 align-middle text-center',
                render: function(data, type, row) {
                    return row.email;
                }
            },
            {
                title: 'Address',
                className: 'text-nowrap p-3 align-middle text-center',
                render: function(data, type, row) {
                    return row.address;
                }
            },
            {
                title: 'Phone Number',
                className: 'text-nowrap p-3 align-middle text-center',
                render: function(data, type, row) {
                    return row.phone_num;
                }
            },
            {
                title: 'Status',
                data: 'status',
                className: 'text-nowrap p-3 align-middle text-center',
                render: statusBadge
            },
            {
                title: 'Date Created',
                className: 'text-nowrap p-3 align-middle text-center',
                render: function(data, type, row) {
                    return formatDatetimeLocalToStr(row.created_at);
                }
            },
            {
                title: 'Action',
                className: 'text-nowrap p-3 align-middle text-center sticky-action',
                render: function(data, type, row) {
                    return `
                        <div class="d-flex gap-2">
                            <button style="width: 120px" class="btn deactivate_user ${row.status=="ACTIVE" ? 'btn-red' : 'btn-green'}" data-user_id="${row.id}" data-status="${row.status}"> 
                                <i class="bi bi-power"></i>
                                ${row.status=="ACTIVE" ? 'Deactivate' : 'Activate'}
                            </button>

                            <button style="padding: 4px 8px" class="btn btn-red delete_user" data-user_id="${row.id}" data-status="${row.status}"> 
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        initComplete: function(settings, json) {
            appendButtonsuser();
            $('[data-bs-toggle="tooltip"]').tooltip();
        }
    };

    $(document).ready(function() {
        renderuserTable();
    })

    function renderuserTable() {
        if (userTable) {
            userTable.destroy();
        }
        userTable = new DataTable('#userTable', userOptions)
    }

    $(document).on("click", "#reloaduserBtn", function() {
        reloadButtonLoading(true);
        reloaduserTable();
        setTimeout(() => {
            reloadButtonLoading(false);
        }, 500);
    });

    function reloaduserTable() {
        if (userTable) {
            userTable.ajax.reload(null, false);
        } else {
            renderuserTable();
        }
    }

    function reloaduserTableWithPagination() {
        if (userTable) {
            userTable.ajax.reload(null, true);
        } else {
            renderuserTable();
        }
    }

    function appendButtonsuser() {
        $('#userTable_wrapper .row .dt-length').append(`
            <div class="d-flex gap-2 ms-2 align-items-center userBtnSm">
           
                <button class="btn btn-gray-new d-flex flex-nowrap align-items-center gap-2" id="reloaduserBtn">
                    <span>
                        <i class="bi bi-arrow-clockwise"></i>
                    </span>
                    Reload
                </button>
            </div>
        `);
    }
    //  <button class="btn btn-success d-flex flex-nowrap align-items-center gap-2" id="newStaff">
    //     <span>
    //         <i class="bi bi-person-fill-add"></i>
    //     </span>
    //     New Staff
    // </button>

    //     <button class="btn btn-info edit_user" data-user_id="${row.id}">
    //     <i class="bi bi-pencil-square"></i>
    // </button>
    function reloadButtonLoading(isLoading) {
        if (isLoading) {
            $("#reloaduserBtn").html(`
                    <div class="spinner-border text-white" role="status" style="width: 14px; height: 14px">
                </div>
                Reloading
            `);
        } else {
            $("#reloaduserBtn").html(`
                <i class="bi bi-arrow-clockwise"></i>
                Reload
            `);
        }
    }

    $(document).on('click', '#newStaff', function() {
        $("#password, #confirm_password").attr("required", true);
        formReset("userForm");
        $(".tipPassword").addClass("d-none");
        $("#userModal").modal("show");
    });

    $(document).on('click', '.edit_user', function() {
        $("#password, #confirm_password").attr("required", false);
        let user_id = $(this).data("user_id");
        $(".tipPassword").removeClass("d-none");
        formReset("userForm");

        let user_data = userData.find(x => x.id == user_id);
        if (user_data) {
            populateForm(user_data, "userForm")
        }
        $("#userModal").modal("show");
    });

    $(document).on('click', '.delete_user', function() {
        let user_id = $(this).data("user_id");
        Swal.fire({
            title: `Delete?`,
            text: `Are you sure you want to Delete this staff?`,
            icon: 'warning',
            confirmButtonText: "Delete",
            showCancelButton: true
        }).then((result) => {
            if (result.isConfirmed) {
                postRequest("{{ route('deletestaff') }}", {
                    id: user_id,
                    status: status
                }, (response) => {
                    if (response.status == "success") {
                        Swal.fire({
                            title: `Success`,
                            text: `Staff Deleted Successfully`,
                            allowOutsideClick: false,
                        });
                        reloaduserTable();
                    }
                })
            }
        });
    });

    $(document).on('click', '.deactivate_user', function() {

        let user_id = $(this).data("user_id");
        let status = $(this).attr("data-status");
        let message = status == "ACTIVE" ? "Deactivate" : "Activate";
        Swal.fire({
            title: `${message}?`,
            text: `Are you sure you want to ${message} this staff?`,
            icon: 'question',
            showCancelButton: true
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: ` ${status == "ACTIVE" ? "Deactivating" : "Activating"}`,
                    text: `Sending Email and ${status == "ACTIVE" ? "Deactivating" : "Activating"} this Account...`,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                postRequest("{{ route('activatedeactivate') }}", {
                    id: user_id,
                    status: status
                }, (response) => {
                    if (response.status == "success") {
                        Swal.fire({
                            title: `Success`,
                            text: `${status == "ACTIVE" ? "Deactivated Successfully" : "Activated Successfully"}`,
                            allowOutsideClick: false,
                        });
                        reloaduserTable();
                    }
                })
            }
        });
    });

    $(document).on('submit', '#userForm', function(e) {
        e.preventDefault();

        if ($("#password").val() != $("#confirm_password").val()) {
            $("#errorPassword").removeClass("d-none");
            return;
        } else {
            $("#errorPassword").addClass("d-none");
        }

        const formData = new FormData(this);
        saveUpdateUser(formData);
    });

    function saveUpdateUser(formData) {
        $.ajax({
            url: "{{ route('save_new_user') }}",
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.status == "success") {
                    $("#userModal").modal("hide");
                    reloaduserTable();
                    Swal.fire({
                        title: "Success",
                        text: response.message,
                        icon: "success",
                        allowOutsideClick: false
                    });
                }
                if (response.status == "error") {
                    Swal.fire({
                        title: "Failed",
                        text: error.error,
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
