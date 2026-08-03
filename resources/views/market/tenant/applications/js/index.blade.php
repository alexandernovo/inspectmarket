<script>
    let tenantApplicationTable;
    let tenantApplicationDateFrom = '';
    let tenantApplicationDateTo = '';
    let tenantApplicationSection = 'ALL';

    $(document).ready(function () {
        tenantApplicationTable = $('#tenantApplicationTable').DataTable({
            processing: true,
            serverSide: true,
            order: [[6, 'desc']],
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50],
            ajax: {
                url: "{{ route('tenant.datatable.applications') }}",
                type: 'GET',
                data: function (data) {
                    data.dateFrom = tenantApplicationDateFrom;
                    data.dateTo = tenantApplicationDateTo;
                    data.section = tenantApplicationSection;
                }
            },
            columns: [
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                { data: 'tin_number', orderable: false },
                { data: 'owner_name', orderable: false },
                { data: 'address', orderable: false },
                { data: 'contact', orderable: false },
                { data: 'status', orderable: false },
                { data: 'submitted' },
                { data: 'action', orderable: false, searchable: false }
            ],
            initComplete: function () {
                renderTenantApplicationToolbar();
            }
        });
    });

    function renderTenantApplicationToolbar() {
        $('.tenant-application-toolbar').html(`
            <div class="tenant-table-filters">
                <select id="tenantApplicationLength" aria-label="Rows per page">
                    <option value="5">5 v</option>
                    <option value="10" selected>10 v</option>
                    <option value="25">25 v</option>
                    <option value="50">50 v</option>
                </select>
                <label>From:<input type="date" id="tenantApplicationDateFrom"></label>
                <label>To:<input type="date" id="tenantApplicationDateTo"></label>
                <select id="tenantApplicationSection">
                    <option value="ALL">All Sections</option>
                    <option value="FISH">Fish</option>
                    <option value="PORK">Pork</option>
                    <option value="POULTRY">Poultry</option>
                    <option value="BEEF">Beef</option>
                    <option value="MIXED">Mixed</option>
                </select>
                <button type="button" class="button" id="filterTenantApplications"><i class="bi bi-funnel-fill"></i> Filter</button>
                <button type="button" class="button" id="reloadTenantApplications"><i class="bi bi-arrow-clockwise"></i> Reload</button>
                <label class="tenant-table-search">Search:<input type="search" id="tenantApplicationSearch"></label>
            </div>
        `);
    }

    $(document).on('change', '#tenantApplicationLength', function () {
        tenantApplicationTable.page.len(Number($(this).val())).draw();
    });

    $(document).on('input', '#tenantApplicationSearch', function () {
        tenantApplicationTable.search($(this).val()).draw();
    });

    $(document).on('click', '#filterTenantApplications', function () {
        tenantApplicationDateFrom = $('#tenantApplicationDateFrom').val();
        tenantApplicationDateTo = $('#tenantApplicationDateTo').val();
        tenantApplicationSection = $('#tenantApplicationSection').val();
        tenantApplicationTable.ajax.reload();
    });

    $(document).on('click', '#reloadTenantApplications', function () {
        tenantApplicationDateFrom = '';
        tenantApplicationDateTo = '';
        tenantApplicationSection = 'ALL';
        $('#tenantApplicationDateFrom, #tenantApplicationDateTo, #tenantApplicationSearch').val('');
        $('#tenantApplicationSection').val('ALL');
        tenantApplicationTable.search('').ajax.reload();
    });

    function openTenantApplicationForm(url) {
        const dialog = document.getElementById('tenantApplicationCreateDialog');
        const modalUrl = url + (url.includes('?') ? '&' : '?') + 'modal=1';

        dialog.showModal();
        $(dialog).html('<div class="tenant-dialog-loading"><i class="bi bi-arrow-clockwise"></i><span>Loading application form...</span></div>');

        $.get(modalUrl)
            .done(function (html) {
                const form = $('<div>').html(html).find('#tenantApplicationForm').first();
                $(dialog).html('<div class="tenant-modal-content"></div>');
                $(dialog).find('.tenant-modal-content').append(form);
                tenantApplicationFiles = [];
                renderTenantApplicationFiles();
            })
            .fail(function () {
                showTenantApplicationLoadError(dialog, 'The application form could not be loaded.');
            });
    }

    $(document).on('click', '[data-create-application-url]', function () {
        openTenantApplicationForm($(this).data('create-application-url'));
    });

    $(document).on('click', '.js-edit-application', function () {
        openTenantApplicationForm($(this).data('url'));
    });

    $(document).on('click', '.js-view-application', function () {
        const dialog = document.getElementById('tenantApplicationViewDialog');

        dialog.showModal();
        $(dialog).html('<div class="tenant-dialog-loading"><i class="bi bi-arrow-clockwise"></i><span>Loading application details...</span></div>');

        $.get($(this).data('url'))
            .done(function (html) {
                const detail = $('<div>').html(html).find('.tenant-application-detail-grid').first();
                detail.find('.application-form-banner').append('<button type="button" class="tenant-management-close" data-close-management-dialog aria-label="Close"><i class="bi bi-x-circle-fill"></i></button>');
                $(dialog).html('<div class="tenant-modal-content tenant-view-content"></div>');
                $(dialog).find('.tenant-view-content').append(detail);
                $(dialog).find('.tenant-detail-back, .tenant-detail-actions').remove();
            })
            .fail(function () {
                showTenantApplicationLoadError(dialog, 'The application details could not be loaded.');
            });
    });

    $(document).on('click', '[data-close-management-dialog]', function (event) {
        event.preventDefault();
        const dialog = $(this).closest('dialog').get(0);
        if (dialog?.open) dialog.close();
    });

    $(document).on('click', '.tenant-management-dialog', function (event) {
        if (event.target === this) this.close();
    });

    function clearTenantApplicationErrors(form) {
        $(form).find('.tenant-field-error').remove();
        $(form).find('[aria-invalid="true"]')
            .removeAttr('aria-invalid')
            .removeAttr('aria-describedby');
    }

    function findTenantApplicationField(form, fieldName) {
        const rootName = fieldName.split('.')[0];

        return $(form).find('[name]').filter(function () {
            return this.name === fieldName || this.name === rootName || this.name === rootName + '[]';
        }).first();
    }

    function showTenantApplicationErrors(form, errors) {
        clearTenantApplicationErrors(form);

        let firstInvalidField = null;

        $.each(errors, function (fieldName, messages) {
            const field = findTenantApplicationField(form, fieldName);
            if (!field.length) return;
            if (field.attr('aria-invalid') === 'true') return;

            const errorId = 'tenant-application-error-' + fieldName.replace(/[^a-zA-Z0-9_-]/g, '-');
            const error = $('<small>', {
                id: errorId,
                class: 'tenant-field-error',
                text: Array.isArray(messages) ? messages[0] : messages
            });

            if (field.attr('type') === 'file') {
                error.insertAfter($(form).find('label[for="' + field.attr('id') + '"]'));
            } else {
                error.insertAfter(field);
            }

            field.attr({
                'aria-invalid': 'true',
                'aria-describedby': errorId
            });

            if (!firstInvalidField) firstInvalidField = field;
        });

        if (firstInvalidField) {
            const focusTarget = firstInvalidField.attr('type') === 'file'
                ? $(form).find('label[for="' + firstInvalidField.attr('id') + '"]')
                : firstInvalidField;

            focusTarget.get(0)?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            if (firstInvalidField.attr('type') !== 'file') firstInvalidField.trigger('focus');
        }
    }

    $(document).on('input change', '#tenantApplicationCreateDialog #tenantApplicationForm [name]', function () {
        const field = $(this);
        const errorId = field.attr('aria-describedby');

        if (!errorId) return;

        $('#' + errorId).remove();
        field.removeAttr('aria-invalid').removeAttr('aria-describedby');
    });

    $(document).on('submit', '#tenantApplicationCreateDialog #tenantApplicationForm', function (event) {
        event.preventDefault();

        const form = this;
        clearTenantApplicationErrors(form);
        const submitButton = $(form).find('[type="submit"]');
        const originalText = submitButton.text();
        submitButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Submitting');

        $.ajax({
            url: form.action,
            type: 'POST',
            data: new FormData(form),
            processData: false,
            contentType: false,
            headers: { Accept: 'application/json' },
            success: function (response) {
                document.getElementById('tenantApplicationCreateDialog').close();
                tenantApplicationTable.ajax.reload(null, false);
                form.reset();
                tenantApplicationFiles = [];

                einspectSuccess(response.message);
            },
            error: function (xhr) {
                const errors = xhr.responseJSON?.errors;

                if (xhr.status === 422 && errors) {
                    showTenantApplicationErrors(form, errors);
                    return;
                }

                Swal.fire({
                    title: 'Please Check the Form',
                    text: xhr.responseJSON?.message || 'The application could not be submitted.',
                    icon: 'error',
                    confirmButtonColor: '#760008',
                    customClass: { popup: 'einspect-swal' }
                });
            },
            complete: function () {
                submitButton.prop('disabled', false).text(originalText);
            }
        });
    });

    function showTenantApplicationLoadError(dialog, message) {
        $(dialog).html(`
            <button type="button" class="tenant-management-close" data-close-management-dialog aria-label="Close"><i class="bi bi-x-circle-fill"></i></button>
            <div class="tenant-dialog-error"><i class="bi bi-exclamation-circle-fill"></i><strong>${message}</strong></div>
        `);
    }

    $(document).on('click', '.js-delete-application', function () {
        const button = $(this);
        einspectConfirm({
            title: 'Delete Application?',
            message: `Remove ${button.data('reference')} and its uploaded documents?`,
            confirmText: 'Yes, Delete',
            cancelText: 'No, Keep It'
        }).then(function (result) {
            if (!result.isConfirmed) return;

            $.ajax({
                url: button.data('url'),
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'DELETE'
                },
                headers: { Accept: 'application/json' },
                success: function (response) {
                    tenantApplicationTable.ajax.reload(null, false);
                    einspectSuccess(response.message);
                },
                error: function (xhr) {
                    Swal.fire({
                        title: 'Unable to Delete',
                        text: xhr.responseJSON?.message || 'The application could not be deleted.',
                        icon: 'error',
                        confirmButtonColor: '#760008',
                        customClass: { popup: 'einspect-swal' }
                    });
                }
            });
        });
    });
</script>
