<?php 

namespace App\Controllers;

use App\Libraries\DataTable;
use DB;
use DateTime;

class SisparmaApp extends BaseController
{
    public $db;

    public function __construct()
    {
      $this->db = \Config\Database::connect();
  }

  private function potensiBerlaku(string $va, string $tanggal): ?array
  {
      $row = $this->db->table('potensi_riwayat')
          ->where('potensi_va', $va)
          ->where('berlaku_mulai <=', $tanggal)
          ->groupStart()->where('berlaku_sampai IS NULL', null, false)->orWhere('berlaku_sampai >=', $tanggal)->groupEnd()
          ->orderBy('berlaku_mulai', 'DESC')->get()->getRowArray();
      return $row ?: ($this->db->table('potensi')->where('potensi_va', $va)->get()->getRowArray() ?: null);
  }

  public function listTransaksiApp($anggota_id)
{
    $db = \Config\Database::connect();

    // Optional filters: ?month=10&year=2025
    $month = $this->request->getGet('month');
    $year  = $this->request->getGet('year');

    $builder = $db->table('transaksi');
    $builder->select('
        titpar_namatempat,
        va_owner_va,
        transaksi_nominal,
        transaksi_tanggal
    ');

    $builder->join('va_owner', 'transaksi.transaksi_va = va_owner.va_owner_va', 'left');
    $builder->join('dishub_titpargrup', 'dishub_titpargrup.titpargrup_id = va_owner.va_owner_titpargrupid', 'left');
    $builder->join('dishub_anggota', 'dishub_anggota.anggota_id = dishub_titpargrup.titpargrup_anggotaid', 'left');
    $builder->join('dishub_titpar', 'dishub_titpar.titpar_id = dishub_titpargrup.titpargrup_titparid', 'left');

    $builder->where('anggota_id', $anggota_id);

    // ✅ Apply filters when present
    if (!empty($month)) {
        $builder->where('MONTH(transaksi_tanggal)', (int)$month);
    }

    if (!empty($year)) {
        $builder->where('YEAR(transaksi_tanggal)', (int)$year);
    }

    $builder->orderBy('transaksi_tanggal', 'desc');

    $query = $builder->get();
    $data = $query->getResultArray();

    return $this->response->setJSON([
        'status' => 'success',
        'data' => $data
    ]);
}


    public function listTransaksiApp2($anggota_id)
{
    $db = \Config\Database::connect();

    $builder = $db->table('transaksi');
    $builder->select('titpar_namatempat, va_owner_va, transaksi_nominal, transaksi_tanggal');
    $builder->join('va_owner', 'transaksi.transaksi_va = va_owner.va_owner_va', 'left');
    $builder->join('dishub_titpargrup', 'dishub_titpargrup.titpargrup_id = va_owner.va_owner_titpargrupid', 'left');
    $builder->join('dishub_anggota', 'dishub_anggota.anggota_id = dishub_titpargrup.titpargrup_anggotaid', 'left');
    $builder->join('dishub_titpar', 'dishub_titpar.titpar_id = dishub_titpargrup.titpargrup_titparid', 'left');
    $builder->where('anggota_id', $anggota_id);
    $builder->orderBy('transaksi_tanggal', 'desc');
    $builder->limit(10);

    $query = $builder->get();
    $data = $query->getResultArray();

    // ✅ Format each record
    foreach ($data as &$row) {
        // Format nominal with thousand separator (e.g., 1.200.000)
        $row['transaksi_nominal'] = number_format((float)$row['transaksi_nominal'], 0, ',', '.');

        // Format date in Indonesian style (e.g., 30 Oktober 2025)
        $timestamp = strtotime($row['transaksi_tanggal']);
        $bulan = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        $bulanNama = $bulan[(int)date('n', $timestamp)];
        $row['transaksi_tanggal'] = date('j', $timestamp) . ' ' . $bulanNama . ' ' . date('Y', $timestamp);
    }

    return $this->response->setJSON([
        'status' => 'success',
        'data' => $data
    ]);
}

public function listTagihanApp($anggota_id)
{
    $db = db_connect();

    $bulan = date('n');
    $tahun = date('Y');

    $bulanPad = str_pad($bulan, 2, '0', STR_PAD_LEFT);
    $startMonth = "{$tahun}-{$bulanPad}-01";
    $endMonth   = date('Y-m-d', strtotime("$startMonth +1 month"));

    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

    $weekdayCount = [
        'senin'=>0,'selasa'=>0,'rabu'=>0,'kamis'=>0,'jumat'=>0,'sabtu'=>0,'minggu'=>0
    ];

    for ($d = 1; $d <= $daysInMonth; $d++) {
        $day = date('N', strtotime("$tahun-$bulanPad-$d"));

        switch ($day) {
            case 1: $weekdayCount['senin']++; break;
            case 2: $weekdayCount['selasa']++; break;
            case 3: $weekdayCount['rabu']++; break;
            case 4: $weekdayCount['kamis']++; break;
            case 5: $weekdayCount['jumat']++; break;
            case 6: $weekdayCount['sabtu']++; break;
            case 7: $weekdayCount['minggu']++; break;
        }
    }

    // ================= DATA UTAMA =================
    $builder = $db->table('va_owner')
        ->select('
            va_owner_va,
            anggota_id,
            titpar_namatempat,
            titpar_lokasi,
            anggota_nama,
            senin,
            selasa,
            rabu,
            kamis,
            jumat,
            sabtu,
            minggu,
            mingguan,
            bulanan,
            tahunan
        ')
        ->join('potensi', 'va_owner.va_owner_va = potensi.potensi_va', 'left')
        ->join('dishub_titpargrup', 'dishub_titpargrup.titpargrup_id = va_owner.va_owner_titpargrupid', 'left')
        ->join('dishub_anggota', 'dishub_anggota.anggota_id = dishub_titpargrup.titpargrup_anggotaid', 'left')
        ->join('dishub_titpar','dishub_titpargrup.titpargrup_titparid = dishub_titpar.titpar_id','left')
        ->where('anggota_id', $anggota_id);

    $data = $builder->get()->getResultArray();

    // ================= AMBIL KENDALA =================
    $kendalaRows = $db->table('kendala k')
        ->select("
            k.kendala_va,
            k.kategori,
            k.created_at,
            DAYOFWEEK(k.created_at) as hari
        ")
        ->where('k.created_at >=', $startMonth)
        ->where('k.created_at <', $endMonth)
        ->where('k.approval', 1)
        ->get()
        ->getResultArray();

    // mapping kendala per VA
    $mapKendala = [];
    foreach ($kendalaRows as $k) {
        $mapKendala[$k['kendala_va']][] = $k;
    }

    // ================= LOOP =================
    foreach ($data as &$row) {

        // Simpan potensi dari query utama sebagai fallback. Jangan menimpa
        // $row karena data dasar ini juga dibutuhkan saat menghitung histori
        // per tanggal.
        $basePotensi = $row;
        // Field potensi yang dikirim ke aplikasi harus mencerminkan potensi
        // yang sedang berlaku hari ini, seperti tampilan "Potensi Aktif" pada
        // halaman detail VA. Perhitungan total tetap memakai snapshot per hari
        // di bawah agar perubahan di tengah bulan tetap terhitung tepat.
        $snapshot = $this->potensiBerlaku($row['va_owner_va'], date('Y-m-d'));
        $potensiFields = ['senin','selasa','rabu','kamis','jumat','sabtu','minggu','mingguan','bulanan','tahunan'];
        $displayPotensi = $snapshot ?: $basePotensi;
        foreach ($potensiFields as $field) {
            $row[$field] = (int) round((float) ($displayPotensi[$field] ?? 0));
        }

        // ===== POTENSI BULANAN =====
        // Hitung per hari agar perubahan potensi di tengah bulan ikut dihitung.
        $total_bulanan = 0;
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = sprintf('%04d-%02d-%02d', $tahun, $bulan, $day);
            $daily = $this->potensiBerlaku($row['va_owner_va'], $date) ?: $basePotensi;
            $weekday = (int) date('N', strtotime($date));
            if ((float)($daily['bulanan'] ?? 0) > 0) $total_bulanan += (float)$daily['bulanan'] / $daysInMonth;
            elseif ((float)($daily['mingguan'] ?? 0) > 0) $total_bulanan += (float)$daily['mingguan'] / 7;
            else {
                $dailyFields = ['senin','selasa','rabu','kamis','jumat','sabtu','minggu'];
                $total_bulanan += (float) ($daily[$dailyFields[$weekday - 1]] ?? 0);
            }
        }

        // ===== KENDALA =====
        $va = $row['va_owner_va'];
        $listKendala = $mapKendala[$va] ?? [];

        $kendala_total = 0;

        foreach ($listKendala as $k) {
            $tanggalKendala = date('Y-m-d', strtotime($k['created_at'] ?? $startMonth));
            $potensiKendala = $this->potensiBerlaku($va, $tanggalKendala) ?: $basePotensi;
            $daysKendala = cal_days_in_month(
                CAL_GREGORIAN,
                (int) date('n', strtotime($tanggalKendala)),
                (int) date('Y', strtotime($tanggalKendala))
            );
            $weekdayKendala = (int) date('N', strtotime($tanggalKendala));
            $dailyFields = ['senin','selasa','rabu','kamis','jumat','sabtu','minggu'];

            // nilai harian
            if ((float) ($potensiKendala['bulanan'] ?? 0) > 0) {
                $nilai_harian = (float) $potensiKendala['bulanan'] / $daysKendala;
            } elseif ((float) ($potensiKendala['mingguan'] ?? 0) > 0) {
                $nilai_harian = (float) $potensiKendala['mingguan'] / 7;
            } else {
                $nilai_harian = (float) ($potensiKendala[$dailyFields[$weekdayKendala - 1]] ?? 0);
            }

            // bobot kategori
            switch ($k['kategori']) {
                case 1: $bobot = 1; break;
                case 2: $bobot = 0.5; break;
                case 3: $bobot = 0.25; break;
                default: $bobot = 0;
            }

            $kendala_total += $nilai_harian * $bobot;
        }

        // ===== FINAL =====
        $row['total_bulanan'] = $total_bulanan;
        $row['kendala_tagihan'] = $kendala_total;
        $row['total_setelah_kendala'] = max($total_bulanan - $kendala_total, 0);

        $row['total_bulanan'] = $row['total_setelah_kendala'];
    }

    return $this->response->setJSON([
        'status' => 'success',
        'data' => $data
    ]);
}
    
public function listTagihanAppOLD2($anggota_id)
{
    $db = db_connect();

    $bulan = date('n');
    $tahun = date('Y');

    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

    $weekdayCount = [
        'senin'=>0,'selasa'=>0,'rabu'=>0,'kamis'=>0,'jumat'=>0,'sabtu'=>0,'minggu'=>0
    ];

    for ($d = 1; $d <= $daysInMonth; $d++) {

        $day = date('N', strtotime("$tahun-$bulan-$d"));

        switch ($day) {
            case 1: $weekdayCount['senin']++; break;
            case 2: $weekdayCount['selasa']++; break;
            case 3: $weekdayCount['rabu']++; break;
            case 4: $weekdayCount['kamis']++; break;
            case 5: $weekdayCount['jumat']++; break;
            case 6: $weekdayCount['sabtu']++; break;
            case 7: $weekdayCount['minggu']++; break;
        }
    }

    $builder = $db->table('va_owner')
        ->select('
            va_owner_va,
            anggota_id,
            titpar_namatempat,
            titpar_lokasi,
            anggota_nama,
            senin,
            selasa,
            rabu,
            kamis,
            jumat,
            sabtu,
            minggu,
            mingguan,
            bulanan,
            tahunan
        ')
        ->join('potensi', 'va_owner.va_owner_va = potensi.potensi_va', 'left')
        ->join('dishub_titpargrup', 'dishub_titpargrup.titpargrup_id = va_owner.va_owner_titpargrupid', 'left')
        ->join('dishub_anggota', 'dishub_anggota.anggota_id = dishub_titpargrup.titpargrup_anggotaid', 'left')
        ->join('dishub_titpar','dishub_titpargrup.titpargrup_titparid = dishub_titpar.titpar_id','left')
        ->where('anggota_id', $anggota_id);

    $query = $builder->get();
    $data = $query->getResultArray();

    foreach ($data as &$row) {

        $senin   = (int) ($row['senin'] ?? 0);
        $selasa  = (int) ($row['selasa'] ?? 0);
        $rabu    = (int) ($row['rabu'] ?? 0);
        $kamis   = (int) ($row['kamis'] ?? 0);
        $jumat   = (int) ($row['jumat'] ?? 0);
        $sabtu   = (int) ($row['sabtu'] ?? 0);
        $minggu  = (int) ($row['minggu'] ?? 0);
        $mingguan = (int) ($row['mingguan'] ?? 0);
        $bulanan  = (int) ($row['bulanan'] ?? 0);

        if ($bulanan > 0) {

            $total_bulanan = $bulanan;

        } elseif ($mingguan > 0) {

            $weeks = ceil($daysInMonth / 7);
            $total_bulanan = $mingguan * $weeks;

        } else {

            $total_bulanan =
                ($senin  * $weekdayCount['senin']) +
                ($selasa * $weekdayCount['selasa']) +
                ($rabu   * $weekdayCount['rabu']) +
                ($kamis  * $weekdayCount['kamis']) +
                ($jumat  * $weekdayCount['jumat']) +
                ($sabtu  * $weekdayCount['sabtu']) +
                ($minggu * $weekdayCount['minggu']);
        }

        $row['total_bulanan'] = $total_bulanan;
    }

    return $this->response->setJSON([
        'status' => 'success',
        'data' => $data
    ]);
}

    public function listTagihanAppOLD($anggota_id)
{
    $db = db_connect();

    $builder = $db->table('va_owner')
        ->select('va_owner_va, anggota_id, titpar_namatempat, titpar_lokasi, anggota_nama, senin, selasa, rabu, kamis, jumat, sabtu, minggu, mingguan, bulanan, tahunan')
        ->join('potensi', 'va_owner.va_owner_va = potensi.potensi_va', 'left')
        ->join('dishub_titpargrup', 'dishub_titpargrup.titpargrup_id = va_owner.va_owner_titpargrupid', 'left')
        ->join('dishub_anggota', 'dishub_anggota.anggota_id = dishub_titpargrup.titpargrup_anggotaid', 'left')
        ->join('dishub_titpar','dishub_titpargrup.titpargrup_titparid = dishub_titpar.titpar_id','left')
        ->where('anggota_id', $anggota_id);

    $query = $builder->get();
    $data = $query->getResultArray();

    // Process each record for monthly total
    foreach ($data as &$row) {
        // Convert all to integer for safety
        $senin   = (int) ($row['senin'] ?? 0);
        $selasa  = (int) ($row['selasa'] ?? 0);
        $rabu    = (int) ($row['rabu'] ?? 0);
        $kamis   = (int) ($row['kamis'] ?? 0);
        $jumat   = (int) ($row['jumat'] ?? 0);
        $sabtu   = (int) ($row['sabtu'] ?? 0);
        $minggu  = (int) ($row['minggu'] ?? 0);
        $mingguan = (int) ($row['mingguan'] ?? 0);
        $bulanan  = (int) ($row['bulanan'] ?? 0);

        if ($bulanan > 0) {
            $total_bulanan = $bulanan;
        } elseif ($mingguan > 0) {
            $total_bulanan = $mingguan * 4;
        } else {
            $total_bulanan = ($senin + $selasa + $rabu + $kamis + $jumat + $sabtu + $minggu) * 4;
        }

        $row['total_bulanan'] = $total_bulanan;
    }

    return $this->response->setJSON([
        'status' => 'success',
        'data' => $data
    ]);
}

public function detailVaApp($anggota_id){
    $db = db_connect();

    // Step 1: Get VA owners (base data)
    $builder = $db->table('va_owner')
        ->select('
            va_owner_anggotaid, 
            va_owner_va, 
            anggota_nama, 
            va_owner_hp,
            va_owner_expired, 

            titpar_namatempat,
            titpar_lokasi,
        ')
        ->join('dishub_titpargrup', 'dishub_titpargrup.titpargrup_id = va_owner.va_owner_titpargrupid', 'left')
        ->join('dishub_anggota', 'dishub_anggota.anggota_id = dishub_titpargrup.titpargrup_anggotaid', 'left')
        ->join('dishub_titpar','dishub_titpar.titpar_id = dishub_titpargrup.titpargrup_titparid','left')
        ->where('anggota_id', $anggota_id);

    $vaResults = $builder->get()->getResultArray();

     return $this->response->setJSON([
        'status' => 'success',
        'data' => $vaResults
    ]);


}

public function kekuranganTagihanAppOLD2($anggota_id)
{
    $bulan = date('n');
    $tahun = date('Y');

    $bulanPad = str_pad($bulan, 2, '0', STR_PAD_LEFT);
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

    $startYear = "{$tahun}-01-01";
    $endYtd    = date('Y-m-d', strtotime("$tahun-$bulanPad-$daysInMonth +1 day"));

    /* =========================
       HITUNG WEEKDAY YTD
    ========================= */

    $start = new DateTime("$tahun-01-01");
    $end   = new DateTime("$tahun-$bulanPad-$daysInMonth");

    $weekdayYtd = [
        'senin'=>0,'selasa'=>0,'rabu'=>0,'kamis'=>0,'jumat'=>0,'sabtu'=>0,'minggu'=>0
    ];

    while ($start <= $end) {
        $day = $start->format('N');

        switch ($day) {
            case 1: $weekdayYtd['senin']++; break;
            case 2: $weekdayYtd['selasa']++; break;
            case 3: $weekdayYtd['rabu']++; break;
            case 4: $weekdayYtd['kamis']++; break;
            case 5: $weekdayYtd['jumat']++; break;
            case 6: $weekdayYtd['sabtu']++; break;
            case 7: $weekdayYtd['minggu']++; break;
        }

        $start->modify('+1 day');
    }

    $weeksYtd = ceil(array_sum($weekdayYtd) / 7);

    /* =========================
       CASE TARGET TAHUN
    ========================= */

    $casePotensiTahun = "
    CASE 
        WHEN p.bulanan > 0 THEN p.bulanan * {$bulan}
        WHEN p.mingguan > 0 THEN p.mingguan * {$weeksYtd}
        ELSE
            (IFNULL(p.senin,0) * {$weekdayYtd['senin']} +
             IFNULL(p.selasa,0) * {$weekdayYtd['selasa']} +
             IFNULL(p.rabu,0) * {$weekdayYtd['rabu']} +
             IFNULL(p.kamis,0) * {$weekdayYtd['kamis']} +
             IFNULL(p.jumat,0) * {$weekdayYtd['jumat']} +
             IFNULL(p.sabtu,0) * {$weekdayYtd['sabtu']} +
             IFNULL(p.minggu,0) * {$weekdayYtd['minggu']})
    END
    ";

    /* =========================
       SUBQUERY KENDALA YTD
    ========================= */

    $kendalaJoin = "
    (
        SELECT 
            k.kendala_va,

            SUM(
                (
                    CASE 
                        WHEN p.bulanan > 0 THEN p.bulanan / DAY(LAST_DAY(k.created_at))
                        WHEN p.mingguan > 0 THEN p.mingguan / 7
                        ELSE
                            CASE DAYOFWEEK(k.created_at)
                                WHEN 2 THEN IFNULL(p.senin,0)
                                WHEN 3 THEN IFNULL(p.selasa,0)
                                WHEN 4 THEN IFNULL(p.rabu,0)
                                WHEN 5 THEN IFNULL(p.kamis,0)
                                WHEN 6 THEN IFNULL(p.jumat,0)
                                WHEN 7 THEN IFNULL(p.sabtu,0)
                                WHEN 1 THEN IFNULL(p.minggu,0)
                            END
                    END
                )
                *
                (
                    CASE k.kategori
                        WHEN 1 THEN 1
                        WHEN 2 THEN 0.5
                        WHEN 3 THEN 0.25
                        ELSE 0
                    END
                )
            ) AS kendala_tahun

        FROM kendala k
        JOIN potensi p ON p.potensi_va = k.kendala_va
        WHERE k.created_at >= '{$startYear}'
        AND k.created_at < '{$endYtd}'
        AND k.approval = 1
        GROUP BY k.kendala_va
    ) k
    ";

    /* =========================
       MAIN QUERY
    ========================= */

    $builder = $this->db->table('va_owner vo');

    $builder->select([
        'vo.va_owner_va',
        'g.titpargrup_anggotaid',
        'g.titpargrup_titparid',
        't.titpar_klrhnid',
        'a.anggota_nama',
        'a.anggota_id',
        't.titpar_namatempat',
        't.titpar_lokasi',

        "{$casePotensiTahun} AS target_tahun",
        "IFNULL(k.kendala_tahun,0) AS kendala_tahun",

        "(
            SELECT IFNULL(SUM(t2.transaksi_nominal),0)
            FROM transaksi t2
            WHERE t2.transaksi_va = vo.va_owner_va
            AND t2.transaksi_tanggal >= '{$startYear}'
            AND t2.transaksi_tanggal < '{$endYtd}'
        ) AS realisasi_tahun",

        "ROUND((
            (
                SELECT IFNULL(SUM(t2.transaksi_nominal),0)
                FROM transaksi t2
                WHERE t2.transaksi_va = vo.va_owner_va
                AND t2.transaksi_tanggal >= '{$startYear}'
                AND t2.transaksi_tanggal < '{$endYtd}'
            ) 
            / NULLIF(GREATEST({$casePotensiTahun} - IFNULL(k.kendala_tahun,0),0),0)
        ) * 100,2) AS persentase_tahun"
    ]);

    $builder->join('potensi p', 'p.potensi_va = vo.va_owner_va', 'left');
    $builder->join('dishub_titpargrup g', 'g.titpargrup_id = vo.va_owner_titpargrupid');
    $builder->join('dishub_anggota a', 'a.anggota_id = g.titpargrup_anggotaid');
    $builder->join('dishub_titpar t', 't.titpar_id = g.titpargrup_titparid');
    $builder->join($kendalaJoin, 'k.kendala_va = vo.va_owner_va', 'left', false);

    $builder->where('a.anggota_id', $anggota_id);

    $vaResults = $builder->get()->getResultArray();

    return $this->response->setJSON([
        'status' => 'success',
        'data' => $vaResults
    ]);
}

public function kekuranganTagihanApp($anggota_id)
{
    $db = db_connect();

    // Perhitungan YTD menggunakan snapshot potensi yang berlaku setiap tanggal,
    // sama seperti rekap pada halaman detail VA.
    $bulanBerjalan = (int) date('n');
    $tahunBerjalan = (int) date('Y');
    $startYear = sprintf('%04d-01-01', $tahunBerjalan);
    $endPeriod = date('Y-m-d', strtotime(sprintf('%04d-%02d-01', $tahunBerjalan, $bulanBerjalan) . ' +1 month'));

    $owners = $db->table('va_owner vo')
        ->select('vo.va_owner_va, a.anggota_id, a.anggota_nama, t.titpar_namatempat, t.titpar_lokasi')
        ->join('dishub_titpargrup g', 'g.titpargrup_id = vo.va_owner_titpargrupid')
        ->join('dishub_anggota a', 'a.anggota_id = g.titpargrup_anggotaid')
        ->join('dishub_titpar t', 't.titpar_id = g.titpargrup_titparid')
        ->where('a.anggota_id', $anggota_id)
        ->get()->getResultArray();

    $result = [];
    foreach ($owners as $owner) {
        $va = $owner['va_owner_va'];
        $target = 0;
        $kendala = 0;
        $potensiAktif = $db->table('potensi')
            ->where('potensi_va', $va)
            ->get()->getRowArray() ?: [];
        $potensiHistory = $db->table('potensi_riwayat')
            ->where('potensi_va', $va)
            ->orderBy('berlaku_mulai', 'DESC')
            ->get()->getResultArray();

        $kendalaRows = $db->table('kendala')
            ->where('kendala_va', $va)
            ->where('created_at >=', $startYear)
            ->where('created_at <', $endPeriod)
            ->where('approval', 1)
            ->where('deleted_at', null)
            ->get()->getResultArray();

        for ($timestamp = strtotime($startYear); $timestamp < strtotime($endPeriod); $timestamp += 86400) {
            $date = date('Y-m-d', $timestamp);
            $snapshot = null;
            foreach ($potensiHistory as $history) {
                if ($history['berlaku_mulai'] <= $date
                    && (empty($history['berlaku_sampai']) || $history['berlaku_sampai'] >= $date)) {
                    $snapshot = $history;
                    break;
                }
            }
            // Jika belum ada riwayat sama sekali, gunakan potensi aktif lama.
            if (!$snapshot && !$potensiHistory) {
                $snapshot = $potensiAktif ?: null;
            }
            if (!$snapshot) continue;

            $daysInMonth = (int) date('t', $timestamp);
            $weekday = (int) date('N', $timestamp);
            $fields = ['senin','selasa','rabu','kamis','jumat','sabtu','minggu'];
            if ((float)($snapshot['bulanan'] ?? 0) > 0) {
                $nilaiHarian = (float)$snapshot['bulanan'] / $daysInMonth;
            } elseif ((float)($snapshot['mingguan'] ?? 0) > 0) {
                $nilaiHarian = (float)$snapshot['mingguan'] / 7;
            } else {
                $nilaiHarian = (float)($snapshot[$fields[$weekday - 1]] ?? 0);
            }
            $target += $nilaiHarian;

            foreach ($kendalaRows as $k) {
                if (date('Y-m-d', strtotime($k['created_at'])) !== $date) continue;
                $bobot = [1 => 1, 2 => 0.5, 3 => 0.25][$k['kategori']] ?? 0;
                $kendala += $nilaiHarian * $bobot;
            }
        }

        $realisasi = (float) ($db->table('transaksi')
            ->selectSum('transaksi_nominal')
            ->where('transaksi_va', $va)
            ->where('transaksi_tanggal >=', $startYear)
            ->where('transaksi_tanggal <', $endPeriod)
            ->get()->getRow('transaksi_nominal') ?? 0);
        $targetSetelahKendala = max($target - $kendala, 0);

        $result[] = array_merge($owner, [
            'senin' => $potensiAktif['senin'] ?? 0,
            'selasa' => $potensiAktif['selasa'] ?? 0,
            'rabu' => $potensiAktif['rabu'] ?? 0,
            'kamis' => $potensiAktif['kamis'] ?? 0,
            'jumat' => $potensiAktif['jumat'] ?? 0,
            'sabtu' => $potensiAktif['sabtu'] ?? 0,
            'minggu' => $potensiAktif['minggu'] ?? 0,
            'mingguan' => $potensiAktif['mingguan'] ?? 0,
            'bulanan' => $potensiAktif['bulanan'] ?? null,
            'target_tahun' => (string) $targetSetelahKendala,
            'realisasi_tahun' => (string) $realisasi,
            'kendala_tahun' => $kendala,
            'target_setelah_kendala' => $targetSetelahKendala,
            'kekurangan' => max($targetSetelahKendala - $realisasi, 0),
            'persentase_tahun' => (string) ($targetSetelahKendala > 0 ? ($realisasi / $targetSetelahKendala) * 100 : 0),
        ]);
    }

    return $this->response->setJSON(['status' => 'success', 'data' => $result]);

    $bulan = date('n');
    $tahun = date('Y');

    $startYear = "{$tahun}-01-01";
    $endPeriod = date('Y-m-d', strtotime("$startYear +$bulan month"));

    // =========================
    // HITUNG WEEKDAY YTD
    // =========================
    $start = new DateTime($startYear);
    $end   = new DateTime(date('Y-m-d', strtotime("$endPeriod -1 day")));

    $weekdayYtd = [
        'senin'=>0,'selasa'=>0,'rabu'=>0,'kamis'=>0,'jumat'=>0,'sabtu'=>0,'minggu'=>0
    ];

    while ($start <= $end) {
        $day = $start->format('N');

        switch ($day) {
            case 1: $weekdayYtd['senin']++; break;
            case 2: $weekdayYtd['selasa']++; break;
            case 3: $weekdayYtd['rabu']++; break;
            case 4: $weekdayYtd['kamis']++; break;
            case 5: $weekdayYtd['jumat']++; break;
            case 6: $weekdayYtd['sabtu']++; break;
            case 7: $weekdayYtd['minggu']++; break;
        }

        $start->modify('+1 day');
    }

    // =========================
    // CASE TARGET YTD
    // =========================
    $casePotensi = "
    (
        CASE 
            WHEN p.bulanan IS NOT NULL AND p.bulanan > 0 
                THEN p.bulanan * {$bulan}

            WHEN p.mingguan IS NOT NULL AND p.mingguan > 0 
                THEN p.mingguan * FLOOR(
                    ({$weekdayYtd['senin']}+
                     {$weekdayYtd['selasa']}+
                     {$weekdayYtd['rabu']}+
                     {$weekdayYtd['kamis']}+
                     {$weekdayYtd['jumat']}+
                     {$weekdayYtd['sabtu']}+
                     {$weekdayYtd['minggu']}) / 7
                )

            ELSE
                (IFNULL(p.senin,0) * {$weekdayYtd['senin']} +
                 IFNULL(p.selasa,0) * {$weekdayYtd['selasa']} +
                 IFNULL(p.rabu,0) * {$weekdayYtd['rabu']} +
                 IFNULL(p.kamis,0) * {$weekdayYtd['kamis']} +
                 IFNULL(p.jumat,0) * {$weekdayYtd['jumat']} +
                 IFNULL(p.sabtu,0) * {$weekdayYtd['sabtu']} +
                 IFNULL(p.minggu,0) * {$weekdayYtd['minggu']})
        END
    )
    ";

    // =========================
    // QUERY UTAMA
    // =========================
    $builder = $db->table('va_owner vo');

    $builder->select([
        'vo.va_owner_va',
        'a.anggota_id',
        'a.anggota_nama',
        't.titpar_namatempat',
        't.titpar_lokasi',

        // potensi (dipakai untuk kendala)
        'p.senin','p.selasa','p.rabu','p.kamis','p.jumat','p.sabtu','p.minggu',
        'p.mingguan','p.bulanan',

        "{$casePotensi} AS target_tahun",

        "(SELECT IFNULL(SUM(t2.transaksi_nominal),0)
          FROM transaksi t2
          WHERE t2.transaksi_va = vo.va_owner_va
          AND t2.transaksi_tanggal >= '{$startYear}'
          AND t2.transaksi_tanggal < '{$endPeriod}'
        ) AS realisasi_tahun"
    ]);

    $builder->join('potensi p', 'p.potensi_va = vo.va_owner_va', 'left');
    $builder->join('dishub_titpargrup g', 'g.titpargrup_id = vo.va_owner_titpargrupid');
    $builder->join('dishub_anggota a', 'a.anggota_id = g.titpargrup_anggotaid');
    $builder->join('dishub_titpar t', 't.titpar_id = g.titpargrup_titparid');
    $builder->where('a.anggota_id', $anggota_id);

    $vaResults = $builder->get()->getResultArray();

    // =========================
    // AMBIL KENDALA YTD
    // =========================
    $kendalaRows = $db->table('kendala k')
        ->select("
            k.kendala_va,
            k.kategori,
            DAYOFWEEK(k.created_at) as hari
        ")
        ->where('k.created_at >=', $startYear)
        ->where('k.created_at <', $endPeriod)
        ->where('k.approval', 1)
        ->get()
        ->getResultArray();

    $mapKendala = [];
    foreach ($kendalaRows as $k) {
        $mapKendala[$k['kendala_va']][] = $k;
    }

    // =========================
    // HITUNG KENDALA
    // =========================
    foreach ($vaResults as &$row) {

        $va = $row['va_owner_va'];
        $listKendala = $mapKendala[$va] ?? [];

        $senin = (float) ($row['senin'] ?? 0);
        $selasa = (float) ($row['selasa'] ?? 0);
        $rabu = (float) ($row['rabu'] ?? 0);
        $kamis = (float) ($row['kamis'] ?? 0);
        $jumat = (float) ($row['jumat'] ?? 0);
        $sabtu = (float) ($row['sabtu'] ?? 0);
        $minggu = (float) ($row['minggu'] ?? 0);
        $mingguan = (float) ($row['mingguan'] ?? 0);
        $bulanan = (float) ($row['bulanan'] ?? 0);

        $kendala_total = 0;

        foreach ($listKendala as $k) {

            // nilai harian
            if ($bulanan > 0) {
                $nilai_harian = $bulanan / 30;
            } elseif ($mingguan > 0) {
                $nilai_harian = $mingguan / 7;
            } else {
                switch ($k['hari']) {
                    case 2: $nilai_harian = $senin; break;
                    case 3: $nilai_harian = $selasa; break;
                    case 4: $nilai_harian = $rabu; break;
                    case 5: $nilai_harian = $kamis; break;
                    case 6: $nilai_harian = $jumat; break;
                    case 7: $nilai_harian = $sabtu; break;
                    case 1: $nilai_harian = $minggu; break;
                    default: $nilai_harian = 0;
                }
            }

            // bobot
            switch ($k['kategori']) {
                case 1: $bobot = 1; break;
                case 2: $bobot = 0.5; break;
                case 3: $bobot = 0.25; break;
                default: $bobot = 0;
            }

            $kendala_total += $nilai_harian * $bobot;
        }

        $target = (float) $row['target_tahun'];
        $realisasi = (float) $row['realisasi_tahun'];

        $target_after = max($target - $kendala_total, 0);

        $row['kendala_tahun'] = $kendala_total;
        $row['target_setelah_kendala'] = $target_after;
        $row['kekurangan'] = max($target_after - $realisasi, 0);
        $row['target_tahun'] = (string)$target_after;
        $row['persentase_tahun'] = $target_after > 0
            ? ($realisasi / $target_after) * 100
            : 0;
        $row['persentase_tahun'] = (string)$row['persentase_tahun'];
    }

    return $this->response->setJSON([
        'status' => 'success',
        'data' => $vaResults
    ]);
}

public function kekuranganTagihanAppOLDNEW($anggota_id)
{
    $bulan = date('n');
    $tahun = date('Y');

    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

    $startYear = "{$tahun}-01-01";

    /* =========================
       HITUNG WEEKDAY YTD
    ========================= */

    $start = new DateTime("$tahun-01-01");
    $end   = new DateTime("$tahun-$bulan-$daysInMonth");

    $weekdayYtd = [
        'senin'=>0,'selasa'=>0,'rabu'=>0,'kamis'=>0,'jumat'=>0,'sabtu'=>0,'minggu'=>0
    ];

    while ($start <= $end) {

        $day = $start->format('N');

        switch ($day) {
            case 1: $weekdayYtd['senin']++; break;
            case 2: $weekdayYtd['selasa']++; break;
            case 3: $weekdayYtd['rabu']++; break;
            case 4: $weekdayYtd['kamis']++; break;
            case 5: $weekdayYtd['jumat']++; break;
            case 6: $weekdayYtd['sabtu']++; break;
            case 7: $weekdayYtd['minggu']++; break;
        }

        $start->modify('+1 day');
    }

    /* =========================
       CASE TARGET TAHUN
    ========================= */

    $casePotensiTahun = "
    (
        CASE 
            WHEN p.bulanan IS NOT NULL AND p.bulanan > 0 
                THEN p.bulanan * {$bulan}

            WHEN p.mingguan IS NOT NULL AND p.mingguan > 0 
                THEN p.mingguan * FLOOR(
                    ({$weekdayYtd['senin']}+
                     {$weekdayYtd['selasa']}+
                     {$weekdayYtd['rabu']}+
                     {$weekdayYtd['kamis']}+
                     {$weekdayYtd['jumat']}+
                     {$weekdayYtd['sabtu']}+
                     {$weekdayYtd['minggu']}) / 7
                )

            ELSE
                (IFNULL(p.senin,0) * {$weekdayYtd['senin']} +
                 IFNULL(p.selasa,0) * {$weekdayYtd['selasa']} +
                 IFNULL(p.rabu,0) * {$weekdayYtd['rabu']} +
                 IFNULL(p.kamis,0) * {$weekdayYtd['kamis']} +
                 IFNULL(p.jumat,0) * {$weekdayYtd['jumat']} +
                 IFNULL(p.sabtu,0) * {$weekdayYtd['sabtu']} +
                 IFNULL(p.minggu,0) * {$weekdayYtd['minggu']})
        END
    )
    ";

    $builder = $this->db->table('va_owner vo');

    $builder->select([

        'vo.va_owner_va',
        'g.titpargrup_anggotaid',
        'g.titpargrup_titparid',
        't.titpar_klrhnid',
        'a.anggota_nama',
        'a.anggota_id',
        't.titpar_namatempat',
        't.titpar_lokasi',

        "{$casePotensiTahun} AS target_tahun",

        "(
            SELECT IFNULL(SUM(t2.transaksi_nominal),0)
            FROM transaksi t2
            WHERE t2.transaksi_va = vo.va_owner_va
            AND t2.transaksi_tanggal >= '{$startYear}'
            AND t2.transaksi_tanggal < DATE_ADD('{$startYear}', INTERVAL {$bulan} MONTH)
        ) AS realisasi_tahun",

        "(
            (
                SELECT IFNULL(SUM(t2.transaksi_nominal),0)
                FROM transaksi t2
                WHERE t2.transaksi_va = vo.va_owner_va
                AND t2.transaksi_tanggal >= '{$startYear}'
                AND t2.transaksi_tanggal < DATE_ADD('{$startYear}', INTERVAL {$bulan} MONTH)
            ) / NULLIF({$casePotensiTahun},0)
        ) * 100 AS persentase_tahun"
    ]);

