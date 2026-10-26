<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class ImportController extends BaseController
{

    public function import(){
        return view('import.php');
    }

public function importCsv()
{
    $file = $this->request->getFile('csv');

    if (!$file || !$file->isValid()) {
        return redirect()->back()->with('error', 'File tidak valid');
    }

    $extension = strtolower($file->getClientExtension());
    if (!in_array($extension, ['csv', 'xlsx'], true)) {
        return redirect()->back()->with('error', 'File harus CSV atau XLSX');
    }

    if ($extension === 'xlsx') {
        try {
            $rows = $this->readXlsxRows($file->getTempName());
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal membaca XLSX: ' . $e->getMessage());
        }
    } else {
        $handle = fopen($file->getTempName(), 'r');
        if ($handle === false) {
            return redirect()->back()->with('error', 'Gagal membaca file');
        }
        $rows = [];
        while (($csvRow = fgetcsv($handle, 1000, ',')) !== false) {
            $rows[] = $csvRow;
        }
        fclose($handle);
    }

    $db = \Config\Database::connect();
    $builder = $db->table('transaksi');

    /**
     * Existing data
     */
    $existingRows = $builder
        ->select("
            transaksi_va,
            transaksi_nominal,
            DATE(transaksi_tanggal) as tgl,
            coreReferenceNo
        ")
        ->get()
        ->getResultArray();

    $existingMap = [];

    foreach ($existingRows as $r) {

        $duplicateKey =
            trim($r['transaksi_va']) . '|' .
            (int) $r['transaksi_nominal'] . '|' .
            $r['tgl'] . '|' .
            trim($r['coreReferenceNo']);

        $existingMap[$duplicateKey] = true;
    }

    $rowNumber = 0;

    $dataBatch = [];

    // Store skipped rows
    $skippedRows = [];

    // Count reasons
    $duplicateCount = 0;
    $invalidDataCount = 0;
    $invalidDateCount = 0;

    foreach ($rows as $row) {

        $rowNumber++;

        // Skip header
        if ($rowNumber === 1) {
            continue;
        }

        // Tanggal XLSX sering disimpan sebagai serial Excel, bukan teks d/m/Y.
        if ($extension === 'xlsx' && isset($row[4]) && is_numeric($row[4])) {
            $excelDate = (new \DateTime('1899-12-30'))->modify('+' . (int) $row[4] . ' days');
            $row[4] = $excelDate->format('d/m/Y');
        }

        /**
         * Check required data
         */
        if (
            empty($row[0]) ||
            empty($row[2]) ||
            empty($row[4])
        ) {

            $skippedRows[] = [
                'row'    => $rowNumber,
                'va'     => $row[0] ?? '',
                'nama'   => $row[1] ?? '',
                'ref'    => $row[2] ?? '',
                'nominal'=> $row[3] ?? '',
                'tanggal'=> $row[4] ?? '',
                'reason' => 'Data wajib kosong'
            ];

            $invalidDataCount++;

            continue;
        }

        /**
         * Nominal
         */
        $rawNominal = trim($row[3] ?? '0');

        $normalized = str_replace(',', '', $rawNominal);

        $nominal = (int) round((float) $normalized);

        /**
         * Date
         */
        $dt = \DateTime::createFromFormat(
            'd/m/Y',
            trim($row[4])
        );

        if (!$dt) {

            $skippedRows[] = [
                'row'    => $rowNumber,
                'va'     => $row[0] ?? '',
                'nama'   => $row[1] ?? '',
                'ref'    => $row[2] ?? '',
                'nominal'=> $row[3] ?? '',
                'tanggal'=> $row[4] ?? '',
                'reason' => 'Format tanggal tidak valid'
            ];

            $invalidDateCount++;

            continue;
        }

        $tanggal = $dt->format('Y-m-d');

        /**
         * Values
         */
        $va = trim($row[0]);
        $referensi = trim($row[2]);

        /**
         * Duplicate key
         */
        $duplicateKey =
            $va . '|' .
            $nominal . '|' .
            $tanggal . '|' .
            $referensi;

        /**
         * Duplicate check
         */
        if (isset($existingMap[$duplicateKey])) {

            $skippedRows[] = [
                'row'    => $rowNumber,
                'va'     => $va,
                'nama'   => $row[1] ?? '',
                'ref'    => $referensi,
                'nominal'=> $nominal,
                'tanggal'=> $tanggal,
                'reason' => 'Duplicate'
            ];

            $duplicateCount++;

            continue;
        }

        /**
         * Add to batch
         */
        $dataBatch[] = [
            'transaksi_va'            => $va,
            'bank_id'                 => null,
            'transaksi_nominal'       => $nominal,
            'jenis'                   => null,
            'transaksi_tanggal'       => $tanggal,
            'wa_konfirmasi'           => null,
            'latestTransactionStatus' => '00',
            'transactionStatusDesc'   => null,
            'coreReferenceNo'         => $referensi,
        ];

        /**
         * Mark as existing
         *
         * Prevent duplicate inside same CSV
         */
        $existingMap[$duplicateKey] = true;
    }

    /**
     * Insert
     */
    if (!empty($dataBatch)) {

        $db->transStart();

        $builder->insertBatch($dataBatch);

        $db->transComplete();
    }

    /**
     * Save skipped rows in session
     */
    session()->setFlashdata('skipped_rows', $skippedRows);

    $importedCount = count($dataBatch);

    return redirect()
        ->back()
        ->with(
            'success',
            "Import selesai. Total CSV: " . ($rowNumber - 1) .
            ", Berhasil: {$importedCount}" .
            ", Dilewati: " . count($skippedRows) .
            " (Duplicate: {$duplicateCount}, Data kosong: {$invalidDataCount}, Tanggal invalid: {$invalidDateCount})"
        );
}

public function importCsvNew()
{
    $file = $this->request->getFile('csv');

    if (!$file || !$file->isValid()) {
        return redirect()->back()->with('error', 'File tidak valid');
    }

    if ($file->getClientExtension() !== 'csv') {
        return redirect()->back()->with('error', 'File harus CSV');
    }

    $handle = fopen($file->getTempName(), 'r');

    if ($handle === false) {
        return redirect()->back()->with('error', 'Gagal membaca file');
    }

    $db = \Config\Database::connect();
    $builder = $db->table('transaksi');

    /**
     * Load existing transactions ONCE
     *
     * Duplicate is based on:
     * transaksi_va
     * transaksi_nominal
     * transaksi_tanggal
     * coreReferenceNo
     */
    $existingRows = $builder
        ->select("
            transaksi_va,
            transaksi_nominal,
            DATE(transaksi_tanggal) as tgl,
            coreReferenceNo
        ")
        ->get()
        ->getResultArray();

    $existingMap = [];

    foreach ($existingRows as $r) {

        $duplicateKey =
            trim($r['transaksi_va']) . '|' .
            (int) $r['transaksi_nominal'] . '|' .
            $r['tgl'] . '|' .
            trim($r['coreReferenceNo']);

        $existingMap[$duplicateKey] = true;
    }

    $rowNumber = 0;
    $dataBatch = [];

    while (($row = fgetcsv($handle, 1000, ',')) !== false) {

        $rowNumber++;

        // Skip header
        if ($rowNumber === 1) {
            continue;
        }

        // Skip incomplete data
        if (
            empty($row[0]) ||
            empty($row[2]) ||
            empty($row[4])
        ) {
            continue;
        }

        /**
         * Nominal
         */
        $rawNominal = trim($row[3] ?? '0');

        // Remove comma separator
        $normalized = str_replace(',', '', $rawNominal);

        $nominal = (int) round((float) $normalized);

        /**
         * Tanggal
         */
        $dt = \DateTime::createFromFormat(
            'd/m/Y',
            trim($row[4])
        );

        if (!$dt) {
            continue;
        }

        $tanggal = $dt->format('Y-m-d');

        /**
         * Data values
         */
        $va = trim($row[0]);
        $referensi = trim($row[2]);

        /**
         * Duplicate key
         *
         * VA
         * + Nominal
         * + Tanggal
         * + Referensi
         */
        $duplicateKey =
            $va . '|' .
            $nominal . '|' .
            $tanggal . '|' .
            $referensi;

        /**
         * Reject duplicate
         */
        if (isset($existingMap[$duplicateKey])) {
            continue;
        }

        /**
         * Add to batch
         */
        $dataBatch[] = [
            'transaksi_va'            => $va,
            'bank_id'                 => null,
            'transaksi_nominal'       => $nominal,
            'jenis'                   => null,
            'transaksi_tanggal'       => $tanggal,
            'wa_konfirmasi'           => null,
            'latestTransactionStatus' => '00',
            'transactionStatusDesc'   => null,
            'coreReferenceNo'         => $referensi,
        ];

        /**
         * Mark as existing
         *
         * This also prevents duplicates
         * inside the same CSV file.
         */
        $existingMap[$duplicateKey] = true;
    }

    fclose($handle);

    /**
     * Insert batch
     */
    if (!empty($dataBatch)) {

        $db->transStart();

        $builder->insertBatch($dataBatch);

        $db->transComplete();
    }

    return redirect()
        ->back()
        ->with(
            'success',
            'Import CSV berhasil (duplikat berdasarkan VA, Nominal, Tanggal, dan Referensi otomatis ditolak)'
        );
}

  public function importCsvOLD()
{
    $file = $this->request->getFile('csv');

    if (!$file || !$file->isValid()) {
        return redirect()->back()->with('error', 'File tidak valid');
    }

    if ($file->getClientExtension() !== 'csv') {
        return redirect()->back()->with('error', 'File harus CSV');
    }

    $handle = fopen($file->getTempName(), 'r');
    if ($handle === false) {
        return redirect()->back()->with('error', 'Gagal membaca file');
    }

    $db = \Config\Database::connect();
    $builder = $db->table('transaksi');

    /**
     * 1️⃣ Load existing transactions ONCE
     * key = coreReferenceNo|YYYY-MM-DD
     */
    $existingRows = $builder
        ->select("coreReferenceNo, DATE(transaksi_tanggal) as tgl")
        ->get()
        ->getResultArray();

    $existingMap = [];
    foreach ($existingRows as $r) {
        $existingMap[$r['coreReferenceNo'] . '|' . $r['tgl']] = true;
    }

    $rowNumber = 0;
    $dataBatch = [];

    while (($row = fgetcsv($handle, 1000, ',')) !== false) {
        $rowNumber++;

        // Skip header
        if ($rowNumber === 1) {
            continue;
        }

        if (empty($row[0]) || empty($row[2]) || empty($row[4])) {
            continue;
        }

        /** Nominal */
        $rawNominal = trim($row[3] ?? '0');
        $normalized = str_replace(',', '', $rawNominal);
        $nominal    = (int) round((float) $normalized);

        /** Tanggal */
        $dt = \DateTime::createFromFormat('d/m/Y', $row[4]);
        if (!$dt) {
            continue;
        }
        $tanggal = $dt->format('Y-m-d');

        /** Duplicate key */
        $duplicateKey = trim($row[2]) . '|' . $tanggal;

        /**
         * 2️⃣ REPEL DUPLICATE
         */
        if (isset($existingMap[$duplicateKey])) {
            continue;
        }

        /** Add to batch + mark as existing */
        $dataBatch[] = [
            'transaksi_va'            => trim($row[0]),
            'bank_id'                 => null,
            'transaksi_nominal'       => $nominal,
            'jenis'                   => null,
            'transaksi_tanggal'       => $tanggal,
            'wa_konfirmasi'           => null,
            'latestTransactionStatus' => '00',
            'transactionStatusDesc'   => null,
            'coreReferenceNo'         => trim($row[2]),
        ];

        $existingMap[$duplicateKey] = true;
    }

    if (!empty($dataBatch)) {
        $db->transStart();
        $builder->insertBatch($dataBatch);
        $db->transComplete();
    }

    return redirect()->back()->with('success', 'Import CSV/XLSX berhasil (duplikat otomatis ditolak)');
}

private function readXlsxRows(string $path): array
{
    $zip = new \ZipArchive();
    if ($zip->open($path) !== true) {
        throw new \RuntimeException('Arsip XLSX tidak dapat dibuka.');
    }

    $sharedStrings = [];
    $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
    if ($sharedXml !== false) {
        $xml = simplexml_load_string($sharedXml);
        foreach ($xml->si as $item) {
            $text = '';
            foreach ($item->t as $part) $text .= (string) $part;
            foreach ($item->r as $run) $text .= (string) $run->t;
            $sharedStrings[] = $text;
        }
    }

    $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
    if ($sheetXml === false) {
        $zip->close();
        throw new \RuntimeException('Sheet pertama XLSX tidak ditemukan.');
    }

    $sheet = simplexml_load_string($sheetXml);
    $rows = [];
    foreach ($sheet->sheetData->row as $xmlRow) {
        $values = [];
        foreach ($xmlRow->c as $cell) {
            $reference = (string) $cell['r'];
            preg_match('/^[A-Z]+/', $reference, $match);
            $column = 0;
            foreach (str_split($match[0] ?? '') as $letter) $column = ($column * 26) + ord($letter) - 64;
            $index = max($column - 1, 0);
            $value = (string) ($cell->v ?? '');
            if ((string) $cell['t'] === 's') $value = $sharedStrings[(int) $value] ?? '';
            $values[$index] = $value;
        }
        if ($values) {
            ksort($values);
            $rows[] = array_replace(array_fill(0, max(5, count($values)), ''), $values);
        }
    }
    $zip->close();
    return $rows;
}


public function downloadAllPhotos()
{
    $db = \Config\Database::connect();

    // Sesuaikan nama tabel dan kolom
    $members = $db->table('dishub_anggota')
        ->select('anggota_id, anggota_nama, anggota_foto')
        ->where('anggota_foto IS NOT NULL')
        ->where('anggota_foto !=', '')
        ->get()
        ->getResultArray();


    // Folder tujuan
    $destination = FCPATH . 'uploads/anggota_foto/';

    if (!is_dir($destination)) {
        mkdir($destination, 0755, true);
    }

    $baseUrl = 'https://sisparma.malangkota.go.id/v2/upload/anggota/';

    $success = 0;
    $failed = 0;
    $skipped = 0;
    $failedDetails = [];

    foreach ($members as $member) {

        $filename = basename($member['anggota_foto']);

        if (!$filename) {
            $failed++;
            $failedDetails[] = [
                'anggota_id' => $member['anggota_id'],
                'nama' => $member['anggota_nama'],
                'filename' => $member['anggota_foto'],
                'reason' => 'Nama file foto kosong atau tidak valid.'
            ];
            continue;
        }

        $targetFile = $destination . $filename;

        // Skip jika sudah ada
        if (file_exists($targetFile)) {
            $skipped++;
            continue;
        }

        $remoteUrl = $baseUrl . rawurlencode($filename);

        // Download menggunakan CURL
        $ch = curl_init($remoteUrl);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0'
        ]);

        $imageData = curl_exec($ch);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);

        curl_close($ch);

        if ($imageData !== false && $httpCode === 200) {

            // Pastikan file benar-benar gambar
            $imageInfo = @getimagesizefromstring($imageData);

            if ($imageInfo !== false) {

                if (file_put_contents($targetFile, $imageData) === false) {
                    $failed++;
                    $failedDetails[] = [
                        'anggota_id' => $member['anggota_id'],
                        'nama' => $member['anggota_nama'],
                        'filename' => $filename,
                        'url' => $remoteUrl,
                        'reason' => 'File berhasil diunduh, tetapi gagal disimpan ke folder tujuan.'
                    ];
                    continue;
                }

                $success++;

            } else {

                $failed++;
                $failedDetails[] = [
                    'anggota_id' => $member['anggota_id'],
                    'nama' => $member['anggota_nama'],
                    'filename' => $filename,
                    'url' => $remoteUrl,
                    'http_code' => $httpCode,
                    'reason' => 'Respons berhasil diterima, tetapi isinya bukan file gambar yang valid.'
                ];
            }

        } else {

            $failed++;
            $failedDetails[] = [
                'anggota_id' => $member['anggota_id'],
                'nama' => $member['anggota_nama'],
                'filename' => $filename,
                'url' => $remoteUrl,
                'http_code' => $httpCode,
                'curl_error' => $curlError ?? null,
                'reason' => $httpCode === 404
                    ? 'Foto tidak ditemukan di server sumber (HTTP 404).'
                    : 'Gagal mengunduh foto dari server sumber.'
            ];
        }
    }

    return $this->response->setJSON([
        'status' => 'completed',
        'success' => $success,
        'failed' => $failed,
        'failed_details' => $failedDetails,
        'skipped' => $skipped,
        'folder' => 'uploads/anggota/'
    ]);
}

