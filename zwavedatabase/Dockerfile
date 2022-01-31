FROM php:8.1.1-apache

RUN apt-get update \
  && apt-get install -y \
    mariadb-client

RUN apt-get install -y \
    libbz2-dev \
    zlib1g \
    libpng-dev \
    libldap-common \
    libldap2-dev \
    libpq-dev \
    libxslt1-dev \
    zip \
    unzip \
    gnupg2 \
    unixodbc-dev

RUN docker-php-ext-install \
  mysqli \
  bcmath \
  bz2 \
  calendar \
  exif \
  gd \
  gettext \
  ldap \
  pcntl \
  pdo_mysql \
  sockets \
  xsl 
