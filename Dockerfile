FROM php:8.1-apache

RUN apt-get update && apt-get install -y nodejs npm \
    && docker-php-ext-install mysqli pdo pdo_mysql

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html/Assets \
    && chmod -R 777 /var/www/html/Assets

WORKDIR /var/www/html/realtime-server
RUN npm install

WORKDIR /var/www/html
EXPOSE 80 3001

CMD apache2-foreground & node /var/www/html/realtime-server/server.js