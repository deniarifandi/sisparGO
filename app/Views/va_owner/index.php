<?php echo view('header'); ?>

<style>
/* KTA portrait */
.kta-card {
    /*width: 300px;*/
    height: 676px; /* 300 * (135.6/85.6) = 475.6 */
    aspect-ratio: 85.6 / 135.6;
    background: #ffffff;
    padding: 24px 20px !important; /* Tambahkan sedikit padding agar terasa lega */
    box-sizing: border-box;
    overflow: hidden;
    margin: 0 10px;
    display: flex;
    flex-direction: column;
    justify-content: space-evenly;
    align-items: center;
    text-align: center; /* Memastikan teks tercetak di tengah */
}
.kta-card::after {
    content: '';
    position: absolute;
    width: 410px;
    height: 410px;
    right: -165px;
    bottom: -165px;
    background: url('<?= base_url('images/dishub.png') ?>') center / contain no-repeat;
    opacity: 0.18;
    pointer-events: none;
    z-index: 0;
}
.kta-card > * { position: relative; z-index: 1; }

/* Penyesuaian Ukuran Visual (Logo, Foto & QR) */
.kta-card .kta-header { width: 100%; }
.kta-card .kta-logos {
    margin-bottom: 10px;
    padding: 0 44px;
    justify-content: space-between;
}
.kta-card .kta-logos img { height: 64px !important; width: auto; object-fit: contain; }
.kta-card #ktaIdJukir { font-size: 12px; }
.kta-card #ktaFoto       { width: 150px !important; height: 188px !important; object-fit: cover; }
.kta-card #ktaQr         { width: 105px !important; height: 105px !important; object-fit: contain; }

/* --- PERBESARAN TEKS KTA --- */
.kta-card .kta-title-small { 
    font-size: 14px !important; 
    font-weight: 600 !important; 
    letter-spacing: 0.5px;
}
.kta-card .kta-title { 
    font-size: 26px !important; 
    font-weight: 800 !important; 
    line-height: 1.2;
}
.kta-card .kta-subtitle { 
    font-size: 16px !important; 
    font-weight: 500 !important;
}
.kta-card #ktaNama { 
    font-size: 20px !important;
    font-weight: 700 !important; 
    line-height: 1.3;
}
.kta-card #ktaStatus { 
    font-size: 15px !important; /* Diperbesar dari 14px ke 15px */
    font-weight: 600 !important;
}


/* Modal Frame Layout */
#ktaModal .modal-dialog {
    max-width: 1500px; /* lebar buat 2 kolom */
}

/* Detail Panel Kanan */
.kta-detail-panel {
    padding: 24px 20px;
    background: #f8f9fa;
    display: flex;
    flex-direction: column;
    justify-content: center;
    height: 676px; /* samain tinggi sama kta-card */
    box-sizing: border-box;
}

.kta-detail-panel dl {
    margin: 0;
}

.kta-detail-panel dt {
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6c757d;
    font-weight: 700;
    margin-top: 12px;
}

.kta-detail-panel dt:first-child {
    margin-top: 0;
}

.kta-detail-panel dd {
    font-size: 15px; /* Diperbesar sedikit agar sejajar dengan KTA */
    font-weight: 600;
    color: #212529;
    margin: 2px 0 0;
    word-break: break-word;
}
</style>

