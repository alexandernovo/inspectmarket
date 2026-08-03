<script>
    let tenantApplicationFiles = [];

    $(document).on('change', '#tenantApplicationDocuments', function () {
        tenantApplicationFiles = Array.from(this.files);
        renderTenantApplicationFiles();
    });

    $(document).on('click', '.remove-tenant-application-file', function () {
        tenantApplicationFiles.splice(Number($(this).data('index')), 1);
        syncTenantApplicationFiles();
        renderTenantApplicationFiles();
    });

    function syncTenantApplicationFiles() {
        const transfer = new DataTransfer();
        tenantApplicationFiles.forEach(function (file) {
            transfer.items.add(file);
        });
        document.getElementById('tenantApplicationDocuments').files = transfer.files;
    }

    function renderTenantApplicationFiles() {
        const fileContainer = $('#tenantApplicationFiles');
        fileContainer.empty();

        tenantApplicationFiles.forEach(function (file, index) {
            const extension = file.name.split('.').pop().toLowerCase();
            const icon = extension === 'pdf'
                ? 'bi-file-earmark-pdf-fill'
                : ['jpg', 'jpeg', 'png'].includes(extension)
                    ? 'bi-file-earmark-image-fill'
                    : 'bi-file-earmark-word-fill';

            fileContainer.append(`
                <article>
                    <i class="bi ${icon}"></i>
                    <div><strong>${$('<div>').text(file.name).html()}</strong><span>${Math.ceil(file.size / 1024)} KB</span></div>
                    <button type="button" class="remove-tenant-application-file" data-index="${index}" aria-label="Remove file"><i class="bi bi-x-lg"></i></button>
                </article>
            `);
        });
    }
</script>
