<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Pendaftaran KTA</h5>
        <a class="btn btn-outline-secondary btn-sm" href="<?= base_url('/') ?>">
            <i class="fa fa-arrow-left"></i> Kembali
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="ktaTable" class="table table-hover align-middle"
                   style="width:100%; font-size:13px;">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Status</th>
                        <th>Foto</th>
                        <th>Catatan</th>
                        <th>Detail</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<?= view('partials/_modal_upload') ?>
<?= view('partials/_modal_catatan') ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('js/admin-register.js') ?>"></script>
<?= $this->endSection() ?>