<?php
$wilayahRows = db_connect()->table('kelurahan')->select('id,kecamatan_id,kelurahan_nama')->where('provinsi', 'Jawa Barat')->where('kabupaten_kota', 'Kota Bogor')->orderBy('kelurahan_nama')->get()->getResultArray();
$kelurahanData = [];
foreach ($wilayahRows as $wilayah) $kelurahanData[$wilayah['kecamatan_id']][] = ['id' => $wilayah['id'], 'name' => $wilayah['kelurahan_nama']];
$wilayahKecamatan = db_connect()->table('kelurahan')->select('kecamatan_id,kecamatan_nama')->where('provinsi', 'Jawa Barat')->where('kabupaten_kota', 'Kota Bogor')->distinct()->orderBy('kecamatan_nama')->get()->getResultArray();
?>
<?= view('header'); ?>

<div class="card">
    <div class="card-header">
        Daftar Kendala Kota Bogor
        <a class="btn btn-primary btn-sm float-end" href="<?= base_url('/') ?>">Kembali</a>

    </div>

    

    <div class="card-body">

        <div class="row mb-3 g-2">
  <div class="row mb-3 g-2">
    <div class="col-md-3">
        <select id="filterKecamatan" class="form-select form-select-sm">
            <option value="">Semua Kecamatan Kota Bogor</option>
            <?php foreach ($wilayahKecamatan as $wilayah): ?>
                <option value="<?= esc($wilayah['kecamatan_id']) ?>"><?= esc($wilayah['kecamatan_nama']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <select id="filterKelurahan" class="form-select form-select-sm">
            <option value="">Semua Kelurahan</option>
        </select>
    </div>
    <div class="col-md-3">
        <select id="filterTipe" class="form-select form-select-sm">
            <option value="">Semua Tipe</option>
            <option value="TJU">TJU</option>
            <option value="TKP">TKP</option>
        </select>
    </div>
</div>

</div>
            <a class="btn btn-danger btn-sm float-start mb-4" href="<?= base_url('/kendalaadmin') ?>">Tambah Kendala</a>

        <table id="kendalaTable" class="display" style="font-size:12px; width:100%;">
            <thead>
               <tr>
                    <th>ID</th>
                    <th>VA</th>
                    <th>Nama</th>
                    <th>Kecamatan</th>
                    <th>Kelurahan</th>
                    <th>Nama Tempat</th>
                    <th>Lokasi</th>
                    <th>Foto</th>
                    <th>Kendala</th>
                    <th>Potongan</th>
                    <th>Status</th>
                    <th>Approved/Rejected By</th>
                    <th>Tanggal</th>
                    <th>Aksi</th> <!-- ✅ NEW column -->
                </tr>
            </thead>
        </table>
    </div>
</div>


<!-- ✅ IMAGE PREVIEW MODAL -->
<div class="modal fade" id="imageModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">

      <div class="modal-body text-center p-0">
        <img id="previewImage" src="" style="width:100%; height:auto; border-radius:4px;">
      </div>

      <div class="modal-footer p-2">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
          Close
        </button>
      </div>

    </div>
  </div>
</div>

<div class="toast-container position-fixed bottom-0 end-0 p-3">
  <div id="liveToast" class="toast align-items-center text-bg-success border-0">
    <div class="d-flex">
      <div class="toast-body"></div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>


<script>
$(document).ready(function() {

    $('#kendalaTable').DataTable({
        processing: true,
        serverSide: true,
        order: [[0, 'desc']],
        ajax: {
    url: "<?= site_url('kendaladata') ?>",
    type: "POST",
    data: function(d) {
        d.kecamatan_id = $('#filterKecamatan').val();
        d.kelurahan_id = $('#filterKelurahan').val();
        d.tipe = $('#filterTipe').val();
    }

        },
        columns: [
    { data: 'kendala_id' },
    { data: 'kendala_va' },
    { data: 'va_owner_nama'},
    { data: 'kecamatan_nama' },
    { data: 'kelurahan_nama' },
    { data: 'titpar_namatempat' },
    { data: 'titpar_lokasi' },
    {
        data: 'kendala_foto',
        render: function(data) {
            if (!data) return '<span class="text-muted">No Image</span>';

            let fullPath = data.startsWith('http')
                ? data
                : "<?= base_url('uploads/kendalajukir') ?>/" + data;

            return `
                <img src="${fullPath}" 
                     class="img-thumbnail preview-img" 
                     style="width:60px; cursor:pointer;"
                     data-full="${fullPath}">
            `;
        }
    },

    { data: 'kendala_alasan' },
      {
  data: 'kategori',
  render: function(data, type, row) {

    let selected1 = data == 1 ? 'selected' : '';
    let selected2 = data == 2 ? 'selected' : '';
    let selected3 = data == 3 ? 'selected' : '';
    let selected4 = data == null ? 'selected' : '';
    return `
      <select class="form-select form-select-sm potonganSelect" 
              data-id="${row.kendala_id}">
        <option value="1" ${selected1}>100%</option>
        <option value="2" ${selected2}>50%</option>
        <option value="3" ${selected3}>25%</option>
        <option value="" ${selected4}>0%</option>
      </select>
    `;
  }
},
    {
        data: 'approval',
        render: function(data) {
            if (data == 1)
                return '<span class="badge bg-success">Approved</span>';
            if (data == 2)
                return '<span class="badge bg-danger">Rejected</span>';
            return '<span class="badge bg-warning text-dark">Pending</span>';
        }
    },
     { data: 'nama' },

         {
            data: 'created_at',
            render: function(datetime) {

                if (!datetime) return "-";

                // Convert to JS date
                let date = new Date(datetime);

                // Indonesian month names
                const bulan = [
                    "Januari", "Februari", "Maret", "April", "Mei", "Juni",
                    "Juli", "Agustus", "September", "Oktober", "November", "Desember"
                ];

                let tgl   = date.getDate();
                let bln   = bulan[date.getMonth()];
                let thn   = date.getFullYear();

                let jam   = String(date.getHours()).padStart(2, '0');
                let menit = String(date.getMinutes()).padStart(2, '0');

                return `${tgl} ${bln} ${thn} - ${jam}:${menit}`;
            }
        },


    // ✅ ACTION BUTTONS FOR APPROVE/REJECT
    {
        data: null,
            render: function(data, type, row) {
        return `
            <button class="btn btn-success btn-sm approveBtn" data-id="${row.kendala_id}">
                <i class="bi bi-check-circle"></i>
            </button>

            <button class="btn btn-danger btn-sm rejectBtn" data-id="${row.kendala_id}">
                <i class="bi bi-x-circle"></i>
            </button>
        `;
    }
    }
]

    });
});


$(document).on('change', '#filterKecamatan, #filterKelurahan, #filterTipe', function() {
    $('#kendalaTable').DataTable().ajax.reload();
});


// ✅ CLICK THUMBNAIL → OPEN MODAL
$(document).on('click', '.preview-img', function() {
    let fullImage = $(this).data('full');
    $('#previewImage').attr('src', fullImage);

    let modal = new bootstrap.Modal(document.getElementById('imageModal'));
    modal.show();
});
</script>


<!-- ✅ BOOTSTRAP JS FIX (required for modal to work) -->
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> -->

<script>
    // ✅ APPROVE BUTTON
$(document).on('click', '.approveBtn', function() {
    let id = $(this).data('id');

    if (!confirm("Setujui kendala ini?")) return;

    $.post("<?= site_url('kendala/approve') ?>", { id: id }, function(response) {
        $('#kendalaTable').DataTable().ajax.reload(null, false);
    });
});

// ✅ REJECT BUTTON
$(document).on('click', '.rejectBtn', function() {
    let id = $(this).data('id');

    if (!confirm("Tolak kendala ini?")) return;

    $.post("<?= site_url('kendala/reject') ?>", { id: id }, function(response) {
        $('#kendalaTable').DataTable().ajax.reload(null, false);
    });
});

$(document).on('change', '.potonganSelect', function() {
    let id   = $(this).data('id');
    let val  = $(this).val();

    $.post("<?= site_url('kendala/updatePotongan') ?>", { id, kategori: val }, function(res) {

        showToast(res.message, res.status);

        $('#kendalaTable').DataTable().ajax.reload(null, false);

    }, 'json').fail(function() {
        showToast('Server error', 'error');
    });
});

function showToast(message, type = 'success') {
    let toastEl = $('#liveToast');

    toastEl.removeClass('text-bg-success text-bg-danger')
           .addClass(type === 'success' ? 'text-bg-success' : 'text-bg-danger');

    toastEl.find('.toast-body').text(message);

    let toast = new bootstrap.Toast(toastEl[0]);
    toast.show();
}

</script>

<script>
const kelurahanData = <?= json_encode($kelurahanData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

const filterKecamatan = document.getElementById("filterKecamatan");
const filterKelurahan = document.getElementById("filterKelurahan");

function loadFilterKelurahan(kecId) {
    filterKelurahan.innerHTML = '<option value="">Semua Kelurahan</option>';
    if (kelurahanData[kecId]) {
        kelurahanData[kecId].forEach(k => {
            const opt = document.createElement("option");
            opt.value = k.id;
            opt.textContent = k.name;
            filterKelurahan.appendChild(opt);
        });
    }
}

filterKecamatan.addEventListener("change", function () {
    loadFilterKelurahan(this.value);
    $('#kendalaTable').DataTable().ajax.reload();
});

$(document).on('change', '#filterKelurahan, #filterTipe', function() {
    $('#kendalaTable').DataTable().ajax.reload();
});
</script>

<?= view('footer'); ?>

