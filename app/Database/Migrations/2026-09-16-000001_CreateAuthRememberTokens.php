<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuthRememberTokens extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'selector' => [
                'type'       => 'CHAR',
                'constraint' => 32,
            ],
            'token_hash' => [
                'type'       => 'CHAR',
                'constraint' => 64,
            ],
            'expires_at' => [
                'type' => 'DATETIME',
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('selector');
        $this->forge->addKey('user_id');
        $this->forge->createTable('auth_remember_tokens', true);
    }

    public function down()
    {
        $this->forge->dropTable('auth_remember_tokens', true);
    }
}
