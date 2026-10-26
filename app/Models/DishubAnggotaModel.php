<?php

namespace App\Models;

use CodeIgniter\Model;

class DishubAnggotaModel extends Model
{
    protected $table      = 'dishub_anggota';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'anggota_newnoreg',
        'anggota_nama',
        'anggota_noktp',
        'anggota_alamat',
        'anggota_tgllhr',
        'anggota_foto',
        'created_at',
    ];

    protected $useTimestamps = false;

    public function getLastNoreg(): string
    {
        $row = $this->selectMax('anggota_newnoreg')->first();
        return $row['anggota_newnoreg'] ?? '000000';
    }

    public function generateNoreg(): string
    {
        $last = $this->getLastNoreg();
        return str_pad(((int)$last) + 1, 6, '0', STR_PAD_LEFT);
    }

    public function getTitparWithAnggota(int $anggotaId): array
{
    $rows = $this->db->table('va_owner v')
        ->select('
            t.titpar_id,
            t.titpar_noreg,
            t.titpar_newnoreg,
            t.titpar_pmlknama,
            t.titpar_namatempat,
            t.titpar_lokasi,
            t.titpar_latitude,
            t.titpar_longitude,
            v.va_owner_va
        ')
        ->join(
            'dishub_titpargrup tg',
            'tg.titpargrup_id = v.va_owner_titpargrupid'
        )
        ->join(
            'dishub_titpar t',
            't.titpar_id = tg.titpargrup_titparid'
        )
        ->where('v.va_owner_va', $anggotaId)
        ->where('t.titpar_status', 3)
        ->get()
        ->getResultArray();

    $titpars = [];

    foreach ($rows as $row) {

        $titparId = $row['titpar_id'];

        if (!isset($titpars[$titparId])) {
            $titpars[$titparId] = [
                'titpar_id'         => $row['titpar_id'],
                'titpar_noreg'      => $row['titpar_noreg'],
                'titpar_newnoreg'   => $row['titpar_newnoreg'],
                'titpar_pmlknama'   => $row['titpar_pmlknama'],
                'titpar_namatempat' => $row['titpar_namatempat'],
                'titpar_lokasi'     => $row['titpar_lokasi'],
                'titpar_latitude'   => $row['titpar_latitude'],
                'titpar_longitude'  => $row['titpar_longitude'],
                'va'                => [],
                'anggota'           => []
            ];
        }

        if (!empty($row['va_owner_va'])) {
            $titpars[$titparId]['va'][] = $row['va_owner_va'];
        }
    }

    foreach ($titpars as &$titpar) {

        $titpar['va'] = array_values(
            array_unique($titpar['va'])
        );

        $titpar['anggota'] = $this->db->table('dishub_titpargrup tg')
            ->select('
                a.anggota_id,
                a.anggota_noreg,
                a.anggota_newnoreg,
                a.anggota_nama,
                a.anggota_nohp,
                a.anggota_foto,
                a.anggota_status,
                tg.titpargrup_staanggota,
                tg.titpargrup_masaberlakustart,
                tg.titpargrup_masaberlakuend
            ')
            ->join(
                'dishub_anggota a',
                'a.anggota_id = tg.titpargrup_anggotaid'
            )
            ->where(
                'tg.titpargrup_titparid',
                $titpar['titpar_id']
            )
            ->get()
            ->getResultArray();
    }

    return array_values($titpars);
}

public function getOtherTitpar(int $anggotaId, int $currentTitparId): array
{
    return $this->db->table('va_owner v')
        ->select('
            t.titpar_id,
            t.titpar_noreg,
            t.titpar_newnoreg,
            t.titpar_pmlknama,
            t.titpar_namatempat,
            t.titpar_lokasi,
            t.titpar_latitude,
            t.titpar_longitude,
            v.va_owner_va,
            v.va_owner_titpargrupid
        ')
        ->join(
            'dishub_titpargrup tg',
            'tg.titpargrup_id = v.va_owner_titpargrupid'
        )
        ->join(
            'dishub_titpar t',
            't.titpar_id = tg.titpargrup_titparid'
        )
        ->where('v.va_owner_anggotaid', $anggotaId)
        ->where('t.titpar_id !=', $currentTitparId)
        ->where('t.titpar_status', 3)
        ->get()
        ->getResultArray();
}   

public function getVaWithTransaksi(string $va, ?int $filterYear = null): ?array
{
    // Ambil detail VA + pemilik + titpar
    $owner = $this->db->table('va_owner v')
    ->select('
        v.va_owner_id,
        v.va_owner_va,
        v.va_owner_anggotaid,
        v.va_owner_titpargrupid,
        v.va_owner_nama,
        v.va_owner_hp,
        v.va_owner_email,
        v.va_owner_expired,

        tg.titpargrup_anggotaid AS anggota_id,
        a.anggota_foto,
        v.va_owner_nama AS anggota_nama,
        v.va_owner_hp AS anggota_nohp,

        t.titpar_id,
        t.titpar_noreg,
        t.titpar_newnoreg,
        t.titpar_namatempat,
        t.titpar_lokasi,
        t.titpar_status,
        tg.titpargrup_masaberlakustart,
        tg.titpargrup_masaberlakuend,
    ')
    ->join(
        'dishub_titpargrup tg',
        'tg.titpargrup_id = v.va_owner_titpargrupid',
        'left'
    )
    ->join(
        'dishub_titpar t',
        't.titpar_id = tg.titpargrup_titparid',
        'left'
    )
    ->join(
        'dishub_anggota a',
        'a.anggota_id = tg.titpargrup_anggotaid',
        'left'
    )
    ->where('v.va_owner_va', $va)
    ->get()
    ->getRowArray();

    if (!$owner) {
        return null;
    }

    // Ambil transaksi
    $transaksi = $this->db->table('transaksi')
        ->select('
            transaksi_id,
            transaksi_va,
            bank_id,
            transaksi_nominal,
            jenis,
            transaksi_tanggal,
            wa_konfirmasi,
            latestTransactionStatus,
            transactionStatusDesc,
            coreReferenceNo
        ')
        ->where('transaksi_va', $va)
        ->orderBy('transaksi_tanggal', 'DESC')
        ->get()
        ->getResultArray();

    // Tambahkan transaksi ke data owner
    $owner['transaksi'] = $transaksi;

    // Potensi aktif dan riwayat perubahan potensi.
    $owner['potensi'] = $this->db->table('potensi')
        ->where('potensi_va', $va)->get()->getRowArray() ?: null;
    $owner['potensi_riwayat'] = $this->db->table('potensi_riwayat')
        ->where('potensi_va', $va)
        ->orderBy('berlaku_mulai', 'DESC')
        ->get()->getResultArray();

    // Kendala user dan nilai pengurangannya. Kendala tetap ditampilkan semua,
    // tetapi hanya yang sudah Approved yang dihitung sebagai pengurangan.
    $kendalaQuery = $this->db->table('kendala k')
        ->select('k.kendala_id, k.kendala_va, k.kendala_alasan, k.kendala_foto, k.kategori, k.approval, k.created_at')
        ->where('k.kendala_va', $va)
        ->where('k.deleted_at', null)
        ->orderBy('k.created_at', 'DESC');
    if ($filterYear) {
        $kendalaQuery->where('YEAR(k.created_at)', (int) $filterYear, false);
    }
    $kendala = $kendalaQuery->get()->getResultArray();

    $bobotKendala = [1 => 1, 2 => 0.5, 3 => 0.25];
    $kendalaTotal = 0;
    foreach ($kendala as &$k) {
        $tanggal = date('Y-m-d', strtotime($k['created_at']));
        $snapshot = null;
        foreach ($owner['potensi_riwayat'] as $riwayat) {
            if ($riwayat['berlaku_mulai'] <= $tanggal && (empty($riwayat['berlaku_sampai']) || $riwayat['berlaku_sampai'] >= $tanggal)) {
                if (!$snapshot || $riwayat['berlaku_mulai'] > $snapshot['berlaku_mulai']) $snapshot = $riwayat;
            }
        }
        $snapshot ??= $owner['potensi'];
        $days = (int) date('t', strtotime($tanggal));
        $weekday = (int) date('N', strtotime($tanggal));
        $dailyFields = ['senin','selasa','rabu','kamis','jumat','sabtu','minggu'];
        $nilai = 0;
        if ($snapshot) {
            if ((float) ($snapshot['bulanan'] ?? 0) > 0) $nilai = (float) $snapshot['bulanan'] / $days;
            elseif ((float) ($snapshot['mingguan'] ?? 0) > 0) $nilai = (float) $snapshot['mingguan'] / 7;
            else $nilai = (float) ($snapshot[$dailyFields[$weekday - 1]] ?? 0);
        }
        $bobot = $bobotKendala[$k['kategori']] ?? 0;
        $k['persentase_potongan'] = $bobot * 100;
        $k['pengurangan'] = (int) $k['approval'] === 1 ? $nilai * $bobot : 0;
        $kendalaTotal += $k['pengurangan'];
    }
    unset($k);
    $owner['kendala'] = $kendala;
    $owner['kendala_total'] = $kendalaTotal;

    // Rekap potensi vs realisasi per bulan/tahun. Nilai potensi diambil dari
    // snapshot yang berlaku pada awal bulan tersebut.
    $years = $filterYear ? [$filterYear] : [$this->db->query("SELECT YEAR(MIN(transaksi_tanggal)) AS tahun FROM transaksi WHERE transaksi_va = ?", [$va])->getRow('tahun')];
    $years = $filterYear ? [$filterYear] : array_values(array_unique(array_filter(array_merge($years, array_map(
        static fn($r) => date('Y', strtotime($r['berlaku_mulai'])), $owner['potensi_riwayat']
    ), [date('Y')]))));
    sort($years);
    $trxByMonth = [];
    foreach ($transaksi as $trx) {
        $key = date('Y-n', strtotime($trx['transaksi_tanggal']));
        $trxByMonth[$key] = ($trxByMonth[$key] ?? 0) + (float)$trx['transaksi_nominal'];
    }
    $owner['potensi_realisasi_bulanan'] = [];
    $owner['potensi_realisasi_tahunan'] = [];
    foreach ($years as $year) {
        $yearTotal = 0; $yearKendala = 0; $yearReal = 0;
        // Detail bulanan tetap menampilkan Januari–Desember.
        // Rekap tahunan untuk tahun berjalan hanya diakumulasi sampai bulan berjalan.
        $lastMonth = ((int) $year === (int) date('Y')) ? (int) date('n') : 12;
        for ($month = 1; $month <= 12; $month++) {
            $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $potential = 0; $snapshotStarts = [];
            for ($day = 1; $day <= $days; $day++) {
                $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                $snapshot = null;
                foreach ($owner['potensi_riwayat'] as $r) {
                    if ($r['berlaku_mulai'] <= $date && (empty($r['berlaku_sampai']) || $r['berlaku_sampai'] >= $date)) {
                        if (!$snapshot || $r['berlaku_mulai'] > $snapshot['berlaku_mulai']) $snapshot = $r;
                    }
                }
                if (!$snapshot) continue;
                $snapshotStarts[$snapshot['berlaku_mulai']] = true;
                $weekday = (int)date('N', strtotime($date));
                $dailyFields = ['senin','selasa','rabu','kamis','jumat','sabtu','minggu'];
                if ((float)$snapshot['bulanan'] > 0) $potential += (float)$snapshot['bulanan'] / $days;
                elseif ((float)$snapshot['mingguan'] > 0) $potential += (float)$snapshot['mingguan'] / 7;
                else $potential += (float)($snapshot[$dailyFields[$weekday - 1]] ?? 0);
            }
            $real = $trxByMonth["$year-$month"] ?? 0;
            $kendalaBulan = 0;
            foreach ($kendala as $k) {
                if ((int) $k['approval'] === 1 && date('Y-n', strtotime($k['created_at'])) === "$year-$month") {
                    $kendalaBulan += (float) $k['pengurangan'];
                }
            }
            $potensiSetelahKendala = max($potential - $kendalaBulan, 0);
            $owner['potensi_realisasi_bulanan'][] = [
                'tahun' => $year,
                'bulan' => $month,
                'potensi' => $potential,
                'pengurangan_kendala' => $kendalaBulan,
                'potensi_setelah_kendala' => $potensiSetelahKendala,
                'realisasi' => $real,
                'snapshot_mulai' => implode(', ', array_keys($snapshotStarts))
            ];
            if ($month <= $lastMonth) {
                $yearTotal += $potential;
                $yearKendala += $kendalaBulan;
                $yearReal += $real;
            }
        }
        $owner['potensi_realisasi_tahunan'][] = [
            'tahun' => $year,
            'potensi' => $yearTotal,
            'kendala' => $yearKendala,
            'potensi_setelah_kendala' => max($yearTotal - $yearKendala, 0),
            'realisasi' => $yearReal
        ];
    }

    // Ringkasan transaksi
    $owner['total_transaksi'] = count($transaksi);

    $owner['total_nominal'] = array_sum(
        array_map(
            fn($item) => (float) $item['transaksi_nominal'],
            $transaksi
        )
    );

    return $owner;
}
}
