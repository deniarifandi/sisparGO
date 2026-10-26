<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\RegisterModel;
use App\Models\RegisterAnggotaModel;
use App\Models\RegisterTitparModel;
use App\Services\MemberService;
use App\Services\UploadService;

class RegisterController extends BaseController
{
    protected RegisterModel $registerModel;
    protected MemberService $memberService;

    public function __construct()
    {
        $this->registerModel = new RegisterModel();
        $this->memberService = new MemberService();
    }

    public function index()
    {
        $status = $this->request->getGet('status');
        $search = $this->request->getGet('search');

        return view('register/admin', [
            'list' => $this->registerModel->getFilteredList($status, $search),
        ]);
    }

    public function detail(int $id)
    {
        $register = $this->registerModel->find($id);
        if (!$register) {
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        $anggota = null;
        // if (!empty($register['register_anggotaid'])) {
        //     $anggota = (new RegisterAnggotaModel())->find($register['register_anggotaid']);
        // }

        $anggota = (new RegisterAnggotaModel())->getByKoordinator($register['anggota_noktp']);
       
        $titpars = (new RegisterTitparModel())->getByNik($register['anggota_noktp']);

        return view('register/admin_detail', compact('register', 'anggota', 'titpars'));
    }

    public function updateInformasi()
    {
        $id = $this->request->getPost('id');

        $this->registerModel->update($id, [
            'anggota_nama'   => $this->request->getPost('anggota_nama'),
            'anggota_tgllhr' => $this->request->getPost('anggota_tgllhr'),
            'hp'             => $this->request->getPost('hp'),
            'anggota_alamat' => $this->request->getPost('anggota_alamat'),
            'masa_berlaku'   => $this->request->getPost('masa_berlaku') ?: null,
            'anggota_status' => $this->request->getPost('anggota_status'),
            'catatan'        => $this->request->getPost('catatan'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(base_url('admin/register/detail/' . $id))
            ->with('success', 'Data berhasil diperbarui.');
    }

    public function updateFoto()
    {
        $id = $this->request->getPost('id');

        $upload = new UploadService();
        $fields = ['anggota_foto', 'foto_ktp', 'kk'];

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


        $this->registerModel->update($id, $data);

        return redirect()->to(base_url('admin/register/detail/' . $id))
            ->with('success', 'Data berhasil diperbarui.');
    }

    public function approveMember()
    {
        $id     = $this->request->getPost('id');
        $result = $this->memberService->approveMember((int)$id);

        if (!$result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        return redirect()->back()->with('success', 'ID Anggota berhasil dibuat.');
    }

    public function data()
    {
        $draw   = $this->request->getPost('draw');
        $start  = (int)$this->request->getPost('start');
        $length = (int)$this->request->getPost('length');
        $search = $this->request->getPost('search')['value'] ?? '';

        $builder = $this->registerModel->builder();

        // Search
        if ($search) {
            $builder->groupStart()
                    ->like('anggota_nama', $search)
                    ->orLike('anggota_noktp', $search)
                    ->groupEnd();
        }

        $total    = $this->registerModel->countAll();
        $filtered = $builder->countAllResults(false);

        $data = $builder->orderBy('id', 'DESC')
                        ->limit($length, $start)
                        ->get()
                        ->getResultArray();

        return $this->response->setJSON([
            'draw'            => $draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $data,
        ]);
    }

      public function printPenunjukan(int $id)
    {
        $register = $this->registerModel->find($id);

        if (!$register) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $titparModel  = new \App\Models\RegisterTitparModel();
        $anggotaModel = new \App\Models\RegisterAnggotaModel();

        $titpars = $titparModel->getByNik($register['anggota_noktp']);
        $anggota = $anggotaModel->getByKoordinator($register['anggota_noktp']);

        return view('register/print_penunjukan', [
            'register' => $register,
            'titpars'  => $titpars,
            'anggota'  => $anggota,
        ]);
    }
}