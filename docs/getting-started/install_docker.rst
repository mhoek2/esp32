Install Docker
==============

Windows
-------

#. Look for docker desktop using WSL2

Linux
-----

This is a quick guide, there are higher detailed guides online!


  
#. Prepare
    .. code-block:: bash

        sudo apt install -y ca-certificates curl gnupg lsb-release software-properties-common
	
        sudo mkdir -p /etc/apt/keyrings
        curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg

        echo \
            "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu \
            $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

#. Install
    .. code-block:: bash

        sudo apt update
        sudo apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

#. Enable docker
    .. code-block:: bash

        sudo systemctl enable docker
        sudo systemctl start docker
        sudo systemctl status docker

#. Verify install
    .. code-block:: bash

        docker version
        docker compose version

Else follow these steps:
`Proceed to: Installation <installing.rst>`_