    $builder->join('potensi p', 'p.potensi_va = vo.va_owner_va', 'left');
    $builder->join('dishub_titpargrup g', 'g.titpargrup_id = vo.va_owner_titpargrupid');
    $builder->join('dishub_anggota a', 'a.anggota_id = g.titpargrup_anggotaid');
    $builder->join('dishub_titpar t', 't.titpar_id = g.titpargrup_titparid');

    $builder->where('a.anggota_id', $anggota_id);

    $vaResults = $builder->get()->getResultArray();

    return $this->response->setJSON([
        'status' => 'success',
        'data' => $vaResults
    ]);
}
public function kekuranganTagihanAppOLD($anggota_id)
{

       if (empty($bulan)) $bulan = date('n');
    if (empty($tahun)) $tahun = date('Y');
 
    $casePotensi = "
        (
            CASE 
                WHEN p.bulanan IS NOT NULL AND p.bulanan > 0 
                    THEN p.bulanan
                WHEN p.mingguan IS NOT NULL AND p.mingguan > 0 
                    THEN p.mingguan * 4
                ELSE 
                    (IFNULL(p.senin,0) + IFNULL(p.selasa,0) + IFNULL(p.rabu,0) 
                     + IFNULL(p.kamis,0) + IFNULL(p.jumat,0)
                     + IFNULL(p.sabtu,0) + IFNULL(p.minggu,0)) * 4
            END
        )
    ";

    // === MAIN BUILDER ===
    $builder = $this->db->table('va_owner vo');
    $builder->select([
        'vo.va_owner_va AS va_owner_va',
        'g.titpargrup_anggotaid',
        'g.titpargrup_titparid',
        't.titpar_klrhnid',
        'a.anggota_nama',
        'a.anggota_id',
        't.titpar_namatempat',
        't.titpar_lokasi',

        // === target tahun berjalan (potensi * bulan) ===
        "({$casePotensi} * {$bulan}) AS target_tahun",

        // === realisasi tahun berjalan ===
        "(
            SELECT IFNULL(SUM(t2.transaksi_nominal), 0)
            FROM transaksi t2
            WHERE t2.transaksi_va = vo.va_owner_va
            AND YEAR(t2.transaksi_tanggal) = {$tahun}
            AND MONTH(t2.transaksi_tanggal) BETWEEN 1 AND {$bulan}
        ) AS realisasi_tahun",

        // === persentase tahun berjalan ===
        "(
            (
                SELECT IFNULL(SUM(t2.transaksi_nominal),0)
                FROM transaksi t2
                WHERE t2.transaksi_va = vo.va_owner_va
                AND YEAR(t2.transaksi_tanggal) = {$tahun}
                AND MONTH(t2.transaksi_tanggal) BETWEEN 1 AND {$bulan}
            ) / NULLIF(({$casePotensi} * {$bulan}),0)
        ) * 100 AS persentase_tahun"
    ]);

    // === JOINS ===
    $builder->join('potensi p', 'p.potensi_va = vo.va_owner_va', 'left');
    $builder->join('dishub_titpargrup g', 'g.titpargrup_id = vo.va_owner_titpargrupid');
    $builder->join('dishub_anggota a', 'a.anggota_id = g.titpargrup_anggotaid');
    $builder->join('dishub_titpar t', 't.titpar_id = g.titpargrup_titparid');

        $builder->where('anggota_id',$anggota_id);
         $vaResults = $builder->get()->getResultArray();

         return $this->response->setJSON([
            'status' => 'success',
            'data' => $vaResults
        ]);

}


