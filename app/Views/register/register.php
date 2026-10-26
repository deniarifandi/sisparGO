<?= $this->extend('layouts/register') ?>

<?= $this->section('content') ?>

    <?= view('partials/_kta_header', [
        'activeStep'  => 2,
        'headerTitle' => 'KTA Digital',
        'showLogout'  => true,
    ]) ?>

    <div class="card-body p-4">

        <!-- Tabs -->
        <ul class="nav nav-pills kta-tabs mb-4" id="ktaTabs" role="tablist">
            <li class="nav-item flex-grow-1 text-center" role="presentation">
                <button class="nav-link active w-100"
                        data-bs-toggle="pill" data-bs-target="#tab-daftar"
                        type="button" role="tab">Pendaftaran Baru</button>
            </li>
            <li class="nav-item flex-grow-1 text-center" role="presentation">
                <button class="nav-link w-100"
                        data-bs-toggle="pill" data-bs-target="#tab-status"
                        type="button" role="tab">Cek Status</button>
            </li>
        </ul>

        <?= view('partials/_flash') ?>

        <div class="tab-content" id="ktaTabContent">
            <div class="tab-pane fade show active" id="tab-daftar" role="tabpanel">
                <?= view('partials/_register_form') ?>
            </div>
            <div class="tab-pane fade" id="tab-status" role="tabpanel">
                <?= view('partials/_progress_form') ?>
            </div>
        </div>

    </div>

<?= $this->endSection() ?>