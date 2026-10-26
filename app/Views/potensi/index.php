<?php
$wilayahRows = db_connect()->table('kelurahan')->select('id,kecamatan_id,kelurahan_nama')->where('provinsi', 'Jawa Barat')->where('kabupaten_kota', 'Kota Bogor')->orderBy('kelurahan_nama')->get()->getResultArray();
$kelurahanData = [];
foreach ($wilayahRows as $wilayah) $kelurahanData[$wilayah['kecamatan_id']][] = ['id' => $wilayah['id'], 'name' => $wilayah['kelurahan_nama']];
$wilayahKecamatan = db_connect()->table('kelurahan')->select('kecamatan_id,kecamatan_nama')->where('provinsi', 'Jawa Barat')->where('kabupaten_kota', 'Kota Bogor')->distinct()->orderBy('kecamatan_nama')->get()->getResultArray();
$wilayahKecamatanMap = [];
foreach ($wilayahKecamatan as $wilayah) $wilayahKecamatanMap[$wilayah['kecamatan_id']] = $wilayah['kecamatan_nama'];
?>
<?php echo view('header'); ?>

<?php if (session()->getFlashdata('error')): ?>
  <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<style type="text/css">
  table.dataTable { width: 100% !important; }
  .dataTables_wrapper { overflow-x: auto; }

  table.table-bordered { border-collapse: collapse; border: 1px solid #ddd; }
  .table-bordered th, .table-bordered td { border: 1px solid #e6ebf4; }

  .highlight-col { background-color: #fff3cd; }
  .green-col { background-color: #d4edda; }

  /* keep headers usable when many columns */
  th, td { white-space: nowrap; }
</style>

<div class="card">
  <div class="card-header">Daftar Potensi Kota Bogor</div>

  <div class="card-body">
    <div style="overflow:auto;">

      <!-- FILTERS -->
      <div class="row g-2 mb-3">
        <div class="col-md-2">
          <select id="filterKec" class="form-control">
            <option value="">-- Kecamatan --</option>
            <?php foreach ($wilayahKecamatan as $wilayah): ?>
              <option value="<?= esc($wilayah['kecamatan_id']) ?>"><?= esc($wilayah['kecamatan_nama']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

      <div class="col-md-2">
          <select id="filterKel" class="form-control">
            <option value="">-- Kelurahan --</option>
          </select>
        </div>

        <div class="col-md-5 d-flex align-items-center gap-2">
          <button id="btnFilter" class="btn btn-primary">Filter</button>
          <button id="btnReset" class="btn btn-secondary">Reset</button>

          <!-- Column Toggle -->
        <!--   <div class="dropdown d-inline-block ms-2">
            <button class="btn btn-info dropdown-toggle" type="button" data-bs-toggle="dropdown">
              Kolom
            </button>
            <ul class="dropdown-menu" id="columnToggleMenu"></ul>
          </div> -->
        </div>
      </div>

      <!-- TABLE -->
      <table id="potensiTable" class="display nowrap table-bordered" style="font-size:12px; width:100%;">
        <thead>
          <tr>
            <th class="highlight-col">VA</th>
            <th class="highlight-col">Nama</th>
            <th>ID Jukir</th>
            <th>Tempat Parkir</th>
            <th>Location</th>
            <th>Kelurahan (Kecamatan)</th>
            <th class="green-col">Senin</th>
            <th class="green-col">Selasa</th>
            <th class="green-col">Rabu</th>
            <th class="green-col">Kamis</th>
            <th class="green-col">Jumat</th>
            <th class="green-col">Sabtu</th>
            <th class="green-col">Minggu</th>
            <?php if (session('role') !== null && (int) session('role') <= 2): ?>
              <th>Action</th>
            <?php endif ?>
          </tr>
        </thead>
      </table>

    </div>
  </div>
</div>

<script>
// ===============================
// KECAMATAN & KELURAHAN DATA
// ===============================
const kecamatanNames = <?= json_encode($wilayahKecamatanMap, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

const kelurahanData = <?= json_encode($kelurahanData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
// Populate Kelurahan when Kecamatan changes
function populateKelurahan() {
  const kec = $('#filterKec').val();
  const $kel = $('#filterKel');
  $kel.html('<option value="">-- Kelurahan --</option>');
  if (kec && kelurahanData[kec]) {
    kelurahanData[kec].forEach(k => {
      $kel.append(`<option value="${k.id}">${k.name}</option>`);
    });
  }
}
$('#filterKec').on('change', populateKelurahan);

// ===============================
// DATATABLE
// ===============================
let table;
const formatPotensi = new Intl.NumberFormat('en-US', { maximumFractionDigits: 0 });
function renderPotensi(data, type) {
  const value = Number(data) || 0;
  return type === 'display' ? formatPotensi.format(value) : value;
}
$(document).ready(function() {

  table = $('#potensiTable').DataTable({
    processing: true,
    serverSide: false,          // we’ll filter on the server
    scrollX: true,
    scrollCollapse: true,

    dom: 'Blfrtip',
    pageLength: 10,
    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Semua']],
    language: {
      lengthMenu: 'Tampilkan _MENU_ data'
    },
    buttons: [
      { extend: 'excelHtml5', title: 'Daftar_Potensi_Kota_Bogor', exportOptions: { orthogonal: 'export' } },
      { extend: 'csvHtml5',   title: 'Daftar_Potensi_Kota_Bogor', exportOptions: { orthogonal: 'export' } },
      { extend: 'pdfHtml5',   title: 'Daftar_Potensi_Kota_Bogor' },
      { extend: 'print',      title: 'Daftar Potensi Kota Bogor' },
      'colvis'
    ],

    fixedColumns: {
      leftColumns: 2,
      <?php if (session('role') !== null && (int) session('role') <= 2): ?> rightColumns: 1 <?php endif ?>
    },

    ajax: {
        url: "<?= site_url('potensi/data') ?>",
        type: "POST",
        data: function(d) {
          d.kecamatan    = $('#filterKec').val();
          d.kelurahan_id = $('#filterKel').val();
          d.searchCustom = $('#filterSearch').val();

          // FORCE LOAD ALL DATA (no pagination)
          d.length = -1;  
          d.start  = 0;
        }
      },


    columns: [
      {
        data: 'va_owner_va',
        render: function(data, type) {
          if (type === 'export' && data !== null && data !== '') {
            return "'" + String(data);
          }
          return data;
        }
      },
      { data: 'anggota_nama' },
      { data: 'anggota_id' },
      { data: 'titpar_namatempat' },
      { data: 'titpar_lokasi' },

      {
        data: 'titpar_klrhnid',
        className: 'wrap-text',
        width: '200px',
        render: function(id) {
          if (!id) return '-';
          const idStr   = id.toString();
          const kecCode = idStr.substring(0, 6);
          const kecName = kecamatanNames[kecCode] || 'Kecamatan tidak diketahui';

          let kelName = 'Kelurahan tidak diketahui';
          if (kelurahanData[kecCode]) {
            const match = kelurahanData[kecCode].find(k => k.id == idStr);
            if (match) kelName = match.name;
          }
          return `${kelName} (${kecName})`;
        }
      },

      { data: 'senin', className: 'text-end', render: renderPotensi },
      { data: 'selasa', className: 'text-end', render: renderPotensi },
      { data: 'rabu', className: 'text-end', render: renderPotensi },
      { data: 'kamis', className: 'text-end', render: renderPotensi },
      { data: 'jumat', className: 'text-end', render: renderPotensi },
      { data: 'sabtu', className: 'text-end', render: renderPotensi },
      { data: 'minggu', className: 'text-end', render: renderPotensi },

      <?php if (session('role') !== null && (int) session('role') <= 2): ?>
      {
        data: null,
        orderable: false,
        render: function(row, type, data) {
          return `<a class="btn btn-warning btn-sm" href="<?= base_url('potensi/edit/') ?>${data.va_owner_va}">Edit</a>`;
        }
      }
      <?php endif ?>
    ],

    initComplete: function() {
      initColumnToggle();
    }
  });

  // Buttons
  $('#btnFilter').on('click', function() { table.ajax.reload(); });
  $('#btnReset').on('click', function() {
    $('#filterKec').val('');
    populateKelurahan();
    $('#filterSearch').val('');
    table.ajax.reload();
  });

  // Live search (debounced)
  let searchTimeout;
  $('#filterSearch').on('keyup', function () {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => table.ajax.reload(), 300);
  });
});

// ===============================
// COLUMN VISIBILITY DROPDOWN
// ===============================
function initColumnToggle() {
  const menu = $('#columnToggleMenu');
  menu.empty();

  table.columns().every(function(idx) {
    const title = $(this.header()).text() || `Kolom ${idx+1}`;
    const checked = this.visible() ? 'checked' : '';
    menu.append(`
      <li>
        <label class="dropdown-item">
          <input type="checkbox" class="col-toggle me-2" data-col="${idx}" ${checked}>
          ${title}
        </label>
      </li>
    `);
  });
}
$(document).on('change', '.col-toggle', function() {
  const i = $(this).data('col');
  table.column(i).visible($(this).is(':checked'));
});
// keep dropdown open when clicking inside
$(document).on('click', '.dropdown-menu', function(e){ e.stopPropagation(); });
</script>

<?php echo view('footer'); ?>


