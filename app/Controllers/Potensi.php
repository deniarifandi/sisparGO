<?php 

namespace App\Controllers;

use App\Models\PotensiModel;
use App\Models\VaOwnerModel;

use App\Models\DishubAnggotaModel;
use App\Libraries\DataTable;
use App\Libraries\Whatsapp;
use DB;
use DateTime;


class Potensi extends BaseController
{
    public $db;

    private function canManagePotensi(): bool
    {
        $role = session('role');
        return $role !== null && is_numeric($role) && (int) $role <= 2;
    }

    private function denyPotensiChange()
    {
        return redirect()->to('/potensi')->with('error', 'Hanya Admin Utama ke atas yang dapat mengubah potensi.');
    }

    public function __construct()
    {
      $this->db = \Config\Database::connect();
    }

       public function index()
    {
        $model = new VaOwnerModel();
        $data['potensi'] = $model->findAll();
        return view('potensi/index', $data);
    }

    public function create($va)
    {
        if (!$this->canManagePotensi()) return $this->denyPotensiChange();
         // $model = new VaOwnerModel();
        $anggotaModel = new DishubAnggotaModel();
       // $data['va_owner'] = $model->find($id);
        $data['va_owner'] = $anggotaModel
        ->select('va_owner_id,va_owner_va,anggota_nama, anggota_id, titpargrup_titparid, titpar_namatempat, titpar_lokasi, titpar_id, senin, selasa, rabu, kamis, jumat, sabtu, minggu, mingguan, bulanan, tahunan')
        ->where('anggota_status', 3)
        ->join('dishub_titpargrup','dishub_titpargrup.titpargrup_anggotaid = dishub_anggota.anggota_id')
        ->join('dishub_titpar','dishub_titpar.titpar_id = dishub_titpargrup.titpargrup_titparid')
        ->join('va_owner','va_owner.va_owner_anggotaid = dishub_anggota.anggota_id')
        ->join('potensi','potensi.potensi_va = va_owner.va_owner_va','left')
        ->where('va_owner_va',$va)
        ->groupBy('va_owner_va')
        ->findAll();

        // print_r($data['va_owner'][0]);
        return view('potensi/form', ['data' => $data['va_owner'][0]]);
    }

    public function store($va)
    {
        if (!$this->canManagePotensi()) return $this->denyPotensiChange();
        $model = new PotensiModel();
        
        //$va = $this->request->getPost('va_owner_va');

        $data = [
            'potensi_va'=> $this->request->getPost('va_owner_va'),
            'senin'     => $this->request->getPost('senin'),
            'selasa'    => $this->request->getPost('selasa'),
            'rabu'      => $this->request->getPost('rabu'),
            'kamis'     => $this->request->getPost('kamis'),
            'jumat'     => $this->request->getPost('jumat'),
            'sabtu'     => $this->request->getPost('sabtu'),
            'minggu'    => $this->request->getPost('minggu'),
            'mingguan'  => 0,
            'bulanan'   => 0,
            'tahunan'   => 0
        ];

        $effectiveFrom = $this->request->getPost('berlaku_mulai') ?: date('Y-m-d');
        $effectiveUntil = $this->request->getPost('berlaku_sampai') ?: null;
        $periodError = $this->validatePotensiPeriod($va, $effectiveFrom, $effectiveUntil);
        if ($periodError !== null) {
            return redirect()->back()->withInput()->with('error', $periodError);
        }
        $existing = $model->where('potensi_va', $va)->first();

        if ($existing) {
            $this->savePotensiHistory($data, $effectiveFrom, $effectiveUntil);
        } else {
            // Add potensi_va to the insert data and insert new row
            $data['potensi_va'] = $va;
            $model->insert($data);
            $this->savePotensiHistory($data, $effectiveFrom, $effectiveUntil);
        }


        // $model->save($data);
        return redirect()->to('/potensi');
    }

