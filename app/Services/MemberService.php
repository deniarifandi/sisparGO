<?php

namespace App\Services;

use App\Models\RegisterModel;
use App\Models\DishubAnggotaModel;

class MemberService
{
    protected RegisterModel $registerModel;
    protected DishubAnggotaModel $dishubAnggotaModel;

    public function __construct()
    {
        $this->registerModel      = new RegisterModel();
        $this->dishubAnggotaModel = new DishubAnggotaModel();
    }

    /**
     * Promote a registrant into an official member.
     *
     * @return array{success: bool, message: string}
     */
    public function approveMember(int $id): array
    {
        $reg = $this->registerModel->find($id);

        // Check exists
        if (!$reg) {
            return $this->fail('Data tidak ditemukan.');
        }

        // Check not already approved
        if (!empty($reg['register_anggotaid'])) {
            return $this->fail('Anggota sudah terdaftar.');
        }

        // Check eligibility
        if ((int)$reg['anggota_status'] !== 1) {
            return $this->fail('Status anggota belum Layak.');
        }

        // Run in transaction
        $db = \Config\Database::connect();
        $db->transStart();

        // Generate noreg & insert to dishub_anggota
        $noreg = $this->dishubAnggotaModel->generateNoreg();

        $this->dishubAnggotaModel->insert([
            'anggota_newnoreg' => $noreg,
            'anggota_nama'     => $reg['anggota_nama'],
            'anggota_noktp'    => $reg['anggota_noktp'],
            'anggota_alamat'   => $reg['anggota_alamat'],
            'anggota_tgllhr'   => $reg['anggota_tgllhr'],
            'anggota_foto'     => $reg['anggota_foto'],
            'created_at'       => date('Y-m-d H:i:s'),
        ]);

        $anggotaId = $db->insertID();

        // Link back to register
        $this->registerModel->update($id, [
            'register_anggotaid' => $anggotaId,
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->fail('Gagal mendaftarkan anggota. Silakan coba lagi.');
        }

        return $this->ok();
    }

    // -------------------------------------------------------
    // Response helpers
    // -------------------------------------------------------

    private function ok(): array
    {
        return ['success' => true, 'message' => ''];
    }

    private function fail(string $message): array
    {
        return ['success' => false, 'message' => $message];
    }
}