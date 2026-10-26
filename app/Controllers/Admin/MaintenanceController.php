<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class MaintenanceController extends BaseController
{
    public function hashLegacyPasswords()
    {
        if ((int) session('role') >= 4) {
            return $this->response->setStatusCode(403)->setJSON(['message' => 'Anda tidak memiliki izin.']);
        }

        $legacy = 'jukirmalang';
        $hash = password_hash($legacy, PASSWORD_DEFAULT);
        $db = db_connect();
        $table = $db->prefixTable('dishub_anggota');
        $query = $db->query("UPDATE {$table} SET password = ? WHERE password = ?", [$hash, $legacy]);

        return $this->response->setJSON([
            'message' => 'Migrasi password legacy selesai.',
            'updated' => $db->affectedRows(),
        ]);
    }
}