    public function edit($va)
    {
        if (!$this->canManagePotensi()) return $this->denyPotensiChange();
        $db      = \Config\Database::connect();
        $builder = $db->table('va_owner');

        $query = $builder
            ->select('va_owner_id, va_owner_va, anggota_nama, anggota_id, titpargrup_titparid, 
            titpar_namatempat, titpar_lokasi, titpar_id, 
            senin, selasa, rabu, kamis, jumat, sabtu, minggu, mingguan, bulanan, tahunan')
            
             ->join('potensi','va_owner.va_owner_va = potensi.potensi_va','left')
            ->join('dishub_titpargrup','dishub_titpargrup.titpargrup_id = va_owner.va_owner_titpargrupid','left')
            ->join('dishub_titpar','dishub_titpar.titpar_id = dishub_titpargrup.titpargrup_titparid','left')
            ->join('dishub_anggota','dishub_anggota.anggota_id = dishub_titpargrup.titpargrup_anggotaid','left')
            ->where('va_owner_va', $va)
            ->get();

        $data = $query->getRowArray(); // get one row as array

        if ($data) {
             return view('potensi/form', [
                 'data' => $data,
                 'isEdit' => true,
                 'return_to' => $this->request->getGet('return_to')
             ]);
        } else {
            echo "Data tidak ditemukan.";
        }

    }

    public function update($va)
    {
        if (!$this->canManagePotensi()) return $this->denyPotensiChange();
         $model = new PotensiModel();
        
        $va = $this->request->getPost('va_owner_va'); 
        $data = [
            
            'senin'     => $this->request->getPost('senin'),
            'selasa'    => $this->request->getPost('selasa'),
            'rabu'      => $this->request->getPost('rabu'),
            'kamis'     => $this->request->getPost('kamis'),
            'jumat'     => $this->request->getPost('jumat'),
            'sabtu'     => $this->request->getPost('sabtu'),
            'minggu'    => $this->request->getPost('minggu'),
            'mingguan'  => 0,
            'bulanan'   => 0,
            'tahunan'   => 0
        ];

        $effectiveFrom = $this->request->getPost('berlaku_mulai') ?: date('Y-m-d');
        $effectiveUntil = $this->request->getPost('berlaku_sampai') ?: null;
        $periodError = $this->validatePotensiPeriod($va, $effectiveFrom, $effectiveUntil);
        if ($periodError !== null) {
            return redirect()->back()->withInput()->with('error', $periodError);
        }
        $this->savePotensiHistory(array_merge(['potensi_va' => $va], $data), $effectiveFrom, $effectiveUntil);
        $returnTo = $this->request->getPost('return_to');
        return redirect()->to($returnTo ?: '/potensi');
    }

    private function validatePotensiPeriod(string $va, string $start, ?string $end): ?string
    {
        $validDate = static function (string $value): bool {
            $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
            return $date !== false && $date->format('Y-m-d') === $value;
        };

        if (!$validDate($start) || ($end !== null && !$validDate($end))) {
            return 'Tanggal masa berlaku tidak valid.';
        }
        if ($end !== null && $end < $start) {
            return 'Berlaku Sampai harus sama atau setelah Berlaku Mulai.';
        }

        if ($end !== null) {
            $next = $this->db->table('potensi_riwayat')
                ->where('potensi_va', $va)
                ->where('berlaku_mulai >', $start)
                ->orderBy('berlaku_mulai', 'ASC')->get()->getRowArray();
            if ($next && $end >= $next['berlaku_mulai']) {
                return 'Berlaku Sampai bertumpang tindih dengan periode berikutnya.';
            }
        }

        return null;
    }

    private function savePotensiHistory(array $data, string $effectiveFrom, ?string $effectiveUntil): void
    {
        $history = $this->db->table('potensi_riwayat');
        $previous = $history->where('potensi_va', $data['potensi_va'])
            ->where('berlaku_mulai <', $effectiveFrom)
            ->orderBy('berlaku_mulai', 'DESC')->get()->getRowArray();

        if ($previous) {
            $latestEnd = date('Y-m-d', strtotime($effectiveFrom . ' -1 day'));
            if ($previous['berlaku_sampai'] === null || $previous['berlaku_sampai'] > $latestEnd) {
                $history->where('potensi_riwayat_id', $previous['potensi_riwayat_id'])
                    ->update(['berlaku_sampai' => $latestEnd]);
            }
        }

        $history->where('potensi_va', $data['potensi_va'])
            ->where('berlaku_mulai', $effectiveFrom)->delete();
        $history->insert([
            'potensi_va' => $data['potensi_va'], 'berlaku_mulai' => $effectiveFrom,
            'berlaku_sampai' => $effectiveUntil,
            'senin' => $data['senin'] ?? 0, 'selasa' => $data['selasa'] ?? 0,
            'rabu' => $data['rabu'] ?? 0, 'kamis' => $data['kamis'] ?? 0,
            'jumat' => $data['jumat'] ?? 0, 'sabtu' => $data['sabtu'] ?? 0,
            'minggu' => $data['minggu'] ?? 0, 'mingguan' => $data['mingguan'] ?? 0,
            'bulanan' => $data['bulanan'] ?? 0, 'tahunan' => $data['tahunan'] ?? 0,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Hitung ulang batas akhir semua riwayat, termasuk saat riwayat lama
        // ditambahkan setelah riwayat yang lebih baru sudah tersedia.
        $this->normalizePotensiHistory($data['potensi_va']);
    }

    public function editRiwayat(int $id)
    {
        if (!$this->canManagePotensi()) return $this->denyPotensiChange();
        $row = $this->db->table('potensi_riwayat')->where('potensi_riwayat_id', $id)->get()->getRowArray();
        if (!$row) return redirect()->back()->with('message', 'Riwayat potensi tidak ditemukan.');
        return view('potensi/riwayat_form', ['riwayat' => $row]);
    }

    public function updateRiwayat(int $id)
    {
        if (!$this->canManagePotensi()) return $this->denyPotensiChange();
        $history = $this->db->table('potensi_riwayat');
        $row = $history->where('potensi_riwayat_id', $id)->get()->getRowArray();
        if (!$row) return redirect()->back()->with('message', 'Riwayat potensi tidak ditemukan.');

        $start = $this->request->getPost('berlaku_mulai');
        $end = $this->request->getPost('berlaku_sampai') ?: null;
        $validDate = static function ($value): bool {
            if (!is_string($value)) return false;
            $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
            return $date !== false && $date->format('Y-m-d') === $value;
        };
        if (!$validDate($start) || ($end !== null && !$validDate($end))) {
            return redirect()->back()->withInput()->with('error', 'Tanggal masa berlaku tidak valid.');
        }
        if ($end !== null && $end < $start) {
            return redirect()->back()->withInput()->with('error', 'Berlaku sampai harus sama atau setelah berlaku mulai.');
        }
        $otherRows = $this->db->table('potensi_riwayat')
            ->where('potensi_va', $row['potensi_va'])
            ->where('potensi_riwayat_id !=', $id)
            ->orderBy('berlaku_mulai', 'ASC')->get()->getResultArray();
        foreach ($otherRows as $other) {
            if ($other['berlaku_mulai'] === $start) {
                return redirect()->back()->withInput()->with('error', 'Sudah ada riwayat potensi yang mulai pada tanggal tersebut.');
            }
            if ($other['berlaku_mulai'] > $start && $end !== null && $end >= $other['berlaku_mulai']) {
                return redirect()->back()->withInput()->with('error', 'Berlaku sampai bertumpang tindih dengan riwayat berikutnya.');
            }
        }

        $fields = ['senin','selasa','rabu','kamis','jumat','sabtu','minggu'];
        $data = ['berlaku_mulai' => $start, 'berlaku_sampai' => $end];
        foreach ($fields as $field) {
            $value = $this->request->getPost($field);
            if (!is_string($value) || !ctype_digit($value)) {
                return redirect()->back()->withInput()->with('error', 'Nilai potensi harus bilangan bulat tanpa angka di belakang koma.');
            }
            $data[$field] = (int) $value;
        }
        $data['mingguan'] = 0;
        $data['bulanan'] = 0;
        $data['tahunan'] = 0;
        $history->where('potensi_riwayat_id', $id)->update($data);
        $this->normalizePotensiHistory($row['potensi_va']);
        return redirect()->to('/transaksi/va/' . $row['potensi_va'])->with('message', 'Riwayat potensi berhasil diperbarui.');
    }

    public function deleteRiwayat(int $id)
    {
        if (!$this->canManagePotensi()) return $this->denyPotensiChange();
        $history = $this->db->table('potensi_riwayat');
        $row = $history->where('potensi_riwayat_id', $id)->get()->getRowArray();
        if (!$row) return redirect()->back()->with('message', 'Riwayat potensi tidak ditemukan.');
        $history->where('potensi_riwayat_id', $id)->delete();
        $this->normalizePotensiHistory($row['potensi_va']);
        return redirect()->to('/transaksi/va/' . $row['potensi_va'])->with('message', 'Riwayat potensi berhasil dihapus.');
    }

    private function normalizePotensiHistory(string $va): void
    {
        $history = $this->db->table('potensi_riwayat');
        $rows = $history->where('potensi_va', $va)->orderBy('berlaku_mulai', 'ASC')->orderBy('potensi_riwayat_id', 'ASC')->get()->getResultArray();
        foreach ($rows as $i => $current) {
            $end = $current['berlaku_sampai'];
            if (isset($rows[$i + 1])) {
                $latestEnd = date('Y-m-d', strtotime($rows[$i + 1]['berlaku_mulai'] . ' -1 day'));
                if ($end === null || $end > $latestEnd) $end = $latestEnd;
            }
            if ($end !== $current['berlaku_sampai']) {
                $history->where('potensi_riwayat_id', $current['potensi_riwayat_id'])->update(['berlaku_sampai' => $end]);
            }
        }
        if ($rows) {
            $latest = end($rows);
            $values = [];
            foreach (['senin','selasa','rabu','kamis','jumat','sabtu','minggu','mingguan','bulanan','tahunan'] as $field) {
                $values[$field] = $latest[$field] ?? 0;
            }

            // Riwayat bisa dibuat untuk VA yang belum memiliki baris di
            // tabel potensi. UPDATE saja tidak cukup karena tidak ada row
            // yang bisa diubah, sehingga halaman /potensi tetap kosong.
            $potensi = $this->db->table('potensi');
            $existing = $potensi->where('potensi_va', $va)->get()->getRowArray();
            if ($existing) {
                $potensi->where('potensi_va', $va)->update($values);
            } else {
                $potensi->insert(array_merge(['potensi_va' => $va], $values));
            }
        }
    }

    public function delete($id)
    {
        if (!$this->canManagePotensi()) return $this->denyPotensiChange();
        $model = new TransaksiModel();
        $model->delete($id);
        return redirect()->to('/transaksi');
    }

    //custom
    public function data()
{
    $request = service('request');
    $db = db_connect();

    $builder = $db->table('va_owner')
        ->select('
            va_owner_va,
            anggota_nama,
            anggota_id,
            titpar_namatempat,
            titpar_lokasi,
            senin,
            selasa,
            rabu,
            kamis,
            jumat,
            sabtu,
            minggu,
            titpar_klrhnid
        ')
        ->join('potensi','va_owner.va_owner_va = potensi.potensi_va','left')
        ->join('dishub_titpargrup','dishub_titpargrup.titpargrup_id = va_owner.va_owner_titpargrupid','left')
        ->join('dishub_titpar','dishub_titpar.titpar_id = dishub_titpargrup.titpargrup_titparid','left')
        ->join('dishub_anggota','dishub_anggota.anggota_id = dishub_titpargrup.titpargrup_anggotaid','left')
        ->groupBy('va_owner.va_owner_va');


    /* =====================================================
       HANDLE CUSTOM FILTERS FROM FRONTEND
       ===================================================== */

    // 1. Kecamatan filter
    $kec = $request->getPost('kecamatan');
    if (!empty($kec)) {
        // Filter kode kecamatan dari kode kelurahan titik parkir.
        // kec code = first 6 digits
        $builder->like('titpar_klrhnid', $kec, 'after');
    }

    $kelurahanId = trim((string) $request->getPost('kelurahan_id'));
    if ($kelurahanId !== '') {
        $builder->where('titpar_klrhnid', $kelurahanId);
    }
    // 2. Custom live search
    $searchCustom = trim($request->getPost('searchCustom') ?? '');
    if ($searchCustom !== '') {
        $builder->groupStart()
            ->like('va_owner_va', $searchCustom)
            ->orLike('anggota_nama', $searchCustom)
            ->orLike('titpar_namatempat', $searchCustom)
            ->orLike('anggota_id', $searchCustom)
            ->groupEnd();
    }


    /* =====================================================
       DATATABLE PAGING & ORDERING
       ===================================================== */

    $start  = intval($request->getPost('start') ?? 0);
    $length = intval($request->getPost('length') ?? 10);

    $order  = $request->getPost('order')[0] ?? null;

    $columns = [
        'va_owner_va',
        'anggota_nama',
        'anggota_id',
        'titpar_namatempat',
        'titpar_lokasi',
        'titpar_klrhnid',
        'senin',
        'selasa',
        'rabu',
        'kamis',
        'jumat',
        'sabtu',
        'minggu'
    ];

    if ($order) {
        $builder->orderBy($columns[$order['column']], $order['dir']);
    }


    /* =====================================================
       GET FILTERED DATA
       ===================================================== */

    if ($length == -1) {
    $data = $builder->get()->getResult();      // No LIMIT, return all.
} else {
    $data = $builder->get($length, $start)->getResult();
}



    /* =====================================================
       TOTAL RECORDS (UNFILTERED)
       ===================================================== */

    $recordsTotal = $db->table('va_owner')->countAll();


    /* =====================================================
       TOTAL FILTERED RECORDS
       ===================================================== */

    // Clone builder for filtered count
    $countBuilder = $db->table('va_owner')
        ->select('va_owner_va')
        ->join('potensi','va_owner.va_owner_va = potensi.potensi_va','left')
        ->join('dishub_titpargrup','dishub_titpargrup.titpargrup_id = va_owner.va_owner_titpargrupid','left')
        ->join('dishub_titpar','dishub_titpar.titpar_id = dishub_titpargrup.titpargrup_titparid','left')
        ->join('dishub_anggota','dishub_anggota.anggota_id = dishub_titpargrup.titpargrup_anggotaid','left');

    if (!empty($kec)) {
        $countBuilder->like('titpar_klrhnid', $kec, 'after');
    }

    if ($searchCustom !== '') {
        $countBuilder->groupStart()
            ->like('va_owner_va', $searchCustom)
            ->orLike('anggota_nama', $searchCustom)
            ->orLike('titpar_namatempat', $searchCustom)
            ->orLike('anggota_id', $searchCustom)
            ->groupEnd();
    }

    $recordsFiltered = $countBuilder->countAllResults();


    /* =====================================================
       RETURN JSON
       ===================================================== */
    return $this->response->setJSON([
        "draw" => intval($request->getPost('draw')),
        "recordsTotal" => $recordsTotal,
        "recordsFiltered" => $recordsFiltered,
        "data" => $data
    ]);
}


     public function send_konfirmasi($id){
        $builder = $this->db->table('transaksi')->select('*')
          ->where('transaksi_id',$id)
          ->join('va_owner','transaksi.transaksi_va = va_owner.va_owner_va')
          ->join('dishub_anggota','dishub_anggota.anggota_id = va_owner.va_owner_anggotaid')
          ->join('dishub_titpargrup','dishub_titpargrup.titpargrup_anggotaid = dishub_anggota.anggota_id')
          ->join('dishub_titpar','dishub_titpargrup.titpargrup_titparid = dishub_titpar.titpar_id')
          ->get()->getResult();

        $wa = new Whatsapp();
         setlocale(LC_TIME, 'id_ID.utf8'); // Use Indonesian locale
        $tanggal = strftime('%e %B %Y', strtotime($builder[0]->transaksi_tanggal));
        $keterangan = substr($builder[0]->titpar_namatempat, 0, 30);
       

        $result = $wa->sendMessage('6281805173445', $builder[0]->anggota_nama,$builder[0]->transaksi_nominal,$tanggal,$builder[0]->titpar_namatempat,$builder[0]->transaksi_id);
        // return $this->response->setJSON($result);

            if ($result) {
                session()->setFlashdata('message', $result);
            } else {
                session()->setFlashdata('message', $result);
            }

            return redirect()->to('/transaksi');
    }

    private function buildPotensiQuery($filterId, $bulan, $tahun, $kecamatan,$jenis = null)
{
    if (empty($bulan)) $bulan = date('n');
    if (empty($tahun)) $tahun = date('Y');

    $bulan = (int) $bulan;
    $tahun = (int) $tahun;

    $bulanPad = str_pad($bulan, 2, '0', STR_PAD_LEFT);

    $startMonth = "{$tahun}-{$bulanPad}-01";
    $startYear  = "{$tahun}-01-01";
    $endMonth   = date('Y-m-d', strtotime($startMonth . ' +1 month'));
    $endYtd     = date('Y-m-d', strtotime($startMonth . ' +1 month'));

    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

    $map = [
        1 => 'senin', 2 => 'selasa', 3 => 'rabu', 4 => 'kamis',
        5 => 'jumat', 6 => 'sabtu', 7 => 'minggu'
    ];

    $weekdayMonth = array_fill_keys(array_values($map), 0);
    for ($d = 1; $d <= $daysInMonth; $d++) {
        $date = sprintf('%04d-%02d-%02d', $tahun, $bulan, $d);
        $day  = (int) date('N', strtotime($date));
        $weekdayMonth[$map[$day]]++;
    }

    $startYtdDate = new DateTime("{$tahun}-01-01");
    $endYtdDate   = new DateTime($endYtd);
    $endYtdDate->modify('-1 day');

    $weekdayYtd = array_fill_keys(array_values($map), 0);
    while ($startYtdDate <= $endYtdDate) {
        $day = (int) $startYtdDate->format('N');
        $weekdayYtd[$map[$day]]++;
        $startYtdDate->modify('+1 day');
    }

    $weeksInMonth = (int) ceil($daysInMonth / 7);
    $totalDaysYtd = array_sum($weekdayYtd);
    $weeksYtd     = (int) ceil($totalDaysYtd / 7);

    $casePotensi = "
        CASE
            WHEN IFNULL(p.bulanan, 0) > 0 THEN p.bulanan
            WHEN IFNULL(p.mingguan, 0) > 0 THEN p.mingguan * {$weeksInMonth}
            ELSE (
                IFNULL(p.senin, 0) * {$weekdayMonth['senin']} +
                IFNULL(p.selasa, 0) * {$weekdayMonth['selasa']} +
                IFNULL(p.rabu, 0) * {$weekdayMonth['rabu']} +
                IFNULL(p.kamis, 0) * {$weekdayMonth['kamis']} +
                IFNULL(p.jumat, 0) * {$weekdayMonth['jumat']} +
                IFNULL(p.sabtu, 0) * {$weekdayMonth['sabtu']} +
                IFNULL(p.minggu, 0) * {$weekdayMonth['minggu']}
            )
        END
    ";

    $casePotensiTahun = "
        CASE
            WHEN IFNULL(p.bulanan, 0) > 0 THEN p.bulanan * {$bulan}
            WHEN IFNULL(p.mingguan, 0) > 0 THEN p.mingguan * {$weeksYtd}
            ELSE (
                IFNULL(p.senin, 0) * {$weekdayYtd['senin']} +
                IFNULL(p.selasa, 0) * {$weekdayYtd['selasa']} +
                IFNULL(p.rabu, 0) * {$weekdayYtd['rabu']} +
                IFNULL(p.kamis, 0) * {$weekdayYtd['kamis']} +
                IFNULL(p.jumat, 0) * {$weekdayYtd['jumat']} +
                IFNULL(p.sabtu, 0) * {$weekdayYtd['sabtu']} +
                IFNULL(p.minggu, 0) * {$weekdayYtd['minggu']}
            )
        END
    ";

    $trxJoin = "
        (
            SELECT
                transaksi_va,
                SUM(CASE WHEN transaksi_tanggal >= '{$startMonth}' AND transaksi_tanggal < '{$endMonth}'
                    THEN IFNULL(transaksi_nominal, 0) ELSE 0 END) AS realisasi_bulanan,
                SUM(CASE WHEN transaksi_tanggal >= '{$startYear}' AND transaksi_tanggal < '{$endYtd}'
                    THEN IFNULL(transaksi_nominal, 0) ELSE 0 END) AS realisasi_tahun
            FROM transaksi
            WHERE transaksi_tanggal >= '{$startYear}' AND transaksi_tanggal < '{$endYtd}'
            GROUP BY transaksi_va
        ) trx
    ";

    $kendalaJoin = "
        (
            SELECT
                k.kendala_va,
                COUNT(CASE WHEN k.created_at >= '{$startMonth}' AND k.created_at < '{$endMonth}'
                    THEN k.kendala_id END) AS jumlah_kendala_bulan,
                COUNT(k.kendala_id) AS jumlah_kendala_tahun,
                SUM(CASE
                    WHEN k.created_at >= '{$startMonth}' AND k.created_at < '{$endMonth}'
                    THEN (
                        CASE DAYOFWEEK(k.created_at)
                            WHEN 2 THEN IFNULL(ph.senin, p.senin)
                            WHEN 3 THEN IFNULL(ph.selasa, p.selasa)
                            WHEN 4 THEN IFNULL(ph.rabu, p.rabu)
                            WHEN 5 THEN IFNULL(ph.kamis, p.kamis)
                            WHEN 6 THEN IFNULL(ph.jumat, p.jumat)
                            WHEN 7 THEN IFNULL(ph.sabtu, p.sabtu)
                            WHEN 1 THEN IFNULL(ph.minggu, p.minggu)
                            ELSE 0
                        END
                    ) * (
                        CASE k.kategori
                            WHEN 1 THEN 1 WHEN 2 THEN 0.5 WHEN 3 THEN 0.25 ELSE 0
                        END
                    )
                    ELSE 0
                END) AS tagihan_kendala_bulan,
                SUM(
                    (
                        CASE DAYOFWEEK(k.created_at)
                            WHEN 2 THEN IFNULL(ph.senin, p.senin)
                            WHEN 3 THEN IFNULL(ph.selasa, p.selasa)
                            WHEN 4 THEN IFNULL(ph.rabu, p.rabu)
                            WHEN 5 THEN IFNULL(ph.kamis, p.kamis)
                            WHEN 6 THEN IFNULL(ph.jumat, p.jumat)
                            WHEN 7 THEN IFNULL(ph.sabtu, p.sabtu)
                            WHEN 1 THEN IFNULL(ph.minggu, p.minggu)
                            ELSE 0
                        END
                    ) * (
                        CASE k.kategori
                            WHEN 1 THEN 1 WHEN 2 THEN 0.5 WHEN 3 THEN 0.25 ELSE 0
                        END
                    )
                ) AS tagihan_kendala_tahun
            FROM kendala k
            JOIN potensi p ON p.potensi_va = k.kendala_va
            LEFT JOIN potensi_riwayat ph
                ON ph.potensi_va = k.kendala_va
                AND ph.berlaku_mulai <= DATE(k.created_at)
                AND (ph.berlaku_sampai IS NULL OR ph.berlaku_sampai >= DATE(k.created_at))
            WHERE k.created_at >= '{$startYear}' AND k.created_at < '{$endYtd}'
              AND k.approval = 1
            GROUP BY k.kendala_va
        ) k
    ";

    $subQuery = $this->db
        ->table('va_owner vo')
        ->select([
            'vo.va_owner_va',
            'g.titpargrup_anggotaid',
            'g.titpargrup_titparid',
            't.titpar_klrhnid',
            'a.anggota_nama',
            'a.anggota_id',
            't.titpar_namatempat',
            't.titpar_lokasi',
            't.titpar_latitude',
            't.titpar_longitude',
            'IFNULL(k.tagihan_kendala_bulan, 0) AS kendala_tagihan_bulan',
            'IFNULL(k.tagihan_kendala_tahun, 0) AS kendala_tagihan_tahun',
            "{$casePotensi} AS potensi_bulanan",
            "{$casePotensiTahun} AS target_tahun",
            'IFNULL(trx.realisasi_bulanan, 0) AS realisasi_bulanan',
            'IFNULL(trx.realisasi_tahun, 0) AS realisasi_tahun'
        ])
        ->join('potensi p', 'p.potensi_va = vo.va_owner_va', 'left')
        ->join('dishub_titpargrup g', 'g.titpargrup_id = vo.va_owner_titpargrupid')
        ->join('dishub_anggota a', 'a.anggota_id = g.titpargrup_anggotaid')
        ->join('dishub_titpar t', 't.titpar_id = g.titpargrup_titparid')
        ->join($trxJoin, 'trx.transaksi_va = vo.va_owner_va', 'left', false)
        ->join($kendalaJoin, 'k.kendala_va = vo.va_owner_va', 'left', false);

    if (!empty($filterId)) {
        $subQuery->groupStart()
            ->where('a.anggota_id', $filterId)
            ->orWhere('g.titpargrup_titparid', $filterId)
            ->orWhere('vo.va_owner_va', $filterId)
            ->groupEnd();
    }

    if (!empty($kecamatan)) {
        $subQuery->where("LEFT(t.titpar_klrhnid, 6)", $kecamatan);
    }

    if ($jenis === 'TJU') {
        $subQuery->like('vo.va_owner_va', '15088', 'after');
    } elseif ($jenis === 'TKP') {
        $subQuery->like('vo.va_owner_va', '10325', 'after');
    }

    $subQuery->where('vo.deleted_at IS NULL', null, false);

    $compiledSubQuery = $subQuery->getCompiledSelect();

    $builder = $this->db->table("({$compiledSubQuery}) x");

    $builder->select([
        '*',
        "CASE
            WHEN (potensi_bulanan - kendala_tagihan_bulan) <= 0
                THEN CASE WHEN realisasi_bulanan > 0 THEN 100 ELSE 0 END
            ELSE ROUND((realisasi_bulanan / (potensi_bulanan - kendala_tagihan_bulan)) * 100, 2)
         END AS persentase",
        "CASE
            WHEN (target_tahun - kendala_tagihan_tahun) <= 0
                THEN CASE WHEN realisasi_tahun > 0 THEN 100 ELSE 0 END
            ELSE ROUND((realisasi_tahun / (target_tahun - kendala_tagihan_tahun)) * 100, 2)
         END AS persentase_tahun"
    ]);

    return $builder;
}

     public function export()
{
    $request = service('request');

    $filterId  = trim($request->getGet('filter_id') ?? '');
    $bulan     = $request->getGet('bulan');
    $tahun     = $request->getGet('tahun');
    $kecamatan = $request->getGet('kecamatan');
     $jenis     = $request->getGet('jenis'); // TAMBAHKAN INI

    $builder = $this->buildPotensiQuery($filterId, $bulan, $tahun, $kecamatan, $jenis);
    $data    = $builder->get()->getResult();


    $kelurahanMap = [];
    foreach ($this->db->table('kelurahan')->select('kecamatan_id,kecamatan_nama')->where('provinsi', 'Jawa Barat')->where('kabupaten_kota', 'Kota Bogor')->distinct()->orderBy('kecamatan_nama')->get()->getResultArray() as $wilayah) {
        $kelurahanMap[$wilayah['kecamatan_id']] = $wilayah['kecamatan_nama'];
    }

    // catatan: kalau butuh nama kelurahan (bukan cuma kecamatan),
    // pindahkan array kelurahanData dari JS view ke sini dengan format sama

    $filename = 'potensi_realisasi_' . date('Ymd_His') . '.xls';

    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment; filename=\"{$filename}\"");
    header('Pragma: no-cache');
    header('Expires: 0');

    echo '<table border="1">';
    echo '<tr>
            <th>No.</th>
            <th>VA</th>
            <th>Lokasi</th>
            <th>Tempat Parkir</th>
            <th>Nama</th>
            <th>ID Jukir</th>
            <th>Kecamatan</th>
            <th>Tagihan Bln</th>
            <th>Pengurangan Bln</th>
            <th>Realisasi Bln</th>
            <th>% Bln</th>
            <th>Target Thn</th>
            <th>Pengurangan Thn</th>
            <th>Realisasi Thn</th>
            <th>% Thn</th>
          </tr>';

    $no = 1;
    foreach ($data as $r) {
        $kecCode = substr((string) $r->titpar_klrhnid, 0, 6);
        $kecName = $kelurahanMap[$kecCode] ?? '-';

        echo '<tr>';
        echo '<td>' . $no++ . '</td>';
        echo '<td style="mso-number-format:\'\@\';">' . htmlspecialchars($r->va_owner_va ?? '-') . '</td>';
        echo '<td>' . htmlspecialchars($r->titpar_lokasi ?? '-') . '</td>';
        echo '<td>' . htmlspecialchars($r->titpar_namatempat ?? '-') . '</td>';
        echo '<td>' . htmlspecialchars($r->anggota_nama) . '</td>';
        echo '<td>' . htmlspecialchars($r->anggota_id) . '</td>';
        echo '<td>' . $kecName . '</td>';
        echo '<td>' . number_format((float) $r->potensi_bulanan, 1) . '</td>';
        echo '<td>' . number_format((float) $r->kendala_tagihan_bulan, 1) . '</td>';
        echo '<td>' . number_format((float) $r->realisasi_bulanan, 1) . '</td>';
        echo '<td>' . number_format((float) $r->persentase, 1) . '%</td>';
        echo '<td>' . number_format((float) $r->target_tahun, 1) . '</td>';
        echo '<td>' . number_format((float) $r->kendala_tagihan_tahun, 1) . '</td>';
        echo '<td>' . number_format((float) $r->realisasi_tahun, 1) . '</td>';
        echo '<td>' . number_format((float) $r->persentase_tahun, 1) . '%</td>';
        echo '</tr>';
    }

    echo '</table>';
}

public function exportResume()
{
    $request = service('request');

    $filterId  = trim($request->getGet('filter_id') ?? '');
    $bulan     = $request->getGet('bulan');
    $tahun     = $request->getGet('tahun');
    $kecamatan = $request->getGet('kecamatan');
    // jenis sengaja tidak difilter di sini — resume justru memecah datanya per jenis

    $builder = $this->buildPotensiQuery($filterId, $bulan, $tahun, $kecamatan);
    $data    = $builder->get()->getResult();

    $kelurahanMap = [];
    foreach ($this->db->table('kelurahan')->select('kecamatan_id,kecamatan_nama')->where('provinsi', 'Jawa Barat')->where('kabupaten_kota', 'Kota Bogor')->distinct()->orderBy('kecamatan_nama')->get()->getResultArray() as $wilayah) {
        $kelurahanMap[$wilayah['kecamatan_id']] = $wilayah['kecamatan_nama'];
    }

    /* =====================================================
       GROUPING: Kecamatan -> Jenis (TJU/TKP)
       ===================================================== */
    $groups = [];

    foreach ($data as $r) {
        $kecCode = substr((string) $r->titpar_klrhnid, 0, 6);
        $kecName = $kelurahanMap[$kecCode] ?? 'Lainnya';

         $va        = (string) $r->va_owner_va;
            $namatempat = (string) ($r->titpar_namatempat ?? '');

            if (stripos($namatempat, 'INSIDENTIL') !== false) {
                $jenis = 'INSIDENTIL';
            } elseif (strpos($va, '15088') === 0) {
                $jenis = 'TJU';
            } elseif (strpos($va, '10325') === 0) {
                $jenis = 'TKP';
            } else {
                $jenis = 'Lainnya';
            }

        if (!isset($groups[$kecName][$jenis])) {
            $groups[$kecName][$jenis] = [
                'jumlah_va'         => 0,
                'potensi_bulanan'   => 0,
                'kendala_bulan'     => 0,
                'realisasi_bulanan' => 0,
                'target_tahun'      => 0,
                'kendala_tahun'     => 0,
                'realisasi_tahun'   => 0,
            ];
        }

        $g = &$groups[$kecName][$jenis];
        $g['jumlah_va']         += 1;
        $g['potensi_bulanan']   += (float) $r->potensi_bulanan;
        $g['kendala_bulan']     += (float) $r->kendala_tagihan_bulan;
        $g['realisasi_bulanan'] += (float) $r->realisasi_bulanan;
        $g['target_tahun']      += (float) $r->target_tahun;
        $g['kendala_tahun']     += (float) $r->kendala_tagihan_tahun;
        $g['realisasi_tahun']   += (float) $r->realisasi_tahun;
        unset($g);
    }

    // Urutkan kecamatan sesuai urutan map, sisanya (Lainnya) di akhir
    $orderedKec = array_values($kelurahanMap);
    uksort($groups, function ($a, $b) use ($orderedKec) {
        $posA = array_search($a, $orderedKec);
        $posB = array_search($b, $orderedKec);
        return ($posA === false ? 999 : $posA) <=> ($posB === false ? 999 : $posB);
    });

    $jenisOrder = ['TJU', 'TKP', 'INSIDENTIL', 'Lainnya'];

    /* =====================================================
       OUTPUT EXCEL
       ===================================================== */
    $filename = 'resume_potensi_realisasi_' . date('Ymd_His') . '.xls';

    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment; filename=\"{$filename}\"");
    header('Pragma: no-cache');
    header('Expires: 0');

    echo '<table border="1">';
    echo '<tr>
            <th>Kecamatan</th>
            <th>Jenis</th>
            <th>Jumlah VA</th>
            <th>Potensi Kotor (Bln)</th>
            <th>Pengurangan (Bln)</th>
            <th>Realisasi (Bln)</th>
            <th>% Bln</th>
         
          </tr>';

    foreach ($groups as $kecName => $jenisData) {

        uksort($jenisData, function ($a, $b) use ($jenisOrder) {
            $posA = array_search($a, $jenisOrder);
            $posB = array_search($b, $jenisOrder);
            return ($posA === false ? 999 : $posA) <=> ($posB === false ? 999 : $posB);
        });

        $first   = true;
        $rowspan = count($jenisData);

        foreach ($jenisData as $jenisName => $g) {

            $pctBulan = ($g['potensi_bulanan'] - $g['kendala_bulan']) > 0
                ? round(($g['realisasi_bulanan'] / ($g['potensi_bulanan'] - $g['kendala_bulan'])) * 100, 2)
                : 0;

            $pctTahun = ($g['target_tahun'] - $g['kendala_tahun']) > 0
                ? round(($g['realisasi_tahun'] / ($g['target_tahun'] - $g['kendala_tahun'])) * 100, 2)
                : 0;

            echo '<tr>';
            if ($first) {
                echo '<td rowspan="' . $rowspan . '" style="vertical-align:top;font-weight:bold;">' . htmlspecialchars($kecName) . '</td>';
                $first = false;
            }
            echo '<td>' . htmlspecialchars($jenisName) . '</td>';
            echo '<td>' . $g['jumlah_va'] . '</td>';
            echo '<td>' . number_format($g['potensi_bulanan'], 1) . '</td>';
            echo '<td>' . number_format($g['kendala_bulan'], 1) . '</td>';
            echo '<td>' . number_format($g['realisasi_bulanan'], 1) . '</td>';
            echo '<td>' . number_format($pctBulan, 1) . '%</td>';
          
            echo '</tr>';
        }
    }

    echo '</table>';
}

    public function invoice($id){

      $builder = $this->db->table('transaksi')->select('*')
      ->where('transaksi_id',$id)
      ->join('va_owner','transaksi.transaksi_va = va_owner.va_owner_va')
      ->join('dishub_anggota','dishub_anggota.anggota_id = va_owner.va_owner_anggotaid')
      ->join('dishub_titpargrup','dishub_titpargrup.titpargrup_anggotaid = dishub_anggota.anggota_id')
      ->join('dishub_titpar','dishub_titpargrup.titpargrup_titparid = dishub_titpar.titpar_id')
      ->get()->getResult();
      // print_r($builder);

        setlocale(LC_TIME, 'id_ID.utf8'); // Use Indonesian locale
        $tanggal = strftime('%e %B %Y', strtotime($builder[0]->transaksi_tanggal));
        
    $data = [
        'invoiceNumber' => $builder[0]->transaksi_id,

        'nama' => $builder[0]->anggota_nama,
        'amount' => $builder[0]->transaksi_nominal,
        'titpar' => $builder[0]->titpar_namatempat,
        'alamat' => $builder[0]->titpar_lokasi,
        'tanggal' => $tanggal,
    ];

    return view('transaksi/invoice', $data);
    }

    public function realisasi(){
        return view('potensi/tagihan');
    }

     public function realisasitahunan(){
        return view('potensi/tagihantahunan');
    }

    public function getDaysInMonth(){

    }

    public function getVaWithTransaksi($va)
{
    $model = new \App\Models\DishubAnggotaModel();

    $data = $model->getVaWithTransaksi($va);

    if (!$data) {
        return $this->response
            ->setStatusCode(404)
            ->setJSON([
                'status'  => false,
                'message' => 'VA tidak ditemukan'
            ]);
    }

    return $this->response->setJSON([
        'status' => true,
        'data'   => $data
    ]);
}

public function datatagihan()
{
    $temporaryTables = [];
    try {
        return $this->buildTagihanResponse($temporaryTables);
    } finally {
        foreach ($temporaryTables as $table) {
            $this->db->query("DROP TEMPORARY TABLE IF EXISTS `{$table}`");
        }
    }
}

private function buildTagihanResponse(array &$temporaryTables)
{
    $request = service('request');

    /* =========================================================
     * FILTER
     * ========================================================= */
    $filterId  = trim($request->getPost('filter_id') ?? '');
    $bulan     = $request->getPost('bulan');
    $tahun     = $request->getPost('tahun');
    $reset     = $request->getPost('reset');
    $kecamatan = $request->getPost('kecamatan');
    $jenis = $this->request->getPost('jenis');

    if ($reset === '1') {
        $filterId  = null;
        $bulan     = null;
        $tahun     = null;
        $kecamatan = null;
         $jenis     = null;
    }

    if (empty($bulan)) {
        $bulan = date('n');
    }

    if (empty($tahun)) {
        $tahun = date('Y');
    }



    $bulan = (int) $bulan;
    $tahun = (int) $tahun;

    $bulanPad = str_pad($bulan, 2, '0', STR_PAD_LEFT);

    $startMonth = "{$tahun}-{$bulanPad}-01";
    $startYear  = "{$tahun}-01-01";

    /*
     * End bulan yang dipilih
     */
    $endMonth = date(
        'Y-m-d',
        strtotime($startMonth . ' +1 month')
    );

    /*
     * End YTD = awal bulan berikutnya
     *
     * Contoh:
     * bulan 7 -> 2026-08-01
     */
    $endYtd = date(
        'Y-m-d',
        strtotime($startMonth . ' +1 month')
    );

    $daysInMonth = cal_days_in_month(
        CAL_GREGORIAN,
        $bulan,
        $tahun
    );

    /* =========================================================
     * MAP HARI
     * ========================================================= */
    $map = [
        1 => 'senin',
        2 => 'selasa',
        3 => 'rabu',
        4 => 'kamis',
        5 => 'jumat',
        6 => 'sabtu',
        7 => 'minggu'
    ];

    /* =========================================================
     * WEEKDAY BULAN
     * ========================================================= */
    $weekdayMonth = array_fill_keys(
        array_values($map),
        0
    );

    for ($d = 1; $d <= $daysInMonth; $d++) {

        $date = sprintf(
            '%04d-%02d-%02d',
            $tahun,
            $bulan,
            $d
        );

        $day = (int) date(
            'N',
            strtotime($date)
        );

        $weekdayMonth[$map[$day]]++;
    }

    /* =========================================================
     * WEEKDAY YTD
     * ========================================================= */
    $startYtdDate = new DateTime(
        "{$tahun}-01-01"
    );

    $endYtdDate = new DateTime(
        $endYtd
    );

    /*
     * Karena endYtd adalah awal bulan berikutnya,
     * kita mundurkan satu hari.
     */
    $endYtdDate->modify('-1 day');

    $weekdayYtd = array_fill_keys(
        array_values($map),
        0
    );

    while ($startYtdDate <= $endYtdDate) {

        $day = (int) $startYtdDate->format('N');

        $weekdayYtd[$map[$day]]++;

        $startYtdDate->modify('+1 day');
    }

    /*
     * Jumlah minggu
     */
    $weeksInMonth = (int) ceil(
        $daysInMonth / 7
    );

    $totalDaysYtd = array_sum(
        $weekdayYtd
    );

    $weeksYtd = (int) ceil(
        $totalDaysYtd / 7
    );

    /* =========================================================
     * TARGET TAHUN BERDASARKAN HISTORY HARIAN
     * ========================================================= */
    // Intersect each history period with months instead of expanding every day.
    // Weekday counts preserve daily/weekly/monthly proration, including leap years.
    // Keep the original six-decimal daily rounding before multiplying day counts.
    $calendarSelects = [];
    for ($month = 1; $month <= $bulan; $month++) {
        $date = sprintf('%04d-%02d-01', $tahun, $month);
        $calendarSelects[] = 'SELECT ' . $this->db->escape($date) . ' AS tanggal';
    }
    $calendarSql = implode(' UNION ALL ', $calendarSelects);
    $dailyTerms = [];
    foreach (array_values($map) as $weekday => $column) {
        $dailyTerms[] = "IFNULL(h.{$column}, 0) * FLOOR((DATEDIFF(h.end_date, h.start_date) + 7
            - MOD({$weekday} - WEEKDAY(h.start_date) + 7, 7)) / 7)";
    }
    $dailyTotal = implode(' + ', $dailyTerms);
    $targetYtdJoin = "
        (
            SELECT h.potensi_va AS va_owner_va,
                COALESCE(SUM(CASE
                    WHEN IFNULL(h.bulanan, 0) > 0
                        THEN CAST(h.bulanan / DAY(LAST_DAY(h.tanggal)) AS DECIMAL(30,6)) * (DATEDIFF(h.end_date, h.start_date) + 1)
                    WHEN IFNULL(h.mingguan, 0) > 0
                        THEN CAST(h.mingguan / 7 AS DECIMAL(30,6)) * (DATEDIFF(h.end_date, h.start_date) + 1)
                    ELSE {$dailyTotal}
                END), 0) AS target_tahun_history
            FROM (
                SELECT ph.*, cal.tanggal,
                    GREATEST(ph.berlaku_mulai, cal.tanggal) AS start_date,
                    LEAST(COALESCE(ph.berlaku_sampai, LAST_DAY(cal.tanggal)), LAST_DAY(cal.tanggal)) AS end_date
                FROM ({$calendarSql}) cal
                INNER JOIN potensi_riwayat ph
                    ON ph.berlaku_mulai <= LAST_DAY(cal.tanggal)
                   AND (ph.berlaku_sampai IS NULL OR ph.berlaku_sampai >= cal.tanggal)
            ) h
            GROUP BY h.potensi_va
        ) target_ytd
    ";

    /* =========================================================
     * CASE POTENSI BULAN
     * ========================================================= */
    $casePotensi = "
        CASE

            WHEN IFNULL(p.bulanan, 0) > 0
                THEN p.bulanan

            WHEN IFNULL(p.mingguan, 0) > 0
                THEN p.mingguan * {$weeksInMonth}

            ELSE
                (
                    IFNULL(p.senin, 0) * {$weekdayMonth['senin']} +
                    IFNULL(p.selasa, 0) * {$weekdayMonth['selasa']} +
                    IFNULL(p.rabu, 0) * {$weekdayMonth['rabu']} +
                    IFNULL(p.kamis, 0) * {$weekdayMonth['kamis']} +
                    IFNULL(p.jumat, 0) * {$weekdayMonth['jumat']} +
                    IFNULL(p.sabtu, 0) * {$weekdayMonth['sabtu']} +
                    IFNULL(p.minggu, 0) * {$weekdayMonth['minggu']}
                )

        END
    ";

    /* =========================================================
     * CASE TARGET TAHUN / YTD
     * ========================================================= */
    $casePotensiTahun = "
        CASE

            WHEN IFNULL(p.bulanan, 0) > 0
                THEN p.bulanan * {$bulan}

            WHEN IFNULL(p.mingguan, 0) > 0
                THEN p.mingguan * {$weeksYtd}

            ELSE
                (
                    IFNULL(p.senin, 0) * {$weekdayYtd['senin']} +
                    IFNULL(p.selasa, 0) * {$weekdayYtd['selasa']} +
                    IFNULL(p.rabu, 0) * {$weekdayYtd['rabu']} +
                    IFNULL(p.kamis, 0) * {$weekdayYtd['kamis']} +
                    IFNULL(p.jumat, 0) * {$weekdayYtd['jumat']} +
                    IFNULL(p.sabtu, 0) * {$weekdayYtd['sabtu']} +
                    IFNULL(p.minggu, 0) * {$weekdayYtd['minggu']}
                )

        END
    ";

    /* =========================================================
     * SUBQUERY TRANSAKSI
     *
     * Monthly:
     *   tanggal >= awal bulan
     *   tanggal < awal bulan berikutnya
     *
     * YTD:
     *   tanggal >= 1 Januari
     *   tanggal < awal bulan berikutnya
     * ========================================================= */
    $trxJoin = "
        (
            SELECT

                transaksi_va,

                SUM(
                    CASE
                        WHEN transaksi_tanggal >= '{$startMonth}'
                         AND transaksi_tanggal < '{$endMonth}'
                        THEN IFNULL(transaksi_nominal, 0)
                        ELSE 0
                    END
                ) AS realisasi_bulanan,

                SUM(
                    CASE
                        WHEN transaksi_tanggal >= '{$startYear}'
                         AND transaksi_tanggal < '{$endYtd}'
                        THEN IFNULL(transaksi_nominal, 0)
                        ELSE 0
                    END
                ) AS realisasi_tahun

            FROM transaksi

            WHERE transaksi_tanggal >= '{$startYear}'
              AND transaksi_tanggal < '{$endYtd}'

            GROUP BY transaksi_va
        ) trx
    ";

    /* =========================================================
     * SUBQUERY KENDALA
     * ========================================================= */
    $kendalaJoin = "
        (
            SELECT

                CONVERT(k.kendala_va USING utf8mb4) COLLATE utf8mb4_general_ci AS kendala_va,

                COUNT(
                    CASE
                        WHEN k.created_at >= '{$startMonth}'
                         AND k.created_at < '{$endMonth}'
                        THEN k.kendala_id
                    END
                ) AS jumlah_kendala_bulan,

                COUNT(
                    k.kendala_id
                ) AS jumlah_kendala_tahun,

                /*
                 * PENGURANGAN BULAN
                 */
                SUM(
                    CASE

                        WHEN k.created_at >= '{$startMonth}'
                         AND k.created_at < '{$endMonth}'

                        THEN

                            (
                                CASE DAYOFWEEK(k.created_at)

                                    WHEN 2 THEN IFNULL(ph.senin, p.senin)
                                    WHEN 3 THEN IFNULL(ph.selasa, p.selasa)
                                    WHEN 4 THEN IFNULL(ph.rabu, p.rabu)
                                    WHEN 5 THEN IFNULL(ph.kamis, p.kamis)
                                    WHEN 6 THEN IFNULL(ph.jumat, p.jumat)
                                    WHEN 7 THEN IFNULL(ph.sabtu, p.sabtu)
                                    WHEN 1 THEN IFNULL(ph.minggu, p.minggu)

                                    ELSE 0

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

                        ELSE 0

                    END
                ) AS tagihan_kendala_bulan,

                /*
                 * PENGURANGAN YTD
                 */
                SUM(

                    (
                        CASE DAYOFWEEK(k.created_at)

                            WHEN 2 THEN IFNULL(ph.senin, p.senin)
                            WHEN 3 THEN IFNULL(ph.selasa, p.selasa)
                            WHEN 4 THEN IFNULL(ph.rabu, p.rabu)
                            WHEN 5 THEN IFNULL(ph.kamis, p.kamis)
                            WHEN 6 THEN IFNULL(ph.jumat, p.jumat)
                            WHEN 7 THEN IFNULL(ph.sabtu, p.sabtu)
                            WHEN 1 THEN IFNULL(ph.minggu, p.minggu)

                            ELSE 0

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

                ) AS tagihan_kendala_tahun

            FROM kendala k

            JOIN potensi p
                ON p.potensi_va = k.kendala_va

            LEFT JOIN potensi_riwayat ph
                ON ph.potensi_va = k.kendala_va
                AND ph.berlaku_mulai <= DATE(k.created_at)
                AND (ph.berlaku_sampai IS NULL OR ph.berlaku_sampai >= DATE(k.created_at))

            WHERE k.created_at >= '{$startYear}'
              AND k.created_at < '{$endYtd}'

              AND k.approval = 1

            GROUP BY k.kendala_va

        ) k
    ";

    // Compute each aggregate once per request and index the join keys.
    // These tables are connection-local and removed in finally; no stale cache.
    foreach ([
        'trx' => [$trxJoin, 'transaksi_va'],
        'target_ytd' => [$targetYtdJoin, 'va_owner_va'],
        'k' => [$kendalaJoin, 'kendala_va'],
    ] as $alias => [$aggregate, $key]) {
        $table = 'tmp_realisasi_' . $alias . '_' . bin2hex(random_bytes(6));
        $this->db->query("CREATE TEMPORARY TABLE `{$table}` (INDEX (`{$key}`)) AS SELECT * FROM {$aggregate}");
        $temporaryTables[] = $table;
        if ($alias === 'trx') {
            $trxJoin = "`{$table}` trx";
        } elseif ($alias === 'target_ytd') {
            $targetYtdJoin = "`{$table}` target_ytd";
        } else {
            $kendalaJoin = "`{$table}` k";
        }
    }

    /* =========================================================
     * MAIN SUBQUERY
     * ========================================================= */
    $subQuery = $this->db
        ->table('va_owner vo')

        ->select([
            'vo.va_owner_va',

            'g.titpargrup_anggotaid',

            'g.titpargrup_titparid',

            't.titpar_klrhnid',

            'a.anggota_nama',

            'a.anggota_id',

            't.titpar_namatempat',

            't.titpar_lokasi',

            't.titpar_latitude',

            't.titpar_longitude',

            /*
             * Pengurangan / Kendala
             */
            'IFNULL(k.tagihan_kendala_bulan, 0) AS kendala_tagihan_bulan',

            'IFNULL(k.tagihan_kendala_tahun, 0) AS kendala_tagihan_tahun',

            /*
             * Potensi Bulanan
             */
            "{$casePotensi} AS potensi_bulanan",

            /*
             * Target Tahun / YTD
             */
            'IFNULL(target_ytd.target_tahun_history, 0) AS target_tahun',

            /*
             * Realisasi
             */
            'IFNULL(trx.realisasi_bulanan, 0) AS realisasi_bulanan',

            'IFNULL(trx.realisasi_tahun, 0) AS realisasi_tahun'
        ])

        ->join(
            '(SELECT p1.* FROM potensi_riwayat p1
              WHERE p1.berlaku_mulai <= ' . $this->db->escape($startMonth) . '
                AND (p1.berlaku_sampai IS NULL OR p1.berlaku_sampai >= ' . $this->db->escape($startMonth) . ')
                AND NOT EXISTS (
                    SELECT 1 FROM potensi_riwayat p2
                    WHERE p2.potensi_va = p1.potensi_va
                      AND p2.berlaku_mulai <= ' . $this->db->escape($startMonth) . '
                      AND (
                          p2.berlaku_mulai > p1.berlaku_mulai
                          OR (
                              p2.berlaku_mulai = p1.berlaku_mulai
                              AND p2.potensi_riwayat_id > p1.potensi_riwayat_id
                          )
                      )
                )) p',
            'p.potensi_va = vo.va_owner_va',
            'left',
            false
        )

        ->join(
            'dishub_titpargrup g',
            'g.titpargrup_id = vo.va_owner_titpargrupid'
        )

        ->join(
            'dishub_anggota a',
            'a.anggota_id = g.titpargrup_anggotaid'
        )

        ->join(
            'dishub_titpar t',
            't.titpar_id = g.titpargrup_titparid'
        )

        // Hanya tampilkan titik parkir yang masih aktif di halaman realisasi.
        ->where('t.titpar_status', 3)

        ->join(
            $trxJoin,
            'trx.transaksi_va = vo.va_owner_va',
            'left',
            false
        )

        ->join(
            $targetYtdJoin,
            'target_ytd.va_owner_va = vo.va_owner_va',
            'left',
            false
        )

        ->join(
            $kendalaJoin,
            'k.kendala_va = vo.va_owner_va',
            'left',
            false
        );

    /* =========================================================
     * FILTER ID
     *
     * Bisa mencari:
     * - anggota_id
     * - titpargrup_titparid
     * - va_owner_va
     * ========================================================= */
    if (!empty($filterId)) {

        $subQuery
            ->groupStart()

            ->where(
                'a.anggota_id',
                $filterId
            )

            ->orWhere(
                'g.titpargrup_titparid',
                $filterId
            )

            ->orWhere(
                'vo.va_owner_va',
                $filterId
            )

            ->groupEnd();
    }

    /* =========================================================
     * FILTER KECAMATAN
     * ========================================================= */
    if (!empty($kecamatan)) {

        $subQuery->where(
            "LEFT(t.titpar_klrhnid, 6)",
            $kecamatan
        );
    }

    /* =========================================================
         * FILTER JENIS BERDASARKAN PREFIX VA
         *
         * 15088 = TJU
         * 10325 = TKP
         * ========================================================= */
        if ($jenis === 'TJU') {

            $subQuery->like(
                'vo.va_owner_va',
                '15088',
                'after'
            );

        } elseif ($jenis === 'TKP') {

            $subQuery->like(
                'vo.va_owner_va',
                '10325',
                'after'
            );
        }

    /* =========================================================
     * FILTER VA YANG TIDAK DIHAPUS
     *
     * Hanya mengambil:
     * deleted_at IS NULL
     *
     * Contoh yang akan dikeluarkan:
     *
     * va_owner_va = 1508800005140611
     * deleted_at  = 2026-07-25 06:12:49
     * ========================================================= */
    $subQuery->where(
        'vo.deleted_at IS NULL',
        null,
        false
    );

    /* =========================================================
     * COMPILE SUBQUERY
     * ========================================================= */
    $compiledSubQuery = $subQuery
        ->getCompiledSelect();

    /* =========================================================
 * TOTAL SUMMARY
 * Menggunakan filter yang sama dengan DataTable
 * ========================================================= */
$summaryBuilder = $this->db
    ->table("({$compiledSubQuery}) x");

$summary = $summaryBuilder
    ->select([
        'COUNT(*) AS total_data',

        'COALESCE(SUM(potensi_bulanan), 0) AS total_potensi_bulanan',

        'COALESCE(SUM(kendala_tagihan_bulan), 0) AS total_kendala_bulanan',

        'COALESCE(SUM(realisasi_bulanan), 0) AS total_realisasi_bulanan',

        'COALESCE(SUM(target_tahun), 0) AS total_target_tahun',

        'COALESCE(SUM(kendala_tagihan_tahun), 0) AS total_kendala_tahun',

        'COALESCE(SUM(realisasi_tahun), 0) AS total_realisasi_tahun'
    ])
    ->get()
    ->getRowArray();

    /* =========================================================
     * OUTER QUERY
     *
     * Dibutuhkan agar alias:
     * - persentase
     * - persentase_tahun
     *
     * bisa digunakan oleh DataTables untuk server-side sorting.
     * ========================================================= */
    $builder = $this->db
        ->table("({$compiledSubQuery}) x");

    $builder->select([
        '*',

        /*
         * Persentase Bulanan
         */
        'CASE
            WHEN (potensi_bulanan - kendala_tagihan_bulan) <= 0
                THEN CASE WHEN realisasi_bulanan > 0 THEN 100 ELSE 0 END
            ELSE ROUND(
                (realisasi_bulanan / (potensi_bulanan - kendala_tagihan_bulan)) * 100,
                2
            )
         END AS persentase',

        /*
         * Persentase YTD
         */
        'CASE
            WHEN (target_tahun - kendala_tagihan_tahun) <= 0
                THEN CASE WHEN realisasi_tahun > 0 THEN 100 ELSE 0 END
            ELSE ROUND(
                (realisasi_tahun / (target_tahun - kendala_tagihan_tahun)) * 100,
                2
            )
         END AS persentase_tahun'
    ]);

    /* =========================================================
     * KOLOM SEARCH DATATABLES
     * ========================================================= */
    $columns = [
        'va_owner_va',
        'anggota_nama',
        'titpar_namatempat',
        'titpar_lokasi',
        'titpar_klrhnid'
    ];

    /* =========================================================
     * MAPPING SORTING DATATABLES
     *
     * Sesuai index kolom HTML:
     *
     * 0  No
     * 1  Jukir / VA
     * 2  Tempat Parkir
     * 3  Kel / Kec
     * 4  Tagihan Bln
     * 5  Pengurangan Bln
     * 6  Realisasi Bln
     * 7  % Bln
     * 8  Target Thn
     * 9  Pengurangan Thn
     * 10 Realisasi Thn
     * 11 % Thn
     * 12 Aksi
     * ========================================================= */
    $sortable = [

        1  => 'anggota_nama',

        2  => 'titpar_namatempat',

        3  => 'titpar_klrhnid',

        4  => 'potensi_bulanan',

        5  => 'kendala_tagihan_bulan',

        6  => 'realisasi_bulanan',

        7  => 'persentase',

        8  => 'target_tahun',

        9  => 'kendala_tagihan_tahun',

        10 => 'realisasi_tahun',

        11 => 'persentase_tahun'
    ];

    /* =========================================================
     * DATATABLE LIBRARY
     * ========================================================= */
    $dt = new \App\Libraries\DataTable(
        $builder,
        $columns,
        $sortable
    );

    $data = $dt->generate((int) $summary['total_data']);

$data['summary'] = [
    'total_data' => (int) ($summary['total_data'] ?? 0),

    'total_potensi_bulanan' =>
        (float) ($summary['total_potensi_bulanan'] ?? 0),

    'total_kendala_bulanan' =>
        (float) ($summary['total_kendala_bulanan'] ?? 0),

    'total_realisasi_bulanan' =>
        (float) ($summary['total_realisasi_bulanan'] ?? 0),

    'total_target_tahun' =>
        (float) ($summary['total_target_tahun'] ?? 0),

    'total_kendala_tahun' =>
        (float) ($summary['total_kendala_tahun'] ?? 0),

    'total_realisasi_tahun' =>
        (float) ($summary['total_realisasi_tahun'] ?? 0),
];

return $this->response->setJSON($data);
}

public function datatagihaneueu()
{
    $request = service('request');

    $filterId  = trim($request->getPost('filter_id') ?? '');
    $bulan     = $request->getPost('bulan');
    $tahun     = $request->getPost('tahun');
    $reset     = $request->getPost('reset');
    $kecamatan = $request->getPost('kecamatan');

    /*
    |--------------------------------------------------------------------------
    | RESET FILTER
    |--------------------------------------------------------------------------
    */

    if ($reset === '1') {
        $filterId  = null;
        $bulan     = null;
        $tahun     = null;
        $kecamatan = null;
    }

    /*
    |--------------------------------------------------------------------------
    | DEFAULT BULAN & TAHUN
    |--------------------------------------------------------------------------
    */

    if (empty($bulan)) {
        $bulan = date('n');
    }

    if (empty($tahun)) {
        $tahun = date('Y');
    }

    $bulan = (int) $bulan;
    $tahun = (int) $tahun;

    $bulanPad = str_pad(
        $bulan,
        2,
        '0',
        STR_PAD_LEFT
    );

    $startMonth = "{$tahun}-{$bulanPad}-01";
    $startYear  = "{$tahun}-01-01";

    /*
    |--------------------------------------------------------------------------
    | JUMLAH HARI DALAM BULAN
    |--------------------------------------------------------------------------
    */

    $daysInMonth = cal_days_in_month(
        CAL_GREGORIAN,
        $bulan,
        $tahun
    );

    /*
    |--------------------------------------------------------------------------
    | MAPPING HARI
    |--------------------------------------------------------------------------
    */

    $map = [
        1 => 'senin',
        2 => 'selasa',
        3 => 'rabu',
        4 => 'kamis',
        5 => 'jumat',
        6 => 'sabtu',
        7 => 'minggu'
    ];

    /*
    |--------------------------------------------------------------------------
    | WEEKDAY BULAN
    |--------------------------------------------------------------------------
    */

    $weekdayMonth = array_fill_keys(
        array_values($map),
        0
    );

    for ($d = 1; $d <= $daysInMonth; $d++) {

        $day = date(
            'N',
            strtotime(
                "{$tahun}-{$bulanPad}-{$d}"
            )
        );

        $weekdayMonth[$map[$day]]++;
    }

    /*
    |--------------------------------------------------------------------------
    | WEEKDAY YTD
    |--------------------------------------------------------------------------
    */

    $startYtd = new DateTime(
        "{$tahun}-01-01"
    );

    $endYtd = new DateTime(
        "{$tahun}-{$bulanPad}-{$daysInMonth}"
    );

    $weekdayYtd = array_fill_keys(
        array_values($map),
        0
    );

    while ($startYtd <= $endYtd) {

        $day = $startYtd->format('N');

        $weekdayYtd[$map[$day]]++;

        $startYtd->modify('+1 day');
    }

    /*
    |--------------------------------------------------------------------------
    | MINGGU
    |--------------------------------------------------------------------------
    */

    $weeksInMonth = ceil(
        $daysInMonth / 7
    );

    $weeksYtd = ceil(
        array_sum($weekdayYtd) / 7
    );

    /*
    |--------------------------------------------------------------------------
    | CASE POTENSI BULAN
    |--------------------------------------------------------------------------
    */

    $casePotensi = "
        CASE

            WHEN p.bulanan > 0
                THEN p.bulanan

            WHEN p.mingguan > 0
                THEN p.mingguan * {$weeksInMonth}

            ELSE
                (
                    IFNULL(p.senin, 0)
                        * {$weekdayMonth['senin']}

                    + IFNULL(p.selasa, 0)
                        * {$weekdayMonth['selasa']}

                    + IFNULL(p.rabu, 0)
                        * {$weekdayMonth['rabu']}

                    + IFNULL(p.kamis, 0)
                        * {$weekdayMonth['kamis']}

                    + IFNULL(p.jumat, 0)
                        * {$weekdayMonth['jumat']}

                    + IFNULL(p.sabtu, 0)
                        * {$weekdayMonth['sabtu']}

                    + IFNULL(p.minggu, 0)
                        * {$weekdayMonth['minggu']}
                )

        END
    ";

    /*
    |--------------------------------------------------------------------------
    | CASE POTENSI TAHUN / YTD
    |--------------------------------------------------------------------------
    */

    $casePotensiTahun = "
        CASE

            WHEN p.bulanan > 0
                THEN p.bulanan * {$bulan}

            WHEN p.mingguan > 0
                THEN p.mingguan * {$weeksYtd}

            ELSE
                (
                    IFNULL(p.senin, 0)
                        * {$weekdayYtd['senin']}

                    + IFNULL(p.selasa, 0)
                        * {$weekdayYtd['selasa']}

                    + IFNULL(p.rabu, 0)
                        * {$weekdayYtd['rabu']}

                    + IFNULL(p.kamis, 0)
                        * {$weekdayYtd['kamis']}

                    + IFNULL(p.jumat, 0)
                        * {$weekdayYtd['jumat']}

                    + IFNULL(p.sabtu, 0)
                        * {$weekdayYtd['sabtu']}

                    + IFNULL(p.minggu, 0)
                        * {$weekdayYtd['minggu']}
                )

        END
    ";

    /*
    |--------------------------------------------------------------------------
    | SUBQUERY TRANSAKSI
    |--------------------------------------------------------------------------
    */

    $trxJoin = "
        (
            SELECT

                transaksi_va,

                SUM(
                    CASE
                        WHEN transaksi_tanggal >= '{$startMonth}'
                        AND transaksi_tanggal < DATE_ADD(
                            '{$startMonth}',
                            INTERVAL 1 MONTH
                        )
                        THEN transaksi_nominal
                        ELSE 0
                    END
                ) AS realisasi_bulanan,

                SUM(
                    CASE
                        WHEN transaksi_tanggal >= '{$startYear}'
                        AND transaksi_tanggal < DATE_ADD(
                            '{$startYear}',
                            INTERVAL {$bulan} MONTH
                        )
                        THEN transaksi_nominal
                        ELSE 0
                    END
                ) AS realisasi_tahun

            FROM transaksi

            WHERE transaksi_tanggal >= '{$startYear}'

            GROUP BY transaksi_va
        ) trx
    ";

    /*
    |--------------------------------------------------------------------------
    | SUBQUERY KENDALA
    |--------------------------------------------------------------------------
    */

    $kendalaJoin = "
        (
            SELECT

                k.kendala_va,

                COUNT(
                    CASE
                        WHEN k.created_at >= '{$startMonth}'
                        AND k.created_at < DATE_ADD(
                            '{$startMonth}',
                            INTERVAL 1 MONTH
                        )
                        THEN k.kendala_id
                    END
                ) AS jumlah_kendala_bulan,

                COUNT(
                    k.kendala_id
                ) AS jumlah_kendala_tahun,

                SUM(
                    CASE

                        WHEN k.created_at >= '{$startMonth}'
                        AND k.created_at < DATE_ADD(
                            '{$startMonth}',
                            INTERVAL 1 MONTH
                        )

                        THEN

                            (
                                CASE DAYOFWEEK(k.created_at)

                                    WHEN 2
                                        THEN IFNULL(p.senin, 0)

                                    WHEN 3
                                        THEN IFNULL(p.selasa, 0)

                                    WHEN 4
                                        THEN IFNULL(p.rabu, 0)

                                    WHEN 5
                                        THEN IFNULL(p.kamis, 0)

                                    WHEN 6
                                        THEN IFNULL(p.jumat, 0)

                                    WHEN 7
                                        THEN IFNULL(p.sabtu, 0)

                                    WHEN 1
                                        THEN IFNULL(p.minggu, 0)

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

                        ELSE 0

                    END
                ) AS tagihan_kendala_bulan,

                SUM(

                    (
                        CASE DAYOFWEEK(k.created_at)

                            WHEN 2
                                THEN IFNULL(p.senin, 0)

                            WHEN 3
                                THEN IFNULL(p.selasa, 0)

                            WHEN 4
                                THEN IFNULL(p.rabu, 0)

                            WHEN 5
                                THEN IFNULL(p.kamis, 0)

                            WHEN 6
                                THEN IFNULL(p.jumat, 0)

                            WHEN 7
                                THEN IFNULL(p.sabtu, 0)

                            WHEN 1
                                THEN IFNULL(p.minggu, 0)

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

                ) AS tagihan_kendala_tahun

            FROM kendala k

            JOIN potensi p
                ON p.potensi_va = k.kendala_va

            WHERE k.created_at >= '{$startYear}'

            AND k.created_at < DATE_ADD(
                '{$startYear}',
                INTERVAL {$bulan} MONTH
            )

            AND k.approval = 1

            GROUP BY k.kendala_va

        ) k
    ";

    /*
    |--------------------------------------------------------------------------
    | MAIN SUBQUERY
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    | vo.deleted_at IS NULL is placed directly here.
    | This guarantees deleted VA records are excluded
    | before getCompiledSelect().
    |
    */

    $subQuery = $this->db
        ->table('va_owner vo')

        ->select([
            'vo.va_owner_va',

            'g.titpargrup_anggotaid',

            'g.titpargrup_titparid',

            't.titpar_klrhnid',

            'a.anggota_nama',

            'a.anggota_id',

            't.titpar_namatempat',

            't.titpar_lokasi',

            't.titpar_latitude',

            't.titpar_longitude',

            'IFNULL(
                k.tagihan_kendala_bulan,
                0
            ) AS kendala_tagihan_bulan',

            'IFNULL(
                k.tagihan_kendala_tahun,
                0
            ) AS kendala_tagihan_tahun',

            "{$casePotensi}
                AS potensi_bulanan",

            "{$casePotensiTahun}
                AS target_tahun",

            'IFNULL(
                trx.realisasi_bulanan,
                0
            ) AS realisasi_bulanan',

            'IFNULL(
                trx.realisasi_tahun,
                0
            ) AS realisasi_tahun'
        ])

