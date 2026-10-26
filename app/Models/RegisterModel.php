<?php

namespace App\Models;

use CodeIgniter\Model;

class RegisterModel extends Model
{
    protected $table      = 'register';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'anggota_nama',
        'anggota_noktp',
        'anggota_tgllhr',
        'hp',
        'anggota_alamat',
        'anggota_foto',
        'foto_ktp',
        'kk',
        'anggota_status',
        'masa_berlaku',
        'catatan',
        'baru',
        'register_anggotaid',
        'updated_at',
        'created_at',
    ];

    protected $useTimestamps = false; // you manage timestamps manually

    // -------------------------------------------------------
    // Custom queries (business-specific finders)
    // -------------------------------------------------------

    public function findByNik(string $nik): ?array
    {
        return $this->where('anggota_noktp', $nik)->first();
    }

    public function findByNikAndTgllhr(string $nik, string $tgllhr): ?array
    {
        return $this->where('anggota_noktp', $nik)
                    ->where('anggota_tgllhr', $tgllhr)
                    ->first();
    }

    public function getFilteredList(?string $status, ?string $search): array
    {
        if ($status !== null && $status !== '') {
            $this->where('anggota_status', $status);
        }

        if ($search) {
            $this->groupStart()
                 ->like('anggota_nama', $search)
                 ->orLike('anggota_noktp', $search)
                 ->groupEnd();
        }

        return $this->orderBy('id', 'DESC')->findAll();
    }
}