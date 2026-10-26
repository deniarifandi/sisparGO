<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSuratPenunjukanToDishubTitpar extends Migration
{
    public function up()
    {
        $this->forge->addColumn('dishub_titpar', [
            'titpar_surat_penunjukan' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('dishub_titpar', 'titpar_surat_penunjukan');
    }
}
