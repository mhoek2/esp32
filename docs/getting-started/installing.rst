Installation
============

Setting up a fresh install

Deploy Docker Package
--------------------------
#. Clone the deploy branch
    .. code-block:: bash

        git clone --branch deploy https://github.com/mhoek2/esp32.git
#. Adjust docker-compose.yml and .env to your needs
#. Compose
    .. code-block:: bash

        docker compose up -d --pull always

Development
-----------
#. Install `Composer<https://getcomposer.org/>`_
#. Clone the repository. see :ref:`Cloning <cloning-guide>`
#. Open a terminal and change working directory to the root of the project folder.
#. Run the following command
    .. code-block:: bash

        # This will install the dependencies set in composer.json
        composer install

Using Docker
------------
`Proceed to: Installing Docker if you have not <install_docker.rst>`_

`Proceed to: Configure Docker <configure_docker.rst>`_

Using Dedicated Server
------------
`Proceed to: Configure Dedicated <configure_dedicated.rst>`_
