<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class AddUploadsTable extends Migration
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
            'global' => [
                'type' => 'INT',
                'constraint' => 2,
                'default' => 0,
                'null'       => false,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
            ],
            'path' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'filename' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'extension' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'mime_type' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'bytes' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'null' => false,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);
 
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['user_id', 'name']);

        $this->forge->createTable('uploads');
    }

    public function down()
    {
        $this->forge->dropTable('uploads');
    }
}
