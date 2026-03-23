<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class AddDevicesTable extends Migration
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
            'group_id' => [
                'type' => 'INT',
                'constraint' => 10,
                'null'       => false,
                'default'    => 0,
            ],
            'name' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'mac' => [
                'type' => 'VARCHAR',
                'constraint' => 17,
                'null' => false,
            ],
            'protocol' => [
                'type' => 'INT',
                'constraint' => 10,
                'null' => true,
                'default' => 0,
            ],
            'sleep' => [
                'type' => 'INT',
                'constraint' => 10,
                'null' => false,
                'default' => 0,
            ],
            'map_x' => [
                'type' => 'FLOAT',
                'null' => false,
                'default' => 0,
            ],
            'map_y' => [
                'type' => 'FLOAT',
                'null' => false,
                'default' => 0,
            ],
        ]);
 
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['mac']);

        $this->forge->createTable('devices');
    }

    public function down()
    {
        $this->forge->dropTable('devices');
    }
}
