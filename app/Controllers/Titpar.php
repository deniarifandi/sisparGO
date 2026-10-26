<?php 

namespace App\Controllers;

use App\Libraries\DataTable;

use DB;
use Dompdf\Dompdf;
use Dompdf\Options;


class Titpar extends BaseController
{
    public $db;

    public function __construct()
    {
      $this->db = \Config\Database::connect();
      helper('qr');
    }

    public function index(){
         return view('titpar/index', ['kelurahan' => $this->bogorKelurahan()]);
    }

    private function bogorKelurahan(): array
    {
        return $this->db->table('kelurahan')
            ->select('id,kecamatan_id,kecamatan_nama,kelurahan_nama')
            ->where('provinsi', 'Jawa Barat')->where('kabupaten_kota', 'Kota Bogor')
            ->orderBy('kecamatan_nama')->orderBy('kelurahan_nama')->get()->getResultArray();
    }

    private function validBogorKelurahan(): bool
    {
        return $this->db->table('kelurahan')
            ->where('id', (string) $this->request->getPost('titpar_klrhnid'))
            ->where('kecamatan_id', (string) $this->request->getPost('kecamatan'))
            ->where('provinsi', 'Jawa Barat')->where('kabupaten_kota', 'Kota Bogor')
            ->countAllResults() === 1;
    }
public function data()
{
    $db = db_connect();

    $builder = $db->table('dishub_titpar')
        ->select('dishub_titpar.*, users.nama, kelurahan.kecamatan_id, kelurahan.kecamatan_nama, kelurahan.kelurahan_nama, kelurahan.kabupaten_kota, kelurahan.provinsi')
        ->join('users','users.user_id = dishub_titpar.pengawas_id','left')
        ->join('kelurahan','kelurahan.id = dishub_titpar.titpar_klrhnid','left')
        ->where('titpar_status !=', 1);

    $columns = ['titpar_id'];

    $dt = new DataTable($builder, $columns);
    $result = $dt->generate();

    // Ambil semua Titpar ID dari hasil DataTable
    $titparIds = array_column($result['data'], 'titpar_id');

    if (!empty($titparIds)) {

        // Ambil semua anggota yang tergabung dalam Titpar
        $anggotaRows = $db->table('dishub_titpargrup tg')
            ->select('
                tg.titpargrup_titparid,
                tg.titpargrup_anggotaid,
                tg.titpargrup_staanggota,
                a.anggota_nama,
                a.anggota_id
            ')
            ->join(
                'dishub_anggota a',
                'a.anggota_id = tg.titpargrup_anggotaid',
                'left'
            )
            ->whereIn(
                'tg.titpargrup_titparid',
                $titparIds
            )
            ->orderBy(
                'a.anggota_nama',
                'ASC'
            )
            ->get()
            ->getResultArray();

        $masaRows = $db->table('dishub_titpargrup')
            ->select('titpargrup_titparid, titpargrup_masaberlakustart, titpargrup_masaberlakuend')
            ->whereIn('titpargrup_titparid', $titparIds)
            ->orderBy('titpargrup_id', 'DESC')
            ->get()->getResultArray();
        $masaMap = [];
        foreach ($masaRows as $masaRow) {
            $masaMap[$masaRow['titpargrup_titparid']] ??= $masaRow;
        }

        // Kelompokkan berdasarkan Titpar
        // Kelompokkan berdasarkan Titpar
$koordinatorMap = [];
$anggotaMap = [];

foreach ($anggotaRows as $member) {

    $titparId = $member['titpargrup_titparid'];

    $memberData = [
        'id'         => $member['titpargrup_anggotaid'],
        'anggota_id' => $member['anggota_id'],
        'nama'       => $member['anggota_nama'],
        'status'     => $member['titpargrup_staanggota'],
    ];

    if ($member['titpargrup_staanggota'] === 'KOORDINATOR') {

        $koordinatorMap[$titparId][] = $memberData;

    } elseif ($member['titpargrup_staanggota'] === 'ANGGOTA') {

        $anggotaMap[$titparId][] = $memberData;
    }
}


// Masukkan data ke setiap Titpar
foreach ($result['data'] as &$row) {

    $titparId = $row['titpar_id'];

    $row['koordinator'] = $koordinatorMap[$titparId] ?? [];

    $row['anggota'] = $anggotaMap[$titparId] ?? [];
    $row['masa_berlaku_start'] = $masaMap[$titparId]['titpargrup_masaberlakustart'] ?? null;
    $row['masa_berlaku_end'] = $masaMap[$titparId]['titpargrup_masaberlakuend'] ?? null;
}

unset($row);
    }

    return $this->response->setJSON($result);
}

    public function datafixold()
{
    $db = db_connect();

    $builder = $db->table('dishub_titpar')
        ->select('dishub_titpar.*')
        ->where('titpar_status !=', 1);

    $columns = ['titpar_id'];

    $dt = new DataTable($builder, $columns);
    $result = $dt->generate();

    // Ambil semua Titpar ID dari hasil DataTable
    $titparIds = array_column($result['data'], 'titpar_id');

    if (!empty($titparIds)) {

        $koordinatorRows = $db->table('dishub_titpargrup tg')
            ->select('
                tg.titpargrup_titparid,
                tg.titpargrup_anggotaid,
                a.anggota_nama,
                a.anggota_id
            ')
            ->join(
                'dishub_anggota a',
                'a.anggota_id = tg.titpargrup_anggotaid',
                'left'
            )
            ->whereIn(
                'tg.titpargrup_titparid',
                $titparIds
            )
            ->where(
                'tg.titpargrup_staanggota',
                'KOORDINATOR'
            )
            ->orderBy(
                'a.anggota_nama',
                'ASC'
            )
            ->get()
            ->getResultArray();

        // Kelompokkan berdasarkan titpar
        $koordinatorMap = [];

        foreach ($koordinatorRows as $row) {

            $titparId = $row['titpargrup_titparid'];

            $koordinatorMap[$titparId][] = [
                'id' => $row['titpargrup_anggotaid'],
                'nama' => $row['anggota_nama'],
                'anggota_id' => $row['anggota_id']
                    ?: $row['anggota_id'],
            ];
        }

        // Masukkan ke setiap row Titpar
        foreach ($result['data'] as &$row) {

            $titparId = $row['titpar_id'];

            $row['koordinator'] =
                $koordinatorMap[$titparId] ?? [];
        }

        unset($row);
    }

    return $this->response->setJSON($result);
}   

public function create()
{
    $db = \Config\Database::connect();

    $users = $db->table('users')
        ->select('user_id, nama')
        ->orderBy('nama', 'ASC')
        ->get()
        ->getResultArray();

    $kelurahan = $db->table('kelurahan')
        ->select('id,kecamatan_id,kecamatan_nama,kelurahan_nama')
        ->where('provinsi', 'Jawa Barat')
        ->where('kabupaten_kota', 'Kota Bogor')
        ->orderBy('kecamatan_nama')->orderBy('kelurahan_nama')->get()->getResultArray();
    $jenisTitikParkir = $db->table('retritype')
        ->select('retritype_id, retrytipe_nama')
        ->orderBy('retritype_id')->get()->getResultArray();
    return view('titpar/form', [
        'users'  => $users,
        'titpar' => [],
        'kelurahan' => $kelurahan,
        'jenisTitikParkir' => $jenisTitikParkir
    ]);
}

    // public function create(){
    //      return view('titpar/form');
    // }

   public function store()
{
    $db = \Config\Database::connect();
    if (!$this->validBogorKelurahan()) {
        return redirect()->back()->withInput()->with('error', 'Pilih kecamatan dan kelurahan Kota Bogor yang sesuai.');
    }
    $jenisId = $this->request->getPost('titpar_retritypeid');
    if (!$jenisId || !$db->table('retritype')->where('retritype_id', $jenisId)->countAllResults()) {
        return redirect()->back()->withInput()->with('error', 'Pilih jenis titik parkir yang valid.');
    }

    // Ambil nilai terakhir
    $last = $db->table('dishub_titpar')
               ->selectMax('titpar_newnoreg')
               ->get()
               ->getRow();

    $lastNoreg = $last->titpar_newnoreg ?? '000000';
    $newNoreg  = str_pad(((int)$lastNoreg) + 1, 6, '0', STR_PAD_LEFT);

    // INSERT
    $db->table('dishub_titpar')->insert([
        'titpar_newnoreg'   => $newNoreg,
        'titpar_namatempat' => $this->request->getPost('titpar_namatempat'),
        'titpar_retritypeid' => $jenisId,
        'zona_id'       => (int) $this->request->getPost('kecamatan'),
        'titpar_lokasi'     => $this->request->getPost('titpar_lokasi'),
        'titpar_klrhnid'    => $this->request->getPost('titpar_klrhnid'),
        'titpar_latitude'   => $this->request->getPost('titpar_latitude'),
        'titpar_longitude'  => $this->request->getPost('titpar_longitude'),
        'titpar_jenis_kendaraan' => json_encode($this->request->getPost('titpar_jenis_kendaraan') ?: [], JSON_UNESCAPED_UNICODE),
        'titpar_status'     => $this->request->getPost('titpar_status') ?: 3,
    ]);

    // ambil ID baru
    $titpar_id = $db->insertID();

    // cek apakah dari register
    $register_id = $this->request->getPost('register_id');

    if ($register_id) {

    // get anggota
    $register = $db->table('register')
        ->select('id, register_anggotaid, register_titpargrupid')
        ->where('id', $register_id)
        ->get()
        ->getRow();

    $anggota_id    = $register->register_anggotaid;
    $titpargrup_id = $register->register_titpargrupid;

    if ($titpargrup_id) {
        // update existing pivot
        $db->table('dishub_titpargrup')
            ->where('titpargrup_id', $titpargrup_id)
            ->update([
                'titpargrup_titparid'   => $titpar_id,
                'titpargrup_anggotaid'  => $anggota_id,
                'titpargrup_staanggota' => 'KOORDINATOR',
            ]);
    } else {
        // insert new pivot
        $db->table('dishub_titpargrup')->insert([
            'titpargrup_titparid'   => $titpar_id,
            'titpargrup_anggotaid'  => $anggota_id,
            'titpargrup_staanggota' => 'KOORDINATOR',
        ]);

        $titpargrup_id = $db->insertID();
    }

    // update register (IMPORTANT: include titpargrupid)
    $db->table('register')
        ->where('id', $register_id)
        ->update([
            'register_titparid'     => $titpar_id,
            'register_titpargrupid' => $titpargrup_id
        ]);

    return redirect()->to(base_url('titpar/manage/' . $titpar_id))
                     ->with('success', 'Titpar berhasil dibuat & dipilih.');
}

    // default (normal flow)
    return redirect()->to(base_url('titpar/manage/' . $titpar_id))
                     ->with('success', 'Data titpar berhasil ditambahkan.');
}

    public function edit($id)
{
    $db = \Config\Database::connect();

    $titpar = $db->table('dishub_titpar')
        ->where('titpar_id', $id)
        ->get()
        ->getRowArray();

    $users = $db->table('users')
        ->select('user_id, nama')
        ->orderBy('nama', 'ASC')
        ->get()
        ->getResultArray();

    $kelurahan = $db->table('kelurahan')
        ->select('id,kecamatan_id,kecamatan_nama,kelurahan_nama')
        ->where('provinsi', 'Jawa Barat')
        ->where('kabupaten_kota', 'Kota Bogor')
        ->orderBy('kecamatan_nama')->orderBy('kelurahan_nama')->get()->getResultArray();
    $jenisTitikParkir = $db->table('retritype')
        ->select('retritype_id, retrytipe_nama')
        ->orderBy('retritype_id')->get()->getResultArray();
    return view('titpar/form', [
        'users'  => $users,
        'titpar' => $titpar,
        'kelurahan' => $kelurahan,
        'jenisTitikParkir' => $jenisTitikParkir
    ]);
}

public function update($id)
{
    $db = \Config\Database::connect();
    if (!$this->validBogorKelurahan()) {
        return redirect()->back()->withInput()->with('error', 'Pilih kecamatan dan kelurahan Kota Bogor yang sesuai.');
    }
    $jenisId = $this->request->getPost('titpar_retritypeid');
    if (!$jenisId || !$db->table('retritype')->where('retritype_id', $jenisId)->countAllResults()) {
        return redirect()->back()->withInput()->with('error', 'Pilih jenis titik parkir yang valid.');
    }
    $lokasi = (string) $this->request->getPost('titpar_lokasi');

    if (mb_strlen($lokasi) > 75) {
        return redirect()->back()->withInput()->with('error', 'Alamat maksimal 75 karakter.');
    }

    $update = [
        'zona_id' => (int) $this->request->getPost('kecamatan'),
        'titpar_namatempat' => $this->request->getPost('titpar_namatempat'),
        'titpar_retritypeid' => $jenisId,
        'titpar_lokasi'     => $lokasi,
        'titpar_klrhnid'    => $this->request->getPost('titpar_klrhnid'),
        'titpar_latitude'   => $this->request->getPost('titpar_latitude'),
        'titpar_longitude'  => $this->request->getPost('titpar_longitude'),
        'titpar_jenis_kendaraan' => json_encode($this->request->getPost('titpar_jenis_kendaraan') ?: [], JSON_UNESCAPED_UNICODE),
        'titpar_status'     => $this->request->getPost('titpar_status'),
    ];

    $denah = $this->request->getFile('titpar_denah');
    if ($denah && $denah->isValid() && !$denah->hasMoved()) {
        if (!in_array($denah->getMimeType(), ['image/jpeg', 'image/png', 'image/webp'], true) || $denah->getSizeByUnit('mb') > 5) {
            return redirect()->back()->withInput()->with('error', 'Denah harus berupa JPG, PNG, atau WEBP dengan ukuran maksimal 5 MB.');
        }
        $folder = FCPATH . 'uploads/denah';
        if (!is_dir($folder)) mkdir($folder, 0775, true);
        $filename = $denah->getRandomName();
        $denah->move($folder, $filename);
        $update['titpar_denah'] = $filename;
    }

    $db->table('dishub_titpar')
        ->where('titpar_id', $id)
        ->update($update);

    return redirect()
        ->to(base_url('titpar/manage/' . $id))
        ->with('success', 'Data titpar berhasil diupdate.');
}

      public function delete($id)
     {
         $db = \Config\Database::connect();

         $db->table('dishub_titpar')
            ->where('titpar_id', $id)
            ->update(['titpar_status' => 2]); // soft delete

         return redirect()->to(base_url('titpar'))->with('success', 'Data anggota berhasil dinonaktifkan.');
     }

      public function undelete($id)
     {
         $db = \Config\Database::connect();

         $db->table('dishub_titpar')
            ->where('titpar_id', $id)
            ->update(['titpar_status' => 3]); // soft undelete

         return redirect()->to(base_url('titpar'))->with('success', 'Data anggota berhasil di-aktifkan.');
     }


    public function detailTitparApp($anggota_id){
    $db = \Config\Database::connect();

    // Subquery to filter titpar by anggota
    $subquery = $db->table('dishub_titpargrup')
                   ->select('titpargrup_titparid')
                   ->where('titpargrup_anggotaid', $anggota_id)
                   ->getCompiledSelect();

    // Main query
    $builder = $db->table('dishub_titpar');
    $builder->select('
        dishub_titpar.titpar_id, 
        dishub_titpar.titpar_namatempat,
        dishub_titpar.titpar_lokasi,
        dishub_titpar.titpar_longitude,
        dishub_titpar.titpar_latitude,
        dishub_titpar.titpar_alihfungsi,
        dishub_anggota.anggota_nama,
        dishub_anggota.anggota_id,
        dishub_titpargrup.titpargrup_staanggota,
        va_owner.va_owner_va,
        va_owner.va_owner_hp,
        dishub_titpargrup.titpargrup_masaberlakuend
    ');
    $builder->join('dishub_titpargrup','dishub_titpargrup.titpargrup_titparid = dishub_titpar.titpar_id','left');
    $builder->join('dishub_anggota','dishub_anggota.anggota_id = dishub_titpargrup.titpargrup_anggotaid','left');
    $builder->join('kelurahan','kelurahan.id = dishub_titpar.titpar_klrhnid');
    $builder->join('retritype','retritype.retritype_id = dishub_titpar.titpar_retritypeid');
    $builder->join('va_owner','va_owner.va_owner_titpargrupid = dishub_titpargrup.titpargrup_id','left');
    
    $builder->where("dishub_titpar.titpar_id IN ($subquery)", NULL, FALSE);

    $query = $builder->get();
    $rows = $query->getResultArray();

    // Transform flat result into nested JSON
    $data = [];
    foreach ($rows as $row) {
        $id = $row['titpar_id'];

        // If this titpar_id is not added yet, create it
        if (!isset($data[$id])) {
            $data[$id] = [
                'titpar_id' => $row['titpar_id'],
                'titpar_namatempat' => $row['titpar_namatempat'],
                'titpar_lokasi' => $row['titpar_lokasi'],
                'titpar_longitude' => $row['titpar_longitude'],
                'titpar_latitude' => $row['titpar_latitude'],
                'titpar_alihfungsi' => $row['titpar_alihfungsi'],
                'anggota' => []
            ];
        }

        // Add anggota info
        if ($row['anggota_nama']) { // check to avoid null
            $data[$id]['anggota'][] = [
                'anggota_nama' => $row['anggota_nama'],
                'anggota_id' => $row['anggota_id'],
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

public function uploadPenunjukan($titparId)
{
    $titpar = $this->db->table('dishub_titpar')->where('titpar_id', $titparId)->get()->getRowArray();
    if (!$titpar) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    $destination = base_url('titpar/manage/' . $titparId);
    if (!$this->validate([
        'surat_penunjukan' => [
            'label' => 'Surat penunjukan',
            'rules' => 'uploaded[surat_penunjukan]|max_size[surat_penunjukan,5120]|ext_in[surat_penunjukan,pdf,jpg,jpeg,png]|mime_in[surat_penunjukan,application/pdf,image/jpeg,image/png]',
        ],
    ])) {
        return redirect()->to($destination)->with('error', 'Pilih berkas surat penunjukan berupa PDF, JPG, atau PNG dengan ukuran maksimal 5 MB.');
    }

    $file = $this->request->getFile('surat_penunjukan');
    $folder = WRITEPATH . 'uploads/surat_penunjukan';
    $extensions = ['application/pdf' => 'pdf', 'image/jpeg' => 'jpg', 'image/png' => 'png'];
    $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$file->getMimeType()];
    try {
        $file->move($folder, $filename);
        $saved = $this->db->table('dishub_titpar')->where('titpar_id', $titparId)->update([
            'titpar_surat_penunjukan' => $filename,
        ]);
        if (!$saved) {
            throw new \RuntimeException('Gagal menyimpan surat penunjukan.');
        }
    } catch (\Throwable $e) {
        if (is_file($folder . '/' . $filename)) {
            unlink($folder . '/' . $filename);
        }
        log_message('error', 'Upload surat penunjukan: {message}', ['message' => $e->getMessage()]);
        return redirect()->to($destination)->with('error', 'Surat penunjukan gagal disimpan. Silakan coba lagi.');
    }

    return redirect()->to($destination)->with('success', 'Surat penunjukan bertanda tangan berhasil diunggah.');
}

public function suratPenunjukan($titparId)
{
    $titpar = $this->db->table('dishub_titpar')->where('titpar_id', $titparId)->get()->getRowArray();
    $filename = basename($titpar['titpar_surat_penunjukan'] ?? '');
    $path = WRITEPATH . 'uploads/surat_penunjukan/' . $filename;
    if ($filename === '' || !is_file($path)) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Surat penunjukan belum diunggah.');
    }

    $response = $this->response->download($path, null, true)
        ->setFileName('Surat-Penunjukan-TTD-' . (int) $titparId . '.' . pathinfo($filename, PATHINFO_EXTENSION));
    if ($this->request->getGet('download') !== '1') {
        $response->inline();
    }
    return $response;
}

public function manage($titparId)
{
    $db = \Config\Database::connect();

    // Data titik parkir
    $titpar = $db->table('dishub_titpar')
        ->select('dishub_titpar.*, retrytipe_nama, kelurahan.kecamatan_id, kelurahan.kecamatan_nama, kelurahan.kelurahan_nama, kelurahan.kabupaten_kota, kelurahan.provinsi')
        ->join('kelurahan', 'kelurahan.id = dishub_titpar.titpar_klrhnid', 'left')
        ->join('retritype', 'retritype.retritype_id = dishub_titpar.titpar_retritypeid', 'left')
        ->where('titpar_id', $titparId)
        ->get()
        ->getRowArray();

    if (!$titpar) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(
            'Data titik parkir tidak ditemukan'
        );
    }

    $masaBerlaku = $db->table('dishub_titpargrup')
        ->select('titpargrup_masaberlakustart, titpargrup_masaberlakuend')
        ->where('titpargrup_titparid', $titparId)
        ->where('titpargrup_masaberlakustart IS NOT NULL', null, false)
        ->orderBy('titpargrup_id', 'DESC')
        ->get(1)->getRowArray() ?? [];

    // ==========================================
    // AMBIL KOORDINATOR
    // ==========================================
    $koordinator = $db->table('dishub_titpargrup tg')
        ->select('
            tg.titpargrup_id,
            tg.titpargrup_titparid,
            tg.titpargrup_anggotaid,
            tg.titpargrup_shifthari,
            tg.titpargrup_shiftjammulai,
            tg.titpargrup_shiftjamselesai,
            tg.titpargrup_masaberlakustart,
            tg.titpargrup_masaberlakuend,
            a.anggota_nama,
            a.anggota_nohp,

            GROUP_CONCAT(
                DISTINCT va.va_owner_va
                ORDER BY va.va_owner_va
                SEPARATOR ", "
            ) AS va_owner_va
        ')
        ->join(
            'dishub_anggota a',
            'a.anggota_id = tg.titpargrup_anggotaid',
            'left'
        )
        ->join(
            'va_owner va',
            'va.va_owner_titpargrupid = tg.titpargrup_id',
            'left'
        )
        ->where('tg.titpargrup_titparid', $titparId)
        ->where('tg.titpargrup_staanggota', 'KOORDINATOR')
        ->groupBy('tg.titpargrup_id')
        ->orderBy('tg.titpargrup_id', 'ASC')
        ->get()
        ->getResultArray();


    // ==========================================
    // AMBIL ANGGOTA DI MASING-MASING KOORDINATOR
    // ==========================================
    foreach ($koordinator as &$k) {

        $k['anggota'] = $db->table('dishub_titpargrup tg')
            ->select('
                tg.titpargrup_id,
                tg.titpargrup_anggotaid,
                tg.titpargrup_masaberlakustart,
                tg.titpargrup_masaberlakuend,
                a.anggota_nama,
                a.anggota_id,
                a.anggota_nohp,

                GROUP_CONCAT(
                    DISTINCT va.va_owner_va
                    ORDER BY va.va_owner_va
                    SEPARATOR ", "
                ) AS va_owner_va
            ')
            ->join(
                'dishub_anggota a',
                'a.anggota_id = tg.titpargrup_anggotaid',
                'left'
            )
            ->join(
                'va_owner va',
                'va.va_owner_titpargrupid = tg.titpargrup_id',
                'left'
            )
            ->where('tg.titpargrup_titparid', $titparId)
            ->where('tg.titpargrup_staanggota', 'ANGGOTA')
            ->where(
                'tg.titpargrup_namashift',
                $k['titpargrup_anggotaid']
            )
            ->groupBy('tg.titpargrup_id')
            ->orderBy('a.anggota_nama', 'ASC')
            ->get()
            ->getResultArray();
    }

    unset($k);

    $coordinatorIds = array_values(array_filter(array_map(
        static fn($item) => (int) ($item['titpargrup_anggotaid'] ?? 0),
        $koordinator
    )));
    $denah = $titpar['titpar_denah'] ?? null;
    if (!$denah && $coordinatorIds) {
        $denah = $db->table('kta_perpanjangan')
            ->select('dokumen_pendukung')
            ->whereIn('anggota_id', $coordinatorIds)
            ->where('status', 'disetujui')
            ->where('dokumen_pendukung IS NOT NULL', null, false)
            ->where('dokumen_pendukung !=', '')
            ->orderBy('perpanjangan_id', 'DESC')
            ->get()->getRowArray()['dokumen_pendukung'] ?? null;
    }


    // ==========================================
    // AMBIL ANGGOTA YANG BELUM MEMILIKI KOORDINATOR
    // ==========================================
    $anggotaUnassigned = $db->table('dishub_titpargrup tg')
        ->select('
            tg.titpargrup_id,
            tg.titpargrup_anggotaid,
            a.anggota_nama,
            a.anggota_id,
            a.anggota_nohp,

            GROUP_CONCAT(
                DISTINCT va.va_owner_va
                ORDER BY va.va_owner_va
                SEPARATOR ", "
            ) AS va_owner_va
        ')
        ->join(
            'dishub_anggota a',
            'a.anggota_id = tg.titpargrup_anggotaid',
            'left'
        )
        ->join(
            'va_owner va',
            'va.va_owner_titpargrupid = tg.titpargrup_id',
            'left'
        )
        ->where('tg.titpargrup_titparid', $titparId)
        ->groupStart()
            ->where('tg.titpargrup_staanggota', 'ANGGOTA')
            ->orWhere('tg.titpargrup_staanggota', null)
        ->groupEnd()
        ->groupStart()
            ->where('tg.titpargrup_namashift', null)
            ->orWhere('tg.titpargrup_namashift', '')
        ->groupEnd()
        ->groupBy('tg.titpargrup_id')
        ->orderBy('a.anggota_nama', 'ASC')
        ->get()
        ->getResultArray();


    // ==========================================
    // DEBUG JSON
    // ==========================================
    // echo json_encode($koordinator);
    // exit();


    return view('titpar/manage', [
        'titpar'            => $titpar,
        'masaBerlaku'       => $masaBerlaku,
        'koordinator'       => $koordinator,
        'anggotaUnassigned' => $anggotaUnassigned,
        'denah'             => $denah,
        'denahPath'         => $titpar['titpar_denah'] ? 'uploads/denah' : 'uploads/kta_perpanjangan',
    ]);
}

public function printPenunjukan($titparId)
{
    $db = \Config\Database::connect();
    $titpar = $db->table('dishub_titpar')
        ->select('dishub_titpar.*, retrytipe_nama')
        ->join('retritype', 'retritype.retritype_id = dishub_titpar.titpar_retritypeid', 'left')
        ->where('titpar_id', $titparId)->get()->getRowArray();
    if (!$titpar) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    $row = $db->table('dishub_titpargrup tg')
        ->select('a.anggota_id, a.anggota_nama, a.anggota_noktp, a.anggota_tgllhr, a.anggota_alamat')
        ->join('dishub_anggota a', 'a.anggota_id = tg.titpargrup_anggotaid')
        ->where('tg.titpargrup_titparid', $titparId)
        ->where('tg.titpargrup_staanggota', 'KOORDINATOR')
        ->orderBy('tg.titpargrup_id', 'ASC')
        ->get()->getRowArray();

    if (!$row) {
        return redirect()->back()->with('error', 'Belum ada koordinator untuk titik parkir ini.');
    }

    $denah = $titpar['titpar_denah'] ?? null;
    if (!$denah) {
        $denah = $db->table('kta_perpanjangan')
            ->select('dokumen_pendukung')
            ->where('anggota_id', $row['anggota_id'])
            ->where('status', 'disetujui')
            ->where('dokumen_pendukung IS NOT NULL', null, false)
            ->where('dokumen_pendukung !=', '')
            ->orderBy('perpanjangan_id', 'DESC')
            ->get()->getRowArray()['dokumen_pendukung'] ?? null;
    }

    $coordinators = $db->table('dishub_titpargrup tg')
        ->select('tg.titpargrup_id, tg.titpargrup_shifthari, tg.titpargrup_shiftjammulai, tg.titpargrup_shiftjamselesai, a.anggota_id, a.anggota_nama, a.anggota_noktp, a.anggota_tgllhr, a.anggota_alamat, GROUP_CONCAT(DISTINCT va.va_owner_va ORDER BY va.va_owner_va SEPARATOR ", ") AS va_owner_va')
        ->join('dishub_anggota a', 'a.anggota_id = tg.titpargrup_anggotaid', 'left')
        ->join('va_owner va', 'va.va_owner_titpargrupid = tg.titpargrup_id', 'left')
        ->where('tg.titpargrup_titparid', $titparId)
        ->where('tg.titpargrup_staanggota', 'KOORDINATOR')
        ->groupBy('tg.titpargrup_id')->orderBy('tg.titpargrup_id', 'ASC')->get()->getResultArray();

    $groups = [];
    foreach ($coordinators as $coordinator) {
        $members = $db->table('dishub_titpargrup tg')
            ->select('a.anggota_nama, a.anggota_noktp, a.anggota_tgllhr AS tgl_lahir, a.anggota_alamat AS alamat, GROUP_CONCAT(DISTINCT va.va_owner_va ORDER BY va.va_owner_va SEPARATOR ", ") AS va_owner_va')
            ->join('dishub_anggota a', 'a.anggota_id = tg.titpargrup_anggotaid', 'left')
            ->join('va_owner va', 'va.va_owner_titpargrupid = tg.titpargrup_id', 'left')
            ->where('tg.titpargrup_titparid', $titparId)
            ->where('tg.titpargrup_staanggota', 'ANGGOTA')
            ->where('tg.titpargrup_namashift', $coordinator['anggota_id'])
            ->groupBy('tg.titpargrup_id')->orderBy('a.anggota_nama', 'ASC')->get()->getResultArray();
        $groups[] = ['coordinator' => $coordinator, 'members' => $members];
    }

    return view('titpar/print_penunjukan', [
        'register' => [
            'anggota_nama' => $row['anggota_nama'],
            'anggota_noktp' => $row['anggota_noktp'],
            'anggota_tgllhr' => $row['anggota_tgllhr'],
            'anggota_alamat' => $row['anggota_alamat'],
        ],
        'titpars' => [$titpar],
        'anggota' => [],
        'coordinatorGroups' => $groups,
        'denah' => $denah,
        'denahPath' => $titpar['titpar_denah'] ? 'uploads/denah' : 'uploads/kta_perpanjangan',
    ]);
}

public function updateMasaBerlaku($titparId)
{
    $start = trim((string) $this->request->getPost('titpargrup_masaberlakustart'));
    $end   = trim((string) $this->request->getPost('titpargrup_masaberlakuend'));

    if (!$start || !$end || strtotime($start) === false || strtotime($end) === false) {
        return redirect()->back()->with('error', 'Tanggal masa berlaku dan masa berakhir wajib diisi.');
    }

    if (strtotime($end) < strtotime($start)) {
        return redirect()->back()->with('error', 'Masa berakhir tidak boleh sebelum masa berlaku.');
    }

    $db = \Config\Database::connect();
    $exists = $db->table('dishub_titpar')->where('titpar_id', $titparId)->countAllResults();
    if (!$exists) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    $updated = $db->table('dishub_titpargrup')
        ->where('titpargrup_titparid', $titparId)
        ->update([
            'titpargrup_masaberlakustart' => $start,
            'titpargrup_masaberlakuend'   => $end,
        ]);

    return redirect()->back()->with(
        $updated ? 'success' : 'error',
        $updated ? 'Masa berlaku KTA berhasil diperbarui.' : 'Masa berlaku KTA tidak berubah.'
    );
}

public function updateMasaBerlakuAnggota($titpargrupId)
{
    $start = trim((string) $this->request->getPost('titpargrup_masaberlakustart'));
    $end   = trim((string) $this->request->getPost('titpargrup_masaberlakuend'));

    if (!$start || !$end || strtotime($start) === false || strtotime($end) === false) {
        return redirect()->back()->with('error', 'Tanggal masa berlaku dan masa berakhir wajib diisi.');
    }
    if (strtotime($end) < strtotime($start)) {
        return redirect()->back()->with('error', 'Masa berakhir tidak boleh sebelum masa berlaku.');
    }

    $db = \Config\Database::connect();
    $group = $db->table('dishub_titpargrup')
        ->select('titpargrup_id, titpargrup_anggotaid, titpargrup_titparid, titpargrup_staanggota')
        ->where('titpargrup_id', (int) $titpargrupId)
        ->get()->getRowArray();

    if (!$group) {
        return redirect()->back()->with('error', 'Data anggota tidak ditemukan.');
    }

    $update = [
        'titpargrup_masaberlakustart' => $start,
        'titpargrup_masaberlakuend'   => $end,
    ];

    $builder = $db->table('dishub_titpargrup');
    $builder->where('titpargrup_id', (int) $titpargrupId);

    // Jika yang diatur adalah koordinator, terapkan juga ke anggota di bawahnya.
    if ($group['titpargrup_staanggota'] === 'KOORDINATOR') {
        $builder->orGroupStart()
            ->where('titpargrup_titparid', (int) $group['titpargrup_titparid'])
            ->where('titpargrup_namashift', (int) $group['titpargrup_anggotaid'])
            ->where('titpargrup_staanggota', 'ANGGOTA')
            ->groupEnd();
    }

    $updated = $builder->update($update);

    return redirect()->back()->with(
        $updated ? 'success' : 'error',
        $updated ? 'Masa berlaku koordinator dan anggota di bawahnya berhasil diperbarui.' : 'Masa berlaku anggota tidak berubah.'
    );
}

public function ktaProfilApi($anggotaId)
{
    $db = \Config\Database::connect();
    $member = $db->table('dishub_anggota')
        ->select('anggota_id, anggota_nama, anggota_foto, anggota_tmplhr, anggota_tgllhr, anggota_jnsklm, anggota_peker, anggota_pente, anggota_nohp, anggota_alamat')
        ->where('anggota_id', (int) $anggotaId)
        ->where('anggota_status !=', 1)
        ->get()->getRowArray();

    if (!$member) {
        return $this->response->setStatusCode(404)->setJSON([
            'status' => 'error',
            'message' => 'Data anggota tidak ditemukan.'
        ]);
    }

    $person = [
        'anggota_id' => $member['anggota_id'],
        'anggota_nama' => $member['anggota_nama'],
        'anggota_foto' => $member['anggota_foto'],
        'anggota_tmplhr' => $member['anggota_tmplhr'],
        'anggota_tgllhr' => $member['anggota_tgllhr'],
        'anggota_jnsklm' => $member['anggota_jnsklm'],
        'anggota_peker' => $member['anggota_peker'],
        'anggota_pente' => $member['anggota_pente'],
        'anggota_nohp' => $member['anggota_nohp'],
        'anggota_alamat' => $member['anggota_alamat'],
        'foto_url' => !empty($member['anggota_foto']) ? base_url('uploads/anggota_foto/' . rawurlencode($member['anggota_foto'])) : null,
        'role' => 'ANGGOTA',
        'validFrom' => null,
        'validUntil' => null,
    ];

    $rows = $db->table('dishub_titpargrup g')
        ->select('t.*, a.anggota_id, a.anggota_nama, a.anggota_foto, a.anggota_tmplhr, a.anggota_tgllhr, a.anggota_jnsklm, a.anggota_peker, a.anggota_pente, a.anggota_nohp, a.anggota_alamat, g.titpargrup_id, g.titpargrup_staanggota, g.titpargrup_masaberlakustart, g.titpargrup_masaberlakuend, k.kelurahan_nama, k.kecamatan_nama')
        ->join('dishub_titpar t', 't.titpar_id = g.titpargrup_titparid')
        ->join('dishub_anggota a', 'a.anggota_id = g.titpargrup_anggotaid')
        ->join('kelurahan k', 'k.id = t.titpar_klrhnid', 'left')
        ->where('g.titpargrup_anggotaid', (int) $anggotaId)
        ->where('a.anggota_status !=', 1)
        ->where('t.titpar_status', 3)
        ->orderBy('t.titpar_namatempat', 'ASC')
        ->get()->getResultArray();

    $points = array_map(static function (array $row): array {
        return [
            'titpar' => [
                'titpar_id' => $row['titpar_id'],
                'titpargrup_id' => $row['titpargrup_id'],
                'titpar_noreg' => $row['titpar_noreg'] ?? null,
                'titpar_newnoreg' => $row['titpar_newnoreg'] ?? null,
                'titpar_namatempat' => $row['titpar_namatempat'],
                'titpar_lokasi' => $row['titpar_lokasi'],
                'titpar_pmlknama' => $row['titpar_pmlknama'] ?? null,
                'titpar_retritypeid' => $row['titpar_retritypeid'] ?? null,
                'titpar_klrhnid' => $row['titpar_klrhnid'] ?? null,
                'titpar_latitude' => $row['titpar_latitude'] ?? null,
                'titpar_longitude' => $row['titpar_longitude'] ?? null,
                'zona_id' => $row['zona_id'] ?? null,
                'titpar_status' => $row['titpar_status'] ?? null,
                'titpar_alihfungsi' => $row['titpar_alihfungsi'] ?? null,
                'kelurahan_nama' => $row['kelurahan_nama'] ?? null,
                'kecamatan_nama' => $row['kecamatan_nama'] ?? null,
                'titpar_denah' => $row['titpar_denah'] ?? null,
                'denah_url' => !empty($row['titpar_denah'])
                    ? base_url('uploads/denah/' . rawurlencode(basename($row['titpar_denah'])))
                    : null,
                'kta_number' => $row['anggota_id'] . '-' . $row['titpar_id'],
            ],
            'person' => [
                'anggota_id' => $row['anggota_id'],
                'anggota_nama' => $row['anggota_nama'],
                'anggota_foto' => $row['anggota_foto'],
                'anggota_tmplhr' => $row['anggota_tmplhr'],
                'anggota_tgllhr' => $row['anggota_tgllhr'],
                'anggota_jnsklm' => $row['anggota_jnsklm'],
                'anggota_peker' => $row['anggota_peker'],
                'anggota_pente' => $row['anggota_pente'],
                'anggota_nohp' => $row['anggota_nohp'],
                'anggota_alamat' => $row['anggota_alamat'],
                'foto_url' => !empty($row['anggota_foto']) ? base_url('uploads/anggota_foto/' . rawurlencode($row['anggota_foto'])) : null,
                'role' => $row['titpargrup_staanggota'] ?: 'ANGGOTA',
                'validFrom' => $row['titpargrup_masaberlakustart'],
                'validUntil' => $row['titpargrup_masaberlakuend'],
            ],
        ];
    }, $rows);

    if ($points) {
        $person = $points[0]['person'];
    }
    return $this->response->setJSON([
        'status' => 'success',
        'data' => $points,
        'person' => $person,
    ]);
}

public function ktaAnggotaApi($titparId)
{
    $db = \Config\Database::connect();
    $titpar = $db->table('dishub_titpar')->where('titpar_id', (int) $titparId)->get()->getRowArray();
    if (!$titpar) {
        return $this->response->setStatusCode(404)->setJSON([
            'status' => 'error',
            'message' => 'Data titik parkir tidak ditemukan.'
        ]);
    }

    $ids = array_values(array_filter(array_map('intval', (array) $this->request->getGet('person_ids'))));
    if (!$ids) {
        return $this->response->setStatusCode(400)->setJSON([
            'status' => 'error',
            'message' => 'person_ids wajib diisi.'
        ]);
    }

    $people = $db->table('dishub_anggota')
        ->select('anggota_id, anggota_nama, anggota_foto')
        ->whereIn('anggota_id', $ids)
        ->where('anggota_status !=', 1)
        ->orderBy('anggota_nama', 'ASC')
        ->get()->getResultArray();

    $coordinatorIds = array_column(
        $db->table('dishub_titpargrup')
            ->select('titpargrup_anggotaid')
            ->where('titpargrup_titparid', (int) $titparId)
            ->where('titpargrup_staanggota', 'KOORDINATOR')
            ->get()->getResultArray(),
        'titpargrup_anggotaid'
    );

    foreach ($people as &$person) {
        $person['role'] = in_array($person['anggota_id'], $coordinatorIds, true) ? 'KOORDINATOR' : 'ANGGOTA';
        $group = $db->table('dishub_titpargrup')
            ->select('titpargrup_masaberlakustart, titpargrup_masaberlakuend')
            ->where('titpargrup_titparid', (int) $titparId)
            ->where('titpargrup_anggotaid', $person['anggota_id'])
            ->orderBy('titpargrup_id', 'DESC')
            ->get(1)->getRowArray();
        $person['validFrom'] = $group['titpargrup_masaberlakustart'] ?? null;
        $person['validUntil'] = $group['titpargrup_masaberlakuend'] ?? null;
    }
    unset($person);

    return $this->response->setJSON([
        'status' => 'success',
        'data' => [
            'titpar' => $titpar,
            'people' => $people,
        ]
    ]);
}

public function printKta($titparId)
{
    $db = \Config\Database::connect();
    $titpar = $db->table('dishub_titpar')->where('titpar_id', $titparId)->get()->getRowArray();
    if (!$titpar) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

    $ids = array_values(array_filter(array_map('intval', (array) $this->request->getGet('person_ids'))));
    $size = strtolower((string) $this->request->getGet('size')) === 'cr80' ? 'cr80' : 'a4';
    $people = [];
    if ($ids) {
        $people = $db->table('dishub_anggota')
            ->whereIn('anggota_id', $ids)->where('anggota_status !=', 1)
            ->orderBy('anggota_nama', 'ASC')->get()->getResultArray();
    }
    $coordinatorIds = array_column($db->table('dishub_titpargrup')->select('titpargrup_anggotaid')->where('titpargrup_titparid', $titparId)->where('titpargrup_staanggota', 'KOORDINATOR')->get()->getResultArray(), 'titpargrup_anggotaid');
    foreach ($people as &$person) {
        $person['role'] = in_array($person['anggota_id'], $coordinatorIds, true) ? 'KOORDINATOR' : 'ANGGOTA';
        $group = $db->table('dishub_titpargrup')
            ->select('titpargrup_masaberlakustart, titpargrup_masaberlakuend')
            ->where('titpargrup_titparid', $titparId)
            ->where('titpargrup_anggotaid', $person['anggota_id'])
            ->orderBy('titpargrup_id', 'DESC')
            ->get(1)->getRowArray();
        $person['validFrom'] = $group['titpargrup_masaberlakustart'] ?? null;
        $person['validUntil'] = $group['titpargrup_masaberlakuend'] ?? null;
    }
    unset($person);
    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $options->set('chroot', FCPATH);
    $pdf = new Dompdf($options);
    $view = $size === 'a4' ? 'titpar/print_kta_a4' : 'titpar/print_kta';
    $html = view($view, ['titpar' => $titpar, 'people' => $people, 'size' => $size]);
    $pdfLayoutCss = $size === 'cr80'
        ? '@page{size:153.07pt 243.78pt;margin:0!important;padding:0!important}.toolbar{display:none!important}html,body{width:153.07pt!important;margin:0!important;padding:0!important}.sheet{display:block!important;width:153.07pt!important;margin:0!important;padding:0!important}.card{display:block!important;width:153.07pt!important;height:238pt!important;margin:0!important;page-break-after:auto!important}.card:not(:first-child){page-break-before:always!important}.photo{object-fit:contain!important;object-position:center center!important;background:#f1f3f5!important}'
        : '.toolbar{display:none!important}.sheet{display:block!important;width:190mm!important;margin:0 auto!important}.card{display:inline-block!important;vertical-align:top;margin:0 5mm 7mm 0!important}';
    $html = str_replace('</head>', '<style>' . $pdfLayoutCss . '</style></head>', $html);
    $pdf->loadHtml($html);
    if ($size === 'cr80') {
        $pdf->setPaper([0, 0, 153.07, 243.78], 'portrait');
    } else {
        $pdf->setPaper('A4', 'portrait');
    }
    $pdf->render();
    return $this->response->setHeader('Content-Type', 'application/pdf')->setBody($pdf->output());
}

public function manageOld($titparId)
{
    $db = \Config\Database::connect();

    // Data titik parkir
    $titpar = $db->table('dishub_titpar')
        ->where('titpar_id', $titparId)
        ->get()
        ->getRowArray();

    if (!$titpar) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(
            'Data titik parkir tidak ditemukan'
        );
    }

    // Ambil koordinator
    $koordinator = $db->table('dishub_titpargrup tg')
        ->select('
            tg.titpargrup_id,
            tg.titpargrup_titparid,
            tg.titpargrup_anggotaid,
            tg.titpargrup_shifthari,
            tg.titpargrup_shiftjammulai,
            tg.titpargrup_shiftjamselesai,
            a.anggota_nama,
            va.va_owner_va,
            a.anggota_nohp,
            va.deleted_at as va_deleted
        ')
        ->join(
            'dishub_anggota a',
            'a.anggota_id = tg.titpargrup_anggotaid',
            'left'
        )
        ->join(
            'va_owner va', 'va.va_owner_titpargrupid = tg.titpargrup_id','left'
        )
        ->where('tg.titpargrup_titparid', $titparId)
        ->where('tg.titpargrup_staanggota', 'KOORDINATOR')
        // ->groupBy('va.va_owner_va')
        ->orderBy('tg.titpargrup_id', 'ASC')
        
        ->get()
        ->getResultArray();

        // echo json_encode($koordinator);
        // exit();

    // Ambil anggota di masing-masing koordinator
    foreach ($koordinator as &$k) {

    $k['anggota'] = $db->table('dishub_titpargrup tg')
    ->select('
        tg.titpargrup_id,
        tg.titpargrup_anggotaid,
        a.anggota_nama,
        a.anggota_id,
        va.va_owner_va,
        va.deleted_at as va_deleted,
        a.anggota_nohp
    ')
    ->join(
        'dishub_anggota a',
        'a.anggota_id = tg.titpargrup_anggotaid',
        'left'
    )
    ->join(
            'va_owner va', 'va.va_owner_titpargrupid = tg.titpargrup_id','left'
        )
    ->where('tg.titpargrup_titparid', $titparId)
    ->where('tg.titpargrup_staanggota', 'ANGGOTA')
    ->where('tg.titpargrup_namashift', $k['titpargrup_anggotaid'])
    ->orderBy('a.anggota_nama', 'ASC')
    ->get()
    ->getResultArray();
}

// Ambil anggota yang belum memiliki koordinator
$anggotaUnassigned = $db->table('dishub_titpargrup tg')
    ->select('
        tg.titpargrup_id,
        tg.titpargrup_anggotaid,
        a.anggota_nama,
        va.va_owner_va,
        a.anggota_id,
        va.deleted_at as va_deleted,
        a.anggota_nohp
    ')
    ->join(
        'dishub_anggota a',
        'a.anggota_id = tg.titpargrup_anggotaid',
        'left'
    )
    ->join(
            'va_owner va', 'va.va_owner_titpargrupid = tg.titpargrup_id','left'
        )
    ->where('tg.titpargrup_titparid', $titparId)
    ->groupStart()
    ->where('tg.titpargrup_staanggota', 'ANGGOTA')
    ->orWhere('tg.titpargrup_staanggota', null)
    ->groupEnd()
    ->groupStart()
        ->where('tg.titpargrup_namashift', null)
        ->orWhere('tg.titpargrup_namashift', '')
    ->groupEnd()
    ->orderBy('a.anggota_nama', 'ASC')
    ->get()
    ->getResultArray();

    echo json_encode($koordinator);
    exit();

    return view('titpar/manage', [
    'titpar'          => $titpar,
    'koordinator'     => $koordinator,
    'anggotaUnassigned' => $anggotaUnassigned
]);
}

public function makeKoordinator($titpargrupId)
{
    $db = \Config\Database::connect();

    // Ambil anggota yang belum memiliki koordinator
    $anggota = $db->table('dishub_titpargrup')
        ->where('titpargrup_id', $titpargrupId)
        // ->where('titpargrup_staanggota', 'ANGGOTA')
        ->groupStart()
            ->where('titpargrup_namashift', null)
            ->orWhere('titpargrup_namashift', '')
        ->groupEnd()
        ->get()
        ->getRowArray();

    if (!$anggota) {
        return redirect()->back()
            ->with(
                'error',
                'Anggota tidak ditemukan atau sudah memiliki koordinator.'
            );
    }

    $titparId   = $anggota['titpargrup_titparid'];
    $anggotaId  = $anggota['titpargrup_anggotaid'];

    // Cek jumlah koordinator saat ini
    $jumlahKoordinator = $db->table('dishub_titpargrup')
        ->where('titpargrup_titparid', $titparId)
        ->where('titpargrup_staanggota', 'KOORDINATOR')
        ->countAllResults();

    // Maksimal 4 koordinator
    if ($jumlahKoordinator >= 4) {
        return redirect()->back()
            ->with(
                'error',
                'Maksimal 4 koordinator untuk satu titik parkir.'
            );
    }

    // Pastikan anggota belum menjadi koordinator
    $sudahKoordinator = $db->table('dishub_titpargrup')
        ->where('titpargrup_titparid', $titparId)
        ->where('titpargrup_anggotaid', $anggotaId)
        ->where('titpargrup_staanggota', 'KOORDINATOR')
        ->countAllResults();

    if ($sudahKoordinator) {
        return redirect()->back()
            ->with(
                'error',
                'Anggota tersebut sudah menjadi koordinator.'
            );
    }

    // Ubah anggota menjadi koordinator
    $updated = $db->table('dishub_titpargrup')
        ->where('titpargrup_id', $titpargrupId)
        ->update([
            'titpargrup_staanggota' => 'KOORDINATOR',
            'titpargrup_namashift'  => null,
        ]);

    if (!$updated) {
        return redirect()->back()
            ->with(
                'error',
                'Gagal menjadikan anggota sebagai koordinator.'
            );
    }

    return redirect()->back()
        ->with(
            'success',
            'Anggota berhasil dijadikan koordinator.'
        );
}

public function addKoordinator($titparId)
{
    $db = \Config\Database::connect();

    $anggotaId = $this->request->getPost('anggota_id');

    if (!$anggotaId) {
        return redirect()->back()
            ->with('error', 'Silakan pilih anggota.');
    }

    // Maksimal 4 koordinator
    $jumlah = $db->table('dishub_titpargrup')
        ->where('titpargrup_titparid', $titparId)
        ->where('titpargrup_staanggota', 'KOORDINATOR')
        ->countAllResults();

    if ($jumlah >= 4) {
        return redirect()->back()
            ->with('error', 'Maksimal 4 koordinator untuk satu titik parkir.');
    }

    // Cek apakah anggota sudah menjadi koordinator
    $exists = $db->table('dishub_titpargrup')
        ->where('titpargrup_titparid', $titparId)
        ->where('titpargrup_anggotaid', $anggotaId)
        ->where('titpargrup_staanggota', 'KOORDINATOR')
        ->countAllResults();

    if ($exists) {
        return redirect()->back()
            ->with('error', 'Anggota tersebut sudah menjadi koordinator.');
    }

    $db->table('dishub_titpargrup')->insert([
        'titpargrup_titparid'   => $titparId,
        'titpargrup_anggotaid'  => $anggotaId,
        'titpargrup_staanggota' => 'KOORDINATOR',
        'titpargrup_namashift'  => null,
    ]);

    return redirect()->back()
        ->with('success', 'Koordinator berhasil ditambahkan.');
}



public function addAnggota($titparId)
{
    $db = \Config\Database::connect();

    $anggotaId     = $this->request->getPost('anggota_id');
    $koordinatorId = $this->request->getPost('koordinator_id');

    if (!$anggotaId || !$koordinatorId) {
        return redirect()->back()
            ->with('error', 'Anggota dan koordinator harus dipilih.');
    }

    // Pastikan koordinator valid
    $koordinator = $db->table('dishub_titpargrup')
        ->where('titpargrup_titparid', $titparId)
        ->where('titpargrup_anggotaid', $koordinatorId)
        ->where('titpargrup_staanggota', 'KOORDINATOR')
        ->countAllResults();

    if (!$koordinator) {
        return redirect()->back()
            ->with('error', 'Koordinator tidak valid.');
    }

    // Cek apakah anggota sudah ada di Titpar
    $existing = $db->table('dishub_titpargrup')
        ->where('titpargrup_titparid', $titparId)
        ->where('titpargrup_anggotaid', $anggotaId)
        ->get()
        ->getRowArray();

    if ($existing) {

        // Jangan izinkan koordinator dipindahkan sebagai anggota
        if ($existing['titpargrup_staanggota'] === 'KOORDINATOR') {
            return redirect()->back()
                ->with('error', 'Orang tersebut sudah menjadi koordinator.');
        }

        // Existing ANGGOTA → assign/reassign koordinator
        $db->table('dishub_titpargrup')
            ->where('titpargrup_id', $existing['titpargrup_id'])
            ->update([
                'titpargrup_staanggota' => 'ANGGOTA',
                'titpargrup_namashift' => $koordinatorId
            ]);

        return redirect()->back()
            ->with('success', 'Anggota berhasil ditempatkan ke koordinator.');
    }

    // Belum ada di Titpar → insert baru
    $db->table('dishub_titpargrup')->insert([
        'titpargrup_titparid'   => $titparId,
        'titpargrup_anggotaid'  => $anggotaId,
        'titpargrup_staanggota' => 'ANGGOTA',
        'titpargrup_namashift'  => $koordinatorId,
    ]);

    return redirect()->back()
        ->with('success', 'Anggota berhasil ditempatkan.');
}

public function removeAnggota($titpargrupId)
{
    $db = \Config\Database::connect();

    // Cari data penempatan
    $anggota = $db->table('dishub_titpargrup')
        ->where('titpargrup_id', $titpargrupId)
        ->where('titpargrup_staanggota', 'ANGGOTA')
        ->get()
        ->getRowArray();

    if (!$anggota) {
        return redirect()->back()
            ->with('error', 'Data penempatan anggota tidak ditemukan.');
    }

    // Hapus penempatan anggota dari Titpar
    $db->table('dishub_titpargrup')
        ->where('titpargrup_id', $titpargrupId)
        ->delete();

    return redirect()->back()
        ->with('success', 'Anggota berhasil dihapus dari titik parkir.');
}

public function searchAnggota($titparId)
{
    $db = \Config\Database::connect();

    $search = trim($this->request->getGet('search') ?? '');

    $builder = $db->table('dishub_anggota a');

    $builder->select('
        a.anggota_id,
        a.anggota_nama,
  
        a.anggota_nohp
    ');

    $builder->where('a.anggota_status', 3);

    // Hanya sembunyikan orang yang sudah menjadi KOORDINATOR
   $builder->whereNotIn(
    'a.anggota_id',
    function ($subquery) use ($titparId) {
        $subquery->select('titpargrup_anggotaid')
            ->from('dishub_titpargrup')
            ->where('titpargrup_titparid', $titparId)
            ->where('titpargrup_staanggota', 'KOORDINATOR');
    }
);

    if ($search !== '') {
        $builder->groupStart()
            ->like('a.anggota_nama', $search)
            ->orLike('a.anggota_id', $search)
            
            ->groupEnd();
    }

    $builder->orderBy('a.anggota_nama', 'ASC');
    $builder->limit(30);

    $rows = $builder->get()->getResultArray();

    $results = [];

    foreach ($rows as $row) {
        $results[] = [
            'id' => $row['anggota_id'],
            'text' => $row['anggota_nama']
                . ' - '
                . ($row['anggota_id'] ?: $row['anggota_id']),
        ];
    }

    return $this->response->setJSON([
        'results' => $results
    ]);
}

public function removeKoordinator($titpargrupId)
{
    $db = \Config\Database::connect();

    // Ambil data koordinator
    $koordinator = $db->table('dishub_titpargrup')
        ->where('titpargrup_id', $titpargrupId)
        ->where('titpargrup_staanggota', 'KOORDINATOR')
        ->get()
        ->getRowArray();

    if (!$koordinator) {
        return redirect()->back()
            ->with('error', 'Data koordinator tidak ditemukan.');
    }

    $titparId = $koordinator['titpargrup_titparid'];

    // ID anggota yang menjadi koordinator
    $koordinatorId = $koordinator['titpargrup_anggotaid'];

    $db->transStart();

    // 1. Hapus semua anggota yang berada
    //    di bawah koordinator ini
    $db->table('dishub_titpargrup')
        ->where('titpargrup_titparid', $titparId)
        ->where('titpargrup_namashift', $koordinatorId)
        ->where('titpargrup_staanggota', 'ANGGOTA')
        ->delete();

    // 2. Hapus koordinator
    $db->table('dishub_titpargrup')
        ->where('titpargrup_id', $titpargrupId)
        ->delete();

    $db->transComplete();

    if ($db->transStatus() === false) {
        return redirect()->back()
            ->with('error', 'Gagal menghapus koordinator.');
    }

    return redirect()->back()
        ->with(
            'success',
            'Koordinator dan seluruh anggota di bawahnya berhasil dihapus dari titik parkir.'
        );
}

public function searchKoordinator($titparId)
{
    $db = \Config\Database::connect();

    $search = trim($this->request->getGet('search') ?? '');

    // Get anggota IDs already registered in this Titpar
    $subquery = $db->table('dishub_titpargrup')
        ->select('titpargrup_anggotaid')
        ->where('titpargrup_titparid', $titparId)
        ->where('titpargrup_anggotaid IS NOT NULL', null, false)
        ->getCompiledSelect();

    $builder = $db->table('dishub_anggota a');

    $builder->select('
        a.anggota_id,
        a.anggota_nama,

        a.anggota_nohp
    ');

    // Active anggota
    $builder->where('a.anggota_status', 3);

    // Exclude anggota already registered in this Titpar
    $builder->where(
        "a.anggota_id NOT IN ($subquery)",
        null,
        false
    );

    // Search
    if ($search !== '') {
        $builder->groupStart()
            ->like('a.anggota_nama', $search)
            ->orLike('a.anggota_id', $search)
            ->orLike('a.anggota_nohp', $search)
            ->groupEnd();
    }

    $builder->orderBy('a.anggota_nama', 'ASC');
    $builder->limit(50);

    $rows = $builder->get()->getResultArray();

    $results = [];

    foreach ($rows as $row) {

        $noreg = $row['anggota_id']
            ?: $row['anggota_id'];

        $results[] = [
            'id' => $row['anggota_id'],
            'text' => $row['anggota_nama'] . ' - ' . $noreg,
        ];
    }

    return $this->response->setJSON([
        'results' => $results
    ]);
}

public function assignAnggota($titpargrupId)
{
    $db = \Config\Database::connect();

    // Ambil data anggota yang akan diassign
    $anggota = $db->table('dishub_titpargrup')
        ->where('titpargrup_id', $titpargrupId)
        ->groupStart()
            ->where('titpargrup_staanggota', 'ANGGOTA')
            ->orWhere('titpargrup_staanggota', null)
        ->groupEnd()
        ->get()
        ->getRowArray();

    if (!$anggota) {
        return redirect()->back()
            ->with('error', 'Data anggota tidak ditemukan.');
    }

    $koordinatorId = $this->request->getPost('koordinator_id');

    if (!$koordinatorId) {
        return redirect()->back()
            ->with('error', 'Koordinator harus dipilih.');
    }

    // Pastikan koordinator memang berada di Titpar yang sama
    $koordinator = $db->table('dishub_titpargrup')
        ->where(
            'titpargrup_titparid',
            $anggota['titpargrup_titparid']
        )
        ->where(
            'titpargrup_anggotaid',
            $koordinatorId
        )
        ->where(
            'titpargrup_staanggota',
            'KOORDINATOR'
        )
        ->countAllResults();

    if (!$koordinator) {
        return redirect()->back()
            ->with('error', 'Koordinator tidak valid.');
    }

    // Assign anggota ke koordinator
    $db->table('dishub_titpargrup')
        ->where('titpargrup_id', $titpargrupId)
        ->update([
            'titpargrup_staanggota' => 'ANGGOTA',
            'titpargrup_namashift' => $koordinatorId
        ]);

    return redirect()->back()
        ->with(
            'success',
            'Anggota berhasil ditempatkan ke koordinator.'
        );
}

public function changeCoordinator($titpargrupId)
{
    $db = \Config\Database::connect();

    // Ambil data anggota
    $anggota = $db->table('dishub_titpargrup')
        ->where('titpargrup_id', $titpargrupId)
        ->where('titpargrup_staanggota', 'ANGGOTA')
        ->get()
        ->getRowArray();

    if (!$anggota) {
        return redirect()->back()
            ->with('error', 'Data anggota tidak ditemukan.');
    }

    $koordinatorId = $this->request->getPost('koordinator_id');

    if (!$koordinatorId) {
        return redirect()->back()
            ->with('error', 'Koordinator harus dipilih.');
    }

    // Pastikan koordinator berada di Titpar yang sama
    $koordinator = $db->table('dishub_titpargrup')
        ->where(
            'titpargrup_titparid',
            $anggota['titpargrup_titparid']
        )
        ->where(
            'titpargrup_anggotaid',
            $koordinatorId
        )
        ->where(
            'titpargrup_staanggota',
            'KOORDINATOR'
        )
        ->countAllResults();

    if (!$koordinator) {
        return redirect()->back()
            ->with('error', 'Koordinator tidak valid.');
    }

    // Pindahkan anggota
    $db->table('dishub_titpargrup')
        ->where('titpargrup_id', $titpargrupId)
        ->update([
            'titpargrup_namashift' => $koordinatorId
        ]);

    return redirect()->back()
        ->with(
            'success',
            'Anggota berhasil dipindahkan ke koordinator baru.'
        );
}

public function searchKoordinatorReplacement($titparId)
{
    $db = \Config\Database::connect();

    $search = trim(
        $this->request->getGet('search') ?? ''
    );

    $builder = $db->table('dishub_anggota a');

    $builder->select('
        a.anggota_id,
        a.anggota_nama,
     
        a.anggota_nohp
    ');

    // Hanya anggota aktif
    $builder->where(
        'a.anggota_status',
        3
    );

    // Jangan tampilkan orang yang sudah menjadi
    // KOORDINATOR di Titpar ini
    $builder->whereNotIn(
        'a.anggota_id',
        function ($subquery) use ($titparId) {

            $subquery->select(
                'titpargrup_anggotaid'
            )
            ->from('dishub_titpargrup')
            ->where(
                'titpargrup_titparid',
                $titparId
            )
            ->where(
                'titpargrup_staanggota',
                'KOORDINATOR'
            );

        }
    );

    if ($search !== '') {

        $builder->groupStart()

            ->like(
                'a.anggota_nama',
                $search
            )

            ->orLike(
                'a.anggota_id',
                $search
            )


        ->groupEnd();

    }

    $builder
        ->orderBy(
            'a.anggota_nama',
            'ASC'
        )
        ->limit(30);

    $rows = $builder
        ->get()
        ->getResultArray();

    $results = [];

    foreach ($rows as $row) {

        $results[] = [

            'id' =>
                $row['anggota_id'],

            'text' =>
                $row['anggota_nama']
                . ' - '
                . (
                    $row['anggota_id']
                    ?: $row['anggota_id']
                ),

        ];

    }

    return $this
        ->response
        ->setJSON([
            'results' => $results
        ]);
}

public function replaceKoordinator($oldTitpargrupId)
{
    $db = \Config\Database::connect();

    // Ambil koordinator lama
    $oldCoordinator = $db->table('dishub_titpargrup')
        ->where(
            'titpargrup_id',
            $oldTitpargrupId
        )
        ->where(
            'titpargrup_staanggota',
            'KOORDINATOR'
        )
        ->get()
        ->getRowArray();

    if (!$oldCoordinator) {

        return redirect()
            ->back()
            ->with(
                'error',
                'Koordinator lama tidak ditemukan.'
            );

    }

    $titparId =
        $oldCoordinator['titpargrup_titparid'];

    $oldCoordinatorId =
        $oldCoordinator['titpargrup_anggotaid'];

    $newCoordinatorId =
        $this->request
            ->getPost(
                'new_koordinator_id'
            );

    if (!$newCoordinatorId) {

        return redirect()
            ->back()
            ->with(
                'error',
                'Koordinator baru harus dipilih.'
            );

    }

    // Tidak boleh mengganti dengan dirinya sendiri
    if (
        $newCoordinatorId ==
        $oldCoordinatorId
    ) {

        return redirect()
            ->back()
            ->with(
                'error',
                'Koordinator baru harus berbeda.'
            );

    }

    // Pastikan orang baru aktif
    $newPerson = $db->table('dishub_anggota')
        ->where(
            'anggota_id',
            $newCoordinatorId
        )
        ->where(
            'anggota_status',
            3
        )
        ->get()
        ->getRowArray();

    if (!$newPerson) {

        return redirect()
            ->back()
            ->with(
                'error',
                'Anggota baru tidak valid.'
            );

    }

    // Pastikan orang baru belum menjadi
    // koordinator lain di Titpar yang sama
    $alreadyCoordinator = $db
        ->table('dishub_titpargrup')
        ->where(
            'titpargrup_titparid',
            $titparId
        )
        ->where(
            'titpargrup_anggotaid',
            $newCoordinatorId
        )
        ->where(
            'titpargrup_staanggota',
            'KOORDINATOR'
        )
        ->countAllResults();

    if ($alreadyCoordinator) {

        return redirect()
            ->back()
            ->with(
                'error',
                'Anggota tersebut sudah menjadi koordinator.'
            );

    }

    $db->transStart();

    /*
    |--------------------------------------------------------------------------
    | 1. Jika calon koordinator sudah menjadi ANGGOTA
    |    di Titpar ini, ubah record tersebut menjadi KOORDINATOR
    |--------------------------------------------------------------------------
    */

    $existingMember = $db
        ->table('dishub_titpargrup')
        ->where(
            'titpargrup_titparid',
            $titparId
        )
        ->where(
            'titpargrup_anggotaid',
            $newCoordinatorId
        )
        ->where(
            'titpargrup_staanggota',
            'ANGGOTA'
        )
        ->get()
        ->getRowArray();

    if ($existingMember) {

        // Ubah record anggota menjadi koordinator
        $db->table('dishub_titpargrup')
            ->where(
                'titpargrup_id',
                $existingMember['titpargrup_id']
            )
            ->update([

                'titpargrup_staanggota' =>
                    'KOORDINATOR',

                'titpargrup_namashift' =>
                    null,

            ]);

        $newCoordinatorTitpargrupId =
            $existingMember['titpargrup_id'];

    } else {

        // Orang baru belum terdaftar di Titpar
        // Buat record koordinator baru

        $db->table('dishub_titpargrup')
            ->insert([

                'titpargrup_titparid' =>
                    $titparId,

                'titpargrup_anggotaid' =>
                    $newCoordinatorId,

                'titpargrup_staanggota' =>
                    'KOORDINATOR',

                'titpargrup_namashift' =>
                    null,

            ]);

        $newCoordinatorTitpargrupId =
            $db->insertID();

    }

    /*
    |--------------------------------------------------------------------------
    | 2. Pindahkan semua anggota dari koordinator lama
    |--------------------------------------------------------------------------
    */

    $db->table('dishub_titpargrup')
        ->where(
            'titpargrup_titparid',
            $titparId
        )
        ->where(
            'titpargrup_staanggota',
            'ANGGOTA'
        )
        ->where(
            'titpargrup_namashift',
            $oldCoordinatorId
        )
        ->update([

            'titpargrup_namashift' =>
                $newCoordinatorId

        ]);

    /*
    |--------------------------------------------------------------------------
    | 3. Hapus koordinator lama
    |--------------------------------------------------------------------------
    */

    $db->table('dishub_titpargrup')
        ->where(
            'titpargrup_id',
            $oldTitpargrupId
        )
        ->delete();

    $db->transComplete();

    if ($db->transStatus() === false) {

        return redirect()
            ->back()
            ->with(
                'error',
                'Gagal mengganti koordinator.'
            );

    }

    return redirect()
        ->back()
        ->with(
            'success',
            'Koordinator berhasil diganti dan seluruh anggota telah dipindahkan.'
        );
}

public function updateShift($titpargrupId)
{
    $db = \Config\Database::connect();

    // Pastikan data adalah KOORDINATOR
    $koordinator = $db->table('dishub_titpargrup')
        ->where('titpargrup_id', $titpargrupId)
        ->where('titpargrup_staanggota', 'KOORDINATOR')
        ->get()
        ->getRowArray();

    if (!$koordinator) {
        return redirect()->back()
            ->with('error', 'Data koordinator tidak ditemukan.');
    }

    // Ambil hari yang dipilih
    $hari = $this->request->getPost('hari') ?? [];

    // Bitmask hari
    $hariMap = [
        'senin'  => 1,
        'selasa' => 2,
        'rabu'   => 4,
        'kamis'  => 8,
        'jumat'  => 16,
        'sabtu'  => 32,
        'minggu' => 64,
    ];

    $hariBitmask = 0;

    foreach ($hari as $h) {
        if (isset($hariMap[$h])) {
            $hariBitmask += $hariMap[$h];
        }
    }

    $jamMulai   = $this->request->getPost('jam_mulai');
    $jamSelesai = $this->request->getPost('jam_selesai');

    // Validasi minimal
    if ($hariBitmask == 0) {
        return redirect()->back()
            ->with('error', 'Pilih minimal satu hari.');
    }

    if (empty($jamMulai) || empty($jamSelesai)) {
        return redirect()->back()
            ->with('error', 'Jam mulai dan jam selesai wajib diisi.');
    }

    // Update
    $db->table('dishub_titpargrup')
        ->where('titpargrup_id', $titpargrupId)
        ->update([
            'titpargrup_shifthari'      => $hariBitmask,
            'titpargrup_shiftjammulai'  => $jamMulai,
            'titpargrup_shiftjamselesai' => $jamSelesai,
        ]);

    return redirect()->back()
        ->with('success', 'Jadwal koordinator berhasil diperbarui.');
}
    
}
