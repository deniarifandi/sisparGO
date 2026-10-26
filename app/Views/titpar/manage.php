<?php echo view('header'); ?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<style>
    #titparMapThumbnail {
        height: 130px;
        width: 100%;
        border-radius: 6px;
        border: 1px solid #dee2e6;
        overflow: hidden;
    }
</style>

<div class="container-fluid">

    <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <?= session()->getFlashdata('error') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>


<!-- HEADER TITPAR -->
<?php
$klrhnId = $titpar['titpar_klrhnid'] ?? '';
$namaKecamatan = $titpar['kecamatan_nama'] ?? '-';
$namaKelurahan = $titpar['kelurahan_nama'] ?? ($klrhnId ?: '-');
$namaZona = ($titpar['kabupaten_kota'] ?? '') === 'Kota Bogor' ? $namaKecamatan : '-';
$jenisTitparRaw = $titpar['retrytipe_nama'] ?? '';
$jenisTitpar = stripos($jenisTitparRaw, 'TJU') !== false || stripos($jenisTitparRaw, 'tepi jalan') !== false
    ? 'TJU'
    : (stripos($jenisTitparRaw, 'TKP') !== false || stripos($jenisTitparRaw, 'tempat khusus') !== false ? 'TKP' : ($jenisTitparRaw ?: ($titpar['titpar_retritypeid'] ?? '-')));
$jenisKendaraan = $titpar['titpar_jenis_kendaraan'] ?? [];
if (is_string($jenisKendaraan)) {
    $jenisKendaraan = json_decode($jenisKendaraan, true) ?: [];
}
$jenisKendaraan = is_array($jenisKendaraan) ? $jenisKendaraan : [];
$labelJenisKendaraan = ['motor' => 'Motor', 'mobil' => 'Mobil', 'truk' => 'Truk'];

$formatTanggal = static function ($date) {
    if (empty($date)) return '-';
    $bulan = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
    $time = strtotime($date);
    return date('j', $time) . ' ' . $bulan[(int) date('n', $time)] . ' ' . date('Y', $time);
};
$masaEnd = $masaBerlaku['titpargrup_masaberlakuend'] ?? null;
$daysLeft = $masaEnd ? (int) floor((strtotime($masaEnd) - strtotime(date('Y-m-d'))) / 86400) : null;
$masaStatus = $daysLeft === null ? ['label' => 'Belum diatur', 'class' => 'secondary', 'icon' => 'question-circle']
    : ($daysLeft < 0 ? ['label' => 'Expired', 'class' => 'danger', 'icon' => 'x-circle-fill']
    : ($daysLeft <= 30 ? ['label' => 'Mendekati akhir', 'class' => 'warning', 'icon' => 'exclamation-triangle-fill']
    : ['label' => 'Aman', 'class' => 'success', 'icon' => 'check-circle-fill']));
$statusMasaPerOrang = static function ($start, $end) {
    if (empty($start) || empty($end)) return ['class' => 'danger', 'text' => 'text-danger'];
    $days = (int) floor((strtotime($end) - strtotime(date('Y-m-d'))) / 86400);
    return $days <= 0
        ? ['class' => 'danger', 'text' => 'text-danger']
        : ($days <= 30 ? ['class' => 'warning', 'text' => 'text-warning'] : ['class' => 'success', 'text' => 'text-success']);
};
?>