        /*
        |--------------------------------------------------------------------------
        | EXCLUDE DELETED VA
        |--------------------------------------------------------------------------
        */

        ->where(
            'vo.deleted_at IS NULL',
            null,
            false
        )

        ->join(
            'potensi p',
            'p.potensi_va = vo.va_owner_va',
            'left'
        )

        ->join(
            'dishub_titpargrup g',
            'g.titpargrup_id = vo.va_owner_titpargrupid'
        )

        ->join(
            'dishub_anggota a',
            'a.anggota_id = g.titpargrup_anggotaid'
        )

        ->join(
            'dishub_titpar t',
            't.titpar_id = g.titpargrup_titparid'
        )

        ->join(
            $trxJoin,
            'trx.transaksi_va = vo.va_owner_va',
            'left',
            false
        )

        ->join(
            $kendalaJoin,
            'k.kendala_va = vo.va_owner_va',
            'left',
            false
        );


    /*
    |--------------------------------------------------------------------------
    | FILTER ID
    |--------------------------------------------------------------------------
    |
    | Search by:
    | - Jukir / Anggota ID
    | - Titik Parkir ID
    | - VA
    |
    */

    if (!empty($filterId)) {

        $subQuery
            ->groupStart()

            ->where(
                'a.anggota_id',
                $filterId
            )

            ->orWhere(
                'g.titpargrup_titparid',
                $filterId
            )

            ->orWhere(
                'vo.va_owner_va',
                $filterId
            )

            ->groupEnd();
    }


