FROM php:8.5-apache-trixie
ENV DEBIAN_FRONTEND=noninteractive
RUN apt-get update && apt-get install -y ca-certificates curl gnupg libkrb5-dev nmap inetutils-ping net-tools libpng-dev libxml2-dev libxslt1-dev libcurl4-openssl-dev zip unzip git libfreetype6-dev libjpeg62-turbo-dev libpng-dev libldap-dev && rm -r /var/lib/apt/lists/*
RUN a2enmod rewrite
RUN docker-php-ext-install pdo_mysql gettext xsl pcntl ldap && docker-php-ext-configure gd --with-freetype --with-jpeg && docker-php-ext-install gd
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN sed -i /etc/apache2/sites-enabled/000-default.conf -e 's,DocumentRoot /var/www/html, DocumentRoot /var/www/html/public,g' -e 's,:80,:8080,g' && sed -i /etc/apache2/ports.conf -e 's,Listen 80,Listen 8080,g'
RUN mkdir -p /etc/apt/keyrings && curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg
RUN echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_24.x nodistro main" | tee /etc/apt/sources.list.d/nodesource.list
RUN apt-get update && apt-get install nodejs -y && apt-get clean && rm -r /var/lib/apt/lists/*
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
ENV APACHE_HTTP_PORT=8080
EXPOSE 8080
WORKDIR /var/www/html

COPY . /var/www/html/

RUN mkdir -p /.config storage && \
    chmod 777 /.config && \
    chmod 777 /var/www/html/storage && \
    chmod 777 /var/www/html/public/ && \
    composer install --no-dev && \
    npm install && \
    npm run build && \
    php artisan storage:link && \
    chmod 755 /var/www/html/public/
