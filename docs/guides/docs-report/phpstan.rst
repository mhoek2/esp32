PHPStan 
=======

This platform integrates `PHPStan<https://phpstan.org/>`_.

This scans the whole codebase and looks for both obvious & tricky bugs. Even in those rarely executed if statements that certainly aren't covered by tests.

Configuration
-------------

File ```phpstan.neon``` in the root folder contains settings

CI-CD
-----

There is a CI-CD workflow ```path/to/my/project/github/workflows/docs.yml``` in place containing a step that automaticly scans the codebase.
The output report is processed in a auto-generated .rst file which stores in ```path/to/my/project/docs/report/``` and included in the phpdocs guide 
when a push or pull request event triggers and is uploaded to the ```gh-pages``` branch.

Run locally
-----------

.. important::

	Continue assuming you to have a local development environment active. A web server such as  `XAMPP<https://www.apachefriends.org/>`_, or just apache with php if you prefer custom installation.

Follow these steps to run PHPStan locally 
	
#. Make sure ```path/to/my/project/vendor/bin``` contains phpstan.
#. If it is missing
    .. code-block:: bash
   
        # cd to the root directory of the project folder
        
        # This will install the dependencies set in composer.json
        composer install
#. Open a terminal of choice and change current directory to the root directory.
#. Run the following command
    .. code-block:: bash
   
        # linux
        vendor/bin/phpstan analyse
        
        # windows
        php vendor/bin/phpstan analyse
        
    This will report directly to the terminal
