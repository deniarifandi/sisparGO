<?php 

namespace App\Controllers;

use App\Libraries\DataTable;
use DB;


class Tiket extends BaseController
{
    public $db;

    public function __construct()
    {
      $this->db = \Config\Database::connect();
  }

  public function index()
  {

    return view('tiket/index');
}

public function data(){

 $subTransaksi = $this->db->table('transaksi')
 ->select('transaksi_va, SUM(transaksi_nominal) as total_transaksi')
 ->groupBy('transaksi_va');

 $subTiket = $this->db->table('tiket')
 ->select('tiket_va, 
  SUM(tiket_motor) as total_motor,
  SUM(tiket_mobil) as total_mobil,
  SUM(tiket_truk) as total_truk,
  SUM(tiket_motor_in) as total_motor_in,
  SUM(tiket_mobil_in) as total_mobil_in,
  SUM(tiket_truk_in) as total_truk_in')
 ->groupBy('tiket_va');

 $builder = $this->db->table('va_owner')
 ->select('va_owner.va_owner_va, 
  va_owner.va_owner_nama, 
  COALESCE(transaksi.total_transaksi, 0) as total_transaksi,
  COALESCE(tiket.total_motor, 0) as tiket_motor,
  COALESCE(tiket.total_mobil, 0) as tiket_mobil,
  COALESCE(tiket.total_truk, 0) as tiket_truk,
  COALESCE(tiket.total_motor_in, 0) as tiket_motor_in,
  COALESCE(tiket.total_mobil_in, 0) as tiket_mobil_in,
  COALESCE(tiket.total_truk_in, 0) as tiket_truk_in,
  (
  COALESCE(transaksi.total_transaksi, 0) -
  (
  COALESCE(tiket.total_motor, 0) * 2000 +
  COALESCE(tiket.total_mobil, 0) * 3000 +
  COALESCE(tiket.total_truk, 0) * 5000 +
  COALESCE(tiket.total_motor_in, 0) * 3000 +
  COALESCE(tiket.total_mobil_in, 0) * 5000 +
  COALESCE(tiket.total_truk_in, 0) * 10000
  )
) as sisa_saldo')
 ->join("({$subTransaksi->getCompiledSelect()}) transaksi", 'transaksi.transaksi_va = va_owner.va_owner_va', 'left')
 ->join("({$subTiket->getCompiledSelect()}) tiket", 'tiket.tiket_va = va_owner.va_owner_va', 'left');


    $columns = ['va_owner_va', 'va_owner_nama', 'total_nominal']; // harus sesuai alias

    $dt = new DataTable($builder, $columns);
    $result = $dt->generate();

    return $this->response->setJSON($result);
}

 public function datahistori(){
        $db = db_connect();
        $builder = $db->table('tiket')->select('tiket.*, va_owner.va_owner_nama')
        ->join('va_owner','va_owner.va_owner_va = tiket.tiket_va','left')
        ->where('tiket_id >',1)
        ;

        $recordsTotal = (clone $builder)->countAllResults(false);

        $ticketType = strtolower(trim((string) $this->request->getPost('ticket_type')));
        $ticketNumber = trim((string) $this->request->getPost('ticket_number'));
        $ownerSearch = trim((string) $this->request->getPost('owner_search'));
        $rangesByType = [
            'motor' => [
                ['tiket_nomor_motor_awal', 'tiket_nomor_motor_akhir', 'tiket_motor'],
                ['tiket_nomor_motor_in_awal', 'tiket_nomor_motor_in_akhir', 'tiket_motor_in'],
            ],
            'mobil' => [
                ['tiket_nomor_mobil_awal', 'tiket_nomor_mobil_akhir', 'tiket_mobil'],
                ['tiket_nomor_mobil_in_awal', 'tiket_nomor_mobil_in_akhir', 'tiket_mobil_in'],
            ],
            'truk' => [
                ['tiket_nomor_truk_awal', 'tiket_nomor_truk_akhir', 'tiket_truk'],
                ['tiket_nomor_truk_in_awal', 'tiket_nomor_truk_in_akhir', 'tiket_truk_in'],
            ],
        ];

        $hasCustomFilter = false;

        if ($ownerSearch !== '') {
            $builder->groupStart()
                ->like('va_owner.va_owner_nama', $ownerSearch)
                ->orLike('tiket.tiket_va', $ownerSearch)
                ->groupEnd();
            $hasCustomFilter = true;
        }

        if ($ticketNumber !== '' && isset($rangesByType[$ticketType])) {
            $rangeColumns = $rangesByType[$ticketType];
            $serialParts = preg_match('/^(.*?)(\d+)$/', $ticketNumber, $match) ? $match : null;

            $builder->groupStart();
            foreach ($rangeColumns as [$startColumn, $endColumn, $quantityColumn]) {
                // Tetap izinkan pencarian nomor awal/akhir secara langsung.
                $builder->orLike($startColumn, $ticketNumber)
                    ->orLike($endColumn, $ticketNumber);

                if ($serialParts === null) {
                    continue;
                }

                $prefix = $serialParts[1];
                $numericValue = (int) $serialParts[2];
                $startNumber = "CAST(SUBSTRING($startColumn, " . (strlen($prefix) + 1) . ") AS UNSIGNED)";
                $endNumber = "CAST(SUBSTRING($endColumn, " . (strlen($prefix) + 1) . ") AS UNSIGNED)";
                $calculatedEnd = "($startNumber + CAST($quantityColumn AS UNSIGNED) - 1)";
                $safeEnd = "LEAST($endNumber, $calculatedEnd)";
                $escapedPrefix = $db->escape($prefix);
                $prefixLength = strlen($prefix);
                $rangeCondition = "(" .
                    "LEFT($startColumn, $prefixLength) = $escapedPrefix" .
                    " AND LEFT($endColumn, $prefixLength) = $escapedPrefix" .
                    " AND $quantityColumn > 0" .
                    " AND $startNumber <= $numericValue" .
                    " AND $safeEnd >= $numericValue" .
                    ")";
                $builder->orWhere($rangeCondition, null, false);
            }
            $builder->groupEnd();
            $hasCustomFilter = true;
        }

        $sortable = [
            0 => 'tiket_id',
            1 => 'tiket_va',
            2 => 'va_owner_nama',
            3 => 'tiket_motor',
            4 => 'tiket_mobil',
            5 => 'tiket_truk',
            6 => 'tiket_motor_in',
            7 => 'tiket_mobil_in',
            8 => 'tiket_truk_in',
            9 => 'tiket_tanggal',
        ];
        $dt = new DataTable($builder, [], $sortable);
        $result = $dt->generate($recordsTotal, $hasCustomFilter);

        return $this->response->setJSON($result);

    }

   public function print($id)
    {


        $db = db_connect();
        $builder = $db->table('tiket')
                      ->select('tiket.*, va_owner.*, titpar_namatempat, titpar_lokasi')
                      ->join('va_owner','va_owner.va_owner_va = tiket.tiket_va','left')
                      ->join('dishub_anggota','va_owner.va_owner_anggotaid = dishub_anggota.anggota_id','left')
                      ->join('dishub_titpargrup','dishub_titpargrup.titpargrup_anggotaid = dishub_anggota.anggota_id','left')
                      ->join('dishub_titpar','dishub_titpar.titpar_id = dishub_titpargrup.titpargrup_titparid','left')
                      ->where('tiket.tiket_id', $id);

        $tiket = $builder->get()->getRow(); // ambil 1 row

        if (!$tiket) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Tiket tidak ditemukan.");
        }

        return view('tiket/print', ['tiket' => $tiket]);
    }

public function pesan($va)
{
    // ---------------------------
    // 1. SUBQUERY: TOTAL TRANSAKSI
    // ---------------------------
    $subTransaksi = $this->db->table('transaksi')
        ->select('transaksi_va, SUM(transaksi_nominal) as total_transaksi')
        ->groupBy('transaksi_va');


    // ---------------------------
    // 2. SUBQUERY: TOTAL TIKET (ALL HISTORICAL)
    // ---------------------------
    $subTiket = $this->db->table('tiket')
        ->select('tiket_va,
            SUM(tiket_motor)     as total_motor,
            SUM(tiket_mobil)     as total_mobil,
            SUM(tiket_truk)      as total_truk,
            SUM(tiket_motor_in)  as total_motor_in,
            SUM(tiket_mobil_in)  as total_mobil_in,
            SUM(tiket_truk_in)   as total_truk_in
        ')
        ->groupBy('tiket_va');


    // ---------------------------
    // 3. MAIN QUERY: VA OWNER + JOIN SUBQUERIES
    // ---------------------------
    $builder = $this->db->table('va_owner')
        ->select("
            va_owner.va_owner_va,
            va_owner.va_owner_nama,

            COALESCE(transaksi.total_transaksi, 0) AS total_transaksi,
            COALESCE(tiket.total_motor,      0) AS tiket_motor,
            COALESCE(tiket.total_mobil,      0) AS tiket_mobil,
            COALESCE(tiket.total_truk,       0) AS tiket_truk,
            COALESCE(tiket.total_motor_in,   0) AS tiket_motor_in,
            COALESCE(tiket.total_mobil_in,   0) AS tiket_mobil_in,
            COALESCE(tiket.total_truk_in,    0) AS tiket_truk_in,

            (
                COALESCE(transaksi.total_transaksi, 0) -
                (
                    COALESCE(tiket.total_motor,     0) * 2000 +
                    COALESCE(tiket.total_mobil,     0) * 3000 +
                    COALESCE(tiket.total_truk,      0) * 5000 +
                    COALESCE(tiket.total_motor_in,  0) * 3000 +
                    COALESCE(tiket.total_mobil_in,  0) * 5000 +
                    COALESCE(tiket.total_truk_in,   0) * 10000
                )
            ) AS sisa_saldo
        ")
        ->join("(" . $subTransaksi->getCompiledSelect() . ") AS transaksi", "transaksi.transaksi_va = va_owner.va_owner_va", "left")
        ->join("(" . $subTiket->getCompiledSelect() . ") AS tiket", "tiket.tiket_va = va_owner.va_owner_va", "left")
        ->where('va_owner_va', $va);

    $result = $builder->get()->getResult();


    // ---------------------------
    // 4. pesanan baru ALWAYS means:
    // tikatData = NULL
    // original_total = 0
    // ---------------------------
    // Token sekali pakai untuk mencegah satu form pemesanan tersimpan dua kali.
    $pesanToken = bin2hex(random_bytes(32));
    session()->set('tiket_pesan_token', $pesanToken);

    return view('tiket/pesan', [
        'data'           => $result,
        'tiket'          => null,   // <-- force new mode
        'original_total' => 0,      // <-- no previous value
        'pesan_token'    => $pesanToken
    ]);
}



public function store()
{
        // Satu token hanya boleh dipakai sekali. Ini melindungi dari dua
        // request POST yang terkirim akibat double-click atau retry browser.
        $token = (string) $this->request->getPost('pesan_token');
        $sessionToken = (string) session()->get('tiket_pesan_token');

        if ($token === '' || $sessionToken === '' || ! hash_equals($sessionToken, $token)) {
            session()->setFlashdata('error', 'Pesanan sudah diproses atau formulir sudah kedaluwarsa.');
            return redirect()->to('/tiket/histori');
        }

        // Hapus sebelum insert agar token tidak dapat digunakan ulang.
        session()->remove('tiket_pesan_token');


        $db = \Config\Database::connect();
        $builder = $db->table('tiket');

        // Ambil nilai dari form
        $va = $this->request->getPost('va_owner_va');

        $data = [
            'tiket_va'       => $va,
            'tiket_motor'    => $this->request->getPost('tiket_motor'),
            'tiket_mobil'    => $this->request->getPost('tiket_mobil'),
            'tiket_truk'     => $this->request->getPost('tiket_truk'),
            'tiket_motor_in' => $this->request->getPost('tiket_motor_in'),
            'tiket_mobil_in' => $this->request->getPost('tiket_mobil_in'),
            'tiket_truk_in'  => $this->request->getPost('tiket_truk_in'),
            'tiket_nomor_motor_awal' => $this->request->getPost('tiket_nomor_motor_awal'),
            'tiket_nomor_motor_akhir'=> $this->request->getPost('tiket_nomor_motor_akhir'),
            'tiket_nomor_mobil_awal' => $this->request->getPost('tiket_nomor_mobil_awal'),
            'tiket_nomor_mobil_akhir'=> $this->request->getPost('tiket_nomor_mobil_akhir'),
            'tiket_nomor_truk_awal' => $this->request->getPost('tiket_nomor_truk_awal'),
            'tiket_nomor_truk_akhir'=> $this->request->getPost('tiket_nomor_truk_akhir'),
            'tiket_nomor_motor_in_awal' => $this->request->getPost('tiket_nomor_motor_in_awal'),
            'tiket_nomor_motor_in_akhir'=> $this->request->getPost('tiket_nomor_motor_in_akhir'),
            'tiket_nomor_mobil_in_awal' => $this->request->getPost('tiket_nomor_mobil_in_awal'),
            'tiket_nomor_mobil_in_akhir'=> $this->request->getPost('tiket_nomor_mobil_in_akhir'),
            'tiket_nomor_truk_in_awal' => $this->request->getPost('tiket_nomor_truk_in_awal'),
            'tiket_nomor_truk_in_akhir'=> $this->request->getPost('tiket_nomor_truk_in_akhir'),
        ];

        if ($builder->insert($data)) {

        session()->setFlashdata('success', 'Data berhasil disimpan!');
        } else {
            // Set flashdata error
            session()->setFlashdata('error', 'Gagal menyimpan data.');
        }

  
        return redirect()->to('/tiket/histori');

    }

    public function histori(){
        return view('tiket/histori');
    }

   public function edit($id)
{
    // DB
    $db = \Config\Database::connect();

    // 1. Load tiket row (the one to edit)
    $tiket = $db->table('tiket')
        ->where('tiket_id', $id)
        ->get()
        ->getRowArray();

    if (!$tiket) {
        return redirect()->to(base_url('tiket'))
            ->with('error', 'Data tiket tidak ditemukan.');
    }

    $va = $tiket['tiket_va'];

    // ---------------------------
    // 2. Calculate ORIGINAL TOTAL
    // ---------------------------
    $original_total =
        ($tiket['tiket_motor']     * 2000) +
        ($tiket['tiket_mobil']     * 3000) +
        ($tiket['tiket_truk']      * 5000) +
        ($tiket['tiket_motor_in']  * 3000) +
        ($tiket['tiket_mobil_in']  * 5000) +
        ($tiket['tiket_truk_in']   * 10000);

    // ---------------------------
    // 3. Calculate SISA SALDO (same as pesan())
    // ---------------------------
    $saldoQuery = $db->query("
        SELECT 
            (
                COALESCE(trx.total_transaksi, 0) -
                COALESCE(tkt.total_pemakaian, 0)
            ) AS sisa_saldo,
            vo.va_owner_nama,
            vo.va_owner_va
        FROM va_owner vo
        LEFT JOIN (
            SELECT transaksi_va, SUM(transaksi_nominal) AS total_transaksi
            FROM transaksi
            GROUP BY transaksi_va
        ) trx ON trx.transaksi_va = vo.va_owner_va
        LEFT JOIN (
            SELECT tiket_va,
                SUM(
                    tiket_motor     * 2000 +
                    tiket_mobil     * 3000 +
                    tiket_truk      * 5000 +
                    tiket_motor_in  * 3000 +
                    tiket_mobil_in  * 5000 +
                    tiket_truk_in   * 10000
                ) AS total_pemakaian
            FROM tiket
            GROUP BY tiket_va
        ) tkt ON tkt.tiket_va = vo.va_owner_va
        WHERE vo.va_owner_va = '$va'
    ");

    $saldoRow = $saldoQuery->getRow();

    if (!$saldoRow) {
        return redirect()->back()->with('error', 'VA tidak ditemukan.');
    }

    // This is the data structure expected by view
    $dataRow = (object)[
        'va_owner_va'  => $saldoRow->va_owner_va,
        'va_owner_nama'=> $saldoRow->va_owner_nama,
        'sisa_saldo'   => (int)$saldoRow->sisa_saldo
    ];

    // ---------------------------
    // 4. RETURN VIEW WITH DATA
    // ---------------------------
    return view('tiket/pesan', [
        'data'           => [$dataRow],   // same structure as pesan()
        'tiket'          => $tiket,       // so form is pre-filled
        'original_total' => $original_total
    ]);
}

public function update($id)
{
    // 1. Get existing tiket row (the one being edited)
    $old = $this->db->table('tiket')
        ->where('tiket_id', $id)
        ->get()
        ->getRowArray();

    if (!$old) {
        return redirect()->back()->with('error', 'Data tiket tidak ditemukan.');
    }

    $va = $old['tiket_va']; // needed to check saldo

    // 2. Compute ORIGINAL TOTAL (before edit)
    $original_total =
        ($old['tiket_motor']     * 2000) +
        ($old['tiket_mobil']     * 3000) +
        ($old['tiket_truk']      * 5000) +
        ($old['tiket_motor_in']  * 3000) +
        ($old['tiket_mobil_in']  * 5000) +
        ($old['tiket_truk_in']   * 10000);

    // 3. Get current sisa saldo
    $saldoQuery = $this->db->query("
        SELECT 
            (
                COALESCE(trx.total_transaksi, 0) -
                COALESCE(tkt.total_pemakaian, 0)
            ) AS sisa_saldo
        FROM va_owner vo
        LEFT JOIN (
            SELECT transaksi_va, SUM(transaksi_nominal) AS total_transaksi
            FROM transaksi
            GROUP BY transaksi_va
        ) trx ON trx.transaksi_va = vo.va_owner_va
        LEFT JOIN (
            SELECT tiket_va,
                SUM(tiket_motor     * 2000 +
                    tiket_mobil     * 3000 +
                    tiket_truk      * 5000 +
                    tiket_motor_in  * 3000 +
                    tiket_mobil_in  * 5000 +
                    tiket_truk_in   * 10000) AS total_pemakaian
            FROM tiket
            GROUP BY tiket_va
        ) tkt ON tkt.tiket_va = vo.va_owner_va
        WHERE vo.va_owner_va = '$va'
    ");

    $saldo = (int) ($saldoQuery->getRow()->sisa_saldo ?? 0);

    // 4. Get NEW values from form
    $req = $this->request;

    $new = [
        'tiket_motor'     => (int)$req->getPost('tiket_motor'),
        'tiket_mobil'     => (int)$req->getPost('tiket_mobil'),
        'tiket_truk'      => (int)$req->getPost('tiket_truk'),

        'tiket_motor_in'  => (int)$req->getPost('tiket_motor_in'),
        'tiket_mobil_in'  => (int)$req->getPost('tiket_mobil_in'),
        'tiket_truk_in'   => (int)$req->getPost('tiket_truk_in'),

        'tiket_nomor_motor_awal' => $req->getPost('tiket_nomor_motor_awal'),
        'tiket_nomor_motor_akhir'=> $req->getPost('tiket_nomor_motor_akhir'),

        'tiket_nomor_mobil_awal' => $req->getPost('tiket_nomor_mobil_awal'),
        'tiket_nomor_mobil_akhir'=> $req->getPost('tiket_nomor_mobil_akhir'),

        'tiket_nomor_truk_awal'  => $req->getPost('tiket_nomor_truk_awal'),
        'tiket_nomor_truk_akhir' => $req->getPost('tiket_nomor_truk_akhir'),

        'tiket_va' => $va
    ];

    // 5. Compute NEW TOTAL
    $new_total =
        ($new['tiket_motor']     * 2000) +
        ($new['tiket_mobil']     * 3000) +
        ($new['tiket_truk']      * 5000) +
        ($new['tiket_motor_in']  * 3000) +
        ($new['tiket_mobil_in']  * 5000) +
        ($new['tiket_truk_in']   * 10000);

    // 6. Compute DIFFERENCE
    $difference = $new_total - $original_total;

    // 7. VALIDATION — Option A (only difference must be ≤ saldo)
    if ($difference > $saldo) {
        return redirect()->back()->with('error',
            "Penambahan biaya sebesar Rp " . number_format($difference, 0, ',', '.') .
            " melebihi sisa saldo Rp " . number_format($saldo, 0, ',', '.')
        );
    }

    // 8. SAVE UPDATE
    $this->db->table('tiket')->where('tiket_id', $id)->update($new);

    // 9. SUCCESS
    return redirect()->to(base_url('tiket/histori'))->with('success', 'Tiket berhasil diupdate.');
}





}
