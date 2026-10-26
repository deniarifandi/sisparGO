<?php

namespace App\Controllers;

use App\Models\RegisterModel;
use App\Models\RegisterTitparModel;
use App\Models\RegisterAnggotaModel;
use App\Services\UploadService;

class RegisterController extends BaseController
{
    use \App\Traits\AuthTrait;

    protected RegisterModel $registerModel;

    public function __construct()
    {
        $this->registerModel = new RegisterModel();
    }

    public function landing()
    {
        if ($this->getNik()) {
            return redirect()->to(base_url('register/progress'));
        }

        return view('register/landing');
    }

    public function register()
    {
        return view('register/register');
    }

    public function logout()
    {
        session()->remove('progress_nik');
        return redirect()->to(base_url('register'))
            ->with('success', 'Anda telah keluar.');
    }

    public function registersave()
    {
        helper(['form']);

        // Rate limit
        $cache = cache();
        $key   = 'register_' . md5($this->request->getIPAddress());
        $count = $cache->get($key) ?? 0;

        if ($count >= 50) {
            return redirect()->back()
                ->with('error', 'Terlalu banyak percobaan. Coba lagi dalam 10 menit.');
        }

        $cache->save($key, $count + 1, 600);

        // Turnstile
        // if (empty($this->request->getPost('cf-turnstile-response'))) {
        //     return redirect()->back()
        //         ->withInput()
        //         ->with('error', 'Verifikasi Turnstile gagal.');
        // }

        // Validate
        $rules = [
            'anggota_noktp'  => 'required|numeric|exact_length[16]|is_unique[register.anggota_noktp]',
            'anggota_nama'   => 'required',
            'hp'             => 'required|numeric',
            'anggota_alamat' => 'required',
            'anggota_tgllhr' => 'required',
        ];

        $messages = [
            'anggota_noktp' => [
                'required'     => 'NIK wajib diisi.',
                'numeric'      => 'NIK hanya boleh berisi angka.',
                'exact_length' => 'NIK harus terdiri dari 16 digit.',
                'is_unique'    => 'NIK sudah terdaftar.',
            ],
            'anggota_nama'   => ['required' => 'Nama lengkap wajib diisi.'],
            'hp'             => ['required' => 'Nomor HP wajib diisi.', 'numeric' => 'Nomor HP hanya boleh berisi angka.'],
            'anggota_alamat' => ['required' => 'Alamat wajib diisi.'],
            'anggota_tgllhr' => ['required' => 'Tanggal lahir wajib diisi.'],
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $data = [
            'anggota_nama'   => $this->request->getPost('anggota_nama'),
            'anggota_noktp'  => $this->request->getPost('anggota_noktp'),
            'hp'             => $this->request->getPost('hp'),
            'anggota_alamat' => $this->request->getPost('anggota_alamat'),
            'anggota_tgllhr' => $this->request->getPost('anggota_tgllhr'),
            'anggota_status' => 0,
            'baru'           => 1,
            'created_at'     => date('Y-m-d H:i:s'),
        ];

        // File uploads — images only
        $upload = new UploadService();
        $fields = ['anggota_foto', 'foto_ktp', 'kk'];

        foreach ($fields as $field) {
            $result = $upload->imagesOnly()->handle(
                $this->request->getFile($field),
                $field
            );

            if (!$result['success']) {
                return redirect()->back()->withInput()->with('error', $result['message']);
            }

            if ($result['filename']) {
                $data[$field] = $result['filename'];
            }
        }

        $this->registerModel->insert($data);

        return redirect()->to('/register/form')->with('success', 'Pendaftaran berhasil.');
    }

    public function progress()
    {
        helper(['form']);

        $nik    = preg_replace('/\D/', '', $this->request->getPost('anggota_noktp'));
        $tgllhr = $this->request->getPost('anggota_tgllhr');

        if (!$nik || strlen($nik) !== 16) {
            return redirect()->back()->with('error', 'NIK tidak valid. Pastikan 16 digit angka.');
        }

        if (!$tgllhr || !strtotime($tgllhr)) {
            return redirect()->back()->with('error', 'Tanggal lahir tidak valid.');
        }

        $user = $this->registerModel->findByNikAndTgllhr($nik, $tgllhr);

        if (!$user) {
            return redirect()->back()
                ->with('error', 'NIK dan tanggal lahir tidak cocok.');
        }

        session()->set('progress_nik', $nik);
        return redirect()->to(base_url('register/progress'));
    }

    public function progressView()
    {
        $nik = $this->requireNik();
        if (!$nik) return redirect()->to(base_url('register'));

        $user = $this->registerModel->findByNik($nik);
        if (!$user) {
            return redirect()->to(base_url('register'))->with('error', 'Data tidak ditemukan.');
        }

        $titparModel  = new RegisterTitparModel();
        $anggotaModel = new RegisterAnggotaModel();

        return view('register/progress', [
            'user'    => $user,
            'titpar'  => $titparModel->getByNik($nik),
            'anggota' => $anggotaModel->getByKoordinator($nik),
        ]);
    }

    public function update()
    {
        helper(['form']);

        $id    = $this->request->getPost('id');
        $noktp = $this->request->getPost('anggota_noktp');

        $existing = $this->registerModel
            ->where('id', $id)
            ->where('anggota_noktp', $noktp)
            ->first();

        if (!$existing) {
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        if ($existing['anggota_status'] == 1) {
            return redirect()->back()->with('error', 'Data yang sudah disetujui tidak dapat diubah.');
        }

        $rules = [
            'anggota_nama'   => 'required',
            'hp'             => 'required|numeric',
            'anggota_alamat' => 'required',
            'anggota_tgllhr' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'anggota_nama'   => $this->request->getPost('anggota_nama'),
            'hp'             => $this->request->getPost('hp'),
            'anggota_alamat' => $this->request->getPost('anggota_alamat'),
            'anggota_tgllhr' => $this->request->getPost('anggota_tgllhr'),
            'anggota_status' => 0,
            'updated_at'     => date('Y-m-d H:i:s'),
        ];

        // File uploads — images only
        $upload = new UploadService();
        $fields = ['anggota_foto', 'foto_ktp', 'kk'];

        foreach ($fields as $field) {
            $result = $upload->imagesOnly()->handle(
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

        $this->registerModel->update($id, $data);

        return redirect()->back()->with('success', 'Data berhasil diperbarui.');
    }

    public function saveCatatan()
    {
        $id      = $this->request->getPost('id');
        $catatan = $this->request->getPost('catatan');

        $this->registerModel->update($id, [
            'catatan'    => $catatan,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON(['status' => true]);
    }
}