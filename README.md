# Docker Symfony 4 (PHP7-FPM - NGINX - MySQL)
 
## Installation

1. Build/run containers with (with and without detached mode)

    ```bash
    $ docker-compose -f docker-compose.dev.yml build
    ```

2. Update your system host file (add wallets)

    ```bash
    # MacOs:
    $ sudo sh -c 'echo "127.0.0.1   wallets" >> /etc/hosts'
    ----
    # UNIX: Get container IP address and update host (replace IP according to your configuration) (on Windows, edit C:\Windows\System32\drivers\etc\hosts)
    $ sudo sh -c 'echo $(docker network inspect bridge | grep Gateway | grep -o -E '[0-9\.]+') "wallets" >> /etc/hosts'
    ```

    **Note:** For **OS X**, please take a look [here](https://docs.docker.com/docker-for-mac/networking/) and for **Windows** read [this](https://docs.docker.com/docker-for-windows/#/step-4-explore-the-application-and-run-examples) (4th step).

3. Prepare app before up
    1. Create environment file for your device and enter appropriate parameters
    ```bash
       $ mv .env.dist .env
    ```
    
3. Prepare Symfony app
    1. Enter to container

        ```bash
        $ docker exec -it app_wallets bash
        ```
    2. Composer && Symfony
        ```bash
        $ composer install
        # ---
        # Symfony
        $ sf3 doctrine:database:create
        $ sf3 doctrine:migrations:migrate --no-interaction
        $ sf3 doctrine:fixtures:load --no-interaction
        ```

## Usage

Just run `docker-compose -f docker-compose.dec.yml up -d --build`, then:
  
* [API DOC](http://wallets/api/doc)
 