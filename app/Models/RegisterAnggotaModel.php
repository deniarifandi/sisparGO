<?php

namespace App\Models;

use CodeIgniter\Model;

class RegisterAnggotaModel extends Model
{
    protected $table      = 'register_anggota';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'koordinator_noktp',
        'anggota_nama',
        'anggota_noktp',
        'tgl_lahir',
        'alamat',
        'foto_ktp',
        'kk',
        'anggota_foto',
        'status',
        'updated_at',
        'created_at',
    ];

    protected $useTimestamps = false;

    // -------------------------------------------------------
    // Custom queries
    // -------------------------------------------------------

    public function getByKoordinator(string $nik): array
    {
        return $this->where('koordinator_noktp', $nik)->findAll();
    }

    public function getOwnedById(int $id, string $nik): ?array
    {
        return $this->where('id', $id)
                    ->where('koordinator_noktp', $nik)
                    ->first();
    }

    public function getDetail($id)
    {
        return $this->db->table('register_anggota a')
            ->select('a.*, r.id as register_id')
            ->join('register r', 'r.anggota_noktp = a.koordinator_noktp', 'left')
            ->where('a.id', $id)
            ->get()
            ->getRowArray();
    }
}