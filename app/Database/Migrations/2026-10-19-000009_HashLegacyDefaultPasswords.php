<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class HashLegacyDefaultPasswords extends Migration
{
    public function up()
    {
        $table = $this->db->prefixTable('dishub_anggota');
        $legacy = 'jukirmalang';
        $hash = password_hash($legacy, PASSWORD_DEFAULT);

        // Only migrate the known legacy default. Unknown legacy values are
        // upgraded safely by Home::loginApp after successful verification.
        $this->db->query("UPDATE {$table} SET password = ? WHERE password = ?", [$hash, $legacy]);
    }

    public function down()
    {
        // Never restore plaintext passwords during rollback.
    }
}
