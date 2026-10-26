<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAdminUtamaRole extends Migration
{
    public function up()
    {
        // Preserve existing accounts while inserting Admin Utama above Admin.
        $this->db->query('UPDATE users SET role = CASE role WHEN 2 THEN 3 WHEN 3 THEN 4 ELSE role END WHERE role IN (2, 3)');
    }

    public function down()
    {
        $this->db->query('UPDATE users SET role = CASE role WHEN 3 THEN 2 WHEN 4 THEN 3 ELSE role END WHERE role IN (3, 4)');
    }
}
