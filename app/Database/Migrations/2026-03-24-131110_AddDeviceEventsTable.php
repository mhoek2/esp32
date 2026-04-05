<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class AddDeviceEventsTable extends Migration
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
            'type' => [
                'type' => 'ENUM',
                'constraint' => ['sta_sleep', 'sta_awake', 'receive'],
                'null' => false,
            ],
            'json' => [
                'type' => 'JSON',
                'null' => false,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);
 
        $this->forge->addKey('id', true);

        $this->forge->createTable('device_events', true);
    }

    public function down()
    {
        $this->forge->dropTable('device_events', true);
    }
}