    /*
    |--------------------------------------------------------------------------
    | FILTER KECAMATAN
    |--------------------------------------------------------------------------
    */

    if (!empty($kecamatan)) {

        $subQuery->where(
            "LEFT(t.titpar_klrhnid, 6)",
            $kecamatan
        );
    }


    /*
    |--------------------------------------------------------------------------
    | COMPILE SUBQUERY
    |--------------------------------------------------------------------------
    */

    $compiledSql =
        $subQuery->getCompiledSelect();


    /*
    |--------------------------------------------------------------------------
    | OPTIONAL DEBUG
    |--------------------------------------------------------------------------
    |
    | Uncomment if you want to inspect the SQL:
    |
    | log_message(
    |     'error',
    |     'DATATAGIHAN SQL: ' . $compiledSql
    | );
    |
    */


    /*
    |--------------------------------------------------------------------------
    | OUTER QUERY
    |--------------------------------------------------------------------------
    */

    $builder = $this->db->table(
        "({$compiledSql}) x"
    );

    $builder->select([
        '*',

        'ROUND(
            (
                realisasi_bulanan
                /
                NULLIF(
                    (
                        potensi_bulanan
                        -
                        kendala_tagihan_bulan
                    ),
                    0
                )
            ) * 100,
            2
        ) AS persentase',

        'ROUND(
            (
                realisasi_tahun
                /
                NULLIF(
                    (
                        target_tahun
                        -
                        kendala_tagihan_tahun
                    ),
                    0
                )
            ) * 100,
            2
        ) AS persentase_tahun'
    ]);


    /*
    |--------------------------------------------------------------------------
    | DATATABLE SEARCH COLUMNS
    |--------------------------------------------------------------------------
    */

    $columns = [
        'va_owner_va',
        'anggota_nama',
        'titpar_namatempat',
        'titpar_lokasi',
        'titpar_klrhnid'
    ];


    /*
    |--------------------------------------------------------------------------
    | DATATABLE SORTING
    |--------------------------------------------------------------------------
    |
    | Index must match the actual HTML table column index.
    |
    | 0 = No
    | 1 = Jukir / VA
    | 2 = Tempat Parkir
    | 3 = Kel / Kec
    | 4 = Tagihan Bln
    | 5 = Pengurangan Bln
    | 6 = Realisasi Bln
    | 7 = % Bln
    | 8 = Target Thn
    | 9 = Pengurangan Thn
    | 10 = Realisasi Thn
    | 11 = % Thn
    | 12 = Aksi
    |
    */

    $sortable = [

        1  => 'anggota_nama',

        2  => 'titpar_namatempat',

        3  => 'titpar_klrhnid',

        4  => 'potensi_bulanan',

        5  => 'kendala_tagihan_bulan',

        6  => 'realisasi_bulanan',

        7  => 'persentase',

        8  => 'target_tahun',

        9  => 'kendala_tagihan_tahun',

        10 => 'realisasi_tahun',

        11 => 'persentase_tahun'

    ];


    /*
    |--------------------------------------------------------------------------
    | DATATABLE
    |--------------------------------------------------------------------------
    */

    $dt = new \App\Libraries\DataTable(
        $builder,
        $columns,
        $sortable
    );


    /*
    |--------------------------------------------------------------------------
    | RETURN JSON
    |--------------------------------------------------------------------------
    */

    return $this->response->setJSON(
        $dt->generate()
    );
}

