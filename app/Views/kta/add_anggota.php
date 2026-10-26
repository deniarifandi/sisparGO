<?php
$isEdit = !empty($anggota);

$uploadFields = [
    'foto' => 'Foto',
    'ktp'  => 'KTP',
    'kk'   => 'Kartu Keluarga',
];

function showFileLink($file, $folder)
{
    if ($file) {
        return '
        <div class="alert alert-success border-dashed p-2 mb-2 rounded-2">
            <span class="text-success small fw-bold d-block">✔ File Tersimpan</span>
            <a href="'.base_url('uploads/'.$folder.'/'.$file).'"
               target="_blank"
               class="small text-decoration-underline">
               Lihat Berkas
            </a>
        </div>';
    }

    return '
    <div class="alert alert-danger border-dashed p-2 mb-2 rounded-2">
        <span class="text-danger small">✘ Belum ada file</span>
    </div>';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title><?= $isEdit ? 'Ubah' : 'Tambah' ?> Anggota</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url('css/pendaftaran.css') ?>">

</head>
<body>

<div class="kta-card card">

    <div class="kta-header">

        <div class="d-flex align-items-center gap-3">

            <div class="hdr-shield">

                <svg width="27" height="27" viewBox="0 0 24 24" fill="none">
                    <path d="M12 2L3 6v6c0 5.25 3.75 10.15 9 11.4C17.25 22.15 21 17.25 21 12V6L12 2z"
                    fill="rgba(255,255,255,.9)"/>
                    <path d="M9 12l2 2 4-4"
                    stroke="#0d47a1"
                    stroke-width="2.2"
                    stroke-linecap="round"
                    stroke-linejoin="round"/>
                </svg>

            </div>

            <div>

                <h1 class="text-white fw-bold mb-0" style="font-size:15px">
                    Dishub Kota Malang
                </h1>

                <div style="font-size:11px;color:rgba(255,255,255,.6)">
                    Dinas Perhubungan · Kota Malang
                </div>

            </div>

        </div>

    </div>

    <div class="kta-steps">

        <div class="step">
            <div class="step-dot">1</div>
            <span class="step-lbl">Isi Form</span>
        </div>

        <div class="step-line"></div>

        <div class="step is-active">
            <div class="step-dot">2</div>
            <span class="step-lbl">Verifikasi</span>
        </div>

        <div class="step-line"></div>

        <div class="step">
            <div class="step-dot">3</div>
            <span class="step-lbl">KTA Terbit</span>
        </div>

    </div>

    <div class="card-body p-4">

        <a href="<?= base_url('register/progress?nik='.$user['anggota_noktp']) ?>"
           class="btn btn-link ps-0 text-decoration-none mb-3">

            ← Kembali ke Progress

        </a>

        <div class="d-flex align-items-center gap-2 mb-4">

            <span class="sec-badge">A</span>

            <span class="sec-title">
                <?= $isEdit ? 'Ubah Anggota' : 'Tambah Anggota' ?>
            </span>

        </div>

        <?php if(session()->getFlashdata('success')): ?>

            <div class="alert alert-success">
                <?= session()->getFlashdata('success') ?>
            </div>

        <?php endif; ?>

        <?php if(session()->getFlashdata('errors')): ?>

            <div class="alert alert-danger">

                <ul class="mb-0">

                <?php foreach(session()->getFlashdata('errors') as $e): ?>

                    <li><?= esc($e) ?></li>

                <?php endforeach ?>

                </ul>

            </div>

        <?php endif; ?>

        <form
            action="<?= $isEdit
                ? base_url('register/anggota/update/'.$anggota['id'])
                : base_url('register/anggota/save') ?>"
            method="post"
            enctype="multipart/form-data">

            <?= csrf_field() ?>

            <input
                type="hidden"
                name="koordinator_noktp"
                value="<?= esc($user['anggota_noktp']) ?>">

            <?php if($isEdit): ?>

                <input
                    type="hidden"
                    name="id"
                    value="<?= $anggota['id'] ?>">

            <?php endif; ?>

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-header bg-light fw-semibold">
                    Data Anggota
                </div>

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Nama Lengkap
                            </label>

                            <input
                                type="text"
                                name="anggota_nama"
                                class="form-control"
                                required
                                value="<?= esc($anggota['anggota_nama'] ?? '') ?>">

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                NIK
                            </label>

                            <input
                                type="text"
                                maxlength="16"
                                name="anggota_noktp"
                                class="form-control"
                                required
                                value="<?= esc($anggota['anggota_noktp'] ?? '') ?>">

                        </div>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Tanggal Lahir
                        </label>

                        <input
                            type="date"
                            name="tgl_lahir"
                            class="form-control"
                            required
                            value="<?= esc($anggota['tgl_lahir'] ?? '') ?>">

                    </div>

                    <div>

                        <label class="form-label">
                            Alamat
                        </label>

                        <textarea
                            name="alamat"
                            rows="4"
                            class="form-control"
                            required><?= esc($anggota['alamat'] ?? '') ?></textarea>

                    </div>

                </div>

            </div>

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-header bg-light fw-semibold">

                    Dokumen Pendukung

                </div>

                <div class="card-body">

                    <div class="row g-4">

                        <?php foreach($uploadFields as $field=>$label): ?>

                        <div class="col-md-4">

                            <label class="form-label">

                                <?= $label ?>

                            </label>

                            <?php if($isEdit): ?>

                                <?= showFileLink(
                                    $anggota[$field] ?? '',
                                    'anggota_'.$field
                                ) ?>

                            <?php endif; ?>

                            <input
                                type="file"
                                name="<?= $field ?>"
                                class="form-control auto-upload"
                                accept=".jpg,.jpeg,.png,.pdf" required>

                        </div>

                        <?php endforeach; ?>

                    </div>

                </div>

            </div>

            <div class="d-flex justify-content-end gap-3">

                <a
                    href="<?= base_url('register/progress?nik='.$user['anggota_noktp']) ?>"
                    class="btn btn-outline-secondary">

                    Batal

                </a>

                <button
                    type="submit"
                    class="btn btn-success">

                    <?= $isEdit
                        ? 'Perbarui Anggota'
                        : 'Simpan Anggota' ?>

                </button>

            </div>

        </form>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

</script>

</body>
</html>