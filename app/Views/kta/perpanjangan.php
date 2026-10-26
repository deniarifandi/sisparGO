<?= view('header') ?>
<style>
.renew-page{background:#f6f8fb;min-height:calc(100vh - 70px)}
.renew-hero{background:linear-gradient(135deg,#173b68,#2563a6);color:#fff;border-radius:16px}
.renew-table td{vertical-align:middle;font-size:.82rem}
.renew-table thead th{font-size:.7rem;text-transform:uppercase;color:#718096;white-space:nowrap}
.member-name{color:#173b68;font-weight:700}
.meta{font-size:.72rem;color:#8491a3}
.status{border-radius:999px;padding:4px 8px;font-size:.7rem;font-weight:600}
.doc-link{border:0;background:#eef6ff;color:#1769aa;border-radius:6px;padding:4px 7px;font-size:.72rem;margin:2px}
.doc-preview-stage{height:70vh;max-height:600px;display:flex;align-items:center;justify-content:center;overflow:hidden}
.doc-preview-stage img{max-width:100%;max-height:100%;object-fit:contain;transition:transform .2s ease}
.renew-detail-card{border:1px solid #e5eaf0;border-radius:12px;background:#fff;overflow:hidden}
.renew-detail-card__title{padding:.85rem 1rem;background:#f4f8fc;color:#173b68;font-weight:700}
.renew-detail-list{padding:.35rem 1rem .7rem}
.renew-detail-row{display:flex;justify-content:space-between;gap:1rem;padding:.65rem 0;border-bottom:1px solid #eef1f4}
.renew-detail-row:last-child{border-bottom:0}
.renew-detail-label{color:#718096;font-size:.78rem}
.renew-detail-value{text-align:right;color:#263548;font-weight:600;max-width:65%;overflow-wrap:anywhere}
.renew-change-box{background:#f8fafc;border:1px solid #e5eaf0;border-radius:10px}
.renew-change-table th{font-size:.76rem;color:#718096}
.renew-change-table td{font-size:.82rem;overflow-wrap:anywhere;min-width:0}
.renew-change-table td:first-child{font-weight:600;color:#263548;width:30%}
.renew-member{border-bottom:1px solid #eef1f4;padding:.65rem 0}
.renew-member:last-child{border-bottom:0}
</style>
<div class="renew-page py-3 py-md-4"><div class="container-fluid px-3 px-md-4">
  <div class="renew-hero p-3 p-md-4 mb-3 d-flex justify-content-between align-items-center">
    <div><div class="small text-uppercase opacity-75">Administrasi KTA</div><h4 class="mb-1">Perpanjangan KTA</h4><div class="small opacity-75">Validasi pengajuan perpanjangan dari aplikasi.</div></div>
    <div class="text-end"><div class="h3 mb-0"><?= (int) $total ?></div><small>pengajuan</small></div>
  </div>
  <?php if ($message = session('success')): ?><div class="alert alert-success py-2"><?= esc($message) ?></div><?php endif; ?>
  <?php if ($message = session('error')): ?><div class="alert alert-danger py-2"><?= esc($message) ?></div><?php endif; ?>
  <div class="bg-white rounded-3 shadow-sm p-2 mb-3 d-flex gap-2 flex-wrap">
    <input id="renewSearch" class="form-control form-control-sm" style="max-width:340px" placeholder="Cari anggota atau nomor KTA" aria-label="Cari pengajuan">
    <select id="renewStatus" class="form-select form-select-sm" style="max-width:170px" aria-label="Filter status">
      <option value="">Semua status</option><option value="menunggu">Menunggu</option><option value="disetujui">Disetujui</option><option value="ditolak">Ditolak</option>
    </select>
  </div>
  <div class="card border-0 shadow-sm rounded-3"><div class="table-responsive">
    <table class="table renew-table mb-0" id="renewTable">
      <thead class="bg-light"><tr><th class="px-3 py-3">Anggota</th><th class="py-3">KTA / Titik</th><th class="py-3">Masa berlaku</th><th class="py-3">Dokumen</th><th class="py-3">Status</th><th class="py-3 pe-3">Aksi</th></tr></thead>
      <tbody></tbody>
    </table>
  </div></div>
</div></div>

<div class="modal fade" id="renewDetail" tabindex="-1" aria-labelledby="renewDetailTitle" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
    <div class="modal-header"><h5 id="renewDetailTitle" class="modal-title">Detail Perpanjangan KTA</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
    <div class="modal-body"></div>
  </div></div>
</div>
<div class="modal fade" id="previewDoc" tabindex="-1" aria-labelledby="docTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content">
    <div class="modal-header"><h5 id="docTitle" class="modal-title">Preview Dokumen</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
    <div class="modal-body text-center bg-light"><div class="doc-preview-stage"><img id="docImage" class="img-fluid" alt="Preview dokumen"></div><div id="docFallback" class="d-none text-muted">Dokumen tidak dapat dipreview.</div></div>
    <div class="p-2 d-flex justify-content-end gap-2"><button type="button" id="rotatePreviewBtn" class="btn btn-outline-warning btn-sm">↻ Putar Preview</button><button type="button" id="saveRotationBtn" class="btn btn-warning btn-sm">Simpan Rotasi</button></div>
  </div></div>
</div>
<div class="modal fade" id="approveRenewModal" tabindex="-1" aria-labelledby="approveRenewTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <div class="modal-header"><h5 id="approveRenewTitle" class="modal-title">Konfirmasi Perpanjangan KTA</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
    <form id="approveRenewForm" method="post"><div class="modal-body">
      <p class="mb-2">Tentukan masa berlaku baru sebelum menyetujui pengajuan ini.</p>
      <div class="row g-3"><div class="col-md-6"><label for="approveRenewStart" class="form-label">Masa berlaku mulai</label><input id="approveRenewStart" type="date" name="masa_berlaku_mulai" class="form-control" required></div>
      <div class="col-md-6"><label for="approveRenewEnd" class="form-label">Masa berlaku selesai</label><input id="approveRenewEnd" type="date" name="masa_berlaku_selesai" class="form-control" required></div></div>
    </div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-success">Setujui Perpanjangan</button></div></form>
  </div></div>
</div>
<?= view('footer') ?>
<script>
$(function () {
  const base = <?= json_encode(rtrim(base_url(), '/') . '/') ?>;
  const api = <?= json_encode(base_url('admin/kta-perpanjangan')) ?>;
  const escapeHtml = value => String(value ?? '-').replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));
  const monthNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
  const formatDate = value => {
    const match = String(value || '').match(/^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2}))?/);
    if (!match || !monthNames[Number(match[2]) - 1]) return value || '-';
    return `${Number(match[3])} ${monthNames[Number(match[2]) - 1]} ${match[1]}${match[4] ? ` ${match[4]}:${match[5]}` : ''}`;
  };
  const detailRow = (label, value) => `<div class="renew-detail-row"><span class="renew-detail-label">${escapeHtml(label)}</span><span class="renew-detail-value">${escapeHtml(value)}</span></div>`;
  const changeLabels = {anggota_nama:'Nama',anggota_noktp:'No. KTP / NIK',nomor_kk:'Nomor KK',anggota_nohp:'No. HP',anggota_tgllhr:'Tanggal lahir',anggota_tmplhr:'Tempat lahir',anggota_jnsklm:'Jenis kelamin',anggota_alamat:'Alamat',anggota_klrhnid:'Kelurahan',anggota_peker:'Pekerjaan',anggota_pente:'Pendidikan terakhir',anggota_staagamaid:'Agama',masa_berlaku_baru:'Masa berlaku selesai'};
  const parseObject = value => { try { const result = typeof value === 'string' ? JSON.parse(value) : value; return result && typeof result === 'object' && !Array.isArray(result) ? result : {}; } catch (error) { return {}; } };
  const displayChange = (key, value) => value === null || value === undefined || value === '' ? '-' : key === 'anggota_tgllhr' || key === 'masa_berlaku_baru' ? formatDate(value) : typeof value === 'object' ? JSON.stringify(value) : String(value);
  const changeTable = row => {
    const old = parseObject(row.data_lama), current = parseObject(row.data_baru);
    const fields = Object.keys(current).filter(key => Object.prototype.hasOwnProperty.call(changeLabels, key));
    if (row.masa_berlaku_baru && !fields.includes('masa_berlaku_baru')) fields.push('masa_berlaku_baru');
    const rows = fields.map(key => {
      let before = key === 'masa_berlaku_baru' ? (old.masa_berlaku_selesai ?? row.masa_berlaku_lama) : old[key];
      if (before === undefined && row.status === 'menunggu') {
        before = key === 'anggota_nama' || key === 'anggota_noktp' ? row[key] : row['profil_' + key];
      }
      const after = key === 'masa_berlaku_baru' ? (current[key] ?? row.masa_berlaku_baru) : current[key];
      if (before !== undefined && String(before ?? '') === String(after ?? '')) return '';
      const previous = before === undefined ? '<span class="text-muted">Tidak tercatat</span>' : escapeHtml(displayChange(key, before));
      return `<tr><td>${escapeHtml(changeLabels[key])}</td><td>${previous}</td><td class="fw-semibold text-primary">${escapeHtml(displayChange(key, after))}</td></tr>`;
    }).filter(Boolean).join('');
    return rows ? `<div class="table-responsive"><table class="table table-sm renew-change-table mb-0"><thead><tr><th>Data</th><th>Sebelum</th><th>Diajukan</th></tr></thead><tbody>${rows}</tbody></table></div>` : '<div class="text-muted small">Tidak ada perubahan data profil yang diajukan.</div>';
  };
  const labels = {foto_selfie:'Foto selfie',foto_ktp:'Foto KTP',foto_kk:'Foto KK',dokumen_pendukung:'Denah'};
  const approvedFolders = {foto_selfie:'anggota_foto',foto_ktp:'foto_ktp',foto_kk:'foto_kk',dokumen_pendukung:'denah'};
  const table = $('#renewTable').DataTable({
    processing:true, serverSide:true, searching:true, pageLength:10, order:[[0,'desc']],
    ajax:{url:api + '/data',type:'POST',data:request => {request.status = $('#renewStatus').val();}},
    columns:[
      {data:'anggota_nama',render:(value,type,row) => type !== 'display' ? value : `<div class="member-name">${escapeHtml(value)}</div><div class="meta">Koordinator · ID ${escapeHtml(row.anggota_id)}</div><div class="meta">${escapeHtml(formatDate(row.created_at))}</div>`},
      {data:'nomor_kta',render:(value,type,row) => type !== 'display' ? value : `<b>${escapeHtml(value)}</b><div class="meta">${escapeHtml(row.titpar_namatempat)}</div><button type="button" class="btn btn-outline-primary btn-sm mt-1 detail-button">Lihat detail</button>`},
      {data:'masa_berlaku_lama',render:(value,type,row) => type !== 'display' ? value : `<span class="meta">Lama</span><br>${escapeHtml(formatDate(value))}<br><span class="text-success fw-semibold">Baru: ${escapeHtml(formatDate(row.masa_berlaku_baru))}</span>`},
      {data:null,orderable:false,searchable:false,render:(value,type,row) => type !== 'display' ? '' : Object.keys(labels).filter(field => row[field]).map(field => {
        const folder = row.status === 'disetujui' ? approvedFolders[field] : 'kta_perpanjangan';
        const filename = String(row[field]).split(/[\\/]/).pop();
        const source = base + 'uploads/' + folder + '/' + encodeURIComponent(filename);
        return `<button class="doc-link" type="button" data-src="${escapeHtml(source)}" data-label="${escapeHtml(labels[field])}">↗ ${escapeHtml(labels[field])}</button>`;
      }).join('')},
      {data:'status',render:(value,type,row) => {
        if (type !== 'display') return value;
        const color = value === 'menunggu' ? 'warning-subtle text-warning-emphasis' : value === 'disetujui' ? 'success-subtle text-success' : 'danger-subtle text-danger';
        return `<span class="status bg-${color}">${escapeHtml(value)}</span>${row.alasan_penolakan ? `<div class="small text-danger mt-1">${escapeHtml(row.alasan_penolakan)}</div>` : ''}`;
      }},
      {data:null,orderable:false,searchable:false,render:(value,type,row) => type !== 'display' ? '' : row.status === 'menunggu' ? `<form method="post" action="${api}/approve/${encodeURIComponent(row.perpanjangan_id)}" class="approve-form mb-1"><button class="btn btn-success btn-sm w-100">✓ Setujui</button></form><form method="post" action="${api}/reject/${encodeURIComponent(row.perpanjangan_id)}" class="reject-form"><input required name="alasan_penolakan" class="form-control form-control-sm mb-1" placeholder="Alasan penolakan" aria-label="Alasan penolakan"><button class="btn btn-outline-danger btn-sm w-100">Tolak</button></form>` : '<span class="text-muted small">Selesai</span>'}
    ],
    columnDefs:[{targets:'_all',defaultContent:'-'}],
    language:{emptyTable:'Tidak ada pengajuan.',zeroRecords:'Tidak ada pengajuan yang cocok.'}
  });
  let timer;
  $('#renewSearch').on('input', function () { clearTimeout(timer); timer = setTimeout(() => table.search(this.value).draw(), 250); });
  $('#renewStatus').on('change', () => table.ajax.reload());
  $('#renewTable').on('click', '.detail-button', function () {
    const row = table.row($(this).closest('tr')).data();
    if (!row) return;
    $('#renewDetail').data('applicationId', row.perpanjangan_id);
    let kendaraan = row.titpar_jenis_kendaraan || '-';
    try { const parsed = JSON.parse(kendaraan); if (Array.isArray(parsed)) kendaraan = parsed.join(', ') || '-'; } catch (error) {}
    $('#renewDetail .modal-body').html(`<div class="row g-3">
      <div class="col-md-6"><section class="renew-detail-card h-100"><div class="renew-detail-card__title">👤 Koordinator pemohon &amp; KTA</div><div class="renew-detail-list">
      ${detailRow('Nama pemohon',row.anggota_nama)}${detailRow('ID anggota',row.anggota_id)}${detailRow('Nomor KTA',row.nomor_kta)}${detailRow('Nomor VA',row.va_owner_va)}${detailRow('Masa berlaku KTA',`${formatDate(row.titpargrup_masaberlakustart)} s/d ${formatDate(row.titpargrup_masaberlakuend)}`)}
      </div></section></div><div class="col-md-6"><section class="renew-detail-card h-100"><div class="renew-detail-card__title">📍 Detail Titpar</div><div class="renew-detail-list">
      ${detailRow('ID Titpar',row.titpar_id)}${detailRow('Nomor registrasi',row.titpar_newnoreg)}${detailRow('Zona',row.zona_id)}${detailRow('Nama titik parkir',row.titpar_namatempat)}${detailRow('Alamat',row.titpar_lokasi)}${detailRow('Koordinat',`${row.titpar_latitude || '-'}, ${row.titpar_longitude || '-'}`)}${detailRow('Jenis titik',row.retrytipe_nama || row.titpar_retritypeid)}${detailRow('Jenis kendaraan',kendaraan)}
      </div></section></div></div>
      <section class="renew-detail-card mt-3"><div class="renew-detail-card__title">👥 Daftar anggota yang diajukan</div><div id="renewMembers" class="renew-detail-list"><div class="text-muted small py-2">Memuat anggota...</div></div></section>
      <section class="renew-change-box mt-3 p-3"><h6 class="fw-bold mb-2">Perbandingan data pengajuan</h6>${changeTable(row)}</section>`);
    bootstrap.Modal.getOrCreateInstance(document.getElementById('renewDetail')).show();
    $.getJSON(api + '/members/' + encodeURIComponent(row.perpanjangan_id))
      .done(response => {
        if ($('#renewDetail').data('applicationId') !== row.perpanjangan_id) return;
        const members = response.members || [];
        const removed = response.removed || [];
        $('#renewMembers').html((members.length ? members.map((member, index) => `<div class="renew-member">
          <div class="fw-semibold">${index + 1}. ${escapeHtml(member.anggota_nama)}</div>
          <div class="meta">ID: ${escapeHtml(member.anggota_id)} · Telepon: ${escapeHtml(member.anggota_nohp)} · VA: ${escapeHtml(member.va_owner_va)}</div>
          <div class="meta">Masa berlaku: ${escapeHtml(formatDate(member.titpargrup_masaberlakustart))} s/d ${escapeHtml(formatDate(member.titpargrup_masaberlakuend))}</div>
        </div>`).join('') : '<div class="text-muted small py-2">Tidak ada anggota yang diajukan.</div>') +
          (removed.length ? `<div class="text-danger small mt-2">Dihapus dari daftar: ${removed.map(member => escapeHtml(member.anggota_nama)).join(', ')}</div>` : ''));
      })
      .fail(() => {
        if ($('#renewDetail').data('applicationId') === row.perpanjangan_id) $('#renewMembers').html('<div class="text-danger small py-2">Daftar anggota gagal dimuat.</div>');
      });
  });
  $('#renewTable').on('submit', '.approve-form', function (event) {
    event.preventDefault();
    const form = document.getElementById('approveRenewForm');
    form.action = this.action;
    form.reset();
    const todayParts = Object.fromEntries(new Intl.DateTimeFormat('en-US', {
      timeZone:'Asia/Bangkok', year:'numeric', month:'2-digit', day:'2-digit'
    }).formatToParts(new Date()).filter(part => part.type !== 'literal').map(part => [part.type, Number(part.value)]));
    const year = todayParts.year + 1;
    const day = Math.min(todayParts.day, new Date(year, todayParts.month, 0).getDate());
    const dateValue = (y, m, d) => `${y}-${String(m).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
    document.getElementById('approveRenewStart').value = dateValue(todayParts.year, todayParts.month, todayParts.day);
    document.getElementById('approveRenewEnd').value = dateValue(year, todayParts.month, day);
    bootstrap.Modal.getOrCreateInstance(document.getElementById('approveRenewModal')).show();
  });
  $('#renewTable').on('submit', '.reject-form', function (event) {
    if (!confirm('Tolak pengajuan ini?')) event.preventDefault();
  });
  let previewSource = '';
  $('#renewTable').on('click', '[data-src]', function () {
    previewSource = this.dataset.src;
    const image = document.getElementById('docImage');
    image.src = previewSource + '?v=' + Date.now();
    image.dataset.turns = '0';
    image.style.transform = 'rotate(0deg)';
    $('#docTitle').text('Preview ' + this.dataset.label);
    $('#docFallback').addClass('d-none');
    bootstrap.Modal.getOrCreateInstance(document.getElementById('previewDoc')).show();
  });
  $('#docImage').on('error', () => $('#docFallback').removeClass('d-none'));
  $('#rotatePreviewBtn').on('click', () => {
    const image = document.getElementById('docImage');
    const turns = (Number(image.dataset.turns || 0) + 1) % 4;
    image.dataset.turns = String(turns);
    image.style.transform = `rotate(${turns * 90}deg)`;
  });
  $('#saveRotationBtn').on('click', () => {
    const turns = Number(document.getElementById('docImage').dataset.turns || 0);
    if (!previewSource || !turns) return;
    const filename = decodeURIComponent(previewSource.split('/').pop());
    const form = document.createElement('form');
    form.method = 'post';
    form.action = api + '/rotate-file/' + encodeURIComponent(filename);
    const input = document.createElement('input');
    input.type = 'hidden'; input.name = 'turns'; input.value = String(turns);
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
  });
});
</script>
