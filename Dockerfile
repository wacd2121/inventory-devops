# Dockerfile
FROM php:8.2-apache

# Install PDO MySQL and MySQLi drivers for PHP database connectivity
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Enable Apache mod_rewrite for clean URLs if needed
RUN a2enmod rewrite

# Copy system code files into Apache container workdir
COPY . /var/www/html/

# Modify permissions for Apache runtime user
RUN chown -R www-data:www-data /var/www/html

# Expose HTTP port
EXPOSE 80
