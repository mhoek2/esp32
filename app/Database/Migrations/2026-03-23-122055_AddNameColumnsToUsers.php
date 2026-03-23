<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNameColumnsToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'firstname' => [
                'type' => 'text',
                'constraint' => 255,
                'null' => false,
            ],
            'middlename' => [
                'type' => 'text',
                'constraint' => 255,
                'null' => false,
            ],
            'lastname' => [
                'type' => 'text',
                'constraint' => 255,
                'null' => false,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'firstname');
        $this->forge->dropColumn('users', 'middlename');
        $this->forge->dropColumn('users', 'lastname');
    }
}
