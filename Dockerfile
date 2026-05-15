FROM php:8.1-apache

RUN apt-get update && apt-get install -y nodejs npm \
    && docker-php-ext-install mysqli pdo pdo_mysql

COPY . /var/www/html/

RUN mkdir -p /var/www/html/uploads/avatars \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 777 /var/www/html/uploads \
    && chmod -R 777 /var/www/html/Assets

WORKDIR /var/www/html/realtime-server
RUN npm install

WORKDIR /var/www/html
EXPOSE 80 

CMD apache2-foreground & node /var/www/html/realtime-server/server.js