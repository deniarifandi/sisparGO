<?php
$isEdit = !empty($anggota);

$uploadFields = [
    'foto' => 'Foto Selfie',
    'ktp'  => 'Foto KTP',
    'kk'   => 'Foto KK',
];

function showFileLink($file, $folder) {
    if ($file) {
        return '
        <div class="d-flex align-items-center justify-content-between bg-light p-2 rounded-2 border border-success-subtle mb-2">
            <span class="text-success small fw-medium">
                <small>
                <svg width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill me-1" viewBox="0 0 16 16">
                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                </svg>
                Tersimpan
                </small>
            </span>
            <a href="'.base_url('uploads/'.$folder.'/'.$file).'" target="_blank"
               class="btn btn-sm btn-link text-decoration-none p-0 text-primary small fw-semibold">
                <small>Lihat Berkas →</small>
            </a>
        </div>';
    }
    return '';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Ubah' : 'Tambah' ?> Anggota Parkir</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('css/pendaftaran.css') ?>">
    <style>
        @media (max-width: 576px) {
            .card-body { padding: 1rem !important; }
            .d-flex.justify-content-end {
                flex-direction: column-reverse;
                gap: 10px !important;
            }
            .d-flex.justify-content-end .btn { width: 100%; }
        }
    </style>
</head>
<body>

<div class="kta-card card">

    <!-- ── HEADER ── -->
    <div class="kta-header">
        <div class="d-flex align-items-center gap-3">
            <div class="hdr-shield">
                <svg width="27" height="27" viewBox="0 0 24 24" fill="none">
                    <path d="M12 2L3 6v6c0 5.25 3.75 10.15 9 11.4C17.25 22.15 21 17.25 21 12V6L12 2z"
                          fill="rgba(255,255,255,.9)"/>
                    <path d="M9 12l2 2 4-4" stroke="#0d47a1" stroke-width="2.2"
                          stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div>
                <h1 class="text-white fw-bold mb-0" style="font-size:15px;">
                    Dishub Kota Malang
                </h1>
                <div style="font-size:11px;color:rgba(255,255,255,.6)">
                    Dinas Perhubungan · Kota Malang
                </div>
            </div>
        </div>
    </div>

    <!-- ── PROCESS STEPS ── -->
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

    <!-- ── BODY ── -->
    <div class="card-body p-4">

        <!-- Flash: success -->
        <?php if(session()->getFlashdata('success')): ?>
        <div class="kta-alert kta-alert-success mb-3">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
            </svg>
            <span><?= esc(session()->getFlashdata('success')) ?></span>
        </div>
        <?php endif; ?>

        <!-- Flash: error -->
        <?php if(session()->getFlashdata('error') || session()->getFlashdata('errors')): ?>
        <div class="kta-alert kta-alert-error mb-3">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
            </svg>
            <span>
                Terjadi kesalahan. Silakan cek form kembali.
                <?php if(session()->getFlashdata('errors')): ?>
                <ul>
                    <?php foreach(session()->getFlashdata('errors') as $e): ?>
                        <li><?= esc($e) ?></li>
                    <?php endforeach ?>
                </ul>
                <?php endif; ?>
                <?php if(session()->getFlashdata('error')): ?>
                <p><?= esc(session()->getFlashdata('error')) ?></p>
                <?php endif; ?>
            </span>
        </div>
        <?php endif; ?>

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
                    <li>Format file: JPG, PNG, atau PDF.</li>
                    <li>Maksimal ukuran file 3 MB.</li>
                </ul>
            </small>
        </div>

        <form action="<?= $isEdit
                ? base_url('register/anggota/update/' . $anggota['id'])
                : base_url('register/anggota/save') ?>"
              method="post"
              enctype="multipart/form-data">

            <?= csrf_field() ?>

            <?php if($isEdit): ?>
                <input type="hidden" name="id" value="<?= $anggota['id'] ?>">
            <?php endif; ?>

            <!-- Section 1: Data Anggota -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light fw-semibold">
                    1. Data Anggota
                </div>
                <div class="card-body">

                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text"
                               name="anggota_nama"
                               class="form-control"
                               placeholder="Sesuai KTP"
                               value="<?= esc(old('anggota_nama', $anggota['anggota_nama'] ?? '')) ?>"
                               required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label">NIK</label>
                            <?php if($isEdit): ?>
                                <!-- NIK tidak bisa diubah saat edit -->
                                <input type="text"
                                       class="form-control bg-light text-muted"
                                       value="<?= esc($anggota['anggota_noktp']) ?>"
                                       disabled>
                                <input type="hidden"
                                       name="anggota_noktp"
                                       value="<?= esc($anggota['anggota_noktp']) ?>">
                            <?php else: ?>
                                <input type="text"
                                       name="anggota_noktp"
                                       class="form-control"
                                       placeholder="16 digit NIK"
                                       maxlength="16"
                                       inputmode="numeric"
                                       oninput="this.value=this.value.replace(/\D/g,'')"
                                       value="<?= esc(old('anggota_noktp')) ?>"
                                       required>
                            <?php endif; ?>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date"
                                   name="tgl_lahir"
                                   class="form-control"
                                   value="<?= esc(old('tgl_lahir', $anggota['tgl_lahir'] ?? '')) ?>"
                                   required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Alamat Lengkap</label>
                        <textarea class="form-control"
                                  name="alamat"
                                  rows="3"
                                  placeholder="Jalan, RT/RW, Kelurahan, Kecamatan..."
                                  required><?= esc(old('alamat', $anggota['alamat'] ?? '')) ?></textarea>
                    </div>

                </div>
            </div>

            <!-- Section 2: Dokumen -->
            <div class="card border border-light-subtle shadow-sm mb-4 rounded-3">
                <div class="card-header bg-light fw-semibold text-secondary d-flex justify-content-between align-items-center py-3">
                    <span class="text-dark">2. Dokumen Anggota</span>
                    <span class="badge bg-secondary-subtle text-secondary small fw-normal">
                        Format: JPG / PNG / PDF
                    </span>
                </div>
                <div class="card-body p-3">
                    <div class="row g-3">
                        <?php foreach($uploadFields as $field => $label):
                            $hasFile = ($isEdit && !empty($anggota[$field]));
                            $folder  = match($field) {
                                'ktp'  => 'anggota_ktp',
                                'kk'   => 'anggota_kk',
                                'foto' => 'anggota_foto',
                                default => $field,
                            };
                        ?>
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 h-100 <?= $hasFile ? 'border-success-subtle bg-success-subtle bg-opacity-10' : 'border-light-subtle' ?>">

                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <label class="form-label fw-semibold text-dark mb-0">
                                        <?= $label ?>
                                    </label>
                                    <?php if($hasFile): ?>
                                        <span class="badge bg-success text-white px-2 py-1 rounded-pill" style="font-size:10px;">
                                            ✓ Sudah Ada
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark px-2 py-1 rounded-pill" style="font-size:10px;">
                                            ⚠ Belum Diunggah
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <?php if($hasFile): ?>
                                    <?= showFileLink($anggota[$field], $folder) ?>
                                <?php endif; ?>

                                <div class="input-group input-group-sm">
                                    <input type="file"
                                           name="<?= $field ?>"
                                           class="form-control"
                                           accept=".pdf,.jpg,.jpeg,.png">
                                </div>
                                <div class="form-text text-muted" style="font-size:11px;">
                                    <?= $hasFile
                                        ? 'Pilih berkas baru jika ingin mengubah.'
                                        : 'Pilih berkas untuk mengunggah.' ?>
                                </div>

                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="d-flex justify-content-end gap-4">
                <a href="<?= base_url('register/progress') ?>"
                   class="btn btn-outline-secondary">
                    Batal
                </a>
                <button type="submit" class="btn btn-success">
                    <?= $isEdit ? 'Perbarui Anggota' : 'Simpan Anggota' ?>
                </button>
            </div>

        </form>
    </div><!-- /card-body -->
</div><!-- /kta-card -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>