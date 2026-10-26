<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDikelolaDishubRetritype extends Migration
{
    public function up()
    {
        $exists = $this->db->table('retritype')
            ->where('retrytipe_nama', 'Dikelola Dishub')
            ->countAllResults();

        if (!$exists) {
            $this->db->table('retritype')->insert([
                'retrytipe_nama' => 'Dikelola Dishub',
            ]);
        }
    }

    public function down()
    {
        $jenis = $this->db->table('retritype')
            ->where('retrytipe_nama', 'Dikelola Dishub')
            ->get()->getRowArray();

        if ($jenis && !$this->db->table('dishub_titpar')
            ->where('titpar_retritypeid', $jenis['retritype_id'])
            ->countAllResults()) {
            $this->db->table('retritype')
                ->where('retritype_id', $jenis['retritype_id'])
                ->delete();
        }
    }
}
