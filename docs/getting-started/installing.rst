Installation
============

Setting up a fresh install

Requirements
------------
1. Hosting environemnt with ``PHP>8.2`` and ``ext-intl`` extension enabled.
2. A fresh copy containing core files from the github repository.

Setup
-----

#. Install `Composer<https://getcomposer.org/>`_
#. Clone the repository from GitHub to your web environment. (see :ref:`Cloning <cloning-guide>` if repo is private)
#. Open a terminal of choice and change current directory to the root directory of the project folder.
#. Run the following command
    .. code-block:: bash

        # This will install the dependencies set in composer.json
        composer install

`Proceed to: Configure <configure.rst>`_




Manual (Not recommended)
------------------------
#. Download the .zip file containg project files.
#. Extract to your webserver, then open a terminal in the project folder.
    .. code-block:: bash

        # This will not install dev-tools
        composer create-project codeigniter4/appstarter esp32
        composer require phpdocumentor/shim
        composer require codeigniter4/shield:dev-develop
        php spark shield:setup


#. Create a Mysql database with PhpMyAdmin and import ``sql/esp32.sql``

`Proceed to: Configure <configure.rst>`_