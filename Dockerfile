# Imagen base con PHP 8.3 + Composer
FROM php:8.3-cli

# Instalar dependencias de sistema necesarias
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libpq-dev \
    libxslt1-dev \
    libgd-dev \
    libssl-dev \
    libsodium-dev \
    librabbitmq-dev \
    pkg-config \
    git \
    unzip \
    wget \
    curl \
    nodejs \
    npm \
 && docker-php-ext-configure intl \
 && docker-php-ext-install \
    intl \
    pdo_mysql \
    xsl \
    gd \
    sockets \
 && pecl install amqp \
 && docker-php-ext-enable amqp sodium \
 && rm -rf /var/lib/apt/lists/*

# Instalar Composer globalmente
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Instalar Symfony CLI
RUN wget https://get.symfony.com/cli/installer -O - | bash \
 && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

# Crear directorio de trabajo
WORKDIR /app

# Exponer el puerto que usa "symfony serve"
EXPOSE 8000

# Comando por defecto
# --dir= tiene que indicar la carpeta del proyecto symfony
CMD ["symfony", "serve", "--no-tls", "--allow-http", "--port=8000", "--allow-all-ip"]