public function detailVaAppOLD($anggota_id,$bulan,$tahun)
    {
          $db = db_connect();

    // Step 1: Get VA owners (base data)
    $builder = $db->table('va_owner')
        ->select('
            va_owner_anggotaid, 
            va_owner_va, 
            anggota_nama, 
            va_owner_expired, 
        ')
        ->join('dishub_titpargrup', 'dishub_titpargrup.titpargrup_id = va_owner.va_owner_titpargrupid', 'left')
        ->join('dishub_anggota', 'dishub_anggota.anggota_id = dishub_titpargrup.titpargrup_anggotaid', 'left')
        ->where('anggota_id', $anggota_id);

    $vaResults = $builder->get()->getResultArray();


    foreach ($vaResults as &$row) {
        $va = $row['va_owner_va'];

        $potensiAktif = $this->potensiBerlaku($va, date('Y-m-d'));
        $potensi = $potensiAktif ? [$potensiAktif] : [];

        $row['potensi'] = $potensi;
    }

    // Step 2: For each VA, get this month's transactions
    foreach ($vaResults as &$row) {
        $va = $row['va_owner_va'];

        $transaksi = $db->table('transaksi')
            ->where('transaksi_va', $va)
            ->where('MONTH(transaksi_tanggal)', $bulan)
            ->where('YEAR(transaksi_tanggal)', $tahun)
            ->orderBy('transaksi_tanggal','desc')
            ->get()
            ->getResultArray();

        $row['transaction'] = $transaksi;
    }

    return $this->response->setJSON([
        'status' => 'success',
        'data' => $vaResults
    ]);
    }


    public function detailAnggotaApp($anggota_id){
         $db = \Config\Database::connect();

        $builder = $db->table('dishub_anggota');
        $builder->select('*');
        
        $builder->where('anggota_id',$anggota_id);

        $query = $builder->get();

        $data = $query->getResultArray();

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function detailTitparApp($anggota_id){
    $db = \Config\Database::connect();

    // Subquery to filter titpar by anggota
    $subquery = $db->table('dishub_titpargrup')
                   ->select('titpargrup_titparid')
                   ->where('titpargrup_anggotaid', $anggota_id)
                   ->where('titpar_status',3)
                   ->getCompiledSelect();

    // Main query
    $builder = $db->table('dishub_titpar');
    $builder->select('
        dishub_titpar.titpar_id, 
        dishub_titpar.titpar_noreg,
        dishub_titpar.titpar_newnoreg,
        dishub_titpar.titpar_namatempat,
        dishub_titpar.titpar_lokasi,
        dishub_titpar.titpar_pmlknama,
        dishub_titpar.titpar_retritypeid,
        dishub_titpar.titpar_klrhnid,
        dishub_titpar.titpar_longitude,
        dishub_titpar.titpar_latitude,
        dishub_titpar.titpar_alihfungsi,
        dishub_titpar.zona_id,
        dishub_titpar.titpar_status,
        dishub_titpar.titpar_denah,
        dishub_titpar.titpar_jenis_kendaraan,
        kelurahan.kelurahan_nama,
        kelurahan.kecamatan_nama,
        dishub_anggota.anggota_nama,
        dishub_anggota.anggota_id,
        dishub_anggota.anggota_foto,
        dishub_titpargrup.titpargrup_staanggota,
        va_owner.va_owner_va,
        va_owner.va_owner_hp,
        dishub_titpargrup.titpargrup_masaberlakuend
    ');
    $builder->join('dishub_titpargrup','dishub_titpargrup.titpargrup_titparid = dishub_titpar.titpar_id','left');
    $builder->join('dishub_anggota','dishub_anggota.anggota_id = dishub_titpargrup.titpargrup_anggotaid','left');
    // Wilayah bersifat pelengkap; jangan sampai titik parkir hilang dari
    // respons aplikasi hanya karena relasi kelurahan belum cocok.
    $builder->join('kelurahan','kelurahan.id = dishub_titpar.titpar_klrhnid','left');
    $builder->join('retritype','retritype.retritype_id = dishub_titpar.titpar_retritypeid');
    $builder->join('va_owner','va_owner.va_owner_titpargrupid = dishub_titpargrup.titpargrup_id','left');
    
    $builder->where("dishub_titpar.titpar_id IN ($subquery)", NULL, FALSE);

    $query = $builder->get();
    $rows = $query->getResultArray();

    $requestedGroupId = (int) ($this->request->getGet('titpargrup_id') ?: 0);
    $potensiTitparId = 0;
    $potensiTerbaru = null;
    if ($requestedGroupId > 0) {
        $requestedGroup = $db->table('dishub_titpargrup')
            ->select('titpargrup_titparid')
            ->where('titpargrup_id', $requestedGroupId)
            ->where('titpargrup_anggotaid', (int) $anggota_id)
            ->where('titpargrup_staanggota', 'KOORDINATOR')
            ->get()->getRowArray();
        if ($requestedGroup) {
            $potensiTitparId = (int) $requestedGroup['titpargrup_titparid'];
            $vaOwner = $db->table('va_owner')
                ->select('va_owner_va')
                ->where('va_owner_titpargrupid', $requestedGroupId)
                ->orderBy('va_owner_id', 'DESC')
                ->get(1)->getRowArray();
            if ($vaOwner && !empty($vaOwner['va_owner_va'])) {
                $potensiTerbaru = $db->table('potensi_riwayat')
                    ->where('potensi_va', $vaOwner['va_owner_va'])
                    ->orderBy('berlaku_mulai', 'DESC')
                    ->get(1)->getRowArray();
                $potensiTerbaru ??= $db->table('potensi')
                    ->where('potensi_va', $vaOwner['va_owner_va'])
                    ->get()->getRowArray();
                if ($potensiTerbaru) {
                    $potensiTerbaru['potensi_va'] = $vaOwner['va_owner_va'];
                }
            }
        }
    }

    $coordinatorSchedules = [];
    $titparIds = array_values(array_unique(array_column($rows, 'titpar_id')));
    if ($titparIds) {
        $schedules = $db->table('dishub_titpargrup')
            ->select('titpargrup_titparid, titpargrup_shifthari, titpargrup_shiftjammulai, titpargrup_shiftjamselesai')
            ->whereIn('titpargrup_titparid', $titparIds)
            ->where('titpargrup_staanggota', 'KOORDINATOR')
            ->orderBy('titpargrup_id', 'DESC')
            ->get()->getResultArray();
        foreach ($schedules as $schedule) {
            $scheduleTitparId = $schedule['titpargrup_titparid'];
            if (!isset($coordinatorSchedules[$scheduleTitparId])) {
                $coordinatorSchedules[$scheduleTitparId] = $schedule;
            }
        }
    }

    // Samakan label zona dengan halaman web.
    $zonaNames = [
        1 => 'Blimbing 1', 2 => 'Blimbing 2',
        3 => 'Klojen 1', 4 => 'Klojen 2', 5 => 'Klojen 3', 6 => 'Klojen 4', 13 => 'Klojen 5',
        7 => 'Kedungkandang 1', 8 => 'Kedungkandang 2',
        9 => 'Sukun 1', 10 => 'Sukun 2',
        11 => 'Lowokwaru 1', 12 => 'Lowokwaru 2',
    ];

    // Transform flat result into nested JSON
    $data = [];
    foreach ($rows as $row) {
        $id = $row['titpar_id'];
        $schedule = $coordinatorSchedules[$id] ?? [];

        // If this titpar_id is not added yet, create it
        if (!isset($data[$id])) {
            $data[$id] = [
                'titpar_id' => $row['titpar_id'],
                'titpar_noreg' => $row['titpar_noreg'],
                'titpar_newnoreg' => $row['titpar_newnoreg'],
                'titpar_namatempat' => $row['titpar_namatempat'],
                'titpar_lokasi' => $row['titpar_lokasi'],
                'titpar_pmlknama' => $row['titpar_pmlknama'],
                'titpar_retritypeid' => $row['titpar_retritypeid'],
                'titpar_klrhnid' => $row['titpar_klrhnid'],
                'titpar_longitude' => $row['titpar_longitude'],
                'titpar_latitude' => $row['titpar_latitude'],
                'titpar_alihfungsi' => $row['titpar_alihfungsi'],
                'zona_id' => $row['zona_id'],
                'zona_nama' => $zonaNames[(int) $row['zona_id']] ?? (($row['zona_id'] !== null && $row['zona_id'] !== '') ? 'Zona ' . $row['zona_id'] : null),
                'titpar_status' => $row['titpar_status'],
                'titpar_denah' => $row['titpar_denah'],
                'titpar_jenis_kendaraan' => $row['titpar_jenis_kendaraan'],
                'denah_url' => !empty($row['titpar_denah'])
                    ? base_url('uploads/denah/' . rawurlencode(basename($row['titpar_denah'])))
                    : null,
                'denah_path' => !empty($row['titpar_denah'])
                    ? basename($row['titpar_denah'])
                    : null,
                'kelurahan_nama' => $row['kelurahan_nama'],
                'kecamatan_nama' => $row['kecamatan_nama'],
                // Alias dipertahankan agar versi aplikasi yang memakai nama
                // field singkat tetap dapat menampilkan data wilayah.
                'zona' => $zonaNames[(int) $row['zona_id']] ?? (($row['zona_id'] !== null && $row['zona_id'] !== '') ? 'Zona ' . $row['zona_id'] : null),
                'kecamatan' => $row['kecamatan_nama'],
                'kelurahan' => $row['kelurahan_nama'],
                'titpargrup_shifthari' => $schedule['titpargrup_shifthari'] ?? null,
                'titpargrup_shiftjammulai' => $schedule['titpargrup_shiftjammulai'] ?? null,
                'titpargrup_shiftjamselesai' => $schedule['titpargrup_shiftjamselesai'] ?? null,
                'potensi_terbaru' => (int) $id === $potensiTitparId ? $potensiTerbaru : null,
                'anggota' => []
            ];
        }

        // Add anggota info
        if ($row['anggota_nama']) { // check to avoid null
            $data[$id]['anggota'][] = [
                'anggota_nama' => $row['anggota_nama'],
                'anggota_id' => $row['anggota_id'],
                'anggota_foto' => $row['anggota_foto'],
                'foto_url' => !empty($row['anggota_foto']) ? base_url('uploads/anggota_foto/' . rawurlencode($row['anggota_foto'])) : null,
                'titpargrup_staanggota' => $row['titpargrup_staanggota'],
                'va_owner_va' => $row['va_owner_va'],
                'va_owner_hp' => $row['va_owner_hp'],
                'titpargrup_masaberlakuend' => $row['titpargrup_masaberlakuend'],
            ];
        }
    }

    // Reset array keys to be numeric
    $data = array_values($data);

    return $this->response->setJSON([
        'status' => 'success',
        'data' => $data
    ]);
}

public function targetSetoranApp($anggota_id)
{
    $db = \Config\Database::connect();

    $bulan = date('n');
    $tahun = date('Y');

    $bulanPad = str_pad($bulan, 2, '0', STR_PAD_LEFT);
    $startMonth = "{$tahun}-{$bulanPad}-01";
    $endMonth   = date('Y-m-d', strtotime("$startMonth +1 month"));

    /* ======================
       HITUNG WEEKDAY BULAN
    ====================== */

    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

    $weekday = [
        'senin'=>0,'selasa'=>0,'rabu'=>0,'kamis'=>0,'jumat'=>0,'sabtu'=>0,'minggu'=>0
    ];

    for ($d=1;$d<=$daysInMonth;$d++){
        $day = date('N', strtotime("$tahun-$bulanPad-$d"));

        switch ($day) {
            case 1: $weekday['senin']++; break;
            case 2: $weekday['selasa']++; break;
            case 3: $weekday['rabu']++; break;
            case 4: $weekday['kamis']++; break;
            case 5: $weekday['jumat']++; break;
            case 6: $weekday['sabtu']++; break;
            case 7: $weekday['minggu']++; break;
        }
    }

    /* ======================
       GET VA OWNER
    ====================== */

    $vaOwners = $db->table('va_owner')
        ->select('
            va_owner_anggotaid,
            va_owner_va,
            anggota_nama,
            va_owner_expired
        ')
        ->join('dishub_titpargrup','dishub_titpargrup.titpargrup_id = va_owner.va_owner_titpargrupid','left')
        ->join('dishub_anggota','dishub_anggota.anggota_id = dishub_titpargrup.titpargrup_anggotaid','left')
        ->where('dishub_titpargrup.titpargrup_anggotaid',$anggota_id)
        ->get()
        ->getResultArray();

    /* ======================
       AMBIL KENDALA BULAN INI
    ====================== */

    $kendalaRows = $db->table('kendala k')
        ->select("
            k.kendala_va,
            k.kategori,
            DAYOFWEEK(k.created_at) as hari
        ")
        ->where('k.created_at >=', $startMonth)
        ->where('k.created_at <', $endMonth)
        ->where('k.approval', 1)
        ->get()
        ->getResultArray();

    $mapKendala = [];
    foreach ($kendalaRows as $k) {
        $mapKendala[$k['kendala_va']][] = $k;
    }

    $finalData = [];

    foreach ($vaOwners as $row) {

        $va = $row['va_owner_va'];

        /* ======================
           GET POTENSI
        ====================== */

        $potensi = $this->potensiBerlaku($va, $startMonth);

        // Pastikan response tetap lengkap untuk VA yang belum memiliki potensi.
        $senin = $selasa = $rabu = $kamis = $jumat = $sabtu = $minggu = 0;
        $mingguan = $bulanan = 0;

        $totalPotensi = 0;
        $kendalaTotal = 0;

        if ($potensi) {

            $senin   = (int)($potensi['senin'] ?? 0);
            $selasa  = (int)($potensi['selasa'] ?? 0);
            $rabu    = (int)($potensi['rabu'] ?? 0);
            $kamis   = (int)($potensi['kamis'] ?? 0);
            $jumat   = (int)($potensi['jumat'] ?? 0);
            $sabtu   = (int)($potensi['sabtu'] ?? 0);
            $minggu  = (int)($potensi['minggu'] ?? 0);
            $mingguan = (int)($potensi['mingguan'] ?? 0);
            $bulanan  = (int)($potensi['bulanan'] ?? 0);

            // ===== POTENSI =====
            // Hitung per hari agar perubahan di tengah bulan ikut terakomodasi.
            $totalPotensi = 0;
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = sprintf('%04d-%02d-%02d', $tahun, $bulan, $day);
                $daily = $this->potensiBerlaku($va, $date) ?: $potensi;
                $weekdayNo = (int) date('N', strtotime($date));
                if ((float)($daily['bulanan'] ?? 0) > 0) $totalPotensi += (float)$daily['bulanan'] / $daysInMonth;
                elseif ((float)($daily['mingguan'] ?? 0) > 0) $totalPotensi += (float)$daily['mingguan'] / 7;
                else $totalPotensi += (float)($daily[['senin','selasa','rabu','kamis','jumat','sabtu','minggu'][$weekdayNo - 1]] ?? 0);
            }

            // ===== KENDALA =====
            $listKendala = $mapKendala[$va] ?? [];

            foreach ($listKendala as $k) {

                if ($bulanan > 0) {

                    $nilai_harian = $bulanan / $daysInMonth;

                } elseif ($mingguan > 0) {

                    $nilai_harian = $mingguan / 7;

                } else {

                    switch ($k['hari']) {
                        case 2: $nilai_harian = $senin; break;
                        case 3: $nilai_harian = $selasa; break;
                        case 4: $nilai_harian = $rabu; break;
                        case 5: $nilai_harian = $kamis; break;
                        case 6: $nilai_harian = $jumat; break;
                        case 7: $nilai_harian = $sabtu; break;
                        case 1: $nilai_harian = $minggu; break;
                        default: $nilai_harian = 0;
                    }
                }

                switch ($k['kategori']) {
                    case 1: $bobot = 1; break;
                    case 2: $bobot = 0.5; break;
                    case 3: $bobot = 0.25; break;
                    default: $bobot = 0;
                }

                $kendalaTotal += $nilai_harian * $bobot;
            }
        }

        /* ======================
           GET TRANSAKSI
        ====================== */

        $transaksi = $db->table('transaksi')
            ->select('transaksi_id, transaksi_va, transaksi_tanggal, transaksi_nominal')
            ->where('transaksi_va',$va)
            ->where('transaksi_tanggal >=',$startMonth)
            ->where('transaksi_tanggal <',$endMonth)
            ->get()
            ->getResultArray();

        $totalTransaksi = !empty($transaksi)
            ? array_sum(array_column($transaksi,'transaksi_nominal'))
            : 0;

        // $finalData[] = [
        //     'va_owner_va'        => $row['va_owner_va'],
        //     'va_owner_anggotaid' => $row['va_owner_anggotaid'],
        //     'anggota_nama'       => $row['anggota_nama'],
        //     'va_owner_expired'   => $row['va_owner_expired'],

        //     'potensi_bulanan'    => $totalPotensi,
        //     'kendala_tagihan'    => $kendalaTotal,
        //     'target_setoran'     => max($totalPotensi - $kendalaTotal, 0),

        //     'transaksi'          => $transaksi,
        //     'total_transaksi'    => $totalTransaksi
        // ];

        $finalData[] = [
                'va_owner_va'        => $row['va_owner_va'],
                'va_owner_anggotaid' => $row['va_owner_anggotaid'],
                'anggota_nama'       => $row['anggota_nama'],
                'va_owner_expired'   => $row['va_owner_expired'],

                // format lama
                'potensi' => [
                    'senin' => $senin,
                    'selasa' => $selasa,
                    'rabu' => $rabu,
                    'kamis' => $kamis,
                    'jumat' => $jumat,
                    'sabtu' => $sabtu,
                    'minggu' => $minggu,
                    'mingguan' => $mingguan,
                    'bulanan' => $bulanan,
                    'total' => $totalPotensi - $kendalaTotal
                ],

                // format baru
                'potensi_bulanan' => $totalPotensi,
                'kendala_tagihan' => $kendalaTotal,
                'target_setoran'  => max($totalPotensi - $kendalaTotal, 0),

                'transaksi'       => $transaksi,
                'total_transaksi' => $totalTransaksi
            ];
    }

    return $this->response->setJSON($finalData);
}
    
    public function targetSetoranAppOLD3($anggota_id)
{
    $db = \Config\Database::connect();

    $bulan = date('n');
    $tahun = date('Y');

    $startMonth = "{$tahun}-{$bulan}-01";

    /* ======================
       HITUNG WEEKDAY BULAN
    ====================== */

    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

    $weekday = [
        'senin'=>0,'selasa'=>0,'rabu'=>0,'kamis'=>0,'jumat'=>0,'sabtu'=>0,'minggu'=>0
    ];

    for ($d=1;$d<=$daysInMonth;$d++){

        $day = date('N', strtotime("$tahun-$bulan-$d"));

        switch ($day) {
            case 1: $weekday['senin']++; break;
            case 2: $weekday['selasa']++; break;
            case 3: $weekday['rabu']++; break;
            case 4: $weekday['kamis']++; break;
            case 5: $weekday['jumat']++; break;
            case 6: $weekday['sabtu']++; break;
            case 7: $weekday['minggu']++; break;
        }
    }

    /* ======================
       GET VA OWNER
    ====================== */

    $vaOwners = $db->table('va_owner')
        ->select('
            va_owner_anggotaid,
            va_owner_va,
            anggota_nama,
            va_owner_expired
        ')
        ->join('dishub_titpargrup','dishub_titpargrup.titpargrup_id = va_owner.va_owner_titpargrupid','left')
        ->join('dishub_anggota','dishub_anggota.anggota_id = dishub_titpargrup.titpargrup_anggotaid','left')
        ->where('dishub_titpargrup.titpargrup_anggotaid',$anggota_id)
        ->get()
        ->getResultArray();

    $finalData = [];

    foreach ($vaOwners as $row) {

        $va = $row['va_owner_va'];

        /* ======================
           GET POTENSI
        ====================== */

        $potensi = $db->table('potensi')
            ->where('potensi_va',$va)
            ->get()
            ->getRowArray();

        $totalPotensi = 0;

        if ($potensi) {

            $senin   = (int)($potensi['senin'] ?? 0);
            $selasa  = (int)($potensi['selasa'] ?? 0);
            $rabu    = (int)($potensi['rabu'] ?? 0);
            $kamis   = (int)($potensi['kamis'] ?? 0);
            $jumat   = (int)($potensi['jumat'] ?? 0);
            $sabtu   = (int)($potensi['sabtu'] ?? 0);
            $minggu  = (int)($potensi['minggu'] ?? 0);
            $mingguan = (int)($potensi['mingguan'] ?? 0);
            $bulanan  = (int)($potensi['bulanan'] ?? 0);
          

            if ($bulanan > 0) {

                $totalPotensi = $bulanan;

            } elseif ($mingguan > 0) {

                $weeks = ceil($daysInMonth / 7);
                $totalPotensi = $mingguan * $weeks;

            } else {

                $totalPotensi =
                    ($senin  * $weekday['senin']) +
                    ($selasa * $weekday['selasa']) +
                    ($rabu   * $weekday['rabu']) +
                    ($kamis  * $weekday['kamis']) +
                    ($jumat  * $weekday['jumat']) +
                    ($sabtu  * $weekday['sabtu']) +
                    ($minggu * $weekday['minggu']);
                // echo (int)$totalPotensi;
                // exit();
                $potensi['total'] = $totalPotensi;
            }
        }

        /* ======================
           GET TRANSAKSI BULAN INI
        ====================== */

        $transaksi = $db->table('transaksi')
            ->select('transaksi_id, transaksi_va, transaksi_tanggal, transaksi_nominal')
            ->where('transaksi_va',$va)
            ->where('transaksi_tanggal >=',$startMonth)
            ->where('transaksi_tanggal <',"DATE_ADD('{$startMonth}', INTERVAL 1 MONTH)", false)
            ->get()
            ->getResultArray();

        $totalTransaksi = 0;

        if (!empty($transaksi)) {
            $totalTransaksi = array_sum(array_column($transaksi,'transaksi_nominal'));
        }

        $finalData[] = [
            'va_owner_va'        => $row['va_owner_va'],
            'va_owner_anggotaid' => $row['va_owner_anggotaid'],
            'anggota_nama'       => $row['anggota_nama'],
            'va_owner_expired'   => $row['va_owner_expired'],
            'potensi'            => $potensi,
           
            'transaksi'          => $transaksi,
            'total_transaksi'    => $totalTransaksi
        ];
    }

    return $this->response->setJSON($finalData);
}


    public function targetSetoranAppOLD($anggota_id){

          $db = \Config\Database::connect();

    // Get current month and year
    $month = date('m');
    $year  = date('Y');
    
    // Get all VA owners for this anggota
    $vaOwners = $db->table('va_owner')
        ->select('
            va_owner_anggotaid, 
            va_owner_va, 
            anggota_nama, 
            va_owner_expired
        ')
        ->join('dishub_titpargrup', 'dishub_titpargrup.titpargrup_id = va_owner.va_owner_titpargrupid', 'left')
        ->join('dishub_anggota', 'dishub_anggota.anggota_id = dishub_titpargrup.titpargrup_anggotaid', 'left')
        ->where('dishub_titpargrup.titpargrup_anggotaid', $anggota_id)
        ->get()
        ->getResultArray();

    $finalData = [];

    foreach ($vaOwners as $row) {
        $va = $row['va_owner_va'];

        // Get potensi for this VA
        $potensi = $db->table('potensi')
            ->select("
                potensi_id,
                potensi_va,
                senin, selasa, rabu, kamis, jumat, sabtu, minggu,
                mingguan, bulanan,
                CASE
                    WHEN bulanan > 0 THEN bulanan
                    WHEN mingguan > 0 THEN mingguan * 4
                    ELSE (senin + selasa + rabu + kamis + jumat + sabtu + minggu) * 4
                END AS total
            ")
            ->where('potensi_va', $va)
            ->get()
            ->getRowArray(); // expect one row per VA

        // Get transaksi for this VA (current month/year)
        $transaksi = $db->table('transaksi')
            ->select('transaksi_id, transaksi_va, transaksi_tanggal, transaksi_nominal')
            ->where('transaksi_va', $va)
            ->where('MONTH(transaksi_tanggal)', $month)
            ->where('YEAR(transaksi_tanggal)', $year)
            ->get()
            ->getResultArray();

        $totalTransaksi = 0;
        if (!empty($transaksi)) {
            $totalTransaksi = array_sum(array_column($transaksi, 'transaksi_nominal'));
        }

        // Build structured object
        $finalData[] = [
            'va_owner_va'        => $row['va_owner_va'],
            'va_owner_anggotaid' => $row['va_owner_anggotaid'],
            'anggota_nama'       => $row['anggota_nama'],
            'va_owner_expired'   => $row['va_owner_expired'],
            'potensi'            => $potensi ?: null,
            'transaksi'          => $transaksi,
            'total_transaksi'    => $totalTransaksi
        ];  
    }

    // Return as proper JSON
    return $this->response->setJSON($finalData);
    
    }
public function kendaladata(){
    $db = db_connect();

    $builder = $db->table('kendala')
        ->select('kelurahan.id as kelurahan_id, kelurahan.kecamatan_id, kecamatan_nama, va_owner_nama, kategori, kelurahan_nama, titpar_namatempat, titpar_lokasi, kendala_id, kendala_va, kendala_alasan, kendala_foto, approval, kendala.created_at, users.username, users.nama')
        ->join('va_owner', 'va_owner.va_owner_va = kendala.kendala_va', 'left')
        ->join('dishub_titpargrup', 'dishub_titpargrup.titpargrup_id = va_owner.va_owner_titpargrupid', 'left')
        ->join('dishub_titpar', 'dishub_titpar.titpar_id = dishub_titpargrup.titpargrup_titparid', 'left')
        ->join('kelurahan', 'kelurahan.id = dishub_titpar.titpar_klrhnid', 'left')
        ->join('retritype', 'retritype.retritype_id = dishub_titpar.titpar_retritypeid', 'left')
        ->join('users', 'kendala.approved_by = users.user_id', 'left')
        ->where('kendala.deleted_at', null)
        ->groupBy('kendala_id');

    $kecamatanId = $this->request->getPost('kecamatan_id');
    if (!empty($kecamatanId)) {
        $builder->where('kelurahan.kecamatan_id', $kecamatanId);
    }

    $kelurahanId = $this->request->getPost('kelurahan_id');
    if (!empty($kelurahanId)) {
        $builder->where('kelurahan.id', $kelurahanId);
    }

    $tipe = $this->request->getPost('tipe');
    if ($tipe === 'TJU') {
        $builder->groupStart()->like('retritype.retrytipe_nama', 'TJU')->orLike('retritype.retrytipe_nama', 'tepi jalan')->groupEnd();
    } elseif ($tipe === 'TKP') {
        $builder->groupStart()->like('retritype.retrytipe_nama', 'TKP')->orLike('retritype.retrytipe_nama', 'tempat khusus')->groupEnd();
    }

    $columns = ['kendala_id', 'kendala_va', 'kendala_alasan', 'va_owner_nama'];
    $dt = new DataTable($builder, $columns);
    $result = $dt->generate();

    return $this->response->setJSON($result);
}
    // public function kendaladata(){
    //       $db = db_connect();

    // $builder = $db->table('kendala')
    //     ->select('kecamatan_nama, va_owner_nama, kategori, kelurahan_nama,titpar_namatempat,titpar_lokasi,kendala_id, kendala_va, kendala_alasan, kendala_foto, approval, kendala.created_at,users.username, users.nama')
    //     ->join('va_owner','va_owner.va_owner_va = kendala.kendala_va','left')
    //     ->join('dishub_titpargrup','dishub_titpargrup.titpargrup_anggotaid = va_owner.va_owner_anggotaid','left')
    //     ->join('dishub_titpar','dishub_titpar.titpar_id = dishub_titpargrup.titpargrup_titparid','left')
    //     ->join('kelurahan','kelurahan.id = dishub_titpar.titpar_klrhnid','left')
    //     ->join('users','kendala.approved_by = users.user_id','left')
    //     ->where('kendala.deleted_at',null)        
    //     ->groupBy('kendala_id');


    // // Columns that can be searched
    // $columns = ['kendala_id', 'kendala_va', 'kendala_alasan','va_owner_nama'];

    //  // $columns = ['titpargrup_id'];
    //     // $columns =  [];
    //     $dt = new DataTable($builder, $columns);
    //     $result = $dt->generate();

    //     return $this->response->setJSON($result);

    // }

   public function updatePotongan()
{
    $id       = $this->request->getPost('id');
    $kategori = $this->request->getPost('kategori');

    $updated = $this->db->table('kendala')
        ->where('kendala_id', $id)
        ->update(['kategori' => $kategori]);

    if ($updated) {
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Potongan berhasil diubah'
        ]);
    }

    return $this->response->setJSON([
        'status' => 'error',
        'message' => 'Gagal update data'
    ]);
}

    public function kendaladataOLD2($iduser){
         $db = db_connect();

    $builder = $db->table('kendala')
        ->select('dishub_anggota.*,kendala_id, kendala_va, kendala_alasan, approval, created_at')
        ->join('va_owner','va_owner.va_owner_va = kendala.kendala_va')
        ->join('dishub_titpargrup','dishub_titpargrup.titpargrup_anggotaid = va_owner.va_owner_anggotaid')
        ->join('dishub_titpar','dishub_titpar.titpar_id = dishub_titpargrup.titpargrup_titparid');

    // Columns that can be searched
    $columns = ['kendala_id', 'kendala_va', 'kendala_alasan'];

      //$columns = ['titpargrup_id'];
        // $columns =  [];
        $dt = new DataTable($builder, $columns);
        $result = $dt->generate();

        return $this->response->setJSON($result);

    }

    public function kendaladataOLD(){
         $db = db_connect();

    $builder = $db->table('kendala')
        ->select('kendala_id, kendala_va, kendala_foto, kendala_alasan, approval, created_at')
        ->where('deleted_at',null);

    // Columns that can be searched
    $columns = ['kendala_id', 'kendala_va', 'kendala_alasan'];

      $columns = ['kendala_id'];
        // $columns =  [];
        $dt = new DataTable($builder, $columns);
        $result = $dt->generate();

        return $this->response->setJSON($result);

    }

    public function getdata(){
          $db = db_connect();

    $builder = $db->table('kendala')
        ->select('kecamatan_nama, kelurahan_nama,titpar_namatempat,titpar_lokasi,kendala_id, kendala_va, kendala_alasan, approval, kendala.created_at')
        ->join('va_owner','va_owner.va_owner_va = kendala.kendala_va')
        ->join('dishub_titpargrup','dishub_titpargrup.titpargrup_anggotaid = va_owner.va_owner_anggotaid')
        ->join('dishub_titpar','dishub_titpar.titpar_id = dishub_titpargrup.titpargrup_titparid')
        ->join('kelurahan','kelurahan.id = dishub_titpar.titpar_klrhnid');

    // Columns that can be searched
    $columns = ['kendala_id', 'kendala_va', 'kendala_alasan'];

      $columns = ['titpargrup_id'];
        // $columns =  [];
        $dt = new DataTable($builder, $columns);
        $result = $dt->generate();

        return $this->response->setJSON($result);

    }

     public function getdataOLD($iduser){
         $db = db_connect();

    $builder = $db->table('kendala')
        ->select('kendala_id, kendala_va, kendala_alasan, approval, created_at');

    // Columns that can be searched
    $columns = ['kendala_id', 'kendala_va', 'kendala_alasan'];

      $columns = ['titpargrup_id'];
        // $columns =  [];
        $dt = new DataTable($builder, $columns);
        $result = $dt->generate();

        return $this->response->setJSON($result);

    }

    // public function kendalaindex(){

    //     return view('kendala/index');
         
    // }

    public function kendalaindex(){
    $db = \Config\Database::connect();

    $listKecamatan = $db->table('kelurahan')
        ->select('kecamatan_id, kecamatan_nama')->where('provinsi', 'Jawa Barat')->where('kabupaten_kota', 'Kota Bogor')
        ->distinct()
        ->orderBy('kecamatan_nama', 'ASC')
        ->get()->getResultArray();

    $listKelurahan = $db->table('kelurahan')
        ->select('id, kecamatan_id, kelurahan_nama')->where('provinsi', 'Jawa Barat')->where('kabupaten_kota', 'Kota Bogor')
        ->orderBy('kelurahan_nama', 'ASC')
        ->get()->getResultArray();

    return view('kendala/index', [
        'listKecamatan' => $listKecamatan,
        'listKelurahan' => $listKelurahan,
    ]);
}

    public function approve()
{

    $id = $this->request->getPost('id');

    $db = db_connect();
    $db->table('kendala')
       ->where('kendala_id', $id)
       ->update([
           'approval' => 1,
           'approved_by' => session('user_id'),
           'updated_at' => date('Y-m-d H:i:s')
       ]);

    return $this->response->setJSON(['status' => 'success']);
}

public function reject()
{
    $id = $this->request->getPost('id');

    $db = db_connect();
    $db->table('kendala')
       ->where('kendala_id', $id)
       ->update([
           'approval' => 2,
           'approved_by' => session('user_id'),
           'updated_at' => date('Y-m-d H:i:s')
       ]);

    return $this->response->setJSON(['status' => 'success']);
}


public function getvalist($user_id)
{
    $db = db_connect();

    $builder = $db->table('va_owner')
        ->select('va_owner.va_owner_id, va_owner.va_owner_va, dishub_titpargrup.titpargrup_titparid, titpar_namatempat')
        ->join('dishub_titpargrup', 'dishub_titpargrup.titpargrup_id = va_owner.va_owner_titpargrupid')
        ->join('dishub_titpar','dishub_titpargrup.titpargrup_titparid = dishub_titpar.titpar_id')
        ->where('dishub_titpargrup.titpargrup_anggotaid', $user_id)
        ->orderBy('va_owner.va_owner_id', 'DESC');

    $result = $builder->get()->getResultArray();

    // ✅ Output format suitable for Flutter dropdown
    $data = [];

    foreach ($result as $row) {
        $data[] = [
            'id'    => $row['va_owner_id'],      // dropdown value
            'va'    => $row['va_owner_va'],      // actual VA number
            'label' => $row['va_owner_va'],      // text shown in Dropdown
            'titpar_id' => $row['titpargrup_titparid'],
            'titpar_namatempat'    => $row['titpar_namatempat'],      // actual VA number
        ];
    }

    return $this->response->setJSON([
        'status' => 'success',
        'data'   => $data
    ]);
}

public function upload()
{
    $db = \Config\Database::connect();

    $title  = strtolower($this->request->getPost('title'));
    $vaId   = $this->request->getPost('va_id');
    $file   = $this->request->getFile('photo');

    if (!$title || !$vaId) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Missing required fields (title, va_id)',
        ])->setStatusCode(400);
    }

    // ✅ Tentukan kategori otomatis
    $kategori = null;

    if (strpos($title, 'gerimis') !== false) {
        $kategori = 3;
    } elseif (strpos($title, 'hujan') !== false) {
        $kategori = 2;
    } elseif (
        strpos($title, 'libur') !== false ||
        strpos($title, 'tutup') !== false ||
        strpos($title, 'sakit') !== false
    ) {
        $kategori = 1;
    }

    // ✅ Validate image
    if (!$this->validate([
        'photo' => [
            'uploaded[photo]',
            'is_image[photo]',
            'mime_in[photo,image/jpg,image/jpeg,image/png,image/webp]',
            'max_size[photo,5048]',
        ],
    ])) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => $this->validator->getError('photo'),
        ])->setStatusCode(400);
    }

    $uploadPath = FCPATH . 'uploads/kendalajukir/';
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0755, true);
    }

    $today = date('Y-m-d');

    $existing = $db->table('kendala')
        ->where('kendala_va', $vaId)
        ->where('created_at >=', $today . ' 00:00:00')
        ->where('created_at <=', $today . ' 23:59:59')
        ->get()
        ->getRow();

    $newName = $file->getRandomName();
    $file->move($uploadPath, $newName);

    \Config\Services::image()
        ->withFile($uploadPath . $newName)
        ->resize(1024, 1024, true, 'auto')
        ->save($uploadPath . $newName, 75);

    if ($existing) {

        if (!empty($existing->kendala_foto) && file_exists($uploadPath . $existing->kendala_foto)) {
            unlink($uploadPath . $existing->kendala_foto);
        }

        $db->table('kendala')
            ->where('kendala_id', $existing->kendala_id)
            ->update([
                'kendala_va'     => $vaId,
                'kendala_foto'   => $newName,
                'kendala_alasan' => $title,
                'kategori'       => $kategori,
                'approval'       => 0,
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Laporan hari ini berhasil diperbarui',
            'file'    => $newName,
        ]);
    }

    $db->table('kendala')->insert([
        'kendala_va'      => $vaId,
        'kendala_foto'    => $newName,
        'kendala_alasan'  => $title,
        'kategori'        => $kategori,
        'approval'        => 0,
        'created_at'      => date('Y-m-d H:i:s'),
        'updated_at'      => date('Y-m-d H:i:s'),
    ]);

    return $this->response->setJSON([
        'status' => 'success',
        'message' => 'Laporan baru berhasil dikirim',
        'file_name' => $newName,
    ]);
}

