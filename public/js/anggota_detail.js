$(document).ready(function () {

    const BASE_URL = document.querySelector('meta[name="base-url"]').content;
    let currentUploadField = '';

    $(document).on('click', '.btn-upload-trigger', function () {
        currentUploadField = $(this).data('field');
        $('#upload_record_id').val($(this).data('id'));
        $('#uploadModalTitle').text($(this).data('title'));
        $('#upload_file').val('').attr('name', currentUploadField);
        bootstrap.Modal.getOrCreateInstance(document.getElementById('universalUploadModal')).show();
    });

    $('#universalUploadForm').on('submit', function (e) {
        e.preventDefault();
        const fd = new FormData(this);

        $.ajax({
            url: BASE_URL + '/admin/anggota/upload/' + currentUploadField,
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: res => res.status ? location.reload() : alert(res.message)
        });
    });

});