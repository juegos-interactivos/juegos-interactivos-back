# juegos-interactivos-back

> Proyecto back de juegos-interactivos

 ## Build Setup

 - Cambiar `docker-compose.yml.example` a `docker-compose.override.yml`
 - Configurar el archivo `docker-compose.override.yml` con tu **usuario** e **uid** actual

 - Tener instalado, docker, docker-compose, composer.


```bash
# Install dependencies
composer install

# Run docker containers first time
docker-compose up -d --build

# Run docker normally afterwards
docker-compose up -d 
```

sudo apt-get install composer
sudo LC_ALL=C.UTF-8 add-apt-repository ppa:ondrej/php
sudo apt install php8.4-cli
sudo update-alternatives --config php
sudo apt-get install php-xml
sudo apt install php8.4-intl
sudo apt install php8.4-mbstring
