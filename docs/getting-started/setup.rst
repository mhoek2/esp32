Setup
======

Setup Docker Package
--------------------
#. Clone the **deploy branch**
    .. code-block:: bash

        git clone --branch deploy https://github.com/mhoek2/esp32.git
        cd esp32

#. Adjust **docker-compose.yml** and **.env** to your needs
#. Compose
    .. code-block:: bash

        docker compose up -d --pull always
#. Dashboard is now accessable using the url set in .env

Updating
--------
#. Update the containers
    .. code-block:: bash

        cd esp32

        # shutdown containers
        docker compose down

        # remove appdata volume (/var/www)
        # TODO: Get rid of this ..
        docker volume rm esp32_appdata

        # pull latest & compose
        docker compose up -d --pull always