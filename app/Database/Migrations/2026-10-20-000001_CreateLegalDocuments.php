<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLegalDocuments extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'legal_id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'document_type' => ['type' => 'VARCHAR', 'constraint' => 30],
            'title' => ['type' => 'VARCHAR', 'constraint' => 180],
            'content' => ['type' => 'TEXT'],
            'effective_date' => ['type' => 'DATE', 'null' => true],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('legal_id', true);
        $this->forge->addKey(['document_type', 'is_active']);
        $this->forge->createTable('legal_documents', true);

        $now = date('Y-m-d H:i:s');
        $this->db->table('legal_documents')->insertBatch([
            ['document_type' => 'terms', 'title' => 'Ketentuan Layanan Sisparma Jukir', 'effective_date' => date('Y-m-d'), 'is_active' => 1, 'content' => "Dengan menggunakan Sisparma Jukir, pengguna menyetujui ketentuan ini. Layanan mencakup pendaftaran dan pengelolaan anggota, KTA, target/setoran, transaksi, tagihan, pengajuan, dan komunikasi bantuan.\n\nPengguna wajib memberikan data yang benar, menjaga kredensial, dan tidak menyalahgunakan layanan, mengakses data pihak lain, mengirim data palsu, atau mengunggah materi yang melanggar hukum maupun hak pihak lain.\n\nLayanan dapat diperbarui, dihentikan sementara, atau mengalami gangguan pemeliharaan. Pengelola berupaya menjaga ketersediaan dan akurasi layanan secara wajar. Ketentuan dapat diperbarui dan tunduk pada hukum Republik Indonesia.", 'created_at' => $now, 'updated_at' => $now],
            ['document_type' => 'privacy', 'title' => 'Kebijakan Privasi Sisparma Jukir', 'effective_date' => date('Y-m-d'), 'is_active' => 1, 'content' => "Sisparma dapat memproses username, nama, ID anggota, nomor telepon, NIK, nomor KK, alamat, data profil, data KTA, transaksi/tagihan, pesan bantuan, serta foto selfie, KTP, dan KK yang dikirim pengguna.\n\nData digunakan untuk autentikasi, verifikasi, administrasi anggota, pengelolaan layanan parkir, dukungan, keamanan, dan kewajiban hukum. Kamera digunakan ketika pengguna mengambil atau memperbarui foto dokumen; data dapat dikirim melalui jaringan ke server Sisparma untuk diproses.\n\nData tidak dijual. Data dapat dibagikan kepada pihak berwenang atau penyedia layanan operasional sejauh diperlukan dan sesuai hukum. Pengguna dapat meminta akses, koreksi, pembaruan, atau penghapusan data melalui pengelola, dengan batasan apabila data masih diperlukan untuk layanan atau kewajiban hukum.", 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down() { $this->forge->dropTable('legal_documents', true); }
}