<div class="card shadow-sm mb-4 border-0 rounded-3">
    <div class="card-body p-4">

        <!-- Top Header Navigation & Action -->
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <!-- Badge Status & ID -->
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                        ID: <?= esc($titpar['titpar_id']) ?>
                    </span>

                    <?php if (($titpar['titpar_status'] ?? 0) == 3): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                            <i class="bi bi-check-circle-fill me-1"></i> Aktif
                        </span>
                    <?php else: ?>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                            <i class="bi bi-x-circle-fill me-1"></i> Non-Aktif
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Judul Utama Nama Tempat -->
                <div class="mb-3">
                    <h3 class="fw-bold mb-0 text-dark">
                        <?= esc($titpar['titpar_namatempat']) ?>
                    </h3>
                </div>

                <p class="text-muted mb-0 small">
                    <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                    <?= esc($titpar['titpar_lokasi']) ?>
                </p>
            </div>

            <!-- Tombol Navigasi / Aksi Header -->
            <div class="d-flex gap-2">
                <a href="<?= base_url('titpar') ?>" class="btn btn-outline-secondary btn-sm px-3 rounded-pill">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>

                <a href="<?= base_url('titpar/edit/' . $titpar['titpar_id']) ?>" class="btn btn-outline-primary btn-sm px-3 rounded-pill">
                    <i class="bi bi-pencil-square me-1"></i> Edit Titik
                </a>

                <a href="<?= base_url('titpar/print-penunjukan/' . $titpar['titpar_id']) ?>"
                   target="_blank" class="btn btn-outline-dark btn-sm px-3 rounded-pill">
                    <i class="bi bi-printer me-1"></i> Surat Penunjukan
                </a>

                <button type="button" class="btn btn-outline-primary btn-sm px-3 rounded-pill"
                        data-bs-toggle="modal" data-bs-target="#printKtaModal">
                    <i class="bi bi-person-badge me-1"></i> Cetak KTA
                </button>

                <button type="button" class="btn btn-outline-success btn-sm px-3 rounded-pill"
                        data-bs-toggle="modal" data-bs-target="#masaBerlakuModal">
                    <i class="bi bi-calendar-check me-1"></i> Masa Berlaku KTA
                </button>

                <?php if (isset($koordinator) && count($koordinator) < 4): ?>
                    <button type="button" 
                            class="btn btn-primary btn-sm px-3 rounded-pill"
                            data-bs-toggle="modal"
                            data-bs-target="#addKoordinatorModal">
                        <i class="bi bi-person-plus-fill me-1"></i> Tambah Koordinator
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <hr class="my-3 text-secondary opacity-25">

        <div class="border rounded-3 px-3 py-2 mb-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex flex-wrap align-items-center gap-2 small">
                <span class="fw-semibold"><i class="bi bi-file-earmark-check me-1"></i> Surat Penunjukan TTD</span>
                <span class="badge <?= empty($titpar['titpar_surat_penunjukan']) ? 'bg-secondary' : 'bg-success' ?>"><?= empty($titpar['titpar_surat_penunjukan']) ? 'Belum diunggah' : 'Sudah diunggah' ?></span>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <?php if (!empty($titpar['titpar_surat_penunjukan'])): ?>
                        <a class="btn btn-outline-primary btn-sm" href="<?= base_url('titpar/surat-penunjukan/' . $titpar['titpar_id']) ?>" target="_blank" rel="noopener">Lihat / Cetak</a>
                        <a class="btn btn-outline-secondary btn-sm" href="<?= base_url('titpar/surat-penunjukan/' . $titpar['titpar_id'] . '?download=1') ?>">Unduh</a>
                <?php endif; ?>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadSuratPenunjukanModal"><i class="bi bi-upload me-1"></i><?= empty($titpar['titpar_surat_penunjukan']) ? 'Unggah' : 'Ganti Surat' ?></button>
            </div>
        </div>

        <!-- Grid Detail Informasi Titik Parkir -->
        <div class="row g-3 text-secondary small">

            <div class="col-md-3 col-6">
                <div class="p-2 rounded bg-light-subtle border h-100">
                    <span class="d-block text-muted" style="font-size: 11px;">NOMOR REGISTRASI</span>
                    <strong class="text-dark"><i class="bi bi-upc-scan me-1"></i><?= esc($titpar['titpar_newnoreg'] ?: ($titpar['titpar_noreg'] ?? '-')) ?></strong>
                </div>
            </div>

            <div class="col-md-3 col-6">
                <div class="p-2 rounded bg-light-subtle border h-100">
                    <span class="d-block text-muted" style="font-size: 11px;">ZONA</span>
                    <strong class="text-dark"><i class="bi bi-grid-3x3-gap me-1"></i><?= esc($namaZona) ?></strong>
                </div>
            </div>
            
            <!-- Wilayah / Alamat Lengkap -->
            <div class="col-md-3 col-6">
                <div class="p-2 rounded bg-light-subtle border">
                    <span class="d-block text-muted" style="font-size: 11px;">KECAMATAN</span>
                    <strong class="text-dark"><i class="bi bi-map me-1"></i><?= esc($namaKecamatan) ?></strong>
                </div>
            </div>

            <div class="col-md-3 col-6">
                <div class="p-2 rounded bg-light-subtle border">
                    <span class="d-block text-muted" style="font-size: 11px;">KELURAHAN</span>
                    <strong class="text-dark"><i class="bi bi-building me-1"></i><?= esc($namaKelurahan) ?></strong>
                </div>
            </div>

            <!-- Tipe Parkir & Retribusi -->
           <!--  <div class="col-md-3 col-6">
                <div class="p-2 rounded bg-light-subtle border">
                    <span class="d-block text-muted" style="font-size: 11px;">TIPE PARKIR / SETOR</span>
                    <strong class="text-dark">
                        Tipe <?= esc($titpar['titpar_partypeid'] ?? '-') ?> / Setor <?= esc($titpar['titpar_setortypeid'] ?? '-') ?>
                    </strong>
                </div>
            </div> -->

            <!-- Koordinat Lokasi -->
            <div class="col-lg-4 col-12 order-lg-1">
                <div class="p-3 rounded-3 bg-light-subtle border h-100">
                    <span class="d-block text-muted fw-semibold mb-2" style="font-size: 11px;">KOORDINAT (LAT, LONG)</span>
                    <?php if (!empty($titpar['titpar_latitude']) && !empty($titpar['titpar_longitude'])): ?>
                        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                            <strong class="text-dark"><i class="bi bi-pin-map-fill text-danger me-1"></i><?= esc($titpar['titpar_latitude']) ?>, <?= esc($titpar['titpar_longitude']) ?></strong>
                            <a href="https://maps.google.com/?q=<?= rawurlencode($titpar['titpar_latitude'] . ',' . $titpar['titpar_longitude']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-box-arrow-up-right me-1"></i>Buka Google Maps
                            </a>
                        </div>
                        <div id="titparMapThumbnail" class="mt-3" aria-label="Peta lokasi titik parkir"></div>
                    <?php else: ?>
                        <span class="text-muted fst-italic">Belum diatur</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Jenis Kendaraan -->
            <div class="col-lg-4 col-12 order-lg-3">
                <div class="p-3 rounded-3 bg-light-subtle border h-100">
                    <span class="d-block text-muted fw-semibold mb-3" style="font-size: 11px;">JENIS TITIK &amp; KENDARAAN</span>
                    <div class="mb-3">
                        <span class="d-block text-muted" style="font-size: 11px;">Jenis Titik Parkir</span>
                        <strong class="text-dark"><?= esc($jenisTitpar) ?></strong>
                    </div>
                    <span class="d-block text-muted" style="font-size: 11px;">Jenis Kendaraan</span>
                    <?php if ($jenisKendaraan): ?>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($jenisKendaraan as $jenis): ?>
                                <?php if (isset($labelJenisKendaraan[$jenis])): ?>
                                    <span class="badge text-bg-primary px-3 py-2"><?= esc($labelJenisKendaraan[$jenis]) ?></span>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <span class="text-muted fst-italic">Belum diatur</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-4 col-12 order-lg-2">
                <div class="p-3 rounded-3 bg-light-subtle border h-100">
                    <span class="d-block text-muted mb-2" style="font-size: 11px;">DENAH</span>
                    <?php if (!empty($denah)): ?>
                        <a href="<?= base_url(($denahPath ?? 'uploads/denah') . '/' . basename($denah)) ?>" target="_blank" rel="noopener noreferrer">
                            <img src="<?= base_url(($denahPath ?? 'uploads/denah') . '/' . basename($denah)) ?>"
                                 alt="Layout Tempat Parkir yang Diijinkan"
                                 class="img-fluid rounded border"
                                 style="width: 100%; max-height: 220px; object-fit: contain; background: #fff;">
                        </a>
                    <?php else: ?>
                        <div class="d-flex align-items-center justify-content-center text-muted border rounded bg-white"
                             style="min-height: 150px;">
                            <span><i class="bi bi-image me-1"></i>Denah belum diunggah</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div> <!-- End Grid Detail -->

    </div>
