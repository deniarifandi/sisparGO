<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<div class="card shadow-sm mb-3">

       <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Detail Pendaftaran KTA</h5>

        <div class="d-flex gap-2">
            <a href="<?= base_url('admin/register/print-penunjukan/' . $register['id']) ?>"
               target="_blank"
               class="btn btn-outline-dark btn-sm">
                <i class="fa fa-print"></i> Surat Penunjukan
            </a>

            <a href="<?= base_url('/admin/register') ?>"
               class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-arrow-left me-1"></i>
                <span class="d-none d-sm-inline">Kembali</span>
            </a>
        </div>
    </div>

    <?= view('partials/_admin_profile_summary', ['register' => $register]) ?>

    <div class="card-body pt-3">

        <ul class="nav nav-tabs mb-3" id="detailTabs">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#tab-register">
                    <i class="fa fa-file-alt me-1"></i>Pendaftaran
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-anggota">
                    <i class="fa fa-user me-1"></i>Anggota
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-titpar">
                    <i class="fa fa-map-marker-alt me-1"></i>Titik Parkir
                </a>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="tab-register">
                <?= view('partials/_tab_register', ['register' => $register]) ?>
            </div>
            <div class="tab-pane fade" id="tab-anggota">
                <?= view('partials/_tab_anggota', ['anggota' => $anggota]) ?>
            </div>
            <div class="tab-pane fade" id="tab-titpar">
                <?= view('partials/_tab_titpar', ['titpars' => $titpars]) ?>
            </div>
        </div>

    </div>
</div>

<?= view('partials/_modal_upload') ?>
<?= view('partials/_modal_edit_register', ['register' => $register]) ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('js/admin-detail.js') ?>"></script>
<?= $this->endSection() ?>