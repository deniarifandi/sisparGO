<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Persetujuan Titik Lokasi Parkir</title>
    <style>
        @page { 
            size: A4; 
            margin: 1.2cm 2cm; 
        }
        *, *::before, *::after { 
            box-sizing: border-box; 
        }
        body {
            font-family: 'Times New Roman', serif;
            font-size: 11.5px; 
            color: #000;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 6px;
            margin-bottom: 12px;
        }
        .header h4 { font-size: 14px; margin: 0 0 2px 0; font-weight: bold; letter-spacing: 0.5px; }
        .header h5 { font-size: 15px; margin: 0; font-weight: bold; letter-spacing: 0.5px; }
        
        .title {
            text-align: center;
            font-weight: bold;
            font-size: 13.5px;
            text-decoration: underline;
            margin: 10px 0;
            text-transform: uppercase;
        }
        
        /* Layout Kompak Dua Kolom */
        .container-table {
            width: 100%;
            display: table;
            table-layout: fixed;
            margin-bottom: 8px;
        }
        .column-table {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .column-table:first-child {
            padding-right: 15px;
        }
        .column-table:last-child {
            padding-left: 15px;
            border-left: 1px dashed #ccc;
        }

        .section-title {
            font-weight: bold;
            font-size: 11.5px;
            margin-top: 4px;
            margin-bottom: 5px;
            text-transform: uppercase;
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
        }
        
        table.info { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 3px; 
        }
        table.info td { 
            padding: 2.5px 2px; 
            vertical-align: top; 
            font-size: 11px;
        }
        table.info td.label { width: 38%; }
        table.info td.colon { width: 4%; text-align: center; }
        
        /* Layout Tiga Kolom untuk Dokumentasi & Map */
        .photo-table {
            width: 100%;
            display: table;
            table-layout: fixed;
            margin: 6px 0;
        }
        .photo-cell {
            display: table-cell;
            width: 33.33%; 
            text-align: center;
            vertical-align: top;
            padding: 0 4px;
        }
        .photo-cell:first-child { padding-left: 0; }
        .photo-cell:last-child { padding-right: 0; }
        
        .photo-box {
            border: 1px solid #777;
            padding: 4px;
            background: #fafafa;
        }
        .photo-box img {
            width: 100%;
            height: 105px; 
            object-fit: cover;
            display: block;
            margin-bottom: 4px;
        }
        .photo-box .no-img {
            height: 105px;
            background: #eee;
            color: #777;
            font-size: 11px;
            line-height: 105px;
            font-style: italic;
            margin-bottom: 4px;
        }
        .photo-box .caption { 
            font-size: 10px; 
            font-weight: bold;
            color: #333;
        }
        
        .keterangan-text {
            text-align: justify;
            margin-bottom: 6px;
            font-size: 11px;
        }
        
        p.narasi {
            text-align: justify;
            text-indent: 30px;
            margin: 0 0 6px 0;
            font-size: 11px;
        }

        /* Tanda Tangan */
        .signature-block {
            margin-top: 12px;
            width: 100%;
            page-break-inside: avoid;
        }
        .signature-block table { width: 100%; border-collapse: collapse; }
        .signature-block td { width: 50%; vertical-align: top; font-size: 11.5px; }
        .signature-block td.right-align { text-align: center; padding-left: 10%; }
        .signature-space { height: 50px; }
        
        .no-print {
            text-align: center;
            margin-bottom: 15px;
        }
        .no-print button {
            padding: 6px 16px; 
            font-size: 13px;
            cursor: pointer;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
        }
        @media print {
            .no-print { display: none; }
            @page { margin: 1.2cm 2cm; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()">
        🖨️ Cetak Dokumen Resmi
    </button>
</div>

<div class="header">
    <h4>PEMERINTAH KOTA MALANG</h4>
    <h5>DINAS PERHUBUNGAN</h5>
</div>

<div class="title">Persetujuan Titik Lokasi Parkir</div>

<div class="container-table">
    
    <div class="column-table">
        <div class="section-title">I. Identitas Koordinator</div>
        <table class="info">
            <tr>
                <td class="label">ID Anggota</td><td class="colon">:</td>
                <td><strong><?= esc($titpar['register_anggotaid']) ?></strong></td>
            </tr>
            <tr>
                <td class="label">Nama</td><td class="colon">:</td>
                <td><?= esc($titpar['anggota_nama']) ?></td>
            </tr>
            <tr>
                <td class="label">No. KTP</td><td class="colon">:</td>
                <td><?= esc($titpar['anggota_noktp']) ?></td>
            </tr>
            <tr>
                <td class="label">Alamat</td><td class="colon">:</td>
                <td><?= esc($titpar['anggota_alamat'] ?? '-') ?></td>
            </tr>
            <tr>
                <td class="label">No. HP</td><td class="colon">:</td>
                <td><?= esc($titpar['hp'] ?? '-') ?></td>
            </tr>
            <tr>
                <td class="label">Tgl Lahir</td><td class="colon">:</td>
                <td><?= esc($titpar['anggota_tgllhr'] ?? '-') ?></td>
            </tr>
        </table>
    </div>

    <div class="column-table">
        <div class="section-title">II. Detail Titik Lokasi Parkir</div>
        <table class="info">
            <tr>
                <td class="label">Nama Tempat</td><td class="colon">:</td>
                <td><?= esc($titpar['titpar_namatempat']) ?></td>
            </tr>
            <tr>
                <td class="label">Lokasi / Alamat</td><td class="colon">:</td>
                <td><?= esc($titpar['titpar_lokasi']) ?></td>
            </tr>
            <tr>
                <td class="label">Kelurahan</td><td class="colon">:</td>
                <td><?= esc($titpar['kelurahan'] ?? '-') ?></td>
            </tr>
            <tr>
                <td class="label">Kecamatan</td><td class="colon">:</td>
                <td><?= esc($titpar['kecamatan'] ?? '-') ?></td>
            </tr>
            <tr>
                <td class="label">Batas Parkir</td><td class="colon">:</td>
                <td><?= esc($titpar['batas_parkir'] ?? '-') ?></td>
            </tr>
            <tr>
                <td class="label">Jam Operasional</td><td class="colon">:</td>
                <td><?= esc($titpar['jam_operasional'] ?? '-') ?></td>
            </tr>
            <tr>
                <td class="label">Jenis Kendaraan</td><td class="colon">:</td>
                <td>
                    <?php
                    $jenis = (int)($titpar['jenis_kendaraan'] ?? 0);
                    $list  = [];
                    if ($jenis & 1) $list[] = 'Motor';
                    if ($jenis & 2) $list[] = 'Mobil';
                    if ($jenis & 4) $list[] = 'Truk';
                    echo $list ? implode(', ', $list) : '-';
                    ?>
                </td>
            </tr>
            <?php if (!empty($titpar['latitude']) && !empty($titpar['longitude'])): ?>
            <tr>
                <td class="label">Koordinat</td><td class="colon">:</td>
                <td><?= $titpar['latitude'] ?>, <?= $titpar['longitude'] ?></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<div class="section-title">III. Dokumentasi & Peta Lokasi</div>
<div class="photo-table">
    <div class="photo-cell">
        <div class="photo-box">
            <?php if (!empty($titpar['foto_tampak_depan'])): ?>
                <img src="<?= base_url('uploads/foto_tampak_depan/' . $titpar['foto_tampak_depan']) ?>" alt="Tampak Depan">
            <?php else: ?>
                <div class="no-img">Tidak ada foto</div>
            <?php endif; ?>
            <div class="caption">Foto Tampak Depan</div>
        </div>
    </div>
    <div class="photo-cell">
        <div class="photo-box">
            <?php if (!empty($titpar['foto_tampak_samping'])): ?>
                <img src="<?= base_url('uploads/foto_tampak_samping/' . $titpar['foto_tampak_samping']) ?>" alt="Tampak Samping">
            <?php else: ?>
                <div class="no-img">Tidak ada foto</div>
            <?php endif; ?>
            <div class="caption">Foto Tampak Samping</div>
        </div>
    </div>
    
    <div class="photo-cell">
        <div class="photo-box">
            <?php if (!empty($titpar['latitude']) && !empty($titpar['longitude'])): ?>
                <?php 
                $lat = $titpar['latitude'];
                $lon = $titpar['longitude'];
                // GANTI tulisan 'YOUR_GEOAPIFY_API_KEY' dengan Key gratisan Anda
                $apiKey = 'fa397167411f46db8590f41310164a1e'; 
                ?>
                <img src="https://maps.geoapify.com/v1/staticmap?style=osm-carto&width=300&height=200&center=lonlat:<?= $lon ?>,<?= $lat ?>&zoom=16&marker=lonlat:<?= $lon ?>,<?= $lat ?>;color:%23ff0000;size:medium&apiKey=<?= $apiKey ?>" alt="Peta Lokasi">
            <?php else: ?>
                <div class="no-img">Koordinat tidak tersedia</div>
            <?php endif; ?>
            <div class="caption">Peta Lokasi (OSM)</div>
        </div>
    </div>
</div>

<div class="section-title">IV. Keterangan Kelayakan</div>
<div class="keterangan-text">
    <strong>Catatan Pemeriksa:</strong> 
    <?= !empty($titpar['catatan'])
        ? nl2br(esc($titpar['catatan']))
        : 'Titik lokasi parkir tersebut telah memenuhi persyaratan dan dinyatakan layak untuk digunakan sebagai tempat perparkiran sesuai dengan ketentuan yang berlaku.' ?>
</div>

<p class="narasi">
    Berdasarkan hasil kelayakan administrasi dan survey lapangan oleh Tim Dishub Kota Malang, maka lokasi sebagaimana dimaksud pada poin II disetujui untuk diterbitkan Surat Penunjukan Petugas Parkir dan Kartu Tanda Anggota (KTA) serta berlaku selama 1 (satu) tahun sejak tanggal diterbitkan.
</p>
<p class="narasi">
    Demikian persetujuan ini dibuat sebagai dasar pencetakan fisik atau digital Surat Penunjukan Petugas Parkir dan Kartu Tanda Anggota (KTA). Apabila di kemudian hari terdapat kekeliruan, maka akan dilakukan perbaikan sebagaimana mestinya.
</p>

<div class="signature-block">
    <table>
        <tr>
            <td></td>
            <td class="right-align">
                <?php
                $bulan = [
                    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', 
                    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', 
                    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                ];
                echo 'Malang, ' . date('d') . ' ' . $bulan[date('m')] . ' ' . date('Y');
                ?>
            </td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td class="right-align">a.n. KEPALA DINAS PERHUBUNGAN<br>Kepala Bidang Pengelolaan Perparkiran</td>
        </tr>
        <tr>
            <td></td>
            <td class="signature-space"></td>
        </tr>
        <tr>
            <td></td>
            <td class="right-align">
                <strong><u>RAHMAT HIDAYAT, S.T., M.Ling</u></strong><br>
                Pembina<br>
                <span style="font-size:11px;">NIP. 19740615 200312 1 004</span>
            </td>
        </tr>
    </table>
</div>

</body>
</html>