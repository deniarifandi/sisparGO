<?php echo view('header'); ?>

<?php if (session()->getFlashdata('message')): ?>
    <div class="alert alert-info">
        <?= session()->getFlashdata('message'); ?>
    </div>
<?php endif; ?>

<style>
#usersTable td { vertical-align: middle; }
#usersTable .anggota-photo { width: 48px; height: 60px; object-fit: cover; border-radius: 6px; background: #f1f5f9; }
#usersTable .anggota-ktp { white-space: nowrap; font-variant-numeric: tabular-nums; }
#usersTable .anggota-address { min-width: 180px; max-width: 300px; white-space: normal; overflow-wrap: anywhere; }
.anggota-summary { display: flex; flex-wrap: wrap; gap: 12px 28px; padding: 16px 20px; margin-bottom: 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; }
.anggota-summary span { color: #64748b; font-size: 13px; }
.anggota-summary strong { margin-left: 8px; color: #0f172a; font-size: 18px; }
</style>
<div class="card border-0 shadow-sm">
    <div class="card-header">
        Daftar Anggota
        <?php if ( session('role') < 4): ?>
        <a class="btn btn-primary btn-sm float-end" href="<?= base_url('anggota/create') ?>">Tambah Anggota</a>
        <?php endif ?>
    </div>
    <div class="card-body">
        <div class="anggota-summary" aria-label="Ringkasan seluruh anggota">
          <span>Total anggota<strong id="anggotaTotal">—</strong></span>
          <span>Aktif<strong id="anggotaActive">—</strong></span>
          <span>Nonaktif<strong id="anggotaInactive">—</strong></span>
        </div>
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
          <small class="text-muted">Cari berdasarkan nama, nomor registrasi, nomor KTP, HP, atau alamat.</small>
          <div class="d-flex align-items-center gap-2">
            <label for="anggotaStatusFilter" class="small text-muted mb-0">Status</label>
            <select id="anggotaStatusFilter" class="form-select form-select-sm w-auto">
              <option value="">Semua status</option>
              <option value="Aktif">Aktif</option>
              <option value="Nonaktif">Nonaktif</option>
              <option value="Belum diketahui">Belum diketahui</option>
            </select>
          </div>
        </div>
        <table id="usersTable" class="display" style="font-size: 12px; width: 100%;">
          <thead>
            <tr>
              <th>ID</th>
              <th>Foto</th>
              <th>Nama</th>
              <th>No. Registrasi</th>
              <th>No. KTP</th>
              <th>No. HP</th>
              <th>Alamat</th>
              <th>Tanggal Lahir</th>
              <th>Status</th>
              <?php if ( session('role') < 4): ?>
              <th>Aksi</th>
               <?php endif ?>
            </tr>
          </thead>
        </table>
    </div>
</div>


 <script>
  $(document).ready(function() {
    const safeText = value => $('<div>').text(value == null || String(value).trim() === '' ? '—' : String(value)).html();
    const textColumn = data => ({ data, defaultContent: '', render: (value, type) => type === 'display' ? safeText(value) : (value || '') });
    const table = $('#usersTable').DataTable({
      processing: true,
      serverSide: true,
      scrollX: true,
      pageLength: 25,
      order: [[0, 'asc']],
      ajax: {
        url: "<?= site_url('anggota/data') ?>",
        type: "POST",
        data: function (request) { request.status = $('#anggotaStatusFilter').val(); },
        dataSrc: function (json) {
          const summary = json.summary || {};
          $('#anggotaTotal').text(Number(summary.total || 0).toLocaleString('id-ID'));
          $('#anggotaActive').text(Number(summary.active || 0).toLocaleString('id-ID'));
          $('#anggotaInactive').text(Number(summary.inactive || 0).toLocaleString('id-ID'));
          return json.data || [];
        }
      },
      columns: [
        { data: 'anggota_id' },
        {
          data: 'anggota_foto', defaultContent: '', orderable: false, searchable: false,
          render: function(value, type) {
            if (type !== 'display') return '';
            const photo = document.createElement('img');
            photo.className = 'anggota-photo';
            photo.alt = 'Foto anggota';
            photo.loading = 'lazy';
            photo.width = 48;
            photo.height = 60;
            photo.src = value
              ? <?= json_encode(base_url()) ?> + 'uploads/anggota_foto/' + value
              : <?= json_encode(base_url('images/no-photo.png')) ?>;
            photo.setAttribute('onerror', "this.onerror=null;this.src=" + JSON.stringify(<?= json_encode(base_url('images/no-photo.png')) ?>));
            return photo.outerHTML;
          }
        },
        textColumn('anggota_nama'),
        textColumn('anggota_newnoreg'),
        { ...textColumn('anggota_noktp'), className: 'anggota-ktp', type: 'string' },
        textColumn('anggota_nohp'),
        { ...textColumn('anggota_alamat'), className: 'anggota-address' },
        {
          data: 'anggota_tgllhr', defaultContent: '',
          render: function(value, type) {
            const match = String(value || '').match(/^(\d{4})-(\d{2})-(\d{2})/);
            if (!match || match[1] === '0000') return type === 'display' ? '—' : '';
            if (type === 'sort' || type === 'type') return match[0];
            const date = new Date(Number(match[1]), Number(match[2]) - 1, Number(match[3]));
            return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
          }
        },
        {
        data: 'anggota_status',
        render: function(data, type, row) {
          const label = data == 2 ? 'Nonaktif' : data == 3 ? 'Aktif' : 'Belum diketahui';
          if (type !== 'display') return label;
          if (data == 2) {
            return '<span class="badge bg-danger">Nonaktif</span>';
          } else if (data == 3) {
            return '<span class="badge bg-success">Aktif</span>';
          } else {
            return '<span class="badge bg-secondary">Belum diketahui</span>';
          }
        }
      },
         <?php if ( session('role') < 4): ?>
        {
          data: null, orderable: false, searchable: false,
          render: function(data, type, row) {
            let buttons = `
              <button type="button" data-anggota-id="${row.anggota_id}" class="btn btn-info btn-sm text-white detailJukirBtn">Detail Jukir</button>
              <button type="button" data-anggota-id="${row.anggota_id}" data-anggota-nama="${$('<div>').text(row.anggota_nama || '').html()}" class="btn btn-outline-dark btn-sm resetPasswordBtn">Ganti Password</button>
              <a href="<?= site_url('anggota/edit/') ?>${row.anggota_id}"
                 class="btn btn-warning btn-sm">Edit</a>
            `;

            if (row.anggota_status == 3) {
              // Aktif → show Non-Aktifkan
              buttons += `
                <a href="<?= site_url('anggota/delete/') ?>${row.anggota_id}"
                   class="btn btn-danger btn-sm"
                   onclick="return confirm('Yakin ingin menonaktifkan anggota ini?')">
                   Non-Aktifkan
                </a>
              `;
            } else if (row.anggota_status == 2) {
              // Non-Aktif → show Aktifkan
              buttons += `
                <a href="<?= site_url('anggota/undelete/') ?>${row.anggota_id}"
                   class="btn btn-success btn-sm"
                   onclick="return confirm('Yakin ingin mengaktifkan anggota ini kembali?')">
                   Aktifkan
                </a>
              `;
            }

            return buttons;
          }
        }
         <?php endif ?>
      ]
    });
    $('#anggotaStatusFilter').on('change', function () {
      table.ajax.reload();
    });
  });
</script>

<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Ganti Password Anggota</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <div id="resetPasswordPrompt"><p class="mb-1">Buat password baru untuk:</p><p class="fw-bold" id="resetPasswordMember">—</p><div class="alert alert-info small mb-0">Password dibuat otomatis dan anggota wajib menggantinya saat login berikutnya.</div></div>
      <div id="resetPasswordResult" class="d-none"><div class="alert alert-success">Password baru berhasil dibuat. Salin dan sampaikan kepada anggota.</div><label class="form-label small text-muted">Password baru</label><div class="input-group"><input id="generatedPassword" class="form-control fw-bold" readonly><button class="btn btn-outline-primary" type="button" id="copyGeneratedPassword">Salin</button></div></div>
      <div id="resetPasswordError" class="alert alert-danger d-none mt-3"></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button><button type="button" class="btn btn-primary" id="confirmResetPassword">Buat Password</button></div>
  </div></div>
</div>

<script>
(() => {
  const initResetPassword = () => {
  const modalEl = document.getElementById('resetPasswordModal');
  if (!modalEl) return;
  const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
  let currentId = null;
  let csrfHash = '<?= csrf_hash() ?>';
  const reset = () => { $('#resetPasswordPrompt').removeClass('d-none'); $('#resetPasswordResult').addClass('d-none'); $('#resetPasswordError').addClass('d-none').text(''); $('#confirmResetPassword').prop('disabled', false).text('Buat Password'); $('#generatedPassword').val(''); };
  $(document).on('click', '.resetPasswordBtn', function () { reset(); currentId = $(this).data('anggota-id'); $('#resetPasswordMember').text($(this).data('anggota-nama') || 'Anggota #' + currentId); modal.show(); });
  $('#confirmResetPassword').on('click', async function () {
    if (!currentId) return;
    const button = $(this); button.prop('disabled', true).text('Memproses...');
    const form = new FormData(); form.append('<?= csrf_token() ?>', csrfHash);
    try {
      const response = await fetch("<?= site_url('anggota/password') ?>/" + encodeURIComponent(currentId), { method: 'POST', body: form, credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
      const result = await response.json();
      if (!response.ok || !result.credentials) throw new Error(result.message || 'Password gagal diperbarui.');
      $('#resetPasswordPrompt').addClass('d-none'); $('#generatedPassword').val(result.credentials.password); $('#resetPasswordResult').removeClass('d-none'); button.addClass('d-none');
      if (result.csrf && result.csrf.hash) csrfHash = result.csrf.hash;
    } catch (error) { $('#resetPasswordError').removeClass('d-none').text(error.message); button.prop('disabled', false).text('Coba Lagi'); }
  });
  $('#copyGeneratedPassword').on('click', async function () { const input = document.getElementById('generatedPassword'); try { await navigator.clipboard.writeText(input.value); $(this).text('Tersalin'); setTimeout(() => $(this).text('Salin'), 1500); } catch (_) { input.select(); document.execCommand('copy'); $(this).text('Tersalin'); } });
  modalEl.addEventListener('hidden.bs.modal', reset);
  };
  if (window.bootstrap) initResetPassword();
  else document.addEventListener('DOMContentLoaded', initResetPassword, { once: true });
})();
</script>

<div class="modal fade" id="detailJukirModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Detail Jukir</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body" id="detailJukirBody"><div class="text-center">Memuat...</div></div>
  </div></div>
</div>

<script>
$(document).on('click', '.detailJukirBtn', function () {
  const id = $(this).data('anggota-id');
  const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('detailJukirModal'));
  const body = $('#detailJukirBody');
  body.html('<div class="text-center p-3">Memuat data...</div>');
  modal.show();

  $.getJSON("<?= site_url('anggota/detail-data/') ?>" + id).done(function (result) {
    const a = result.anggota || {}, rows = result.titpars || [];
    const foto = a.anggota_foto ? "<?= base_url('uploads/anggota_foto/') ?>" + encodeURIComponent(a.anggota_foto) : "<?= base_url('images/no-photo.png') ?>";
    const esc = v => $('<div>').text(v || '-').html();
    const value = (label, v) => `<div class="col-md-6 mb-2"><div class="small text-muted">${label}</div><div class="fw-medium">${esc(v)}</div></div>`;

    const docUrl = (folder, file) => file ? `<?= base_url('uploads/') ?>${folder}/${encodeURIComponent(String(file).split('/').pop())}?v=${Date.now()}` : '';
    const docCard = (label, folder, file) => {
        if (!file) return `<div class="col-12"><div class="border rounded p-2 text-muted small">${label}: belum diupload</div></div>`;
        const url=docUrl(folder,file), ext=String(file).toLowerCase().split('.').pop();
        return `<div class="col-6"><div class="small text-muted mb-1">${label}</div>${['jpg','jpeg','png','webp'].includes(ext)?`<a href="${url}" target="_blank"><img src="${url}" class="img-fluid rounded border" style="max-height:100px;object-fit:cover"></a>`:`<a href="${url}" target="_blank" class="btn btn-outline-danger btn-sm w-100">Buka ${label}</a>`}</div>`;
    };

    let html = `
      <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
        <img src="${foto}" style="width:70px;height:70px;object-fit:cover" class="rounded-circle shadow-sm" onerror="this.src='<?= base_url('images/no-photo.png') ?>'">
        <div class="ms-3">
          <h5 class="mb-0 fw-bold">${esc(a.anggota_nama)}</h5>
          <div class="text-muted small">ID: ${a.anggota_id || '-'}</div>
        </div>
      </div>

      <ul class="nav nav-pills nav-justified mb-3" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab1">Biodata</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab2">Dokumen</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab3">Titik Parkir (${rows.length})</button></li>
      </ul>

      <div class="tab-content">
        <div class="tab-pane fade show active" id="tab1">
          <div class="row">
            ${value('No. Registrasi', a.anggota_noreg)}
            ${value('No. Registrasi Baru', a.anggota_newnoreg)}
            ${value('No. KTP / NIK', a.anggota_noktp)}
            ${value('Nomor KK', a.nomor_kk)}
            ${value('Tanggal Lahir', a.anggota_tgllhr)}
            ${value('Tempat Lahir', a.anggota_tmplhr)}
            ${value('Jenis Kelamin', a.anggota_jnsklm)}
            ${value('Pekerjaan', a.anggota_peker)}
            ${value('Pendidikan Terakhir', a.anggota_pente)}
            ${value('Agama', a.anggota_staagamaid)}
            ${value('No. HP', a.anggota_nohp)}
            ${value('Status', String(a.anggota_status)==='3' ? 'Aktif' : String(a.anggota_status)==='2' ? 'Nonaktif' : '-')}
            <div class="col-12"><div class="small text-muted">Alamat Lengkap</div><div class="fw-medium p-2 bg-light rounded">${esc(a.anggota_alamat)}</div></div>
          </div>
        </div>
        <div class="tab-pane fade" id="tab2">
           <div class="row g-2">${docCard('KTP','foto_ktp',a.foto_ktp)}${docCard('KK','foto_kk',a.foto_kk)}</div>
        </div>
        <div class="tab-pane fade" id="tab3">
           <div id="titparList" class="d-flex flex-column gap-2"></div>
        </div>
      </div>
    `;

    body.html(html);

    const titparList = $('#titparList');
    if (!rows.length) titparList.html('<div class="alert alert-info py-2">Belum memiliki titik parkir.</div>');
    else {
      rows.forEach((r, i) => {
        const titparStatus = String(r.titpar_status) === '3' ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Nonaktif</span>';
        const personQuery = `&person_ids[]=${encodeURIComponent(Number(a.anggota_id))}`;
        const printUrl = size => `<?= base_url('titpar/print-kta/') ?>${r.titpar_id}?size=${size}${personQuery}`;
        const members = (r.anggota_titik || []).map(m => `<tr><td>${esc(m.anggota_nama)}</td><td><span class="badge text-bg-light">${esc(m.titpargrup_staanggota)}</span></td></tr>`).join('') || '<tr><td colspan="2" class="text-muted">Belum ada data anggota</td></tr>';
        titparList.append(`<div class="card border shadow-sm"><div class="card-header bg-light d-flex justify-content-between align-items-center"><div><div class="fw-bold">${i+1}. ${esc(r.titpar_namatempat)}</div><small class="text-muted">${esc(r.titpar_lokasi)}</small></div><div class="d-flex gap-1 align-items-center">${titparStatus}<div class="btn-group ms-1"><button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown"><i class="bi bi-person-badge me-1"></i>Cetak KTA</button><ul class="dropdown-menu dropdown-menu-end"><li><a class="dropdown-item" href="${printUrl('cr80')}" target="_blank">CR80 - Ukuran Kartu</a></li><li><a class="dropdown-item" href="${printUrl('a4')}" target="_blank">A4 - Beberapa Kartu</a></li></ul></div><a href="<?= base_url('titpar/manage/') ?>${r.titpar_id}" target="_blank" class="btn btn-sm btn-outline-secondary">Kelola</a></div></div><div class="card-body"><div class="row g-2"><div class="col-md-6"><div class="small text-muted">Wilayah</div><div><b>Kelurahan:</b> ${esc(r.kelurahan_nama)}</div><div><b>Kecamatan:</b> ${esc(r.kecamatan_nama)}</div></div><div class="col-md-6"><div class="small text-muted">Masa berlaku & shift</div><div><b>Masa:</b> ${esc((r.titpargrup_masaberlakustart || '-') + ' s/d ' + (r.titpargrup_masaberlakuend || '-'))}</div><div><b>Shift:</b> ${esc((r.titpargrup_shifthari || '-') + ' ' + (r.titpargrup_shiftjammulai || '') + '-' + (r.titpargrup_shiftjamselesai || ''))}</div></div></div><hr><div class="small text-muted mb-1">Anggota di titik ini</div><div class="table-responsive"><table class="table table-sm table-striped mb-0"><thead><tr><th>Nama</th><th>Status</th></tr></thead><tbody>${members}</tbody></table></div>${r.titpar_alihfungsi ? `<hr><div class="small text-muted">Keterangan</div><div>${esc(r.titpar_alihfungsi)}</div>` : ''}</div></div>`);
      });
    }

  }).fail(function () { body.html('<div class="alert alert-danger">Gagal memuat detail jukir.</div>'); });
});
</script>


<script type="text/javascript">
  const overlay = document.getElementById("overlaySpinner");

 $(document).on("click", ".sendBtn", function() {
  // Show overlay spinner right away
  overlay.classList.remove("d-none");

  // The browser will continue with the link (redirect) immediately
  // No need for setTimeout, page will change and overlay stays until load
});


</script>


<?php echo view('footer'); ?>
