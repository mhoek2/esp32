<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeviceGroupsTable extends Migration
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
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'color' => [
                'type' => 'VARCHAR',
                'constraint' => 7,
                'null' => false,
                'default => '#e0cfff',
            ],
        ]);
 
        $this->forge->addKey('id', true);

        $this->forge->createTable('device_groups', true);
    }

    public function down()
    {
        $this->forge->dropTable('device_groups', true);
    }
}
