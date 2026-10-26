<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Karcis Parkir Invoice</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      background: #fff;
      color: #333;
      font-size: 12px;
    }

    .invoice {
      max-width: 650px;
      margin: auto;
      padding: 15px;
      border: 1px solid #ddd;
      border-radius: 8px;
    }

    h2, h3 {
      text-align: center;
      color: #007bff;
      margin: 5px 0;
      font-weight: 600;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin: 8px 0;
    }

    table th, table td {
      border: 1px solid #ddd;
      padding: 6px 8px;
      vertical-align: middle;
      font-size: 12px;
    }

    table th {
      background-color: #007bff;
      color: #fff;
      font-weight: 600;
      text-align: left;
    }

    table td.text-right {
      text-align: right;
    }

    table tr:nth-child(even) td {
      background-color: #f9f9f9;
    }

    .totals-row th {
      background-color: #f1f1f1;
    }

    .invoice-header td {
      padding: 4px 8px;
    }

    .signature {
      display: flex;
      justify-content: space-between;
      margin-top: 30px;
    }

    .signature div {
      width: 45%;
      text-align: center;
    }

    .signature .name {
      margin-top: 60px;
      font-weight: bold;
      border-top: 1px solid #333;
    }

    .footer {
      text-align: center;
      margin-top: 15px;
      font-size: 10px;
      color: #777;
    }

    .jukir-receipt {
      margin-top: 20px;
      padding-top: 8px;
      border-top: 1px dashed #333;
      break-inside: avoid;
      page-break-inside: avoid;
    }

    .cut-label {
      text-align: center;
      font-size: 10px;
      margin-bottom: 8px;
    }

    .receipt-details {
      line-height: 1.5;
      overflow-wrap: anywhere;
    }

    .receipt-signature {
      text-align: right;
      padding: 4px 30px 0 0;
      line-height: 1.4;
    }

    @page {
      size: A4 portrait;
      margin: 10mm;
    }

    @media print {
      .invoice {
        box-sizing: border-box;
        max-width: none;
        width: 100%;
        padding: 0;
        border: 0;
        border-radius: 0;
        min-height: 276mm;
        display: flex;
        flex-direction: column;
        break-inside: avoid;
        page-break-inside: avoid;
      }

      .invoice > * {
        flex-shrink: 0;
      }

      .jukir-receipt table th, .jukir-receipt table td {
        padding: 3px 5px;
        font-size: 10px;
      }

      .jukir-receipt h3 { font-size: 13px; }

      body {
        margin: 0;
        background: #fff;
        font-size: 11px;
        line-height: 1.3;
      }

      table {
        margin: 5px 0;
      }

      table th, table td {
        font-size: 11px;
        padding: 5px 6px;
        overflow-wrap: anywhere;
      }

      .invoice-header td {
        padding: 5px 6px;
      }

      h2, h3 {
        margin: 2px 0;
        line-height: 1.2;
      }

      h2 { font-size: 16px; }
      h3 { font-size: 12px; }

      .main-signatures td {
        height: 100px !important;
      }

      .footer {
        margin-top: 5px;
        font-size: 9px;
      }

      .jukir-receipt {
        margin-top: auto;
        padding-top: 3mm;
      }

      .cut-label {
        margin-bottom: 4px;
        font-size: 9px;
      }

      .receipt-details, .receipt-signature {
        line-height: 1.25;
      }

      .receipt-signature {
        min-height: 22mm;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        align-items: flex-end;
      }

      .receipt-signature br {
        display: none;
      }
    }
  </style>
