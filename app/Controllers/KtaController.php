<?php 

namespace App\Controllers;

use App\Libraries\DataTable;

use DB;
use CodeIgniter\API\ResponseTrait;

class KtaController extends BaseController
{
    public $db;
    use ResponseTrait;


    //Status 5 = Pendaftaran Baru
    //Status 6 = Perpanjangan

    protected $table = 'register';

    public function __construct()
    {
      $this->db = \Config\Database::connect();
  }

  public function index()
  {
    $builder = $this->db->table($this->table);

        // Filter
    $status = $this->request->getGet('status');
    if ($status != '') {
        $builder->where('anggota_status', $status);
    }

        // Search
    $search = $this->request->getGet('search');
    if ($search) {
        $builder->groupStart()
        ->like('anggota_nama', $search)
        ->orLike('anggota_noktp', $search)
        ->groupEnd();
    }

    $data['list'] = $builder->orderBy('id', 'DESC')->get()->getResult();
    return view('kta/index', $data);
}

public function detail($id)
{
    $data['item'] = $this->db->table($this->table)
    ->where('id', $id)
    ->get()
    ->getRow();

    if (!$data['item']) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    return view('kta/detail', $data);
}

    /* ========================
       UPDATE STATUS
       ======================== */
       public function updateStatus($id)
       {
        $status = $this->request->getPost('status');

        $this->db->table($this->table)
        ->where('id', $id)
        ->update([
            'anggota_status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return redirect()->to('/admin/kta')->with('msg', 'Status updated');
    }

    public function test(){
        echo "test";
    }

    public function addTitpar()
    {
        $nik = $this->request->getGet('nik');

        if (!$nik) {
            return redirect()->to(base_url('pendaftaran'))
            ->with('error', 'NIK tidak ditemukan.');
        }

        $db = \Config\Database::connect();

        $user = $db->table('register')
        ->where('anggota_noktp', $nik)
        ->get()
        ->getRowArray();

        if (!$user) {
            return redirect()->to(base_url('pendaftaran'))
            ->with('error', 'Data anggota tidak ditemukan.');
        }

        return view('kta/add_titpar', [
            'user' => $user
        ]);
    }

    public function addAnggota()
    {
        $nik = $this->request->getGet('nik');

        if (!$nik) {
            return redirect()->to(base_url('pendaftaran'))
            ->with('error', 'NIK tidak ditemukan.');
        }

        $db = \Config\Database::connect();

        $user = $db->table('register')
        ->where('anggota_noktp', $nik)
        ->get()
        ->getRowArray();

        if (!$user) {
            return redirect()->to(base_url('pendaftaran'))
            ->with('error', 'Data anggota tidak ditemukan.');
        }

        return view('kta/add_anggota', [
            'user' => $user
        ]);
    }


    public function saveTitpar()
    {
        helper(['form']);

        $db = \Config\Database::connect();

        $id = $this->request->getPost('id');

    // Hanya validasi field wajib
        $rules = [
            'anggota_noktp'     => 'required',
            'titpar_namatempat' => 'required',
            'titpar_lokasi'     => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
            ->withInput()
            ->with('errors', $this->validator->getErrors());
        }

        $data = [
            'anggota_noktp'     => $this->request->getPost('anggota_noktp'),
            'titpar_namatempat' => $this->request->getPost('titpar_namatempat'),
            'titpar_lokasi'     => $this->request->getPost('titpar_lokasi'),
        ];

        $folders = [
            'kesanggupan'  => FCPATH . 'uploads/kesanggupan/',
            'keberatan'    => FCPATH . 'uploads/keberatan/',
            'denah'        => FCPATH . 'uploads/denah/',
            'kk'           => FCPATH . 'uploads/kk_titpar/',
            'berita_acara' => FCPATH . 'uploads/berita_acara/',
            'penunjukan'   => FCPATH . 'uploads/penunjukan/',
        ];

        $allowedMime = [
            'image/jpeg',
            'image/png',
            'application/pdf',
        ];

    $maxSize = 3 * 1024 * 1024; // 5 MB

    $old = [];

    if ($id) {
        $old = $db->table('register_titpar')
        ->where('id', $id)
        ->get()
        ->getRowArray();

        if (!$old) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
    }

    $db->transStart();

    foreach ($folders as $field => $path) {

        $file = $this->request->getFile($field);

        // Tidak upload file -> skip
        if (!$file || $file->getError() === UPLOAD_ERR_NO_FILE) {
            continue;
        }

        // Upload gagal
        if (!$file->isValid()) {
            return redirect()->back()
            ->withInput()
            ->with('error', $field . ': ' . $file->getErrorString());
        }

        // Validasi MIME
        if (!in_array($file->getMimeType(), $allowedMime)) {
            return redirect()->back()
            ->withInput()
            ->with('error', "Format file {$field} harus JPG, PNG, atau PDF.");
        }

        // Validasi ukuran
        if ($file->getSize() > $maxSize) {
            return redirect()->back()
            ->withInput()
            ->with('error', "Ukuran file {$field} maksimal 3 MB.");
        }

        // Buat folder jika belum ada
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        // Hapus file lama jika edit
        if ($id && !empty($old[$field])) {
            $oldFile = $path . $old[$field];

            if (is_file($oldFile)) {
                unlink($oldFile);
            }
        }

        $newName = $file->getRandomName();
        $file->move($path, $newName);

        $data[$field] = $newName;
    }

    if ($id) {

        $data['updated_at'] = date('Y-m-d H:i:s');

        $db->table('register_titpar')
        ->where('id', $id)
        ->update($data);

    } else {

        $data['created_at'] = date('Y-m-d H:i:s');

        $db->table('register_titpar')
        ->insert($data);

        $id = $db->insertID();
    }

    $db->transComplete();

    if (!$db->transStatus()) {
        return redirect()->back()
        ->withInput()
        ->with('error', 'Gagal menyimpan data.');
    }

    return redirect()->to(base_url('register/titpar/edit/' . $id))
    ->with('success', 'Data titik parkir berhasil disimpan.');
}

public function saveAnggota()
{
    helper(['form']);

    $rules = [
        'koordinator_noktp' => 'required',
        'anggota_nama'      => 'required',
        'anggota_noktp'     => 'required|min_length[16]|max_length[16]',
        'tgl_lahir'         => 'required',
        'alamat'            => 'required',

        'foto' => 'uploaded[foto]|is_image[foto]',
        'ktp'  => 'uploaded[ktp]|is_image[ktp]',
        'kk'   => 'uploaded[kk]|is_image[kk]',
    ];

    if (! $this->validate($rules)) {
        return redirect()->back()
        ->withInput()
        ->with('errors', $this->validator->getErrors());
    }

    $db = db_connect();

    // Upload file
    $foto = $this->request->getFile('foto');
    $ktp  = $this->request->getFile('ktp');
    $kk   = $this->request->getFile('kk');

    $namaFoto = $foto->getRandomName();
    $namaKtp  = $ktp->getRandomName();
    $namaKK   = $kk->getRandomName();

    $foto->move('uploads/anggota_foto', $namaFoto);
    $ktp->move('uploads/anggota_ktp', $namaKtp);
    $kk->move('uploads/anggota_kk', $namaKK);
    $koordinator_noktp = $this->request->getPost('koordinator_noktp');
    // Insert
    $db->table('register_anggota')->insert([
        'koordinator_noktp' => $koordinator_noktp,
        'anggota_nama'      => $this->request->getPost('anggota_nama'),
        'anggota_noktp'     => $this->request->getPost('anggota_noktp'),
        'tgl_lahir'         => $this->request->getPost('tgl_lahir'),
        'alamat'            => $this->request->getPost('alamat'),

        'foto'              => $namaFoto,
        'ktp'               => $namaKtp,
        'kk'                => $namaKK,

        'status'            => 0,
        'created_at'        => date('Y-m-d H:i:s'),
    ]);

    $id = $db->insertID();

    return redirect()->to(base_url('register/anggota/edit/' . $id))
    ->with('success', 'Data anggota berhasil ditambahkan.');
}

public function deleteTitpar($id)
{
    $db = \Config\Database::connect();

    $titpar = $db->table('register_titpar')
    ->where('id', $id)
    ->get()
    ->getRowArray();

    if (!$titpar) {
        return redirect()->back()
        ->with('error', 'Data tidak ditemukan.');
    }

    $folders = [
        'kesanggupan'  => 'uploads/kesanggupan/',
        'keberatan'    => 'uploads/keberatan/',
        'denah'        => 'uploads/denah/',
        'kk'           => 'uploads/kk_titpar/',
        'berita_acara' => 'uploads/berita_acara/',
        'penunjukan'   => 'uploads/penunjukan/',
    ];

    foreach ($folders as $field => $folder) {
        if (!empty($titpar[$field])) {
            $file = $folder . $titpar[$field];

            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    $db->table('register_titpar')
    ->where('id', $id)
    ->delete();

    return redirect()->to(
        base_url('register/progress?nik=' . urlencode($titpar['anggota_noktp']))
    )->with('success', 'Titik parkir berhasil dihapus.');
}
public function editTitpar($id)
{
    $db = \Config\Database::connect();

    $titpar = $db->table('register_titpar')
    ->where('id', $id)
    ->get()
    ->getRowArray();

    if (!$titpar) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    $user = $db->table('register')
    ->where('anggota_noktp', $titpar['anggota_noktp'])
    ->get()
    ->getRowArray();

    return view('kta/add_titpar', [
        'user'   => $user,
        'titpar' => $titpar,
    ]);
}


public function list()
{
    $db = \Config\Database::connect();
    $keyword = $this->request->getGet('q');

    $builder = $db->table('dishub_titpar');
    $builder->select('titpar_id, titpar_namatempat, titpar_lokasi');
    $builder->where('titpar_status',3);

    if ($keyword) {
        $builder->groupStart()
        ->like('titpar_namatempat', $keyword)
        ->orLike('titpar_lokasi', $keyword)
        ->groupEnd();
    }

    $builder->orderBy('titpar_namatempat', 'ASC');
    $builder->limit(50);

    $data = $builder->get()->getResultArray();

    return $this->response->setJSON($data);
}

public function setTitpar()
{
    $db = \Config\Database::connect();

    $register_id = $this->request->getPost('register_id');
    $titpar_id   = $this->request->getPost('titpar_id');

    if (!$register_id || !$titpar_id) {
        return $this->response->setJSON([
            'status' => false,
            'message' => 'Invalid parameter'
        ]);
    }

    $db->transStart();

    // get register
    $register = $db->table('register')
    ->select('id, register_anggotaid, register_titpargrupid')
    ->where('id', $register_id)
    ->get()
    ->getRow();

    if (!$register) {
        return $this->response->setJSON([
            'status' => false,
            'message' => 'Register not found'
        ]);
    }

    $anggota_id       = $register->register_anggotaid;
    $titpargrup_id    = $register->register_titpargrupid;

    // validate titpar
    $exists = $db->table('dishub_titpar')
    ->where('titpar_id', $titpar_id)
    ->countAllResults();

    if (!$exists) {
        return $this->response->setJSON([
            'status' => false,
            'message' => 'Titpar not found'
        ]);
    }

    if ($titpargrup_id) {
        // UPDATE pivot
        $db->table('dishub_titpargrup')
        ->where('titpargrup_id', $titpargrup_id)
        ->update([
            'titpargrup_titparid'   => $titpar_id,
            'titpargrup_anggotaid'  => $anggota_id,
            'titpargrup_staanggota' => 'KOORDINATOR',
        ]);
    } else {
        // INSERT pivot
        $pivotData = [
            'titpargrup_titparid'   => $titpar_id,
            'titpargrup_anggotaid'  => $anggota_id,
            'titpargrup_staanggota' => 'KOORDINATOR',
        ];

        $db->table('dishub_titpargrup')->insert($pivotData);
        $titpargrup_id = $db->insertID();
    }

    // update register
    $db->table('register')
    ->where('id', $register_id)
    ->update([
        'register_titparid'     => $titpar_id,
        'register_titpargrupid' => $titpargrup_id,
        'updated_at'            => date('Y-m-d H:i:s')
    ]);

    $db->transComplete();

    return $this->response->setJSON([
        'status' => $db->transStatus()
    ]);
}

public function setTitpar2()
{
    $db = \Config\Database::connect();

    $register_id = $this->request->getPost('register_id');
    $titpar_id   = $this->request->getPost('titpar_id');

    // basic validation
    if (!$register_id || !$titpar_id) {
        return $this->response->setJSON([
            'status' => false,
            'message' => 'Invalid parameter'
        ]);
    }

    // optional: validate titpar exists
    $exists = $db->table('dishub_titpar')
    ->where('titpar_id', $titpar_id)
    ->countAllResults();

    if (!$exists) {
        return $this->response->setJSON([
            'status' => false,
            'message' => 'Titpar not found'
        ]);
    }

    // update register
    $updated = $db->table('register')
    ->where('id', $register_id)
    ->update([
        'register_titparid' => $titpar_id,
        'updated_at' => date('Y-m-d H:i:s')
    ]);

    return $this->response->setJSON([
        'status' => $updated ? true : false
    ]);
}

public function data()
{
    $request = service('request');

    $start  = $request->getPost('start');
    $length = $request->getPost('length');
    $search = $request->getPost('search')['value'];

    $builder = $this->db->table('register r');

    // JOIN titpar
    $builder->select('r.*');
    //$builder->join('dishub_titpar t', 't.titpar_id = r.register_titparid', 'left');

    // SEARCH
    if (!empty($search)) {
        $builder->groupStart()
        ->like('r.anggota_nama', $search)
        ->orLike('r.anggota_noktp', $search)
        ->groupEnd();
    }

    // COUNT FILTERED
    $filtered = clone $builder;
    $recordsFiltered = $filtered->countAllResults(false);

    // LIMIT + DATA
    $builder->orderBy('r.id', 'DESC')
    ->limit($length, $start);

    $data = $builder->get()->getResult();

    // TOTAL
    $total = $this->db->table('register')->countAllResults();

    return $this->response->setJSON([
        "draw"            => intval($request->getPost('draw')),
        "recordsTotal"    => $total,
        "recordsFiltered" => $recordsFiltered,
        "data"            => $data
    ]);
}



     /* =======================================================
       APPROVE ACTION
       ======================================================= */

       public function approve(){

           $db = \Config\Database::connect();
           $id = $this->request->getPost('id');

    // ambil data register
           $reg = $db->table('register')
           ->where('id', $id)
           ->get()
           ->getRow();

           if (!$reg) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ]);
        }

        $db->transStart();

    // 1. update status register
        $db->table('register')
        ->where('id', $id)
        ->update([
            'anggota_status' => 1
        ]);

        $db->transComplete();

        return $this->response->setJSON([
            'status' => true
        ]);
    }

    public function registerAnggota()
    {
        $db = \Config\Database::connect();
        $id = $this->request->getPost('id');

    // ambil data register
        $reg = $db->table('register')
        ->where('id', $id)
        ->get()
        ->getRow();

        if (!$reg) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ]);
        }

        $db->transStart();

    // 1. update status register
        $db->table('register')
        ->where('id', $id)
        ->update([
            'anggota_status' => 1
        ]);

    // 2. generate anggota_newnoreg
        $last = $db->table('dishub_anggota')
        ->selectMax('anggota_newnoreg')
        ->get()
        ->getRow();

        $lastNoreg = $last->anggota_newnoreg ?? '000000';
        $newNoreg  = str_pad(((int)$lastNoreg) + 1, 6, '0', STR_PAD_LEFT);

    // 3. insert anggota
        $db->table('dishub_anggota')->insert([
            'anggota_newnoreg' => $newNoreg,
            'anggota_nama'     => $reg->anggota_nama,
            'anggota_noktp'    => $reg->anggota_noktp,
            'anggota_alamat'   => $reg->anggota_alamat,
            'anggota_tgllhr'   => $reg->anggota_tgllhr,
            'anggota_foto'     => $reg->anggota_foto,
            'created_at'       => date('Y-m-d H:i:s')
        ]);

        $anggota_id = $db->insertID();

    // 4. update register -> link anggota
        $db->table('register')
        ->where('id', $id)
        ->update([
            'register_anggotaid' => $anggota_id
        ]);

        $db->transComplete();

        return $this->response->setJSON([
            'status' => true
        ]);
    }

     /* =======================================================
       REJECT ACTION
       ======================================================= */
       public function reject()
       {
        $id = $this->request->getPost('id');

        $this->db->table('register')
        ->where('id', $id)
        ->update(['anggota_status' => 2]);

        return $this->response->setJSON(["status" => "success"]);
    }


    //API Mobile Pendaftaran Baru

    public function pendaftaran()
    {
        return view('kta/pendaftaran');
    }

    public function pendaftaransave()
    {
        helper(['form']);

        $rules = [
            'anggota_nama'  => 'required',
            'hp'            => 'required',
            'anggota_noktp' => 'required|min_length[16]|max_length[16]',
            'anggota_alamat'=> 'required',
            'anggota_tgllhr'=> 'required',
            'anggota_foto'  => 'uploaded[anggota_foto]|is_image[anggota_foto]',
            'foto_ktp'      => 'uploaded[foto_ktp]|is_image[foto_ktp]',
            'kk'            => 'uploaded[kk]|is_image[kk]'
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
            ->withInput()
            ->with('errors', $this->validator->getErrors());
        }

        $db  = \Config\Database::connect();
        $nik = $this->request->getPost('anggota_noktp');

        // cek pending saja
        if ($db->table('register')
            ->where('anggota_noktp', $nik)
            ->countAllResults() > 0) {
            return redirect()->back()
        ->withInput()
        ->with('error', 'NIK Sudah Terdaftar, tidak dapat mendaftarakan NIK yang sama.');
    }

        // upload
    $fotoSelfie = $this->request->getFile('anggota_foto');
    $fotoKtp    = $this->request->getFile('foto_ktp');
    $fotoKK  = $this->request->getFile('kk');

    $namaSelfie = $fotoSelfie->getRandomName();
    $namaKtp    = $fotoKtp->getRandomName();
    $namaKK     = $fotoKK->getRandomName();

    $fotoSelfie->move('uploads/anggota_foto', $namaSelfie);
    $fotoKtp->move('uploads/foto_ktp', $namaKtp);
    $fotoKK->move('uploads/kk',$namaKK);

        // insert
    $db->table('register')->insert([
        'anggota_nama'   => $this->request->getPost('anggota_nama'),
        'anggota_noktp'  => $nik,
        'hp'    => $this->request->getPost('hp'),
        'register_va'    => $this->request->getPost('register_va'),
        'anggota_alamat' => $this->request->getPost('anggota_alamat'),
        'anggota_tgllhr' => $this->request->getPost('anggota_tgllhr'),
        'anggota_foto'   => $namaSelfie,
        'foto_ktp'       => $namaKtp,
        'kk'     => $namaKK,
        'anggota_status' => 0,
        'created_at'     => date('Y-m-d H:i:s'),
        'baru'           => 1
    ]);

    return redirect()->to('/pendaftaran')
    ->with('success', 'Pendaftaran berhasil, login dengan NIK untuk cek progress');
}


