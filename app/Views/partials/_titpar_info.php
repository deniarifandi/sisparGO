<?php helper('admin'); ?>

<div class="table-responsive">
    <table class="table table-sm table-borderless mb-0 align-middle">
        <tr>
            <th style="min-width:130px; width:35%;">Nama Tempat</th>
            <td><?= esc($titpar['titpar_namatempat']) ?></td>
        </tr>
        <tr><th>Lokasi</th><td><?= esc($titpar['titpar_lokasi']) ?></td></tr>
        <tr><th>Batas Parkir</th><td><?= esc($titpar['batas_parkir']) ?></td></tr>
        <tr><th>Jam Operasional</th><td><?= esc($titpar['jam_operasional']) ?></td></tr>
        <tr>
            <th>Jenis Kendaraan</th>
            <?php
            $jenis = (int)($titpar['jenis_kendaraan'] ?? 0);
            $list  = [];
            if ($jenis & 1) $list[] = 'Motor';
            if ($jenis & 2) $list[] = 'Mobil';
            if ($jenis & 4) $list[] = 'Truk';
            ?>
            <td><?= $list ? implode(', ', $list) : '-' ?></td>
        </tr>
        <tr>
            <th>Status</th>
            <td><?= statusBadge((int)($titpar['status'] ?? 0)) ?></td>
        </tr>
        <tr>
            <th>Virtual Account No.</th>
            <td><?= esc($titpar['va'] ?? '-') ?></td>
        </tr>
        <tr>
            <th>Tipe</th>
            <td>
                <?= $titpar['baru']
                    ? '<span class="badge bg-info">Baru</span>'
                    : '<span class="badge bg-secondary">Lama</span>' ?>
            </td>
        </tr>
        <tr><th>Approved By</th><td><?= esc($titpar['approved_by'] ?? '-') ?></td></tr>
        <tr><th>Catatan</th><td><?= esc($titpar['catatan'] ?? '-') ?></td></tr>
        <tr>
            <th>Dibuat</th>
            <td><?= !empty($titpar['created_at']) ? date('d M Y H:i', strtotime($titpar['created_at'])) : '-' ?></td>
        </tr>
        <tr>
            <th>Diperbarui</th>
            <td><?= !empty($titpar['updated_at']) ? date('d M Y H:i', strtotime($titpar['updated_at'])) : '-' ?></td>
        </tr>
    </table>
</div>