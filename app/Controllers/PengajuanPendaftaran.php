<?php
namespace App\Controllers;

class PengajuanPendaftaran extends BaseController
{
    public function status()
    {
        $this->response->setHeader('Cache-Control', 'no-store');
        if (!service('throttler')->check('registration-status-' . hash('sha256', $this->request->getIPAddress()), 30, MINUTE)) {
            return $this->response->setStatusCode(429)->setJSON(['message'=>'Terlalu banyak percobaan. Coba lagi dalam satu menit.']);
        }
        $nik = $this->request->getPost('nik');
        $code = $this->request->getPost('nomor_pengajuan');
        if (!is_string($nik) || !preg_match('/^[0-9]{16}$/', $nik) || !is_string($code) || strlen($code) > 64) {
            return $this->response->setStatusCode(422)->setJSON(['message'=>'Isi NIK 16 digit dan kode pengajuan yang valid.']);
        }
        $code = strtoupper(trim($code));
        if (preg_match('/^[A-HJ-NP-Z2-9]{8}$/', $code)) $code = substr($code, 0, 4) . '-' . substr($code, 4);
        if (!preg_match('/^(?:[A-HJ-NP-Z2-9]{4}-[A-HJ-NP-Z2-9]{4}|REG-[0-9]{8}-[A-F0-9]{16})$/', $code)) {
            return $this->response->setStatusCode(422)->setJSON(['message'=>'Format kode pengajuan tidak valid.']);
        }
        $row = db_connect()->table('pengajuan_pendaftaran_anggota')
            ->select('nomor_pengajuan, status, catatan_verifikasi, created_at, diproses_at')
            ->where('nik', $nik)->where('nomor_pengajuan', $code)->get()->getRowArray();
        if (!$row) return $this->response->setStatusCode(404)->setJSON(['message'=>'Pengajuan tidak ditemukan. Periksa kembali NIK dan kode pengajuan.']);
        // Only rejection reasons are public; internal approval notes and identity data stay private.
        $row['alasan_penolakan'] = in_array($row['status'], ['ditolak','perlu_perbaikan'], true) ? $row['catatan_verifikasi'] : null;
        $row['bisa_diperbaiki'] = $row['status'] === 'perlu_perbaikan';
        unset($row['catatan_verifikasi']);
        return $this->response->setJSON(['data'=>$row]);
    }

    public function checkNik()
    {
        $nik = $this->request->getPost('nik');
        $this->response->setHeader('Cache-Control', 'no-store');
        if (!is_string($nik) || !preg_match('/^[0-9]{16}$/', $nik)) {
            return $this->response->setStatusCode(422)->setJSON(['message'=>'NIK harus terdiri dari 16 digit.']);
        }
        $db = db_connect();
        $exists = $db->table('dishub_anggota')->where('anggota_noktp', $nik)->countAllResults() > 0
            || $db->table('pengajuan_pendaftaran_anggota')->where('nik', $nik)->countAllResults() > 0;
        return $this->response->setJSON(['available'=>!$exists, 'message'=>$exists
            ? 'NIK sudah terdaftar atau memiliki pengajuan. Hubungi petugas untuk tindak lanjut.'
            : 'NIK belum terdaftar. Silakan lanjutkan pendaftaran.']);
    }

    private function revisionApplicant(): ?array
    {
        $this->response->setHeader('Cache-Control','no-store');
        $nik=$this->request->getPost('nik');
        $code=$this->request->getPost('nomor_pengajuan');
        if(!is_string($nik) || !preg_match('/^[0-9]{16}$/',$nik) || !is_string($code) || strlen($code)>64) return null;
        $code=strtoupper(trim($code));
        if(preg_match('/^[A-HJ-NP-Z2-9]{8}$/',$code)) $code=substr($code,0,4).'-'.substr($code,4);
        return db_connect()->table('pengajuan_pendaftaran_anggota')->where('nik',$nik)->where('nomor_pengajuan',$code)->get()->getRowArray();
    }

