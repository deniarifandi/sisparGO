let currentUploadField = '';

$(document).on('click', '.btn-upload-trigger', function () {
    currentUploadField = $(this).data('field');

    $('#upload_record_id').val($(this).data('id'));
    $('#uploadModalTitle').text($(this).data('title'));

    $('#upload_file')
        .val('')
        .attr('name', currentUploadField);

    new bootstrap.Modal('#universalUploadModal').show();
});

$('#universalUploadForm').on('submit', function (e) {
    e.preventDefault();

    const fd  = new FormData(this);
    const btn = $('#btnSubmitUpload');

    btn.prop('disabled', true).text('Uploading...');

    $.ajax({
        url:         document.querySelector('meta[name="base-url"]').content + 'api/upload/' + currentUploadField,
        type:        'POST',
        data:        fd,
        processData: false,
        contentType: false,
        success: res => {
            if (res.status) {
                location.reload();
            } else {
                alert(res.message);
            }
        },
        complete: () => btn.prop('disabled', false).text('Upload')
    });
});