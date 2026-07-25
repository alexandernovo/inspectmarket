<script>
    $(document).on('click', '.tenant-document-tab', function () {
        const target = $(this).data('document-target');
        $('.tenant-document-tab').removeClass('active');
        $('.tenant-document-frame').removeClass('active');
        $(this).addClass('active');
        $('#' + target).addClass('active');
    });
</script>
