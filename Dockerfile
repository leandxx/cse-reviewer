FROM php:8.2-cli

RUN docker-php-ext-install pdo pdo_mysql mysqli

WORKDIR /app
COPY . .

CMD mysql -h $MYSQLHOST -P $MYSQLPORT -u $MYSQLUSER -p$MYSQLPASSWORD $MYSQLDATABASE < database/setup.sql && php -S 0.0.0.0:$PORT -t .