    public function revisionData()
    {
        if(!service('throttler')->check('registration-edit-'.hash('sha256',$this->request->getIPAddress()),30,MINUTE)) {
            return $this->response->setStatusCode(429)->setJSON(['message'=>'Terlalu banyak percobaan. Coba lagi nanti.']);
        }
        $row=$this->revisionApplicant();
        if(!$row) return $this->response->setStatusCode(404)->setJSON(['message'=>'NIK dan kode pengajuan tidak cocok.']);
        if($row['status']!=='perlu_perbaikan') return $this->response->setStatusCode(409)->setJSON(['message'=>'Pengajuan ini tidak sedang meminta perbaikan.']);
        $keys=['nama_lengkap','nik','nomor_kk','no_handphone','tanggal_lahir','tempat_lahir','jenis_kelamin','alamat_lengkap','provinsi','kabupaten_kota','kecamatan_id','kelurahan_id','pekerjaan','pendidikan_terakhir','agama','nomor_pengajuan','catatan_verifikasi'];
        $data=array_intersect_key($row,array_flip($keys));
        foreach(['pas_foto','dokumen_ktp','dokumen_kk'] as $key) $data[$key.'_tersedia']=!empty($row[$key]);
        return $this->response->setJSON(['data'=>$data]);
    }

    public function revise() { return $this->saveApplication(true); }
    public function store() { return $this->saveApplication(false); }

    private function saveApplication(bool $revision)

