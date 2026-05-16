FROM php:8.2-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql

RUN curl -sL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get install -y nodejs

WORKDIR /var/www/html

COPY . .

RUN cd realtime-server && npm install

RUN mkdir -p /var/www/html/uploads/avatars \
    && chown -R www-data:www-data /var/www/html/uploads \
    && chmod -R 755 /var/www/html/uploads

EXPOSE 80 3001