public function kendalaAdminForm()
{
    $db = \Config\Database::connect();

    $listva = $db->table('va_owner')
            ->select('va_owner_va, va_owner_nama, titpar_lokasi, titpar_namatempat')
            ->join('dishub_titpargrup','dishub_titpargrup.titpargrup_id = va_owner.va_owner_titpargrupid','left')
            ->join('dishub_anggota', 'dishub_anggota.anggota_id = dishub_titpargrup.titpargrup_anggotaid','left')
            ->join('dishub_titpar','dishub_titpar.titpar_id =dishub_titpargrup.titpargrup_titparid','left')
            ->get()->getResultArray();

    return view('kendala/kendala_admin', [
        'listva' => $listva
    ]);
}

public function kendalaAdminSave()
{
       $db = \Config\Database::connect();
    $builder = $db->table('kendala'); // sesuaikan nama tabel

    $va        = $this->request->getPost('kendala_va');
    $alasan    = $this->request->getPost('kendala_alasan');
    $kategori  = $this->request->getPost('kategori');
    $start     = $this->request->getPost('start_date');
    $end       = $this->request->getPost('end_date');

    if (!$va || !$start || !$end) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Data tidak lengkap'
        ]);
    }

    $startDate = new \DateTime($start);
    $endDate   = new \DateTime($end);
    $endDate->modify('+1 day');

    $period = new \DatePeriod($startDate, new \DateInterval('P1D'), $endDate);

    $dataInsert = [];

    foreach ($period as $date) {

        $tanggal = $date->format('Y-m-d');

        // OPTIONAL: skip jika sudah ada
        $exists = $builder
            ->where('kendala_va', $va)
            ->where('DATE(created_at)', $tanggal)
            ->countAllResults();

        if ($exists) continue;

        $dataInsert[] = [
            'kendala_va'     => $va,
            'kendala_alasan' => $alasan,
            'kategori'       => $kategori,
            'approval' => 1,
            'approved_by' => session('user_id'),
            'created_at'     => $tanggal . ' 00:00:00'
        ];
    }

    if (!empty($dataInsert)) {
        $builder->insertBatch($dataInsert);
    }

    return $this->response->setJSON([
        'status' => 'success',
        'message' => 'Data range berhasil diproses',
        'redirect' => site_url('kendalaindex')
    ]);
}

