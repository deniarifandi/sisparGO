<?php
$wilayahKecamatanRows = db_connect()->table('kelurahan')->select('kecamatan_id,kecamatan_nama')->where('provinsi', 'Jawa Timur')->where('kabupaten_kota', 'Kota Malang')->distinct()->orderBy('kecamatan_nama')->get()->getResultArray();
$wilayahKecamatanMap = [];
foreach ($wilayahKecamatanRows as $wilayah) $wilayahKecamatanMap[$wilayah['kecamatan_id']] = $wilayah['kecamatan_nama'];
?>
<?php echo view('header'); ?>

<?php if (session()->getFlashdata('message')): ?>
    <div class="alert alert-info">
        <?= session()->getFlashdata('message'); ?>
    </div>
<?php endif; ?>

<style type="text/css">
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
  .filter-toggle-btn { display: none; }
  .filter-btn-group { display: flex; gap: 6px; }

  .summary-card {
    border: 1px solid #eef0f3;
    border-radius: 10px;
  }
  .summary-card .summary-icon {
    width: 34px; height: 34px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
    background: #d4edda; color: #1e7e34;
  }
  .summary-card .summary-value { font-size: 15px; font-weight: 700; line-height: 1.2; }
  .summary-card .summary-label { font-size: 11px; color: #8a8f98; }

  #overlaySpinner {
    position: absolute;
    inset: 0;
    background: rgba(255,255,255,0.75);
    z-index: 5;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
  }
  .table-wrap-relative { position: relative; }

  @media (max-width: 767.98px) {
    .filter-toggle-btn { display: inline-flex; }
    .filter-collapse-body { display: none; }
    .filter-collapse-body.show { display: block; }
    .filter-btn-group .btn { flex: 1; }
  }
</style>

<div class="card shadow-sm">
  <div class="card-header py-3 d-flex justify-content-between align-items-center">
    <div>
      <h6 class="m-0 font-weight-bold text-primary">
        <i class="fa fa-receipt me-1"></i> Daftar Transaksi
      </h6>
      <small class="text-muted d-none d-md-block">Riwayat transaksi pembayaran juru parkir</small>
    </div>
    <div class="d-flex align-items-center gap-2">
      <a href="<?= base_url('import') ?>" class="btn btn-outline-primary btn-sm">
        <i class="fa fa-file-import me-1"></i> Import Data
      </a>
      <button type="button" class="btn btn-outline-secondary btn-sm filter-toggle-btn" id="btnToggleFilter">
        <i class="fa fa-sliders-h me-1"></i> Filter
      </button>
    </div>
  </div>

  <div class="card-body">

    <!-- Filter Panel -->
    <div class="filter-card mb-3 filter-collapse-body" id="filterPanel">
      <div class="row g-2 align-items-end">
        <div class="col-6 col-md-3">
          <label class="filter-label" for="minDate">Tanggal Mulai</label>
          <input type="date" id="minDate" class="form-control form-control-sm" placeholder="Tanggal Mulai">
        </div>
        <div class="col-6 col-md-3">
          <label class="filter-label" for="maxDate">Tanggal Akhir</label>
          <input type="date" id="maxDate" class="form-control form-control-sm" placeholder="Tanggal Akhir">
        </div>
        <div class="col-12 col-md-6 filter-btn-group">
          <button id="filterBtn" class="btn btn-sm btn-primary sendBtn" title="Terapkan filter tanggal">
            <i class="fa fa-filter"></i> Terapkan
          </button>
          <button id="resetBtn" class="btn btn-sm btn-outline-secondary sendBtn" title="Reset filter tanggal">
            <i class="fa fa-undo"></i> Reset
          </button>
        </div>
      </div>
    </div>

    <!-- Ringkasan -->
    <div class="row g-2 mb-3">
      <div class="col-12 col-md-4">
        <div class="card summary-card">
          <div class="card-body py-2 px-3 d-flex align-items-center gap-2">
            <div class="summary-icon"><i class="fa fa-money-bill-wave"></i></div>
            <div>
              <div class="summary-label">Total Nominal (Halaman Ini)</div>
              <div class="summary-value" id="footerTotalDisplay">Rp 0</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="table-responsive table-wrap-relative">
      <div id="overlaySpinner" class="d-none">
        <div class="text-center">
          <div class="spinner-border text-primary" role="status"></div>
          <div class="small text-muted mt-2">Memuat data...</div>
        </div>
      </div>
      <table id="usersTable" class="display table table-striped table-bordered table-hover" style="font-size: 12px; width: 100%;">
        <thead>
          <tr>
            <th>ID</th>
            <th>VA</th>
            <th>Anggota Nama</th>
            <th>Nama Tempat</th>
            <th>Lokasi</th>
            <th>Kecamatan</th>
            <th>Tanggal</th>
            <th>Nominal</th>
            <th>Ref. Transaksi</th>
          </tr>
        </thead>
        <tfoot>
          <tr>
            <th colspan="7" style="text-align:right">Subtotal Halaman:</th>
            <th></th>
            <th></th>
          </tr>
        </tfoot>
      </table>
      <small class="text-muted d-block mt-1">
        <i class="fa fa-circle-info me-1"></i>
        Subtotal hanya menjumlahkan baris yang tampil di halaman saat ini, bukan seluruh hasil filter.
      </small>
    </div>

  </div>