</div>

<?php if (!empty($titpar['titpar_latitude']) && !empty($titpar['titpar_longitude'])): ?>
<script>
    (() => {
        const lat = <?= json_encode((float) $titpar['titpar_latitude']) ?>;
        const lng = <?= json_encode((float) $titpar['titpar_longitude']) ?>;
        const map = L.map('titparMapThumbnail', {
            zoomControl: false,
            attributionControl: true,
            dragging: false,
            scrollWheelZoom: false,
            doubleClickZoom: false,
            touchZoom: false,
            boxZoom: false,
            keyboard: false
        }).setView([lat, lng], 16);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        L.marker([lat, lng]).addTo(map);
    })();
</script>
<?php endif; ?>


    <!-- KOORDINATOR -->
    <div class="row g-4">

        <?php if (empty($koordinator)): ?>

            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">

                        <i class="bi bi-people fs-1 text-muted"></i>

                        <h5 class="mt-3">
                            Belum ada koordinator
                        </h5>

                        <p class="text-muted">
                            Tambahkan koordinator untuk mulai mengatur anggota
                            pada titik parkir ini.
                        </p>

                        <button class="btn btn-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#addKoordinatorModal">
                        <i class="bi bi-person-plus"></i>
                        Tambah Koordinator
                    </button>

                </div>
            </div>
        </div>

    <?php else: ?>

       <?php 
$hariMap = [
    1  => 'Senin',  2  => 'Selasa', 4  => 'Rabu',
    8  => 'Kamis', 16 => 'Jumat',  32 => 'Sabtu', 64 => 'Minggu'
];
?>

<?php foreach ($koordinator as $index => $k): ?>
    <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
        <div class="card shadow-sm h-100 border-0 rounded-3">

            <!-- KOORDINATOR HEADER -->
            <div class="card-header bg-primary text-white py-3 rounded-top-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-uppercase tracking-wider small opacity-75 fw-semibold" style="font-size: 11px;">
                            Koordinator <?= $index + 1 ?>
                        </div>
                        <h6 class="mb-0 fw-bold text-truncate" style="max-width: 150px;" title="<?= esc($k['anggota_nama']) ?>">
                            <?= esc($k['anggota_nama']) ?>
                        </h6>
                    </div>

                    <div class="d-flex align-items-center gap-1">
                        <span class="badge bg-light text-primary rounded-pill px-2 me-1">
                            <?= count($k['anggota']) ?> Anggota
                        </span>
                        
                        <button type="button" 
                                class="btn btn-sm btn-light text-primary rounded-circle p-1 d-flex align-items-center justify-content-center" 
                                style="width: 28px; height: 28px;"
                                title="Ganti Koordinator"
                                onclick="replaceCoordinator(<?= $k['titpargrup_id'] ?>)">
                            <i class="bi bi-person-gear"></i>
                        </button>

                        <button type="button" class="btn btn-sm btn-light text-success rounded-circle p-1 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;" title="Atur masa berlaku koordinator"
                                onclick="openEditMasaAnggotaModal(<?= $k['titpargrup_id'] ?>, '<?= esc($k['titpargrup_masaberlakustart'] ?? '') ?>', '<?= esc($k['titpargrup_masaberlakuend'] ?? '') ?>', '<?= esc($k['anggota_nama'] ?? 'Koordinator') ?>', true)">
                            <i class="bi bi-calendar-check"></i>
                        </button>
                        
                        <button type="button" 
                                class="btn btn-sm btn-light text-danger rounded-circle p-1 d-flex align-items-center justify-content-center" 
                                style="width: 28px; height: 28px;"
                                title="Hapus Koordinator"
                                onclick="removeKoordinator(<?= $k['titpargrup_id'] ?>)">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- CARD BODY -->
            <div class="card-body d-flex flex-column p-3">
                
                <!-- KOORDINATOR INFO -->
                <div class="small text-muted mb-3 pb-2 border-bottom">
                    <?php $statusKoordinator = $statusMasaPerOrang($k['titpargrup_masaberlakustart'] ?? null, $k['titpargrup_masaberlakuend'] ?? null); ?>
                    <div class="d-flex align-items-start gap-2 mb-2 p-2 rounded bg-<?= $statusKoordinator['class'] ?>-subtle <?= $statusKoordinator['text'] ?>">
                        <i class="bi bi-calendar-check mt-1"></i>
                        <div>
                            <div class="fw-bold" style="font-size: 11px;">Masa berlaku koordinator</div>
                            <div style="font-size: 11px;"><?= esc($formatTanggal($k['titpargrup_masaberlakustart'] ?? null)) ?> <span class="mx-1">s/d</span> <?= esc($formatTanggal($k['titpargrup_masaberlakuend'] ?? null)) ?></div>
                        </div>
                    </div>
                    <?php if (!empty($k['titpargrup_anggotaid'])): ?>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="bi bi-person-badge text-primary"></i>
                            <span><?= esc($k['titpargrup_anggotaid']) ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($k['anggota_nohp'])): ?>
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-phone text-success"></i>
                            <span><?= esc($k['anggota_nohp']) ?></span>
                        </div>
                    <?php endif; ?>
                  <?php if (!empty($k['va_owner_va'])): ?>
    <div class="d-flex align-items-center gap-2 text-primary fw-bold">
        <i class="bi bi-credit-card-2-front text-primary fw-bold"></i>
        <span>VA: <?= esc($k['va_owner_va']) ?></span>
    </div>