</head>
<body>

  <div class="invoice">

    <h2>Dokumen Penerimaan Karcis Parkir</h2>

    <!-- Header Info -->
    <table class="invoice-header">
      <tr>
        <th>Nama Jukir:</th>
        <td><?= $tiket->va_owner_nama ?></td>
        <th>No. VA:</th>
        <td><?= $tiket->va_owner_va ?></td>
      </tr>
      <tr>
        <th>Tanggal:</th>
        <td colspan="3">  <?= date('d F Y', strtotime($tiket->tiket_tanggal)) ?></td>

      </tr>
      <tr>
        <th>Titik Parkir:</th>
        <td><?= $tiket->titpar_namatempat ?></td>
        <th>Alamat:</th>
        <td ><?= $tiket->titpar_lokasi ?></td>
      </tr>
      <tr>

      </tr>
    </table>

    <!-- Parkir Umum -->
    <h3>Parkir Umum</h3>
    <table>
      <thead>
        <tr>
          <th>No</th>
          <th>Karcis</th>
          <th class="text-right">Nilai</th>
          <th class="text-right">Jumlah</th>
          <th>No. Seri</th>
          <th class="text-right">Total</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>1</td><td>Motor</td><td class="text-right">Rp 2.000</td><td class="text-right"><?= $tiket->tiket_motor ?></td><td><?= $tiket->tiket_nomor_motor_awal ?> - <?= $tiket->tiket_nomor_motor_akhir ?></td><td class="text-right"><?= 'Rp ' . number_format($tiket->tiket_motor * 2000, 0, ',', '.') ?>
        </td>
      </tr>
      <tr>
       <td>1</td><td>Mobil</td><td class="text-right">Rp 3.000</td><td class="text-right"><?= $tiket->tiket_mobil ?></td><td><?= $tiket->tiket_nomor_mobil_awal ?> - <?= $tiket->tiket_nomor_mobil_akhir ?></td><td class="text-right"><?= 'Rp ' . number_format($tiket->tiket_mobil * 3000, 0, ',', '.') ?>
     </td>
   </tr>
   <tr>
     <td>1</td><td>Truk</td><td class="text-right">Rp 5.000</td><td class="text-right"><?= $tiket->tiket_truk ?></td><td><?= $tiket->tiket_nomor_truk_awal ?> - <?= $tiket->tiket_nomor_truk_akhir ?></td><td class="text-right"><?= 'Rp ' . number_format($tiket->tiket_truk * 5000, 0, ',', '.') ?>
   </td>
 </tr>
 <tr class="totals-row">
  <th colspan="5" style="color: black;">Total Parkir Umum</th>
  <th class="text-right" style="color: black;">
    <?= 'Rp ' . number_format(
      ($tiket->tiket_motor * 2000) +
      ($tiket->tiket_mobil * 3000) +
      ($tiket->tiket_truk * 5000),
      0,
      ',',
      '.'
    ) ?>
  </th>
</tr>
</tbody>
</table>

<!-- Parkir Insidentil -->
<h3>Parkir Insidentil</h3>
<table>
  <thead>
    <tr>
      <th>No</th>
      <th>Karcis</th>
      <th class="text-right">Nilai</th>
      <th class="text-right">Jumlah</th>
      <th>No. Seri</th>
      <th class="text-right">Total</th>
    </tr>
  </thead>
   <tbody>
        <tr>
          <td>1</td><td>Motor</td><td class="text-right">Rp 3.000</td><td class="text-right"><?= $tiket->tiket_motor_in ?></td><td><?= $tiket->tiket_nomor_motor_in_awal ?> - <?= $tiket->tiket_nomor_motor_in_akhir ?></td><td class="text-right"><?= 'Rp ' . number_format($tiket->tiket_motor_in * 3000, 0, ',', '.') ?>
        </td>
      </tr>
      <tr>
       <td>1</td><td>Mobil</td><td class="text-right">Rp 5.000</td><td class="text-right"><?= $tiket->tiket_mobil_in ?></td><td><?= $tiket->tiket_nomor_mobil_in_awal ?> - <?= $tiket->tiket_nomor_mobil_in_akhir ?></td><td class="text-right"><?= 'Rp ' . number_format($tiket->tiket_mobil_in * 5000, 0, ',', '.') ?>
     </td>
   </tr>
   <tr>
     <td>1</td><td>Truk</td><td class="text-right">Rp 10.000</td><td class="text-right"><?= $tiket->tiket_truk_in ?></td><td><?= $tiket->tiket_nomor_truk_in_awal ?> - <?= $tiket->tiket_nomor_truk_in_akhir ?></td><td class="text-right"><?= 'Rp ' . number_format($tiket->tiket_truk_in * 10000, 0, ',', '.') ?>
   </td>
 </tr>
 <tr class="totals-row">
  <th colspan="5" style="color: black;">Total Parkir Umum</th>
  <th class="text-right" style="color: black;">
    <?= 'Rp ' . number_format(
      ($tiket->tiket_motor_in * 3000) +
      ($tiket->tiket_mobil_in * 5000) +
      ($tiket->tiket_truk_in * 10000),
      0,
      ',',
      '.'
    ) ?>
  </th>
