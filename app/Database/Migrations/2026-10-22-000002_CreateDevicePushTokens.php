<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDevicePushTokens extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'VARCHAR', 'constraint' => 32],
            'device_token' => ['type' => 'VARCHAR', 'constraint' => 255],
            'platform' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'android'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('device_token');
        $this->forge->addKey('user_id');
        $this->forge->createTable('device_push_tokens', true);
    }

    public function down()
    {
        $this->forge->dropTable('device_push_tokens', true);
    }
}
