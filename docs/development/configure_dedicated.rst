Configure Dedicated Server
==========================

A few things need to be set-up and configured.

Some composer modules might have changed been updated, so we want to verify a few things.

.. tip::
    Hosting environment requires ``PHP>8.2`` and ``ext-intl`` extension enabled.


Adjust the .env configuration
-----------------------------

#. Uncomment and point ``app.baseURL`` in ``.env`` to the root url of your server. eg: http://esp32.your-domain.com/
    .. code-block:: php

        app.baseURL = ''

#. Ensure database connection variables uncommented and correctly set in ``.env``
    .. code-block:: php

        database.default.hostname = localhost <- Set this
        database.default.database = esp32_db
        database.default.username = esp32_user
        database.default.password = esp32_pass
        database.default.DBDriver = MySQLi
        database.default.DBPrefix =
        database.default.port = 3306


When not using the .env file:
-----------------------------

#. Set variable ``$baseURL`` in ``app/Config/App.php`` to the root url of your server. eg: http://esp32.your-domain.com/
    .. code-block:: php

        /**
        * --------------------------------------------------------------------------
        * Base Site URL
        * --------------------------------------------------------------------------
        *
        * URL to your CodeIgniter root. Typically, this will be your base URL,
        * WITH a trailing slash:
        *
        * E.g., http://example.com/
        */
        public string $baseURL = 'http://esp32.your-domain.com/';

#. Ensure database connection variables are correctly set in ``app/Config/Database.php``
    .. code-block:: php

        /**
        * The default database connection.
        *
        * @var array<string, mixed>
        */
        public array $default = [
            'DSN'          => '',
            'hostname'     => 'localhost',
            'username'     => 'your-username',
            'password'     => 'your-password',
            'database'     => 'your-database-name',
            'DBDriver'     => 'MySQLi',

Setup Database
---------------

#. Run the migration to create the database tables. See :ref:`Database Migrations <migrations>` for more info. or Run:
    .. code-block:: bash
        # phpmyadmin: http://localhost/phpmyadmin

        # using dedicated app (hostname = localhost)
        php spark migrate --all

        # setup demo user (admin@esp32.io:admin) and devices
        docker exec -it esp32_app php spark db:seed DemoSeeder

Verify
------

#. The Login controller is a clone of Shield's, this prevents the custom login page from being overwritten. 
        Ensure ``Controllers\LoginController::loginView()`` matches ``CodeIgniter\Shield\Controllers\LoginController::loginView()``.
		
		
Configure Apache server
-----------------------

If you run a fresh apache webserver, you might want to set up the following.

#. Give valid permissions to writable folder and subfolders
    .. code-block:: bash

        # This will add permissions, use valid user, commonly www-data for apache2 servers
        sudo chown -R www-data writable

#. Enable modrewrite
    .. code-block:: bash

        # Check if modrewrite is enabled:
        apache2ctl -M | grep rewrite

        # when nothing returns, enable using:
        sudo a2enmod rewrite
        sudo systemctl restart apache2

#. Create Virtual Host 
    .. code-block:: bash

        # When you don't have this set up already, add a vhost.
        # sudo nano /etc/apache2/sites-available/esp32.your-domain.com.conf
        <VirtualHost *:80>
            ServerName esp32.your-domain.com

            ServerAdmin webmaster@localhost
            DocumentRoot /var/www/html/esp32/public

            <Directory /var/www/html/esp32/public>
                Options Indexes FollowSymLinks
                AllowOverride All
                Require all granted
            </Directory>

            ErrorLog ${APACHE_LOG_DIR}/esp32_error.log
            CustomLog ${APACHE_LOG_DIR}/esp32_access.log combined
        </VirtualHost>

        # save & exit
        sudo a2ensite esp32.your-domain.com.conf
        sudo systemctl reload apache2

        # disable default vhost:
        sudo a2dissite 000-default.conf
        sudo systemctl reload apache2

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

Done
----
	You should now be able to see a correctly setup instance when you navigate to: http://esp32.your-domain.com/
	