</div>

<script>
const kelurahanMap = <?= json_encode($wilayahKecamatanMap, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

let table;

$(document).ready(function() {
  const isMobile = $(window).width() < 768;
  if (isMobile) {
    $('#filterPanel').removeClass('show');
  } else {
    $('#filterPanel').addClass('show');
  }
  $('#btnToggleFilter').on('click', function() {
    $('#filterPanel').toggleClass('show');
  });

  const overlay = document.getElementById("overlaySpinner");

  table = $('#usersTable').DataTable({
    processing: true,
    serverSide: true,
    dom: '<"top"lBf>rt<"bottom"ip><"clear">',
    buttons: [
      { extend: 'excelHtml5', className: 'btn-sm' },
      { extend: 'csvHtml5', className: 'btn-sm' },
      { extend: 'pdfHtml5', className: 'btn-sm' },
      { extend: 'print', className: 'btn-sm' }
    ],
    pageLength: 10,
    lengthMenu: [[10, 25, 50, 250, -1], [10, 25, 50, 250, "Semua"]],
    language: {
      processing: "Memuat data...",
      emptyTable: "Tidak ada data untuk ditampilkan",
      zeroRecords: "Data tidak ditemukan, coba ubah rentang tanggal",
      search: "Cari:",
      lengthMenu: "Tampilkan _MENU_ baris",
      info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
      infoEmpty: "Tidak ada data",
      infoFiltered: "(disaring dari _MAX_ total data)",
      paginate: { first: "Awal", last: "Akhir", next: "Berikutnya", previous: "Sebelumnya" }
    },
    ajax: {
      url: "<?= site_url('transaksi/data') ?>",
      type: "POST",
      dataSrc: "data",
      data: function(d) {
        d.minDate = $('#minDate').val();
        d.maxDate = $('#maxDate').val();
      }
    },
    columns: [
      { data: 'transaksi_id' },
      { data: 'transaksi_va'},
      { data: 'anggota_nama' },
      { data: 'titpar_namatempat'},
      { data: 'titpar_lokasi'},
      {
        data: 'titpar_klrhnid',
        render: function(data) {
          const prefix = data ? data.substring(0, 6) : '';
          return kelurahanMap[prefix] || 'Tidak diketahui';
        }
      },
      {
        data: 'transaksi_tanggal',
        className: 'dt-body-right',
        render: function (data, type) {
          if (!data) return '';
          if (type === 'display' || type === 'filter') {
            const date = new Date(data);
            if (isNaN(date.getTime())) return data;
            // Format as: 5 Oktober 2025
            return date.toLocaleDateString('id-ID', {
              day: 'numeric',
              month: 'long',
              year: 'numeric'
            });
          }
          return data;
        }
      },
      {
        data: 'transaksi_nominal',
        className: 'dt-body-right',
        render: $.fn.dataTable.render.number(',', '.', 0, 'Rp ')
      },
      { data: 'coreReferenceNo'}
    ],
    order: [[6, 'desc']],
    footerCallback: function(row, data, start, end, display) {
      const api = this.api();
      const intVal = i => typeof i === 'string'
        ? i.replace(/[Rp,. ]/g, '') * 1
        : typeof i === 'number' ? i : 0;
      const pageTotal = api.column(7, { page: 'current' }).data().reduce((a, b) => a + intVal(b), 0);
      const formatted = 'Rp ' + pageTotal.toLocaleString('id-ID');
      $(api.column(7).footer()).html(formatted);
      $('#footerTotalDisplay').text(formatted);
    },
    drawCallback: function() {
      if (overlay) overlay.classList.add("d-none");
    }
  });

  $('#filterBtn').on('click', function() {
    if (overlay) overlay.classList.remove("d-none");
    table.ajax.reload();
    if (isMobile) $('#filterPanel').removeClass('show');
  });

  $('#resetBtn').on('click', function() {
    $('#minDate, #maxDate').val('');
    if (overlay) overlay.classList.remove("d-none");
    table.ajax.reload();
  });
});
</script>

<?php echo view('footer'); ?>
