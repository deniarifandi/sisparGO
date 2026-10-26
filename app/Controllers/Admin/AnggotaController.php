<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\RegisterAnggotaModel;
use App\Services\UploadService;

class AnggotaController extends BaseController
{
    protected RegisterAnggotaModel $anggotaModel;

    public function __construct()
    {
        $this->anggotaModel = new RegisterAnggotaModel();
    }

    public function detail(int $id)
    {
        $anggota = $this->anggotaModel->getDetail($id);

        if (!$anggota) {
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        return view('register/detail_anggota', ['anggota' => $anggota]);
    }

    public function update(int $id)
    {
        $this->anggotaModel->update($id, [
            'anggota_nama' => $this->request->getPost('anggota_nama'),
            'tgl_lahir'    => $this->request->getPost('tgl_lahir'),
            'alamat'       => $this->request->getPost('alamat'),
            'status'       => $this->request->getPost('status'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);

        return redirect()->back()->with('success', 'Data anggota berhasil diperbarui.');
    }

    public function upload(string $field)
    {
        $allowed = ['foto_ktp', 'kk', 'anggota_foto'];

        if (!in_array($field, $allowed)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Field tidak valid.']);
        }

        $id  = $this->request->getPost('id');
        $row = $this->anggotaModel->find($id);

        if (!$row) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data tidak ditemukan.']);
        }

        $upload = new UploadService();
        $result = $upload->handle($this->request->getFile($field), $field, $row[$field] ?? '');

        if (!$result['success']) {
            return $this->response->setJSON(['status' => false, 'message' => $result['message']]);
        }

        $this->anggotaModel->update($id, [
            $field       => $result['filename'],
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON(['status' => true, 'message' => 'Upload berhasil.']);
    }

    public function updateFoto()
    {
        $id = $this->request->getPost('id');

        $upload = new UploadService();

        $fields = ['foto_ktp','anggota_foto','kk'];

        $data = [
            'updated_at'     => date('Y-m-d H:i:s'),
        ];


        foreach ($fields as $field) {
            $result = $upload->handle(
                $this->request->getFile($field),
                $field,
                $existing[$field] ?? ''
            );

            if (!$result['success']) {
                return redirect()->back()->withInput()->with('error', $result['message']);
            }

            if ($result['filename']) {
                $data[$field] = $result['filename'];
                
            }
        }


        $this->anggotaModel->update($id, $data);

        return redirect()->to(base_url('admin/anggota/detail/' . $id))
            ->with('success', 'Data berhasil diperbarui.');
    }
}