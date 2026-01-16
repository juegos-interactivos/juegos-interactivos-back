FROM php:8.4-fpm

# Argumentos definidos en docker-compose.override.yml
ARG user
ARG uid

# 1. Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    default-mysql-client \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. Instalar extensiones de PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# 3. Instalar y habilitar Xdebug (Opcional pero recomendado para dev local)
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# 4. Configuración básica de Xdebug (para que conecte con tu IDE)
RUN echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# 5. Obtener Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 6. Crear el usuario del sistema con el mismo UID que tu usuario de EndeavourOS
# Esto es la magia para evitar problemas de permisos
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# 7. Establecer directorio de trabajo
WORKDIR /var/www

# 8. Cambiar al usuario sin privilegios
USER $user
