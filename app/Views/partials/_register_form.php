<form id="form-register"
      action="<?= base_url('register/save') ?>"
      method="post"
      enctype="multipart/form-data">
    <?= csrf_field() ?>

    <!-- Section A -->
    <div class="d-flex align-items-center gap-2 mb-3">
        <span class="sec-badge">A</span>
        <span class="sec-title">Data Koordinator</span>
    </div>

    <div class="mb-3">
        <label class="form-label">Nama Lengkap</label>
        <input type="text" class="form-control" name="anggota_nama"
               placeholder="Sesuai KTP — contoh: Budi Santoso"
               value="<?= old('anggota_nama') ?>" required>
        <?php if(session('errors.anggota_nama')): ?>
            <div class="invalid-text"><?= session('errors.anggota_nama') ?></div>
        <?php endif; ?>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <label class="form-label">NIK (Sesuai KTP)</label>
            <div class="position-relative">
                <input type="text" class="form-control pe-5"
                       name="anggota_noktp" id="nikInput"
                       placeholder="16 digit NIK"
                       minlength="16" maxlength="16"
                       value="<?= old('anggota_noktp') ?>"
                       oninput="onNik(this)" inputmode="numeric" required>
                <span class="nik-ctr" id="nikCtr">0/16</span>
            </div>
            <?php if(session('errors.anggota_noktp')): ?>
                <div class="invalid-text"><?= session('errors.anggota_noktp') ?></div>
            <?php endif; ?>
        </div>
        <div class="col-md-6">
            <label class="form-label">No. Telepon</label>
            <input type="tel" class="form-control" id="hp" name="hp"
                   placeholder="08xx-xxxx-xxxx"
                   value="<?= old('hp') ?>" inputmode="tel" required>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Alamat Lengkap</label>
        <textarea class="form-control" name="anggota_alamat" rows="2"
                  placeholder="Jalan, RT/RW, Kelurahan, Kecamatan..." required
        ><?= old('anggota_alamat') ?></textarea>
        <?php if(session('errors.anggota_alamat')): ?>
            <div class="invalid-text"><?= session('errors.anggota_alamat') ?></div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label class="form-label">Tanggal Lahir</label>
        <input type="date" class="form-control" name="anggota_tgllhr"
               value="<?= old('anggota_tgllhr') ?>">
        <?php if(session('errors.anggota_tgllhr')): ?>
            <div class="invalid-text"><?= session('errors.anggota_tgllhr') ?></div>
        <?php endif; ?>
    </div>

    <!-- Section B -->
    <div class="d-flex align-items-center gap-2 mb-3 mt-4 pt-3 border-top">
        <span class="sec-badge">B</span>
        <span class="sec-title">Dokumen Pendukung</span>
    </div>

    <?php
    $uploadFields = [
        [
            'id'    => 'selfie',
            'name'  => 'anggota_foto',
            'label' => 'Foto Selfie',
            'svg'   => '<path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/>',
        ],
        [
            'id'    => 'ktp',
            'name'  => 'foto_ktp',
            'label' => 'Foto KTP',
            'svg'   => '<rect x="2" y="5" width="20" height="14" rx="2"/><circle cx="8" cy="11" r="2"/><line x1="13" y1="9.5" x2="19" y2="9.5"/><line x1="13" y1="12.5" x2="19" y2="12.5"/><line x1="6" y1="15.5" x2="18" y2="15.5"/>',
        ],
        [
            'id'    => 'kk',
            'name'  => 'kk',
            'label' => 'Foto KK',
            'svg'   => '<rect x="2" y="5" width="20" height="14" rx="2"/><circle cx="8" cy="11" r="2"/><line x1="13" y1="9.5" x2="19" y2="9.5"/><line x1="13" y1="12.5" x2="19" y2="12.5"/><line x1="6" y1="15.5" x2="18" y2="15.5"/>',
        ],
    ];
    ?>

    <div class="upload-grid mb-1">
        <?php foreach ($uploadFields as $f): ?>
        <div>
            <div class="uz-label"><?= $f['label'] ?></div>
            <div class="uz" id="uz-<?= $f['id'] ?>">
                <input type="file" name="<?= $f['name'] ?>" accept="image/*"
                       onchange="onFile('uz-<?= $f['id'] ?>','un-<?= $f['id'] ?>',this)">
                <div class="uz-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <?= $f['svg'] ?>
                    </svg>
                </div>
                <div class="uz-name" id="un-<?= $f['id'] ?>">Pilih Foto</div>
                <div class="uz-hint">JPG/PNG · maks 3MB</div>
            </div>
            <?php if(session('errors.' . $f['name'])): ?>
                <div class="invalid-text mt-1"><?= session('errors.' . $f['name']) ?></div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Turnstile + Submit -->
    <div class="border rounded-3 p-3 bg-light mt-4">
        <div class="text-center mb-3">
            <!-- <div class="cf-turnstile"
                 data-sitekey="<?= env('TURNSTILE_SITE_KEY') ?>"
                 data-callback="turnstileSuccess">
            </div> -->
        </div>
     <!--    <button type="submit" id="btnSubmit"
                class="btn btn-navy w-100 d-flex align-items-center justify-content-center gap-2"
                disabled>
            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor">
                <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
            </svg>
            Kirim Pendaftaran
        </button> -->

        <button type="submit"
        id="btnSubmit"
        class="btn btn-navy w-100 d-flex align-items-center justify-content-center gap-2">
    Kirim Pendaftaran
</button>
    </div>
</form>