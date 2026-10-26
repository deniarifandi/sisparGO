<?php $isEdit = !empty($anggota); ?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-light fw-semibold">1. Data Anggota</div>
    <div class="card-body">

        <div class="mb-3">
            <label class="form-label">Nama Lengkap</label>
            <input type="text" name="anggota_nama" class="form-control"
                   placeholder="Sesuai KTP"
                   value="<?= esc(old('anggota_nama', $anggota['anggota_nama'] ?? '')) ?>"
                   required>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-12 col-sm-6">
                <label class="form-label">NIK</label>
                <?php if ($isEdit): ?>
                    <input type="text" class="form-control bg-light text-muted"
                           value="<?= esc($anggota['anggota_noktp']) ?>" disabled>
                    <input type="hidden" name="anggota_noktp"
                           value="<?= esc($anggota['anggota_noktp']) ?>">
                <?php else: ?>
                    <input type="text" name="anggota_noktp" class="form-control"
                           placeholder="16 digit NIK"
                           maxlength="16" inputmode="numeric"
                           oninput="this.value=this.value.replace(/\D/g,'')"
                           value="<?= esc(old('anggota_noktp')) ?>"
                           required>
                <?php endif; ?>
            </div>
            <div class="col-12 col-sm-6">
                <label class="form-label">Tanggal Lahir</label>
                <input type="date" name="tgl_lahir" class="form-control"
                       value="<?= esc(old('tgl_lahir', $anggota['tgl_lahir'] ?? '')) ?>"
                       required>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Alamat Lengkap</label>
            <textarea class="form-control" name="alamat" rows="3"
                      placeholder="Jalan, RT/RW, Kelurahan, Kecamatan..."
                      required><?= esc(old('alamat', $anggota['alamat'] ?? '')) ?></textarea>
        </div>

    </div>
</div>