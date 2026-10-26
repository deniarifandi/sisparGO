<?php

function tanggalIndo($date): string
{
    if (!$date || !($ts = strtotime($date))) return '-';

    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    return date('j', $ts) . ' ' . $bulan[(int) date('n', $ts)] . ' ' . date('Y', $ts);
}

function fitImage(string $file, float $maxW, float $maxH, array $fallback): array
{
    if (!is_file($file) || !($d = @getimagesize($file))) return $fallback;

    [$natW, $natH] = $d;
    if (!$natW || !$natH) return $fallback;

    $scale = min($maxW / $natW, $maxH / $natH);

    return [$natW * $scale, $natH * $scale];
}

function normalizedPhotoSrc(string $file, string $url): string
{
    if (!is_file($file) || !function_exists('exif_read_data') || !function_exists('imagecreatefromstring')) return $url;
    $exif = @exif_read_data($file);
    $orientation = (int) ($exif['Orientation'] ?? 1);
    if ($orientation <= 1) return $url;
    $image = @imagecreatefromstring((string) file_get_contents($file));
    if (!$image) return $url;
    switch ($orientation) {
        case 2: imageflip($image, IMG_FLIP_HORIZONTAL); break;
        case 3: $image = imagerotate($image, 180, 0); break;
        case 4: imageflip($image, IMG_FLIP_VERTICAL); break;
        case 5: imageflip($image, IMG_FLIP_HORIZONTAL); $image = imagerotate($image, 90, 0); break;
        case 6: $image = imagerotate($image, -90, 0); break;
        case 7: imageflip($image, IMG_FLIP_HORIZONTAL); $image = imagerotate($image, -90, 0); break;
        case 8: $image = imagerotate($image, 90, 0); break;
    }
    ob_start(); imagejpeg($image, null, 90); $data = base64_encode(ob_get_clean());
    imagedestroy($image);
    return 'data:image/jpeg;base64,' . $data;
}

function photoBox($photo): array
{
    $boxW = 27;
    $boxH = 36;

    $photo = trim((string) $photo);
    $file  = FCPATH . 'uploads/anggota_foto/' . $photo;

    if (!$photo || !is_file($file) || !($d = @getimagesize($file))) {
        return [$boxW, $boxH, 0, 0, false];
    }

    [$natW, $natH] = $d;
    if (function_exists('exif_read_data')) {
        $exif = @exif_read_data($file);
        if (in_array((int) ($exif['Orientation'] ?? 1), [5, 6, 7, 8], true)) [$natW, $natH] = [$natH, $natW];
    }

    $scale = max($boxW / $natW, $boxH / $natH);
    $w = $natW * $scale;
    $h = $natH * $scale;

    return [
        $w,
        $h,
        ($boxH - $h) / 2,
        ($boxW - $w) / 2,
        true
    ];
}

$logoCity   = fitImage(FCPATH . 'images/logomalang.png', 10, 13, [10, 13]);
$logoDishub = fitImage(FCPATH . 'images/dishub.png', 10, 13, [10, 13]);

$pages = array_chunk($people ?? [], 4);
?>

<!doctype html>
<html lang="id">

<head>
<meta charset="utf-8">
<title>Cetak KTA</title>

<style>
@page {
    size: A4 landscape;
}

* {
    box-sizing: border-box;
}

html, body {
    margin: 0;
    margin-left: 3mm;
    margin-top: 3mm;
    margin-bottom: 0mm;
    padding: 0;
    font-family: Arial, sans-serif;
    color: #111;
    background: #fff;
}

.toolbar {
    margin-bottom: 5mm;
    padding: 10px;
    text-align: center;
    background: #eef2f7;
}

.toolbar button {
    margin: 0 3px;
    padding: 8px 20px;
    cursor: pointer;
}

.print-page {
    width: 100%;
    height: 100%;
    padding: 0;
    font-size: 0;
    white-space: nowrap;
    page-break-after: always;
    page-break-inside: avoid;
}

.print-page:last-child {
    page-break-after: auto;
}

.person-card {
    display: inline-block;
    vertical-align: top;
    width: 65mm;
    height: 200mm;
    border: 1px solid #455b70;
    font-size: 10px;
    white-space: normal;
    page-break-inside: avoid;
}