public function saveCatatan()
{
    $id = $this->request->getPost('id');
    $catatan = $this->request->getPost('catatan');

    $db = \Config\Database::connect();

    $db->table('register')
    ->where('id', $id)
    ->update([
        'catatan' => $catatan
    ]);

    return $this->response->setJSON([
        'status' => true,
        'message' => 'Catatan berhasil disimpan'
    ]);
}


public function edit()
{
    $nik = $this->request->getGet('nik');
    $db  = \Config\Database::connect();

    if (!$nik) {
        return redirect()->to('/register')->with('error', 'NIK tidak ada');
    }

    $data = $db->table('register')
    ->where('anggota_noktp', $nik)
    ->orderBy('id', 'DESC')
    ->get()
    ->getRow();

    if (!$data) {
        return redirect()->to('/register')->with('error', 'Data tidak ditemukan');
    }

    return view('kta/edit_register', ['data' => $data]);
}

public function login()
{
    $db = \Config\Database::connect();
    $nik = $this->request->getPost('anggota_noktp');

        // Query Builder: SELECT * FROM pendaftaran WHERE anggota_noktp = ?
    $user = $db->table('register')
    ->where('anggota_noktp', $nik)
    ->get()
    ->getRowArray();

    if ($user) {
            //return view('kta/check_progress', ['user' => $user]);
        return redirect()->to(base_url('register/progress?nik=' . $nik));
    } else {
        session()->setFlashdata('error', 'NIK tidak terdaftar.');
        return redirect()->to(base_url('pendaftaran'));
    }
}

    /**
     * Logic for User to Update/Complete Data
     */

    public function progress()
    {
        $db = \Config\Database::connect();
        $nik = $this->request->getPost('anggota_noktp');

        if (!$nik) {
            return redirect()->to(base_url('pendaftaran'))->with('error', 'Masukkan NIK terlebih dahulu.');
        }

        $user = $db->table('register')
        ->where('anggota_noktp', $nik)
        ->get()
        ->getRowArray();

        if (!$user) {
            return redirect()
            ->to(base_url('pendaftaran'))
            ->with('error', 'NIK tidak ditemukan.');
        }

        $titpar = $db->table('register_titpar')
        ->where('anggota_noktp', $nik)
        ->get()
        ->getResultArray();

        $anggota = $db->table('register_anggota')
        ->where('koordinator_noktp', $nik)
        ->get()
        ->getResultArray();

        return view('kta/check_progress', compact('user', 'titpar', 'anggota'));
}

