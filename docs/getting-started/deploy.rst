Deploy backup
=============

Assuming you received a copy of a production ready version.

Requirements
------------
#. Hosting environemnt with ``PHP>8.2`` and ``ext-intl`` extension enabled.
#. .zip file containing a copy of an existing install
#. .sql file containing a copy of the database


Setup
-----
#. From the received .zip, unzip all contents in the subfolder which contains composer.json to the project folder in your web environment.
#. If root folder ``vendor`` is not included with the .zip. Run composer from step 4 in `Installation <installing.rst>`_
#. Create a `Mysql` database with `PhpMyAdmin` and import the recevied .sql

`Proceed to: Configure <configure.rst>`_