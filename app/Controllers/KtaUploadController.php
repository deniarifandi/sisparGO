<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class KtaUploadController extends BaseController
{
    use ResponseTrait;

    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /*
    |-------------------------------------------------
    | MAIN ENTRY (api/upload/{type})
    |-------------------------------------------------
    */
    public function upload($type)
    {
        $map = [
            'foto_ktp'   => ['folder'=>'foto_ktp','column'=>'foto_ktp','size'=>3],
            'kesanggupan'   => ['folder'=>'kesanggupan','column'=>'kesanggupan','size'=>3],
            'keberatan'     => ['folder'=>'keberatan','column'=>'keberatan','size'=>3],
            'denah'         => ['folder'=>'denah','column'=>'denah','size'=>3],
            'kk'            => ['folder'=>'kk','column'=>'kk','size'=>3],
            'berita_acara'  => [
                'folder'=>'berita_acara',
                'column'=>'berita_acara',
                'size'=>5,
                'ext'=>['pdf','jpg','jpeg','png']
            ],
            'penunjukan'    => [
                'folder'=>'penunjukan',
                'column'=>'penunjukan',
                'size'=>5,
                'ext'=>['pdf','jpg','jpeg','png']
            ],
            'anggota_foto' => [
                'folder' => 'anggota_foto',
                'column' => 'anggota_foto',
                'size'   => 3,
                'ext'    => ['jpg','jpeg','png']
            ],
        ];

        if (!isset($map[$type])) {
            return $this->fail('Invalid upload type', 400);
        }

        $cfg = $map[$type];

        return $this->handleUpload(
            $type,
            $cfg['folder'],
            $cfg['column'],
            $cfg['size'],
            $cfg['ext'] ?? []
        );
    }

    /*
    |-------------------------------------------------
    | CORE LOGIC
    |-------------------------------------------------
    */
    private function handleUpload($field, $folder, $column, $maxSizeMB = 3, $allowedExt = [])
    {
        $id   = $this->request->getPost('id');
        $file = $this->request->getFile($field);

        // VALIDATION
        if (!$id) {
            return $this->fail(['id' => 'ID wajib'], 400);
        }

        if (!$file) {
            return $this->fail([$field => 'File wajib'], 400);
        }

        if (!$file->isValid()) {
            return $this->fail([$field => $file->getErrorString()], 400);
        }

        if ($file->getSize() > $maxSizeMB * 1024 * 1024) {
            return $this->fail([$field => "Max {$maxSizeMB}MB"], 400);
        }

        // EXTENSION CHECK
        if (!empty($allowedExt)) {
            $ext = strtolower($file->getClientExtension());
            if (!in_array($ext, $allowedExt)) {
                return $this->fail([$field => 'Format tidak diizinkan'], 400);
            }
        }

        // OPTIONAL MIME CHECK (lebih aman)
        $allowedMime = [
            'application/pdf',
            'image/jpeg',
            'image/png'
        ];

        if (!in_array($file->getMimeType(), $allowedMime)) {
            return $this->fail([$field => 'Mime tidak valid'], 400);
        }

        // GET DATA
        $row = $this->db->table('register')
            ->select($column)
            ->where('id', $id)
            ->get()
            ->getRow();

        if (!$row) {
            return $this->fail('Data tidak ditemukan', 404);
        }

        // DELETE OLD FILE
        $this->deleteOldFile($folder, $row->$column ?? null);

        // CREATE FOLDER IF NOT EXISTS
        $targetDir = FCPATH . "uploads/$folder/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        // UPLOAD FILE
        $newName = $file->getRandomName();

        try {
            $file->move($targetDir, $newName);
        } catch (\Exception $e) {
            return $this->fail(['upload' => $e->getMessage()], 500);
        }

        // UPDATE DB
        try {
            $this->db->table('register')
                ->where('id', $id)
                ->update([
                    $column => $newName,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
        } catch (\Exception $e) {
            return $this->fail(['db' => $e->getMessage()], 500);
        }

        return $this->respond([
            'status' => true,
            'message' => 'Upload berhasil',
            'file'   => $newName,
            'url'    => base_url("uploads/$folder/$newName")
        ]);
    }

    /*
    |-------------------------------------------------
    | DELETE OLD FILE
    |-------------------------------------------------
    */
    private function deleteOldFile($folder, $filename)
    {
        if (!$filename) return;

        $path = FCPATH . "uploads/$folder/" . $filename;

        if (file_exists($path)) {
            @unlink($path);
        }
    }

    /*
    |-------------------------------------------------
    | OPTIONAL: SERVE FILE
    |-------------------------------------------------
    */
    public function serve($folder, $filename)
    {
        $filename = basename($filename); // prevent path traversal
        $path = FCPATH . "uploads/$folder/" . $filename;

        if (!file_exists($path)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $this->response
            ->setHeader('Content-Type', mime_content_type($path))
            ->setHeader('Content-Disposition', 'inline; filename="'.$filename.'"')
            ->setBody(file_get_contents($path));
    }
}