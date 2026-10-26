<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dishub Malang – KTA Digital</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
     <link rel="stylesheet" type="text/css" href="<?php echo base_url() ?>css/pendaftaran.css">
     <script
  src="https://challenges.cloudflare.com/turnstile/v0/api.js"
  async
  defer
></script>
</head>
<body>

<div class="kta-card card">

    <!-- ── HEADER ── -->
    <div class="kta-header">
        <div class="d-flex align-items-center gap-3 position-relative">
            <div class="hdr-shield">
                <svg width="27" height="27" viewBox="0 0 24 24" fill="none">
                    <path d="M12 2L3 6v6c0 5.25 3.75 10.15 9 11.4C17.25 22.15 21 17.25 21 12V6L12 2z"
                          fill="rgba(255,255,255,.9)"/>
                    <path d="M9 12l2 2 4-4" stroke="#0d47a1" stroke-width="2.2"
                          stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div>
                <h1 class="text-white fw-bold mb-0" style="font-size:15px;line-height:1.3">
                    Dishub Kota Malang
                </h1>
                <div class="mt-1" style="font-size:11px;color:rgba(255,255,255,.55);letter-spacing:.04em">
                    Dinas Perhubungan · Kota Malang
                </div>
            </div>
        </div>
        <div class="kta-pill mt-3">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                <path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 14H4V10h16v8zm0-10H4V6h16v2z"/>
            </svg>
            Pengurusan KTA Digital
        </div>
    </div>

    <!-- ── PROCESS STEPS ── -->
    <div class="kta-steps">
        <div class="step is-active">
            <div class="step-dot">1</div>
            <span class="step-lbl">Isi Form</span>
        </div>
        <div class="step-line"></div>
        <div class="step">
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

        <!-- Bootstrap nav-pills tabs -->
        <ul class="nav nav-pills kta-tabs mb-4" id="ktaTabs" role="tablist">
            <li class="nav-item flex-grow-1 text-center" role="presentation">
                <button class="nav-link active w-100"
                        id="tab-daftar-btn"
                        data-bs-toggle="pill"
                        data-bs-target="#tab-daftar"
                        type="button" role="tab">
                    Pendaftaran Baru
                </button>
            </li>
            <li class="nav-item flex-grow-1 text-center" role="presentation">
                <button class="nav-link w-100"
                        id="tab-status-btn"
                        data-bs-toggle="pill"
                        data-bs-target="#tab-status"
                        type="button" role="tab">
                    Cek Status
                </button>
            </li>
        </ul>

        <!-- Flash: success -->
        <?php if(session()->getFlashdata('success')): ?>

        <div class="kta-alert kta-alert-success mb-3">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
            </svg>
            <span><?= session()->getFlashdata('success') ?></span>
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
                <ul><?php foreach(session()->getFlashdata('errors') as $e): ?><li><?= esc($e) ?></li><?php endforeach ?></ul>
                <?php endif; ?>
                <?php if(session()->getFlashdata('error')): ?>
                <p><?= esc(session()->getFlashdata('error')) ?></p>
                <?php endif; ?>
            </span>
        </div>
        <?php endif; ?>

        <!-- Tab panes -->
        <div class="tab-content" id="ktaTabContent">

            <!-- ── PENDAFTARAN BARU ── -->
            <div class="tab-pane fade show active" id="tab-daftar" role="tabpanel">

                <form id="form-register"
                      action="<?= base_url('register/save') ?>"
                      method="post"
                      enctype="multipart/form-data">
                    <?= csrf_field() ?>
                   

                    <!-- Section A: Data Koordinator -->
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="sec-badge">A</span>
                        <span class="sec-title">Data Koordinator</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text"
                               class="form-control"
                               name="anggota_nama"
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
                                <input type="text"
                                       class="form-control pe-5"
                                       name="anggota_noktp"
                                       id="nikInput"
                                       placeholder="16 digit NIK"
                                       minlength="16"
                                       maxlength="16"
                                       value="<?= old('anggota_noktp') ?>"
                                       oninput="onNik(this)"
                                       inputmode="numeric" required>

                                <span class="nik-ctr" id="nikCtr">0/16</span>
                            </div>
                            <?php if(session('errors.anggota_noktp')): ?>
                                <div class="invalid-text"><?= session('errors.anggota_noktp') ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. Telepon</label>
                            <input type="tel"
                                   class="form-control"
                                   id="hp"
                                   name="hp"
                                   placeholder="08xx-xxxx-xxxx"
                                   value="<?= old('hp') ?>"
                                   inputmode="tel" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Alamat Lengkap</label>
                        <textarea class="form-control"
                                  name="anggota_alamat"
                                  rows="2"
                                  placeholder="Jalan, RT/RW, Kelurahan, Kecamatan..." required><?= old('anggota_alamat') ?></textarea>
                        <?php if(session('errors.anggota_alamat')): ?>
                            <div class="invalid-text"><?= session('errors.anggota_alamat') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tanggal Lahir</label>
                        <input type="date"
                               class="form-control"
                               name="anggota_tgllhr"
                               value="<?= old('anggota_tgllhr') ?>">
                        <?php if(session('errors.anggota_tgllhr')): ?>
                            <div class="invalid-text"><?= session('errors.anggota_tgllhr') ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Section B: Dokumen -->
                    <div class="d-flex align-items-center gap-2 mb-3 mt-4 pt-3 border-top">
                        <span class="sec-badge">B</span>
                        <span class="sec-title">Dokumen Pendukung</span>
                    </div>

                    <div class="upload-grid mb-1">

                        <!-- Foto Selfie -->
                        <div>
                            <div class="uz-label">Foto Selfie</div>
                            <div class="uz" id="uz-selfie">
                                <input type="file" name="anggota_foto" accept="image/*"
                                       onchange="onFile('uz-selfie','un-selfie',this)">
                                <div class="uz-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                                        <circle cx="12" cy="13" r="4"/>
                                    </svg>
                                </div>
                                <div class="uz-name" id="un-selfie">Pilih Foto</div>
                                <div class="uz-hint">JPG/PNG · maks 3MB</div>
                            </div>
                            <?php if(session('errors.anggota_foto')): ?>
                                <div class="invalid-text mt-1"><?= session('errors.anggota_foto') ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Foto KTP -->
                        <div>
                            <div class="uz-label">Foto KTP</div>
                            <div class="uz" id="uz-ktp">
                                <input type="file" name="foto_ktp" accept="image/*"
                                       onchange="onFile('uz-ktp','un-ktp',this)">
                                <div class="uz-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="2" y="5" width="20" height="14" rx="2"/>
                                        <circle cx="8" cy="11" r="2"/>
                                        <line x1="13" y1="9.5" x2="19" y2="9.5"/>
                                        <line x1="13" y1="12.5" x2="19" y2="12.5"/>
                                        <line x1="6" y1="15.5" x2="18" y2="15.5"/>
                                    </svg>
                                </div>
                                <div class="uz-name" id="un-ktp">Pilih Foto</div>
                                <div class="uz-hint">JPG/PNG · maks 2MB</div>
                            </div>
                            <?php if(session('errors.foto_ktp')): ?>
                                <div class="invalid-text mt-1"><?= session('errors.foto_ktp') ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Foto KK -->
                        <div>
                            <div class="uz-label">Foto KK</div>
                            <div class="uz" id="uz-kk">
                                <input type="file" name="kk" accept="image/*"
                                       onchange="onFile('uz-kk','un-kk',this)">
                                <div class="uz-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="2" y="5" width="20" height="14" rx="2"/>
                                        <circle cx="8" cy="11" r="2"/>
                                        <line x1="13" y1="9.5" x2="19" y2="9.5"/>
                                        <line x1="13" y1="12.5" x2="19" y2="12.5"/>
                                        <line x1="6" y1="15.5" x2="18" y2="15.5"/>
                                    </svg>
                                </div>
                                <div class="uz-name" id="un-kk">Pilih Foto</div>
                                <div class="uz-hint">JPG/PNG · maks 2MB</div>
                            </div>
                            <?php if(session('errors.kk')): ?>
                                <div class="invalid-text mt-1"><?= session('errors.kk') ?></div>
                            <?php endif; ?>
                        </div>

                    </div><!-- /upload-grid -->

                  <div class="border rounded-3 p-3 bg-light mt-4">
                    <div class="text-center mb-3">
                        <div class="cf-turnstile"
                             data-sitekey="<?= env('TURNSTILE_SITE_KEY') ?>"
                             data-callback="turnstileSuccess">
                        </div>
                    </div>

                    <button type="submit"
                            id="btnSubmit"
                            class="btn btn-navy w-100 d-flex align-items-center justify-content-center gap-2"
                            disabled>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                        </svg>
                        Kirim Pendaftaran
                    </button>
                </div>
                </form>
            </div><!-- /tab-daftar -->

            <!-- ── CEK STATUS ── -->
            <div class="tab-pane fade" id="tab-status" role="tabpanel">

                <div class="status-note d-flex align-items-start gap-2 mb-3">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="#1a4699"
                         class="flex-shrink-0 mt-1">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
                    </svg>
                    Masukkan NIK yang digunakan saat mendaftar untuk memantau proses verifikasi dokumen Anda.
                </div>

             <form action="<?= base_url('register/progress') ?>" method="post">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label">Nomor NIK Terdaftar</label>
                    <input type="text" class="form-control" name="anggota_noktp"
                           placeholder="16 digit NIK" maxlength="16" inputmode="numeric" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tanggal Lahir</label>
                    <input type="date" class="form-control" name="anggota_tgllhr" required>
                </div>
                <button type="submit" class="btn btn-navy w-100">Cek Status</button>
            </form>

            </div><!-- /tab-status -->

        </div><!-- /tab-content -->
    </div><!-- /card-body -->
