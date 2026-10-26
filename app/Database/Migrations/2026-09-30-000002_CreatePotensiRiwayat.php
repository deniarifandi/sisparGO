<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePotensiRiwayat extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'potensi_riwayat_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'potensi_va' => ['type' => 'VARCHAR', 'constraint' => 255],
            'berlaku_mulai' => ['type' => 'DATE'],
            'berlaku_sampai' => ['type' => 'DATE', 'null' => true],
            'senin' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'selasa' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'rabu' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'kamis' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'jumat' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'sabtu' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'minggu' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'mingguan' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'bulanan' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'tahunan' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('potensi_riwayat_id', true);
        $this->forge->addKey(['potensi_va', 'berlaku_mulai']);
        $this->forge->createTable('potensi_riwayat', true);

        // Snapshot data lama sebagai nilai yang berlaku sejak awal tahun berjalan.
        $this->db->query("INSERT INTO potensi_riwayat
            (potensi_va, berlaku_mulai, senin, selasa, rabu, kamis, jumat, sabtu, minggu, mingguan, bulanan, tahunan, created_at)
            SELECT potensi_va, CONCAT(YEAR(CURDATE()), '-01-01'), senin, selasa, rabu, kamis, jumat, sabtu, minggu, mingguan, bulanan, tahunan, NOW()
            FROM potensi");
    }

    public function down()
    {
        $this->forge->dropTable('potensi_riwayat', true);
    }
}
