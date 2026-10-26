<?= $this->extend('layouts/admin') ?>

<?= $this->section('head') ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="card shadow-sm mx-1 mx-md-0">
   <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h5 class="mb-0 text-wrap"><?= esc($titpar['titpar_namatempat']) ?></h5>
        <small class="text-muted"><?= esc($titpar['titpar_lokasi']) ?></small>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('admin/titpar/print/' . $titpar['id']) ?>"
           target="_blank"
           class="btn btn-outline-dark btn-sm">
            <i class="fa fa-print"></i> Cetak Persetujuan
        </a>
        <a href="<?= base_url('admin/register/detail/' . $titpar['register_id']) ?>"
           class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

    <div class="card-body p-2 p-md-3">
        <div class="row g-3">

            <div class="col-12 col-lg-5">
                <div class="card border shadow-none h-100">
                    <div class="card-body">
                        <h6 class="mb-3 d-flex justify-content-between align-items-center">
                            <span><i class="fa fa-parking text-primary me-2"></i>Informasi Titik Parkir</span>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalEdit">
                                <i class="fa fa-pencil"></i> Edit
                            </button>
                        </h6>

                        <?= view('partials/_titpar_info', ['titpar' => $titpar]) ?>

                        <?php $isEdit = true; ?>
                        <form action="<?= $isEdit
                        ? base_url('admin/titpar/upload/' . $titpar['id'])
                        : base_url('admin/titpar/upload/') ?>"
                          method="post"
                          enctype="multipart/form-data">

                            <?= csrf_field() ?>
                            <input type="hidden" name="anggota_noktp" value="<?= esc($titpar['anggota_noktp']) ?>">
                            <?php if ($isEdit): ?>
                                <input type="hidden" name="id" value="<?= $titpar['id'] ?>">
                            <?php endif; ?>

                            <?= view('partials/_upload_fields', [
                                'uploadFields'       => [
                                    'foto_tampak_depan'         => 'Foto Depan',
                                    'foto_tampak_samping'      => 'Foto Samping',
                                    'survey'                    => 'Berita Acara Survey Lapangan',
                                    'persetujuan'               => 'Persetujuan Kepala Bidang'
                                ],
                                'existingData'       => $titpar ?? [],
                                'isEdit'             => $isEdit,
                                'uploadSectionTitle' => '2. Dokumen Pendukung',
                            ]) ?>

                            <div class="d-flex justify-content-end gap-4">
                                <button type="submit" class="btn btn-success">
                                    <?= $isEdit ? 'Simpan Dokumen' : 'Simpan Titik Parkir' ?>
                                </button>
                            </div>

                        </form>

                        <!-- admin pakai model baru -->
                        <!-- <?= view('partials/_titpar_admin_docs', ['titpar' => $titpar]) ?> -->
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-7">
                <?= view('partials/_titpar_kondisi', ['titpar' => $titpar]) ?>
                <?= view('partials/_titpar_map_display', ['titpar' => $titpar]) ?>
            </div>

            <div class="col-12">
                <?= view('partials/_titpar_potensi', ['titpar' => $titpar]) ?>
            </div>

            <div class="col-12">

                <!-- dokumen pakai model baru -->
                <?php $isEdit = true; ?>
                        <form action="<?= $isEdit
                        ? base_url('admin/titpar/upload/' . $titpar['id'])
                        : base_url('admin/titpar/upload/') ?>"
                          method="post"
                          enctype="multipart/form-data">

                            <?= csrf_field() ?>
                            <input type="hidden" name="anggota_noktp" value="<?= esc($titpar['anggota_noktp']) ?>">
                            <?php if ($isEdit): ?>
                                <input type="hidden" name="id" value="<?= $titpar['id'] ?>">
                            <?php endif; ?>

                            <?= view('partials/_upload_fields', [
                                'uploadFields'       => [
                                    'kesanggupan'  => 'Surat Kesanggupan',
                                    'keberatan'    => 'Surat Keberatan',
                                    'denah'        => 'Denah Lokasi',
                                    'berita_acara' => 'Berita Acara',
                                    'penunjukan'   => 'Surat Penunjukan',
                                ],
                                'existingData'       => $titpar ?? [],
                                'isEdit'             => $isEdit,
                                'uploadSectionTitle' => '2. Dokumen Pendukung',
                            ]) ?>

                            <div class="d-flex justify-content-end gap-4">
                                <button type="submit" class="btn btn-success">
                                    <?= $isEdit ? 'Perbarui Titik Parkir' : 'Simpan Titik Parkir' ?>
                                </button>
                            </div>

                        </form>
                <!-- <?= view('partials/_titpar_public_docs', ['titpar' => $titpar]) ?> -->
            </div>

        </div>
    </div>
</div>

<?= view('partials/_modal_titpar_edit', ['titpar' => $titpar]) ?>
<?= view('partials/_modal_titpar_potensi', ['titpar' => $titpar]) ?>
<?= view('partials/_modal_upload') ?>
<?= view('partials/_modal_upload_admin') ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="<?= base_url('js/titpar-detail.js') ?>"></script>
<?= $this->endSection() ?>