public function update()
{
    $db = \Config\Database::connect();
    $id = $this->request->getPost('id');
    $nik = $this->request->getPost('anggota_noktp');
    
    $data = [
        'anggota_nama'   => $this->request->getPost('anggota_nama'),
        'hp'             => $this->request->getPost('hp'),
        'anggota_alamat' => $this->request->getPost('anggota_alamat'),
        'anggota_tgllhr' => $this->request->getPost('anggota_tgllhr'),
        'anggota_status' => 0
    ];

    // List of fields that are files
    $fileFields = ['kesanggupan', 'keberatan', 'denah', 'kk', 'anggota_foto', 'foto_ktp'];
    
    foreach ($fileFields as $fieldName) {
        $file = $this->request->getFile($fieldName);
        
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            
            // Move to: public/uploads/{fieldname}/filename.jpg
            $file->move(FCPATH . 'uploads/' . $fieldName . '/', $newName); 
            
            // Save path to DB: "kesanggupan/filename.jpg"
            $data[$fieldName] = $newName;
        }
    }

    $db->table('register')->where('id', $id)->update($data);

    // Refresh data and return view
    $user = $db->table('register')->where('id', $id)->get()->getRowArray();
    session()->setFlashdata('success', 'Berkas berhasil diperbarui.');
    return redirect()->to(base_url('register/progress?nik=' . $nik));
    //return view('kta/check_progress', ['user' => $user]);
}

