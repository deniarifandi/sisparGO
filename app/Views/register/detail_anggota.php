<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<div class="card shadow-sm mx-1 mx-md-0">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h5 class="mb-0"><?= esc($anggota['anggota_nama']) ?></h5>
            <small class="text-muted"><?= esc($anggota['anggota_noktp']) ?></small>
        </div>
        <a href="<?= base_url('admin/register/detail/' . $anggota['register_id']) ?>"
           class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="card-body">
        <div class="row g-3">

            <div class="col-12 col-md-5">
                <div class="card border shadow-none h-100">
                    <div class="card-body">
                        <h6 class="mb-3 d-flex justify-content-between align-items-center">
                            <span><i class="fa fa-user text-primary me-2"></i>Informasi Anggota</span>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditAnggota">
                                <i class="fa fa-pencil"></i> Edit
                            </button>
                        </h6>
                        <?= view('partials/_anggota_info', ['anggota' => $anggota]) ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-7">
                <div class="card border shadow-none h-100">
                    <div class="card-body">
                        <!-- <?= view('partials/_anggota_docs', ['anggota' => $anggota]) ?> -->

                        <?php $isEdit = true; ?>
                        <form action="<?= $isEdit
                        ? base_url('admin/anggota/upload/' . $anggota['id'])
                        : base_url('admin/anggota/upload/') ?>"
                          method="post"
                          enctype="multipart/form-data">

                            <?= csrf_field() ?>
                            <input type="hidden" name="anggota_noktp" value="<?= esc($anggota['anggota_noktp']) ?>">
                            <?php if ($isEdit): ?>
                                <input type="hidden" name="id" value="<?= $anggota['id'] ?>">
                            <?php endif; ?>

                            <?= view('partials/_upload_fields', [
                                'uploadFields'      => [
                                    'anggota_foto'  => 'Swafoto',
                                    'kk'            => 'Foto KK',
                                    'foto_ktp'      => 'Foto KTP',
                                ],
                                'existingData'       => $anggota ?? [],
                                'isEdit'             => $isEdit,
                                'uploadSectionTitle' => '2. Dokumen Pendukung',
                            ]) ?>

                            <div class="d-flex justify-content-end gap-4">
                                <button type="submit" class="btn btn-success">
                                    <?= $isEdit ? 'Perbarui Titik Parkir' : 'Simpan Titik Parkir' ?>
                                </button>
                            </div>

                        </form>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?= view('partials/_modal_anggota_edit', ['anggota' => $anggota]) ?>
<?= view('partials/_modal_upload') ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('js/anggota-detail.js') ?>"></script>
<?= $this->endSection() ?>