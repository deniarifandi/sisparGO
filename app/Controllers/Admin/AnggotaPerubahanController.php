<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController;

class AnggotaPerubahanController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $rows = $db->table('anggota_perubahan p')->select('p.*, a.anggota_nama, a.anggota_noktp')->join('dishub_anggota a', 'a.anggota_id=p.anggota_id', 'left')->orderBy('p.created_at', 'DESC')->get()->getResultArray();
        $wilayah = [];
        foreach ($db->table('kelurahan')->select('id,kelurahan_nama,kecamatan_nama,kabupaten_kota,provinsi')->get()->getResultArray() as $item) {
            $wilayah[(string)$item['id']] = $item;
        }
        $agama = [1 => 'Islam', 2 => 'Katolik', 3 => 'Kristen', 4 => 'Hindu', 5 => 'Budha', 6 => 'Kong Hu Cu'];
        return view('anggota/perubahan', ['rows' => $rows, 'wilayahMap' => $wilayah, 'agamaMap' => $agama]);
    }

    public function approve($id)
    {
        $db = \Config\Database::connect();
        $item = $db->table('anggota_perubahan')->where('perubahan_id', (int)$id)->where('status', 'menunggu')->get()->getRowArray();
        if (!$item) return redirect()->back()->with('error', 'Pengajuan tidak ditemukan atau sudah diproses.');
        $data = json_decode($item['data_baru'] ?? '{}', true) ?: [];
        $allowed = ['anggota_nama','anggota_noktp','nomor_kk','anggota_nohp','anggota_tgllhr','anggota_tmplhr','anggota_jnsklm','anggota_alamat','anggota_klrhnid','anggota_peker','anggota_pente','anggota_staagamaid'];
        $update = array_intersect_key($data, array_flip($allowed));
        foreach ([
            'foto_selfie' => ['column' => 'anggota_foto', 'folder' => 'anggota_foto'],
            'foto_ktp' => ['column' => 'foto_ktp', 'folder' => 'foto_ktp'],
            'foto_kk' => ['column' => 'foto_kk', 'folder' => 'foto_kk'],
        ] as $sourceColumn => $target) {
            if (empty($item[$sourceColumn])) continue;
            $source = FCPATH . 'uploads/profile_changes/' . basename($item[$sourceColumn]);
            $folder = FCPATH . 'uploads/' . $target['folder'];
            if (!is_dir($folder)) mkdir($folder, 0775, true);
            $filename = basename($item[$sourceColumn]);
            $destination = $folder . '/' . $filename;
            if (is_file($source)) rename($source, $destination);
            if (is_file($destination)) $update[$target['column']] = $filename;
        }
        $db->transStart();
        $db->table('dishub_anggota')->where('anggota_id', $item['anggota_id'])->update($update);
        $db->table('anggota_perubahan')->where('perubahan_id', $id)->update(['status'=>'disetujui','diproses_oleh'=>session('user_id') ?: null,'diproses_at'=>date('Y-m-d H:i:s')]);
        $db->transComplete();
        return redirect()->back()->with($db->transStatus() ? 'success' : 'error', $db->transStatus() ? 'Perubahan disetujui.' : 'Gagal menerapkan perubahan.');
    }

    public function reject($id)
    {
        $reason = trim((string)$this->request->getPost('alasan_penolakan'));
        if ($reason === '') return redirect()->back()->with('error', 'Alasan penolakan wajib diisi.');
        $ok = \Config\Database::connect()->table('anggota_perubahan')->where('perubahan_id', (int)$id)->where('status', 'menunggu')->update(['status'=>'ditolak','alasan_penolakan'=>$reason,'diproses_oleh'=>session('user_id') ?: null,'diproses_at'=>date('Y-m-d H:i:s')]);
        return redirect()->back()->with($ok ? 'success' : 'error', $ok ? 'Pengajuan ditolak.' : 'Gagal menolak pengajuan.');
    }

    public function rotateFile($filename)
    {
        $db = \Config\Database::connect();
        $item = null; $field = null;
        foreach (['foto_selfie', 'foto_ktp', 'foto_kk'] as $candidate) {
            $found = $db->table('anggota_perubahan')->where($candidate, basename($filename))->get()->getRowArray();
            if ($found) { $item = $found; $field = $candidate; break; }
        }
        if (!$item) return redirect()->back()->with('error', 'File foto perubahan tidak ditemukan.');
        $folder = ($item['status'] ?? '') === 'disetujui' ? 'uploads/' . ($field === 'foto_selfie' ? 'anggota_foto' : $field) : 'uploads/profile_changes';
        $path = FCPATH . $folder . '/' . basename($filename);
        if (!is_file($path)) return redirect()->back()->with('error', 'File foto perubahan tidak ditemukan.');
        $turns = (int) $this->request->getPost('turns');
        if ($turns <= 0 || $turns % 4 === 0) return redirect()->back()->with('success', 'Tidak ada perubahan rotasi yang disimpan.');
        $source = @imagecreatefromstring((string) file_get_contents($path));
        if (!$source) return redirect()->back()->with('error', 'Format foto tidak dapat diputar.');
        $rotated = $source;
        for ($i = 0; $i < min(3, $turns); $i++) $rotated = imagerotate($rotated, -90, 0);
        $saved = match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => imagejpeg($rotated, $path, 88),
            'png' => imagepng($rotated, $path, 6),
            'webp' => imagewebp($rotated, $path, 88),
            default => false,
        };
        imagedestroy($source); imagedestroy($rotated);
        return redirect()->back()->with($saved ? 'success' : 'error', $saved ? 'Foto berhasil diputar.' : 'Gagal menyimpan hasil rotasi foto.');
    }
}