</div><!-- /kta-card -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    <?php if(session()->getFlashdata('success')): ?>
    localStorage.removeItem('activeTab');
    <?php endif; ?>
</script>

<script>
    const form = document.getElementById('form-register');

   form.addEventListener('submit', function(e) {
    let valid = true;
    if (!/^\d{16}$/.test(document.getElementById('nikInput').value.trim())) {
        alert('NIK harus terdiri dari 16 digit.');
        valid = false;
    }
    if (!/^\d+$/.test(document.getElementById('hp').value.trim())) {
        alert('Nomor HP hanya boleh angka.');
        valid = false;
    }
    if (!valid) e.preventDefault();
});
</script>

<script>
    // NIK: numbers-only + live counter
    function onNik(el) {
        el.value = el.value.replace(/\D/g, '');
        const n   = el.value.length;
        const ctr = document.getElementById('nikCtr');
        ctr.textContent = n + '/16';
        ctr.classList.toggle('ok', n === 16);
    }

    // File upload zone feedback
    function onFile(zoneId, nameId, input) {
        if (input.files && input.files[0]) {
            if (input.files[0].size > 3 * 1024 * 1024) {
                alert('Ukuran file maksimal 3MB.');
                input.value = '';
                return;
            }

            const name = input.files[0].name;
            document.getElementById(nameId).textContent =
                name.length > 20 ? name.slice(0, 17) + '…' : name;
            document.getElementById(zoneId).classList.add('filled');
        }
    }

    // Re-apply NIK counter on page reload (flash session)
    window.addEventListener('DOMContentLoaded', () => {
        const nik = document.getElementById('nikInput');
        if (nik && nik.value.length > 0) onNik(nik);
    });
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    // Restore last active tab
    const activeTab = localStorage.getItem("activeTab");
    if (activeTab) {
        const trigger = document.querySelector(
            `[data-bs-target="${activeTab}"]`
        );
        if (trigger) {
            new bootstrap.Tab(trigger).show();
        }
    }

    // Save active tab whenever changed
    document.querySelectorAll('[data-bs-toggle="pill"]').forEach(tab => {
        tab.addEventListener("shown.bs.tab", function (e) {
            localStorage.setItem(
                "activeTab",
                e.target.getAttribute("data-bs-target")
            );
        });
    });
});
</script>

<script>
function turnstileSuccess() {
    const btn = document.getElementById('btnSubmit');
    btn.disabled = false;
    btn.innerHTML = `
        <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor">
            <path d="M9 16.17L4.83 12l-1.41 1.41L9 19 21 7l-1.41-1.41z"/>
        </svg>
        Kirim Pendaftaran
    `;
}
</script>

</body>
</html>