<?php
$isCr80    = $size === 'cr80';


function tanggalIndo($date)
{
    $bulanIndo = [
        1  => 'Januari',
        2  => 'Februari',
        3  => 'Maret',
        4  => 'April',
        5  => 'Mei',
        6  => 'Juni',
        7  => 'Juli',
        8  => 'Agustus',
        9  => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember'
    ];

    $timestamp = strtotime($date);

    return date('j', $timestamp) . ' ' .
           $bulanIndo[(int) date('n', $timestamp)] . ' ' .
           date('Y', $timestamp);
}

/**
 * dompdf (v3.1.6) does NOT support `object-fit`. So for ANY image we place
 * on the card (member photo, logos) we must compute its on-page box
 * ourselves, keeping the true aspect ratio (proportional, never stretched)
 * and centering it inside a fixed area.
 *
 * Returns: [drawW_mm, drawH_mm]
 */
function kta_fit_image(string $absolutePath, float $maxW, float $maxH, array $fallback): array
{
    if (!is_file($absolutePath)) {
        return $fallback;
    }

    $dims = @getimagesize($absolutePath);
    if (!$dims || count($dims) < 2 || intval($dims[0]) <= 0 || intval($dims[1]) <= 0) {
        return $fallback;
    }

    $natW  = floatval($dims[0]);
    $natH  = floatval($dims[1]);
    $ratio = $natW / $natH;

    // contain-fit inside maxW x maxH, preserving ratio
    $w = $maxW;
    $h = $maxW / $ratio;
    if ($h > $maxH) {
        $h = $maxH;
        $w = $maxH * $ratio;
    }

    return [$w, $h];
}

function kta_normalized_photo_src(string $file, string $url): string
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

/**
 * "Cover" fit (like CSS background-size:cover, which dompdf doesn't
 * support): scales the image so it fully covers a box of boxW x boxH
 * (cropping the overflow), then centers it. Used to turn any source photo
 * into a proper 3:4 pas-foto style crop, never letterboxed/stretched.
 *
 * Returns: [drawW_mm, drawH_mm, top_mm, left_mm] — top/left are typically
 * negative (or zero), meant to be used with the box as overflow:hidden.
 */
function kta_cover_fit(float $natW, float $natH, float $boxW, float $boxH): array
{
    $scale = max($boxW / $natW, $boxH / $natH);
    $drawW = $natW * $scale;
    $drawH = $natH * $scale;
    $left  = ($boxW - $drawW) / 2.0;
    $top   = ($boxH - $drawH) / 2.0;

    return [$drawW, $drawH, $top, $left];
}

/**
 * Member photo box: crop (cover-fit) into a fixed 3:4 portrait box
 * ("pas foto" style), centered, regardless of the source photo's own
 * aspect ratio (portrait, landscape, or square).
 *
 * Returns: [drawW_mm, drawH_mm, top_mm, left_mm, hasImage]
 */
function kta_photo_box($photo): array
{
    $boxW = 27.0; // mm — 3:4 ratio with boxH below (3 : 4 = 21 : 28)
    $boxH = 36.0; // mm — matches .photobox height in CSS; smaller so name/role/reg/QR all fit on one card

    $photo = trim((string) ($photo ?? ''));
    $file  = $photo !== '' ? FCPATH . 'uploads/anggota_foto/' . $photo : '';

    if ($file === '' || !file_exists($file)) {
        return [$boxW, $boxH, 0.0, 0.0, false];
    }

    $dims = @getimagesize($file);
    if (!$dims || count($dims) < 2 || intval($dims[0]) <= 0 || intval($dims[1]) <= 0) {
        return [$boxW, $boxH, 0.0, 0.0, false];
    }

    [$natW, $natH] = [$dims[0], $dims[1]];
    if (function_exists('exif_read_data')) {
        $exif = @exif_read_data($file);
        if (in_array((int) ($exif['Orientation'] ?? 1), [5, 6, 7, 8], true)) [$natW, $natH] = [$natH, $natW];
    }
    [$w, $h, $top, $left] = kta_cover_fit(floatval($natW), floatval($natH), $boxW, $boxH);

    return [$w, $h, $top, $left, true];
}

// Logos: computed once, proportionally, so they never get stretched.
$logoCityBox   = kta_fit_image(FCPATH . 'images/logomalang.png', 8.0, 11.0, [8.0, 10.0]);
$logoDishubBox = kta_fit_image(FCPATH . 'images/dishub.png', 9.0, 11.0, [9.0, 10.0]);

