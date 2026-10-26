<?php 

namespace App\Controllers;

use DB;


class Anggota extends BaseController
{
    public $db;

    public function __construct()
    {
      $this->db = \Config\Database::connect();
    }

    public function index(){
         return view('anggota/index');
    }

    public function detail(int $id)
    {
        $anggota = $this->db->table('dishub_anggota')->where('anggota_id', $id)->get()->getRowArray();
        if (!$anggota) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Data anggota tidak ditemukan');
        $titpars = $this->db->table('dishub_titpargrup tg')
            ->select('t.titpar_id, t.titpar_namatempat, t.titpar_lokasi, tg.titpargrup_staanggota, tg.titpargrup_masaberlakustart, tg.titpargrup_masaberlakuend')
            ->join('dishub_titpar t', 't.titpar_id = tg.titpargrup_titparid', 'left')
            ->where('tg.titpargrup_anggotaid', $id)
            ->orderBy('t.titpar_namatempat', 'ASC')->get()->getResultArray();
        return view('anggota/detail', ['anggota' => $anggota, 'titpars' => $titpars]);
    }

    public function detailData(int $id)
    {
        $anggota = $this->db->table('dishub_anggota')->where('anggota_id', $id)->get()->getRowArray();
        if (!$anggota) return $this->response->setStatusCode(404)->setJSON(['message' => 'Data anggota tidak ditemukan']);
        $titpars = $this->db->table('dishub_titpargrup tg')
            ->select('t.titpar_id, t.titpar_namatempat, t.titpar_lokasi, t.titpar_status, t.titpar_latitude, t.titpar_longitude, t.titpar_alihfungsi, k.kecamatan_nama, k.kelurahan_nama, tg.titpargrup_id, tg.titpargrup_staanggota, tg.titpargrup_shifthari, tg.titpargrup_shiftjammulai, tg.titpargrup_shiftjamselesai, tg.titpargrup_masaberlakustart, tg.titpargrup_masaberlakuend')
            ->join('dishub_titpar t', 't.titpar_id = tg.titpargrup_titparid', 'left')
            ->join('kelurahan k', 'k.id = t.titpar_klrhnid', 'left')
            ->where('tg.titpargrup_anggotaid', $id)->orderBy('t.titpar_namatempat', 'ASC')
            ->get()->getResultArray();
        foreach ($titpars as &$titpar) {
            $titpar['anggota_titik'] = $this->db->table('dishub_titpargrup tg2')
                ->select('a.anggota_id, a.anggota_nama, tg2.titpargrup_staanggota')
                ->join('dishub_anggota a', 'a.anggota_id = tg2.titpargrup_anggotaid', 'left')
                ->where('tg2.titpargrup_titparid', $titpar['titpar_id'])
                ->orderBy('a.anggota_nama', 'ASC')->get()->getResultArray();
        }
        unset($titpar);
        return $this->response->setJSON(['anggota' => $anggota, 'titpars' => $titpars]);
    }

    public function data(){
        $db = db_connect();
        $request = $this->request->getPost();
        $columns = ['anggota_id', 'anggota_foto', 'anggota_nama', 'anggota_newnoreg', 'anggota_noktp', 'anggota_nohp', 'anggota_alamat', 'anggota_tgllhr', 'anggota_status'];
        $searchColumns = ['anggota_id', 'anggota_nama', 'anggota_newnoreg', 'anggota_noktp', 'anggota_nohp', 'anggota_alamat'];
        $summaryBase = $db->table('dishub_anggota')->where('anggota_status !=', 1);
        $summary = [
            'total' => (clone $summaryBase)->countAllResults(),
            'active' => (clone $summaryBase)->where('anggota_status', 3)->countAllResults(),
            'inactive' => (clone $summaryBase)->where('anggota_status', 2)->countAllResults(),
        ];
        $base = $db->table('dishub_anggota')->where('anggota_status !=', 1);
        $status = (string) ($request['status'] ?? '');
        if ($status === 'Aktif') $base->where('anggota_status', 3);
        elseif ($status === 'Nonaktif') $base->where('anggota_status', 2);
        $recordsTotal = (clone $base)->countAllResults();
        $filtered = clone $base;
        $search = trim((string) ($request['search']['value'] ?? ''));
        if ($search !== '') {
            $filtered->groupStart();
            foreach ($searchColumns as $i => $column) $i === 0 ? $filtered->like($column, $search) : $filtered->orLike($column, $search);
            $filtered->groupEnd();
        }
        $recordsFiltered = (clone $filtered)->countAllResults();
        $orderIndex = (int) ($request['order'][0]['column'] ?? 0);
        $orderColumn = $columns[$orderIndex] ?? 'anggota_id';
        $orderColumn = $orderColumn === 'anggota_foto' ? 'anggota_id' : $orderColumn;
        $direction = strtolower((string) ($request['order'][0]['dir'] ?? 'asc')) === 'desc' ? 'DESC' : 'ASC';
        $start = max(0, (int) ($request['start'] ?? 0));
        $length = min(100, max(1, (int) ($request['length'] ?? 25)));
        $rows = $filtered->select(implode(',', $columns))->orderBy($orderColumn, $direction)->limit($length, $start)->get()->getResultArray();
        return $this->response->setJSON(['draw' => (int) ($request['draw'] ?? 0), 'recordsTotal' => $recordsTotal, 'recordsFiltered' => $recordsFiltered, 'summary' => $summary, 'data' => $rows]);
    }

