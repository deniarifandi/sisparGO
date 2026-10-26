<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\DataTable;

class KtaPerpanjanganController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        return view('kta/perpanjangan', [
            'total' => $db->table('kta_perpanjangan')->countAllResults(),
        ]);
    }

    public function data()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('kta_perpanjangan p')
            ->select('p.*, a.anggota_nama, a.anggota_noktp, a.nomor_kk AS profil_nomor_kk, a.anggota_nohp AS profil_anggota_nohp, a.anggota_tgllhr AS profil_anggota_tgllhr, a.anggota_tmplhr AS profil_anggota_tmplhr, a.anggota_jnsklm AS profil_anggota_jnsklm, a.anggota_alamat AS profil_anggota_alamat, a.anggota_klrhnid AS profil_anggota_klrhnid, a.anggota_peker AS profil_anggota_peker, a.anggota_pente AS profil_anggota_pente, a.anggota_staagamaid AS profil_anggota_staagamaid, g.titpargrup_titparid, g.titpargrup_masaberlakustart, g.titpargrup_masaberlakuend, (SELECT vo.va_owner_va FROM va_owner vo WHERE vo.va_owner_titpargrupid = p.titpargrup_id ORDER BY vo.va_owner_id DESC LIMIT 1) AS va_owner_va, t.*')
            ->join('dishub_anggota a', 'a.anggota_id=p.anggota_id', 'left')
            ->join('dishub_titpargrup g', 'g.titpargrup_id=p.titpargrup_id', 'left')
            ->join('dishub_titpar t', 't.titpar_id=g.titpargrup_titparid', 'left');
        $status = (string) $this->request->getPost('status');
        if (in_array($status, ['menunggu', 'disetujui', 'ditolak'], true)) {
            $builder->where('p.status', $status);
        }
        $columns = ['p.perpanjangan_id', 'a.anggota_nama', 'a.anggota_noktp', 'p.nomor_kta', 't.titpar_namatempat', 'p.status', 'p.created_at'];
        return $this->response->setJSON((new DataTable($builder, $columns, [0 => 'p.perpanjangan_id']))->generate());
    }

    public function members($id)
    {
        $db = \Config\Database::connect();
        $application = $db->table('kta_perpanjangan')
            ->select('anggota_id, titpargrup_id, data_lama, data_baru')
            ->where('perpanjangan_id', (int) $id)
            ->get()->getRowArray();
        if (!$application) {
            return $this->response->setStatusCode(404)->setJSON(['message' => 'Pengajuan tidak ditemukan.']);
        }

        $group = $db->table('dishub_titpargrup')
            ->select('titpargrup_titparid, titpargrup_staanggota')
            ->where('titpargrup_id', $application['titpargrup_id'])
            ->where('titpargrup_anggotaid', $application['anggota_id'])
            ->get()->getRowArray();
        if (!$group || $group['titpargrup_staanggota'] !== 'KOORDINATOR') {
            return $this->response->setJSON(['members' => []]);
        }

        $members = $db->table('dishub_titpargrup tg')
            ->select('tg.titpargrup_id, tg.titpargrup_masaberlakustart, tg.titpargrup_masaberlakuend, a.anggota_id, a.anggota_nama, a.anggota_nohp, (SELECT GROUP_CONCAT(DISTINCT vo.va_owner_va ORDER BY vo.va_owner_va SEPARATOR ", ") FROM va_owner vo WHERE vo.va_owner_titpargrupid = tg.titpargrup_id) AS va_owner_va')
            ->join('dishub_anggota a', 'a.anggota_id = tg.titpargrup_anggotaid', 'left')
            ->where('tg.titpargrup_titparid', $group['titpargrup_titparid'])
            ->where('tg.titpargrup_staanggota', 'ANGGOTA')
            ->where('tg.titpargrup_status', 3)
            ->where('tg.titpargrup_namashift', $application['anggota_id'])
            ->orderBy('a.anggota_nama', 'ASC')
            ->get()->getResultArray();

        $new = json_decode($application['data_baru'] ?? '{}', true) ?: [];
        if (!array_key_exists('anggota_ids', $new)) return $this->response->setJSON(['members' => $members]);
        $ids = array_map('intval', $new['anggota_ids']);
        $currentById = array_column($members, null, 'anggota_id');
        $proposed = [];
        if ($ids) {
            $people = $db->table('dishub_anggota')
                ->select('anggota_id, anggota_nama, anggota_nohp')
                ->whereIn('anggota_id', $ids)->get()->getResultArray();
            $peopleById = array_column($people, null, 'anggota_id');
            foreach ($ids as $memberId) {
                if (isset($peopleById[$memberId])) $proposed[] = array_merge($currentById[$memberId] ?? [], $peopleById[$memberId]);
            }
        }
        $removed = array_values(array_filter($members, static fn($member) => !in_array((int) $member['anggota_id'], $ids, true)));
        return $this->response->setJSON(['members' => $proposed, 'removed' => $removed]);
    }

    public function approve($id)
    {
        $db = \Config\Database::connect();
        $item = $db->table('kta_perpanjangan')->where('perpanjangan_id', (int) $id)->where('status', 'menunggu')->get()->getRowArray();
        if (!$item) return redirect()->back()->with('error', 'Pengajuan tidak ditemukan atau sudah diproses.');
        $masaMulai = trim((string) $this->request->getPost('masa_berlaku_mulai'));
        $masaSelesai = trim((string) $this->request->getPost('masa_berlaku_selesai'));
        if (!$masaMulai || !$masaSelesai || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $masaMulai) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $masaSelesai) || $masaSelesai < $masaMulai) {
            return redirect()->back()->with('error', 'Masa berlaku mulai dan selesai wajib diisi dengan benar.');
        }
        $newData = json_decode($item['data_baru'] ?? '{}', true) ?: [];
        $desiredIds = array_key_exists('anggota_ids', $newData) && is_array($newData['anggota_ids'])
            ? array_map('intval', $newData['anggota_ids']) : null;
        $coordinatorGroup = $db->table('dishub_titpargrup')->select('titpargrup_titparid')
            ->where('titpargrup_id', $item['titpargrup_id'])
            ->where('titpargrup_anggotaid', $item['anggota_id'])
            ->where('titpargrup_staanggota', 'KOORDINATOR')->get()->getRowArray();
        if (!$coordinatorGroup) return redirect()->back()->with('error', 'Koordinator pengajuan tidak ditemukan.');
        $titparId = (int) $coordinatorGroup['titpargrup_titparid'];
        if ($desiredIds !== null) {
            if (count($desiredIds) !== count(array_unique($desiredIds)) || in_array((int) $item['anggota_id'], $desiredIds, true)) {
                return redirect()->back()->with('error', 'Daftar anggota pengajuan tidak valid.');
            }
            if ($desiredIds && $db->table('dishub_anggota')->whereIn('anggota_id', $desiredIds)->where('anggota_status', 3)->countAllResults() !== count($desiredIds)) {
                return redirect()->back()->with('error', 'Salah satu anggota tidak lagi aktif.');
            }
            $pointGroups = $db->table('dishub_titpargrup')->where('titpargrup_titparid', $titparId)->get()->getResultArray();
            $groupsByMember = array_column($pointGroups, null, 'titpargrup_anggotaid');
            foreach ($desiredIds as $memberId) {
                $existing = $groupsByMember[$memberId] ?? null;
                if ($existing && ($existing['titpargrup_staanggota'] !== 'ANGGOTA' ||
                    ($existing['titpargrup_namashift'] !== null && $existing['titpargrup_namashift'] !== '' && (int) $existing['titpargrup_namashift'] !== (int) $item['anggota_id']))) {
                    return redirect()->back()->with('error', 'Ada anggota yang sudah ditempatkan pada koordinator lain.');
                }
            }
        }
        $allowed = ['anggota_nama','anggota_noktp','nomor_kk','anggota_nohp','anggota_tgllhr','anggota_tmplhr','anggota_jnsklm','anggota_alamat','anggota_klrhnid','anggota_peker','anggota_pente','anggota_staagamaid'];
        $memberUpdate = array_intersect_key($newData, array_flip($allowed));
        foreach (['foto_selfie' => ['column' => 'anggota_foto', 'folder' => 'anggota_foto'], 'foto_ktp' => ['column' => 'foto_ktp', 'folder' => 'foto_ktp'], 'foto_kk' => ['column' => 'foto_kk', 'folder' => 'foto_kk']] as $sourceColumn => $target) {
            if (empty($item[$sourceColumn])) continue;
            $source = FCPATH . 'uploads/kta_perpanjangan/' . basename($item[$sourceColumn]);
            $folder = FCPATH . 'uploads/' . $target['folder'];
            if (!is_dir($folder)) mkdir($folder, 0775, true);
            $filename = basename($item[$sourceColumn]);
            $destination = $folder . DIRECTORY_SEPARATOR . $filename;
            if (is_file($source) && !is_file($destination)) rename($source, $destination);
            if (is_file($destination)) $memberUpdate[$target['column']] = $filename;
        }
        $denahFilename = null;
        if (!empty($item['dokumen_pendukung'])) {
            $source = FCPATH . 'uploads/kta_perpanjangan/' . basename($item['dokumen_pendukung']);
            $folder = FCPATH . 'uploads/denah';
            if (!is_dir($folder)) mkdir($folder, 0775, true);
            $denahFilename = basename($item['dokumen_pendukung']);
            $destination = $folder . DIRECTORY_SEPARATOR . $denahFilename;
            // Denah dipindahkan seperti foto selfie/KTP/KK, bukan disalin.
            if (is_file($source) && !is_file($destination)) rename($source, $destination);
            if (!is_file($destination)) $denahFilename = null;
        }
        $titparGroup = $db->table('dishub_titpargrup')
            ->select('titpargrup_titparid')
            ->where('titpargrup_id', $item['titpargrup_id'])
            ->get()->getRowArray();
        $stage = 'memulai transaksi';
        $db->transException(true);
        try {
        $db->transStart();
        if ($desiredIds !== null) {
            $stage = 'memperbarui daftar anggota';
            foreach ($pointGroups as $groupRow) {
                if ($groupRow['titpargrup_staanggota'] === 'ANGGOTA' &&
                    (int) $groupRow['titpargrup_status'] === 3 &&
                    (int) $groupRow['titpargrup_namashift'] === (int) $item['anggota_id'] &&
                    !in_array((int) $groupRow['titpargrup_anggotaid'], $desiredIds, true)) {
                    // Simpan riwayat relasi yang mungkin sudah dirujuk pengajuan lain.
                    $db->table('dishub_titpargrup')->where('titpargrup_id', $groupRow['titpargrup_id'])
                        ->where('titpargrup_titparid', $titparId)
                        ->where('titpargrup_namashift', $item['anggota_id'])
                        ->where('titpargrup_staanggota', 'ANGGOTA')
                        ->update(['titpargrup_status' => 2, 'titpargrup_namashift' => null]);
                }
            }
            foreach ($desiredIds as $memberId) {
                if (isset($groupsByMember[$memberId])) {
                    $db->table('dishub_titpargrup')->where('titpargrup_id', $groupsByMember[$memberId]['titpargrup_id'])
                        ->where('titpargrup_titparid', $titparId)
                        ->where('titpargrup_anggotaid', $memberId)
                        ->update([
                            'titpargrup_namashift' => $item['anggota_id'],
                            'titpargrup_status' => 3,
                            'titpargrup_masaberlakustart' => $masaMulai,
                            'titpargrup_masaberlakuend' => $masaSelesai,
                        ]);
                } else {
                    $db->table('dishub_titpargrup')->insert([
                        'titpargrup_titparid' => $titparId,
                        'titpargrup_anggotaid' => $memberId,
                        'titpargrup_staanggota' => 'ANGGOTA',
                        'titpargrup_namashift' => $item['anggota_id'],
                        'titpargrup_status' => 3,
                        'titpargrup_masaberlakustart' => $masaMulai,
                        'titpargrup_masaberlakuend' => $masaSelesai,
                    ]);
                }
            }
        }
        $stage = 'memperbarui data KTA';
        if ($memberUpdate) $db->table('dishub_anggota')->where('anggota_id', $item['anggota_id'])->update($memberUpdate);
        $db->table('dishub_titpargrup')->where('titpargrup_id', $item['titpargrup_id'])
            ->where('titpargrup_titparid', $titparId)->update([
            'titpargrup_masaberlakustart' => $masaMulai,
            'titpargrup_masaberlakuend' => $masaSelesai,
        ]);
        if ($denahFilename && !empty($titparGroup['titpargrup_titparid'])) {
            $db->table('dishub_titpar')->where('titpar_id', $titparGroup['titpargrup_titparid'])->update([
                'titpar_denah' => $denahFilename,
            ]);
        }
        $db->table('kta_perpanjangan')->where('perpanjangan_id', $id)->update([
            'status' => 'disetujui', 'masa_berlaku_lama' => $masaMulai, 'masa_berlaku_baru' => $masaSelesai, 'diproses_oleh' => session('user_id') ?: null, 'diproses_at' => date('Y-m-d H:i:s'),
        ]);
        $ok = $db->transComplete();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Persetujuan perpanjangan KTA {id} gagal saat {stage}: {error}', [
                'id' => $id, 'stage' => $stage, 'error' => $e->getMessage(),
            ]);
            return redirect()->back()->with('error', 'Gagal menerapkan perpanjangan KTA saat ' . $stage . '.');
        }
        if (!$ok) log_message('error', 'Persetujuan perpanjangan KTA {id} gagal saat {stage}: {error}', [
            'id' => $id, 'stage' => $stage, 'error' => json_encode($db->error()),
        ]);
        return redirect()->back()->with($ok ? 'success' : 'error', $ok ? 'Perpanjangan KTA disetujui.' : 'Gagal menerapkan perpanjangan KTA.');
    }

    public function reject($id)
    {
        $reason = trim((string) $this->request->getPost('alasan_penolakan'));
        if ($reason === '') return redirect()->back()->with('error', 'Alasan penolakan wajib diisi.');
        $ok = \Config\Database::connect()->table('kta_perpanjangan')->where('perpanjangan_id', (int) $id)->where('status', 'menunggu')->update([
            'status' => 'ditolak', 'alasan_penolakan' => $reason, 'diproses_oleh' => session('user_id') ?: null, 'diproses_at' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->back()->with($ok ? 'success' : 'error', $ok ? 'Pengajuan ditolak.' : 'Gagal menolak pengajuan.');
    }

    public function rotate($id, $field, int $turns = 1)
    {
        $allowed = ['foto_selfie', 'foto_ktp', 'foto_kk', 'dokumen_pendukung'];
        if (!in_array($field, $allowed, true)) return redirect()->back()->with('error', 'Dokumen tidak valid.');
        $item = \Config\Database::connect()->table('kta_perpanjangan')->where('perpanjangan_id', (int) $id)->get()->getRowArray();
        $filename = $item[$field] ?? null;
        $path = $filename ? FCPATH . 'uploads/kta_perpanjangan/' . basename($filename) : '';
        if (!$path || !is_file($path)) return redirect()->back()->with('error', 'File dokumen tidak ditemukan.');
        $source = @imagecreatefromstring((string) file_get_contents($path));
        if (!$source) return redirect()->back()->with('error', 'Format gambar tidak dapat diputar.');
        // Normalisasi EXIF terlebih dahulu. Browser biasanya membaca EXIF,
        // sedangkan GD membaca pixel mentah sehingga orientasi dapat berbeda.
        $source = $this->normalizeExifOrientation($source, $path);
        $turns = max(1, min(4, $turns));
        $rotated = $source;
        for ($i = 0; $i < $turns; $i++) $rotated = imagerotate($rotated, -90, 0); // setiap klik = 90 derajat ke kanan
        if (!$rotated) { imagedestroy($source); return redirect()->back()->with('error', 'Gagal memutar gambar.'); }
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $saved = match ($extension) {
            'jpg', 'jpeg' => imagejpeg($rotated, $path, 88),
            'png' => imagepng($rotated, $path, 6),
            'webp' => imagewebp($rotated, $path, 88),
            default => false,
        };
        imagedestroy($source); imagedestroy($rotated);
        return redirect()->back()->with($saved ? 'success' : 'error', $saved ? 'Dokumen berhasil diputar 90°.' : 'Gagal menyimpan hasil rotasi.');
    }

    public function rotateFile($filename)
    {
        $db = \Config\Database::connect();
        $allowed = ['foto_selfie', 'foto_ktp', 'foto_kk', 'dokumen_pendukung'];
        $item = null; $field = null;
        foreach ($allowed as $candidate) {
            $found = $db->table('kta_perpanjangan')->where($candidate, basename($filename))->get()->getRowArray();
            if ($found) { $item = $found; $field = $candidate; break; }
        }
        if (!$item) return redirect()->back()->with('error', 'File dokumen tidak ditemukan.');
        $turns = (int) $this->request->getPost('turns');
        if ($turns <= 0 || $turns % 4 === 0) {
            return redirect()->back()->with('success', 'Tidak ada perubahan rotasi yang disimpan.');
        }
        return $this->rotate($item['perpanjangan_id'], $field, $turns);
    }

    private function normalizeExifOrientation($image, string $path)
    {
        if (!function_exists('exif_read_data')) return $image;
        $exif = @exif_read_data($path);
        $orientation = (int) ($exif['Orientation'] ?? 1);
        $normalized = $image;
        switch ($orientation) {
            case 2: imageflip($normalized, IMG_FLIP_HORIZONTAL); break;
            case 3: $normalized = imagerotate($normalized, 180, 0); break;
            case 4: imageflip($normalized, IMG_FLIP_VERTICAL); break;
            case 5: imageflip($normalized, IMG_FLIP_HORIZONTAL); $normalized = imagerotate($normalized, 90, 0); break;
            case 6: $normalized = imagerotate($normalized, -90, 0); break;
            case 7: imageflip($normalized, IMG_FLIP_HORIZONTAL); $normalized = imagerotate($normalized, -90, 0); break;
            case 8: $normalized = imagerotate($normalized, 90, 0); break;
        }
        return $normalized;
    }
}
