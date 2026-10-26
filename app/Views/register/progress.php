<?= $this->extend('layouts/register') ?>

<?= $this->section('content') ?>

    <?= view('partials/_kta_header', [
        'activeStep'  => 2,
        'headerTitle' => 'KTA Digital',
        'showLogout'  => true,
    ]) ?>

    <div class="card-body p-4">

        <?= view('partials/_status_card', ['user' => $user]) ?>

        <!-- Tabs -->
        <ul class="nav nav-pills kta-tabs mb-4" id="ktaTabs" role="tablist">
            <li class="nav-item flex-grow-1 text-center" role="presentation">
                <button class="nav-link active w-100"
                        data-bs-toggle="pill" data-bs-target="#tab-koordinator"
                        type="button" role="tab">Koordinator</button>
            </li>
            <li class="nav-item flex-grow-1 text-center" role="presentation">
                <button class="nav-link w-100"
                        data-bs-toggle="pill" data-bs-target="#tab-anggota"
                        type="button" role="tab">Anggota</button>
            </li>
            <li class="nav-item flex-grow-1 text-center" role="presentation">
                <button class="nav-link w-100"
                        data-bs-toggle="pill" data-bs-target="#tab-titpar"
                        type="button" role="tab">Titik Parkir</button>
            </li>
        </ul>

        <?= view('partials/_flash') ?>

        <div class="tab-content" id="ktaTabContent">
            <div class="tab-pane fade show active" id="tab-koordinator" role="tabpanel">
                <?= view('partials/_koordinator_form', ['user' => $user]) ?>
            </div>
            <div class="tab-pane fade" id="tab-anggota" role="tabpanel">
                <?= view('partials/_anggota_list', ['anggota' => $anggota]) ?>
            </div>
            <div class="tab-pane fade" id="tab-titpar" role="tabpanel">
                <?= view('partials/_titpar_list', ['titpar' => $titpar]) ?>
            </div>
        </div>

    </div>

<?= $this->endSection() ?>