</tr>
</tbody>
</table>

<!-- Total Keseluruhan -->
<h3>Total Nilai</h3>
<table>
  <tr>
    <th>Parkir Umum</th>
    <th>Insidentil</th>
    <th>Total</th>
  </tr>
  <tr>
    <?php 
      $total_umum = ($tiket->tiket_motor * 2000) + ($tiket->tiket_mobil * 3000) + ($tiket->tiket_truk * 5000);
      $total_in = ($tiket->tiket_motor_in * 3000) + ($tiket->tiket_mobil_in * 5000) + ($tiket->tiket_truk_in * 10000)
      ;      
      $grand_total = $total_umum + $total_in;
    ?>
    <td class="text-right"><?= 'Rp ' . number_format($total_umum, 0, ',', '.') ?></td>
    <td class="text-right"><?= 'Rp ' . number_format($total_in, 0, ',', '.') ?></td>
    <td class="text-right"><?= 'Rp ' . number_format($grand_total, 0, ',', '.') ?></td>
  </tr>
</table>

<table class="main-signatures">
  <tr>
    <th>Admin</th>
    <th>Juru Parkir</th>

  </tr>
  <tr>
  <td style="width:50%; text-align:center; vertical-align:bottom; height:100px;">
    
  </td>
  <td style="width:50%; text-align:center; vertical-align:bottom; height:100px;">
    <?= $tiket->va_owner_nama ?>
  </td>
</tr>

</table>

<div class="footer">
  &copy; 2025 Sisparma - Dinas Perhubungan Kota Malang • Dicetak otomatis
</div>

<section class="jukir-receipt" aria-label="Bukti penerimaan karcis untuk juru parkir">
  <div class="cut-label">Gunting sepanjang garis putus-putus — Bagian untuk Juru Parkir</div>
  <h3>Bukti Penerimaan Karcis Parkir</h3>
  <div class="receipt-details">
    <strong>No. Bukti:</strong> <?= esc($tiket->tiket_id) ?>
    &nbsp; | &nbsp; <strong>Tanggal:</strong> <?= date('d-m-Y', strtotime($tiket->tiket_tanggal)) ?><br>
    <strong>Juru Parkir:</strong> <?= esc($tiket->va_owner_nama) ?>
    &nbsp; | &nbsp; <strong>No. VA:</strong> <?= esc($tiket->va_owner_va) ?><br>
    <strong>Titik Parkir:</strong> <?= esc($tiket->titpar_namatempat) ?> — <?= esc($tiket->titpar_lokasi) ?>
  </div>
  <table>
    <thead>
      <tr><th>Jenis Karcis</th><th>Jumlah</th><th>No. Seri</th><th>Nilai</th></tr>
    </thead>
    <tbody>
      <?php foreach ([
        ['Motor Umum', 'motor', 2000],
        ['Mobil Umum', 'mobil', 3000],
        ['Truk Umum', 'truk', 5000],
        ['Motor Insidentil', 'motor_in', 3000],
        ['Mobil Insidentil', 'mobil_in', 5000],
        ['Truk Insidentil', 'truk_in', 10000],
      ] as [$label, $jenis, $tarif]): ?>
      <tr>
        <td><?= esc($label) ?></td>
        <td class="text-right"><?= esc($tiket->{'tiket_' . $jenis}) ?></td>
        <td><?= esc($tiket->{'tiket_nomor_' . $jenis . '_awal'}) ?> - <?= esc($tiket->{'tiket_nomor_' . $jenis . '_akhir'}) ?></td>
        <td class="text-right">Rp <?= number_format($tiket->{'tiket_' . $jenis} * $tarif, 0, ',', '.') ?></td>
      </tr>
      <?php endforeach; ?>
      <tr><td colspan="3"><strong>Total Nilai Karcis</strong></td><td class="text-right"><strong>Rp <?= number_format($grand_total, 0, ',', '.') ?></strong></td></tr>
    </tbody>
  </table>
  <div class="receipt-signature"><span>Petugas / Admin</span><br><br><br><span>(____________________)</span></div>
</section>

</div>

</body>
</html>
