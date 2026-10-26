<?php echo view('header'); ?>

<style>
.table-responsive { overflow: visible !important; }
.card { overflow: visible !important; }
</style>

<style>
    .table-responsive { overflow: visible !important; }
    
    /* Prevent the thumbnail from shaking on hover */
    img { transition: transform 0.2s; }
    img:hover { transform: scale(1.05); }
</style>

<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Pendaftaran KTA</h5>
        <a class="btn btn-outline-secondary btn-sm" href="<?= base_url('/') ?>">
            <i class="fa fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="ktaTable" class="table table-hover align-middle" style="width:100%; font-size:13px;">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nama</th> 
                        <th class="text-center">Dokumen</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                        
                        <th>Anggota ID</th>

                        <!-- <th>Titpar</th>
                        <th>Penempatan ID</th> -->
                        <!-- <th>Tipe / VA</th> -->
                        <th>Foto</th>
                         <th>Catatan</th>  
                        <th>Titik Parkir</th>
                        
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- UPLOAD MODAL -->
<div class="modal fade" id="universalUploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="universalUploadForm" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadModalTitle">Upload File</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="upload_record_id">
                    <div class="mb-3">
                        <label class="form-label" id="uploadLabelText">Pilih File</label>
                        <input type="file" class="form-control" required>
                        <small class="text-danger" id="uploadError"></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary w-100" id="btnSubmitUpload">
                        Mulai Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_catatan" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="catatan">
                <div class="modal-header">
                    <h5 class="modal-title">Catatan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="id" id="catatan_record_id">

                    <div class="mb-3">
                        <label for="catatan_text" class="form-label">Masukkan Catatan</label>
                        <textarea
                            class="form-control"
                            id="catatan_text"
                            name="catatan"
                            rows="4"
                            required
                            placeholder="Tulis catatan di sini..."></textarea>
                        <small class="text-danger" id="catatanError"></small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary w-100">
                        Simpan Catatan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= view('footer'); ?>

