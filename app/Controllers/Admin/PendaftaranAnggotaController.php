<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class PendaftaranAnggotaController extends BaseController
{
    public function index()
    {
        $role = session('role');
        if ($role === null || !is_numeric($role) || (int) $role >= 4) {
            return $this->response->setStatusCode(403)->setBody('Akses ditolak.');
        }
        $page = max(1, (int) $this->request->getGet('page'));
        $table = db_connect()->table('pengajuan_pendaftaran_anggota');
        $total = $table->countAllResults();
        $pages = max(1, (int) ceil($total / 20));
        $page = min($page, $pages);
        $rows = $table->select('id, nomor_pengajuan, nama_lengkap, no_handphone, status, created_at')
            ->orderBy('id', 'DESC')->limit(20, ($page - 1) * 20)->get()->getResultArray();
        return view('admin/pendaftaran_anggota', compact('rows', 'total', 'page', 'pages'));
    }

    private function allowed(): bool
    {
        $role = session('role');
        return $role !== null && is_numeric($role) && (int) $role < 4;
    }

    public function detail(int $id)
    {
        if (!$this->allowed()) return $this->response->setStatusCode(403)->setBody('Akses ditolak.');
        $db = db_connect();
        $row = $db->table('pengajuan_pendaftaran_anggota')->where('id', $id)->get()->getRowArray();
        if (!$row) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Pengajuan tidak ditemukan.');
        $wilayah = [];
        if (!empty($row['kelurahan_id'])) {
            $wilayah = $db->table('kelurahan')->select('kecamatan_nama, kelurahan_nama')->where('id', $row['kelurahan_id'])->get()->getRowArray() ?? [];
        } elseif (!empty($row['kecamatan_id'])) {
            $wilayah = $db->table('kelurahan')->select('kecamatan_nama')->where('kecamatan_id', $row['kecamatan_id'])->get()->getRowArray() ?? [];
        }
        $history=$db->table('pengajuan_pendaftaran_riwayat')->where('pengajuan_id',$id)->orderBy('id','DESC')->get()->getResultArray();
        $modal = $this->request->isAJAX();
        $html = view('admin/pendaftaran_anggota_detail', compact('row', 'wilayah', 'modal', 'history'));
        $this->response->setHeader('Cache-Control', 'private, no-store');
        return $modal ? $this->response->setJSON(['html' => $html]) : $this->response->setBody($html);
    }

    public function approve(int $id)
    {
        $this->response->setHeader('Cache-Control','private, no-store');
        $reply=function(int $code,array $data) {
            return $this->response->setStatusCode($code)->setJSON($data+['csrf'=>['name'=>csrf_token(),'hash'=>csrf_hash()]]);
        };
        if(!$this->allowed() || !session('user_id')) return $reply(403,['message'=>'Akses ditolak.']);
        $note=$this->request->getPost('catatan_verifikasi');
        if(($note!==null && !is_string($note)) || mb_strlen(trim($note ?? ''))>2000) return $reply(422,['message'=>'Catatan maksimal 2000 karakter.']);
        $db=db_connect();$copied=[];$sourcesToDelete=[];$db->transBegin();
        try {
            $before=$db->query('SELECT * FROM '.$db->prefixTable('pengajuan_pendaftaran_anggota').' WHERE id = ? FOR UPDATE',[$id])->getRowArray();
            if(!$before || $before['status']!=='menunggu' || !empty($before['anggota_id'])) {
                $db->transRollback();
                return $reply(409,['message'=>'Pengajuan sudah diproses atau tidak ditemukan. Akun tidak dibuat ulang.']);
            }
            $credentials=\App\Libraries\RegistrationAccount::create($db,$before,(int)session('user_id'),$copied,$sourcesToDelete);
            $update=['status'=>'disetujui','anggota_id'=>(int)$credentials['user_id'],
                'catatan_verifikasi'=>trim($note ?? '') ?: null,'diproses_oleh'=>(string)session('user_id'),
                'diproses_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')];
            if(!$db->table('pengajuan_pendaftaran_anggota')->where('id',$id)->where('status','menunggu')->update($update) || $db->affectedRows()!==1) throw new \RuntimeException('Approval update failed');
            $after=array_merge($before,$update);
            \App\Libraries\PendaftaranHistory::record($db,$before,$after,'disetujui',(string)session('user_id'));
            if(!$db->transStatus() || !$db->transCommit()) throw new \RuntimeException('Approval transaction failed');
        } catch(\Throwable $error) {
            $db->transRollback();
            foreach($copied as $path) if(is_file($path)) unlink($path);
            log_message('error','Registration approval failed: {type}',['type'=>get_class($error)]);
            return $reply($error instanceof \DomainException ? 409 : 500,['message'=>$error instanceof \DomainException ? $error->getMessage() : 'Persetujuan gagal disimpan. Silakan coba lagi.']);
        }
        foreach($sourcesToDelete as $path) {
            if(is_file($path) && !unlink($path)) {
                log_message('warning','Approved registration source file could not be removed: {path}',['path'=>$path]);
            }
        }
        // Plaintext credentials exist only in this one response; never store them in session/history.
        return $reply(201,['message'=>'Anggota dan akun login berhasil dibuat.','credentials'=>$credentials]);
    }

    public function requestCorrection(int $id) { return $this->review($id, 'perlu_perbaikan'); }

    public function reject(int $id)
    {
        return $this->review($id, 'ditolak');
    }

    private function review(int $id, string $status)
    {
        if (!$this->allowed() || !session('user_id')) {
            return $this->response->setStatusCode(403)->setBody('Akses ditolak.');
        }
        $target = base_url('admin/pendaftaran-anggota');
        $input = $this->request->getPost('catatan_verifikasi');
        if ($input !== null && !is_string($input)) {
            return redirect()->to($target)->with('error', 'Format catatan tidak valid.');
        }
        $note = trim($input ?? '');
        if ((in_array($status, ['ditolak','perlu_perbaikan'], true) && $note === '') || mb_strlen($note) > 2000) {
            return redirect()->to($target)->with('error', 'Penolakan atau permintaan perbaikan wajib disertai alasan. Catatan maksimal 2000 karakter.');
        }
        $db = db_connect();
        $db->transBegin();
        try {
            $before=$db->query('SELECT * FROM '.$db->prefixTable('pengajuan_pendaftaran_anggota').' WHERE id = ? FOR UPDATE',[$id])->getRowArray();
            if(!$before || $before['status']!=='menunggu') {
                $db->transRollback();
                return redirect()->to($target)->with('error','Pengajuan sudah diproses atau tidak ditemukan.');
            }
            $ok = $db->table('pengajuan_pendaftaran_anggota')->where('id', $id)
                ->where('status', 'menunggu')->update([
                    'status'=>$status, 'catatan_verifikasi'=>$note ?: null,
                    'diproses_oleh'=>(string) session('user_id'),
                    'diproses_at'=>date('Y-m-d H:i:s'), 'updated_at'=>date('Y-m-d H:i:s'),
                ]);
            if (!$ok) throw new \RuntimeException('Review update failed');
            if ($db->affectedRows() !== 1) {
                $db->transRollback();
                return redirect()->to($target)->with('error', 'Pengajuan tidak ditemukan atau sudah diproses. Muat ulang detail untuk melihat status terbaru.');
            }
            $after=$db->table('pengajuan_pendaftaran_anggota')->where('id',$id)->get()->getRowArray();
            \App\Libraries\PendaftaranHistory::record($db,$before,$after,$status,(string)session('user_id'));
            if(!$db->transStatus() || !$db->transCommit()) throw new \RuntimeException('Review transaction failed');
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Registration review failed: {type}', ['type'=>get_class($e)]);
            return redirect()->to($target)->with('error', 'Keputusan belum dapat disimpan. Silakan coba lagi.');
        }
        return redirect()->to($target)->with('success', 'Pengajuan berhasil ' . $status . '.');
    }

    public function document(int $id, string $field)
    {
        if (!$this->allowed()) return $this->response->setStatusCode(403)->setBody('Akses ditolak.');
        if (!in_array($field, ['pas_foto', 'dokumen_ktp', 'dokumen_kk'], true)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        $db = db_connect();
        $row = $db->table('pengajuan_pendaftaran_anggota')->select($field . ', status, anggota_id')->where('id', $id)->get()->getRowArray();
        $name = $row[$field] ?? '';
        $folder = realpath(WRITEPATH . 'uploads/pendaftaran_anggota');
        $path = $folder && $name ? realpath($folder . DIRECTORY_SEPARATOR . basename($name)) : false;
        if ((!$path || !is_file($path)) && ($row['status'] ?? '') === 'disetujui' && !empty($row['anggota_id'])) {
            $publicDocuments = [
                'pas_foto' => ['column' => 'anggota_foto', 'folder' => 'anggota_foto'],
                'dokumen_ktp' => ['column' => 'foto_ktp', 'folder' => 'foto_ktp'],
                'dokumen_kk' => ['column' => 'foto_kk', 'folder' => 'foto_kk'],
            ];
            $document = $publicDocuments[$field];
            $member = $db->table('dishub_anggota')->select($document['column'])
                ->where('anggota_id', (int) $row['anggota_id'])->get()->getRowArray();
            $publicName = $member[$document['column']] ?? '';
            $publicFolder = realpath(FCPATH . 'uploads/' . $document['folder']);
            $path = $publicFolder && $publicName
                ? realpath($publicFolder . DIRECTORY_SEPARATOR . basename($publicName))
                : false;
            $folder = $publicFolder;
        }
        if (!$path || dirname($path) !== $folder || !is_file($path)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Dokumen tidak ditemukan.');
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        return $this->response->setContentType($mime)
            ->setHeader('Cache-Control', 'private, no-store')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setBody(file_get_contents($path));
    }
}