public function uploadOLD()
{
    $db = \Config\Database::connect();

    $title  = $this->request->getPost('title');
    $vaId   = $this->request->getPost('va_id');
    $file   = $this->request->getFile('photo');

    if (!$title || !$vaId) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Missing required fields (title, va_id)',
        ])->setStatusCode(400);
    }

    // ✅ Validate image
    if (!$this->validate([
        'photo' => [
            'uploaded[photo]',
            'is_image[photo]',
            'mime_in[photo,image/jpg,image/jpeg,image/png,image/webp]',
            'max_size[photo,5048]',
        ],
    ])) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => $this->validator->getError('photo'),
        ])->setStatusCode(400);
    }

    $uploadPath = FCPATH . 'uploads/kendalajukir/';
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0777, true);
    }

    // ✅ Cek apakah laporan VA ini sudah ada hari ini
    $today = date('Y-m-d');

    $existing = $db->table('kendala')
        ->where('kendala_va', $vaId)
        ->where('DATE(created_at)', $today)
        ->get()
        ->getRow();

    // ✅ Upload file baru
    $newName = $file->getRandomName();
    $file->move($uploadPath, $newName);

    // ✅ Resize / compress
    \Config\Services::image()
        ->withFile($uploadPath . $newName)
        ->resize(1024, 1024, true, 'auto')
        ->save($uploadPath . $newName, 75);

    // ✅ Jika ada laporan sebelumnya → REPLACE (update)
    if ($existing) {

        // ✅ Hapus foto lama
        if (!empty($existing->kendala_foto) && file_exists($uploadPath . $existing->kendala_foto)) {
            unlink($uploadPath . $existing->kendala_foto);
        }

        // ✅ Update data
        $db->table('kendala')
            ->where('kendala_id', $existing->kendala_id)
            ->update([
                'kendala_va'     => $vaId,
                'kendala_foto'   => $newName,
                'kendala_alasan' => $title,
                'approval'       => 0,
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Laporan hari ini berhasil diperbarui',
            'file'    => $newName,
        ]);
    }

    // ✅ Jika belum ada → INSERT baru
    $db->table('kendala')->insert([
        'kendala_va'      => $vaId,
        'kendala_foto'    => $newName,
        'kendala_alasan'  => $title,
        'approval'        => 0,
        'created_at'      => date('Y-m-d H:i:s'),
        'updated_at'      => date('Y-m-d H:i:s'),
    ]);

    return $this->response->setJSON([
        'status' => 'success',
        'message' => 'Laporan baru berhasil dikirim',
        'file_name' => $newName,
    ]);
}

