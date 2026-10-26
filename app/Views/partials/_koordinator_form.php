<?php
helper('upload');

$isApproved = ($user['anggota_status'] ?? '0') == '1';

$uploadFields = [
    'anggota_foto' => 'Swafoto',
    'kk'           => 'Foto KK',
    'foto_ktp'     => 'Foto KTP',
];
?>

<form action="<?= base_url('register/update') ?>" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="id"           value="<?= $user['id'] ?>">
    <input type="hidden" name="anggota_noktp" value="<?= $user['anggota_noktp'] ?>">

    <div class="d-flex align-items-center gap-2 mb-3">
        <span class="sec-badge">A</span>
        <span class="sec-title">Data Koordinator</span>
    </div>

    <fieldset <?= $isApproved ? 'disabled' : '' ?>>

        <div class="row g-3">
            <div class="col-12 col-sm-6">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" class="form-control"
                       name="anggota_nama" value="<?= esc($user['anggota_nama']) ?>" required>
            </div>
            <div class="col-12 col-sm-6">
                <label class="form-label fw-semibold">NIK (Tidak dapat diubah)</label>
                <input type="text" class="form-control bg-light text-muted"
                       value="<?= esc($user['anggota_noktp']) ?>" disabled>
            </div>
        </div>

        <div class="mt-3">
            <label class="form-label fw-semibold">Nomor HP / WhatsApp</label>
            <input type="text" class="form-control" name="hp"
                   value="<?= esc($user['hp']) ?>" placeholder="Contoh: 08123456789" required>
        </div>

        <div class="mt-3">
            <label class="form-label fw-semibold">Alamat Lengkap</label>
            <textarea class="form-control" name="anggota_alamat" rows="3" required
            ><?= esc($user['anggota_alamat']) ?></textarea>
        </div>

        <div class="mt-3">
            <label class="form-label fw-semibold">Tanggal Lahir</label>
            <input type="date" class="form-control" name="anggota_tgllhr"
                   value="<?= esc($user['anggota_tgllhr']) ?>" required>
        </div>

        <!-- Upload Berkas -->
        <h3 class="h5 text-primary mt-5 pt-3 border-top mb-3">Unggah Berkas (PDF/JPG)</h3>

        <div class="row g-3">
            <?php foreach ($uploadFields as $field => $label):
                $hasFile = !empty($user[$field]);
            ?>
            <div class="col-md-6">
                <div class="p-3 border rounded-3 h-100 <?= $hasFile ? 'border-success-subtle bg-success-subtle bg-opacity-10' : 'border-light-subtle' ?>">

                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <label class="form-label fw-semibold text-dark mb-0"><?= $label ?></label>
                        <?php if ($hasFile): ?>
                            <span class="badge bg-success text-white px-2 py-1 rounded-pill" style="font-size:10px;">
                                ✓ Sudah Ada
                            </span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark px-2 py-1 rounded-pill" style="font-size:10px;">
                                ⚠ Belum Diunggah
                            </span>
                        <?php endif; ?>
                    </div>

                    <?= showFileLink($user[$field] ?? null, $field) ?>

                    <div class="input-group input-group-sm">
                        <input type="file" name="<?= $field ?>"
                               class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                    <div class="form-text text-muted" style="font-size:11px;">
                        <?= $hasFile ? 'Pilih berkas baru jika ingin mengubah.' : 'Pilih berkas untuk mengunggah.' ?>
                    </div>

                </div>
            </div>
            <?php endforeach; ?>
        </div>

    </fieldset>

    <?php if (!$isApproved): ?>
        <button type="submit" class="btn btn-success w-100 fw-bold py-3 fs-6 mt-4 shadow-sm">
            Simpan & Perbarui Data
        </button>
    <?php endif; ?>

</form>