.card {
    position: relative;
    display: block;
    width: 65mm;
    height: 90mm;
    
    overflow: hidden;
    border: 1px solid #7c8b9b;
    /*border-radius: 3mm;*/
    /*background-color: red;*/
    
}

.front {
    /*padding: 3mm;*/
    height: 93mm;
    width: 65mm;
    /*padding-top: 5mm;
    padding-left: 5mm;
    margin-right: 5mm;*/
    /*background-color: red;*/
    transform: rotate(180deg);
    transform-origin: center;
}

.watermark {
    position: absolute;
    width: 80%;
    right: -20%;
    bottom: -20%;
    opacity: .30;
}

/* FRONT */

.top {
    position: relative;
    /*height: 21mm;*/
    /*padding-top: 5mm;*/
    /*padding: 2mm 2mm 0;*/
    /*padding-top: 4mm;*/
    text-align: center;
    font-size: 7.2px;
}

.logo-city,
.logo-dishub {

    position: absolute;
    top: 4mm;
    margin-left: 3mm;
}

.logo-city {
    left: 5mm;
    /*margin-left: 3mm;*/
}

.logo-dishub {
    right: 5mm;
    margin-right: 3mm;
}

.title {
    margin-top: 13mm;
    font-size: 8px;
}

.title strong {
    display: block;
    font-size: 10px;
}

.title span {
    display: block;
    font-size: 9px;
}

.photobox {
    position: relative;
    width: 27mm;
    height: 35mm;
    margin: 0 auto;
    overflow: hidden;
    border: 1px solid #455b70;
    border-radius: 1.5mm;
    background: #f1f3f5;
}

.photo {
    position: absolute;
    display: block;
}

.info-row {
    display: table;
    width: 100%;
    margin-top: 2mm;
    padding: 0 3mm;
}

.qr-wrap {
    display: table-cell;
    width: 17mm;
    vertical-align: middle;
    text-align: center;
}

.qr {
    display: block;
    width: 18mm;
    height: 18mm;
    /*margin: auto;*/
    margin-left: 5mm;
}

.qr-id {
    margin-top: .5mm;
    font-size: 8px;
    text-align: center;
}

.front-info {
    display: table-cell;
    padding-left: 3mm;
    vertical-align: middle;
    text-align: center;
}

.name {
    font-size: 12px;
    font-weight: bold;
    text-transform: uppercase;
}

.role {
    margin-top: 1.2mm;
    font-size: 8px;
    font-weight: bold;
}

.reg {
    margin-top: .8mm;
    font-size: 7px;
}

/* BACK */

.back {
    /*padding-top: 4mm;*/
    padding-top: 5mm;
    width: 60mm;
    /*padding-bottom: 5mm;*/
    padding-left: 3mm;
    padding-right: 2mm;
    text-align: center;
}

.back-title {
    margin-bottom: 2mm;
    font-size: 9px;
}

.place {
    padding: 0 2mm;
    font-size: 12px;
    font-weight: bold;
    text-transform: uppercase;
}

.address {
    margin-top: 1mm;
    padding: 0 2mm;
    font-size: 8px;
}

.valid {
    margin-top: 2mm;
    font-size: 8px;
}

.sign {
    margin-top: 1mm;
    /*padding: 0 4mm;*/
    font-size: 7px;
    text-align: center;
}

.ttd {
    display: block;
    max-width: 30%;
    max-height: 12mm;
    margin: 1mm auto 0;
}

.sign-name,
.sign-name strong {
    font-size: 8px;
    text-align: center;
}

.notes {
    margin-top: 2mm;
    padding: 0 6mm;
    font-size: 7px;
    line-height: 1.2;
    text-align: justify;
    color: #444;
    font-weight: bold;
}

@media print {
    .toolbar {
        display: none;
    }


}
</style>
</head>

<body>

<div class="toolbar">
    <button type="button" onclick="window.print()">Cetak</button>
    <button type="button" onclick="window.close()">Tutup</button>
</div>

<?php foreach ($pages as $pagePeople): ?>

<div class="print-page">

