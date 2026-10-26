<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class CreatePendaftaranHistory extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'=>['type'=>'BIGINT','unsigned'=>true,'auto_increment'=>true],
            'pengajuan_id'=>['type'=>'BIGINT','unsigned'=>true],
            'aksi'=>['type'=>'VARCHAR','constraint'=>30],
            'status_awal'=>['type'=>'VARCHAR','constraint'=>20,'null'=>true],
            'status_akhir'=>['type'=>'VARCHAR','constraint'=>20],
            'catatan'=>['type'=>'TEXT','null'=>true],
            'aktor'=>['type'=>'VARCHAR','constraint'=>100],
            'request_id'=>['type'=>'VARCHAR','constraint'=>64,'null'=>true],
            'payload_hash'=>['type'=>'CHAR','constraint'=>64,'null'=>true],
            'data_sebelum'=>['type'=>'LONGTEXT','null'=>true],
            'data_sesudah'=>['type'=>'LONGTEXT'],
            'created_at'=>['type'=>'DATETIME'],
        ]);
        $this->forge->addKey('id',true);
        $this->forge->addKey(['pengajuan_id','id']);
        $this->forge->addUniqueKey(['pengajuan_id','request_id']);
        $this->forge->createTable('pengajuan_pendaftaran_riwayat');
        // Preserve the current state of existing applications; do not invent prior events.
        $rows=$this->db->table('pengajuan_pendaftaran_anggota')->get()->getResultArray();
        foreach($rows as $row) {
            $this->db->table('pengajuan_pendaftaran_riwayat')->insert([
                'pengajuan_id'=>$row['id'],'aksi'=>'status_awal','status_akhir'=>$row['status'],
                'catatan'=>$row['catatan_verifikasi'],'aktor'=>'sistem',
                'data_sesudah'=>json_encode($row),'created_at'=>date('Y-m-d H:i:s'),
            ]);
        }
    }
    public function down() { $this->forge->dropTable('pengajuan_pendaftaran_riwayat'); }
}
