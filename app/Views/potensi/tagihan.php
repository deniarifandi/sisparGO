<?php
$wilayahRows = db_connect()->table('kelurahan')->select('id,kecamatan_id,kecamatan_nama,kelurahan_nama')->where('provinsi', 'Jawa Barat')->where('kabupaten_kota', 'Kota Bogor')->orderBy('kecamatan_nama')->orderBy('kelurahan_nama')->get()->getResultArray();
$kelurahanData = $wilayahKecamatanMap = [];
foreach ($wilayahRows as $wilayah) {
    $kelurahanData[$wilayah['kecamatan_id']][] = ['id' => $wilayah['id'], 'name' => $wilayah['kelurahan_nama']];
    $wilayahKecamatanMap[$wilayah['kecamatan_id']] = $wilayah['kecamatan_nama'];
}
$wilayahKecamatan = [];
foreach ($wilayahKecamatanMap as $id => $nama) $wilayahKecamatan[] = ['kecamatan_id' => $id, 'kecamatan_nama' => $nama];
?>
<?php echo view('header'); ?>

<style type="text/css">
  /* Utility & Typography */
  .table-compact { font-size: 11px; }
  .table-compact th, .table-compact td { padding: 6px 8px !important; vertical-align: middle; }
  .wrap-text { white-space: normal !important; word-break: break-word; min-width: 120px; max-width: 180px; }

  /* Table Layout & Colors */
  table.dataTable { width: 100% !important; margin: 0 !important; }
  .dataTables_wrapper { overflow-x: auto; -webkit-overflow-scrolling: touch; }
  .highlight-col { background-color: #fff3cd !important; }
  .green-col { background-color: #d4edda !important; }

  /* Compact Progress Bar */
  .progress-compact { height: 12px; background: #e9ecef; border-radius: 4px; overflow: hidden; margin-bottom: 2px; }
  .progress-compact .bar { height: 100%; transition: width .3s ease; }

  /* Card & Layout Enhancements */
  .card { border: none; border-radius: 10px; }
  .card-header { background-color: #ffffff; border-bottom: 1px solid #f0f0f0; }

  /* Filter panel */
  .filter-label {
    font-size: 11px;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: .02em;
    margin-bottom: 2px;
    display: block;
  }
  .filter-card {
    background: #f8f9fb;
    border: 1px solid #eef0f3;
    border-radius: 10px;
    padding: 14px;
  }
  .filter-toggle-btn {
    display: none;
  }

  /* Summary cards */
  .summary-card {
    border: 1px solid #eef0f3;
    border-radius: 10px;
    height: 100%;
  }
  .summary-card .summary-icon {
    width: 34px; height: 34px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
  }
  .summary-card .summary-value { font-size: 15px; font-weight: 700; line-height: 1.2; }
  .summary-card .summary-label { font-size: 11px; color: #8a8f98; }
  .summary-section-title {
    font-size: 12px;
    font-weight: 700;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: .03em;
    margin: 4px 0 8px;
  }

  /* Legend for percentage bars */
  .pct-legend { font-size: 11px; color: #8a8f98; display: flex; gap: 14px; flex-wrap: wrap; }
  .pct-legend span { display: inline-flex; align-items: center; gap: 4px; }
  .pct-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }

  .table-wrap-relative { position: relative; }
  #potensiTable_wrapper .dataTables_processing { display: none !important; }
  .loading-notice {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 1060;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 15px 20px;
    border-radius: 10px;
    color: #fff;
    background: rgba(33, 37, 41, .92);
    box-shadow: 0 4px 14px rgba(0,0,0,.2);
    font-size: 14px;
  }
  .loading-notice .spinner-border { width: 23px; height: 23px; }

  /* Mobile Adjustments */
  @media (max-width: 767.98px) {
    .card-body { padding: 0.75rem; }
    .filter-btn-group { display: flex; gap: 6px; margin-top: 4px; }
    .filter-btn-group .btn { flex: 1; }
    .filter-toggle-btn { display: inline-flex; }
    .filter-collapse-body { display: none; }
    .filter-collapse-body.show { display: block; }
  }
</style>

<div class="card shadow-sm">
  <div class="card-header py-3 d-flex justify-content-between align-items-center">
    <div>
      <h6 class="m-0 font-weight-bold text-primary">
        <i class="fa fa-chart-line me-1"></i> Daftar Potensi &amp; Realisasi Kota Bogor
      </h6>
      <small class="text-muted d-none d-md-block">Rekap tagihan, pengurangan, dan realisasi per juru parkir Kota Bogor</small>
    </div>
    <button type="button" class="btn btn-outline-secondary btn-sm filter-toggle-btn" id="btnToggleFilter">
      <i class="fa fa-sliders-h me-1"></i> Filter
    </button>
  </div>

  <div class="card-body">

    <!-- Filter Panel -->
    <div class="filter-card mb-3 filter-collapse-body" id="filterPanel">
      <div class="row g-2 align-items-end">

        <div class="col-6 col-md-2">
          <label class="filter-label" for="filterId">Cari ID / Nama Jukir</label>
          <div class="input-group input-group-sm">
            <span class="input-group-text bg-white"><i class="fa fa-id-card text-muted"></i></span>
            <input type="text" id="filterId" class="form-control form-control-sm" placeholder="Masukkan ID atau nama jukir">
          </div>
        </div>

        <div class="col-6 col-md-2">
          <label class="filter-label" for="filterJenis">Jenis Parkir</label>
          <select id="filterJenis" class="form-control form-control-sm">
            <option value="">Semua (TJU &amp; TKP)</option>
            <option value="TJU">TJU</option>
            <option value="TKP">TKP</option>
          </select>
        </div>

        <div class="col-4 col-md-2">
          <label class="filter-label" for="filterBulan">Bulan</label>
          <select id="filterBulan" class="form-control form-control-sm">
            <option value="">Semua Bulan</option>
            <option value="1">Januari</option>
            <option value="2">Februari</option>
            <option value="3">Maret</option>
            <option value="4">April</option>
            <option value="5">Mei</option>
            <option value="6">Juni</option>
            <option value="7">Juli</option>
            <option value="8">Agustus</option>
            <option value="9">September</option>
            <option value="10">Oktober</option>
            <option value="11">November</option>
            <option value="12">Desember</option>
          </select>
        </div>

        <div class="col-4 col-md-2">
          <label class="filter-label" for="filterTahun">Tahun</label>
          <select id="filterTahun" class="form-control form-control-sm">
            <option value="">Semua Tahun</option>
            <?php for($y=2024; $y<=date('Y'); $y++): ?>
              <option value="<?= $y ?>"><?= $y ?></option>
            <?php endfor; ?>
          </select>
        </div>

        <div class="col-4 col-md-2">
          <label class="filter-label" for="filterKec">Kecamatan</label>
          <select id="filterKec" class="form-control form-control-sm">
            <option value="">Semua Kecamatan Kota Bogor</option>
            <?php foreach ($wilayahKecamatan as $wilayah): ?>
              <option value="<?= esc($wilayah['kecamatan_id']) ?>"><?= esc($wilayah['kecamatan_nama']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-12 col-md-2 filter-btn-group">
          <button id="btnFilter" class="btn btn-primary btn-sm sendBtn" title="Terapkan filter">
            <i class="fa fa-filter"></i> <span class="d-none d-md-inline">Terapkan</span>
          </button>
          <button id="btnReset" class="btn btn-outline-secondary btn-sm sendBtn" title="Reset semua filter">
            <i class="fa fa-undo"></i> <span class="d-none d-md-inline">Reset</span>
          </button>
          <button id="btnExport" class="btn btn-success btn-sm" title="Export data yang tampil ke Excel">
            <i class="fa fa-file-excel"></i> <span class="d-none d-md-inline">Export</span>
          </button>
        </div>

        

      </div>
    </div>

    <div class="card filter-card mb-2 filter-collapse-body" id="filterPanel">
  <!-- Bagian Isi Card -->
    <div class="card-body p-0" >
      <div class="row g-2 align-items-end">
        <div class="col-12 col-md-6">
          <button id="btnExportResume" class="btn btn-outline-success btn-sm" title="Export rekap per kecamatan">
            <i class="fa fa-layer-group"></i> <span class="d-none d-md-inline">Resume Per Kecamatan</span>
          </button>
        </div>
      </div>
    </div>
  </div>


    <!-- Ringkasan Bulanan & Tahunan -->
    <div class="mb-3">
      <div class="summary-section-title">Ringkasan Bulan Berjalan</div>
      <div class="row g-2 mb-2">
        <div class="col-6 col-md-4">
          <div class="card summary-card">
            <div class="card-body py-2 px-3 d-flex align-items-center gap-2">
              <div class="summary-icon" style="background:#fff3cd;color:#b8860b;"><i class="fa fa-file-invoice"></i></div>
              <div>
                <div class="summary-label">Total Tagihan</div>
                <div class="summary-value" id="totalPotensiBulanan">Rp 0</div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-4">
          <div class="card summary-card">
            <div class="card-body py-2 px-3 d-flex align-items-center gap-2">
              <div class="summary-icon" style="background:#f1f1f1;color:#6c757d;"><i class="fa fa-minus-circle"></i></div>
              <div>
                <div class="summary-label">Total Pengurangan</div>
                <div class="summary-value" id="totalKendalaBulanan">Rp 0</div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-4">
          <div class="card summary-card">
            <div class="card-body py-2 px-3 d-flex align-items-center gap-2">
              <div class="summary-icon" style="background:#d4edda;color:#1e7e34;"><i class="fa fa-check-circle"></i></div>
              <div>
                <div class="summary-label">Total Realisasi</div>
                <div class="summary-value" id="totalRealisasiBulanan">Rp 0</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="summary-section-title">Ringkasan Tahun Berjalan</div>
      <div class="row g-2">
        <div class="col-6 col-md-4">
          <div class="card summary-card">
            <div class="card-body py-2 px-3 d-flex align-items-center gap-2">
              <div class="summary-icon" style="background:#fff3cd;color:#b8860b;"><i class="fa fa-bullseye"></i></div>
              <div>
                <div class="summary-label">Total Target</div>
                <div class="summary-value" id="totalTargetTahun">Rp 0</div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-4">
          <div class="card summary-card">
            <div class="card-body py-2 px-3 d-flex align-items-center gap-2">
              <div class="summary-icon" style="background:#f1f1f1;color:#6c757d;"><i class="fa fa-minus-circle"></i></div>
              <div>
                <div class="summary-label">Total Pengurangan</div>
                <div class="summary-value" id="totalKendalaTahun">Rp 0</div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-4">
          <div class="card summary-card">
            <div class="card-body py-2 px-3 d-flex align-items-center gap-2">
              <div class="summary-icon" style="background:#d4edda;color:#1e7e34;"><i class="fa fa-check-circle"></i></div>
              <div>
                <div class="summary-label">Total Realisasi</div>
                <div class="summary-value" id="totalRealisasiTahun">Rp 0</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="pct-legend mb-2">
      <span><span class="pct-dot" style="background:#007bff;"></span> % Bulanan normal</span>
      <span><span class="pct-dot" style="background:#28a745;"></span> % Tahunan normal</span>
      <span><span class="pct-dot" style="background:#dc3545;"></span> Di Bawah 80%</span>
    </div>

    <!-- Data Table Container -->
    <div class="table-responsive table-wrap-relative">
      <table id="potensiTable" class="table table-striped table-bordered table-hover nowrap table-compact">
        <thead class="thead-light">
          <tr>
            <th>No.</th>
            <th class="highlight-col">Jukir / VA</th>
            <th>Tempat Parkir</th>
            <th>Kel / Kec</th>
            <th class="green-col">Tagihan Bln</th>
            <th>Pengurangan (Bln)</th>
            <th>Realisasi Bln</th>
            <th>% Bln</th>
            <th class="green-col">Target Thn</th>
            <th>Pengurangan (Thn)</th>
            <th>Realisasi Thn</th>
            <th>% Thn</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody></tbody>
        <tfoot>
          <tr>
            <th></th>
            <th>Subtotal Halaman</th>
            <th></th>
            <th></th>

            <th id="footerPotensiBulanan"></th>
            <th id="footerKendalaBulanan"></th>
            <th id="footerRealisasiBulanan"></th>
            <th></th>

            <th id="footerTargetTahun"></th>
            <th id="footerKendalaTahun"></th>
            <th id="footerRealisasiTahun"></th>
            <th></th>

            <th></th>
          </tr>
        </tfoot>
      </table>
    </div>
      <small class="text-muted d-block mt-1">
        <i class="fa fa-circle-info me-1"></i>
        Kartu ringkasan di atas menampilkan total keseluruhan data hasil filter, sedangkan baris "Subtotal Halaman" di bawah tabel hanya menjumlahkan baris yang tampil di halaman ini.
      </small>

  </div>
</div>

<div id="loadingNotice" class="loading-notice" role="status" aria-live="polite">
  <span class="spinner-border spinner-border-sm text-light" aria-hidden="true"></span>
  <span>Memuat data...</span>
</div>

<script>
  const kelurahanMap = <?= json_encode($wilayahKecamatanMap, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
  const kelurahanData = <?= json_encode($kelurahanData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

  $(document).ready(function() {
    const isMobile = $(window).width() < 768;

    // Filter panel default: shown on desktop, collapsed on mobile
    if (isMobile) {
      $('#filterPanel').removeClass('show');
    } else {
      $('#filterPanel').addClass('show');
    }
    $('#btnToggleFilter').on('click', function() {
      $('#filterPanel').toggleClass('show');
    });

    var table = $('#potensiTable').DataTable({
      processing: true,
      serverSide: true,

      lengthMenu: [
        [10, 25, 50, 100, -1],
        [10, 25, 50, 100, "All"]
      ],

      pageLength: 25,

      scrollX: true,
      scrollCollapse: true,
      fixedColumns: isMobile ? false : { leftColumns: 2, rightColumns: 1 },
      language: {
        processing: "Memuat data...",
        emptyTable: "Tidak ada data untuk ditampilkan",
        zeroRecords: "Data tidak ditemukan, coba ubah filter",
        search: "Cari:",
        lengthMenu: "Tampilkan _MENU_ baris",
        info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
        infoEmpty: "Tidak ada data",
        infoFiltered: "(disaring dari _MAX_ total data)",
        paginate: { first: "Awal", last: "Akhir", next: "Berikutnya", previous: "Sebelumnya" }
      },
      ajax: {
        url: "<?= site_url('potensi/datatagihan') ?>",
        type: "POST",
        data: function (d) {
          d.filter_id = $('#filterId').val(); // Parameter Filter Khusus ID
          d.jenis     = $('#filterJenis').val();
          d.bulan     = $('#filterBulan').val();
          d.tahun     = $('#filterTahun').val();
          d.kecamatan = $('#filterKec').val();
          d.reset     = $('#btnReset').data('reset') ? 1 : 0;
        },
        dataSrc: function(json) {

          const formatRupiah = (value) => {
            return new Intl.NumberFormat('id-ID', {
              style: 'currency',
              currency: 'IDR',
              minimumFractionDigits: 0
            }).format(value || 0);
          };

          $('#totalPotensiBulanan').text(
            formatRupiah(json.summary.total_potensi_bulanan)
            );

          $('#totalKendalaBulanan').text(
            formatRupiah(json.summary.total_kendala_bulanan)
            );

          $('#totalRealisasiBulanan').text(
            formatRupiah(json.summary.total_realisasi_bulanan)
            );

          $('#totalTargetTahun').text(
            formatRupiah(json.summary.total_target_tahun)
            );

          $('#totalKendalaTahun').text(
            formatRupiah(json.summary.total_kendala_tahun)
            );

          $('#totalRealisasiTahun').text(
            formatRupiah(json.summary.total_realisasi_tahun)
            );

          return json.data;
        },

      },
        footerCallback: function (row, data, start, end, display) {

    const api = this.api();

    const formatNumber = (value) => {
        return new Intl.NumberFormat('id-ID', {
            maximumFractionDigits: 0
        }).format(value || 0);
    };

    // Column indexes
    // 4 = Tagihan Bln
    // 5 = Pengurangan Bln
    // 6 = Realisasi Bln
    // 8 = Target Thn
    // 9 = Pengurangan Thn
    // 10 = Realisasi Thn

    const total = (columnIndex) => {
        return api
            .column(columnIndex, { page: 'current' })
            .data()
            .reduce(function (a, b) {
                return (parseFloat(a) || 0) + (parseFloat(b) || 0);
            }, 0);
    };

    $('#footerPotensiBulanan').text(
        formatNumber(total(4))
    );

    $('#footerKendalaBulanan').text(
        formatNumber(total(5))
    );

    $('#footerRealisasiBulanan').text(
        formatNumber(total(6))
    );

    $('#footerTargetTahun').text(
        formatNumber(total(8))
    );

    $('#footerKendalaTahun').text(
        formatNumber(total(9))
    );

    $('#footerRealisasiTahun').text(
        formatNumber(total(10))
    );
},
      columns: [
        {
          data: null, orderable: false, searchable: false, width: '30px',
          render: (d, t, r, meta) => meta.row + meta.settings._iDisplayStart + 1
        },
        /* Gabungkan Nama, ID Jukir, dan VA dalam 1 Sel */
        {
          data: null, className: 'wrap-text', width: '140px',
          render: (d, t, r) => `<b>${r.anggota_nama}</b> <small class="text-muted">(${r.anggota_id})</small><br><span class="badge badge-primary border text-primary">VA: ${r.va_owner_va || '-'}</span>`
        },
        {
          data: null, 
          className: 'wrap-text', 
          width: '180px',
          render: function (d, t, r) {
            const lat = r.titpar_latitude;
            const lng = r.titpar_longitude;
            
            let btnMaps = '';
            if (lat && lng) {
              const mapsUrl = `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`;
              btnMaps = `
                <a href="${mapsUrl}" target="_blank" rel="noopener noreferrer" 
                   class="btn btn-danger btn-sm rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 ms-2" 
                   style="width: 32px; height: 32px; padding: 0;" title="Petunjuk Arah">
                  <i class="fas fa-location-arrow" style="font-size: 13px;"></i>
              </a>`;
            }

            return `
              <div class="d-flex align-items-center justify-content-between">
                <div class="me-auto" style="min-width: 0;">
                  <div class="fw-bold text-truncate" style="font-size: 13px;">${r.titpar_namatempat ?? '-'}</div>
                  <small class="text-muted d-block text-truncate">${r.titpar_lokasi ?? '-'}</small>
                </div>
                ${btnMaps}
              </div>
            `;
          }
        },
        {
          data: "titpar_klrhnid", className: "wrap-text", width: "130px",
          render: (id) => {
            if (!id) return "-";
            const kecCode = id.toString().substring(0, 6);
            const kecName = kelurahanMap[kecCode] || "-";
            let kelName = "-";
            if (kelurahanData[kecCode]) {
              const match = kelurahanData[kecCode].find(k => k.id === id.toString());
              if (match) kelName = match.name;
            }
            return `${kelName}<br><small class="text-muted">${kecName}</small>`;
          }
        },

        // Monthly
        { data: 'potensi_bulanan', className: 'text-end', width: '75px', render: $.fn.dataTable.render.number(',', '.', 0) },
        { data: 'kendala_tagihan_bulan', className: 'text-end', width: '75px', render: $.fn.dataTable.render.number(',', '.', 0) },
        { data: 'realisasi_bulanan', className: 'text-end', width: '75px', render: $.fn.dataTable.render.number(',', '.', 0) },
        {
          data: "persentase", className: 'text-end', orderable: true, width: "80px",
          render: function (data, type) {
            const real = parseFloat(data) || 0;
            if (type === "sort" || type === "filter") return real;
            const color = real > 80 ? "#007bff" : "#dc3545";
            return `
              <div class="progress-compact"><div class="bar" style="width:${Math.min(real, 100)}%; background:${color};"></div></div>
              <small>${real.toFixed(1)}%</small>`;
            }
          },

        // YTD
          { data: 'target_tahun', className: 'text-end', width: '75px', render: $.fn.dataTable.render.number(',', '.', 0) },
          { data: 'kendala_tagihan_tahun', className: 'text-end', width: '75px', render: $.fn.dataTable.render.number(',', '.', 0) },
          { data: 'realisasi_tahun', className: 'text-end', width: '75px', render: $.fn.dataTable.render.number(',', '.', 0) },
          {
            data: "persentase_tahun", className: 'text-end', orderable: true, width: "80px",
            render: function (data, type) {
              const real = parseFloat(data) || 0;
              if (type === "sort" || type === "filter") return real;
              const color = real > 80 ? "#28a745" : "#dc3545";
              return `
              <div class="progress-compact"><div class="bar" style="width:${Math.min(real, 100)}%; background:${color};"></div></div>
                <small>${real.toFixed(1)}%</small>`;
              }
            },

        // Action
            {
              data: null,
              width: '60px',
              orderable: false,
              searchable: false,
              render: function(data, type, row) {
                if (!row.va_owner_va) {
                  return `
                <button class="btn btn-secondary btn-sm py-0 px-2" 
                        style="font-size: 11px;" 
                        disabled>
                  Detail
                </button>
                  `;
                }

                const url = `<?= base_url('transaksi/va') ?>/${encodeURIComponent(row.va_owner_va)}`;

                return `
              <a class="btn btn-warning btn-sm py-0 px-2"
                 style="font-size: 11px;"
                 href="${url}">
                Detail
              </a>
                `;
              }
            }
          ],

        });

const loadingNotice = document.getElementById("loadingNotice");
const setLoading = (loading) => loadingNotice && loadingNotice.classList.toggle('d-none', !loading);

// Tutup/nyalakan overlay untuk semua request DataTables, termasuk search,
// sorting, pagination, filter, dan reload AJAX.
table.on('processing.dt', function(e, settings, processing) {
  if (processing) setLoading(true);
});
table.on('preXhr.dt', function() {
  setLoading(true);
});
table.on('draw.dt', function() {
  setLoading(false);
});
table.on('error.dt', function() {
  setLoading(false);
});

    // Event Enter pada input Filter ID
$('#filterId').on('keyup', function(e) {
  if (e.key === 'Enter' || e.keyCode === 13) {
    $('#btnFilter').click();
  }
});

    // Event Klik Tombol Filter
$('#btnFilter').on('click', function() {
  $('#btnReset').data('reset', 0);
  setLoading(true);
  table.ajax.reload();
  if (isMobile) $('#filterPanel').removeClass('show');
});

    // Event Klik Tombol Reset
$('#btnReset').on('click', function() {
  $('#filterId, #filterBulan, #filterTahun, #filterKec, #filterJenis').val('');
  $(this).data('reset', 1);

  setLoading(true);
  table.ajax.reload();
});
});
</script>

<script>
  $('#btnExport').on('click', function() {
    const $btn = $(this);
    const originalHtml = $btn.html();
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> <span class="d-none d-md-inline">Menyiapkan...</span>');

    const params = new URLSearchParams({
      filter_id: $('#filterId').val(),
      bulan:     $('#filterBulan').val(),
      tahun:     $('#filterTahun').val(),
      kecamatan: $('#filterKec').val(),
      jenis:     $('#filterJenis').val()
    });
    window.location.href = "<?= site_url('potensi/export') ?>?" + params.toString();

    setTimeout(() => $btn.prop('disabled', false).html(originalHtml), 2000);
  });
</script>

<script>
  $('#btnExportResume').on('click', function() {
  const params = new URLSearchParams({
    filter_id: $('#filterId').val(),
    bulan:     $('#filterBulan').val(),
    tahun:     $('#filterTahun').val(),
    kecamatan: $('#filterKec').val()
  });
  window.location.href = "<?= site_url('potensi/export-resume') ?>?" + params.toString();
});
</script>

<?php echo view('footer'); ?>


