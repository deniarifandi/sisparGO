const BASE_URL  = document.querySelector('meta[name="base-url"]')?.content ?? '';
const UPLOAD_URL = BASE_URL + 'api/upload/';
const DATA_URL   = BASE_URL + 'kta/data';

let currentUploadField = '';

// ─── Helpers ─────────────────────────────────────────────────────────────────

function badge(text, color) {
    return `<span class="badge bg-${color}">${text}</span>`;
}

function imgPreview(file, folder, label = '', id = null) {
    if (!file) {
        return `
            <button class="btn btn-sm btn-outline-primary btn-upload-trigger"
                    data-id="${id}" data-field="${folder}" data-title="${label}">
                <i class="fa fa-upload"></i>
            </button>`;
    }

    const url = `${BASE_URL}/uploads/${folder}/${file}`;

    return `
        <div style="position:relative; width:50px; height:50px; margin:auto;">
            <a href="${url}" target="_blank">
                <img src="${url}" class="rounded border shadow-sm"
                     style="width:50px; height:50px; object-fit:cover; cursor:pointer;">
            </a>
            <button class="btn btn-dark btn-upload-trigger"
                    style="position:absolute; top:-8px; right:-8px; width:24px; height:24px;
                           padding:0; line-height:24px; border-radius:50%; z-index:10; border:2px solid white;"
                    data-id="${id}" data-field="${folder}" data-title="${label}" title="Ganti Foto">
                <small>✎</small>
            </button>
        </div>`;
}

function showToast(msg, color = 'success') {
    const toast = $(`
        <div class="toast align-items-center text-bg-${color} border-0 position-fixed bottom-0 end-0 m-3">
            <div class="d-flex">
                <div class="toast-body">${msg}</div>
            </div>
        </div>
    `);
    $('body').append(toast);
    new bootstrap.Toast(toast[0]).show();
    setTimeout(() => toast.remove(), 2500);
}

// ─── DataTable ────────────────────────────────────────────────────────────────

$(document).ready(function () {

    const table = $('#ktaTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: { url: DATA_URL, type: 'POST' },
        columns: [
            { data: 'id' },
            {
                data: 'anggota_nama',
                render: (d, t, r) =>
                    `<b>${d}</b><br><small class="text-muted">${r.anggota_noktp}</small>`
            },
            {
                data: 'anggota_status',
                render: d =>
                    d == 1 ? badge('Layak', 'success') :
                    d == 2 ? badge('Tidak Layak', 'danger') :
                             badge('Pending', 'warning')
            },
            {
                data: 'anggota_foto',
                className: 'text-center',
                render: (d, t, r) => imgPreview(d, 'anggota_foto', 'Foto Anggota', r.id)
            },
            {
                data: 'catatan',
                className: 'text-start',
                render: (d, t, r) => {
                    const text      = d || '-';
                    const shortText = text.length > 25
                        ? text.substring(0, 25) + '...'
                        : text;

                    return `
                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <span title="${text}">${shortText}</span>
                            <button type="button"
                                    class="btn btn-sm btn-outline-primary btn-catatan"
                                    data-id="${r.id}"
                                    data-catatan="${encodeURIComponent(d || '')}">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                        </div>`;
                }
            },
            {
                data: null,
                className: 'text-center',
                render: (d, t, r) =>
                    `<a href="${BASE_URL}admin/register/detail/${r.id}"
                        class="btn btn-sm btn-primary">Detail</a>`
            }
        ]
    });

    // ─── Upload Modal ─────────────────────────────────────────────────────────

    $(document).on('click', '.btn-upload-trigger', function (e) {
        e.preventDefault();

        const d            = $(this).data();
        currentUploadField = d.field;

        $('#upload_record_id').val(d.id);
        $('#uploadModalTitle').text('Upload ' + d.title);

        const input = $('#universalUploadForm input[type="file"]');
        input.val('').attr('name', currentUploadField);

        new bootstrap.Modal('#universalUploadModal').show();
    });

    $('#universalUploadForm').on('submit', function (e) {
        e.preventDefault();

        const fd  = new FormData(this);
        const btn = $('#btnSubmitUpload');

        btn.prop('disabled', true).text('Uploading...');

        $.ajax({
            url:         UPLOAD_URL + currentUploadField,
            type:        'POST',
            data:        fd,
            processData: false,
            contentType: false,
            success: res => {
                if (res.status) {
                    bootstrap.Modal.getInstance(
                        document.getElementById('universalUploadModal')
                    ).hide();
                    table.ajax.reload(null, false);
                    showToast('Upload berhasil!');
                } else {
                    $('#uploadError').text(res.message);
                }
            },
            complete: () => btn.prop('disabled', false).text('Mulai Upload')
        });
    });

    // ─── Catatan Modal ────────────────────────────────────────────────────────

    $(document).on('click', '.btn-catatan', function () {
        $('#catatan_record_id').val($(this).data('id'));
        $('#catatan_text').val(decodeURIComponent($(this).data('catatan') || ''));
        $('#catatanError').text('');
        new bootstrap.Modal(document.getElementById('modal_catatan')).show();
    });

    $('#formCatatan').on('submit', function (e) {
        e.preventDefault();

        $.ajax({
            url:      BASE_URL + '/register/save-catatan',
            type:     'POST',
            data: {
                id:      $('#catatan_record_id').val(),
                catatan: $('#catatan_text').val()
            },
            dataType: 'json',
            success: res => {
                if (res.status) {
                    bootstrap.Modal.getInstance(
                        document.getElementById('modal_catatan')
                    ).hide();
                    table.ajax.reload(null, false);
                    showToast('Catatan tersimpan!');
                } else {
                    $('#catatanError').text(res.message || 'Gagal menyimpan catatan.');
                }
            },
            error: () => $('#catatanError').text('Gagal menyimpan catatan.')
        });
    });
});