public function updateOld()
{
    $db = \Config\Database::connect();
    $id = $this->request->getPost('id');
    
    // 1. Prepare only text data first
    $data = [
        'anggota_nama'   => $this->request->getPost('anggota_nama'),
        'hp'             => $this->request->getPost('hp'),
        'anggota_alamat' => $this->request->getPost('anggota_alamat'),
        'anggota_tgllhr' => $this->request->getPost('anggota_tgllhr'),
    ];

    // 2. Handle ALL File Uploads (kesanggupan, keberatan, denah, kk)
    $fileFields = ['kesanggupan', 'keberatan', 'denah', 'kk'];
    
    foreach ($fileFields as $fieldName) {
        $file = $this->request->getFile($fieldName);
        
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            // Move to public/uploads/documents/ for better organization
            $file->move(FCPATH . 'uploads/$fileFields/', $newName); 
            $data[$fieldName] = $newName;
        }
    }

    // 3. Update database
    $db->table('register')->where('id', $id)->update($data);

    // 4. Refresh data for view
    $user = $db->table('register')->where('id', $id)->get()->getRowArray();
    session()->setFlashdata('success', 'Berkas berhasil diperbarui.');
    
    return view('kta/check_progress', ['user' => $user]);
}

public function new()
{
    $validation = \Config\Services::validation();
    $validation->setRules([
        'anggota_nama'  => 'required',
        'anggota_noktp' => 'required|min_length[16]|max_length[16]',
        'anggota_alamat'=> 'required',
        'anggota_tgllhr'=> 'required',
        'anggota_foto'  => 'uploaded[anggota_foto]|is_image[anggota_foto]',
        'foto_ktp'      => 'uploaded[foto_ktp]|is_image[foto_ktp]'
    ]);

    if (! $validation->withRequest($this->request)->run()) {
        return $this->respond([
            'status' => 'error',
            'errors' => $validation->getErrors()
        ], 400);
    }

    // 1. Prevent duplicate NIK
    $nik = $this->request->getPost('anggota_noktp');

    // Check in verified anggota
    $exists = $this->db->table('dishub_anggota')
    ->where('anggota_noktp', $nik)
    ->get()
    ->getRow();

    if ($exists) {
        return $this->respond([
            'status' => 'error',
            'message' => 'NIK sudah terdaftar sebagai anggota.'
        ], 409);
    }

    // Check in pending new register
    $pending = $this->db->table('register')
    ->where('anggota_noktp', $nik)
    ->where('baru', 1)
    ->where('anggota_status', 0)
    ->get()
    ->getRow();

    if ($pending) {
        return $this->respond([
            'status' => 'error',
            'message' => 'NIK sedang dalam proses pendaftaran baru.'
        ], 409);
    }

    // 2. Upload files
    $fotoSelfieFile = $this->request->getFile('anggota_foto');
    $fotoKtpFile    = $this->request->getFile('foto_ktp');

    $fotoSelfieName = $fotoSelfieFile->getRandomName();
    $fotoKtpName    = $fotoKtpFile->getRandomName();

    $fotoSelfieFile->move(ROOTPATH . 'public/uploads/anggota_foto', $fotoSelfieName);
    $fotoKtpFile->move(ROOTPATH . 'public/uploads/foto_ktp', $fotoKtpName);

    // 3. Insert
    $data = [
        'anggota_nama'   => $this->request->getPost('anggota_nama'),
        'anggota_noktp'  => $nik,
        'register_va'    => $this->request->getPost('register_va'),
        'anggota_alamat' => $this->request->getPost('anggota_alamat'),
        'anggota_tgllhr' => $this->request->getPost('anggota_tgllhr'),
        'anggota_foto'   => $fotoSelfieName,
        'foto_ktp'       => $fotoKtpName,
        'anggota_status' => 0,
        'created_at'     => date('Y-m-d H:i:s'),
        'baru'           => 1
    ];

    $this->db->table('register')->insert($data);
    $id = $this->db->insertID();

    return $this->respond([
        'status' => 'success',
        'message' => 'Pendaftaran berhasil, menunggu verifikasi.',
        'id' => $id
    ]);
}