<?php endif; ?>
                </div>

                <!-- JADWAL SHIFT -->
                <div class="border rounded-3 p-2 mb-3 bg-light-subtle">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-bold text-dark">
                            <i class="bi bi-clock me-1 text-primary"></i>Jadwal
                        </span>
                        <button type="button" 
                                class="btn btn-xs btn-outline-primary py-0 px-2 rounded-pill" 
                                style="font-size: 11px;"
                                onclick="openEditShiftModal(
                                    <?= $k['titpargrup_id'] ?>,
                                    <?= (int) $k['titpargrup_shifthari'] ?>,
                                    '<?= esc($k['titpargrup_shiftjammulai'] ?? '') ?>',
                                    '<?= esc($k['titpargrup_shiftjamselesai'] ?? '') ?>'
                                )">
                            <i class="bi bi-pencil me-1"></i>Edit
                        </button>
                    </div>

                    <div class="small text-secondary" style="font-size: 12px;">
                        <?php if (!empty($k['titpargrup_shifthari'])): ?>
                            <?php
                            $hariAktif = [];
                            foreach ($hariMap as $bit => $namaHari) {
                                if (((int)$k['titpargrup_shifthari'] & $bit) === $bit) {
                                    $hariAktif[] = $namaHari;
                                }
                            }
                            ?>
                            <div class="fw-semibold text-dark"><?= esc(implode(', ', $hariAktif)) ?></div>
                            
                            <?php if (!empty($k['titpargrup_shiftjammulai']) && !empty($k['titpargrup_shiftjamselesai'])): ?>
                                <div class="text-muted">
                                    <i class="bi bi-alarm me-1"></i><?= esc($k['titpargrup_shiftjammulai']) ?> - <?= esc($k['titpargrup_shiftjamselesai']) ?>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="fst-italic text-muted">Jadwal belum diatur</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- DAFTAR ANGGOTA HEADER -->
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-bold text-dark">Daftar Anggota</span>
                    <button type="button" 
                            class="btn btn-sm btn-outline-primary py-0 px-2 rounded-pill"
                            style="font-size: 11px;"
                            onclick="openAddAnggotaModal(<?= $k['titpargrup_anggotaid'] ?>)">
                        <i class="bi bi-person-plus me-1"></i>Tambah
                    </button>
                </div>

                <!-- LIST ANGGOTA CONTENT -->
                <div class="flex-grow-1">
                    <?php if (empty($k['anggota'])): ?>
                        <div class="text-center text-muted py-4 small border rounded-3 bg-light">
                            <i class="bi bi-people fs-4 d-block mb-1 opacity-50"></i>
                            Belum ada anggota
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush rounded-3 border">
                            <?php foreach ($k['anggota'] as $anggota): ?>
                                <div class="list-group-item p-2 d-flex justify-content-between align-items-center">
                                    <div class="overflow-hidden me-2">
                                        <div class="fw-semibold text-dark text-truncate" style="font-size: 12px;">
                                            <?= esc($anggota['anggota_nama']) ?>
                                        </div>
                                        <?php $statusAnggota = $statusMasaPerOrang($anggota['titpargrup_masaberlakustart'] ?? null, $anggota['titpargrup_masaberlakuend'] ?? null); ?>
                                        <div class="<?= $statusAnggota['text'] ?>" style="font-size: 10px;"><i class="bi bi-calendar-check me-1"></i><?= esc($formatTanggal($anggota['titpargrup_masaberlakustart'] ?? null)) ?> <span class="mx-1">s/d</span> <?= esc($formatTanggal($anggota['titpargrup_masaberlakuend'] ?? null)) ?></div>
                                            <div class="text-muted" style="font-size: 10px;">
    <?php if (!empty($anggota['anggota_id'])): ?>
        <div>ID: <?= esc($anggota['anggota_id']) ?></div>
    <?php endif; ?>

    <?php if (!empty($anggota['va_owner_va'])): ?>
        <?php if (empty($anggota['va_deleted'])): ?>
            <div class="text-primary fw-bold">VA: <?= esc($anggota['va_owner_va']) ?></div>
        <?php endif; ?>
    <?php endif; ?>
</div>
                                    </div>

                                    <div class="d-flex gap-1 flex-shrink-0">
                                        <button type="button" 
                                                class="btn btn-xs btn-outline-secondary p-1" 
                                                title="Pindah Koordinator"
                                                onclick="changeCoordinator(<?= $anggota['titpargrup_id'] ?>)">
                                            <i class="bi bi-arrow-left-right"></i>
                                        </button>
                                        <button type="button" class="btn btn-xs btn-outline-success p-1" title="Atur masa berlaku"
                                                onclick="openEditMasaAnggotaModal(<?= $anggota['titpargrup_id'] ?>, '<?= esc($anggota['titpargrup_masaberlakustart'] ?? '') ?>', '<?= esc($anggota['titpargrup_masaberlakuend'] ?? '') ?>', '<?= esc($anggota['anggota_nama'] ?? 'Anggota') ?>', false)">
                                            <i class="bi bi-calendar-check"></i>
                                        </button>
                                        <button type="button" 
                                                class="btn btn-xs btn-outline-danger p-1" 
                                                title="Hapus dari Titpar"
                                                onclick="removeAnggota(<?= $anggota['titpargrup_id'] ?>)">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div> <!-- END CARD BODY -->

        </div>
    </div>
<?php endforeach; ?>


    <!-- SLOT KOORDINATOR KOSONG -->
    <?php if (count($koordinator) < 4): ?>

        <div class="col-xl-3 col-lg-4 col-md-6">

            <div class="card border-2 border-dashed h-100"
            style="min-height: 300px;">

            <div class="card-body d-flex flex-column
            justify-content-center align-items-center
            text-center">

            <i class="bi bi-person-plus fs-1 text-muted"></i>

            <h6 class="mt-3">
                Slot Koordinator <?= count($koordinator) + 1 ?>
            </h6>

            <p class="text-muted small">
                Slot koordinator masih tersedia.
            </p>

            <button class="btn btn-outline-primary btn-sm"
            data-bs-toggle="modal"
            data-bs-target="#addKoordinatorModal">

            Tambah Koordinator

        </button>

    </div>

</div>

</div>

<?php endif; ?>

<?php endif; ?>

</div>

