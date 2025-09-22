FROM php:8.2-cli

WORKDIR /var/www/html

# Install PHP extensions including GD
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libpng-dev \
    && docker-php-ext-install zip pdo pdo_mysql mbstring xml gd \
    && apt-get clean

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy project files
COPY . .

ENV COMPOSER_MEMORY_LIMIT=-1

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Expose port
EXPOSE 10000

CMD ["php", "-S", "0.0.0.0:10000", "-t", "."]
