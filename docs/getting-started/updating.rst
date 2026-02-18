Updating
========

Updating (cloned version only)
------------------------------

.. tip::
	- Assuming you followed deployment key setup in :ref:`Cloning <cloning-guide>`
	- If you use a fork, replace 'mhoek2' in the following snippets with your username

#. Create a update.sh file
    .. code-block:: bash

        # cd to your root application instance path. (NOT server root like clone.sh)
        cd /var/www/html/esp32
        sudo nano update.sh

#. Write to update.sh
    .. code-block:: bash

        #!/bin/bash
        #

        # Check if ssh-agent is running
        if [ -z "$SSH_AUTH_SOCK" ]; then
            echo "Starting ssh-agent..."
            eval "$(ssh-agent -s)"
        fi

        # Check if the key is already added
        if ! ssh-add -l | grep -q "esp-deploy"; then
            echo "Adding SSH key..."
            ssh-add ~/.ssh/esp-deploy
        else
            echo "SSH key already added."
        fi

        # Update the repo
        git pull git@github.com:mhoek2/esp32.git

#. Make update.sh executable
    .. code-block:: bash

        sudo chmod +x update.sh

#. Run update.sh
    .. code-block:: bash

        ./update.sh
