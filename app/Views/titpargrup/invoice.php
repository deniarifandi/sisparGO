<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bukti Setoran Retribusi</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 30px;
            color: #000;
        }
        .container {
            width: 700px;
            margin: auto;
            border: 1px solid #000;
            padding: 25px;
        }
        .kop {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .kop h2 {
            margin: 0;
            font-size: 20px;
        }
        .kop small {
            font-size: 13px;
        }
        .title {
            text-align: center;
            font-size: 18px;
            margin-bottom: 25px;
            text-decoration: underline;
        }
        table {
            width: 100%;
            font-size: 15px;
            margin-bottom: 20px;
        }
        table td {
            padding: 6px;
        }
        .right {
            text-align: right;
        }
        .ttd {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            font-size: 14px;
        }
        .terbilang {
            font-style: italic;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="kop">
        <h2>PEMERINTAH KOTA MALANG<br>DINAS PERHUBUNGAN</h2>
        <small>Jl. Raden Intan No.1, Malang - Telp. (0341) 123456</small>
    </div>

    <div class="title">BUKTI SETORAN RETRIBUSI PARKIR</div>

    <table>
        <tr>
            <td><strong>No. Invoice</strong></td>
            <td>: <?= esc($invoiceNumber) ?></td>
            <td class="right"><strong>Tanggal</strong></td>
            <td>: <?= esc($tanggal) ?></td>
        </tr>
        <tr>
            <td><strong>Nama Jukir</strong></td>
            <td>: <?= esc($nama) ?></td>
            <td class="right"><strong>Lokasi Titik Parkir</strong></td>
            <td>: <?= esc($titpar) ?></td>
        </tr>    
        <tr>
            <td><strong>Jumlah Setoran</strong></td>
            <td>: Rp <?= number_format($amount, 0, ',', '.') ?></td>
            <td class="right"><strong>Alamat</strong></td>
            <td>: <?= esc($alamat) ?></td>
        </tr>
    </table>

    <div class="terbilang">
        <strong>Terbilang:</strong> <?= ucwords(terbilang($amount)) ?> rupiah.
    </div>

   
</div>
</body>
</html>

<script type="text/javascript">
  
  function terbilang($angka) {
    $angka = abs($angka);
    $huruf = ["", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas"];
    $temp = "";

    if ($angka < 12) {
        $temp = " " . $huruf[$angka];
    } else if ($angka < 20) {
        $temp = terbilang($angka - 10) . " belas";
    } else if ($angka < 100) {
        $temp = terbilang($angka / 10) . " puluh" . terbilang($angka % 10);
    } else if ($angka < 200) {
        $temp = " seratus" . terbilang($angka - 100);
    } else if ($angka < 1000) {
        $temp = terbilang($angka / 100) . " ratus" . terbilang($angka % 100);
    } else if ($angka < 2000) {
        $temp = " seribu" . terbilang($angka - 1000);
    } else if ($angka < 1000000) {
        $temp = terbilang($angka / 1000) . " ribu" . terbilang($angka % 1000);
    } else if ($angka < 1000000000) {
        $temp = terbilang($angka / 1000000) . " juta" . terbilang($angka % 1000000);
    }

    return trim($temp);
}
</script>