/**
 * Sinkronisasi nama file foto anggota dari Sisparma.
 * Route ini bersifat sementara: import/sync-anggota-foto
 */
public function syncAnggotaFoto()
{
    $apiUrl = 'https://sisparma.malangkota.go.id/api/dishub_dynamic_api_fortified.php'
        . '?table=dishub_anggota&parameter=anggota_id,anggota_foto,anggota_nama'
        . '&order=anggota_id&ad=asc&limit=10000';

    $ch = curl_init($apiUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_ENCODING => '',
        CURLOPT_HTTPHEADER => [
            'Accept: application/json, text/plain, */*',
            'Accept-Language: id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
            'Referer: https://sisparma.malangkota.go.id/',
            'Origin: https://sisparma.malangkota.go.id',
            'X-Requested-With: XMLHttpRequest',
        ],
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/131.0.0.0 Safari/537.36',
    ]);

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $curlError !== '') {
        return $this->response->setStatusCode(502)->setJSON([
            'status' => 'error',
            'message' => 'Gagal mengakses API: ' . $curlError,
        ]);
    }

    if ($httpCode < 200 || $httpCode >= 300) {
        return $this->response->setStatusCode(502)->setJSON([
            'status' => 'error',
            'message' => 'API mengembalikan HTTP ' . $httpCode,
        ]);
    }

    $payload = json_decode($response, true);
    if (!is_array($payload)) {
        return $this->response->setStatusCode(502)->setJSON([
            'status' => 'error',
            'message' => 'Respons API bukan JSON yang valid.',
        ]);
    }

    // API dapat mengembalikan array langsung atau membungkus data dalam data/results.
    $rows = $payload;
    foreach (['data', 'results', 'result'] as $key) {
        if (isset($payload[$key]) && is_array($payload[$key])) {
            $rows = $payload[$key];
            break;
        }
    }

    $db = \Config\Database::connect();
    $builder = $db->table('dishub_anggota');
    $updated = 0;
    $skipped = 0;

    foreach ($rows as $row) {
        $id = $row['anggota_id'] ?? null;
        $nama = trim((string) ($row['anggota_nama'] ?? ''));
        $foto = trim((string) ($row['anggota_foto'] ?? ''));
        if ($id === null || $foto === '') {
            $skipped++;
            continue;
        }

        $affected = $builder
            ->where('anggota_id', $id)
            ->groupStart()
                ->where('anggota_foto IS NULL', null, false)
                ->orWhere('anggota_foto', '')
            ->groupEnd()
            ->update(['anggota_foto' => $foto]);

        $updated += $affected ? $db->affectedRows() : 0;
    }

    return $this->response->setJSON([
        'status' => 'success',
        'message' => 'Sinkronisasi anggota_foto selesai.',
        'api_rows' => count($rows),
        'updated' => $updated,
        'skipped' => $skipped,
    ]);
}

