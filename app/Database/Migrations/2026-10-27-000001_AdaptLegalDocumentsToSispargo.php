<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AdaptLegalDocumentsToSispargo extends Migration
{
    public function up()
    {
        foreach ($this->db->table('legal_documents')->get()->getResultArray() as $row) {
            $data = [];
            foreach (['title', 'content'] as $field) {
                $value = str_ireplace(
                    ['Sisparma Jukir', 'Sisparma', 'Kota Malang', 'malangkota.go.id'],
                    ['SISPARGO Kota Bogor', 'SISPARGO', 'Kota Bogor', 'kotabogor.go.id'],
                    $row[$field]
                );
                if ($value !== $row[$field]) {
                    $data[$field] = $value;
                }
            }
            if ($data) {
                $data['updated_at'] = date('Y-m-d H:i:s');
                $this->db->table('legal_documents')->where('legal_id', $row['legal_id'])->update($data);
            }
        }
    }

    public function down()
    {
        // Preserve edited policies; previous wording cannot be reconstructed safely.
    }
}
