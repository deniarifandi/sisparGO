<?php helper('admin'); ?>

<div class="table-responsive">
    <table class="table table-sm table-borderless mb-0 align-middle">
        <tr>
            <th style="min-width:130px; width:35%;">Nama</th>
            <td><?= esc($anggota['anggota_nama']) ?></td>
        </tr>
        <tr><th>No. KTP</th><td><?= esc($anggota['anggota_noktp']) ?></td></tr>
        <tr>
            <th>Tgl. Lahir</th>
            <td><?= !empty($anggota['tgl_lahir']) ? date('d M Y', strtotime($anggota['tgl_lahir'])) : '-' ?></td>
        </tr>
        <tr><th>Alamat</th><td><?= esc($anggota['alamat'] ?? '-') ?></td></tr>
        <tr><th>No. KTP Koordinator</th><td><?= esc($anggota['koordinator_noktp'] ?? '-') ?></td></tr>
        <tr>
            <th>Status</th>
            <td><?= statusBadge((int)($anggota['status'] ?? 0)) ?></td>
        </tr>
        <tr>
            <th>Dibuat</th>
            <td><?= !empty($anggota['created_at']) ? date('d M Y H:i', strtotime($anggota['created_at'])) : '-' ?></td>
        </tr>
        <tr>
            <th>Diperbarui</th>
            <td><?= !empty($anggota['updated_at']) ? date('d M Y H:i', strtotime($anggota['updated_at'])) : '-' ?></td>
        </tr>
        <?php if (!empty($anggota['deleted_at'])): ?>
        <tr>
            <th>Dihapus</th>
            <td class="text-danger"><?= date('d M Y H:i', strtotime($anggota['deleted_at'])) ?></td>
        </tr>
        <?php endif; ?>
    </table>
</div>