<?php foreach ($pagePeople as $person):

    /* FOTO */
    $photo = trim((string) ($person['anggota_foto'] ?? ''));

    [$pw, $ph, $pt, $pl, $hasImg] = photoBox($photo);

    $photoSrc = $hasImg
        ? normalizedPhotoSrc(FCPATH . 'uploads/anggota_foto/' . $photo, base_url('uploads/anggota_foto/' . $photo))
        : base_url('images/no-photo.png');

    /* QR */
    $qrData = ($titpar['titpar_id'] ?? '')
        . '-'
        . ($person['anggota_id'] ?? '');

    $qr = generateQr($qrData);
?>

<div class="person-card">

    <!-- FRONT -->
    <div class="card front" style="margin-bottom:0mm">

        <img
            class="watermark"
            src="<?= base_url('images/dishub.png') ?>"
            alt=""
        >

        <div class="top">

            <img
                class="logo-city"
                src="<?= base_url('images/logomalang.png') ?>"
                style="width:<?= $logoCity[0] ?>mm;height:<?= $logoCity[1] ?>mm"
                alt=""
            >

            <img
                class="logo-dishub"
                src="<?= base_url('images/dishub.png') ?>"
                style="width:<?= $logoDishub[0] ?>mm;height:<?= $logoDishub[1] ?>mm"
                alt=""
            >

            <div class="title">
                Kartu Tanda Anggota (KTA)

                <strong>PETUGAS PARKIR</strong>

                <span>
                    DINAS PERHUBUNGAN<br>
                    KOTA MALANG
                </span>
                <br>
            </div>

        </div>

        <div class="photobox">
            <img
                class="photo"
                src="<?= $photoSrc ?>"
                style="
                    width:<?= $pw ?>mm;
                    height:<?= $ph ?>mm;
                    top:<?= $pt ?>mm;
                    left:<?= $pl ?>mm
                "
                alt=""
            >
        </div>

        <div class="info-row">

            <div class="qr-wrap">

                <img
                    class="qr"
                    src="<?= esc($qr) ?>"
                    alt="QR"
                >

                <div class="qr-id">
                    <?= esc($qrData) ?>
                </div>

            </div>

            <div class="front-info">

                <div class="name">
                    <?= esc($person['anggota_nama'] ?? '-') ?>
                </div>

                <div class="role">
                    <?= esc($person['role'] ?? '-') ?>
                </div>

                <div class="reg">
                    <?= esc($person['anggota_id'] ?? '-') ?>
                </div>

            </div>

        </div>

    </div>


    <!-- BACK -->
    <div class="card back" >

        <img
            class="watermark"
            src="<?= base_url('images/dishub.png') ?>"
            alt=""
        >
        <br>
        <div class="back-title">
            Titik Parkir
        </div>

        <div class="place">
            <?= esc($titpar['titpar_namatempat'] ?? '-') ?>
        </div>

        <div class="address">
            <?= esc($titpar['titpar_lokasi'] ?? '-') ?>
        </div>

        <div class="valid">

            Berlaku s/d
            <?= tanggalIndo($person['validUntil'] ?? null) ?>

            <br><br>

            Malang,
            <?= tanggalIndo($person['validFrom'] ?? null) ?>

        </div>

        <div class="sign">
            Kepala Dinas Perhubungan<br>
            Kota Malang
        </div>

        <img
            class="ttd"
            src="<?= base_url('images/ttd.png') ?>"
            alt="Tanda tangan"
        >

        <div class="sign-name">
            <strong>
                Drs. R. WIDJAJA SALEH PUTRA<br>
                Pembina Utama Muda<br>
                NIP. 19661119 1986021 002
            </strong>
        </div>

        <div class="notes">
            Catatan:<br>

            1. KTA ini bukan bukti kepemilikan tempat parkir dan satu kesatuan dengan Surat Penunjukan.<br>

            2. KTA ini akan dicabut apabila Pemerintah Daerah membutuhkan untuk kepentingan umum/Jalan atau Petugas Parkir melakukan pelanggaran berdasarkan Perda No. 2 Tahun 2026.<br>

            3. Dilarang meminjamkan KTA ini kepada orang lain dan memperjualbelikan/menggadaikan atau sejenisnya dan dikenakan sanksi sesuai ketentuan peraturan perundang-undangan.
        </div>

    </div>

</div>

<?php endforeach; ?>

</div>

<?php endforeach; ?>

</body>
</html>
