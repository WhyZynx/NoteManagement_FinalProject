FROM php:8.2-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql

RUN curl -sL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get install -y nodejs

WORKDIR /var/www/html

COPY . .

RUN cd realtime-server && npm install

RUN mkdir -p /var/www/html/uploads/avatars \
    && mkdir -p /var/www/html/Assets/uploads \
    && chown -R www-data:www-data /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html/Assets/uploads \
    && chmod -R 755 /var/www/html/uploads \
    && chmod -R 755 /var/www/html/Assets/uploads

EXPOSE 80 3001