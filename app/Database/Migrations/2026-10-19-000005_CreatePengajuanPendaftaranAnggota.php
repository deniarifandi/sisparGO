<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreatePengajuanPendaftaranAnggota extends Migration
{
    public function up()
    {
        $fields = [
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'nomor_pengajuan' => ['type' => 'VARCHAR', 'constraint' => 40],
            'request_id' => ['type' => 'VARCHAR', 'constraint' => 64],
            'payload_hash' => ['type' => 'CHAR', 'constraint' => 64],
            'nama_lengkap' => ['type' => 'VARCHAR', 'constraint' => 150],
            'nik' => ['type' => 'CHAR', 'constraint' => 16],
            'tanggal_lahir' => ['type' => 'DATE', 'null' => true],
            'alamat_lengkap' => ['type' => 'TEXT', 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'menunggu'],
            'catatan_verifikasi' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME'],
            'updated_at' => ['type' => 'DATETIME'],
        ];
        foreach (['nomor_kk'=>16, 'no_handphone'=>20, 'tempat_lahir'=>50,
            'jenis_kelamin'=>20, 'provinsi'=>100, 'kabupaten_kota'=>100,
            'kecamatan_id'=>30, 'kelurahan_id'=>30, 'pekerjaan'=>100,
            'pendidikan_terakhir'=>100, 'agama'=>20, 'pas_foto'=>255,
            'dokumen_ktp'=>255, 'dokumen_kk'=>255] as $name => $length) {
            $fields[$name] = ['type'=>'VARCHAR', 'constraint'=>$length, 'null'=>true];
        }
        $this->forge->addField($fields);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('nomor_pengajuan');
        $this->forge->addUniqueKey('request_id');
        $this->forge->addUniqueKey('nik');
        $this->forge->addKey(['status', 'created_at']);
        $this->forge->createTable('pengajuan_pendaftaran_anggota');
    }
    public function down()
    {
        $this->forge->dropTable('pengajuan_pendaftaran_anggota');
    }
}