    public function resetPassword(int $id)
    {
        if ((int) session('role') >= 4) {
            return $this->response->setStatusCode(403)->setJSON(['message' => 'Anda tidak memiliki izin.']);
        }

        $db = $this->db;
        $db->transStart();
        $anggota = $db->query('SELECT anggota_id, anggota_nama FROM dishub_anggota WHERE anggota_id = ? FOR UPDATE', [$id])->getRowArray();
        if (!$anggota) {
            $db->transRollback();
            return $this->response->setStatusCode(404)->setJSON(['message' => 'Data anggota tidak ditemukan.']);
        }

        $password = \App\Libraries\RegistrationAccount::temporaryPassword();
        $updated = $db->table('dishub_anggota')->where('anggota_id', $id)->update([
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'must_change_password' => 1,
            'anggota_udate' => date('Y-m-d H:i:s'),
        ]);
        $db->transComplete();
        if (!$updated || !$db->transStatus()) {
            return $this->response->setStatusCode(500)->setJSON(['message' => 'Password gagal diperbarui.']);
        }

        return $this->response->setJSON([
            'message' => 'Password berhasil dibuat ulang.',
            'credentials' => ['user_id' => (string) $id, 'password' => $password, 'name' => $anggota['anggota_nama']],
            'csrf' => ['name' => csrf_token(), 'hash' => csrf_hash()],
        ]);
    }

    public function create(){
         return view('anggota/form', ['wilayah' => []]);
    }

    public function provinsi()
    {
        return $this->response->setJSON($this->db->table('kelurahan')->select('provinsi')->distinct()->orderBy('provinsi')->get()->getResultArray());
    }

    public function kabupaten(string $provinsi)
    {
        return $this->response->setJSON($this->db->table('kelurahan')->select('kabupaten_kota')->where('provinsi', urldecode($provinsi))->distinct()->orderBy('kabupaten_kota')->get()->getResultArray());
    }

    public function kecamatan(string $kabupaten)
    {
        return $this->response->setJSON($this->db->table('kelurahan')->select('kecamatan_id, kecamatan_nama')->where('kabupaten_kota', urldecode($kabupaten))->distinct()->orderBy('kecamatan_nama')->get()->getResultArray());
    }

    public function kelurahan(string $kecamatan)
    {
        return $this->response->setJSON($this->db->table('kelurahan')->select('id, kelurahan_nama')->where('kecamatan_id', urldecode($kecamatan))->orderBy('kelurahan_nama')->get()->getResultArray());
    }

    public function store()
     {
         $db = \Config\Database::connect();

         // Ambil nilai terakhir dari anggota_newnoreg
         $last = $db->table('dishub_anggota')
                    ->selectMax('anggota_newnoreg')
                    ->get()
                    ->getRow();

         $lastNoreg = $last->anggota_newnoreg ?? '000000'; // kalau kosong, mulai dari 000000
         $newNoreg  = str_pad(((int)$lastNoreg) + 1, 6, '0', STR_PAD_LEFT);

         if (! $this->validateAnggota()) {
             return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
         }
         $data = $this->anggotaData();
         $data['anggota_newnoreg'] = $newNoreg;
         $data['anggota_status'] = 3;
         // Manual accounts start with the legacy default, but it is never
         // stored as plaintext in the database.
         $data['password'] = password_hash('jukirmalang', PASSWORD_DEFAULT);
         if (($foto = $this->saveFoto()) !== null) $data['anggota_foto'] = $foto;
         foreach (['foto_ktp', 'foto_kk'] as $field) if (($file = $this->saveDocument($field)) !== null) $data[$field] = $file;
         $db->table('dishub_anggota')->insert($data);

        return redirect()->to(base_url('anggota'))->with('success', 'Data anggota berhasil ditambahkan.');

     }

     public function edit($id)
     {
         $db = \Config\Database::connect();

         $anggota = $db->table('dishub_anggota')
                       ->where('anggota_id', $id)
                       ->get()
                       ->getRowArray();

         if (!$anggota) {
             throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Data anggota tidak ditemukan");
         }

         $wilayah = $db->table('kelurahan')->where('id', $anggota['anggota_klrhnid'] ?? '')->get()->getRowArray() ?? [];
         return view('anggota/form', ['anggota' => $anggota, 'wilayah' => $wilayah]);
     }