public function history($va)
{
    $db = \Config\Database::connect();
    
    $history = $db->table('kendala')
        ->where('kendala_va', $va)
        ->orderBy('created_at', 'DESC')
        ->get()->getResult();

    // Map through the results to transform the approval field
    $transformedHistory = array_map(function($item) {
        // Convert object to array if necessary, or just modify the object property
        $item->approval = ($item->approval == "1") ? "disetujui" : "pending";
        return $item;
    }, $history);

    return $this->response->setJSON([
        "status" => "success",
        "data"   => $transformedHistory
    ]);
}

 public function historyOlh($va)
    {
          $db = \Config\Database::connect();
       
        $history = $db->table('kendala')
            ->where('kendala_va', $va)
            ->orderBy('created_at', 'DESC')
            ->get()->getResult();

        return $this->response->setJSON([
            "status" => "success",
            "data"   => $history
        ]);
    }

    private function profilePreviewUrl(?string $file, string $sourceFolder, string $prefix): ?string
    {
        if (!$file || str_starts_with($file, 'pendaftaran/') || !preg_match('/\.(jpe?g|png|webp)$/i', $file)) return null;
        $source = FCPATH . 'uploads/' . $sourceFolder . '/' . basename($file);
        if (!is_file($source)) $source = FCPATH . 'uploads/profile_changes/' . basename($file);
        if (!is_file($source)) return null;
        $dir = FCPATH . 'uploads/profile_preview';
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $name = $prefix . '_' . md5($source . '|' . filemtime($source)) . '.jpg';
        $target = $dir . '/' . $name;
        if (!is_file($target)) {
            try {
                service('image')->withFile($source)->resize(800, 800, true, 'auto')->save($target, 78);
            } catch (\Throwable $e) {
                return base_url('uploads/' . $sourceFolder . '/' . rawurlencode(basename($file)));
            }
        }
        return base_url('uploads/profile_preview/' . $name);
    }

    public function profilApp($anggotaId)
    {
        $db = \Config\Database::connect();
        $row = $db->table('dishub_anggota')->where('anggota_id', (int) $anggotaId)->get()->getRowArray();
        if (!$row) return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Anggota tidak ditemukan.']);
        unset($row['password'], $row['jukir_pass'], $row['jukir_token']);
        // Samakan nama dokumen dengan halaman edit anggota agar aplikasi dapat
        // menampilkan dokumen yang sudah tersimpan sebelum pengguna menggantinya.
        $row['foto_selfie'] = $row['anggota_foto'] ?? null;
        $row['foto_selfie_url'] = $this->profilePreviewUrl($row['anggota_foto'] ?? null, 'anggota_foto', 'selfie');
        $row['foto_ktp_url'] = $this->profilePreviewUrl($row['foto_ktp'] ?? null, 'foto_ktp', 'ktp');
        $row['foto_kk_url'] = $this->profilePreviewUrl($row['foto_kk'] ?? null, 'foto_kk', 'kk');
        return $this->response->setJSON(['status' => 'success', 'data' => $row]);
    }

    public function listPerpanjanganKta($anggotaId)
    {
        $db = \Config\Database::connect();
        $anggotaId = (int) $anggotaId;

        $anggota = $db->table('dishub_anggota')
            ->select('anggota_id, anggota_nama, anggota_noktp, anggota_foto')
            ->where('anggota_id', $anggotaId)
            ->where('anggota_status !=', 1)
            ->get()->getRowArray();

        if (!$anggota) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Anggota tidak ditemukan.',
            ]);
        }

        $rows = $db->table('dishub_titpargrup g')
            ->select('g.titpargrup_id, g.titpargrup_anggotaid, g.titpargrup_titparid, g.titpargrup_staanggota, g.titpargrup_masaberlakustart, g.titpargrup_masaberlakuend, t.titpar_namatempat, t.titpar_lokasi')
            ->join('dishub_titpar t', 't.titpar_id = g.titpargrup_titparid')
            ->where('g.titpargrup_anggotaid', $anggotaId)
            ->where('t.titpar_status', 3)
            ->orderBy('g.titpargrup_masaberlakuend', 'ASC')
            ->get()->getResultArray();

        $pending = $db->table('kta_perpanjangan')
            ->select('titpargrup_id, status, masa_berlaku_baru, created_at')
            ->where('anggota_id', $anggotaId)
            ->whereIn('status', ['menunggu', 'disetujui'])
            ->orderBy('created_at', 'DESC')
            ->get()->getResultArray();

        $latestByGroup = [];
        foreach ($pending as $item) {
            $groupId = (int) ($item['titpargrup_id'] ?? 0);
            if ($groupId && !isset($latestByGroup[$groupId])) $latestByGroup[$groupId] = $item;
        }

        $today = date('Y-m-d');
        $data = array_map(static function (array $row) use ($anggota, $latestByGroup, $today): array {
            $groupId = (int) $row['titpargrup_id'];
            $request = $latestByGroup[$groupId] ?? null;
            $validUntil = $row['titpargrup_masaberlakuend'] ?: null;

            return [
                'titpargrup_id' => $groupId,
                'kta_number' => $anggota['anggota_id'] . '-' . $row['titpargrup_titparid'],
                'anggota_id' => (int) $anggota['anggota_id'],
                'anggota_nama' => $anggota['anggota_nama'],
                'titpar_id' => (int) $row['titpargrup_titparid'],
                'titpar_nama' => $row['titpar_namatempat'],
                'titpar_lokasi' => $row['titpar_lokasi'],
                'masa_berlaku_mulai' => $row['titpargrup_masaberlakustart'],
                'masa_berlaku_selesai' => $validUntil,
                'sudah_kadaluarsa' => $validUntil ? $validUntil < $today : false,
                'status_pengajuan' => $request['status'] ?? null,
                'masa_berlaku_diajukan' => $request['masa_berlaku_baru'] ?? null,
                'bisa_diperpanjang' => !$request || $request['status'] === 'ditolak',
            ];
        }, $rows);

        return $this->response->setJSON(['status' => 'success', 'data' => $data]);
    }

    public function anggotaAktifApp()
    {
        $rows = \Config\Database::connect()->table('dishub_anggota')
            ->select('anggota_id, anggota_nama, anggota_newnoreg, anggota_foto, anggota_status')
            ->where('anggota_status', 3)
            ->orderBy('anggota_nama', 'ASC')
            ->get()->getResultArray();
        return $this->response->setJSON(['status' => 'success', 'data' => $rows]);
    }

    public function anggotaPerpanjanganKta($anggotaId)
    {
        $db = \Config\Database::connect();
        $groupId = (int) $this->request->getGet('titpargrup_id');
        $group = $db->table('dishub_titpargrup')
            ->select('titpargrup_titparid')
            ->where('titpargrup_id', $groupId)
            ->where('titpargrup_anggotaid', (int) $anggotaId)
            ->where('titpargrup_staanggota', 'KOORDINATOR')
            ->get()->getRowArray();
        if (!$group) return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Koordinator tidak ditemukan.']);
        $members = $db->table('dishub_titpargrup g')
            ->select('a.anggota_id, a.anggota_nama, a.anggota_foto, a.anggota_status, g.titpargrup_staanggota')
            ->join('dishub_anggota a', 'a.anggota_id = g.titpargrup_anggotaid')
            ->where('g.titpargrup_titparid', $group['titpargrup_titparid'])
            ->where('g.titpargrup_staanggota', 'ANGGOTA')
            ->where('g.titpargrup_status', 3)
            ->where('g.titpargrup_namashift', (int) $anggotaId)
            ->orderBy('a.anggota_nama', 'ASC')
            ->get()->getResultArray();
        return $this->response->setJSON(['status' => 'success', 'data' => $members]);
    }

    public function ajukanPerpanjanganKta($anggotaId)
    {
        $db = \Config\Database::connect();
        $anggotaId = (int) $anggotaId;
        $titpargrupId = (int) ($this->request->getPost('titpargrup_id') ?: 0);
        $masaBaru = trim((string) $this->request->getPost('masa_berlaku_baru'));

        if (!$titpargrupId || ($masaBaru !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $masaBaru))) {
            return $this->response->setStatusCode(422)->setJSON(['status' => 'error', 'message' => 'titpargrup_id wajib diisi dan tanggal harus berformat YYYY-MM-DD jika dikirim.']);
        }

        $anggota = $db->table('dishub_anggota')->where('anggota_id', $anggotaId)->where('anggota_status !=', 1)->get()->getRowArray();
        if (!$anggota) return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Anggota tidak ditemukan.']);

        $kta = $db->table('dishub_titpargrup g')
            ->select('g.*, t.titpar_namatempat, t.titpar_lokasi')
            ->join('dishub_titpar t', 't.titpar_id = g.titpargrup_titparid')
            ->where('g.titpargrup_id', $titpargrupId)
            ->where('g.titpargrup_anggotaid', $anggotaId)
            ->where('t.titpar_status', 3)
            ->get()->getRowArray();
        if (!$kta) return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'KTA tidak ditemukan untuk anggota tersebut.']);

        if ($masaBaru !== '' && $masaBaru <= ($kta['titpargrup_masaberlakuend'] ?? '0000-00-00')) {
            return $this->response->setStatusCode(422)->setJSON(['status' => 'error', 'message' => 'Masa berlaku baru harus lebih besar dari masa berlaku saat ini.']);
        }

        if ($db->table('kta_perpanjangan')->where('anggota_id', $anggotaId)->where('titpargrup_id', $titpargrupId)->where('status', 'menunggu')->countAllResults() > 0) {
            return $this->response->setStatusCode(409)->setJSON(['status' => 'error', 'message' => 'Masih ada pengajuan perpanjangan yang menunggu persetujuan.']);
        }

        $new = $this->request->getPost('data_baru');
        $new = is_string($new) ? json_decode($new, true) : $new;
        $new = is_array($new) ? $new : [];
        $currentMemberIds = array_map('intval', array_column($db->table('dishub_titpargrup')
            ->select('titpargrup_anggotaid')
            ->where('titpargrup_titparid', $kta['titpargrup_titparid'])
            ->where('titpargrup_staanggota', 'ANGGOTA')
            ->where('titpargrup_status', 3)
            ->where('titpargrup_namashift', $anggotaId)
            ->get()->getResultArray(), 'titpargrup_anggotaid'));
        if (array_key_exists('anggota_ids', $new)) {
            if (!is_array($new['anggota_ids'])) return $this->response->setStatusCode(422)->setJSON(['status' => 'error', 'message' => 'Daftar anggota tidak valid.']);
            $ids = [];
            foreach ($new['anggota_ids'] as $id) {
                if (!is_scalar($id) || !ctype_digit((string) $id) || (int) $id < 1 || (int) $id === $anggotaId) {
                    return $this->response->setStatusCode(422)->setJSON(['status' => 'error', 'message' => 'ID anggota tidak valid.']);
                }
                $ids[] = (int) $id;
            }
            if (count($ids) !== count(array_unique($ids))) return $this->response->setStatusCode(422)->setJSON(['status' => 'error', 'message' => 'Anggota tidak boleh diduplikasi.']);
            if ($ids) {
                $activeCount = $db->table('dishub_anggota')->whereIn('anggota_id', $ids)->where('anggota_status', 3)->countAllResults();
                if ($activeCount !== count($ids)) return $this->response->setStatusCode(422)->setJSON(['status' => 'error', 'message' => 'Pilih hanya anggota aktif.']);
            }
            $new['anggota_ids'] = $ids;
        }

        $dir = FCPATH . 'uploads/kta_perpanjangan/';
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $files = [];
        foreach (['foto_selfie', 'foto_ktp', 'foto_kk', 'dokumen_pendukung'] as $field) {
            $file = $this->request->getFile($field);
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $files[$field] = $file->getRandomName();
                $file->move($dir, $files[$field]);
            }
        }

        $old = [
            'anggota_id' => $anggotaId,
            'anggota_nama' => $anggota['anggota_nama'] ?? null,
            'titpargrup_id' => $titpargrupId,
            'titpar_id' => $kta['titpargrup_titparid'],
            'titpar_nama' => $kta['titpar_namatempat'],
            'masa_berlaku_mulai' => $kta['titpargrup_masaberlakustart'],
            'masa_berlaku_selesai' => $kta['titpargrup_masaberlakuend'],
            'anggota_ids' => $currentMemberIds,
        ];
        foreach (['anggota_noktp', 'nomor_kk', 'anggota_nohp', 'anggota_tgllhr', 'anggota_tmplhr', 'anggota_jnsklm', 'anggota_alamat', 'anggota_klrhnid', 'anggota_peker', 'anggota_pente', 'anggota_staagamaid'] as $field) {
            $old[$field] = $anggota[$field] ?? null;
        }
        if ($masaBaru !== '') $new['masa_berlaku_baru'] = $masaBaru;

        $insert = [
            'anggota_id' => $anggotaId,
            'titpargrup_id' => $titpargrupId,
            'nomor_kta' => $anggotaId . '-' . $kta['titpargrup_titparid'],
            'data_lama' => json_encode($old, JSON_UNESCAPED_UNICODE),
            'data_baru' => json_encode($new, JSON_UNESCAPED_UNICODE),
            'masa_berlaku_lama' => $kta['titpargrup_masaberlakuend'] ?: null,
            'masa_berlaku_baru' => $masaBaru !== '' ? $masaBaru : null,
            'foto_selfie' => $files['foto_selfie'] ?? null,
            'foto_ktp' => $files['foto_ktp'] ?? null,
            'foto_kk' => $files['foto_kk'] ?? null,
            'dokumen_pendukung' => $files['dokumen_pendukung'] ?? null,
            'status' => 'menunggu',
        ];
        if (!$db->table('kta_perpanjangan')->insert($insert)) return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Pengajuan perpanjangan gagal disimpan.']);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Pengajuan perpanjangan berhasil dikirim.', 'id' => $db->insertID()]);
    }

    public function statusPerpanjanganKta($anggotaId)
    {
        $db = \Config\Database::connect();
        $anggotaId = (int) $anggotaId;
        $exists = $db->table('dishub_anggota')->where('anggota_id', $anggotaId)->countAllResults();
        if (!$exists) return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Anggota tidak ditemukan.']);

        $rows = $db->table('kta_perpanjangan p')
            ->select('p.perpanjangan_id, p.anggota_id, p.titpargrup_id, p.nomor_kta, p.masa_berlaku_lama, p.masa_berlaku_baru, p.status, p.alasan_penolakan, p.diproses_at, p.created_at, t.titpar_namatempat, t.titpar_lokasi')
            ->join('dishub_titpargrup g', 'g.titpargrup_id = p.titpargrup_id', 'left')
            ->join('dishub_titpar t', 't.titpar_id = g.titpargrup_titparid', 'left')
            ->where('p.anggota_id', $anggotaId)
            ->orderBy('p.created_at', 'DESC')
            ->get()->getResultArray();

        return $this->response->setJSON(['status' => 'success', 'data' => $rows]);
    }

    public function ajukanPerubahanProfil($anggotaId)
    {
        $db = \Config\Database::connect();
        $old = $db->table('dishub_anggota')->where('anggota_id', (int) $anggotaId)->get()->getRowArray();
        if (!$old) return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Anggota tidak ditemukan.']);
        unset($old['password'], $old['jukir_pass'], $old['jukir_token']);
        $dataBaru = $this->request->getPost('data_baru');
        $dataBaru = is_string($dataBaru) ? json_decode($dataBaru, true) : $dataBaru;
        if (!is_array($dataBaru)) return $this->response->setStatusCode(422)->setJSON(['status' => 'error', 'message' => 'data_baru wajib berupa JSON.']);
        $allowed = ['anggota_nama','anggota_noktp','nomor_kk','anggota_nohp','anggota_tgllhr','anggota_tmplhr','anggota_jnsklm','anggota_alamat','anggota_klrhnid','provinsi_id','kabkota_id','kecamatan_id','kelurahan_id','anggota_peker','anggota_pente','anggota_staagamaid','anggota_foto','foto_ktp','kk','foto_selfie','foto_kk'];
        $clean = array_intersect_key($dataBaru, array_flip($allowed));
        $dir = FCPATH . 'uploads/profile_changes/';
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $files = [];
        foreach (['foto_selfie','foto_ktp','foto_kk'] as $field) {
            $file = $this->request->getFile($field);
            if ($file && $file->isValid() && !$file->hasMoved()) { $name = $file->getRandomName(); $file->move($dir, $name); $files[$field] = $name; }
        }
        foreach (['foto_selfie' => 'anggota_foto', 'foto_ktp' => 'foto_ktp', 'foto_kk' => 'kk'] as $upload => $column) {
            if (!array_key_exists($upload, $clean) && !empty($old[$column])) $clean[$upload] = $old[$column];
        }
        if ($db->table('anggota_perubahan')->where('anggota_id', (int) $anggotaId)->where('status', 'menunggu')->countAllResults() > 0) return $this->response->setStatusCode(409)->setJSON(['status' => 'error', 'message' => 'Masih ada pengajuan yang menunggu persetujuan.']);
        $db->table('anggota_perubahan')->insert(['anggota_id'=>(int)$anggotaId,'data_lama'=>json_encode($old),'data_baru'=>json_encode($clean),'foto_selfie'=>$files['foto_selfie']??null,'foto_ktp'=>$files['foto_ktp']??null,'foto_kk'=>$files['foto_kk']??null,'status'=>'menunggu']);
        return $this->response->setJSON(['status'=>'success','message'=>'Pengajuan perubahan berhasil dikirim.','id'=>$db->insertID()]);
    }

    public function statusPerubahanProfil($anggotaId)
    {
        $rows = \Config\Database::connect()->table('anggota_perubahan')->where('anggota_id', (int)$anggotaId)->orderBy('created_at','DESC')->get()->getResultArray();
        return $this->response->setJSON(['status'=>'success','data'=>$rows]);
    }

}
