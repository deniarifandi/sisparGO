<?php

namespace App\Controllers;

use App\Models\RegisterModel;
use App\Models\RegisterTitparModel;
use App\Services\UploadService;

class TitparController extends BaseController
{
    use \App\Traits\AuthTrait;

    protected RegisterModel $registerModel;
    protected RegisterTitparModel $titparModel;

    public function __construct()
    {
        $this->registerModel = new RegisterModel();
        $this->titparModel   = new RegisterTitparModel();
    }

    public function add()
    {
        $nik = $this->requireNik();
        if (!$nik) return redirect()->to(base_url('register'));

        $user = $this->registerModel->findByNik($nik);
        if (!$user) {
            return redirect()->to(base_url('register'))->with('error', 'Data anggota tidak ditemukan.');
        }

        return view('register/titpar_add', [
            'user'   => $user,
            'titpar' => [],       // ← empty array, not null
            'isEdit' => false,    // ← explicit
        ]);
    }

    public function save(int $id = 0)
    {
        helper(['form']);

        $nik = $this->requireNik();
        if (!$nik) return redirect()->to(base_url('register'));

          $id = $id ?: (int)$this->request->getPost('id');

        $rules = [
            'titpar_namatempat' => 'required',
            'titpar_lokasi'     => 'required',
            'latitude'          => 'required|decimal',
            'longitude'         => 'required|decimal',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'anggota_noktp'     => $nik,
            'titpar_namatempat' => $this->request->getPost('titpar_namatempat'),
            'titpar_lokasi'     => $this->request->getPost('titpar_lokasi'),
            'jam_operasional'   => $this->request->getPost('jam_operasional'),
            'batas_parkir'      => $this->request->getPost('batas_parkir'),
            'jenis_kendaraan'   => RegisterTitparModel::parseJenisKendaraan(
                                        (array)$this->request->getPost('jenis_kendaraan')
                                   ),
            'latitude'          => $this->request->getPost('latitude'),
            'longitude'         => $this->request->getPost('longitude'),
        ];

        // Get old data if editing
        $old = $id ? $this->titparModel->getOwnedById((int)$id, $nik) : [];

        if ($id && !$old) {
            return redirect()->to(base_url('register/progress'))->with('error', 'Data tidak ditemukan.');
        }

        // File uploads
        $upload = new UploadService();
        $fields = ['kesanggupan', 'keberatan', 'denah', 'berita_acara', 'penunjukan'];

        foreach ($fields as $field) {
            $result = $upload->handle(
                $this->request->getFile($field),
                $field,
                $old[$field] ?? ''
            );

            if (!$result['success']) {
                return redirect()->back()->withInput()->with('error', $result['message']);
            }

            if ($result['filename']) {
                $data[$field] = $result['filename'];
            }
        }

        // Insert or update
        if ($id) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $this->titparModel->where('anggota_noktp', $nik)->update($id, $data);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->titparModel->insert($data);
            $id = $this->titparModel->getInsertID();
        }

        return redirect()->to(base_url('register/titpar/edit/' . $id))
            ->with('success', 'Data titik parkir berhasil disimpan.');
    }

    public function edit(int $id = null)
    {
        if (!$id) return redirect()->to(base_url('register/progress'));

        $nik = $this->requireNik();
        if (!$nik) return redirect()->to(base_url('register'));

        $titpar = $this->titparModel->getOwnedById($id, $nik);
        if (!$titpar) {
            return redirect()->to(base_url('register/progress'))->with('error', 'Data tidak ditemukan.');
        }

        $user = $this->registerModel->findByNik($nik);

        return view('register/titpar_add', [
            'user'   => $user,
            'titpar' => $titpar,
            'isEdit' => true,    // ← explicit
        ]);
    }

    public function delete(int $id = null)
    {
        if (!$id) return redirect()->to(base_url('register/progress'));

        $nik = $this->requireNik();
        if (!$nik) return redirect()->to(base_url('register'));

        $titpar = $this->titparModel->getOwnedById($id, $nik);
        if (!$titpar) {
            return redirect()->to(base_url('register/progress'))->with('error', 'Data tidak ditemukan.');
        }

        // Delete related files
        $upload = new UploadService();
        $fields = ['kesanggupan', 'keberatan', 'denah', 'berita_acara', 'penunjukan'];

        foreach ($fields as $field) {
            if (!empty($titpar[$field])) {
                $upload->delete($field, $titpar[$field]);
            }
        }

        $this->titparModel->where('anggota_noktp', $nik)->delete($id);

        return redirect()->to(base_url('register/progress'))->with('success', 'Titik parkir berhasil dihapus.');
    }
}