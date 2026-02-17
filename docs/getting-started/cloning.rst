.. _cloning-guide:

Cloning
=======

Cloning (private repo only)
------------------------------

When the repository is private, a deployment key is required

.. tip::
	Replace 'repo-user' in the following snippets with the repo owner username

#. Create a public key using ssh-keygen
    .. code-block:: bash

        # Create a ssh pub key
        cd ~/.ssh/
        ssh-keygen -t rsa -b 4096 -C "esp-deploy"

#. *Optional SSH host: 
    .. code-block:: bash

        # use git@esp instead of git@github.com
        # for now, use git@github.com in the setup scripts (clone.sh & update.sh)

        # Add to ssh config
        nano ~/.ssh/config:

        Host esp
          HostName github.com
          User repo-user
          IdentityFile ~/.ssh/esp-deploy

        # Test authentication
        ssh -T git@esp

#. Go to GitHub.com and navigate to: **Your repository -> Settings -> Deploy keys**
#. Click **Add a deploy key** and keep it read-only!
#. Enter a name, then copy the content of the .pub file in the value field.
    .. code-block:: bash

        # find the content of the .pub key:
        cat ~/.ssh/esp-deploy.pub


#. Create a clone.sh file
    .. code-block:: bash

        # cd to your root webserver path.
        cd /var/www/html
        sudo nano clone.sh

#. Write to clone.sh
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

        # Clone the repo
        git clone git@github.com:repo-user/windowstate.git

#. Make clone.sh executable
    .. code-block:: bash

        sudo chmod +x clone.sh

#. Run clone.sh
    .. code-block:: bash

        ./clone.sh
