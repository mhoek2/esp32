<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeviceMetaTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null'           => false,
                'auto_increment' => true,
            ],
            'mac' => [
                'type' => 'VARCHAR',
                'constraint' => 17,
                'null' => false,
            ],
        ]);
 
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['mac']);

        $this->forge->createTable('device_meta');
    }

    public function down()
    {
        $this->forge->dropTable('device_meta');
    }
}