<?php if (!empty($anggotaUnassigned)): ?>

    <div class="col-12 mt-4">

        <div class="card shadow-sm border-warning">

            <div class="card-header bg-warning">

                <div class="d-flex justify-content-between align-items-center">

                    <strong>
                        <i class="bi bi-exclamation-triangle"></i>
                        Anggota Belum Ditentukan Koordinator
                    </strong>

                    <span class="badge bg-dark">
                        <?= count($anggotaUnassigned) ?> Anggota
                    </span>

                </div>

            </div>

            <div class="card-body">

                <div class="row g-2">

                    <?php foreach ($anggotaUnassigned as $anggota): ?>

                        <div class="col-md-6 col-lg-4">

                            <div class="border rounded p-2">

                                <div class="d-flex justify-content-between align-items-center">

                                   <div>
    <div class="fw-semibold">
        <?= esc($anggota['anggota_nama']) ?>
    </div>

    <div class="text-muted small">
        <?php if (!empty($anggota['anggota_id'])): ?>
            <div>ID: <?= esc($anggota['anggota_id']) ?></div>
        <?php endif; ?>

        <?php if (!empty($anggota['va_owner_va'])): ?>
            <div class="text-danger fw-bold">VA: <?= esc($anggota['va_owner_va']) ?></div>
        <?php endif; ?>
    </div>
</div>

                                    <div class="d-flex gap-1">

    <!-- Assign ke Koordinator -->
    <button
        type="button"
        class="btn btn-sm btn-primary"
        onclick="assignExistingAnggota(
            <?= $anggota['titpargrup_id'] ?>
        )"
        title="Tentukan koordinator">

        <i class="bi bi-person-check"></i>
        Tentukan

    </button>

    <!-- Jadikan Koordinator -->
    <?php if (count($koordinator) < 4): ?>

        <button
            type="button"
            class="btn btn-sm btn-success"
            onclick="makeExistingKoordinator(
                <?= $anggota['titpargrup_id'] ?>
            )"
            title="Jadikan koordinator">

            <i class="bi bi-person-badge"></i>
            Jadikan Koordinator

        </button>

    <?php endif; ?>

</div>

                                </div>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            </div>

        </div>

    </div>

<?php endif; ?>

</div>


<!-- ========================================================= -->
<!-- MODAL TAMBAH KOORDINATOR -->
<!-- ========================================================= -->

<div class="modal fade"
id="addKoordinatorModal"
tabindex="-1">

<div class="modal-dialog modal-dialog-centered">

    <div class="modal-content">

        <form method="post"
        action="<?= base_url(
          'titpar/add-koordinator/' . $titpar['titpar_id']
          ) ?>">

          <?= csrf_field() ?>

          <div class="modal-header">

            <h5 class="modal-title">
                Tambah Koordinator
            </h5>

            <button type="button"
            class="btn-close"
            data-bs-dismiss="modal"></button>

        </div>


        <div class="modal-body">

            <label class="form-label">
                Pilih Anggota
            </label>

            <select name="anggota_id"
            id="koordinatorSelect"
            class="form-select"
            style="width: 100%;"
            required>
        </select>

        <div class="form-text">
            Anggota yang dipilih akan menjadi koordinator
            untuk titik parkir ini.
        </div>

    </div>


    <div class="modal-footer">

        <button type="button"
        class="btn btn-secondary"
        data-bs-dismiss="modal">

        Batal

    </button>

    <button type="submit"
    class="btn btn-primary">

    Simpan

</button>

</div>

</form>

</div>

</div>

</div>


<!-- ========================================================= -->
<!-- MODAL TAMBAH ANGGOTA -->
<!-- ========================================================= -->

<div class="modal fade"
id="addAnggotaModal"
tabindex="-1">

<div class="modal-dialog modal-dialog-centered">

    <div class="modal-content">

        <form method="post"
        id="addAnggotaForm">

        <?= csrf_field() ?>

        <div class="modal-header">

            <h5 class="modal-title">
                Tambah Anggota
            </h5>

            <button type="button"
            class="btn-close"
            data-bs-dismiss="modal"></button>

        </div>


        <div class="modal-body">

            <input type="hidden"
            name="koordinator_id"
            id="selectedKoordinator">


            <label class="form-label">
                Pilih Anggota
            </label>

            <select name="anggota_id"
            id="anggotaSelect"
            class="form-select"
            style="width: 100%;"
            required>
        </select>

    </div>


    <div class="modal-footer">

        <button type="button"
        class="btn btn-secondary"
        data-bs-dismiss="modal">

        Batal

    </button>

    <button type="submit"
    class="btn btn-primary">

    Tambahkan

</button>

</div>

</form>

</div>

</div>

</div>

<!-- MODAL ASSIGN EXISTING ANGGOTA -->
<div class="modal fade" id="assignAnggotaModal" tabindex="-1">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <form method="post" id="assignAnggotaForm">

                <?= csrf_field() ?>

                <div class="modal-header">

                    <h5 class="modal-title">
                        Tentukan Koordinator
                    </h5>

                    <button type="button"
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                <input type="hidden"
                name="titpargrup_id"
                id="assignTitpargrupId">

                <label class="form-label">
                    Pilih Koordinator
                </label>

                <select name="koordinator_id"
                id="assignKoordinatorSelect"
                class="form-select"
                required>

                <option value="">
                    -- Pilih Koordinator --
                </option>

                <?php foreach ($koordinator as $k): ?>

                    <option value="<?= $k['titpargrup_anggotaid'] ?>">

                        <?= esc($k['anggota_nama']) ?>

                        -
                        <?= esc(
                            $k['titpargrup_anggotaid']
                            ?: $k['titpargrup_anggotaid']
                        ) ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </div>

        <div class="modal-footer">

            <button type="button"
            class="btn btn-secondary"
            data-bs-dismiss="modal">

            Batal

        </button>

        <button type="submit"
        class="btn btn-primary">

        <i class="bi bi-person-check"></i>
        Tetapkan

    </button>

</div>

</form>

</div>

</div>

</div>

<!-- CHANGE COORDINATOR MODAL -->
<div class="modal fade"
id="changeCoordinatorModal"
tabindex="-1">

