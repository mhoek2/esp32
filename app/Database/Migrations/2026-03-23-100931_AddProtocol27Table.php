<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProtocol27Table extends Migration
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
            'state' => [
                'type' => 'INT',
                'constraint' => 10,
                'null' => false,
                'default' => 0,
            ],
        ]);
 
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['mac']);

        $this->forge->createTable('protocol_27', true);
    }

    public function down()
    {
        $this->forge->dropTable('protocol_27', true);
    }
}
