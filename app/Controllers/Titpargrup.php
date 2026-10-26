<?php 

namespace App\Controllers;

use App\Libraries\DataTable;
use DB;

class Titpargrup extends BaseController
{
    public $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        return view('titpargrup/index');
    }

    public function data()
{
    $db = db_connect();

    $builder = $db->table('dishub_titpargrup')
    ->select('
        dishub_titpargrup.*, 
        dishub_anggota.anggota_nama, 
        koordinator.anggota_nama AS koordinator_nama,
        dishub_titpar.titpar_namatempat
    ')
    ->join(
        'dishub_anggota',
        'dishub_anggota.anggota_id = dishub_titpargrup.titpargrup_anggotaid'
    )
    ->join(
        'dishub_anggota AS koordinator',
        'koordinator.anggota_id = dishub_titpargrup.titpargrup_namashift',
        'left'
    )
    ->join(
        'dishub_titpar',
        'dishub_titpar.titpar_id = dishub_titpargrup.titpargrup_titparid'
    )
    ->where('titpargrup_status !=', 1);

    $dataRaw = $builder->get()->getResultArray();

    $hariMap = [
        1  => 'Senin',
        2  => 'Selasa',
        4  => 'Rabu',
        8  => 'Kamis',
        16 => 'Jumat',
        32 => 'Sabtu',
        64 => 'Minggu'
    ];

    $formattedData = [];
    foreach ($dataRaw as $row) {
        // 1. Nama + Anggota ID
        $row['nama_anggota_custom'] = esc($row['anggota_nama']) . ' <span class="text-muted">(' . esc($row['titpargrup_anggotaid']) . ')</span>';

        // 2. Nama Tempat + Titpar ID
        $row['nama_tempat_custom'] = esc($row['titpar_namatempat']) . ' <span class="text-muted">(' . esc($row['titpargrup_titparid']) . ')</span>';

        // 3. Nama Koordinator
        $row['koordinator_custom'] = !empty($row['koordinator_nama'])
    ? esc($row['koordinator_nama']) . ' <span class="text-muted">(' . esc($row['titpargrup_namashift']) . ')</span>'
    : '-';

        // 4. Shift Hari & Jam
        $shiftHariVal = (int) $row['titpargrup_shifthari'];
        $hariTeks = [];
        foreach ($hariMap as $bit => $namaHari) {
            if ($shiftHariVal & $bit) {
                $hariTeks[] = $namaHari;
            }
        }
        $stringHari = !empty($hariTeks) ? implode(', ', $hariTeks) : '-';

        $jamMulai   = !empty($row['titpargrup_shiftjammulai']) ? date('H:i', strtotime($row['titpargrup_shiftjammulai'])) : '';
        $jamSelesai = !empty($row['titpargrup_shiftjamselesai']) ? date('H:i', strtotime($row['titpargrup_shiftjamselesai'])) : '';
        $stringJam  = ($jamMulai && $jamSelesai) ? "({$jamMulai} - {$jamSelesai})" : '';

        $row['shift_custom'] = trim("{$stringHari} {$stringJam}");

        $formattedData[] = $row;
    }

    return $this->response->setJSON([
        'data' => $formattedData
    ]);
}

    public function create()
    {
        $anggotaList = $this->db->table('dishub_anggota')
            ->select('anggota_id, anggota_nama')
            ->where('anggota_status !=', 1)
            ->get()
            ->getResultArray();

        // Ambil data titik parkir
        $titparList = $this->db->table('dishub_titpar')
            ->select('titpar_id, titpar_namatempat, titpar_lokasi')
            ->where('titpar_status !=', 1)
            ->get()
            ->getResultArray();

        return view('titpargrup/form', [
            'anggotaList' => $anggotaList,
            'titparList'  => $titparList,
            'titpargrup'  => [] // Kosong dulu untuk form create
        ]);
    }

    public function store()
    {
        $db = \Config\Database::connect();

        // 1. Bitwise sum untuk shift hari
        $shiftHariInput = $this->request->getPost('titpargrup_shifthari');
        $shiftHariInt = is_array($shiftHariInput) ? array_sum($shiftHariInput) : 0;

        // 2. Tentukan nama shift (hanya disimpan jika statusnya ANGGOTA, selain itu NULL)
        $staAnggota = $this->request->getPost('titpargrup_staanggota');
        $namaShift  = ($staAnggota === 'ANGGOTA') ? $this->request->getPost('titpargrup_namashift') : null;

        $data = [
            'titpargrup_anggotaid'       => $this->request->getPost('titpargrup_anggotaid'),
            'titpargrup_titparid'        => $this->request->getPost('titpargrup_titparid'),
            'titpargrup_staanggota'      => $staAnggota,
            'titpargrup_namashift'       => $namaShift,
            'titpargrup_shifthari'       => $shiftHariInt,
            'titpargrup_shiftjammulai'   => $this->request->getPost('titpargrup_shiftjammulai'),
            'titpargrup_shiftjamselesai' => $this->request->getPost('titpargrup_shiftjamselesai'),
        ];

        $db->table('dishub_titpargrup')->insert($data);

        return redirect()->to(base_url('titpargrup'))->with('success', 'Data titpargrup berhasil ditambahkan.');
    }

    public function update($id)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('dishub_titpargrup');

        // 1. Bitwise sum untuk shift hari
        $shiftHariInput = $this->request->getPost('titpargrup_shifthari');
        $shiftHariInt = is_array($shiftHariInput) ? array_sum($shiftHariInput) : 0;

        // 2. Tentukan nama shift (hanya disimpan jika statusnya ANGGOTA, selain itu NULL)
        $staAnggota = $this->request->getPost('titpargrup_staanggota');
        $namaShift  = ($staAnggota === 'ANGGOTA') ? $this->request->getPost('titpargrup_namashift') : null;

        $data = [
            'titpargrup_anggotaid'       => $this->request->getPost('titpargrup_anggotaid'),
            'titpargrup_titparid'        => $this->request->getPost('titpargrup_titparid'),
            'titpargrup_staanggota'      => $staAnggota,
            'titpargrup_namashift'       => $namaShift,
            'titpargrup_shifthari'       => $shiftHariInt,
            'titpargrup_shiftjammulai'   => $this->request->getPost('titpargrup_shiftjammulai'),
            'titpargrup_shiftjamselesai' => $this->request->getPost('titpargrup_shiftjamselesai'),
        ];

        if (empty($data['titpargrup_anggotaid']) || empty($data['titpargrup_titparid'])) {
            return redirect()->back()->with('error', 'Data tidak boleh kosong')->withInput();
        }

        $builder->where('titpargrup_id', $id)->update($data);

        return redirect()->to(base_url('titpargrup'))->with('success', 'Data berhasil diperbarui');
    }

    public function edit($id)
    {
        $db = \Config\Database::connect();

        // Ambil data titpargrup by ID
        $titpargrup = $db->table('dishub_titpargrup')
            ->where('titpargrup_id', $id)
            ->get()
            ->getRowArray();

        if (!$titpargrup) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Data titpargrup tidak ditemukan");
        }

        // Ambil data anggota aktif
        $anggotaList = $db->table('dishub_anggota')
            ->select('anggota_id, anggota_nama')
            ->where('anggota_status !=', 1)
            ->get()
            ->getResultArray();

        // Ambil data titik parkir aktif
        $titparList = $db->table('dishub_titpar')
            ->select('titpar_id, titpar_namatempat, titpar_lokasi')
            ->where('titpar_status !=', 1)
            ->get()
            ->getResultArray();

        return view('titpargrup/form', [
            'anggotaList' => $anggotaList,
            'titparList'  => $titparList,
            'titpargrup'  => $titpargrup // Isi dengan data lama
        ]);
    }


    public function delete($id)
    {
        $db = \Config\Database::connect();

        $db->table('dishub_titpargrup')
            ->where('titpargrup_id', $id)
            ->update(['titpargrup_status' => 1]); // Soft delete

        return redirect()->to(base_url('titpargrup'))->with('success', 'Data anggota berhasil dinonaktifkan.');
    }
}