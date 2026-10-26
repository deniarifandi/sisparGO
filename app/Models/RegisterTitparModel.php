<?php

namespace App\Models;

use CodeIgniter\Model;

class RegisterTitparModel extends Model
{
    protected $table      = 'register_titpar';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'anggota_noktp',
        'titpar_namatempat',
        'titpar_lokasi',
        'latitude',
        'longitude',
        'jam_operasional',
        'batas_parkir',
        'jenis_kendaraan',
        'kesanggupan',
        'keberatan',
        'denah',
        'kk',
        'berita_acara',
        'penunjukan',
        'foto_tampak_depan',
        'foto_tampak_samping',
        'melintang',
        'melintang_alasan',
        'macet',
        'macet_alasan',
        'sosial',
        'sosial_alasan',
        'potensi_senin',
        'potensi_selasa',
        'potensi_rabu',
        'potensi_kamis',
        'potensi_jumat',
        'potensi_sabtu',
        'potensi_minggu',
        'status',
        'catatan',
        'survey',
        'persetujuan',
        'updated_at',
        'created_at',
        'va'
    ];

    protected $useTimestamps = false;

    // -------------------------------------------------------
    // Custom queries
    // -------------------------------------------------------

    public function getByNik(string $nik): array
    {
        return $this->where('anggota_noktp', $nik)
                    ->orderBy('id', 'DESC')
                    ->findAll();
    }

    public function getOwnedById(int $id, string $nik): ?array
    {
        return $this->where('id', $id)
                    ->where('anggota_noktp', $nik)
                    ->first();
    }

    public function getWithRegister(int $id): ?array
    {
        return $this->db->table('register_titpar t')
                    ->select('t.*, r.id as register_id')
                    ->join('register r', 'r.anggota_noktp = t.anggota_noktp')
                    ->where('t.id', $id)
                    ->get()
                    ->getRowArray();
    }

    // convert checkbox array → bitmask integer
    public static function parseJenisKendaraan(array $jenis): int
    {
        $bit = 0;
        if (in_array('motor', $jenis)) $bit |= 1;
        if (in_array('mobil', $jenis)) $bit |= 2;
        if (in_array('truk',  $jenis)) $bit |= 4;
        return $bit;
    }

    public function getFullDetailForPrint(int $id): ?array
    {
        return $this->db->table('register_titpar t')
                    ->select('t.*, r.anggota_nama, r.anggota_noktp, r.anggota_alamat, r.register_anggotaid,r.anggota_tgllhr, r.hp')
                    ->join('register r', 'r.anggota_noktp = t.anggota_noktp')
                    ->where('t.id', $id)
                    ->get()
                    ->getRowArray();
    }
}