// Signature (public/ttd.png): computed proportionally, shown above the name
// on the back of the card. Adjust FCPATH path below if ttd.png actually
// lives inside a subfolder (e.g. 'images/ttd.png').
$ttdBox = kta_fit_image(FCPATH . 'ttd.png', 20.0, 10.0, [20.0, 8.0]);
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Cetak KTA</title>
<style>
@page {
    size: <?= $isCr80 ? '54mm 85.6mm' : 'A4' ?>;
    margin: <?= $isCr80 ? '0' : '5mm' ?>;
}
* { box-sizing: border-box; }
body { font-family: Arial, sans-serif; margin: 0; color: #111; }

.toolbar {
    text-align: center;
    padding: 12px;
    background: #eef2f7;
}

.sheet {
    display: grid;
    grid-template-columns: repeat(3, 54mm);
    gap: 7mm 5mm;
    width: <?= $isCr80 ? '54mm' : '180mm' ?>;
    margin: <?= $isCr80 ? '0' : 'auto' ?>;
}

.card {
    width: 54mm;
    height: 85.6mm;
    border: 1px solid #7c8b9b;
    border-radius: 1.8mm;
    overflow: hidden;
    position: relative;
    background: #fff;
    page-break-inside: avoid;
    color: #111;
}

/* --- front --- */
.top {
    height: 21mm;
    text-align: center;
    padding: 2mm 2mm 0;
    font-size: 7.2px;
    position: relative;
}
/* Width/height are set inline per-image (computed proportionally in PHP);
   only positioning stays here. */
.top img.logo-city {
    position: absolute;
    left: 5mm;
    top: 2mm;
}
.top img.logo-dishub {
    position: absolute;
    right: 5mm;
    top: 2mm;
}
.title { margin-top: 9mm; font-size: 7px; }
.title strong { display: block; font-size: 9px; }
.title span  { display: block; font-size: 8px; }

/* Photo area: fixed 3:4 portrait box ("pas foto"), centered on the card.
   overflow:hidden crops whatever part of the cover-fit image sticks out. */
.photobox {
    width: 27mm;  /* 3:4 ratio with height below */
    height: 36mm;
    margin: 0 auto;
    overflow: hidden;
    position: relative;
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
    margin-top: 1.5mm;
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
    width: 15mm;
    height: 15mm;
    margin: 0 auto;
    padding: 0;
}

.qr-id {
    font-size: 5.5px;
    text-align: center;
    margin-top: 0.5mm;
}

.front-info {
    display: table-cell;
    vertical-align: middle;
    text-align: center;
    padding-left: 2mm;
}

.name {
    font-weight: bold;
    font-size: 12px;
    text-transform: uppercase;
    margin: 0;
}

.role {
    font-size: 8px;
    font-weight: bold;
    margin-top: 1.2mm;
}

.reg {
    font-size: 7px;
    margin-top: 0.8mm;
}

/* --- back --- */
.back {
    padding-top: 2mm;
    padding-bottom: 2mm;
    text-align: center;
    background: linear-gradient(
        #fff,
        #eef4f8
    );
}
.back:after {
    content: "";
    /*position: absolute;*/
    inset: 0;
    background: linear-gradient(145deg, transparent 55%, rgba(32, 91, 133, .1));
    pointer-events: none;
}
.watermark {
    position: absolute;
    bottom: 0;
    right: 0;
    /*width: 100%;*/
    opacity: .30;
}

.back-title { font-size: 9px; margin-bottom: 2mm; }
.place  { font-weight: bold; font-size: 10px; text-transform: uppercase; }
.address { font-size: 8px; margin-top: 1mm; }
.valid  { font-size: 7px; margin-top: 2mm; }

/* Signature block: everything center-aligned, ttd.png sits above the name. */
.sign {
    /*position: absolute;*/

    left: 4mm;
    right: 4mm;
    font-size: 7px;
    text-align: center;
}
.sign img.ttd {
    /*display: block;*/
    margin: 1mm auto;
}
.sign strong { font-size: 8px; text-decoration: underline; }

.notes {
    /*position: absolute;*/

    bottom: 1.5mm;

    left: 4mm;
    right: 4mm;
    padding-left: 4mm;
    padding-right: 4mm;

    text-align: justify;

    font-size: 7px;

    color: #444;
}

@media print {
    .toolbar { display: none; }
    .sheet   { margin: 0; }
    .card    { break-inside: avoid; }
}
</style>
</head>
<body>
<div class="toolbar">
    <button onclick="window.print()">Cetak</button>
    <button onclick="window.close()">Tutup</button>
</div>

<div class="sheet">
<?php foreach ($people as $person): ?>

<?php
$photo    = trim((string) ($person['anggota_foto'] ?? ''));
$box      = kta_photo_box($photo);
$pw       = $box[0];
$ph       = $box[1];
$pt       = $box[2];
$pl       = $box[3];
$hasImg   = $box[4];
$photoSrc = $hasImg
    ? kta_normalized_photo_src(FCPATH . 'uploads/anggota_foto/' . $photo, base_url('uploads/anggota_foto/' . $photo))
    : base_url('images/no-photo.png');


$qrData = ($titpar['titpar_id'] ?? '') . '-' . ($person['anggota_id'] ?? '');

$qr = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data='
    . rawurlencode($qrData);
?>

<!-- FRONT -->
<div class="card front">
    <img
    class="watermark"
    src="<?= base_url('images/dishub.png') ?>"
    alt=""
    style="
        width: 80%;
        position: absolute;
        right: -20%;
        bottom: -20%;
    "
>
    <div class="top">
        <img class="logo-city"
             style="width:<?= $logoCityBox[0] ?>mm;height:<?= $logoCityBox[1] ?>mm"
             src="<?= base_url('images/logomalang.png') ?>">
        <img class="logo-dishub"
             style="width:<?= $logoDishubBox[0] ?>mm;height:<?= $logoDishubBox[1] ?>mm"
             src="<?= base_url('images/dishub.png') ?>">
        <div class="title">
            Kartu Tanda Anggota (KTA)
            <strong>PETUGAS PARKIR</strong>
            <span>DINAS PERHUBUNGAN<br>KOTA MALANG</span>
        </div>
    </div>
    <div class="photobox">
        <img class="photo"
             style="width:<?= $pw ?>mm;height:<?= $ph ?>mm;top:<?= $pt ?>mm;left:<?= $pl ?>mm"
             src="<?= $photoSrc ?>"
             alt="Foto anggota">
    </div>

<div class="info-row" style="margin-top: 5mm;">

    <div class="qr-wrap">
        <img class="qr" src="<?= esc($qr) ?>" alt="QR Code">
        <div class="qr-id">
            <?= esc($titpar['titpar_id'].'-'.$person['anggota_id']) ?>
        </div>
    </div>

    <div class="front-info">
        <div class="name"><?= esc($person['anggota_nama'] ?? '-') ?></div>
        <div class="role"><?= esc($person['role'] ?? '-') ?></div>
        <div class="reg"><?= esc($person['anggota_id'] ?? '-') ?></div>
    </div>

</div>
</div>
</div>

<!-- BACK -->
<div class="card back" style="margin-bottom:0px; padding-bottom: 0px;">
    <img
    class="watermark"
    src="<?= base_url('images/dishub.png') ?>"
    alt=""
    style="
        width: 80%;
        position: absolute;
        right: -20%;
        bottom: -20%;
    "
>
    <div class="back-title">Titik Parkir</div>
    <div class="place" style="padding-left: 1.5mm; padding-right: 1.5mm;"><?= esc($titpar['titpar_namatempat'] ?? '-') ?></div>
    <div class="address"><?= esc($titpar['titpar_lokasi'] ?? '-') ?></div>
    <div class="valid">
        Berlaku s/d <?= tanggalIndo($person['validUntil'] ?? null) ?><br><br>
        Malang, <?= tanggalIndo($person['validFrom'] ?? null) ?>
    </div>
    <div class="sign">
        Kepala Dinas Perhubungan<br>
        Kota Malang
    </div>

    <img class="ttd"
             style="max-width: 30%;"
             src="<?= base_url('images/ttd.png') ?>"
             alt="Tanda tangan">
    <br>
    <div  style="font-size:10px">
        <strong>Drs. R. WIDJAJA SALEH PUTRA<br>
        Pembina Utama Muda<br>
        NIP. 19661119 1986021 002
        </strong>
    </div>
    <br>

    <div class="notes">Catatan:<br>
        1. KTA ini bukan bukti kepemilikan tempat parkir dan satu kesatuan dengan Surat Penunjukan.<br>
        2. KTA ini akan dicabut apabila Pemerintah Daerah membutuhkan untuk kepentingan umum/Jalan atau Petugas Parkir melakukan pelanggaran berdasarkan Perda No. 2 Tahun 2026.<br>
        3. Dilarang meminjamkan KTA ini kepada orang lain dan memperjualbelikan/menggadaikan atau sejenisnya dan dikenakan sanksi sesuai ketentuan peraturan perundang-undangan.</div>
</div>

<?php endforeach; ?>
</div>
</body>
</html>
