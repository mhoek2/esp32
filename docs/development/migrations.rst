.. _migrations:

Database Migrations
===================

Apply migrations
----------------

.. code-block:: bash

    # cd to the root of the application
    # or use the build-in terminal in your IDE. eg, vscode

    # NOTE: If this is for a clean install append the --all flag


    # using docker app container, prefix commands with docker exec -it esp32_app
    docker exec -it esp32_app php spark migrate

    # using dedicated app (hostname = localhost)
    php spark migrate

Create migrations
-----------------

#. Create a migration  
    .. code-block:: bash

        php spark make:migration AddProtocol27Table

#. Migration file in app/Database/Migrations will be created with the name: 2026-03-23-095226_AddProtocol27Table.php.
#. Open the file and add the fields to the up() method. eg: 
    .. code-block:: php

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

            $this->forge->createTable('protocol_27');
        }

#. Also add the down() method to drop the table when rolling back or resetting migrations. eg:
    .. code-block:: php

        public function down()
        {
            $this->forge->dropTable('protocol_27');
        }

#. Apply the migration.
    .. code-block:: bash

        docker exec -it esp32_app php spark migrate

#. Table will now be available in the database.

Rollback migrations
-----------------

.. code-block:: bash

    # NOTE: using docker app container, prefix commands with docker exec -it esp32_app
    php spark migrate:rollback

Reset migrations
----------------

.. code-block:: bash

    # NOTE: using docker app container, prefix commands with docker exec -it esp32_app
    php spark migrate:reset


Migrations Status
----------------

.. code-block:: bash

    php spark migrate:status

Raw SQL migrations
------------------

Some features like CURRENT_TIMESTAMP for a DATETIME column require raw SQL.
    .. code-block:: php

        // include RawSql class at the top of the migration file
        use CodeIgniter\Database\RawSql;

        // then use it in the field definition
        'created_at' => [
            'type'    => 'DATETIME',
            'null'    => true,
            'default' => new RawSql('CURRENT_TIMESTAMP'),
        ],