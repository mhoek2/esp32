<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DemoSeeder extends Seeder
{
    private function generateFakeMac() {
        $mac = [
            0x02, // Locally administered
            mt_rand(0x00, 0x7F),
            mt_rand(0x00, 0xFF),
            mt_rand(0x00, 0xFF),
            mt_rand(0x00, 0xFF),
            mt_rand(0x00, 0xFF),
        ];

        return implode(':', array_map(fn($b) => sprintf('%02X', $b), $mac));
    }

    private function createSensor( $config )
    {
        $_mac = $this->generateFakeMac();
        $config['device']['mac'] = $_mac;
        $config['protocol']['mac'] = $_mac;

        $this->db->table('devices')->insert(
            $config['device']
        );

        $this->db->table('protocol_' . $config['device']['protocol'])->insert(
            $config['protocol']
        );
    }

    public function run()
    {
        // Users
        $this->db->table('users')->insert([
            'username' => '3512d1b3ec7aba55b72d',
            'firstname' => 'Admin',
            'middlename' => '',
            'lastname' => 'ESP32',
            'active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $user_id = $this->db->insertID();

        $this->db->table('auth_groups_users')->insert([
            'user_id' => (int)$user_id,
            'group' => 'admin',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->db->table('auth_identities')->insert([
            'user_id' => (int)$user_id,
            'type' => 'email_password',
            'secret' => 'admin@esp32.io',
            'secret2' => password_hash('admin', PASSWORD_DEFAULT),
        ]);

        // Demo sensors
        $this->db->table('device_groups')->insert([
            'name' => 'Group one',
            'color' => '#e0cfff',
        ]);
        $device_group_id = $this->db->insertID();

        $this->createSensor( [
            'device' => [
                'group_id'  => (int)$device_group_id,
                'name'      => 'Sensor one',
                'mac'       => 'auto-generated',
                'protocol'  => 27,
                'sleep'     => 0,
                'map_x'     => 0.934729,
                'map_y'     => 0.347931,
            ],
            'protocol' => [
                'mac'       => 'auto-generated',
                'state'     => 1,
            ]
        ]); 

        $this->createSensor( [
            'device' => [
                'group_id'  => (int)$device_group_id,
                'name'      => 'Sensor two',
                'mac'       => 'auto-generated',
                'protocol'  => 27,
                'sleep'     => 1,
                'map_x'     => 0.734729,
                'map_y'     => 0.247931,
            ],
            'protocol' => [
                'mac'       => 'auto-generated',
                'state'     => 0,
            ]
        ]); 

    }
}
