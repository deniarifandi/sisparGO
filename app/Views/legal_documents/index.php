<?= view('header') ?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <div><h4 class="mb-1">Ketentuan & Kebijakan</h4><div class="text-muted small">Kelola dokumen yang digunakan aplikasi SISPARGO Kota Bogor.</div></div>
</div>
<?php if (session()->getFlashdata('success')): ?><div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>
<div class="card shadow-sm border-0"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
<thead class="table-light"><tr><th>Jenis</th><th>Judul</th><th>Berlaku</th><th>Status</th><th class="text-end">Aksi</th></tr></thead><tbody>
<?php foreach ($rows as $row): ?><tr><td><?= $row['document_type'] === 'privacy' ? 'Kebijakan Privasi' : 'Ketentuan Layanan' ?></td><td><?= esc($row['title']) ?></td><td><?= esc($row['effective_date'] ?: '-') ?></td><td><span class="badge <?= $row['is_active'] ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= $row['is_active'] ? 'Aktif' : 'Nonaktif' ?></span></td><td class="text-end"><details><summary class="btn btn-sm btn-outline-primary d-inline-block">Edit</summary><form method="post" action="<?= base_url('legal-documents/save') ?>" class="text-start mt-3" style="min-width:320px"><?= csrf_field() ?><input type="hidden" name="legal_id" value="<?= (int) $row['legal_id'] ?>"><input class="form-control mb-2" name="title" value="<?= esc($row['title']) ?>" required><input type="date" class="form-control mb-2" name="effective_date" value="<?= esc($row['effective_date']) ?>"><textarea class="form-control mb-2" name="content" rows="7" required><?= esc($row['content']) ?></textarea><div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="is_active" value="1" <?= $row['is_active'] ? 'checked' : '' ?>><label class="form-check-label">Aktif</label></div><button class="btn btn-primary btn-sm">Simpan</button></form></details></td></tr><?php endforeach; ?>
</tbody></table></div></div>
<?= view('footer') ?>
