<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Penunjukan Petugas Parkir</title>
    <style>
        @page { 
            size: A4; 
            margin: 1.2cm 1.5cm; 
        }
        *, *::before, *::after { 
            box-sizing: border-box; 
        }
        body {
            font-family: 'Times New Roman', serif;
            font-size: 14px;
            color: #000;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }
        
        /* Kop Surat Dinas Dengan Logo (Menggunakan Tabel Struktur) */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 3px double #000;
            padding-bottom: 4px;
            margin-bottom: 8px;
        }
        .header-table td {
            padding: 0;
            vertical-align: middle;
        }
        .header-logo-cell {
            width: 100px; /* Alokasi ruang untuk logo */
            text-align: left;
            padding-bottom: 4px;
        }
        .header-logo {
            width: 100px; /* Ukuran lebar logo proporsional */
            height: auto;
            display: block;
        }
        .header-text-cell {
            text-align: center;
            padding-right: 60px; /* Menyeimbangkan posisi teks agar tetap tepat di tengah */
            padding-bottom: 4px;
        }
        .header-text-cell h4 { font-size: 13px; margin: 0 0 1px 0; font-weight: bold; letter-spacing: 0.5px; }
        .header-text-cell h5 { font-size: 14px; margin: 0 0 2px 0; font-weight: bold; letter-spacing: 0.5px; }
        .header-text-cell p { font-size: 9.5px; margin: 1px 0; font-style: normal; }
        
        .title {
            text-align: center;
            font-weight: bold;
            font-size: 12px;
            margin: 8px 0 2px 0;
            text-transform: uppercase;
        }
        .nomor { 
            text-align: center; 
            font-size: 11px; 
            margin-bottom: 10px; 
        }
        
        /* Layout Struktur Matriks (Dasar, Memperhatikan, Menunjuk, Untuk) */
        table.structure-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        table.structure-table td {
            padding: 1px 0;
            vertical-align: top;
        }
        table.structure-table td.main-label {
            width: 100px;
            font-weight: bold;
        }
        table.structure-table td.colon {
            width: 12px;
            text-align: center;
        }
        
        /* List di dalam konten struktural */
        table.list-table {
            width: 100%;
            border-collapse: collapse;
        }
        table.list-table td {
            padding: 1px 0;
            vertical-align: top;
        }
        table.list-table td.num {
            width: 18px;
        }
        table.list-table td.alpha {
            width: 18px;
            padding-left: 3px;
        }
        
        /* Sub-tabel untuk bagian 'MENUNJUK' */
        table.inner-info {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1px;
        }
        table.inner-info td {
            padding: 1.5px 0;
            vertical-align: top;
        }
        table.inner-info td.sub-label { width: 130px; }
        table.inner-info td.sub-colon { width: 12px; text-align: center; }

        /* Blok Tanda Tangan Super Compact */
        .signature-block {
            margin-top: 10px;
            width: 100%;
            page-break-inside: avoid;
        }
        .signature-block table { width: 100%; border-collapse: collapse; }
        .signature-block td { width: 50%; vertical-align: top; font-size: 11px; }
        .signature-block td.right-align { text-align: left; padding-left: 65%; }
        .signature-space { height: 40px; }
        
        /* Halaman Lampiran */
        .page-break { page-break-before: always; }
        .lampiran-title {
            text-align: center;
            font-weight: bold;
            font-size: 11.5px;
            margin-bottom: 10px;
            line-height: 1.3;
        }
        
        table.anggota-table { 
            width: 100%; 
            border-collapse: collapse; 
            font-size: 10.5px; 
            margin-top: 6px;
        }
        table.anggota-table th, table.anggota-table td {
            border: 1px solid #000;
            padding: 4px 5px;
        }
        table.anggota-table th { background: #e9e9e9; text-align: center; font-weight: bold; }
        table.anggota-table td.center { text-align: center; }
        
        .no-print { text-align: center; margin-bottom: 10px; }
        .no-print button {
            padding: 5px 14px; font-size: 12px; cursor: pointer;
            background: #28a745; color: #fff; border: none; border-radius: 4px;
        }
        @media print { 
            .no-print { display: none; } 
            @page { margin: 1.2cm 1.5cm; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()">
        🖨️ Cetak Surat Penunjukan (SP) - Hemat Ruang
    </button>
</div>

<table class="header-table">
    <tr>
        <td class="header-logo-cell">
            <img class="header-logo" src="<?php echo base_url() ?>images/logomalang.png" alt="Logo Kota Malang">
        </td>
        <td class="header-text-cell">
            <h4>PEMERINTAH KOTA MALANG</h4>
            <h5>DINAS PERHUBUNGAN</h5>
            <p>Jalan Raden Intan No 1, Kec. Blimbing, Kota Malang, Jawa Timur 65126, Telepon (0341) 491140, Faksimile (0341) 411626</p>
            <p>Laman dishub.malangkota.go.id, Pos-el dishub@malangkota.go.id</p>
        </td>
    </tr>
</table>

<div class="title">SURAT PENUNJUKAN (SP) PETUGAS PARKIR</div>
<div class="nomor">Nomor : 800.1.11.1/ &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; /KTA/35.73.410/2026</div>

<table class="structure-table">
    <tr>
        <td class="main-label">Dasar Hukum</td>
        <td class="colon">:</td>
        <td>
            <table class="list-table">
                <tr><td class="num">1.</td><td>Peraturan Daerah Kota Malang No. 7 Tahun 2016 tentang Pembentukan dan Susunan Perangkat Daerah;</td></tr>
                <tr><td class="num">2.</td><td>Peraturan Daerah Kota Malang No. 4 Tahun 2023 tentang Pajak Daerah dan Retribusi Daerah sebagaimana telah diubah dengan Peraturan Daerah Kota Malang No. 1 Tahun 2025;</td></tr>
                <tr><td class="num">3.</td><td>Peraturan Daerah Kota Malang No. 2 Tahun 2026 tentang Penyelenggaraan Perparkiran;</td></tr>
                <tr><td class="num">4.</td><td>Peraturan Walikota Malang No. 71 Tahun 2019 tentang Kedudukan, Susunan Organisasi, Tugas dan Fungsi serta Tata Kerja Dinas Perhubungan Kota Malang.</td></tr>
            </table>
        </td>
    </tr>
</table>

<table class="structure-table">
    <tr>
        <td class="main-label">Memperhatikan</td>
        <td class="colon">:</td>
        <td>
            <table class="list-table">
                <tr><td class="num">1.</td><td>Permohonan pengajuan Surat Penunjukan dan Kartu Tanda Anggota (KTA) dari Koordinator Petugas Parkir serta hasil verifikasi Tim Dinas Perhubungan.</td></tr>
            </table>
        </td>
    </tr>
</table>

<div style="text-align: center; font-weight: bold; margin: 5px 0; letter-spacing: 1px;">MENUNJUK</div>

<table class="structure-table">
    <tr>
        <td class="main-label">Kepada</td>
        <td class="colon">:</td>
        <td>
            <table class="inner-info">
                <tr><td class="sub-label">Koordinator dan Anggota</td><td class="sub-colon">:</td><td>Terlampir</td></tr>
                <tr><td class="sub-label">Nama titik parkir</td><td class="sub-colon">:</td><td><?= !empty($titpars) ? esc(implode(', ', array_map(static fn($t) => $t['titpar_namatempat'] ?? '-', $titpars))) : '-' ?></td></tr>
                <tr><td class="sub-label">Alamat lokasi parkir</td><td class="sub-colon">:</td><td><?= !empty($titpars) ? esc(implode('; ', array_map(static fn($t) => $t['titpar_lokasi'] ?? '-', $titpars))) : '-' ?></td></tr>
                <tr><td class="sub-label">Lokasi Tempat Parkir</td><td class="sub-colon">:</td><td>Terlampir</td></tr>
                <tr><td class="sub-label">Titik koordinat</td><td class="sub-colon">:</td><td><?= !empty($titpars) ? esc(implode('; ', array_map(static fn($t) => ($t['titpar_latitude'] ?? '-') . ', ' . ($t['titpar_longitude'] ?? '-'), $titpars))) : '-' ?></td></tr>
                <tr><td class="sub-label">Jam operasional</td><td class="sub-colon">:</td><td><?= !empty($titpars) ? esc(implode('; ', array_map(static fn($t) => $t['titpar_alihfungsi'] ?? '-', $titpars))) : '-' ?></td></tr>
                <tr><td class="sub-label">Jenis kendaraan parkir</td><td class="sub-colon">:</td><td><strong>Mobil dan/atau Motor</strong></td></tr>
            </table>
        </td>
    </tr>
</table>

<table class="structure-table">
    <tr>
        <td class="main-label">Hak &amp; Kewajiban</td>
        <td class="colon">:</td>
        <td>
            <table class="list-table">
                <tr><td colspan="2">Dengan Kewajiban dan Hak kepada Koordinator dan Anggota Petugas Parkir yaitu:</td></tr>
                <tr><td colspan="2"><strong>A. Kewajiban</strong></td></tr>
                <tr><td class="num">1.</td><td>Koordinator bertanggung jawab terhadap anggota dalam melaksanakan tugas di tempat parkir dan pembayaran retribusi parkir ke RKUD melalui transfer VA dengan imbal jasa pada koordinator sebesar …% dan Pemerintah Daerah sebesar …% atau sesuai ketentuan dari realisasi pembayaran retribusi bruto.</td></tr>
                <tr><td class="num">2.</td><td>Koordinator bertanggung jawab terhadap kehilangan dan/atau kerusakan kendaraan di tempat parkir.</td></tr>
                <tr><td class="num">3.</td><td>Koordinator dan anggota dalam melaksanakan tugas parkir mengikuti ketentuan:
                        <table class="list-table">
                            <tr><td class="alpha">a.</td><td>Menggunakan atribut/rompi dan membawa KTA dari Dinas Perhubungan;</td></tr>
                            <tr><td class="alpha">b.</td><td>Memungut retribusi parkir yang besaran tarifnya sesuai ketentuan;</td></tr>
                            <tr><td class="alpha">c.</td><td>Mengarahkan, menata, dan menempatkan kendaraan di tempat parkir;</td></tr>
                            <tr><td class="alpha">d.</td><td>Dilarang menggunakan tempat parkir selain untuk peruntukan parkir kendaraan;</td></tr>
                            <tr><td class="alpha">e.</td><td>Memberikan karcis parkir kepada pengguna jasa parkir setiap kali parkir;</td></tr>
                            <tr><td class="alpha">f.</td><td>Menjaga kebersihan, kenyamanan, dan keamanan tempat parkir.</td></tr>
                        </table>
                    </td>
                </tr>
                <tr><td class="num">4.</td><td>Dilarang melakukan jual beli lahan/titik parkir dan mengganti nama/orang Surat Penunjukan dan KTA pada orang lain;</td></tr>
                <tr><td class="num">5.</td><td>Tidak menuntut apabila di kemudian hari titik/tempat parkir dimaksud digunakan untuk fasilitas umum atau fungsi jalan oleh Pemerintah Daerah;</td></tr>
                <tr><td class="num">6.</td><td>Dikenakan sanksi apabila terdapat pelanggaran parkir berdasarkan Peraturan Daerah Kota Malang No. 2 Tahun 2026 tentang Penyelenggaraan Perparkiran.</td></tr>
                <tr><td colspan="2"><strong>B. Hak</strong></td></tr>
                <tr><td class="num">1.</td><td>Mendapatkan Surat Penunjukan dan KTA, karcis parkir dan atribut/rompi parkir;</td></tr>
                <tr><td class="num">2.</td><td>Pemungutan retribusi parkir kepada pengguna jasa di titik/tempat parkir dimaksud.</td></tr>
                <tr><td colspan="2">Surat penunjukan ini satu kesatuan dengan lampiran dan KTA yang berlaku 1 (satu) tahun sejak ditandatangani. Apabila ada kekeliruan/kesalahan akan diperbaiki sebagaimana mestinya.</td></tr>
            </table>
        </td>
    </tr>
</table>

<div class="signature-block" style="width:100%">
    <table style="width:100%">
        <tr>
            <td class="right-align">Dikeluarkan di : Malang</td>
        </tr>
        <tr>
           <td class="right-align">Pada tanggal : <?= date('d') . ' ' . ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'][(int)date('m') - 1] . ' ' . date('Y') ?></td>
        </tr>
        <tr>
            <td class="right-align" style="font-weight: bold; padding-top: 10px;">
                KEPALA DINAS PERHUBUNGAN<br>KOTA MALANG,
            </td>
        </tr>
        <tr>
            <td class="signature-space"></td>
        </tr>
        <tr>
            <td class="right-align">
                <strong><u>Drs. R. WIDJAJASALEH PUTRA</u></strong><br>
                Pembina Utama Muda<br>
                <span style="font-size:10px;">NIP. 19661119 1986021 002</span>
            </td>
        </tr>
    </table>
</div>

<div class="page-break"></div>

<table class="header-table">
    <tr>
        <td class="header-logo-cell">
            <img class="header-logo" src="https://upload.wikimedia.org/wikipedia/commons/b/b2/Lambang_Kota_Malang.svg" alt="Logo Kota Malang">
        </td>
        <td class="header-text-cell">
            <h4>PEMERINTAH KOTA MALANG</h4>
            <h5>DINAS PERHUBUNGAN</h5>
            <p>Jalan Raden Intan No 1, Kec. Blimbing, Kota Malang, Jawa Timur 65126, Telepon (0341) 491140, Faksimile (0341) 411626</p>
            <p>Laman dishub.malangkota.go.id, Pos-el dishub@malangkota.go.id</p>
        </td>
    </tr>
</table>

<div class="lampiran-title">
    LAMPIRAN<br>
    SURAT PENUNJUKAN (SP) PETUGAS PARKIR<br>
    Nomor : 800.1.11.1/ &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; /KTA/35.73.410/2026
</div>

<p style="font-size: 11px; margin-bottom: 4px;">Daftar Anggota Petugas Parkir:</p>

<p style="font-size: 14px; margin: 0 0 8px 0;"><strong>Titik Parkir:</strong>
    <?php if (!empty($titpars)): ?>
        <?= esc(implode(', ', array_map(static fn($t) => $t['titpar_namatempat'] ?? '-', $titpars))) ?>
    <?php else: ?>
        -
    <?php endif; ?>
</p>

<table class="anggota-table">
    <thead>
        <tr>
            <th width="5%">No.</th>
            <th width="25%">Nama Petugas</th>
            <th width="20%">No. KTP</th>
            <th width="15%">Tanggal Lahir</th>
            <th width="35%">Alamat</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($coordinatorGroups)): ?>
            <?php $number = 1; ?>
            <?php foreach ($coordinatorGroups as $group): ?>
            <tr style="background:#f3f3f3;">
                <td class="center"><?= $number++ ?></td>
                <td><strong><?= esc($group['coordinator']['anggota_nama']) ?> (Koordinator)</strong></td>
                <td><?= esc($group['coordinator']['anggota_noktp'] ?: '-') ?></td>
                <td><?= !empty($group['coordinator']['anggota_tgllhr']) ? date('d-m-Y', strtotime($group['coordinator']['anggota_tgllhr'])) : '-' ?></td>
                <td><?= esc($group['coordinator']['anggota_alamat'] ?: '-') ?></td>
            </tr>
            <?php foreach ($group['members'] as $a): ?>
            <tr>
                <td class="center"><?= $number++ ?></td>
                <td style="padding-left: 18px;">↳ <?= esc($a['anggota_nama']) ?></td>
                <td><?= esc($a['anggota_noktp'] ?: '-') ?></td>
                <td><?= !empty($a['tgl_lahir']) ? date('d-m-Y', strtotime($a['tgl_lahir'])) : '-' ?></td>
                <td><?= esc($a['alamat'] ?: '-') ?></td>
            </tr>
            <?php endforeach; ?>
            <?php endforeach; ?>
        <?php else: ?>
        <tr>
            <td class="center">1</td>
            <td><strong><?= esc($register['anggota_nama']) ?> (Koor)</strong></td>
            <td><?= esc($register['anggota_noktp']) ?></td>
            <td><?= !empty($register['anggota_tgllhr']) ? esc($register['anggota_tgllhr']) : '-' ?></td>
            <td><?= esc($register['anggota_alamat'] ?? '-') ?></td>
        </tr>
        <?php if (!empty($anggota)): ?>
            <?php foreach ($anggota as $i => $a): ?>
            <tr>
                <td class="center"><?= $i + 2 ?></td>
                <td><?= esc($a['anggota_nama']) ?></td>
                <td><?= esc($a['anggota_noktp']) ?></td>
                <td><?= !empty($a['tgl_lahir']) ? date('d-m-Y', strtotime($a['tgl_lahir'])) : '-' ?></td>
                <td><?= esc($a['alamat'] ?? '-') ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        <?php endif; ?>
    </tbody>
</table>

<div class="signature-block">
    <table>
        <tr>
            <td class="right-align" style="font-weight: bold; padding-top: 10px;">
                KEPALA DINAS PERHUBUNGAN<br>KOTA MALANG,
            </td>
        </tr>
        <tr>
            <td class="signature-space"></td>
        </tr>
        <tr>
            <td class="right-align">
                <strong><u>Drs. R. WIDJAJASALEH PUTRA</u></strong><br>
                Pembina Utama Muda<br>
                <span style="font-size:10px;">NIP. 19661119 1986021 002</span>
            </td>
        </tr>
    </table>
</div>

</body>
</html>
