<?php helper('admin'); ?>

<div class="card border shadow-none mb-3">

    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Data Anggota</strong>

        <button
            type="button"
            class="btn btn-primary btn-sm"
            data-bs-toggle="modal"
            data-bs-target="#modalEditAnggota">
            <i class="fa fa-edit me-1"></i>
            Edit
        </button>
    </div>

    <div class="card-body p-0">

        <table class="table table-bordered mb-0">
            <tr>
                <th width="220">Nama</th>
                <td><?= esc($anggota['anggota_nama']) ?></td>
            </tr>

            <tr>
                <th>No. KTP</th>
                <td><?= esc($anggota['anggota_noktp']) ?></td>
            </tr>

            <tr>
                <th>Tanggal Lahir</th>
                <td><?= !empty($anggota['tgl_lahir']) ? date('d M Y', strtotime($anggota['tgl_lahir'])) : '-' ?></td>
            </tr>

            <tr>
                <th>Alamat</th>
                <td><?= nl2br(esc($anggota['alamat'])) ?></td>
            </tr>

            <tr>
                <th>Status</th>
                <td><?= statusBadge((int)$anggota['status']) ?></td>
            </tr>

            <tr>
                <th>Dibuat</th>
                <td><?= $anggota['created_at'] ?></td>
            </tr>
        </table>

    </div>

</div>