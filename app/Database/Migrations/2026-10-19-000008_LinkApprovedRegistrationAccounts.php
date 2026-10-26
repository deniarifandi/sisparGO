<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class LinkApprovedRegistrationAccounts extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('dishub_anggota', [
            'anggota_nama'=>['type'=>'VARCHAR','constraint'=>150,'null'=>true],
            'anggota_alamat'=>['type'=>'TEXT','null'=>true],
        ]);
        $this->forge->addColumn('dishub_anggota', [
            'pendaftaran_id'=>['type'=>'BIGINT','unsigned'=>true,'null'=>true],
            'must_change_password'=>['type'=>'TINYINT','constraint'=>1,'default'=>0],
        ]);
        $this->db->query('ALTER TABLE '.$this->db->prefixTable('dishub_anggota').' ADD UNIQUE KEY uq_anggota_pendaftaran (pendaftaran_id), ADD KEY idx_anggota_nik (anggota_noktp)');
        $this->forge->addColumn('pengajuan_pendaftaran_anggota', ['anggota_id'=>['type'=>'INT','null'=>true]]);
        $this->db->query('ALTER TABLE '.$this->db->prefixTable('pengajuan_pendaftaran_anggota').' ADD UNIQUE KEY uq_pengajuan_anggota (anggota_id)');
    }
    public function down()
    {
        $this->db->query('ALTER TABLE '.$this->db->prefixTable('dishub_anggota').' DROP INDEX uq_anggota_pendaftaran, DROP INDEX idx_anggota_nik');
        $this->forge->dropColumn('dishub_anggota',['pendaftaran_id','must_change_password']);
        $this->db->query('ALTER TABLE '.$this->db->prefixTable('pengajuan_pendaftaran_anggota').' DROP INDEX uq_pengajuan_anggota');
        $this->forge->dropColumn('pengajuan_pendaftaran_anggota','anggota_id');
        // Keep widened name/address columns to avoid truncating existing data.
    }
}
