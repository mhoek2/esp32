ESLint
======

This platform integrates `ESLint<https://eslint.org/>`_.

ESLint is an open source project that helps you find and fix problems with your JavaScript code.

Configuration
-------------

File ```eslint.config.mjs``` in the root folder contains settings and code convention rules

Inline PHP
##########
A custom plugin is included that removes inline PHP syntax

CI-CD
-----

There is a CI-CD workflow ```path/to/my/project/github/workflows/docs.yml``` in place containing a step that automaticly scans the codebase.
The output report is processed in a auto-generated .rst file which stores in ```path/to/my/project/docs/report/``` and included in the phpdocs guide 
when a push or pull request event triggers and is uploaded to the ```gh-pages``` branch.

Run locally
-----------

Follow these steps to run ESLint locally 

#. Make sure npm packages are installed.
#. If it is missing
    .. code-block:: bash
   
        # cd to the root directory of the project folder
        
        # This will install the dependencies set in package.json
        npm install
#. Open a terminal of choice and change current directory to the root directory.
#. Run the following command
    .. code-block:: bash
   
        npx eslint "app/Views/**/*.php" --format stylish
        
    This will report directly to the terminal
