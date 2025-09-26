FROM php:8.2-apache

# Instalar dependencias (netcat para el script de espera)
RUN apt-get update && apt-get install -y netcat-openbsd && apt-get clean

# Instalar extensiones de PHP
RUN docker-php-ext-install pdo pdo_mysql

# Habilitar mod_rewrite de Apache
RUN a2enmod rewrite

# Copiar el código de la aplicación
# Nota: los volúmenes en docker-compose sobreescribirán esto, pero es bueno tenerlo para builds sin volúmenes
COPY ./backend /var/www/html/backend
COPY ./config /var/www/html/config
COPY ./database /var/www/html/database

# Copiar y preparar el entrypoint
COPY ./backend/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