<div class="modal-dialog modal-dialog-centered">

    <div class="modal-content">

        <form method="post"
        id="changeCoordinatorForm">

        <?= csrf_field() ?>

        <div class="modal-header">

            <h5 class="modal-title">
                Pindah Koordinator
            </h5>

            <button type="button"
            class="btn-close"
            data-bs-dismiss="modal">
        </button>

    </div>

    <div class="modal-body">

        <input type="hidden"
        id="changeTitpargrupId"
        name="titpargrup_id">

        <label class="form-label">
            Koordinator Baru
        </label>

        <select name="koordinator_id"
        id="changeCoordinatorSelect"
        class="form-select"
        required>

        <option value="">
            -- Pilih Koordinator --
        </option>

        <?php foreach ($koordinator as $k): ?>

            <option value="<?= $k['titpargrup_anggotaid'] ?>">

                <?= esc($k['anggota_nama']) ?>

                -
                <?= esc(
                    $k['titpargrup_anggotaid']
                    ?: $k['titpargrup_anggotaid']
                ) ?>

            </option>

        <?php endforeach; ?>

    </select>

</div>

<div class="modal-footer">

    <button type="button"
    class="btn btn-secondary"
    data-bs-dismiss="modal">

    Batal

</button>

<button type="submit"
class="btn btn-primary">

<i class="bi bi-arrow-left-right"></i>
Pindahkan

</button>

</div>

</form>

</div>

</div>

</div>

<!-- REPLACE COORDINATOR MODAL -->
<div class="modal fade"
id="replaceCoordinatorModal"
tabindex="-1">

<div class="modal-dialog modal-dialog-centered">

    <div class="modal-content">

        <form method="post"
        id="replaceCoordinatorForm">

        <?= csrf_field() ?>

        <div class="modal-header">

            <h5 class="modal-title">
                Ganti Koordinator
            </h5>

            <button type="button"
            class="btn-close"
            data-bs-dismiss="modal">
        </button>

    </div>

    <div class="modal-body">

        <input type="hidden"
        name="old_koordinator_id"
        id="oldCoordinatorId">

        <div class="alert alert-warning small">

            <i class="bi bi-exclamation-triangle"></i>

            Semua anggota yang berada di bawah koordinator
            lama akan otomatis dipindahkan ke koordinator baru.

        </div>

        <label class="form-label">
            Pilih Koordinator Baru
        </label>

        <select name="new_koordinator_id"
        id="replaceCoordinatorSelect"
        class="form-select"
        style="width: 100%;"
        required>

    </select>

</div>

<div class="modal-footer">

    <button type="button"
    class="btn btn-secondary"
    data-bs-dismiss="modal">

    Batal

</button>

<button type="submit"
class="btn btn-primary">

<i class="bi bi-person-gear"></i>
Ganti Koordinator

</button>

</div>

</form>

</div>

</div>

</div>

<div class="modal fade" id="editShiftModal" tabindex="-1">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <form method="post" id="editShiftForm">

                <?= csrf_field() ?>

                <div class="modal-header">

                    <h5 class="modal-title">
                        Atur Jadwal Koordinator
                    </h5>

                    <button type="button"
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                <label class="form-label fw-bold">
                    Hari
                </label>

                <div class="row g-2 mb-3">

                    <?php
                    $hariList = [
                        'senin'  => 'Senin',
                        'selasa' => 'Selasa',
                        'rabu'   => 'Rabu',
                        'kamis'  => 'Kamis',
                        'jumat'  => 'Jumat',
                        'sabtu'  => 'Sabtu',
                        'minggu' => 'Minggu',
                    ];
                    ?>

                    <?php foreach ($hariList as $value => $label): ?>

                        <div class="col-6">

                            <div class="form-check">

                                <input
                                class="form-check-input shiftHari"
                                type="checkbox"
                                name="hari[]"
                                value="<?= $value ?>"
                                id="hari_<?= $value ?>">

                                <label
                                class="form-check-label"
                                for="hari_<?= $value ?>">

                                <?= $label ?>

                            </label>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>


            <div class="row">

                <div class="col-6">

                    <label class="form-label fw-bold">
                        Jam Mulai
                    </label>

                    <input
                    type="time"
                    name="jam_mulai"
                    id="shiftJamMulai"
                    class="form-control"
                    required>

                </div>

                <div class="col-6">

                    <label class="form-label fw-bold">
                        Jam Selesai
                    </label>

                    <input
                    type="time"
                    name="jam_selesai"
                    id="shiftJamSelesai"
                    class="form-control"
                    required>

                </div>

            </div>

        </div>

        <div class="modal-footer">

            <button
            type="button"
            class="btn btn-secondary"
            data-bs-dismiss="modal">

            Batal

        </button>

        <button
        type="submit"
        class="btn btn-primary">

        Simpan Jadwal

    </button>

</div>

</form>

</div>

</div>

</div>

<script>

    const titparId = <?= $titpar['titpar_id'] ?>;


/**
 * Buka modal tambah anggota
 */
    function openAddAnggotaModal(koordinatorId)
    {
        $('#selectedKoordinator').val(koordinatorId);

        $('#addKoordinatorModal').on('shown.bs.modal', function () {
            $('#koordinatorSelect').val(null).trigger('change');
        });

        document.getElementById('addAnggotaForm').action =
        "<?= base_url('titpar/add-anggota/') ?>" + titparId;

        const modal = new bootstrap.Modal(
            document.getElementById('addAnggotaModal')
            );

        modal.show();
    }


/**
 * Hapus anggota
 */
    function removeAnggota(titpargrupId)
    {
        if (!confirm(
            'Yakin ingin menghapus anggota ini dari titik parkir?'
            )) {
            return;
    }

    const form = document.createElement('form');

    form.method = 'POST';

    form.action =
    "<?= base_url('titpar/remove-anggota/') ?>" +
    titpargrupId;

    const csrf = document.createElement('input');

    csrf.type = 'hidden';
    csrf.name = '<?= csrf_token() ?>';
    csrf.value = '<?= csrf_hash() ?>';

    form.appendChild(csrf);

    document.body.appendChild(form);

    form.submit();
}

</script>

