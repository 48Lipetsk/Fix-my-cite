FROM php:8.1-apache

# Install PHP extensions required by OpenCart
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    unzip \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        gd \
        mysqli \
        pdo_mysql \
        zip \
        intl \
        mbstring \
        opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache modules
RUN a2enmod rewrite headers

# PHP configuration
RUN echo "upload_max_filesize = 50M" > /usr/local/etc/php/conf.d/opencart.ini \
    && echo "post_max_size = 50M" >> /usr/local/etc/php/conf.d/opencart.ini \
    && echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/opencart.ini \
    && echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/opencart.ini \
    && echo "short_open_tag = On" >> /usr/local/etc/php/conf.d/opencart.ini

# Apache configuration — allow .htaccess overrides
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Set working directory
WORKDIR /var/www/html

# Set permissions
RUN chown -R www-data:www-data /var/www
