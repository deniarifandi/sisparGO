<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-cloud-download me-2"></i>Pull Source dari Git</h5>
        </div>
        <div class="card-body">
            <?php if ($result !== null): ?>
                <div class="alert <?= $result['success'] ? 'alert-success' : 'alert-danger' ?>">
                    <?= $result['success'] ? 'Proses git pull selesai.' : 'Proses git pull gagal atau menghasilkan error.' ?>
                </div>
                <pre class="bg-dark text-light rounded p-3 mb-4" style="white-space: pre-wrap;"><?= esc($result['output']) ?></pre>
            <?php endif; ?>

            <p class="text-muted">Folder project: <code><?= esc($projectPath) ?></code></p>
            <p>Gunakan fitur ini hanya setelah memastikan tidak ada perubahan lokal yang belum disimpan.</p>
            <form method="post" onsubmit="return confirm('Jalankan git pull sekarang?');">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-cloud-download me-1"></i> Jalankan Git Pull
                </button>
                <a href="<?= base_url('/') ?>" class="btn btn-outline-secondary">Kembali</a>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
