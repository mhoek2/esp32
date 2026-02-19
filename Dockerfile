FROM php:8.2-fpm

# Install packages needed for extensions
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libicu-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    libonig-dev \
    zlib1g-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        mysqli \
        mbstring \
        intl \
        xml \
        curl \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www

CMD ["php-fpm"]