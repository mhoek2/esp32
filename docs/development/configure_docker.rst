Configure Docker
================

A few things need to be set-up and configured.

Some composer modules might have changed been updated, so we want to verify a few things.

.. tip::
    Requires docker and docker compose to be installed

Setup containers
----------------
#. Open a terminal and change working directory to the root of the project folder.
#. If you want to serve on custom ports or database credentials, modify in ``docker-compose.yml``
#. Deploy the docker images and services
    .. code-block:: bash

        # Set up mysql, nginx and app services:
        docker compose up -d --build

Setup domain
------------

#. Uncomment and point ``app.baseURL`` in ``.env`` to the root url of your server. eg: http://esp32.your-domain.com/
    .. code-block:: php

        app.baseURL = ''

#. Ensure database connection variables uncommented and correctly set in ``.env``
    .. code-block:: php

        database.default.hostname = db
        database.default.database = esp32_db
        database.default.username = esp32_user
        database.default.password = esp32_pass
        database.default.DBDriver = MySQLi
        database.default.DBPrefix =
        database.default.port = 3306

Setup Database
---------------

#. Run the migration to create the database tables. See :ref:`Database Migrations <migrations>` for more info. or Run:
    .. code-block:: bash
        # phpmyadmin: http://localhost/phpmyadmin

        # using docker app container, prefix commands with docker exec -it esp32_app
        docker exec -it esp32_app php spark migrate --all
       
        # setup demo user (admin@esp32.io:admin) and devices
        docker exec -it esp32_app php spark db:seed DemoSeeder
     

Access Rights
------

#. Give valid permissions to writable folder and subfolders
    .. code-block:: bash

        # This will add permissions, use valid user, commonly www-data for apache2 servers
        sudo chown -R www-data writable

Done
----
	You should now be able to see a correctly setup instance when you navigate to: http://esp32.your-domain.com/

.. tip::
	Additional info:

Verify
------

#. The Login controller is a clone of Shield's, this prevents the custom login page from being overwritten. 
        Ensure ``Controllers\LoginController::loginView()`` matches ``CodeIgniter\Shield\Controllers\LoginController::loginView()``.

Notes
-----

#. Usfull docker commands: 
    .. code-block:: bash

        docker compose exec db mysql -uroot -proot -e "SELECT User,Host FROM mysql.user;"
        docker compose exec db mysql -uroot -proot -e "SHOW DATABASES;"
        docker compose exec db mysql -uroot -proot -e "USE esp32_db; SHOW TABLES;"

        # Start all services defined in docker-compose.yml in detached mode
        docker compose up -d

        # Build (or rebuild) images and start services in detached mode
        docker compose up -d --build

        # Stop and remove containers, networks, and default resources
        docker compose down

        # Stop and remove containers, networks, AND associated named volumes
        docker compose down -v

        # change environment to a docker container, to close type; exit
        # use either container name or ID
        docker exec -it esp32_app /bin/bash

        # directory on host to volumes
        ls /var/lib/docker/volumes/esp32_appdata/_data/

        # list volumes
        docker volume ls

        # remove volume (container has to be shut down)
        docker volume rm esp32_appdata

#. TIP: Temporary enable error printing:
    .. code-block:: bash

        nano app/Config/Boot/production.php
        # Change:
        ini_set('display_errors', '0');
        # To:
        ini_set('display_errors', '1');

#. ERROR: Call to a member function getErrors() on null
    .. code-block:: bash

        # If you encounter a php error during login, likely reason is a glitch with shield.
        # Try the following commands in the root of the project using the cli:

        composer require codeigniter4/shield:^1.1
        composer update