<script>
    $(document).ready(function () {

    // ==========================
    // KOORDINATOR
    // ==========================



        $('#koordinatorSelect').select2({
            dropdownParent: $('#addKoordinatorModal'),
            placeholder: 'Cari nama atau id...',
            allowClear: true,
            minimumInputLength: 1,

            ajax: {
                url: "<?= base_url('titpar/search-koordinator/' . $titpar['titpar_id']) ?>",
                type: 'GET',
                dataType: 'json',
                delay: 300,
                cache: false,

                data: function (params) {
                    return {
                        search: params.term || ''
                    };
                },

                processResults: function (data) {

                    console.log('Koordinator response:', data);

                    return {
                        results: (data.results || []).map(function (item) {
                            return {
                                id: String(item.id),
                                text: String(item.text)
                            };
                        })
                    };
                }
            }
        });


    // ==========================
    // ANGGOTA
    // ==========================

        const titparId = <?= $titpar['titpar_id'] ?>;

        console.log('TITPAR ID FROM PHP:', titparId);

        $('#anggotaSelect').select2({
            dropdownParent: $('#addAnggotaModal'),
            placeholder: 'Cari nama atau id...',
            allowClear: true,
            minimumInputLength: 1,

            ajax: {
                url: "<?= base_url('titpar/search-anggota/') ?>" + titparId,
                type: 'GET',
                dataType: 'json',
                delay: 300,
                cache: false,

                data: function (params) {
                    return {
                        search: params.term || ''
                    };
                },

                processResults: function (data) {

                    console.log('Anggota response:', data);

                    return {
                        results: (data.results || []).map(function (item) {
                            return {
                                id: String(item.id),
                                text: String(item.text)
                            };
                        })
                    };
                }
            }
        });


    // Reset koordinator ketika modal dibuka
        $('#addKoordinatorModal').on('shown.bs.modal', function () {

            $('#koordinatorSelect')
            .val(null)
            .trigger('change');

        });

    });
</script>


<script>
    function removeKoordinator(titpargrupId)
    {
        if (!confirm(
            'Yakin ingin menghapus koordinator ini?\n\n' +
            'Anggota yang berada di bawah koordinator ini akan dilepaskan.'
            )) {
            return;
    }

    const form = document.createElement('form');

    form.method = 'POST';

    form.action =
    "<?= base_url('titpar/remove-koordinator/') ?>" +
    titpargrupId;

    // CSRF
    const csrf = document.createElement('input');

    csrf.type = 'hidden';

    csrf.name = '<?= csrf_token() ?>';

    csrf.value = '<?= csrf_hash() ?>';

    form.appendChild(csrf);

    document.body.appendChild(form);

    form.submit();
}
</script>


<script>
    function assignExistingAnggota(titpargrupId)
    {
    // Simpan ID record dishub_titpargrup
        $('#assignTitpargrupId').val(titpargrupId);

    // Reset pilihan koordinator
        $('#assignKoordinatorSelect').val('');

    // Set action form
        $('#assignAnggotaForm').attr(
            'action',
            "<?= base_url('titpar/assign-anggota/') ?>" + titpargrupId
            );

    // Open modal
        const modal = new bootstrap.Modal(
            document.getElementById('assignAnggotaModal')
            );

        modal.show();
    }
</script>

<script>
    function changeCoordinator(titpargrupId)
    {
        $('#changeTitpargrupId').val(titpargrupId);

        $('#changeCoordinatorSelect').val('');

        $('#changeCoordinatorForm').attr(
            'action',
            "<?= base_url('titpar/change-coordinator/') ?>" +
            titpargrupId
            );

        const modal = new bootstrap.Modal(
            document.getElementById('changeCoordinatorModal')
            );

        modal.show();
    }
</script>

<script>
    function replaceCoordinator(titpargrupId)
    {
        $('#oldCoordinatorId').val(titpargrupId);

        $('#replaceCoordinatorForm').attr(
            'action',
            "<?= base_url('titpar/replace-koordinator/') ?>" +
            titpargrupId
            );

        $('#replaceCoordinatorSelect').val(null).trigger('change');

        const modal = new bootstrap.Modal(
            document.getElementById('replaceCoordinatorModal')
            );

        modal.show();
    }

    $('#replaceCoordinatorSelect').select2({

        dropdownParent: $('#replaceCoordinatorModal'),

        placeholder: 'Cari nama atau id...',

        allowClear: true,

        minimumInputLength: 2,

        ajax: {

            url: "<?= base_url('titpar/search-koordinator-replacement/' . $titpar['titpar_id']) ?>",

            dataType: 'json',

            delay: 300,

            data: function(params) {

                return {
                    search: params.term || ''
                };

            },

            processResults: function(data) {

                return {
                    results: data.results
                };

            },

            cache: false

        }

    });
</script>

<script>

    function openEditShiftModal(
        titpargrupId,
        hariBitmask,
        jamMulai,
        jamSelesai
        ) {

    // Reset checkbox
        $('.shiftHari').prop('checked', false);

    // Mapping bitmask
        const hariMap = {
            senin: 1,
            selasa: 2,
            rabu: 4,
            kamis: 8,
            jumat: 16,
            sabtu: 32,
            minggu: 64
        };

    // Set checkbox berdasarkan bitmask
        Object.keys(hariMap).forEach(function(hari) {

            const bit = hariMap[hari];

            if ((parseInt(hariBitmask) & bit) === bit) {

                $('#hari_' + hari).prop('checked', true);

            }

        });

    // Set jam
        $('#shiftJamMulai').val(jamMulai || '');
        $('#shiftJamSelesai').val(jamSelesai || '');

    // Set action form
        $('#editShiftForm').attr(
            'action',
            "<?= base_url('titpar/update-shift/') ?>" + titpargrupId
            );

    // Show modal
        const modal = new bootstrap.Modal(
            document.getElementById('editShiftModal')
            );

        modal.show();
    }

</script>


    <script>

function makeExistingKoordinator(titpargrupId)
{
    if (!confirm(
        'Jadikan anggota ini sebagai koordinator baru?\n\n' +
        'Anggota ini akan menjadi koordinator untuk titik parkir ini.'
    )) {
        return;
    }

    const form = document.createElement('form');

    form.method = 'POST';

    form.action =
        "<?= base_url('titpar/make-koordinator/') ?>" +
        titpargrupId;

    // CSRF
    const csrf = document.createElement('input');

    csrf.type = 'hidden';

    csrf.name = '<?= csrf_token() ?>';

    csrf.value = '<?= csrf_hash() ?>';

    form.appendChild(csrf);

    document.body.appendChild(form);

    form.submit();
}


