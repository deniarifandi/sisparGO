<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddJenisKendaraanToDishubTitpar extends Migration
{
    public function up()
    {
        $this->forge->addColumn('dishub_titpar', [
            'titpar_jenis_kendaraan' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'titpar_longitude',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('dishub_titpar', 'titpar_jenis_kendaraan');
    }
}