public function datatagihanuaeoua()
{
    $request = service('request');

    $filterId  = trim($request->getPost('filter_id') ?? '');
    $bulan     = $request->getPost('bulan');
    $tahun     = $request->getPost('tahun');
    $reset     = $request->getPost('reset');
    $kecamatan = $request->getPost('kecamatan');

    /*
    |--------------------------------------------------------------------------
    | RESET
    |--------------------------------------------------------------------------
    */

    if ($reset === '1') {
        $filterId  = null;
        $bulan     = null;
        $tahun     = null;
        $kecamatan = null;
    }

    /*
    |--------------------------------------------------------------------------
    | DEFAULT DATE
    |--------------------------------------------------------------------------
    */

    if (empty($bulan)) {
        $bulan = date('n');
    }

    if (empty($tahun)) {
        $tahun = date('Y');
    }

    $bulan = (int) $bulan;
    $tahun = (int) $tahun;

    $bulanPad = str_pad(
        $bulan,
        2,
        '0',
        STR_PAD_LEFT
    );

    $startMonth = "{$tahun}-{$bulanPad}-01";
    $startYear  = "{$tahun}-01-01";

    $daysInMonth = cal_days_in_month(
        CAL_GREGORIAN,
        $bulan,
        $tahun
    );


    /*
    |--------------------------------------------------------------------------
    | WEEKDAY MAPPING
    |--------------------------------------------------------------------------
    */

    $map = [
        1 => 'senin',
        2 => 'selasa',
        3 => 'rabu',
        4 => 'kamis',
        5 => 'jumat',
        6 => 'sabtu',
        7 => 'minggu'
    ];


    /*
    |--------------------------------------------------------------------------
    | WEEKDAY BULAN
    |--------------------------------------------------------------------------
    */

    $weekdayMonth = array_fill_keys(
        array_values($map),
        0
    );

    for ($d = 1; $d <= $daysInMonth; $d++) {

        $day = date(
            'N',
            strtotime(
                "{$tahun}-{$bulanPad}-{$d}"
            )
        );

        $weekdayMonth[$map[$day]]++;
    }


    /*
    |--------------------------------------------------------------------------
    | WEEKDAY YTD
    |--------------------------------------------------------------------------
    */

    $startYtd = new DateTime(
        "{$tahun}-01-01"
    );

    $endYtd = new DateTime(
        "{$tahun}-{$bulanPad}-{$daysInMonth}"
    );

    $weekdayYtd = array_fill_keys(
        array_values($map),
        0
    );

    while ($startYtd <= $endYtd) {

        $day = $startYtd->format('N');

        $weekdayYtd[$map[$day]]++;

        $startYtd->modify('+1 day');
    }


    /*
    |--------------------------------------------------------------------------
    | WEEK CALCULATION
    |--------------------------------------------------------------------------
    */

    $weeksInMonth = ceil(
        $daysInMonth / 7
    );

    $weeksYtd = ceil(
        array_sum($weekdayYtd) / 7
    );


    /*
    |--------------------------------------------------------------------------
    | POTENSI BULAN
    |--------------------------------------------------------------------------
    */

    $casePotensi = "
        CASE

            WHEN p.bulanan > 0
                THEN p.bulanan

            WHEN p.mingguan > 0
                THEN p.mingguan * {$weeksInMonth}

            ELSE
                (
                    IFNULL(p.senin, 0)
                    * {$weekdayMonth['senin']}

                    +

                    IFNULL(p.selasa, 0)
                    * {$weekdayMonth['selasa']}

                    +

                    IFNULL(p.rabu, 0)
                    * {$weekdayMonth['rabu']}

                    +

                    IFNULL(p.kamis, 0)
                    * {$weekdayMonth['kamis']}

                    +

                    IFNULL(p.jumat, 0)
                    * {$weekdayMonth['jumat']}

                    +

                    IFNULL(p.sabtu, 0)
                    * {$weekdayMonth['sabtu']}

                    +

                    IFNULL(p.minggu, 0)
                    * {$weekdayMonth['minggu']}
                )

        END
    ";


    /*
    |--------------------------------------------------------------------------
    | POTENSI TAHUN / YTD
    |--------------------------------------------------------------------------
    */

    $casePotensiTahun = "
        CASE

            WHEN p.bulanan > 0
                THEN p.bulanan * {$bulan}

            WHEN p.mingguan > 0
                THEN p.mingguan * {$weeksYtd}

            ELSE
                (
                    IFNULL(p.senin, 0)
                    * {$weekdayYtd['senin']}

                    +

                    IFNULL(p.selasa, 0)
                    * {$weekdayYtd['selasa']}

                    +

                    IFNULL(p.rabu, 0)
                    * {$weekdayYtd['rabu']}

                    +

                    IFNULL(p.kamis, 0)
                    * {$weekdayYtd['kamis']}

                    +

                    IFNULL(p.jumat, 0)
                    * {$weekdayYtd['jumat']}

                    +

                    IFNULL(p.sabtu, 0)
                    * {$weekdayYtd['sabtu']}

                    +

                    IFNULL(p.minggu, 0)
                    * {$weekdayYtd['minggu']}
                )

        END
    ";


    /*
    |--------------------------------------------------------------------------
    | TRANSAKSI SUBQUERY
    |--------------------------------------------------------------------------
    */

    $trxJoin = "
        (
            SELECT

                transaksi_va,

                SUM(
                    CASE
                        WHEN transaksi_tanggal >= '{$startMonth}'
                        AND transaksi_tanggal <
                            DATE_ADD(
                                '{$startMonth}',
                                INTERVAL 1 MONTH
                            )
                        THEN transaksi_nominal
                        ELSE 0
                    END
                ) AS realisasi_bulanan,

                SUM(
                    CASE
                        WHEN transaksi_tanggal >= '{$startYear}'
                        AND transaksi_tanggal <
                            DATE_ADD(
                                '{$startYear}',
                                INTERVAL {$bulan} MONTH
                            )
                        THEN transaksi_nominal
                        ELSE 0
                    END
                ) AS realisasi_tahun

            FROM transaksi

            WHERE transaksi_tanggal >= '{$startYear}'

            GROUP BY transaksi_va

        ) trx
    ";


    /*
    |--------------------------------------------------------------------------
    | KENDALA SUBQUERY
    |--------------------------------------------------------------------------
    */

    $kendalaJoin = "
        (
            SELECT

                k.kendala_va,

                COUNT(
                    CASE
                        WHEN k.created_at >= '{$startMonth}'
                        AND k.created_at <
                            DATE_ADD(
                                '{$startMonth}',
                                INTERVAL 1 MONTH
                            )
                        THEN k.kendala_id
                    END
                ) AS jumlah_kendala_bulan,

                COUNT(
                    k.kendala_id
                ) AS jumlah_kendala_tahun,

                SUM(
                    CASE

                        WHEN k.created_at >= '{$startMonth}'
                        AND k.created_at <
                            DATE_ADD(
                                '{$startMonth}',
                                INTERVAL 1 MONTH
                            )

                        THEN

                            (
                                CASE DAYOFWEEK(k.created_at)

                                    WHEN 2
                                        THEN IFNULL(p.senin, 0)

                                    WHEN 3
                                        THEN IFNULL(p.selasa, 0)

                                    WHEN 4
                                        THEN IFNULL(p.rabu, 0)

                                    WHEN 5
                                        THEN IFNULL(p.kamis, 0)

                                    WHEN 6
                                        THEN IFNULL(p.jumat, 0)

                                    WHEN 7
                                        THEN IFNULL(p.sabtu, 0)

                                    WHEN 1
                                        THEN IFNULL(p.minggu, 0)

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

                        ELSE 0

                    END
                ) AS tagihan_kendala_bulan,


                SUM(

                    (
                        CASE DAYOFWEEK(k.created_at)

                            WHEN 2
                                THEN IFNULL(p.senin, 0)

                            WHEN 3
                                THEN IFNULL(p.selasa, 0)

                            WHEN 4
                                THEN IFNULL(p.rabu, 0)

                            WHEN 5
                                THEN IFNULL(p.kamis, 0)

                            WHEN 6
                                THEN IFNULL(p.jumat, 0)

                            WHEN 7
                                THEN IFNULL(p.sabtu, 0)

                            WHEN 1
                                THEN IFNULL(p.minggu, 0)

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

                ) AS tagihan_kendala_tahun


            FROM kendala k

            JOIN potensi p
                ON p.potensi_va = k.kendala_va

            WHERE k.created_at >= '{$startYear}'

            AND k.created_at <
                DATE_ADD(
                    '{$startYear}',
                    INTERVAL {$bulan} MONTH
                )

            AND k.approval = 1

            GROUP BY k.kendala_va

        ) k
    ";


    /*
    |--------------------------------------------------------------------------
    | MAIN SUBQUERY
    |--------------------------------------------------------------------------
    */

    $subQuery = $this->db
        ->table('va_owner vo')

        ->select([
            'vo.va_owner_va',

            'g.titpargrup_anggotaid',

            'g.titpargrup_titparid',

            't.titpar_klrhnid',

            'a.anggota_nama',

            'a.anggota_id',

            't.titpar_namatempat',

            't.titpar_lokasi',

            't.titpar_latitude',

            't.titpar_longitude',

            "IFNULL(
                k.tagihan_kendala_bulan,
                0
            ) AS kendala_tagihan_bulan",

            "IFNULL(
                k.tagihan_kendala_tahun,
                0
            ) AS kendala_tagihan_tahun",

            "{$casePotensi}
                AS potensi_bulanan",

            "{$casePotensiTahun}
                AS target_tahun",

            "IFNULL(
                trx.realisasi_bulanan,
                0
            ) AS realisasi_bulanan",

            "IFNULL(
                trx.realisasi_tahun,
                0
            ) AS realisasi_tahun"
        ])

        ->join(
            'potensi p',
            'p.potensi_va = vo.va_owner_va',
            'left'
        )

        ->join(
            'dishub_titpargrup g',
            'g.titpargrup_id = vo.va_owner_titpargrupid'
        )

        ->join(
            'dishub_anggota a',
            'a.anggota_id = g.titpargrup_anggotaid'
        )

        ->join(
            'dishub_titpar t',
            't.titpar_id = g.titpargrup_titparid'
        )

        ->join(
            $trxJoin,
            'trx.transaksi_va = vo.va_owner_va',
            'left',
            false
        )

        ->join(
            $kendalaJoin,
            'k.kendala_va = vo.va_owner_va',
            'left',
            false
        );


    /*
    |--------------------------------------------------------------------------
    | FILTER ID
    |--------------------------------------------------------------------------
    */

    if (!empty($filterId)) {

        $subQuery
            ->groupStart()

            ->where(
                'a.anggota_id',
                $filterId
            )

            ->orWhere(
                'g.titpargrup_titparid',
                $filterId
            )

            ->orWhere(
                'vo.va_owner_va',
                $filterId
            )

            ->groupEnd();
    }


    /*
    |--------------------------------------------------------------------------
    | FILTER KECAMATAN
    |--------------------------------------------------------------------------
    */

    if (!empty($kecamatan)) {

        $subQuery->where(
            "LEFT(t.titpar_klrhnid, 6)",
            $kecamatan
        );
    }


    /*
    |--------------------------------------------------------------------------
    | COMPILE SUBQUERY
    |--------------------------------------------------------------------------
    */

    $subQuerySql =
        $subQuery->getCompiledSelect();


    /*
    |--------------------------------------------------------------------------
    | DERIVED TABLE
    |--------------------------------------------------------------------------
    */

    $builder = $this->db
        ->newQuery()
        ->from(
            "({$subQuerySql}) x",
            false
        );


    /*
    |--------------------------------------------------------------------------
    | FINAL SELECT
    |--------------------------------------------------------------------------
    */

    $builder->select([
        'x.*',

        'ROUND(
            (
                x.realisasi_bulanan /
                NULLIF(
                    (
                        x.potensi_bulanan
                        -
                        x.kendala_tagihan_bulan
                    ),
                    0
                )
            ) * 100,
            2
        ) AS persentase',

        'ROUND(
            (
                x.realisasi_tahun /
                NULLIF(
                    (
                        x.target_tahun
                        -
                        x.kendala_tagihan_tahun
                    ),
                    0
                )
            ) * 100,
            2
        ) AS persentase_tahun'
    ], false);


    /*
    |--------------------------------------------------------------------------
    | DATATABLE SEARCH COLUMNS
    |--------------------------------------------------------------------------
    */

    $columns = [
        'va_owner_va',
        'anggota_nama',
        'titpar_namatempat',
        'titpar_lokasi',
        'titpar_klrhnid'
    ];


    /*
    |--------------------------------------------------------------------------
    | DATATABLE SORTING
    |--------------------------------------------------------------------------
    |
    | HTML TABLE INDEX
    |
    | 0  = No.
    | 1  = Jukir / VA
    | 2  = Tempat Parkir
    | 3  = Kel / Kec
    | 4  = Tagihan Bln
    | 5  = Pengurangan Bln
    | 6  = Realisasi Bln
    | 7  = % Bln
    | 8  = Target Thn
    | 9  = Pengurangan Thn
    | 10 = Realisasi Thn
    | 11 = % Thn
    | 12 = Aksi
    |
    */

    $sortable = [
        0  => null,

        1  => 'anggota_nama',

        2  => 'titpar_namatempat',

        3  => 'titpar_klrhnid',

        4  => 'potensi_bulanan',

        5  => 'kendala_tagihan_bulan',

        6  => 'realisasi_bulanan',

        7  => 'persentase',

        8  => 'target_tahun',

        9  => 'kendala_tagihan_tahun',

        10 => 'realisasi_tahun',

        11 => 'persentase_tahun',

        12 => null
    ];


    /*
    |--------------------------------------------------------------------------
    | DATATABLE
    |--------------------------------------------------------------------------
    */

    $dt = new \App\Libraries\DataTable(
        $builder,
        $columns,
        $sortable
    );


    /*
    |--------------------------------------------------------------------------
    | RESPONSE
    |--------------------------------------------------------------------------
    */

    return $this->response->setJSON(
        $dt->generate()
    );
}
public function datatagihanOLDSFIX()
{
    $request = service('request');

    $bulan     = $request->getPost('bulan');
    $tahun     = $request->getPost('tahun');
    $reset     = $request->getPost('reset');
    $kecamatan = $request->getPost('kecamatan');

    if ($reset === '1') {
        $bulan = null;
        $tahun = null;
    }

    if (empty($bulan)) $bulan = date('n');
    if (empty($tahun)) $tahun = date('Y');

    $bulan = (int)$bulan;
    $tahun = (int)$tahun;

    $bulanPad = str_pad($bulan, 2, '0', STR_PAD_LEFT);

    $startMonth = "{$tahun}-{$bulanPad}-01";
    $startYear  = "{$tahun}-01-01";

    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
    $weeksInMonth = ceil($daysInMonth / 7);

    $map = [1=>'senin',2=>'selasa',3=>'rabu',4=>'kamis',5=>'jumat',6=>'sabtu',7=>'minggu'];

    /* ========================= WEEKDAY BULAN ========================= */
    $weekdayMonth = array_fill_keys(array_values($map), 0);

    for ($d = 1; $d <= $daysInMonth; $d++) {
        $day = date('N', strtotime("$tahun-$bulanPad-$d"));
        $weekdayMonth[$map[$day]]++;
    }

    /* ========================= WEEKDAY YTD ========================= */
    $startYtd = new DateTime("$tahun-01-01");
    $endYtd   = new DateTime("$tahun-$bulanPad-$daysInMonth");

    $weekdayYtd = array_fill_keys(array_values($map), 0);

    while ($startYtd <= $endYtd) {
        $day = $startYtd->format('N');
        $weekdayYtd[$map[$day]]++;
        $startYtd->modify('+1 day');
    }

    $weeksYtd = ceil(array_sum($weekdayYtd) / 7);

    /* ========================= CASE POTENSI ========================= */

    $casePotensi = "
    CASE 
        WHEN p.bulanan > 0 THEN p.bulanan
        WHEN p.mingguan > 0 THEN p.mingguan * {$weeksInMonth}
        ELSE
            (IFNULL(p.senin,0) * {$weekdayMonth['senin']} +
             IFNULL(p.selasa,0) * {$weekdayMonth['selasa']} +
             IFNULL(p.rabu,0) * {$weekdayMonth['rabu']} +
             IFNULL(p.kamis,0) * {$weekdayMonth['kamis']} +
             IFNULL(p.jumat,0) * {$weekdayMonth['jumat']} +
             IFNULL(p.sabtu,0) * {$weekdayMonth['sabtu']} +
             IFNULL(p.minggu,0) * {$weekdayMonth['minggu']})
    END
    ";

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

    /* ========================= SUBQUERY TRANSAKSI ========================= */

    $trxJoin = "
    (
        SELECT 
            transaksi_va,
            SUM(CASE 
                WHEN transaksi_tanggal >= '{$startMonth}'
                AND transaksi_tanggal < DATE_ADD('{$startMonth}', INTERVAL 1 MONTH)
                THEN transaksi_nominal ELSE 0 END
            ) AS realisasi_bulanan,

            SUM(CASE 
                WHEN transaksi_tanggal >= '{$startYear}'
                AND transaksi_tanggal < DATE_ADD('{$startYear}', INTERVAL {$bulan} MONTH)
                THEN transaksi_nominal ELSE 0 END
            ) AS realisasi_tahun

        FROM transaksi
        WHERE transaksi_tanggal >= '{$startYear}'
        GROUP BY transaksi_va
    ) trx
    ";

    /* ========================= SUBQUERY KENDALA ========================= */

    $kendalaJoin = "
(
    SELECT 
        k.kendala_va,

        COUNT(CASE 
            WHEN k.created_at >= '{$startMonth}'
            AND k.created_at < DATE_ADD('{$startMonth}', INTERVAL 1 MONTH)
            THEN k.kendala_id END
        ) AS jumlah_kendala_bulan,

        COUNT(k.kendala_id) AS jumlah_kendala_tahun,

        SUM(
            CASE 
                WHEN k.created_at >= '{$startMonth}'
                AND k.created_at < DATE_ADD('{$startMonth}', INTERVAL 1 MONTH)
                THEN
                    (
                        CASE DAYOFWEEK(k.created_at)
                            WHEN 2 THEN IFNULL(p.senin,0)
                            WHEN 3 THEN IFNULL(p.selasa,0)
                            WHEN 4 THEN IFNULL(p.rabu,0)
                            WHEN 5 THEN IFNULL(p.kamis,0)
                            WHEN 6 THEN IFNULL(p.jumat,0)
                            WHEN 7 THEN IFNULL(p.sabtu,0)
                            WHEN 1 THEN IFNULL(p.minggu,0)
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
                ELSE 0
            END
        ) AS tagihan_kendala_bulan,

        SUM(
            (
                CASE DAYOFWEEK(k.created_at)
                    WHEN 2 THEN IFNULL(p.senin,0)
                    WHEN 3 THEN IFNULL(p.selasa,0)
                    WHEN 4 THEN IFNULL(p.rabu,0)
                    WHEN 5 THEN IFNULL(p.kamis,0)
                    WHEN 6 THEN IFNULL(p.jumat,0)
                    WHEN 7 THEN IFNULL(p.sabtu,0)
                    WHEN 1 THEN IFNULL(p.minggu,0)
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
        ) AS tagihan_kendala_tahun

    FROM kendala k
    JOIN potensi p ON p.potensi_va = k.kendala_va
    WHERE k.created_at >= '{$startYear}'
    AND k.created_at < DATE_ADD('{$startYear}', INTERVAL {$bulan} MONTH)
    AND k.approval = 1
    GROUP BY k.kendala_va
)
";

    /* ========================= MAIN QUERY ========================= */

    $subQuery = $this->db->table('va_owner vo')
        ->select([
            'vo.va_owner_va',
            'g.titpargrup_anggotaid',
            'g.titpargrup_titparid',
            't.titpar_klrhnid',
            'a.anggota_nama',
            'a.anggota_id',
            't.titpar_namatempat',
            't.titpar_lokasi',
            't.titpar_latitude',
            't.titpar_longitude',
            "IFNULL(k.tagihan_kendala_bulan,0) AS kendala_tagihan_bulan",
            "IFNULL(k.tagihan_kendala_tahun,0) AS kendala_tagihan_tahun",
            "{$casePotensi} AS potensi_bulanan",
            "{$casePotensiTahun} AS target_tahun",
            "IFNULL(trx.realisasi_bulanan,0) AS realisasi_bulanan",
            "IFNULL(trx.realisasi_tahun,0) AS realisasi_tahun"
        ])
        ->join('potensi p', 'p.potensi_va = vo.va_owner_va', 'left')
        ->join('dishub_titpargrup g', 'g.titpargrup_id = vo.va_owner_titpargrupid')
        ->join('dishub_anggota a', 'a.anggota_id = g.titpargrup_anggotaid')
        ->join('dishub_titpar t', 't.titpar_id = g.titpargrup_titparid')
        ->join($trxJoin, 'trx.transaksi_va = vo.va_owner_va', 'left', false)
        ->join("{$kendalaJoin} k", 'k.kendala_va = vo.va_owner_va', 'left', false);

    if (!empty($kecamatan)) {
        $subQuery->where("LEFT(t.titpar_klrhnid,6)", $kecamatan);
    }
     $subQuery->where(
    'vo.deleted_at IS NULL',
    null,
    false
);

    $builder = $this->db->table("({$subQuery->getCompiledSelect()}) x");

    $builder->select([
        '*',
        'ROUND((realisasi_bulanan / NULLIF((potensi_bulanan-kendala_tagihan_bulan),0)) * 100, 2) AS persentase',
        'ROUND((realisasi_tahun / NULLIF(target_tahun-kendala_tagihan_tahun,0)) * 100, 2) AS persentase_tahun'
    ]);

    $columns = ['va_owner_va','anggota_nama','titpar_namatempat','titpar_klrhnid'];

    $sortable = [
        1 => 'va_owner_va',
        2 => 'anggota_nama',
        3 => 'titpar_namatempat',
        4 => 'titpar_klrhnid',
        5 => 'potensi_bulanan',
        6 => 'realisasi_bulanan',
        7 => 'persentase',
        8 => 'target_tahun',
        9 => 'realisasi_tahun',
        10 => 'persentase_tahun'
    ];

    $dt = new \App\Libraries\DataTable($builder, $columns, $sortable);
    return $this->response->setJSON($dt->generate());
}

public function datatagihantest()
{
    $request = service('request');

    $bulan     = $request->getPost('bulan');
    $tahun     = $request->getPost('tahun');
    $reset     = $request->getPost('reset');
    $kecamatan = $request->getPost('kecamatan');

    if ($reset === '1') {
        $bulan = null;
        $tahun = null;
    }

    if (empty($bulan)) $bulan = date('n');
    if (empty($tahun)) $tahun = date('Y');

    $bulan = (int)$bulan;
    $tahun = (int)$tahun;

    $bulanPad = str_pad($bulan, 2, '0', STR_PAD_LEFT);

    $startMonth = "{$tahun}-{$bulanPad}-01";
    $startYear  = "{$tahun}-01-01";

    /* =========================
       HITUNG WEEKDAY BULANAN
    ========================= */

    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

    $weekdayMonth = [
        'senin'=>0,'selasa'=>0,'rabu'=>0,'kamis'=>0,'jumat'=>0,'sabtu'=>0,'minggu'=>0
    ];

    for ($d = 1; $d <= $daysInMonth; $d++) {

        $day = date('N', strtotime("$tahun-$bulanPad-$d"));

        switch ($day) {
            case 1: $weekdayMonth['senin']++; break;
            case 2: $weekdayMonth['selasa']++; break;
            case 3: $weekdayMonth['rabu']++; break;
            case 4: $weekdayMonth['kamis']++; break;
            case 5: $weekdayMonth['jumat']++; break;
            case 6: $weekdayMonth['sabtu']++; break;
            case 7: $weekdayMonth['minggu']++; break;
        }
    }

    /* =========================
       HITUNG WEEKDAY YTD
    ========================= */

    $startYtd = new DateTime("$tahun-01-01");
    $endYtd   = new DateTime("$tahun-$bulanPad-$daysInMonth");

    $weekdayYtd = [
        'senin'=>0,'selasa'=>0,'rabu'=>0,'kamis'=>0,'jumat'=>0,'sabtu'=>0,'minggu'=>0
    ];

    while ($startYtd <= $endYtd) {

        $day = $startYtd->format('N');

        switch ($day) {
            case 1: $weekdayYtd['senin']++; break;
            case 2: $weekdayYtd['selasa']++; break;
            case 3: $weekdayYtd['rabu']++; break;
            case 4: $weekdayYtd['kamis']++; break;
            case 5: $weekdayYtd['jumat']++; break;
            case 6: $weekdayYtd['sabtu']++; break;
            case 7: $weekdayYtd['minggu']++; break;
        }

        $startYtd->modify('+1 day');
    }

    /* =========================
       CASE POTENSI BULAN
    ========================= */

    $casePotensi = "
    (
        CASE 
            WHEN p.bulanan IS NOT NULL AND p.bulanan > 0 
                THEN p.bulanan

            WHEN p.mingguan IS NOT NULL AND p.mingguan > 0 
                THEN p.mingguan * 4

            ELSE
                (IFNULL(p.senin,0) * {$weekdayMonth['senin']} +
                 IFNULL(p.selasa,0) * {$weekdayMonth['selasa']} +
                 IFNULL(p.rabu,0) * {$weekdayMonth['rabu']} +
                 IFNULL(p.kamis,0) * {$weekdayMonth['kamis']} +
                 IFNULL(p.jumat,0) * {$weekdayMonth['jumat']} +
                 IFNULL(p.sabtu,0) * {$weekdayMonth['sabtu']} +
                 IFNULL(p.minggu,0) * {$weekdayMonth['minggu']})
        END
    )
    ";

    /* =========================
       CASE POTENSI TAHUN (YTD)
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

    /* =========================
       SUBQUERY TRANSAKSI (OPTIMIZED)
    ========================= */

    $trxJoin = "
    (
        SELECT 
            transaksi_va,

            SUM(
                CASE 
                    WHEN transaksi_tanggal >= '{$startMonth}'
                    AND transaksi_tanggal < DATE_ADD('{$startMonth}', INTERVAL 1 MONTH)
                    THEN transaksi_nominal ELSE 0
                END
            ) AS realisasi_bulanan,

            SUM(
                CASE 
                    WHEN transaksi_tanggal >= '{$startYear}'
                    AND transaksi_tanggal < DATE_ADD('{$startYear}', INTERVAL {$bulan} MONTH)
                    THEN transaksi_nominal ELSE 0
                END
            ) AS realisasi_tahun

        FROM transaksi
        GROUP BY transaksi_va
    ) trx
    ";

    /* =========================
       QUERY BUILDER
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

        "{$casePotensi} AS potensi_bulanan",

        "IFNULL(trx.realisasi_bulanan,0) AS realisasi_bulanan",

        "(IFNULL(trx.realisasi_bulanan,0) / NULLIF({$casePotensi},0)) * 100 AS persentase",

        "{$casePotensiTahun} AS target_tahun",

        "IFNULL(trx.realisasi_tahun,0) AS realisasi_tahun",

        "(IFNULL(trx.realisasi_tahun,0) / NULLIF({$casePotensiTahun},0)) * 100 AS persentase_tahun"
    ]);

    $builder->join('potensi p', 'p.potensi_va = vo.va_owner_va', 'left');
    $builder->join('dishub_titpargrup g', 'g.titpargrup_id = vo.va_owner_titpargrupid');
    $builder->join('dishub_anggota a', 'a.anggota_id = g.titpargrup_anggotaid');
    $builder->join('dishub_titpar t', 't.titpar_id = g.titpargrup_titparid');
    $builder->join($trxJoin, 'trx.transaksi_va = vo.va_owner_va', 'left', false);

    if (!empty($kecamatan)) {
        $builder->where("LEFT(t.titpar_klrhnid,6)", $kecamatan);
    }

    $columns = [
        'va_owner_va',
        'anggota_nama',
        'titpar_namatempat',
        'titpar_klrhnid'
    ];

    $sortable = [
        1 => 'va_owner_va',
        2 => 'anggota_nama',
        3 => 'titpar_namatempat',
        4 => 'titpar_klrhnid',
        5 => 'potensi_bulanan',
        6 => 'realisasi_bulanan',
        7 => 'persentase',
        8 => 'target_tahun',
        9 => 'realisasi_tahun',
        10 => 'persentase_tahun'
    ];

    $dt = new \App\Libraries\DataTable($builder, $columns, $sortable);
    $result = $dt->generate();

    return $this->response->setJSON($result);
}

public function datatagihan9()
{
    $request = service('request');

    $bulan     = $request->getPost('bulan');
    $tahun     = $request->getPost('tahun');
    $reset     = $request->getPost('reset');
    $kecamatan = $request->getPost('kecamatan');

    if ($reset == '1') {
        $bulan = null;
        $tahun = null;
    }

    if (empty($bulan)) $bulan = date('n');
    if (empty($tahun)) $tahun = date('Y');

    $bulan = (int)$bulan;
    $tahun = (int)$tahun;

    $startMonth = "{$tahun}-{$bulan}-01";
    $startYear  = "{$tahun}-01-01";

    /* =========================
       HITUNG WEEKDAY BULANAN
    ========================= */

    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

    $weekdayMonth = [
        'senin'=>0,'selasa'=>0,'rabu'=>0,'kamis'=>0,'jumat'=>0,'sabtu'=>0,'minggu'=>0
    ];

    for ($d = 1; $d <= $daysInMonth; $d++) {

        $day = date('N', strtotime("$tahun-$bulan-$d"));

        switch ($day) {
            case 1: $weekdayMonth['senin']++; break;
            case 2: $weekdayMonth['selasa']++; break;
            case 3: $weekdayMonth['rabu']++; break;
            case 4: $weekdayMonth['kamis']++; break;
            case 5: $weekdayMonth['jumat']++; break;
            case 6: $weekdayMonth['sabtu']++; break;
            case 7: $weekdayMonth['minggu']++; break;
        }
    }

    /* =========================
       HITUNG WEEKDAY YTD
    ========================= */

    $startYtd = new DateTime("$tahun-01-01");
    $endYtd   = new DateTime("$tahun-$bulan-$daysInMonth");

    $weekdayYtd = [
        'senin'=>0,'selasa'=>0,'rabu'=>0,'kamis'=>0,'jumat'=>0,'sabtu'=>0,'minggu'=>0
    ];

    while ($startYtd <= $endYtd) {

        $day = $startYtd->format('N');

        switch ($day) {
            case 1: $weekdayYtd['senin']++; break;
            case 2: $weekdayYtd['selasa']++; break;
            case 3: $weekdayYtd['rabu']++; break;
            case 4: $weekdayYtd['kamis']++; break;
            case 5: $weekdayYtd['jumat']++; break;
            case 6: $weekdayYtd['sabtu']++; break;
            case 7: $weekdayYtd['minggu']++; break;
        }

        $startYtd->modify('+1 day');
    }

    /* =========================
       CASE POTENSI BULAN
    ========================= */

    $casePotensi = "
    (
        CASE 
            WHEN p.bulanan IS NOT NULL AND p.bulanan > 0 
                THEN p.bulanan

            WHEN p.mingguan IS NOT NULL AND p.mingguan > 0 
                THEN p.mingguan * 4

            ELSE
                (IFNULL(p.senin,0) * {$weekdayMonth['senin']} +
                 IFNULL(p.selasa,0) * {$weekdayMonth['selasa']} +
                 IFNULL(p.rabu,0) * {$weekdayMonth['rabu']} +
                 IFNULL(p.kamis,0) * {$weekdayMonth['kamis']} +
                 IFNULL(p.jumat,0) * {$weekdayMonth['jumat']} +
                 IFNULL(p.sabtu,0) * {$weekdayMonth['sabtu']} +
                 IFNULL(p.minggu,0) * {$weekdayMonth['minggu']})
        END
    )
    ";

    /* =========================
       CASE POTENSI TAHUN (YTD)
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

    /* =========================
       QUERY BUILDER
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

        "{$casePotensi} AS potensi_bulanan",

        "(
            SELECT IFNULL(SUM(t1.transaksi_nominal),0)
            FROM transaksi t1
            WHERE t1.transaksi_va = vo.va_owner_va
            AND t1.transaksi_tanggal >= '{$startMonth}'
            AND t1.transaksi_tanggal < DATE_ADD('{$startMonth}', INTERVAL 1 MONTH)
        ) AS realisasi_bulanan",

        "(
            (
                SELECT IFNULL(SUM(t1.transaksi_nominal),0)
                FROM transaksi t1
                WHERE t1.transaksi_va = vo.va_owner_va
                AND t1.transaksi_tanggal >= '{$startMonth}'
                AND t1.transaksi_tanggal < DATE_ADD('{$startMonth}', INTERVAL 1 MONTH)
            ) / NULLIF({$casePotensi},0)
        ) * 100 AS persentase",

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

    if (!empty($kecamatan)) {
        $builder->where("LEFT(t.titpar_klrhnid,6)", $kecamatan);
    }

    $columns = [
        'va_owner_va',
        'anggota_nama',
        'titpar_namatempat',
        'titpar_klrhnid'
    ];

    $sortable = [
        1 => 'va_owner_va',
        2 => 'anggota_nama',
        3 => 'titpar_namatempat',
        4 => 'titpar_klrhnid',
        5 => 'potensi_bulanan',
        6 => 'realisasi_bulanan',
        7 => 'persentase',
        8 => 'target_tahun',
        9 => 'realisasi_tahun',
        10 => 'persentase_tahun'
    ];

    $dt = new \App\Libraries\DataTable($builder, $columns, $sortable);
    $result = $dt->generate();

    return $this->response->setJSON($result);
}
    public function datatagihan50()
{
    $request = service('request');
    $bulan = $request->getPost('bulan');
    $tahun = $request->getPost('tahun');
    $reset = $request->getPost('reset');
    $kecamatan = $request->getPost('kecamatan');

    if ($reset == '1') {
        $bulan = null;
        $tahun = null;
    }

    if (empty($bulan)) $bulan = date('n');
    if (empty($tahun)) $tahun = date('Y');

    $bulan = (int)$bulan;
    $tahun = (int)$tahun;

    //new rule
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

    $weekdayCount = [
        'senin' => 0,
        'selasa' => 0,
        'rabu' => 0,
        'kamis' => 0,
        'jumat' => 0,
        'sabtu' => 0,
        'minggu' => 0,
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

    $casePotensi = "
    (
        CASE 
            WHEN p.bulanan IS NOT NULL AND p.bulanan > 0 
                THEN p.bulanan
            WHEN p.mingguan IS NOT NULL AND p.mingguan > 0 
                THEN p.mingguan * 4
            ELSE
                (IFNULL(p.senin,0) * {$weekdayCount['senin']} +
                 IFNULL(p.selasa,0) * {$weekdayCount['selasa']} +
                 IFNULL(p.rabu,0) * {$weekdayCount['rabu']} +
                 IFNULL(p.kamis,0) * {$weekdayCount['kamis']} +
                 IFNULL(p.jumat,0) * {$weekdayCount['jumat']} +
                 IFNULL(p.sabtu,0) * {$weekdayCount['sabtu']} +
                 IFNULL(p.minggu,0) * {$weekdayCount['minggu']})
        END
    )
    ";

    //new rule end

    // === CASE potensi bulanan ===
    // $casePotensi = "
    //     (
    //         CASE 
    //             WHEN p.bulanan IS NOT NULL AND p.bulanan > 0 
    //                 THEN p.bulanan
    //             WHEN p.mingguan IS NOT NULL AND p.mingguan > 0 
    //                 THEN p.mingguan * 4
    //             ELSE 
    //                 (IFNULL(p.senin,0) + IFNULL(p.selasa,0) + IFNULL(p.rabu,0) 
    //                  + IFNULL(p.kamis,0) + IFNULL(p.jumat,0)
    //                  + IFNULL(p.sabtu,0) + IFNULL(p.minggu,0)) * 4
    //         END
    //     )
    // ";

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

        // === potensi bulanan ===
        "{$casePotensi} AS potensi_bulanan",

        // === realisasi bulanan ===
        "(
            SELECT IFNULL(SUM(t1.transaksi_nominal), 0)
            FROM transaksi t1
            WHERE t1.transaksi_va = vo.va_owner_va
            AND MONTH(t1.transaksi_tanggal) = {$bulan}
            AND YEAR(t1.transaksi_tanggal) = {$tahun}
        ) AS realisasi_bulanan",

        // === persentase bulanan ===
        "(
            (
                SELECT IFNULL(SUM(t1.transaksi_nominal),0)
                FROM transaksi t1
                WHERE t1.transaksi_va = vo.va_owner_va
                AND MONTH(t1.transaksi_tanggal) = {$bulan}
                AND YEAR(t1.transaksi_tanggal) = {$tahun}
            ) / NULLIF({$casePotensi},0)
        ) * 100 AS persentase",

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
                AND t2.transaksi_tanggal >= '{$tahun}-01-01'
                AND t2.transaksi_tanggal < DATE_ADD('{$tahun}-01-01', INTERVAL {$bulan} MONTH)
            ) / NULLIF(({$casePotensi} * {$bulan}),0)
        ) * 100 AS persentase_tahun"       
        ]);

    // === JOINS ===
    $builder->join('potensi p', 'p.potensi_va = vo.va_owner_va', 'left');
    $builder->join('dishub_titpargrup g', 'g.titpargrup_id = vo.va_owner_titpargrupid');
    $builder->join('dishub_anggota a', 'a.anggota_id = g.titpargrup_anggotaid');
    $builder->join('dishub_titpar t', 't.titpar_id = g.titpargrup_titparid');

    // === KECAMATAN FILTER (MUST BE AFTER JOINS) ===
    if (!empty($kecamatan)) {
        $builder->where("LEFT(t.titpar_klrhnid, 6)", $kecamatan);
    }

    // === DATATABLE COLUMNS ===
    $columns = [
        'va_owner_va',
        'anggota_nama',
        'titpar_namatempat',
        'titpar_klrhnid'
    ];

    $sortable = [
        
        1 => 'va_owner_va',
        2 => 'anggota_nama',
        3 => 'titpar_namatempat',
        4 => 'titpar_klrhnid',
        5 => 'potensi_bulanan',
        6 => 'realisasi_bulanan',
        7 => 'persentase',
        8 => 'target_tahun',
        9 => 'realisasi_tahun',
        10 => 'persentase_tahun'
    ];

    // === RUN DATATABLE ===
    $dt = new \App\Libraries\DataTable($builder, $columns, $sortable);
    $result = $dt->generate();

    return $this->response->setJSON($result);
}


   public function datatagihan6()
{
    $request = service('request');
    $bulan = $request->getPost('bulan');
    $tahun = $request->getPost('tahun');
    $reset = $request->getPost('reset');
    $kecamatan = $request->getPost('kecamatan');

    if ($reset == '1') {
        $bulan = null;
        $tahun = null;
    }

    if (empty($bulan)) $bulan = date('n');
    if (empty($tahun)) $tahun = date('Y');

    $bulan = (int)$bulan;
    $tahun = (int)$tahun;

    // CASE expression reused for potensi and percentage
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

       
        // potensi
        "{$casePotensi} AS potensi_bulanan",

        // realisasi
        "(
            SELECT SUM(t.transaksi_nominal)
            FROM transaksi t
            WHERE t.transaksi_va = vo.va_owner_va
            AND MONTH(t.transaksi_tanggal) = {$bulan}
            AND YEAR(t.transaksi_tanggal) = {$tahun}
        ) AS realisasi_bulanan",

        // percentage
        "(
            (
                (
                    SELECT IFNULL(SUM(t.transaksi_nominal),0)
                    FROM transaksi t
                    WHERE t.transaksi_va = vo.va_owner_va
                    AND MONTH(t.transaksi_tanggal) = {$bulan}
                    AND YEAR(t.transaksi_tanggal) = {$tahun}
                ) / {$casePotensi}
            ) * 100
        ) AS persentase"
    ]);
     if (!empty($kecamatan)) {
                // Filter by first 6 digits of kelurahan ID
                $builder->where("LEFT(t.titpar_klrhnid, 6)", $kecamatan);
            }

    $builder->join('potensi p', 'p.potensi_va = vo.va_owner_va', 'left');
    $builder->join('dishub_titpargrup g','g.titpargrup_id = vo.va_owner_titpargrupid');
    $builder->join('dishub_anggota a','a.anggota_id = g.titpargrup_anggotaid');
    $builder->join('dishub_titpar t','t.titpar_id = g.titpargrup_titparid');

   $columns = [
    'va_owner_va',
    'anggota_nama',
    'titpar_namatempat',
    'titpar_klrhnid',
];

$sortable = [
    0 => 'va_owner_va',
    1 => 'anggota_nama',
    2 => 'titpar_namatempat',
    3 => 'titpar_klrhnid',
    4 => 'potensi_bulanan',   // computed alias
    5 => 'realisasi_bulanan', // computed alias
    6 => 'persentase'         // computed alias
];

    $dt = new \App\Libraries\DataTable($builder, $columns, $sortable);

    $result = $dt->generate();

    return $this->response->setJSON($result);
}



    public function datatagihan3(){

   $request = service('request');
    $bulan = $request->getPost('bulan');
    $tahun = $request->getPost('tahun');
    $reset = $request->getPost('reset');

    // reset filter → show all
    if ($reset == '1') {
        $bulan = null;
        $tahun = null;
    }

    // default → current month/year
    if (empty($bulan)) {
        $bulan = date('n');
    }
    if (empty($tahun)) {
        $tahun = date('Y');
    }

    $db = \Config\Database::connect();
    $builder = $db->table('va_owner');

    $builder->select("
        va_owner.va_owner_va,
        dishub_anggota.anggota_id,
        dishub_anggota.anggota_nama,
        dishub_titpar.titpar_namatempat,
        dishub_titpar.titpar_lokasi,
        dishub_titpar.titpar_klrhnid,
        potensi.potensi_id,
        CASE 
            WHEN tahunan IS NOT NULL AND tahunan > 0 THEN ROUND(tahunan / 12, 0)
            WHEN mingguan IS NOT NULL AND mingguan > 0 THEN mingguan * 4
            WHEN (COALESCE(senin,0)+COALESCE(selasa,0)+COALESCE(rabu,0)+
                  COALESCE(kamis,0)+COALESCE(jumat,0)+COALESCE(sabtu,0)+COALESCE(minggu,0)) > 0
                 THEN (COALESCE(senin,0)+COALESCE(selasa,0)+COALESCE(rabu,0)+
                       COALESCE(kamis,0)+COALESCE(jumat,0)+COALESCE(sabtu,0)+COALESCE(minggu,0)) * 4
            ELSE bulanan
        END as tagihanBulanan,
        COALESCE(SUM(transaksi.transaksi_nominal), 0) as total_setoran
    ", false);

    $builder->join('potensi','va_owner.va_owner_va = potensi.potensi_va','left');
    $builder->join('dishub_anggota','va_owner.va_owner_anggotaid = dishub_anggota.anggota_id','left');
    $builder->join('dishub_titpargrup','dishub_titpargrup.titpargrup_anggotaid = dishub_anggota.anggota_id','left');
    $builder->join('dishub_titpar','dishub_titpar.titpar_id = dishub_titpargrup.titpargrup_titparid','left');

    // join transaksi with/without filter
    if ($reset == '1') {
        $builder->join('transaksi','va_owner.va_owner_va = transaksi.transaksi_va','left');
    } else {
        $builder->join(
            'transaksi',
            "va_owner.va_owner_va = transaksi.transaksi_va 
             AND YEAR(transaksi.transaksi_tanggal) = {$tahun} 
             AND MONTH(transaksi.transaksi_tanggal) = {$bulan}",
            'left'
        );
    }

    $builder->groupBy('va_owner.va_owner_va');

    $columns = ['potensi_va','anggota_nama','titpar_namatempat'];
    $dt = new \App\Libraries\DataTable($builder, $columns);
    $result = $dt->generate();

    return $this->response->setJSON($result);
    }

    public function datatagihan2($field){
        $db = db_connect();
        
        $builder = $db->table('va_owner')
        ->select('va_owner_va, anggota_id, anggota_nama, titpar_namatempat, titpar_lokasi, 
                  senin, selasa, rabu, kamis, jumat, sabtu, minggu, mingguan, bulanan, tahunan,
                  potensi.potensi_id')
        ->select("
            CASE 
                WHEN tahunan IS NOT NULL AND tahunan > 0 
                    THEN ROUND(tahunan / 12, 0)
                WHEN mingguan IS NOT NULL AND mingguan > 0 
                    THEN mingguan * 4
                WHEN (COALESCE(senin,0) + COALESCE(selasa,0) + COALESCE(rabu,0) + 
                      COALESCE(kamis,0) + COALESCE(jumat,0) + COALESCE(sabtu,0) + COALESCE(minggu,0)) > 0
                    THEN (COALESCE(senin,0) + COALESCE(selasa,0) + COALESCE(rabu,0) + 
                          COALESCE(kamis,0) + COALESCE(jumat,0) + COALESCE(sabtu,0) + COALESCE(minggu,0)) * 4
                ELSE bulanan
            END as tagihanBulanan
        ", false)
        ->join('potensi','va_owner.va_owner_va = potensi.potensi_va','left')
        ->join('dishub_anggota','va_owner.va_owner_anggotaid = dishub_anggota.anggota_id')
        ->join('dishub_titpargrup','dishub_titpargrup.titpargrup_anggotaid = dishub_anggota.anggota_id')
        ->join('dishub_titpar','dishub_titpar.titpar_id = dishub_titpargrup.titpargrup_titparid')
        ->where("{$field} >", 0)    
        ->groupBy('potensi_id');
    
        // Columns to apply search on
        $columns = ['potensi_va','anggota_nama','titpar_namatempat'];
        // $columns =  [];
        $dt = new DataTable($builder, $columns);
        $result = $dt->generate();

        return $this->response->setJSON($result);

    }

    


}
