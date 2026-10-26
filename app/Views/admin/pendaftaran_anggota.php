<?= view('header') ?>
<div class="container-fluid py-4">
  <?php if (session('success')): ?><div class="alert alert-success" role="status"><?= esc(session('success')) ?></div><?php endif ?>
  <?php if (session('error')): ?><div class="alert alert-danger" role="alert"><?= esc(session('error')) ?></div><?php endif ?>
  <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between mb-4">
    <div><h4 class="fw-bold mb-1">Pengajuan Pendaftaran Anggota</h4>
    <p class="text-muted mb-0"><?= (int) $total ?> pengajuan dari aplikasi SISPARMA</p></div>
    <a href="<?= base_url('/') ?>" class="btn btn-outline-secondary btn-sm">Kembali ke Dashboard</a>
  </div>
  <div class="card border-0 shadow-sm rounded-4">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light"><tr><th>Nomor Pengajuan</th><th>Nama Lengkap</th><th>No. Handphone</th><th>Status</th><th>Tanggal Pengajuan</th><th>Aksi</th></tr></thead>
        <tbody>
          <?php if (!$rows): ?>
            <tr><td colspan="6" class="text-center text-muted py-5">Belum ada pengajuan pendaftaran anggota.</td></tr>
          <?php endif ?>
          <?php foreach ($rows as $row): ?>
            <tr>
              <td><?= esc($row['nomor_pengajuan']) ?></td>
              <td><?= esc($row['nama_lengkap']) ?></td>
              <td><?= esc($row['no_handphone'] ?: '-') ?></td>
              <?php
                $status = strtolower((string) $row['status']);
                $statusClass = in_array($status, ['disetujui', 'approved', 'diterima', 'selesai'], true)
                  ? 'text-bg-success'
                  : (in_array($status, ['ditolak', 'rejected', 'gagal'], true) ? 'text-bg-danger' : 'text-bg-warning');
              ?>
              <td><span class="badge <?= $statusClass ?>"><?= esc(ucfirst(str_replace('_', ' ', $row['status']))) ?></span></td>
              <td><?= esc($row['created_at']) ?></td>
              <td><button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#registrationDetailModal" data-detail-url="<?= base_url('admin/pendaftaran-anggota/detail/' . (int) $row['id']) ?>"><i class="bi bi-eye me-1"></i> Detail</button></td>
            </tr>
          <?php endforeach ?>
        </tbody>
      </table>
    </div>
    <div class="card-footer bg-white d-flex align-items-center justify-content-between py-3">
      <span class="text-muted small">Halaman <?= (int) $page ?> dari <?= (int) $pages ?></span>
      <div class="d-flex gap-2">
        <?php if ($page > 1): ?><a class="btn btn-outline-primary btn-sm" href="<?= base_url('admin/pendaftaran-anggota?page=' . ($page - 1)) ?>">Sebelumnya</a><?php endif ?>
        <?php if ($page < $pages): ?><a class="btn btn-outline-primary btn-sm" href="<?= base_url('admin/pendaftaran-anggota?page=' . ($page + 1)) ?>">Berikutnya</a><?php endif ?>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="registrationDetailModal" tabindex="-1" aria-labelledby="registrationDetailTitle" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-sm-down">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="registrationDetailTitle">Detail Pendaftar</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body bg-light" id="registrationDetailContent" aria-live="polite"></div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button></div>
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('registrationDetailModal');
  const content = document.getElementById('registrationDetailContent');
  let activeRequest = null;
  let detailUrl = null;
  async function loadDetail() {
    activeRequest?.abort();
    const controller = new AbortController();
    activeRequest = controller;
    content.setAttribute('aria-busy', 'true');
    content.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Memuat...</span></div><p class="text-muted mt-3">Memuat detail pendaftar...</p></div>';
    const timeout = setTimeout(() => controller.abort(), 20000);
    try {
      const response = await fetch(detailUrl, {
        headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'},
        credentials: 'same-origin', cache: 'no-store', signal: controller.signal,
      });
      if (!response.ok || response.redirected) throw new Error('Detail tidak tersedia');
      const data = await response.json();
      if (typeof data.html !== 'string') throw new Error('Respons tidak valid');
      if (activeRequest === controller) content.innerHTML = data.html;
    } catch (_) {
      if (activeRequest !== controller) return;
      content.innerHTML = '<div class="alert alert-warning" role="alert">Detail belum dapat dimuat. Pastikan sesi login masih aktif dan coba lagi.</div><button type="button" class="btn btn-outline-primary" data-retry-detail>Coba lagi</button>';
    } finally {
      clearTimeout(timeout);
      if (activeRequest === controller) content.removeAttribute('aria-busy');
    }
  }
  modal.addEventListener('show.bs.modal', event => {
    detailUrl = event.relatedTarget?.dataset.detailUrl;
    if (detailUrl) loadDetail();
  });
  modal.addEventListener('hide.bs.modal', () => {
    const previous = activeRequest;
    activeRequest = null;
    previous?.abort();
  });
  modal.addEventListener('hidden.bs.modal', () => {
    content.replaceChildren();
    content.removeAttribute('aria-busy');
  });
  content.addEventListener('click', event => {
    if (event.target.closest('[data-retry-detail]')) loadDetail();
  });
});
</script>
<?= view('admin/pendaftaran_review_script') ?>
<?= view('footer') ?>
