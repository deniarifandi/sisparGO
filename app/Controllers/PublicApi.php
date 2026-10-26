<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

/**
 * Read-only, public-facing API for the parking dashboard.
 *
 * Security notes (see chat for full explanation):
 * - Only GET methods, only whitelisted queries below. Do NOT add write/update/delete here.
 * - Never expose owner/VA-linked rupiah figures or anything traceable to an individual account.
 * - Register this behind the 'publicApiThrottle' filter in Routes (rate limiting).
 * - Keep app.CSPEnabled / your own CSP meta tag on, and app.forceGlobalSecureRequests = true in production.
 * - Make sure app environment is 'production' so CI4 debug toolbar / error details never leak.
 */
class PublicApi extends BaseController
{
    /**
     * GET /public-api/statistik
     * Aggregate counters only — no per-record data.
     */
    public function statistik(): ResponseInterface
    {
        $db = db_connect();

        // Adjust table/column names to match your schema.
        $titikParkir = $db->table('titpar')->countAllResults();
        $jukirAktif  = $db->table('register_anggota')->where('status', 'aktif')->countAllResults();
        $kecamatan   = $db->table('kecamatan')->countAllResults();
        $kta         = $db->table('register')->where('status_kta', 'terbit')->countAllResults();

        return $this->response->setJSON([
            'titik_parkir'         => $titikParkir,
            'jukir_aktif'          => $jukirAktif,
            'kecamatan_terlayani'  => $kecamatan,
            'kta_terbit'           => $kta,
        ]);
    }

    /**
     * GET /public-api/target-kecamatan
     * Same shape as internal targetsetoranbulanankecamatan/1 but re-implemented
     * here so the public route never touches the internal/admin controller.
     */
    public function targetKecamatan(): ResponseInterface
    {
        $db = db_connect();

        $bulan = (int) date('n');
        $tahun = (int) date('Y');

        $rows = $db->table('target_setoran ts')
            ->select('k.nama as kecamatan_nama, ts.potensi, COALESCE(SUM(tr.jumlah), 0) as transaksi')
            ->join('kecamatan k', 'k.id = ts.kecamatan_id')
            ->join('transaksi tr', 'tr.kecamatan_id = ts.kecamatan_id AND MONTH(tr.tanggal) = ' . $bulan . ' AND YEAR(tr.tanggal) = ' . $tahun, 'left')
            ->where('ts.bulan', $bulan)
            ->where('ts.tahun', $tahun)
            ->groupBy('k.id, k.nama, ts.potensi')
            ->get()
            ->getResultArray();

        return $this->response->setJSON($rows);
    }

    /**
     * GET /public-api/target-bulanan
     * Monthly totals for the current year, no owner/account breakdown.
     */
    public function targetBulanan(): ResponseInterface
    {
        $db = db_connect();
        $tahun = (int) date('Y');

        $rows = $db->table('target_setoran ts')
            ->select('ts.bulan, SUM(ts.potensi) as potensi, COALESCE(SUM(tr.jumlah), 0) as transaksi')
            ->join('transaksi tr', 'MONTH(tr.tanggal) = ts.bulan AND YEAR(tr.tanggal) = ts.tahun', 'left')
            ->where('ts.tahun', $tahun)
            ->groupBy('ts.bulan')
            ->orderBy('ts.bulan', 'ASC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON($rows);
    }

    /**
     * Entry point that renders the dashboard shell (data itself loads via the
     * endpoints above, client-side).
     */
    public function dashboard(): string
    {
        return view('public/dashboard');
    }
}