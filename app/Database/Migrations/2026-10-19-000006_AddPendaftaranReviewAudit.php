<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class AddPendaftaranReviewAudit extends Migration
{
    public function up()
    {
        $this->forge->addColumn('pengajuan_pendaftaran_anggota', [
            'diproses_oleh' => ['type'=>'VARCHAR', 'constraint'=>100, 'null'=>true],
            'diproses_at' => ['type'=>'DATETIME', 'null'=>true],
        ]);
    }
    public function down()
    {
        $this->forge->dropColumn('pengajuan_pendaftaran_anggota', ['diproses_oleh', 'diproses_at']);
    }
}