     public function update($id)
     {
         $db = \Config\Database::connect();

         if (! $this->validateAnggota($id)) {
             return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
         }
         $data = $this->anggotaData();
         if (($foto = $this->saveFoto()) !== null) $data['anggota_foto'] = $foto;
         foreach (['foto_ktp', 'foto_kk'] as $field) if (($file = $this->saveDocument($field)) !== null) $data[$field] = $file;
         $db->table('dishub_anggota')->where('anggota_id', $id)->update($data);

         return redirect()->to(base_url('anggota/edit/' . $id))->with('success', 'Data anggota berhasil disimpan.');
     }

     public function delete($id)
     {
         $db = \Config\Database::connect();

         $db->table('dishub_anggota')
            ->where('anggota_id', $id)
            ->update(['anggota_status' => 2]); // soft delete

         return redirect()->to(base_url('anggota'))->with('success', 'Data anggota berhasil dinonaktifkan.');
     }

     public function undelete($id)
     {
         $db = \Config\Database::connect();

         $db->table('dishub_anggota')
            ->where('anggota_id', $id)
            ->update(['anggota_status' => 3]); // soft delete

         return redirect()->to(base_url('anggota'))->with('success', 'Data anggota berhasil dinonaktifkan.');
     }

    private function validateAnggota(?int $id = null): bool
    {
        $nikUnique = 'is_unique[dishub_anggota.anggota_noktp' . ($id ? ',anggota_id,' . $id : '') . ']';
        return $this->validate([
            'anggota_nama' => 'required|max_length[150]',
            'anggota_noktp' => 'required|numeric|exact_length[16]|' . $nikUnique,
            'anggota_nohp' => 'permit_empty|max_length[20]',
            'anggota_tgllhr' => 'permit_empty|valid_date[Y-m-d]',
            'anggota_alamat' => 'permit_empty|max_length[1000]',
            'anggota_foto' => 'permit_empty|is_image[anggota_foto]|max_size[anggota_foto,2048]|mime_in[anggota_foto,image/jpg,image/jpeg,image/png,image/webp]',
        ], [
            'anggota_nama' => ['required' => 'Nama lengkap wajib diisi.'],
            'anggota_noktp' => ['required' => 'NIK wajib diisi.', 'numeric' => 'NIK hanya boleh berisi angka.', 'exact_length' => 'NIK harus terdiri dari 16 digit.', 'is_unique' => 'NIK sudah terdaftar.'],
            'anggota_foto' => ['is_image' => 'Foto harus berupa gambar.', 'max_size' => 'Ukuran foto maksimal 2 MB.'],
        ]);
    }

    private function anggotaData(): array
    {
        return [
            'anggota_noreg' => trim((string) $this->request->getPost('anggota_noreg')),
            'anggota_newnoreg' => trim((string) $this->request->getPost('anggota_newnoreg')),
            'anggota_nama' => trim((string) $this->request->getPost('anggota_nama')),
            'anggota_noktp' => trim((string) $this->request->getPost('anggota_noktp')),
            'nomor_kk' => trim((string) $this->request->getPost('nomor_kk')),
            'anggota_nohp' => trim((string) $this->request->getPost('anggota_nohp')),
            'anggota_tgllhr' => $this->request->getPost('anggota_tgllhr') ?: null,
            'anggota_tmplhr' => trim((string) $this->request->getPost('anggota_tmplhr')),
            'anggota_jnsklm' => $this->request->getPost('anggota_jnsklm') ?: null,
            'anggota_alamat' => trim((string) $this->request->getPost('anggota_alamat')),
            'anggota_peker' => trim((string) $this->request->getPost('anggota_peker')),
            'anggota_pente' => trim((string) $this->request->getPost('anggota_pente')),
            'anggota_klrhnid' => trim((string) $this->request->getPost('anggota_klrhnid')),
            'anggota_staagamaid' => $this->request->getPost('anggota_staagamaid') ?: null,
            'anggota_status' => $this->request->getPost('anggota_status') ?: 3,
        ];
    }

    private function saveFoto(): ?string
    {
        $foto = $this->request->getFile('anggota_foto');
        if ($foto === null || $foto->getError() === UPLOAD_ERR_NO_FILE) return null;
        $namaFoto = $foto->getRandomName();
        $foto->move(FCPATH . 'uploads/anggota_foto', $namaFoto);
        return $namaFoto;
    }

    private function saveDocument(string $field): ?string
    {
        $file = $this->request->getFile($field);
        if ($file === null || $file->getError() === UPLOAD_ERR_NO_FILE || !$file->isValid()) return null;
        if (!in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'], true)) return null;
        $folder = FCPATH . 'uploads/' . $field;
        if (!is_dir($folder)) mkdir($folder, 0775, true);
        $name = $file->getRandomName();
        $file->move($folder, $name);
        return $name;
    }

    
}
