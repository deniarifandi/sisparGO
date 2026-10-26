<div class="status-note d-flex align-items-start gap-2 mb-3">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="#1a4699" class="flex-shrink-0 mt-1">
        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
    </svg>
    <span>Daftarkan titik parkir Anda di bawah ini.</span>
</div>

<?php if (!empty($titpar)): ?>
    <div class="list-group mb-3">
        <?php foreach ($titpar as $row): ?>
        <div class="list-group-item d-flex justify-content-between align-items-center">
            <div>
                <div class="fw-semibold small"><?= esc($row['titpar_namatempat']) ?></div>
                <small class="text-muted">
                    <?= esc(mb_strimwidth($row['titpar_lokasi'], 0, 25, '...')) ?>
                </small>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= base_url('register/titpar/edit/' . $row['id']) ?>"
                   class="btn btn-sm btn-outline-primary">Edit</a>
                <a href="<?= base_url('register/titpar/delete/' . $row['id']) ?>"
                   class="btn btn-sm btn-outline-danger"
                   onclick="return confirm('Hapus titik parkir ini?')">Hapus</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="alert alert-light border">Belum ada titik parkir yang terdaftar.</div>
<?php endif; ?>

<a href="<?= base_url('register/titpar/add') ?>" class="btn btn-primary w-100">
    + Tambah Titik Parkir
</a>