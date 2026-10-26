<?php

namespace App\Controllers;

use App\Models\RegisterModel;
use App\Models\RegisterAnggotaModel;
use App\Services\UploadService;

class AnggotaController extends BaseController
{
    use \App\Traits\AuthTrait;

    protected RegisterModel $registerModel;
    protected RegisterAnggotaModel $anggotaModel;

    public function __construct()
    {
        $this->registerModel = new RegisterModel();
        $this->anggotaModel  = new RegisterAnggotaModel();
    }

    public function add()
    {
        $nik = $this->requireNik();
        if (!$nik) return redirect()->to(base_url('register'));

        $user = $this->registerModel->findByNik($nik);
        if (!$user) {
            return redirect()->to(base_url('register/progress'))->with('error', 'Data koordinator tidak ditemukan.');
        }

        return view('register/anggota_add', compact('user'));
    }

        public function save(int $id = 0)
        {
    
        helper(['form']);

        $nik = $this->requireNik();
        if (!$nik) return redirect()->to(base_url('register'));

        $id = $id ?: (int)$this->request->getPost('id');

        $rules = [
            'anggota_nama'  => 'required',
            'anggota_noktp' => 'required|numeric|exact_length[16]',
            'tgl_lahir'     => 'required',
            'alamat'        => 'required',
        ];

        if (!$id) {
            $rules['anggota_noktp'] .= '|is_unique[register_anggota.anggota_noktp]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'koordinator_noktp' => $nik,
            'anggota_nama'      => $this->request->getPost('anggota_nama'),
            'anggota_noktp'     => $this->request->getPost('anggota_noktp'),
            'tgl_lahir'         => $this->request->getPost('tgl_lahir'),
            'alamat'            => $this->request->getPost('alamat'),
        ];

        $old = $id ? $this->anggotaModel->getOwnedById((int)$id, $nik) : [];

        if ($id && !$old) {
            return redirect()->to(base_url('register/progress'))->with('error', 'Data tidak ditemukan.');
        }

        // File uploads
        $upload = new UploadService();
        $fields = ['anggota_foto', 'kk', 'foto_ktp'];

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

        if ($id) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $this->anggotaModel->where('koordinator_noktp', $nik)->update($id, $data);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->anggotaModel->insert($data);
            $id = $this->anggotaModel->getInsertID();
        }

        return redirect()->to(base_url('register/anggota/edit/' . $id))
            ->with('success', 'Data anggota berhasil disimpan.');
    }

    public function edit(int $id = null)
    {
        if (!$id) return redirect()->to(base_url('register/progress'));

        $nik = $this->requireNik();
        if (!$nik) return redirect()->to(base_url('register'));

        $anggota = $this->anggotaModel->getOwnedById($id, $nik);
        if (!$anggota) {
            return redirect()->to(base_url('register/progress'))->with('error', 'Data tidak ditemukan.');
        }

        $user = $this->registerModel->findByNik($nik);

        return view('register/anggota_add', compact('anggota', 'user'));
    }

    public function delete(int $id = null)
    {
        if (!$id) return redirect()->to(base_url('register/progress'));

        $nik = $this->requireNik();
        if (!$nik) return redirect()->to(base_url('register'));

        $anggota = $this->anggotaModel->getOwnedById($id, $nik);
        if (!$anggota) {
            return redirect()->to(base_url('register/progress'))->with('error', 'Data tidak ditemukan.');
        }

        $upload = new UploadService();
        foreach (['foto_ktp', 'kk', 'anggota_foto'] as $field) {
            if (!empty($anggota[$field])) {
                $upload->delete('anggota_' . $field, $anggota[$field]);
            }
        }

        $this->anggotaModel->where('koordinator_noktp', $nik)->delete($id);

        return redirect()->to(base_url('register/progress'))->with('success', 'Anggota berhasil dihapus.');
    }
}