    {
        $db = db_connect();
        $original=null;
        if($revision) {
            if(!service('throttler')->check('registration-edit-'.hash('sha256',$this->request->getIPAddress()),30,MINUTE)) {
                return $this->response->setStatusCode(429)->setJSON(['message'=>'Terlalu banyak percobaan. Coba lagi nanti.']);
            }
            $original=$this->revisionApplicant();
            if(!$original) return $this->response->setStatusCode(404)->setJSON(['message'=>'NIK dan kode pengajuan tidak cocok.']);
        }
        $rules = [
            'request_id'=>'required|regex_match[/^[a-zA-Z0-9-]{20,64}$/]',
            'nama_lengkap'=>'required|max_length[150]',
            'nik'=>'required|regex_match[/^[0-9]{16}$/]',
            'nomor_kk'=>'permit_empty|regex_match[/^[0-9]{16}$/]',
            'no_handphone'=>'permit_empty|max_length[20]|regex_match[/^[+0-9 ()-]+$/]',
            'tanggal_lahir'=>'permit_empty|valid_date[Y-m-d]',
            'tempat_lahir'=>'permit_empty|max_length[50]',
            'jenis_kelamin'=>'permit_empty|in_list[Laki-laki,Perempuan]',
            'alamat_lengkap'=>'permit_empty|max_length[2000]',
            'provinsi'=>'permit_empty|max_length[100]',
            'kabupaten_kota'=>'permit_empty|max_length[100]',
            'kecamatan_id'=>'permit_empty|max_length[30]',
            'kelurahan_id'=>'permit_empty|max_length[30]',
            'pekerjaan'=>'permit_empty|max_length[100]',
            'pendidikan_terakhir'=>'permit_empty|max_length[100]',
            'agama'=>'permit_empty|in_list[Islam,Katolik,Kristen,Hindu,Budha,Kong Hu Cu]',
        ];
        $data = [];
        foreach ($rules as $key => $_) {
            $value = $this->request->getPost($key);
            if ($value !== null && !is_string($value)) {
                return $this->response->setStatusCode(422)->setJSON(['message'=>'Format data tidak valid.']);
            }
            $data[$key] = trim($value ?? '');
        }
        $validator = service('validation');
        $validator->setRules($rules);
        if (!$validator->run($data)) {
            return $this->response->setStatusCode(422)->setJSON(['message'=>'Periksa kembali data pendaftaran.', 'errors'=>$validator->getErrors()]);
        }
        if ($data['tanggal_lahir'] !== '' && $data['tanggal_lahir'] > date('Y-m-d')) {
            return $this->response->setStatusCode(422)->setJSON(['message'=>'Tanggal lahir tidak boleh di masa depan.']);
        }
        // Validate the complete supplied hierarchy against the same reference table as the web form.
        $parentFields = ['provinsi'=>'provinsi', 'kabupaten_kota'=>'kabupaten_kota', 'kecamatan_id'=>'kecamatan_id', 'kelurahan_id'=>'id'];
        $conditions = [];
        $missingParent = false;
        foreach ($parentFields as $input => $column) {
            if ($data[$input] === '') { $missingParent = true; continue; }
            $conditions[$column] = $data[$input];
            if ($missingParent || !$db->table('kelurahan')->where($conditions)->countAllResults()) {
                return $this->response->setStatusCode(422)->setJSON(['message'=>'Pilihan wilayah tidak sesuai. Pilih ulang wilayah secara berurutan.']);
            }
        }
        $files = [];
        $fingerprints = [];
        foreach (['pas_foto', 'dokumen_ktp', 'dokumen_kk'] as $field) {
            $file = $this->request->getFile($field);
            if ($file === null || $file->getError() === UPLOAD_ERR_NO_FILE) continue;
            if (!$file->isValid() || $file->getSize() > 2 * 1024 * 1024
                || !in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/webp'], true)
                || @getimagesize($file->getTempName()) === false) {
                return $this->response->setStatusCode(422)->setJSON(['message'=>"$field harus berupa JPG, PNG, atau WEBP maksimal 2 MB."]);
            }
            $files[$field] = $file;
            $fingerprints[$field] = hash_file('sha256', $file->getTempName());
        }
        $hash = hash('sha256', json_encode([$data, $fingerprints]));
        if($revision) {
            $retry=$db->table('pengajuan_pendaftaran_riwayat')->where('pengajuan_id',$original['id'])->where('request_id',$data['request_id'])->get()->getRowArray();
            if($retry) {
                if(!hash_equals($retry['payload_hash'],$hash)) return $this->response->setStatusCode(409)->setJSON(['message'=>'Kode pengiriman sudah digunakan untuk data lain.']);
                return $this->receipt($original,200);
            }
            if($original['status']!=='perlu_perbaikan') return $this->response->setStatusCode(409)->setJSON(['message'=>'Pengajuan ini tidak dapat diperbaiki.']);
            if($db->table('dishub_anggota')->where('anggota_noktp',$data['nik'])->countAllResults()) return $this->response->setStatusCode(409)->setJSON(['message'=>'NIK sudah terdaftar sebagai anggota. Hubungi petugas.']);
        } else {
        $existing = $db->table('pengajuan_pendaftaran_anggota')->where('request_id', $data['request_id'])->get()->getRowArray();
        if ($existing) {
            if (!hash_equals($existing['payload_hash'], $hash)) {
                return $this->response->setStatusCode(409)->setJSON(['message'=>'Kode pengiriman sudah digunakan untuk data lain.']);
            }
            return $this->receipt($existing, 200);
        }
        if ($db->table('dishub_anggota')->where('anggota_noktp', $data['nik'])->countAllResults()) {
            return $this->response->setStatusCode(409)->setJSON(['message'=>'NIK sudah terdaftar sebagai anggota. Pendaftaran baru tidak dapat dilakukan.']);
        }
        if ($db->table('pengajuan_pendaftaran_anggota')->where('nik', $data['nik'])->countAllResults()) {
            return $this->response->setStatusCode(409)->setJSON(['message'=>'NIK sudah memiliki pengajuan pendaftaran. Hubungi petugas untuk tindak lanjut.']);
        }
        }
        $data['payload_hash'] = $hash;
        $data['nomor_pengajuan'] = $revision ? $original['nomor_pengajuan'] : $this->newRegistrationCode($db);
        $data['status'] = 'menunggu';
        $data['created_at'] = $data['updated_at'] = date('Y-m-d H:i:s');
        foreach ($data as $key => $value) if ($value === '') $data[$key] = null;
        $saved = [];
        $db->transBegin();
        try {
            if($revision) {
                $original=$db->query('SELECT * FROM '.$db->prefixTable('pengajuan_pendaftaran_anggota').' WHERE id = ? FOR UPDATE',[$original['id']])->getRowArray();
                $retry=$db->table('pengajuan_pendaftaran_riwayat')->where('pengajuan_id',$original['id'])->where('request_id',$data['request_id'])->get()->getRowArray();
                if($retry && hash_equals($retry['payload_hash'],$hash)) { $db->transRollback(); return $this->receipt($original,200); }
                if($original['status']!=='perlu_perbaikan' || $retry) {
                    $db->transRollback();
                    return $this->response->setStatusCode(409)->setJSON(['message'=>'Status pengajuan telah berubah. Cek status kembali.']);
                }
            }
            // Private storage: identity documents must not be accessible as public upload URLs.
            $folder = WRITEPATH . 'uploads/pendaftaran_anggota';
            if ($files && !is_dir($folder) && !mkdir($folder, 0700, true) && !is_dir($folder)) {
                throw new \RuntimeException('Cannot create upload directory');
            }
            foreach ($files as $field => $file) {
                $ext = ['image/jpeg'=>'jpg', 'image/png'=>'png', 'image/webp'=>'webp'][$file->getMimeType()];
                $name = bin2hex(random_bytes(20)) . '.' . $ext;
                $file->move($folder, $name);
                $saved[] = $folder . DIRECTORY_SEPARATOR . $name;
                $data[$field] = $name;
            }
            if($revision) {
                $revisionRequest=$data['request_id'];
                unset($data['request_id'],$data['payload_hash'],$data['created_at']);
                $data['catatan_verifikasi']=null;
                $data['diproses_oleh']=null;
                $data['diproses_at']=null;
                if(!$db->table('pengajuan_pendaftaran_anggota')->where('id',$original['id'])->where('status','perlu_perbaikan')->update($data)) throw new \RuntimeException('Update failed');
                $data=array_merge($original,$data);
                \App\Libraries\PendaftaranHistory::record($db,$original,$data,'kirim_ulang','pendaftar',$revisionRequest,$hash);
            } else {
                if(!$db->table('pengajuan_pendaftaran_anggota')->insert($data)) throw new \RuntimeException('Insert failed');
                $data['id']=$db->insertID();
                \App\Libraries\PendaftaranHistory::record($db,null,$data,'pendaftaran','pendaftar');
            }
            if(!$db->transStatus()) throw new \RuntimeException('Transaction failed');
            if (!$db->transCommit()) throw new \RuntimeException('Commit failed');
        } catch (\Throwable $e) {
            $db->transRollback();
            foreach ($saved as $path) if (is_file($path)) unlink($path);
            if($revision) {
                return $this->response->setStatusCode(500)->setJSON(['message'=>'Perbaikan belum dapat disimpan. Silakan coba lagi.']);
            }
            // A concurrent retry may have committed the same request first.
            $existing = $db->table('pengajuan_pendaftaran_anggota')->where('request_id', $data['request_id'])->get()->getRowArray();
            if ($existing && hash_equals($existing['payload_hash'], $hash)) return $this->receipt($existing, 200);
            if ($db->table('pengajuan_pendaftaran_anggota')->where('nik', $data['nik'])->countAllResults()) {
                return $this->response->setStatusCode(409)->setJSON(['message'=>'NIK sudah memiliki pengajuan pendaftaran.']);
            }
            log_message('error', 'Registration storage failure: {type}', ['type'=>get_class($e)]);
            return $this->response->setStatusCode(500)->setJSON(['message'=>'Pengajuan belum dapat disimpan. Silakan coba lagi.']);
        }
        return $this->receipt($data, 201);
    }

    private function newRegistrationCode($db): string
    {
        // Avoid ambiguous I/1 and O/0; retain the database unique constraint.
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $code = '';
            for ($i = 0; $i < 8; $i++) {
                $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            $code = substr($code, 0, 4) . '-' . substr($code, 4);
            if (!$db->table('pengajuan_pendaftaran_anggota')->where('nomor_pengajuan', $code)->countAllResults()) {
                return $code;
            }
        }
        throw new \RuntimeException('Unable to allocate registration code');
    }

    private function receipt(array $data, int $status)
    {
        return $this->response->setStatusCode($status)->setJSON([
            'message'=>'Pengajuan pendaftaran berhasil diterima.',
            'data'=>['nomor_pengajuan'=>$data['nomor_pengajuan'], 'status'=>$data['status']],
        ]);
    }
}
