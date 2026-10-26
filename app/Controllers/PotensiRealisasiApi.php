<?php

namespace App\Controllers;

use App\Models\DishubAnggotaModel;

class PotensiRealisasiApi extends BaseController
{
    public function bulanan(string $va)
    {
        return $this->rekap($va);
    }

    public function tahunan(string $va)
    {
        return $this->rekap($va, true);
    }

    private function rekap(string $va, bool $hanyaTahunan = false)
    {
        $tahun = $this->request->getGet('tahun') ?? date('Y');
        if (!is_string($tahun) || !preg_match('/^[0-9]{4}$/D', $tahun) || (int) $tahun < 1900 || (int) $tahun > 9999) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Parameter tahun harus berupa tahun 4 digit antara 1900 dan 9999.',
            ]);
        }

        $detail = (new DishubAnggotaModel())->getVaWithTransaksi($va, (int) $tahun);
        if ($detail === null) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Data VA tidak ditemukan.',
            ]);
        }

        $namaBulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $bulanan = array_map(static function (array $row) use ($namaBulan): array {
            return [
                'tahun' => (int) $row['tahun'],
                'bulan' => (int) $row['bulan'],
                'nama_bulan' => $namaBulan[(int) $row['bulan']],
                'potensi' => (float) $row['potensi'],
                'pengurangan_kendala' => (float) $row['pengurangan_kendala'],
                'potensi_setelah_kendala' => (float) $row['potensi_setelah_kendala'],
                'realisasi' => (float) $row['realisasi'],
                'selisih' => (float) $row['realisasi'] - (float) $row['potensi_setelah_kendala'],
                'snapshot_mulai' => (string) $row['snapshot_mulai'],
            ];
        }, $detail['potensi_realisasi_bulanan']);

        $tahunan = array_map(static function (array $row): array {
            $target = (float) $row['potensi_setelah_kendala'];
            $realisasi = (float) $row['realisasi'];
            return [
                'tahun' => (int) $row['tahun'],
                'bulan_sampai' => (int) $row['tahun'] === (int) date('Y') ? (int) date('n') : 12,
                'potensi' => (float) $row['potensi'],
                'pengurangan_kendala' => (float) $row['kendala'],
                'potensi_setelah_kendala' => $target,
                'realisasi' => $realisasi,
                'selisih' => $realisasi - $target,
                'persentase' => $target > 0 ? round($realisasi / $target * 100, 2) : 0,
            ];
        }, $detail['potensi_realisasi_tahunan']);

        $responseData = ['va' => $va, 'tahun' => (int) $tahun, 'tahunan' => $tahunan];
        if (!$hanyaTahunan) {
            $responseData['bulanan'] = $bulanan;
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $responseData,
        ]);
    }
}