<?php if (session()->getFlashdata('message')): ?>
    <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm mb-3" role="alert">
        <?= session()->getFlashdata('message'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <!-- Header Card -->
    <div class="card-header bg-white py-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            
            <!-- Judul -->
            <?php $wilayahKecamatan = db_connect()->table('kelurahan')->select('kecamatan_id,kecamatan_nama')->where('provinsi', 'Jawa Timur')->where('kabupaten_kota', 'Kota Malang')->distinct()->orderBy('kecamatan_nama')->get()->getResultArray(); $wilayahKecamatanMap = []; foreach ($wilayahKecamatan as $wilayah) $wilayahKecamatanMap[$wilayah['kecamatan_id']] = $wilayah['kecamatan_nama']; ?>
            <h5 class="m-0 fw-bold text-dark">VA Owner List</h5>

            <!-- Area Action & Filter (Dibuat Rapi Menggunakan Flexbox & Input Group) -->
            <div class="d-flex flex-wrap align-items-center gap-2">
                <!-- Dropdown + Button Filter -->
                <div class="input-group input-group-sm" style="width: auto;">
                    <select id="filterKecamatan" class="form-select form-select-sm" style="max-width: 170px;">
                        <option value="">-- Semua Kec. --</option>
                        <?php foreach ($wilayahKecamatan as $wilayah): ?>
                            <option value="<?= esc($wilayah['kecamatan_id']) ?>"><?= esc($wilayah['kecamatan_nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select id="filterStatus" class="form-select form-select-sm" style="max-width: 140px;">
                        <option value="">-- Status --</option>
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                    <button id="btnFilter" class="btn btn-primary btn-sm" type="button">Filter</button>
                    <button id="btnReset" class="btn btn-outline-secondary btn-sm" type="button">Reset</button>
                </div>

                <!-- Tombol Add New -->
                <?php if (session('role') < 4): ?>
                    <button type="button" class="btn btn-success btn-sm fw-semibold" disabled title="Penambahan VA sementara dinonaktifkan">
                        + Add New
                    </button>
                <?php endif ?>
            </div>

        </div>
    </div>

    <!-- Body Card -->
    <div class="card-body p-0">
        <div class="table-responsive m-3">
            <table id="usersTable" class="table table-hover table-striped align-middle text-nowrap w-100 mb-0" style="font-size: 12px;">
                <thead class="table-light">
                    <tr>
                      <th>Details</th>
                        <th>VA</th>
                        <th>Nama</th>
                        <th>ID Jukir</th>
                        <th>Tempat Parkir</th>
                        <th>Location</th>
                        <th>Kecamatan</th>
                        <th>Status Anggota</th>
                        <!-- <th>Berita 3</th> -->
                        <!-- <th>Berita 4</th> -->
                        <th>Berlaku</th>
                        <!-- <th>No Hp</th> -->
                        <th>Aktivasi</th>
                        <?php if (session('role') < 4): ?>
                            <th class="text-center" style="width: 100px;">Aksi</th>
                        <?php endif ?>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Modal Detail -->
<!-- Modal KTA -->
<div class="modal fade" id="ktaModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">

      <button type="button" class="btn-close position-absolute top-0 end-0 m-3" style="z-index:10;" data-bs-dismiss="modal" aria-label="Close"></button>

      <div class="modal-body p-0">
        <div class="row g-0">

          <!-- KIRI: Kartu KTA -->
          <div class="col-auto">
            <div class="kta-card text-center position-relative">

              <div class="kta-header">
              <div class="d-flex align-items-center kta-logos">
                <img src="<?= base_url('images/logomalang.png') ?>" alt="Logo Pemkot" style="height: 38px; object-fit: contain;">
                <img src="<?= base_url('images/dishub.png') ?>" alt="Logo Dishub" style="height: 38px; object-fit: contain;">
              </div>

              <div class="lh-sm">
                <div class="fw-bold text-uppercase text-secondary kta-title-small" style="letter-spacing: 0.5px;">
                  Kartu Tanda Anggota (KTA)
                </div>
                <div class="fw-bold text-dark text-uppercase kta-title">PETUGAS PARKIR</div>
                <div class="text-muted text-uppercase kta-subtitle">DINAS PERHUBUNGAN KOTA MALANG</div>
              </div>
              </div>

              <div class="d-flex justify-content-center align-items-center gap-4">
                <div class="border p-1 bg-light rounded-2 shadow-sm">
                  <img id="ktaFoto" src="" alt="Foto" class="rounded-1" style="object-fit: cover;"
                       onerror="this.onerror=null; this.src='<?= base_url('images/no-photo.png') ?>';">
                </div>
              </div>

              <div class="p-0" style="margin-top:8px">
                <div class="d-flex align-items-center gap-3">
                  <div class="flex-shrink-0">
                    <img id="ktaQr" src="" alt="QR" class="img-fluid" style="width: 80px; height: 80px; object-fit: contain;">
                    <div id="ktaIdJukir" class="mt-2 fw-semibold text-dark">-</div>
                  </div>
                  <div class="flex-grow-1 min-width-0">
                    <h6 id="ktaNama" class="fw-bold text-dark m-0" style="font-size: 12px;">-</h6>
                    <div class="mt-1">
                      <span id="ktaStatus" class="rounded-pill px-3 py-1" style="font-size: 11px;">-</span>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>

          <!-- KANAN: Detail Anggota -->
          <!-- KANAN: Detail + List Anggota -->
<div class="col-12 col-md">
  <div class="row g-0 h-100">

    <!-- DETAIL ANGGOTA -->
    <div class="col-12 col-lg-7">
      <div class="kta-detail-panel p-4 bg-light h-100">

        <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
          <h5 class="fw-bold text-dark m-0">
            <i class="bi bi-person-vcard text-primary me-2"></i>
            Detail Anggota
          </h5>
        </div>

        <div class="row g-3">

          <!-- ID Jukir -->
          <div class="col-6">
            <div class="bg-white p-3 rounded-3 border shadow-sm h-100">
              <small class="text-muted d-block fw-bold text-uppercase mb-1"
                     style="font-size: 11px; letter-spacing: 0.5px;">
                ID Jukir
              </small>
              <span id="detailIdJukir"
                    class="fw-bold text-dark d-block text-truncate fs-6">-</span>
            </div>
          </div>

          <!-- Status -->
          <div class="col-6">
            <div class="bg-white p-3 rounded-3 border shadow-sm h-100">
              <small class="text-muted d-block fw-bold text-uppercase mb-1"
                     style="font-size: 11px; letter-spacing: 0.5px;">
                Status
              </small>
              <span id="detailStatus"
                    class="fw-semibold text-dark d-block text-truncate fs-6">-</span>
            </div>
          </div>

          <!-- Nama -->
          <div class="col-12">
            <div class="bg-white p-3 rounded-3 border shadow-sm">
              <small class="text-muted d-block fw-bold text-uppercase mb-1"
                     style="font-size: 11px; letter-spacing: 0.5px;">
                Nama Lengkap
              </small>
              <span id="detailNama"
                    class="fw-bold text-dark d-block fs-6">-</span>
            </div>
          </div>

          <!-- Tempat Parkir -->
          <div class="col-12">
            <div class="bg-white p-3 rounded-3 border shadow-sm">
              <small class="text-muted d-block fw-bold text-uppercase mb-1"
                     style="font-size: 11px; letter-spacing: 0.5px;">
                Tempat Parkir
              </small>
              <span id="detailTempat"
                    class="fw-semibold text-dark d-block fs-6">-</span>
            </div>
          </div>

          <!-- Lokasi -->
          <div class="col-6">
            <div class="bg-white p-3 rounded-3 border shadow-sm h-100">
              <small class="text-muted d-block fw-bold text-uppercase mb-1"
                     style="font-size: 11px; letter-spacing: 0.5px;">
                Lokasi
              </small>
              <span id="detailLokasi"
                    class="text-secondary d-block text-truncate"
                    style="font-size: 14px;">-</span>
            </div>
          </div>

          <!-- Kecamatan -->
          <div class="col-6">
            <div class="bg-white p-3 rounded-3 border shadow-sm h-100">
              <small class="text-muted d-block fw-bold text-uppercase mb-1"
                     style="font-size: 11px; letter-spacing: 0.5px;">
                Kecamatan
              </small>
              <span id="detailKecamatan"
                    class="text-secondary d-block text-truncate"
                    style="font-size: 14px;">-</span>
            </div>
          </div>

          <!-- VA -->
          <div class="col-6">
            <div class="bg-white p-3 rounded-3 border shadow-sm h-100">
              <small class="text-muted d-block fw-bold text-uppercase mb-1"
                     style="font-size: 11px; letter-spacing: 0.5px;">
                No. Virtual Account
              </small>
              <span id="detailVa"
                    class="fw-bold text-primary d-block text-truncate"
                    style="font-size: 14px;">-</span>
            </div>
          </div>

          <!-- HP -->
          <div class="col-6">
            <div class="bg-white p-3 rounded-3 border shadow-sm h-100">
              <small class="text-muted d-block fw-bold text-uppercase mb-1"
                     style="font-size: 11px; letter-spacing: 0.5px;">
                No. HP
              </small>
              <span id="detailHp"
                    class="text-dark d-block text-truncate"
                    style="font-size: 14px;">-</span>
            </div>
          </div>

          <!-- Masa Berlaku -->
          <div class="col-6">
            <div class="bg-white p-3 rounded-3 border shadow-sm h-100">
              <small class="text-muted d-block fw-bold text-uppercase mb-1"
                     style="font-size: 11px; letter-spacing: 0.5px;">
                Masa Berlaku
              </small>
              <span id="detailBerlaku"
                    class="text-dark d-block text-truncate"
                    style="font-size: 14px;">-</span>
            </div>
          </div>

          <!-- Aktivasi -->
          <div class="col-6">
            <div class="bg-white p-3 rounded-3 border shadow-sm h-100">
              <small class="text-muted d-block fw-bold text-uppercase mb-1"
                     style="font-size: 11px; letter-spacing: 0.5px;">
                Aktivasi Akun
              </small>
              <div id="detailAktivasi" style="font-size: 14px;">-</div>
            </div>
          </div>

        </div>
        <div class="mt-4 pt-3 border-top">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h6 class="fw-bold text-dark m-0">
            <i class="bi bi-geo-alt text-primary me-2"></i>
            Titik Parkir Lainnya
        </h6>

        <span id="otherTitparCount"
              class="badge bg-primary rounded-pill">
            0
        </span>
    </div>

    <div id="otherTitparList">
        <div class="text-center text-muted py-3">
            Memuat data...
        </div>
    </div>
</div>
      </div>
    </div>


    <!-- LIST ANGGOTA DI SEBELAH KANAN -->
    <div class="col-12 col-lg-5">
      <div class="p-4 bg-white h-100 border-start">

        <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
          <h6 class="fw-bold text-dark m-0">
            <i class="bi bi-people-fill text-primary me-2"></i>
            Anggota Titpar
          </h6>

          <span id="detailJumlahAnggota"
                class="badge bg-primary rounded-pill">
            0
          </span>
        </div>

        <div id="detailAnggotaList">

          <div class="text-muted text-center py-4">
            <i class="bi bi-people fs-3 d-block mb-2"></i>
            Tidak ada anggota
          </div>

        </div>

      </div>
    </div>

  </div>
</div>

        </div>
      </div>

      <div class="modal-footer bg-white border-top px-4 py-3">
        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Tutup</button>
      </div>

    </div>
  </div>
</div>

<script>
  const kelurahanMap = <?= json_encode($wilayahKecamatanMap ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

  // Custom DataTables Search
  $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
      const selected = $('#filterKecamatan').val();
      if (!selected) return true;

      const table = $('#usersTable').DataTable();
      const rowData = table.row(dataIndex).data();

      if (!rowData || !rowData.titpar_klrhnid) return false;
      return rowData.titpar_klrhnid.substring(0, 6) === selected;
  });

  $(document).ready(function() {
      let table = $('#usersTable').DataTable({
          processing: true,
          serverSide: false,
          scrollX: true, // Biar tabel tidak meluber ke luar card
          // Format Layout DataTables agar kontrol Search & Export bawaan tampil rapi
          dom: "<'row p-3 align-items-center'<'col-sm-12 col-md-4 mb-2 mb-md-0'B><'col-sm-12 col-md-4'l><'col-sm-12 col-md-4'f>>" +
               "<'row'<'col-sm-12'tr>>" +
               "<'row p-3 align-items-center'<'col-sm-12 col-md-5 mb-2 mb-md-0'i><'col-sm-12 col-md-7'p>>",
          lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Semua']],
          pageLength: 25,
          // Simpan VA sebagai teks di Excel tanpa menambahkan petik ke isi sel.
          buttons: [
              { extend: 'excelHtml5', className: 'btn btn-outline-secondary btn-sm', exportOptions: { format: { body: function(data) { return $('<div>').html(data).text(); } } }, customize: function(xlsx) { const sheet = xlsx.xl.worksheets['sheet1.xml']; $('row c[r^="B"]', sheet).each(function() { const cell = $(this); const value = $('v', cell).text(); cell.attr('t', 'inlineStr'); $('v', cell).remove(); cell.append('<is><t>' + $('<div>').text(value).html() + '</t></is>'); }); } },
              { extend: 'csvHtml5', className: 'btn btn-outline-secondary btn-sm', exportOptions: { format: { body: function(data, row, column) { return column === 1 ? '"' + $('<div>').html(data).text() + '"' : $('<div>').html(data).text(); } } } },
              { extend: 'pdfHtml5', className: 'btn btn-outline-secondary btn-sm', exportOptions: { format: { body: function(data, row, column) { return column === 1 ? '"' + $('<div>').html(data).text() + '"' : $('<div>').html(data).text(); } } } },
              { extend: 'print', className: 'btn btn-outline-secondary btn-sm', exportOptions: { format: { body: function(data, row, column) { return column === 1 ? '"' + $('<div>').html(data).text() + '"' : $('<div>').html(data).text(); } } } }
          ],
          ajax: {
              url: "<?= site_url('va-owner/data') ?>",
              type: "POST",
              data: function(d) { d.status = $('#filterStatus').val(); }
          },
          columns: [
            {
              data: null,
              orderable: false,
              className: 'text-center',
              render: function(data, type, row) {
                // Default placeholder jika anggota_foto kosong/null
                const defaultFoto = 'https://via.placeholder.com/100x120?text=No+Photo';
                
                // Path URL gambar utama
                const fotoUrl = row.anggota_foto 
                  ? `<?php echo base_url(); ?>uploads/anggota_foto/${row.anggota_foto}` 
                  : defaultFoto;

                // di dalam render tombol KTA, tambahkan data-* baru:
                return `
                  <button type="button" 
                          class="btn btn-outline-primary btn-sm px-2 py-0 btn-kta" 
                          style="font-size: 11px;"
                          data-bs-toggle="modal" 
                          data-bs-target="#ktaModal"
                          data-nama="${$('<div>').text(row.anggota_nama || '-').html()}"
                          data-status="${$('<div>').text(row.titpargrup_staanggota || '-').html()}"
                          data-foto="${fotoUrl}"
                          data-qr="${row.anggota_id && row.titpar_id ? 'https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=' + encodeURIComponent(row.anggota_id + '-' + row.titpar_id) : ''}"
                          data-idjukir="${$('<div>').text(row.anggota_id || '-').html()}"
                          data-idtitpar="${$('<div>').text(row.titpar_id || '-').html()}"
                          data-tempat="${$('<div>').text(row.titpar_namatempat || '-').html()}"
                          data-lokasi="${$('<div>').text(row.titpar_lokasi || '-').html()}"
                          data-kecamatan="${$('<div>').text(kelurahanMap[(row.titpar_klrhnid || '').substring(0,6)] || '-').html()}"
                          data-va="${$('<div>').text(row.va_owner_va || '-').html()}"
                          data-hp="${$('<div>').text(row.va_owner_hp || '-').html()}"
                          data-berlaku="${row.va_owner_expired || ''}"
                          data-aktivasi="${row.deleted_at != null ? 'Non-Aktif' : 'Aktif'}">
                    Detail Petugas
                  </button>
                `;
              }
            },
              { data: 'va_owner_va' },
              { data: 'anggota_nama' },
              { data: 'anggota_id' },
              { data: 'titpar_namatempat' },
              { data: 'titpar_lokasi' },
              {
                  data: 'titpar_klrhnid',
                  render: function(data) {
                      const prefix = data ? data.substring(0, 6) : '';
                      return kelurahanMap[prefix] || '-';
                  }
              },
              {
                  data: 'titpargrup_staanggota',
                  render: function(data) {
                    if (!data) {
                      return '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2">-</span>';
                    }

                    const value = data.toString().toLowerCase().trim();

                    if (value === 'koordinator') {
                      return '<span class="badge bg-danger-subtle text-danger border border-primary-subtle rounded-pill px-2">Koordinator</span>';
                    } else if (value === 'anggota') {
                      return '<span class="badge bg-primary-subtle text-primary border border-info-subtle rounded-pill px-2">Anggota</span>';
                    } else {
                      return `<span class="badge bg-light text-dark border rounded-pill px-2">${data}</span>`;
                    }
                  }
                },
              // { data: 'titpar_namatempat' },
              // { data: 'va_owner_berita_2' },
              {
                  data: 'va_owner_expired',
                  render: function (data) {
                      if (!data) return '-';
                      const date = new Date(data);
                      return date.toLocaleDateString('id-ID', {
                          day: '2-digit',
                          month: 'short',
                          year: 'numeric'
                      });
                  }
              },
              // { data: 'va_owner_hp' },
              {
                  data: 'deleted_at',
                  render: function(data) {
                      return data != null
                          ? '<span class="badge bg-danger">Non-Aktif</span>'
                          : '<span class="badge bg-success">Aktif</span>';
                  }
              },
              <?php if (session('role') < 4): ?>
              {
                  data: null,
                  orderable: false,
                  className: 'text-center',
                  render: function(data, type, row) {
                      let editBtn = `<a href="<?= site_url('va-owner/edit/') ?>${row.va_owner_id}" class="btn btn-warning btn-sm py-0 px-2" style="font-size: 11px;">Edit</a>`;
                      
                      let statusBtn = row.deleted_at == null
                          ? `<a href="<?= site_url('va-owner/delete/') ?>${row.va_owner_id}" class="btn btn-danger btn-sm py-0 px-2 ms-1" style="font-size: 11px;" onclick="return confirm('Nonaktifkan anggota ini?')">Non-Aktifkan</a>`
                          : `<a href="<?= site_url('va-owner/undelete/') ?>${row.va_owner_id}" class="btn btn-success btn-sm py-0 px-2 ms-1" style="font-size: 11px;" onclick="return confirm('Aktifkan kembali?')">Aktifkan</a>`;

                      return `<div class="d-inline-flex">${editBtn}${statusBtn}</div>`;
                  }
              }
              <?php endif ?>
          ]
      });

      // Event Filter
      $('#btnFilter').on('click', function() {
          table.ajax.reload();
      });

      $('#btnReset').on('click', function() {
          $('#filterKecamatan').val('');
          $('#filterStatus').val('');
          table.ajax.reload();
      });
  });
</script>

<script>
  // Tangkap data saat modal dibuka
// Dynamic Data Injector ke Modal KTA
$('#ktaModal').on('show.bs.modal', function (event) {
  const button = $(event.relatedTarget);

  const nama = button.data('nama');
  const status = button.data('status');
  const foto = button.data('foto');
  const qr = button.data('qr');

  $('#ktaNama').text(nama);
  $('#ktaFoto').attr('src', foto);
  const idJukir = button.data('idjukir');
  const idTitpar = button.data('idtitpar');
  $('#ktaIdJukir').text(idJukir && idTitpar ? `${idJukir}-${idTitpar}` : (idJukir || '-'));
  $('#ktaQr').attr('src', qr || '<?= base_url('images/no-qr.png') ?>');

  const ktaStatusEl = $('#ktaStatus');
  const valStatus = (status || '').toString().toLowerCase().trim();
  ktaStatusEl.removeClass('bg-primary-subtle text-primary bg-info-subtle text-info bg-secondary-subtle text-secondary border border-primary-subtle border-info-subtle border-secondary-subtle');
  ktaStatusEl.text(valStatus === 'koordinator' ? 'Koordinator' : valStatus === 'anggota' ? 'Anggota' : '-');

  // Panel detail kanan
  $('#detailIdJukir').text(button.data('idjukir') || '-');
  $('#detailNama').text(nama || '-');
  $('#detailStatus').text(valStatus === 'koordinator' ? 'Koordinator' : valStatus === 'anggota' ? 'Anggota' : '-');
  $('#detailTempat').text(button.data('tempat') || '-');
  $('#detailLokasi').text(button.data('lokasi') || '-');
  $('#detailKecamatan').text(button.data('kecamatan') || '-');
  $('#detailVa').text(button.data('va') || '-');
  $('#detailHp').text(button.data('hp') || '-');

  loadKTAData(button.data('va'));
  loadOtherTitpar(button.data('idjukir'),button.data('idtitpar'));

  const berlaku = button.data('berlaku');
  $('#detailBerlaku').text(berlaku ? new Date(berlaku).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '-');

  const aktivasi = button.data('aktivasi');
  $('#detailAktivasi').html(aktivasi === 'Aktif' ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Non-Aktif</span>');
});
</script>


<script>
function setKtaNama(name) {
    const maxLength = 20;
    const displayName = name.length > maxLength
        ? name.substring(0, maxLength) + '...'
        : name;

    document.getElementById('ktaNama').textContent = displayName;
}
</script>

<script>
  async function loadKTAData(anggotaId) {
    try {
        const response = await fetch(
            `<?= base_url('va-owner/get_titpar_anggota/') ?>${anggotaId}`
        );

        const result = await response.json();

        if (!result.status || !result.data || result.data.length === 0) {
            console.log('Tidak ada data titpar');
            return;
        }

        // For now, display the first titpar
        const titpar = result.data[0];

        // =========================
        // DATA TITPAR
        // =========================

        document.getElementById('detailTempat').textContent =
            titpar.titpar_namatempat || '-';

        document.getElementById('detailLokasi').textContent =
            titpar.titpar_lokasi || '-';

        // =========================
        // VA
        // =========================

        const vaList = titpar.va || [];

        document.getElementById('detailVa').textContent =
            vaList.length > 0
                ? vaList.join(', ')
                : '-';

        // =========================
        // ANGGOTA
        // =========================

        const anggotaList = titpar.anggota || [];

        document.getElementById('detailJumlahAnggota').textContent =
            `${anggotaList.length} Anggota`;

        const container =
            document.getElementById('detailAnggotaList');

        container.innerHTML = '';

        if (anggotaList.length === 0) {

            container.innerHTML = `
                <div class="text-muted text-center py-3">
                    Tidak ada anggota pada titik parkir ini
                </div>
            `;

        } else {

            anggotaList.forEach(anggota => {

                const foto = anggota.anggota_foto
                    ? `<?= base_url('uploads/anggota_foto/') ?>${anggota.anggota_foto}`
                    : `<?= base_url('images/no-photo.png') ?>`;
                const formatTanggal = value => value
                    ? new Date(value).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
                    : '-';
                const masaBerlaku = `${formatTanggal(anggota.titpargrup_masaberlakustart)} s/d ${formatTanggal(anggota.titpargrup_masaberlakuend)}`;
                const masaEnd = anggota.titpargrup_masaberlakuend ? new Date(anggota.titpargrup_masaberlakuend) : null;
                const hariTersisa = masaEnd ? Math.ceil((masaEnd - new Date()) / 86400000) : null;
                const masaClass = hariTersisa === null ? 'text-muted' : hariTersisa < 0 ? 'text-danger' : hariTersisa <= 30 ? 'text-warning' : 'text-success';

                container.innerHTML += `
                    <div class="d-flex align-items-center gap-3
                                border rounded-3 p-2 mb-2">

                        <img src="${foto}"
                             class="rounded-circle"
                             style="width:42px;
                                    height:42px;
                                    object-fit:cover;"
                             onerror="this.onerror=null;
                                      this.src='<?= base_url('images/no-photo.png') ?>';">

                        <div class="flex-grow-1 min-width-0">

                            <div class="fw-bold text-dark text-truncate"
                                 style="font-size:13px;">
                                ${anggota.anggota_nama || '-'}
                            </div>

                            <div class="text-muted"
                                 style="font-size:11px;">
                                ${anggota.anggota_newnoreg || anggota.anggota_noreg || '-'}
                            </div>
                            <div class="${masaClass}"
                                 style="font-size:10px;">
                                <i class="bi bi-calendar-check me-1"></i>${masaBerlaku}
                            </div>

                        </div>

                        <div class="text-end">
                            <div class="text-muted"
                                 style="font-size:10px;">
                                ${anggota.titpargrup_staanggota || '-'}
                            </div>
                        </div>

                    </div>
                `;
            });
        }

    } catch (error) {
        console.error('Gagal mengambil data KTA:', error);
    }
}
</script>

<script>
  async function loadOtherTitpar(anggotaId, currentTitparId) {

    const container = document.getElementById('otherTitparList');
    const count = document.getElementById('otherTitparCount');

    container.innerHTML = `
        <div class="text-center text-muted py-3">
            Memuat data...
        </div>
    `;

    try {

        const response = await fetch(
            `<?= base_url('va-owner/other-titpar/') ?>${anggotaId}/${currentTitparId}`
        );

        const result = await response.json();

        if (!result.status || !result.data || result.data.length === 0) {

            count.textContent = '0';

            container.innerHTML = `
                <div class="text-center text-muted py-3">
                    Tidak ada titik parkir lainnya.
                </div>
            `;

            return;
        }

        count.textContent = result.data.length;

        container.innerHTML = '';

        result.data.forEach(titpar => {

            container.innerHTML += `
                <div class="border rounded-3 p-3 mb-2 bg-white shadow-sm">

                    <div class="d-flex align-items-start justify-content-between gap-3">

                        <div class="flex-grow-1 min-width-0">

                            <div class="fw-bold text-dark mb-1">
                                ${titpar.titpar_namatempat || '-'}
                            </div>

                            <div class="text-muted mb-1"
                                 style="font-size:12px;">
                                <i class="bi bi-geo-alt me-1"></i>
                                ${titpar.titpar_lokasi || '-'}
                            </div>

                            <div class="text-muted"
                                 style="font-size:11px;">
                                No. Reg:
                                ${titpar.titpar_newnoreg || titpar.titpar_noreg || '-'}
                            </div>

                        </div>

                        <div class="text-end">

                            <div class="small text-muted mb-1">
                                VA
                            </div>

                            <div class="fw-bold text-primary"
                                 style="font-size:12px;">
                                ${titpar.va_owner_va || '-'}
                            </div>

                        </div>

                    </div>

                </div>
            `;
        });

    } catch (error) {

        console.error(error);

        count.textContent = '0';

        container.innerHTML = `
            <div class="text-center text-danger py-3">
                Gagal memuat titik parkir.
            </div>
        `;
    }
}
</script>

<?php echo view('footer'); ?>
