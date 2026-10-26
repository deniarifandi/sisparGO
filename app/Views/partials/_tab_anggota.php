<?php helper('admin'); ?>

<div class="card border shadow-none mb-3">
    <div class="card-header">
        <i class="fa fa-users me-2 text-primary"></i>Data Anggota
    </div>

    <div class="card-body">

        <?php if (empty($anggota)): ?>

            <div class="text-muted">Belum ada data anggota.</div>

        <?php else: ?>

            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>No. KTP</th>
                            <th>Tgl. Lahir</th>
                            <th>Alamat</th>
                            <th>Status</th>
                            <th width="100">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>

                    <?php foreach ($anggota as $a): ?>

                        <tr>
                            <td><?= esc($a['anggota_nama']) ?></td>
                            <td><?= esc($a['anggota_noktp']) ?></td>
                            <td><?= !empty($a['tgl_lahir']) ? date('d M Y', strtotime($a['tgl_lahir'])) : '-' ?></td>
                            <td class="text-wrap"><?= esc($a['alamat']) ?></td>
                            <td><?= statusBadge((int)($a['status'] ?? 0)) ?></td>
                            <td>
                                <a href="<?= site_url('admin/anggota/detail/' . $a['id']) ?>"
                                   class="btn btn-sm btn-primary">
                                    Detail
                                </a>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                    </tbody>
                </table>
            </div>

        <?php endif; ?>

    </div>
</div>