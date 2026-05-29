FROM php:8.2-fpm-alpine

RUN docker-php-ext-install pdo pdo_mysql mysqli

RUN apk add --no-cache nginx

WORKDIR /var/www/html
COPY . .

COPY nginx.conf /etc/nginx/http.d/default.conf

EXPOSE 80

CMD sh -c "php-fpm -D && nginx -g 'daemon off;'"
