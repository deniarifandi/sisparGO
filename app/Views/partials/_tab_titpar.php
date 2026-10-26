<?php helper('admin'); ?>

<div class="card border shadow-none mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>
            <i class="fa fa-map-marker-alt me-2 text-primary"></i>Titik Parkir
        </span>
    </div>
    <div class="card-body">
        <?php if (empty($titpars)): ?>
            <div class="text-muted">Belum ada titik parkir.</div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th>Nama Tempat</th>
                        <th>Lokasi</th>
                        <th>Jam Operasional</th>
                        <th>Status</th>
                        <th width="100">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($titpars as $t): ?>
                    <tr>
                        <td><?= esc($t['titpar_namatempat']) ?></td>
                        <td><?= esc($t['titpar_lokasi']) ?></td>
                        <td><?= esc($t['jam_operasional']) ?></td>
                        <td><?= statusBadge((int)($t['status'] ?? 0)) ?></td>
                        <td>
                            <a href="<?= base_url('admin/titpar/detail/' . $t['id']) ?>"
                               class="btn btn-sm btn-primary">Detail</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>