<script>
$(document).ready(function() {

    let currentUploadField = '';

    const table = $('#ktaTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: { url: "<?= site_url('kta/data') ?>", type: "POST" },
        columns: [
            { data: 'id' },
            { 
                data: 'anggota_nama',
                render: (d,t,r) => `<b>${d}</b><br><small>${r.anggota_noktp}</small>`
            },
             {
                data: null,
                className: 'text-center',
                render: r => `
                <div class="dropdown">
                    <button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">Dokumen</button>
                    <ul class="dropdown-menu shadow">
                        <li>${docLink(r,'foto_ktp','foto_ktp')}</li>
                        <li>${docLink(r,'kk','Kartu Keluarga')}</li>
                    </ul>
                </div>`
            },
             { 
                data: 'anggota_status',
                render: d => d==1 ? badge('Layak','success') : d==2 ? badge('Tidak Layak','danger') : badge('Pending','warning')
            },
           {
                data: null,
                className: 'text-center',
                render: r => {

                    let disabled = (r.anggota_status != 0) ? 'disabled' : '';

                    return `
                    <div class="btn-group">
                        <button class="btn btn-success btn-sm approveBtn"
                                data-id="${r.id}" ${disabled}>
                            SETUJU
                        </button>
                        <button class="btn btn-danger btn-sm rejectBtn"
                                data-id="${r.id}" ${disabled}>
                            TOLAK
                        </button>
                    </div>`;
                }
            },
           {
            data: null,
            className: 'text-center',
            render: r => {
                // disable jika bukan Layak
                if (r.anggota_status != 1) {
                    return `<button class="btn btn-secondary btn-sm" disabled>
                                Register
                            </button>`;
                }

                // jika sudah ada anggota_id
                if (r.register_anggotaid) {
                    return `<span class="badge bg-success">${r.register_anggotaid}</span>`;
                }

                // jika layak tapi belum ada → boleh register
                return `
                    <button class="btn btn-success btn-sm registeranggota" data-id="${r.id}">
                        Register
                    </button>
                `;
            }
        },
              
            //  {
            //     data: null,
            //     render: r => {
            //         let disabled = !r.register_anggotaid ? 'disabled' : '';

            //         return `
            //             <div class="d-flex gap-1">
            //                 <select class="form-select form-select-sm pilihTitparDropdown"
            //                         data-id="${r.id}" ${disabled}>
            //                     <option value="">-- Pilih --</option>
            //                 </select>

            //                 <button class="btn btn-sm btn-success tambahTitpar"
            //                         data-id="${r.id}" ${disabled}>
            //                     +
            //                 </button>
            //             </div>
            //         `;
            //     }
            // },
            // { data: 'register_titpargrupid' },
        //    {
        //     data: 'register_va',
        //     render: (d,t,r) => {

        //         let btn = '';

        //         // kondisi:
        //         // 1. harus ada titpar
        //         // 2. register_va masih kosong
        //         if (r.register_titpargrupid && !r.register_va) {
        //             btn = `
        //                 <button class="btn btn-sm btn-primary mt-1 btn-create-va"
        //                     data-id="${r.id}">
        //                     Buat VA
        //                 </button>
        //             `;
        //         } else if (!r.register_titpargrupid) {
        //             btn = `
        //                 <button class="btn btn-sm btn-secondary mt-1" disabled>
        //                     Pilih Titpar dulu
        //                 </button>
        //             `;
        //         } else {
                   
        //         }

        //         return `
        //             ${d ?? '-'}<br>
        //             ${r.baru==1 ? badge('Baru','info') : badge('Perpanjangan','danger')}
        //             <br>${btn}
        //         `;
        //     }
        // },
            { 
                data: 'anggota_foto',
                className: 'text-center',
                render: (d, t, r) => imgPreview(d, 'anggota_foto', 'Foto Anggota', r.id) 
            },
               {
                data: 'catatan',
                className: 'text-start',
               render: (d, t, r) => {
                const text = d || '-';
                const shortText = text.length > 25
                    ? text.substring(0, 25) + '...'
                    : text;

                return `
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <span title="${text}">${shortText}</span>

                        <button
                            type="button"
                            class="btn btn-sm btn-outline-primary btn-catatan"
                             data-id="${r.id}"
                            data-catatan="${encodeURIComponent(d || '')}">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                    </div>
                `;
            }
            },
           
                 {
                data: null,
                className: 'text-center',
                render: (d, t, r) => `
                    <a href="<?php echo base_url() ?>titik-parkir/${r.id}"
                       class="btn btn-sm btn-primary">
                        Titik Parkir
                    </a>
                `
            }
        ]
    });

    function badge(t,c){
        return `<span class="badge bg-${c}">${t}</span>`;
    }

    function docLink(row, field, label) {
        let file = row[field];
        let base = "<?= base_url('uploads') ?>/"+field+"/";

        if (file) {
            return `
            <div class="dropdown-item d-flex justify-content-between align-items-center">
                <span>${label}</span>
                <div>
                    <a href="${base+file}" target="_blank" class="btn btn-sm btn-info">View</a>
                    <button class="btn btn-sm btn-warning btn-upload-trigger"
                        data-id="${row.id}" data-field="${field}" data-title="${label}">
                        Edit
                    </button>
                </div>
            </div>`;
        } else {
            return `
            <a href="#" class="dropdown-item btn-upload-trigger"
                data-id="${row.id}" data-field="${field}" data-title="${label}">
                ${label} - <span class="text-danger">Upload</span>
            </a>`;
        }
    }

    function imgPreview(file, folder, label='', id=null) {
    if (!file) {
        return `<button class="btn btn-sm btn-outline-primary btn-upload-trigger"
            data-id="${id}" data-field="${folder}" data-title="${label}">
            <i class="fa fa-upload"></i>
        </button>`;
    }

    let url = "<?= base_url('uploads') ?>/"+folder+"/"+file;

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

    // OPEN MODAL
    $(document).on('click','.btn-upload-trigger',function(e){
        e.preventDefault();

        let d = $(this).data();
        currentUploadField = d.field;

        $('#upload_record_id').val(d.id);
        $('#uploadModalTitle').text('Upload '+d.title);

        let input = $('#universalUploadForm input[type="file"]');
        input.val('');
        input.attr('name', currentUploadField);

        new bootstrap.Modal('#universalUploadModal').show();
    });

     $(document).on('click', '.btn-catatan', function () {
        $('#catatan_record_id').val($(this).data('id'));

        const catatan = decodeURIComponent($(this).data('catatan') || '');
        $('#catatan_text').val(catatan);

        new bootstrap.Modal(
            document.getElementById('modal_catatan')
        ).show();
    });

        $('#catatan').on('submit', function (e) {
    e.preventDefault();

    $.ajax({
        url: '<?php echo base_url() ?>/register/save-catatan',
        type: 'POST',
        data: {
            id: $('#catatan_record_id').val(),
            catatan: $('#catatan_text').val()
        },
        dataType: 'json',
        success: function (res) {
            if (res.status) {
                location.reload();
            } else {
                $('#catatanError').text(res.message || 'Gagal menyimpan catatan');
            }
        },
        error: function () {
            $('#catatanError').text('Gagal menyimpan catatan');
        }
    });
});

    // SUBMIT
    $('#universalUploadForm').submit(function(e){
        e.preventDefault();

        let fd = new FormData(this);
        let btn = $('#btnSubmitUpload');

        btn.prop('disabled',true).text('Uploading...');

        $.ajax({
            url: "<?= site_url('api/upload/') ?>"+currentUploadField,
            type: "POST",
            data: fd,
            processData:false,
            contentType:false,
            success: res => {
                if(res.status){
                    bootstrap.Modal.getInstance(document.getElementById('universalUploadModal')).hide();
                    table.ajax.reload(null,false);
                } else {
                    alert(res.message);
                }
            },
            complete: ()=> btn.prop('disabled',false).text('Mulai Upload')
        });
    });

    // SETUJU
$(document).on('click', '.approveBtn', function() {
    let id = $(this).data('id');

    if (!confirm('Setujui pendaftaran ini?')) return;

    $.ajax({
        url: "<?= site_url('kta/approve') ?>",
        type: "POST",
        data: { id: id },
        dataType: "json",
        success: function(res) {
            if (res.status) {

                table.ajax.reload(null, false);
            } else {
                alert(res.message || 'Gagal approve');
            }
        },
        error: function(xhr) {
            alert('Error: ' + xhr.responseText);
        }
    });
});

$(document).on('click', '.registeranggota', function() {
    let id = $(this).data('id');

    if (!confirm('Daftarkan Anggota??')) return;

    $.ajax({
        url: "<?= site_url('kta/registeranggota') ?>",
        type: "POST",
        data: { id: id },
        dataType: "json",
        success: function(res) {
            if (res.status) {

                table.ajax.reload(null, false);
            } else {
                alert(res.message || 'Gagal Register');
            }
        },
        error: function(xhr) {
            alert('Error: ' + xhr.responseText);
        }
    });
});



// TOLAK
$(document).on('click', '.rejectBtn', function() {
    let id = $(this).data('id');

    if (!confirm('Tolak pendaftaran ini?')) return;

    $.ajax({
        url: "<?= site_url('kta/reject') ?>",
        type: "POST",
        data: { id: id },
        dataType: "json",
        success: function(res) {
            if (res.status) {
                table.ajax.reload(null, false);
            } else {
                alert(res.message || 'Gagal reject');
            }
        },
        error: function(xhr) {
            alert('Error: ' + xhr.responseText);
        }
    });
});


function initTitparSelect(el, selectedId = null, selectedText = null) {

    el.data('init', true); // tandai init

    el.select2({
        theme: 'bootstrap4',
        width: '100%',
        placeholder: '-- Pilih Titpar --',
        minimumInputLength: 1,
        ajax: {
            url: "<?= site_url('titpar/list') ?>",
            dataType: 'json',
            delay: 250,
            data: params => ({ q: params.term }),
            processResults: data => ({
                results: data.map(t => ({
                    id: t.titpar_id,
                    text: t.titpar_namatempat + ' (' + (t.titpar_lokasi ?? '') + ')'
                }))
            })
        }
    });

    if (selectedId && selectedText) {
        let option = new Option(selectedText, selectedId, true, true);
        el.append(option).trigger('change');
    }

    // selesai init
    setTimeout(() => el.removeData('init'), 0);
}

 $('#ktaTable').on('draw.dt', function(){
    $('.pilihTitparDropdown').each(function(){
        let row = table.row($(this).closest('tr')).data();

        let text = row.titpar_namatempat 
            ? row.titpar_namatempat + ' (' + (row.titpar_lokasi ?? '') + ')'
            : null;

        initTitparSelect($(this), row.register_titparid, text);
    });
});



$(document).on('change', '.pilihTitparDropdown', function(){

    if ($(this).data('init')) return;

    let $el = $(this);
    let register_id = $el.data('id');
    let titpar_id   = $el.val();

    if (!titpar_id) return;

    $el.prop('disabled', true);

    $.ajax({
        url: "<?= site_url('register/setTitpar') ?>",
        type: "POST",
        dataType: "json",
        data: {
            register_id: register_id,
            titpar_id: titpar_id
        },
        success: function(res){
            if (res.status) {
                showToast('Titpar tersimpan');

                if ($.fn.DataTable.isDataTable('#ktaTable')) {
                    $('#ktaTable').DataTable().ajax.reload(null, false);
                }
            } else {
                alert(res.message || 'Gagal set titpar');
            }
        },
        error: function(){
            alert('Server error');
        },
        complete: function(){
            $el.prop('disabled', false);
        }
    });
});


$(document).on('click', '.tambahTitpar', function(){
    let register_id = $(this).data('id');

    window.location.href = "<?= base_url('titpar/create?register_id=') ?>" + register_id;
});

});

function showToast(msg){
    let toast = $(`
        <div class="toast align-items-center text-bg-success border-0 position-fixed bottom-0 end-0 m-3">
            <div class="d-flex">
                <div class="toast-body">${msg}</div>
            </div>
        </div>
    `);

    $('body').append(toast);
    new bootstrap.Toast(toast[0]).show();

    setTimeout(()=> toast.remove(), 2000);
}

$(document).on('click', '.btn-create-va', function() {
    let id = $(this).data('id');

    window.location.href = "<?= base_url('va-owner/create?register_id=') ?>" + id;
});


</script>

