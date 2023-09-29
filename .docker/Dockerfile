FROM php:8.1-apache

# Copy virtual host into container
COPY conf/local.ascendion.com.conf /usr/local/apache2/conf/local.ascendion.com.conf

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Enable rewrite mode
RUN a2enmod rewrite
RUN a2enmod ssl

# Install necessary packages
RUN apt-get update && \
  apt-get install \
  libzip-dev \
  wget \
  git \
  unzip \
  -y --no-install-recommends


# Install PHP Extensions
RUN docker-php-ext-install zip
RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN docker-php-ext-enable mysqli

RUN pecl install -o -f xdebug-3.1.5 \
  && docker-php-ext-enable xdebug

# Copy composer installable
COPY ./conf/install-composer.sh ./

# Copy php.ini
COPY ./conf/php.ini /usr/local/etc/php/

# Cleanup packages and install composer
RUN apt-get purge -y g++ \
  && apt-get autoremove -y \
  && rm -r /var/lib/apt/lists/* \
  && rm -rf /tmp/* \
  && sh ./install-composer.sh \
  && rm ./install-composer.sh

# Change the current working directory
WORKDIR /var/www

# Change the owner of the container document root
RUN chown -R www-data:www-data /var/www
