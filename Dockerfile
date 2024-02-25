# Base image - PHP with Apache
FROM php:8.2-apache

# working directory
WORKDIR /var/www/html

# Install the PHP dependency installation tool
RUN apt-get update && apt-get install -y \
        git \
        unzip \
    && docker-php-ext-install pdo_mysql \
    && a2enmod rewrite

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy the application files to the container
COPY . .

# Install PHP dependencies
RUN composer install --no-scripts --no-autoloader

# Process Composer scripts
RUN composer dump-autoload --optimize

# Set the file owner to the www-data user
RUN chown -R www-data:www-data /var/www/html

#I am changing the apache location because it is not in /var/www/html but in /var/www/html/public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public/
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Open the HTTP port
EXPOSE 80

# Start the Apache server
CMD ["apache2-foreground"]
