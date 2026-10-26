<?= $this->extend('layouts/register') ?>

<?= $this->section('head') ?>
    <!-- <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"> -->

<?= $this->endSection() ?>

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
            <span class="sec-badge">TP</span>
            <span class="sec-title">
                <?= $isEdit ? 'Ubah Titik Parkir' : 'Tambah Titik Parkir' ?>
            </span>
        </div>

        <div class="alert alert-info">
            <small>
                <strong>Petunjuk</strong>
                <ul class="mb-0 mt-2">
                    <li>Isi data lokasi parkir.</li>
                    <li>Upload dokumen pendukung.</li>
                    <li>Format file: PDF, JPG, JPEG, PNG.</li>
                </ul>
            </small>
        </div>

        <form action="<?= $isEdit
        ? base_url('register/titpar/save/' . $titpar['id'])
        : base_url('register/titpar/save') ?>"
          method="post"
          enctype="multipart/form-data">

            <?= csrf_field() ?>
            <input type="hidden" name="anggota_noktp" value="<?= esc($user['anggota_noktp']) ?>">
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= $titpar['id'] ?>">
            <?php endif; ?>

            <?= view('partials/_titpar_lokasi', ['titpar' => $titpar ?? []]) ?>

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
                <a href="<?= base_url('register/progress') ?>"
                   class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-success">
                    <?= $isEdit ? 'Perbarui Titik Parkir' : 'Simpan Titik Parkir' ?>
                </button>
            </div>

        </form>
    </div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <!-- <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script> -->
    <!-- <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"> -->
    <script src="<?= base_url('js/titpar.js') ?>"></script>
<?= $this->endSection() ?>