</script>

<?php
$masaBerlakuStart = $koordinator[0]['titpargrup_masaberlakustart'] ?? '';
$masaBerlakuEnd = $koordinator[0]['titpargrup_masaberlakuend'] ?? '';
$masaBerlakuStart = $masaBerlakuStart ? date('Y-m-d', strtotime($masaBerlakuStart)) : '';
$masaBerlakuEnd = $masaBerlakuEnd ? date('Y-m-d', strtotime($masaBerlakuEnd)) : '';
?>

<div class="modal fade" id="masaBerlakuModal" tabindex="-1" aria-labelledby="masaBerlakuModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="masaBerlakuModalLabel">
                    <i class="bi bi-calendar-check me-2"></i>Atur Masa Berlaku KTA
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form method="post" action="<?= base_url('titpar/update-masa-berlaku/' . $titpar['titpar_id']) ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="alert alert-info small mb-3">
                        Tanggal ini akan diterapkan ke seluruh koordinator dan anggota pada titik parkir ini.
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="titpargrup_masaberlakustart" class="form-label fw-semibold">Masa berlaku</label>
                            <input type="date" class="form-control" id="titpargrup_masaberlakustart"
                                   name="titpargrup_masaberlakustart" value="<?= esc($masaBerlakuStart) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="titpargrup_masaberlakuend" class="form-label fw-semibold">Masa berakhir</label>
                            <input type="date" class="form-control" id="titpargrup_masaberlakuend"
                                   name="titpargrup_masaberlakuend" value="<?= esc($masaBerlakuEnd) ?>" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save me-1"></i>Simpan Masa Berlaku
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="masaBerlakuAnggotaModal" tabindex="-1" aria-labelledby="masaBerlakuAnggotaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title" id="masaBerlakuAnggotaModalLabel"><i class="bi bi-calendar-check me-2"></i>Atur Masa Berlaku Anggota</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <form method="post" id="masaBerlakuAnggotaForm">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="alert alert-info small" id="masaBerlakuAnggotaKeterangan"></div>
                <div class="row g-3"><div class="col-md-6"><label for="masaAnggotaStart" class="form-label fw-semibold">Masa berlaku</label><input type="date" class="form-control" id="masaAnggotaStart" name="titpargrup_masaberlakustart" required></div><div class="col-md-6"><label for="masaAnggotaEnd" class="form-label fw-semibold">Masa berakhir</label><input type="date" class="form-control" id="masaAnggotaEnd" name="titpargrup_masaberlakuend" required></div></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-success"><i class="bi bi-save me-1"></i>Simpan</button></div>
        </form>
    </div></div>
</div>

<script>
function openEditMasaAnggotaModal(id, start, end, nama, isKoordinator = false) {
    document.getElementById('masaBerlakuAnggotaForm').action = "<?= base_url('titpar/update-masa-berlaku-anggota/') ?>" + id;
    document.getElementById('masaAnggotaStart').value = start ? start.substring(0, 10) : '';
    document.getElementById('masaAnggotaEnd').value = end ? end.substring(0, 10) : '';
    document.getElementById('masaBerlakuAnggotaKeterangan').innerHTML = isKoordinator
        ? 'Tanggal akan diterapkan untuk koordinator <strong>' + nama + '</strong> dan seluruh anggota di bawahnya.'
        : 'Tanggal hanya diterapkan untuk <strong>' + nama + '</strong>.';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('masaBerlakuAnggotaModal')).show();
}
</script>

<div class="modal fade" id="printKtaModal" tabindex="-1" aria-labelledby="printKtaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="printKtaModalLabel"><i class="bi bi-person-badge me-2"></i>Cetak KTA</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form method="get" action="<?= base_url('titpar/print-kta/' . $titpar['titpar_id']) ?>" target="_blank">
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Ukuran cetak</label>
                            <select name="size" class="form-select">
                                <option value="cr80">CR80 - ukuran kartu</option>
                                <option value="a4">A4 - beberapa kartu per halaman</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <span class="small text-muted">Centang orang yang ingin dicetak.</span>
                        </div>
                    </div>
                    <div class="border rounded p-2" style="max-height: 360px; overflow:auto">
                        <?php foreach (($koordinator ?? []) as $k): ?>
                            <?php $koorId = (int) ($k['titpargrup_anggotaid'] ?? 0); ?>
                            <label class="d-block py-1 fw-semibold">
                                <input class="form-check-input me-2" type="checkbox" name="person_ids[]" value="<?= $koorId ?>" checked>
                                <?= esc($k['anggota_nama'] ?? '-') ?> <span class="badge bg-primary-subtle text-primary">Koordinator</span>
                            </label>
                            <?php foreach (($k['anggota'] ?? []) as $a): ?>
                                <label class="d-block py-1 ps-4">
                                    <input class="form-check-input me-2" type="checkbox" name="person_ids[]" value="<?= (int) ($a['anggota_id'] ?? 0) ?>" checked>
                                    <?= esc($a['anggota_nama'] ?? '-') ?>
                                </label>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-printer me-1"></i> Buka Cetak</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="uploadSuratPenunjukanModal" tabindex="-1" aria-labelledby="uploadSuratPenunjukanLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadSuratPenunjukanLabel">Unggah Surat Penunjukan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form action="<?= base_url('titpar/upload-penunjukan/' . $titpar['titpar_id']) ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <p class="small text-muted">Unggah hasil pindai surat yang sudah ditandatangani Kadis.</p>
                    <label for="suratPenunjukan" class="form-label small">Berkas surat bertanda tangan</label>
                    <input type="file" class="form-control" id="suratPenunjukan" name="surat_penunjukan" accept=".pdf,.jpg,.jpeg,.png" aria-describedby="suratPenunjukanHelp" required>
                    <div id="suratPenunjukanHelp" class="form-text">PDF, JPG, atau PNG. Maksimal 5 MB. Unggah ulang akan mengganti surat yang ditampilkan.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-upload me-1"></i>Unggah Surat</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php echo view('footer'); ?>
