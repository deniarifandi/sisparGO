<?php helper('admin'); ?>

<div class="card-body border-bottom pb-3">
    <div class="row align-items-center g-3">

        <?= view('partials/_flash_dismissible') ?>

        <!-- Photo -->
        <div class="col-auto">
            <?php if (!empty($register['anggota_foto'])): ?>
                <img src="<?= base_url('uploads/anggota_foto/' . $register['anggota_foto']) ?>"
                     class="profile-img" alt="Foto">
            <?php else: ?>
                <div class="profile-placeholder">
                    <i class="fa fa-user fa-2x text-secondary"></i>
                </div>
            <?php endif; ?>
        </div>

        <!-- Info -->
        <div class="col">
            <h5 class="mb-1 fw-bold"><?= esc($register['anggota_nama']) ?></h5>

            <div class="text-muted small mb-1">
                <i class="fa fa-id-card me-1"></i><?= esc($register['anggota_noktp']) ?>
                <span class="d-none d-sm-inline">&nbsp;·&nbsp;</span>
                <i class="fa fa-phone me-1"></i><?= esc($register['hp'] ?? '-') ?>
            </div>

            <div class="text-muted small mb-2">
                <i class="fa fa-credit-card me-1"></i>VA:
                <strong class="text-primary"><?= esc($register['register_va'] ?? '-') ?></strong>
            </div>

            <div class="d-flex flex-wrap gap-1 mb-2">
                <?= statusBadge((int)($register['anggota_status'] ?? 0)) ?>
                <?php if (!empty($register['baru'])): ?>
                    <span class="badge bg-info text-dark">Baru</span>
                <?php endif; ?>
                <?php if (!empty($register['masa_berlaku'])): ?>
                    <span class="badge bg-light text-dark border">
                        <i class="fa fa-calendar me-1"></i>Berlaku: <?= esc($register['masa_berlaku']) ?>
                    </span>
                <?php endif; ?>
            </div>

            <div class="meta-block text-muted small">
                <div>Didaftarkan:
                    <span class="fw-semibold text-dark">
                        <?= date('d M Y', strtotime($register['created_at'])) ?>
                    </span>
                </div>
                <?php if (!empty($register['catatan'])): ?>
                    <div class="mt-1 text-danger">
                        <i class="fa fa-sticky-note me-1"></i><?= esc($register['catatan']) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>