public function checkStatus()
{
    $nik = $this->request->getPost('nik');

    if (!$nik) {
        return $this->respond([
            'status' => false,
            'message' => 'NIK required'
        ], 400);
    }

    // 1. Check if member exists (dishub_anggota)
    $member = $this->db->table('dishub_anggota')
    ->where('anggota_noktp', $nik)
    ->get()
    ->getRow();

    if (! $member) {
        return $this->respond([
            'status' => false,
            'registered' => false,
            'message' => 'NIK belum terdaftar sebagai anggota'
        ]);
    }

    // 2. Check if there is a PENDING renewal in register
    $pending = $this->db->table('register')
    ->where('anggota_noktp', $nik)
        ->where('baru', 0)          // renewal
        ->where('anggota_status', 0) // pending
        ->get()
        ->getRow();

        if ($pending) {
            return $this->respond([
                'status' => true,
                'registered' => true,
                'pendingRenewal' => true,
                'message' => 'Anda sudah mengajukan perpanjangan dan sedang menunggu verifikasi.',
                'submitted_at' => $pending->created_at,
                'register_id' => $pending->id
            ]);
        }

    // 3. If no pending → allow renewal
        return $this->respond([
            'status' => true,
            'registered' => true,
            'pendingRenewal' => false,
            'message' => 'Anda dapat melakukan perpanjangan sekarang.'
        ]);
    }

    public function detailAnggota($anggota_id){

    // $anggota_id = $this->request->getPost('anggota_id');

        $member = $this->db->table('dishub_anggota')
        ->select('*')
        ->where('anggota_id', $anggota_id)
        ->get()
        ->getResult();

        echo json_encode($member);
    }


    public function renew()
    {
        $nik = $this->request->getPost('anggota_noktp');

        if (!$nik) {
            return $this->respond([
                'status' => false,
                'message' => 'NIK wajib diisi'
            ], 400);
        }

    // 1. Check if member exists in dishub_anggota
        $member = $this->db->table('dishub_anggota')
        ->where('anggota_noktp', $nik)
        ->get()
        ->getRow();

        if (! $member) {
            return $this->respond([
                'status' => false,
                'message' => 'NIK tidak ditemukan dalam data anggota'
            ], 404);
        }

    // 2. Check if there is an existing pending renewal
        $pending = $this->db->table('register')
        ->where('anggota_noktp', $nik)
        ->where('baru', 0)              // renewal
        ->where('anggota_status', 0)    // still pending
        ->get()
        ->getRow();

        if ($pending) {
            return $this->respond([
                'status' => true,
                'message' => 'Anda sudah mengajukan perpanjangan dan sedang diverifikasi',
                'pending_id' => $pending->id,
                'submitted_at' => $pending->created_at
            ]);
        }

    // 3. Validate required images
        $validation = \Config\Services::validation();
        $validation->setRules([
            'anggota_foto' => 'uploaded[anggota_foto]|is_image[anggota_foto]',
            'foto_ktp'     => 'uploaded[foto_ktp]|is_image[foto_ktp]',
        ]);

        if (! $validation->withRequest($this->request)->run()) {
            return $this->respond([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validation->getErrors()
            ], 400);
        }

    // 4. Upload files
        $fotoSelfie = $this->request->getFile('anggota_foto');
        $fotoKtp    = $this->request->getFile('foto_ktp');

        $selfieName = $fotoSelfie->getRandomName();
        $ktpName    = $fotoKtp->getRandomName();

        $fotoSelfie->move(ROOTPATH . 'public/uploads/anggota_foto/', $selfieName);
        $fotoKtp->move(ROOTPATH . 'public/uploads/foto_ktp/', $ktpName);

    // 5. Insert renewal into "register" table
        $data = [
            'anggota_nama'   => $member->anggota_nama,
            'anggota_noktp'  => $member->anggota_noktp,
            'anggota_alamat' => $member->anggota_alamat,
            'anggota_tgllhr' => $member->anggota_tgllhr,

            'anggota_foto'   => $selfieName,
            'foto_ktp'       => $ktpName,

        'anggota_status' => 0, // pending
        'baru'           => 0, // renewal
        'created_at'     => date('Y-m-d H:i:s'),
    ];

    $this->db->table('register')->insert($data);
    $renewId = $this->db->insertID();

    // 6. Success response
    return $this->respond([
        'status' => true,
        'message' => 'Pengajuan perpanjangan berhasil, menunggu verifikasi',
        'id' => $renewId
    ]);
}




public function status($id)
{
    $data = $this->db->table('register')
    ->select('id, 
        anggota_nama, 
        anggota_status, 
        anggota_foto, 
        foto_ktp, 
        created_at,
        updated_at')
    ->where('id', $id)
    ->get()
    ->getRowArray();

    if (! $data) {
        return $this->respond([
            'status' => 'error',
            'message' => 'Data tidak ditemukan.'
        ], 404);
    }

    return $this->respond([
        'status' => 'success',
        'data' => $data
    ]);
}
}