/**
 * Import anggota_foto dari file JSON lokal.
 * Format JSON boleh berupa array langsung atau {"data": [...]}/{"results": [...]}
 */
public function importAnggotaFotoJson()
{
    if (strtolower($this->request->getMethod()) !== 'post') {
        return '<!doctype html><html lang="id"><head><meta charset="utf-8">'
            . '<meta name="viewport" content="width=device-width,initial-scale=1">'
            . '<title>Import Foto Anggota</title><style>'
            . '*{box-sizing:border-box}body{margin:0;background:#f4f7fb;color:#172033;font:15px Arial,sans-serif}'
            . '.wrap{max-width:900px;margin:48px auto;padding:0 20px}.brand{color:#3563e9;font-weight:700;letter-spacing:.08em;text-transform:uppercase;font-size:12px}'
            . 'h1{margin:8px 0 10px;font-size:32px}.sub{color:#667085;margin:0 0 28px}'
            . '.card{background:#fff;border:1px solid #e5eaf2;border-radius:18px;padding:28px;box-shadow:0 8px 30px #243b5310}'
            . '.drop{border:2px dashed #b9c7e6;border-radius:14px;padding:38px 20px;text-align:center;background:#f9fbff;cursor:pointer}'
            . '.drop:hover{border-color:#3563e9;background:#f3f6ff}.drop strong{display:block;font-size:18px;margin-bottom:8px}.drop span{color:#667085}'
            . 'input[type=file]{display:none}.file{margin-top:14px;color:#3563e9;font-weight:600;min-height:20px}'
            . 'button{margin-top:24px;border:0;border-radius:10px;background:#3563e9;color:white;padding:13px 22px;font-weight:700;font-size:15px;cursor:pointer;width:100%}'
            . 'button:hover{background:#244bc4}.hint{margin-top:16px;color:#667085;font-size:13px;line-height:1.6}'
            . '</style></head><body><main class="wrap"><div class="brand">Dishub 2026</div>'
            . '<h1>Import Foto Anggota</h1><p class="sub">Unggah file JSON untuk mengisi data foto anggota yang masih kosong.</p>'
            . '<section class="card"><form method="post" action="' . base_url('import/anggota-foto-json') . '" enctype="multipart/form-data">'
            . csrf_field() . '<label class="drop" for="json"><strong>Pilih file datafoto.json</strong><span>Klik area ini untuk memilih file JSON</span></label>'
            . '<input id="json" type="file" name="json" accept="application/json,.json" required onchange="document.getElementById(\'filename\').textContent=this.files[0]?.name||\'\'">'
            . '<div id="filename" class="file"></div><button type="submit">Mulai Import</button>'
            . '<div class="hint">Format yang didukung: JSON array atau JSON dengan pembungkus <b>data</b>, <b>results</b>, atau <b>result</b>.<br>Data yang sudah memiliki foto tidak akan ditimpa.</div>'
            . '</form></section></main></body></html>';
    }

    $file = $this->request->getFile('json');
    if (!$file || !$file->isValid()) {
        return $this->response->setStatusCode(400)->setJSON([
            'status' => 'error',
            'message' => 'File JSON tidak valid atau belum dipilih.',
        ]);
    }

    $jsonText = (string) file_get_contents($file->getTempName());
    // Be tolerant of UTF-8 BOM and whitespace before the JSON payload.
    $jsonText = preg_replace('/^\xEF\xBB\xBF/', '', $jsonText);

    // File hasil export dapat diawali komentar metadata seperti:
    // // Status 200 (...)
    // Ambil hanya blok JSON array-nya.
    $jsonText = str_replace(['&#x20;', '&quot;', '&#34;'], [' ', '"', '"'], $jsonText);
    $jsonText = trim($jsonText);
    // Export lama dapat memiliki koma terakhir sebelum penutup array/object.
    $jsonText = preg_replace('/,\s*([}\]])/', '$1', $jsonText);
    // Beberapa nama pada export memiliki tanda kutip ganda di dalam nilai string.
    $jsonText = preg_replace('/(\"\s*:\s*\"[^\"\r\n]*)\"\"([^\"\r\n]*\")/', '$1\\"$2', $jsonText);
    $start = strpos($jsonText, '[');
    $objectStart = strpos($jsonText, '{');
    if ($start === false || ($objectStart !== false && $objectStart < $start)) {
        $start = $objectStart;
    }
    $end = strrpos($jsonText, $start === $objectStart ? '}' : ']');
    if ($start !== false && $end !== false && $end >= $start) {
        $jsonText = substr($jsonText, $start, $end - $start + 1);
    }

    $payload = json_decode($jsonText, true);
    if (!is_array($payload) || json_last_error() !== JSON_ERROR_NONE) {
        return $this->response->setStatusCode(400)->setJSON([
            'status' => 'error',
            'message' => 'Isi file bukan JSON yang valid: ' . json_last_error_msg(),
        ]);
    }

    $rows = $payload;
    foreach (['data', 'results', 'result'] as $key) {
        if (isset($payload[$key]) && is_array($payload[$key])) {
            $rows = $payload[$key];
            break;
        }
    }

    $db = \Config\Database::connect();
    $updated = 0;
    $skipped = 0;
    $alreadyFilled = 0;
    $notFound = 0;
    $imported = [];
    $errors = [];

    foreach ($rows as $row) {
        if (!is_array($row)) {
            $skipped++;
            continue;
        }

        $id = $row['anggota_id'] ?? null;
        $nama = trim((string) ($row['anggota_nama'] ?? ''));
        $foto = trim((string) ($row['anggota_foto'] ?? ''));
        $fields = [
            'anggota_foto', 'anggota_noreg', 'anggota_noktp', 'anggota_alamat',
            'anggota_tgllhr', 'anggota_tmplhr', 'anggota_klrhnid',
        ];
        $hasImportValue = $foto !== '' || count(array_filter(array_map(
            static fn ($field) => trim((string) ($row[$field] ?? '')),
            array_slice($fields, 1)
        ))) > 0;
        if ($nama === '' || !$hasImportValue) {
            $skipped++;
            continue;
        }

        // ID file sumber dapat berbeda dari ID database lokal. Cocokkan nama saja.
        $lookup = $db->table('dishub_anggota');
        $lookup->where("LOWER(TRIM(anggota_nama)) = " . $db->escape(strtolower($nama)), null, false);
        $existing = $lookup->get()->getRowArray();
        if (!$existing) {
            $notFound++;
            continue;
        }

        $changes = [];
        foreach ($fields as $field) {
            $value = trim((string) ($row[$field] ?? ''));
            if ($value !== '' && trim((string) ($existing[$field] ?? '')) === '') {
                $changes[$field] = $value;
            }
        }

        $ok = $changes === [] ? true : $db->table('dishub_anggota')
            ->where('anggota_id', $existing['anggota_id'])->update($changes);

        if (!$ok) {
            $errors[] = [
                'anggota_id' => $id,
                'error' => $db->error(),
            ];
            continue;
        }

        $affectedRows = $db->affectedRows();
        $updated += $affectedRows;
        if ($affectedRows > 0) {
            $imported[] = [
                'anggota_id' => $existing['anggota_id'],
                'anggota_nama' => $existing['anggota_nama'] ?? $nama,
                'anggota_foto' => $changes['anggota_foto'] ?? '',
                'anggota_noreg' => $changes['anggota_noreg'] ?? '',
                'anggota_noktp' => $changes['anggota_noktp'] ?? '',
                'anggota_alamat' => $changes['anggota_alamat'] ?? '',
                'anggota_tgllhr' => $changes['anggota_tgllhr'] ?? '',
                'anggota_tmplhr' => $changes['anggota_tmplhr'] ?? '',
                'anggota_klrhnid' => $changes['anggota_klrhnid'] ?? '',
            ];
        } else {
            if ($existing) {
                $alreadyFilled++;
            } else {
                $notFound++;
            }
        }
    }

    $h = static fn ($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    $tableRows = '';
    foreach ($imported as $item) {
        $tableRows .= '<tr><td>' . $h($item['anggota_id']) . '</td><td>'
            . $h($item['anggota_nama']) . '</td><td>' . $h($item['anggota_foto']) . '</td><td>'
            . $h($item['anggota_noreg']) . '</td><td>' . $h($item['anggota_noktp']) . '</td><td>'
            . $h($item['anggota_alamat']) . '</td><td>' . $h($item['anggota_tgllhr']) . '</td><td>'
            . $h($item['anggota_tmplhr']) . '</td><td>' . $h($item['anggota_klrhnid']) . '</td></tr>';
    }
    if ($tableRows === '') {
        $tableRows = '<tr><td colspan="3">Tidak ada data yang di-update.</td></tr>';
    }

    $errorHtml = '';
    foreach ($errors as $error) {
        $errorHtml .= '<li>Anggota ID ' . $h($error['anggota_id']) . ': '
            . $h($error['error']['message'] ?? 'Update gagal') . '</li>';
    }

    $resultHtml = '<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Hasil Import Foto</title>'
        . '<style>*{box-sizing:border-box}body{margin:0;background:#f4f7fb;color:#172033;font:14px Arial,sans-serif}.wrap{max-width:1180px;margin:38px auto;padding:0 20px}.brand{color:#3563e9;font-weight:700;letter-spacing:.08em;text-transform:uppercase;font-size:12px}h1{margin:8px 0 5px;font-size:30px}.sub{color:#667085;margin:0 0 24px}.ok{padding:16px 18px;background:#e8f7ee;border:1px solid #b8e3c7;color:#176b38;border-radius:12px;margin-bottom:18px}.stats{display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:20px}.stat{background:#fff;border:1px solid #e5eaf2;border-radius:14px;padding:16px}.stat b{display:block;font-size:25px;margin-top:7px}.stat span{color:#667085;font-size:12px}.panel{background:#fff;border:1px solid #e5eaf2;border-radius:16px;padding:20px;overflow:hidden}table{border-collapse:collapse;width:100%;margin-top:8px}th,td{border-bottom:1px solid #edf0f5;padding:11px 10px;text-align:left}th{background:#f8faff;color:#475467;font-size:12px;text-transform:uppercase}td{color:#344054}.actions{margin:22px 0}.btn{display:inline-block;background:#3563e9;color:#fff;text-decoration:none;padding:11px 17px;border-radius:9px;font-weight:700}.error{background:#fff0f0;border:1px solid #f0b9b9;padding:14px;border-radius:10px;margin-bottom:18px;color:#9b1c1c}@media(max-width:760px){.stats{grid-template-columns:repeat(2,1fr)}.panel{overflow:auto}table{min-width:700px}}</style></head><body><main class="wrap"><div class="brand">Dishub 2026</div><h1>Hasil Import Foto Anggota</h1><p class="sub">Proses import selesai. Berikut ringkasan data yang diproses.</p>'
        . '<div class="ok">File berhasil diproses. Data dengan foto yang sudah terisi tidak ditimpa.</div><div class="stats">'
        . '<div class="stat"><span>Total JSON</span><b>' . count($rows) . '</b></div><div class="stat"><span>Berhasil di-update</span><b>' . $updated . '</b></div><div class="stat"><span>Sudah terisi</span><b>' . $alreadyFilled . '</b></div><div class="stat"><span>Tidak ditemukan</span><b>' . $notFound . '</b></div><div class="stat"><span>Dilewati / error</span><b>' . ($skipped + count($errors)) . '</b></div></div>'
        . '<div class="ok">JSON: ' . count($rows) . ' data | Berhasil di-update: '
        . $updated . ' | Sudah terisi: ' . $alreadyFilled . ' | Tidak ditemukan: '
        . $notFound . ' | Dilewati: ' . $skipped . ' | Error: ' . count($errors) . '</div>'
        . ($errorHtml ? '<div class="error"><b>Error saat import</b><ul>' . $errorHtml . '</ul></div>' : '')
        . '<section class="panel"><h3>Data yang berhasil diimport</h3><table><thead><tr><th>ID</th><th>Nama</th><th>Foto</th><th>No. Reg</th><th>No. KTP</th><th>Alamat</th><th>Tgl Lahir</th><th>Tempat Lahir</th><th>Kelurahan/NID</th>'
        . '</tr></thead><tbody>' . $tableRows . '</tbody></table></section><div class="actions"><a class="btn" href="'
        . base_url('import/anggota-foto-json') . '">Import File Lain</a></div></main></body></html>';

    session()->set('import_anggota_foto_result', $resultHtml);
    // Gunakan 303 agar browser pasti melakukan GET ke halaman hasil setelah POST.
    return redirect()->to(base_url('import/anggota-foto-json/hasil'), 303);
}

/** Menampilkan hasil import pada halaman terpisah. */
public function hasilImportAnggotaFotoJson()
{
    $resultHtml = session()->get('import_anggota_foto_result');
    if (!$resultHtml) {
        return redirect()->to(base_url('import/anggota-foto-json'));
    }

    session()->remove('import_anggota_foto_result');
    return $resultHtml;
}

}
