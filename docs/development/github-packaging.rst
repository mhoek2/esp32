Github Packaging
================

Collection of CLI commands for packaging a docker image manually on Github.

This can be integrated in **CICD** for convenience later.

Personal Access Token - GitHub (PTA)
------------------------------------

#. Go to your account **Settings → Developer Settings → Personal Access Tokens → Tokens (classic)**
#. Generate new classic token with **read:packages** and **write:packages** enabled.
#. Store the generated token somewhere safe and treat it as a password.

#. Login to GitHub Container Registry (GHCR)
    .. code-block:: bash

        # cd to the root of the application
        # or use the build-in terminal in your IDE. eg, vscode
        # login with your generated PTA 
        # replace 'mhoek2' with your username
        echo ghp_xxxxxxxxxxxxxxxxxxxx | docker login ghcr.io -u mhoek2 --password-stdin

Build & Publish package to GitHub
---------------
**Use bash script**
    .. code-block:: bash

        # cd to the root of the application
        # or use the build-in terminal in your IDE. eg, vscode
        # NOTE: edit 'package-latest.sh' to change version number of repo
        ./package-latest.sh

**Manual steps**

#. Build and push package with version number
    .. code-block:: bash

        # explicit use of Dockerfile.deploy because:
        # in production; the appdata (php/assets) that lives in 'var/www' is bundled in the image
        # in development; the appdata lives in the root of the project
        docker build -f Dockerfile.deploy -t ghcr.io/mhoek2/esp32:1.0.0 .
        docker push ghcr.io/mhoek2/esp32:1.0.0

#. Tagging with latest
    .. code-block:: bash

        # build and push
        docker build -f Dockerfile.deploy-t ghcr.io/mhoek2/esp32:1.0.0 .
        
        # tag
        docker tag ghcr.io/mhoek2/esp32:1.0.0 ghcr.io/mhoek2/esp32:latest

        # push
        docker push ghcr.io/mhoek2/esp32:1.0.0
        docker push ghcr.io/mhoek2/esp32:latest