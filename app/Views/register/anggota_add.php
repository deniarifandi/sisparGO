<?php $isEdit = !empty($anggota); ?>

<?= $this->extend('layouts/register') ?>

<?= $this->section('content') ?>

    <?= view('partials/_kta_header', [
        'activeStep'  => 2,
        'headerTitle' => 'KTA Digital',
        'showLogout'  => true,
    ]) ?>

    <div class="card-body p-4">

        <?= view('partials/_flash') ?>

        <a href="<?= base_url('register/progress') ?>"
           class="btn btn-link text-decoration-none ps-0 mb-3">
            ← Kembali ke Progress
        </a>

        <div class="d-flex align-items-center gap-2 mb-4">
            <span class="sec-badge">AG</span>
            <span class="sec-title">
                <?= $isEdit ? 'Ubah Data Anggota' : 'Tambah Anggota Parkir' ?>
            </span>
        </div>

        <div class="alert alert-info">
            <small>
                <strong>Petunjuk</strong>
                <ul class="mb-0 mt-2">
                    <li>Isi data anggota sesuai KTP.</li>
                    <li>Upload foto selfie, KTP, dan KK anggota.</li>
                    <li>Format file: JPG, PNG, atau PDF. Maksimal 3 MB.</li>
                </ul>
            </small>
        </div>

        <form action="<?= $isEdit
                ? base_url('register/anggota/save/' . $anggota['id'])
                : base_url('register/anggota/save') ?>"
              method="post"
              enctype="multipart/form-data">

            <?= csrf_field() ?>
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= $anggota['id'] ?>">
            <?php endif; ?>

            <?= view('partials/_anggota_form', ['anggota' => $anggota ?? []]) ?>

             <?= view('partials/_upload_fields', [
                'uploadFields'       => [
                    'anggota_foto'    => 'Swafoto (pas foto)',
                    'kk'              => 'KK (Kartu Keluarga)',
                    'foto_ktp'        => 'foto_ktp'
                ],
                'existingData'       => $anggota ?? [],
                'isEdit'             => $isEdit,
                'uploadSectionTitle' => '2. Dokumen Pendukung',
            ]) ?>

            <div class="d-flex justify-content-end gap-4">
                <a href="<?= base_url('register/progress') ?>"
                   class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-success">
                    <?= $isEdit ? 'Perbarui Anggota' : 'Simpan Anggota' ?>
                </button>
            </div>

        </form>
    </div>

<?= $this->endSection() ?>