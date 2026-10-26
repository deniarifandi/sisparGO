<?php
$isEdit = !empty($titpar);

$uploadFields = [
    'kesanggupan'  => 'Surat Kesanggupan',
    'keberatan'    => 'Surat Keberatan',
    'denah'        => 'Denah Lokasi',
   
    'berita_acara' => 'Berita Acara',
    'penunjukan'   => 'Surat Penunjukan',
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
            <a href="'.base_url('uploads/'.$folder.'/'.$file).'" target="_blank" class="btn btn-sm btn-link text-decoration-none p-0 text-primary small text-small fw-semibold">
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
    <title><?= $isEdit ? 'Ubah' : 'Tambah' ?> Titik Parkir</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('css/pendaftaran.css') ?>">

    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">


    <style>
        @media (max-width: 576px) {
        /* Mengurangi padding card utama agar area isi lebih luas di HP */
        .card-body {
            padding: 1rem !important; 
        }
        /* Memastikan tombol aksi (Batal & Simpan) menjadi penuh/full-width di HP */
        .d-flex.justify-content-end {
            flex-direction: column-reverse;
            gap: 10px !important;
        }
        .d-flex.justify-content-end .btn {
            width: 100%;
        }
    }
    </style>

</head>
<body>

<div class="kta-card card">

    <div class="kta-header">

        <div class="d-flex align-items-center gap-3 position-relative">

            <div class="hdr-shield">
                <svg width="27" height="27" viewBox="0 0 24 24" fill="none">
                    <path d="M12 2L3 6v6c0 5.25 3.75 10.15 9 11.4C17.25 22.15 21 17.25 21 12V6L12 2z"
                          fill="rgba(255,255,255,.9)"/>
                    <path d="M9 12l2 2 4-4"
                          stroke="#0d47a1"
                          stroke-width="2.2"
                          stroke-linecap="round"
                          stroke-linejoin="round"/>
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

    <!-- Flash: success -->
<?php if(session()->getFlashdata('success')): ?>
<div class="kta-alert kta-alert-success mb-3">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
    </svg>
    <span><?= session()->getFlashdata('success') ?></span>
</div>
<?php endif; ?>

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

    <div class="card-body p-4">

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
            <ul><?php echo session()->getFlashdata('error') ?></ul>
            <?php endif; ?>
            </span>
        </div>
        <?php endif; ?>

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
                <li>Maksimal ukuran file sesuai ketentuan server.</li>
            </ul>
            </small>

        </div>

        <form
            action="<?= $isEdit
                ? base_url('register/titpar/update/'.$titpar['id'])
                : base_url('register/titpar/save') ?>"
            method="post"
            enctype="multipart/form-data">

            <?= csrf_field() ?>

            <input
                type="hidden"
                name="anggota_noktp"
                value="<?= esc($user['anggota_noktp']) ?>">

            <?php if($isEdit): ?>

                <input
                    type="hidden"
                    name="id"
                    value="<?= $titpar['id'] ?>">

            <?php endif; ?>

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-header bg-light fw-semibold">
                    1. Data Lokasi
                </div>

                <div class="card-body">

                    <div class="mb-3">

                        <label class="form-label">
                            Nama Titik Parkir
                        </label>

                        <input
                            type="text"
                            name="titpar_namatempat"
                            class="form-control"
                            value="<?= esc($titpar['titpar_namatempat'] ?? '') ?>"
                            required>

                    </div>

                    <div>

                        <label class="form-label">
                            Alamat / Lokasi
                        </label>

                        <textarea
                            class="form-control"
                            rows="4"
                            name="titpar_lokasi"
                            required><?= esc($titpar['titpar_lokasi'] ?? '') ?></textarea>

                    </div>

                    <div class="mt-2">

                        <label class="form-label">
                            Batas Parkir
                        </label>

                        <textarea
                            class="form-control"
                            rows="4"
                            name="batas_parkir"
                            required><?= esc($titpar['batas_parkir'] ?? '') ?></textarea>

                    </div>

                      <div class="mt-2">

                        <label class="form-label">
                            Jam Operasional
                        </label>

                        <textarea
                            class="form-control"
                            rows="4"
                            name="jam_operasional"
                            required><?= esc($titpar['jam_operasional'] ?? '') ?></textarea>

                    </div>

                <?php
                $jenis = (int) ($titpar['jenis_kendaraan'] ?? 0);
                ?>

                <div class="mt-2">
                    <label class="form-label d-block">Jenis Kendaraan Parkir</label>

                    <div class="form-check">
                        <input class="form-check-input"
                               type="checkbox"
                               id="motor"
                               name="jenis_kendaraan[]"
                               value="motor"
                               <?= ($jenis & 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="motor">Motor</label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input"
                               type="checkbox"
                               id="mobil"
                               name="jenis_kendaraan[]"
                               value="mobil"
                               <?= ($jenis & 2) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="mobil">Mobil</label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input"
                               type="checkbox"
                               id="truk"
                               name="jenis_kendaraan[]"
                               value="truk"
                               <?= ($jenis & 4) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="truk">Truk</label>
                    </div>
                </div>

                <div class="mt-3">
    <label class="form-label">Lokasi Titik Parkir</label>
    <div class="alert alert-warning py-2 mt-3">
    <small>
        Izinkan akses lokasi. Geser pin atau klik pada peta untuk menentukan lokasi parkir.
    </small>

    </div>
        <div id="map" class="rounded border shadow-sm" style="height:400px;"></div>
    </div>
    <button type="button"
        class="btn btn-outline-primary btn-sm mt-2"
        id="btnMyLocation">
        📍 Gunakan Lokasi Saya
    </button>

<div class="row mt-3">
    <div class="col-md-6">
        <label class="form-label">Latitude</label>
        <input type="text"
               class="form-control"
               id="latitude"
               name="latitude"
               value="<?= esc($titpar['latitude'] ?? '') ?>"
               readonly>
    </div>

    <div class="col-md-6">
        <label class="form-label">Longitude</label>
        <input type="text"
               class="form-control"
               id="longitude"
               name="longitude"
               value="<?= esc($titpar['longitude'] ?? '') ?>"
               readonly>
    </div>
</div>

                </div>

            </div>

            <div class="card border border-light-subtle shadow-sm mb-4 rounded-3">
                <div class="card-header bg-light fw-semibold text-secondary d-flex justify-content-between align-items-center py-3">
                    <span class="text-dark">2. Dokumen Pendukung</span>
                    <span class="badge bg-secondary-subtle text-secondary small fw-normal">Format: PDF/Gambar</span>
                </div>
                <div class="card-body p-3">
                    <div class="row g-3">
                        <?php foreach($uploadFields as $field => $label): 
                            $hasFile = ($isEdit && !empty($titpar[$field]));
                        ?>
                            <div class="col-md-6">
                                <div class="p-3 border rounded-3 h-100 <?= $hasFile ? 'border-success-subtle bg-success-subtle bg-opacity-10' : 'border-light-subtle' ?>">
                                    
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <label class="form-label fw-semibold text-dark mb-0">
                                            <?= $label ?>
                                        </label>
                                        <?php if($hasFile): ?>
                                            <span class="badge bg-success text-white px-2 py-1 rounded-pill" style="font-size: 10px;">
                                                ✓ Sudah Ada
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark px-2 py-1 rounded-pill" style="font-size: 10px;">
                                                ⚠ Belum Diunggah
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <?php if($hasFile): ?>
                                        <?= showFileLink($titpar[$field], $field) ?>
                                    <?php endif; ?>

                                    <div class="input-group input-group-sm">
                                        <input type="file" name="<?= $field ?>" class="form-control auto-upload" accept=".pdf,.jpg,.jpeg,.png">
                                    </div>
                                    <div class="form-text text-muted style-opacity" style="font-size: 11px;">
                                        <?= $hasFile ? 'Pilih berkas baru jika ingin mengubah berkas saat ini.' : 'Pilih berkas untuk mengunggah.' ?>
                                    </div>

                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-4">

                <a href="<?= base_url('register/progress') ?>"
                   class="btn btn-outline-secondary">
                    Batal
                </a>

                <button type="submit" class="btn btn-success">

                    <?= $isEdit
                        ? 'Perbarui Titik Parkir'
                        : 'Simpan Titik Parkir' ?>

                </button>

            </div>

        </form>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
const fallbackLat = -7.981894;
const fallbackLng = 112.626503;

const map = L.map('map').setView([fallbackLat, fallbackLng], 15);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

let marker = L.marker([fallbackLat, fallbackLng], {
    draggable: true
}).addTo(map);

function updateLocation(lat, lng, center = false) {
    const pos = L.latLng(lat, lng);
    marker.setLatLng(pos);
    if (center) map.flyTo(pos, 16);
    document.getElementById('latitude').value  = lat.toFixed(8);
    document.getElementById('longitude').value = lng.toFixed(8);
}

// ─── Fungsi geolocation terpusat (reusable) ───────────────────────────────
function getMyLocation(onSuccess, onError) {
    if (!navigator.geolocation) {
        onError('Browser Anda tidak mendukung geolokasi.');
        return;
    }
    navigator.geolocation.getCurrentPosition(
        function(pos) {
            onSuccess(pos.coords.latitude, pos.coords.longitude);
        },
        function(err) {
    let msg;
    if (err.code === 1) {
        const isSecure = location.protocol === 'https:' || location.hostname === 'localhost' || location.hostname === '127.0.0.1';
        msg = isSecure
            ? 'Akses lokasi ditolak. Izinkan akses lokasi di pengaturan browser Anda.'
            : '⚠️ Geolokasi memerlukan HTTPS. Akses via localhost atau gunakan HTTPS.';
    } else if (err.code === 2) {
        msg = 'Informasi lokasi tidak tersedia.';
    } else if (err.code === 3) {
        msg = 'Permintaan lokasi habis waktu (timeout).';
    } else {
        msg = 'Gagal mendapatkan lokasi.';
    }
    onError(msg);
},
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 } // ← posisi benar
    );
}

// ─── Inisialisasi awal ────────────────────────────────────────────────────
<?php if (!empty($titpar['latitude']) && !empty($titpar['longitude'])): ?>
    // Mode edit: gunakan koordinat yang tersimpan
    updateLocation(<?= $titpar['latitude'] ?>, <?= $titpar['longitude'] ?>);
<?php else: ?>
    // Mode tambah: coba ambil lokasi user otomatis
    getMyLocation(
        function(lat, lng) { updateLocation(lat, lng, true); },
        function()          { updateLocation(fallbackLat, fallbackLng); }
    );
<?php endif; ?>

// ─── Klik peta ────────────────────────────────────────────────────────────
map.on('click', function(e) {
    updateLocation(e.latlng.lat, e.latlng.lng);
});

// ─── Drag marker ─────────────────────────────────────────────────────────
marker.on('dragend', function(e) {
    const pos = e.target.getLatLng();
    updateLocation(pos.lat, pos.lng);
});

// ─── Tombol "Gunakan Lokasi Saya" ─────────────────────────────────────────
document.getElementById('btnMyLocation').addEventListener('click', function () {
    const btn = this;
    btn.disabled    = true;
    btn.textContent = '⏳ Mendeteksi lokasi...';

    getMyLocation(
        function(lat, lng) {
            updateLocation(lat, lng, true);
            btn.disabled    = false;
            btn.textContent = '📍 Gunakan Lokasi Saya';
        },
        function(msg) {
            btn.disabled    = false;
            btn.textContent = '📍 Gunakan Lokasi Saya';
            alert('⚠️ ' + msg);
        }
    );
});

// ─── Validasi koordinat sebelum submit ────────────────────────────────────
document.querySelector('form').addEventListener('submit', function(e) {
    const lat = document.getElementById('latitude').value.trim();
    const lng = document.getElementById('longitude').value.trim();

    if (!lat || !lng) {
        e.preventDefault();
        alert('⚠️ Harap tentukan lokasi parkir pada peta terlebih dahulu.');
        document.getElementById('map').scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});
</script>

</body>
</html>