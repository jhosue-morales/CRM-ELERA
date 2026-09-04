ARG CACHE_BUST=7777777
FROM php:8.2-apache

# Instalar pdo
RUN docker-php-ext-install pdo pdo_mysql

# La matanza definitiva de los módulos
RUN rm -f /etc/apache2/mods-enabled/mpm_event.conf \
    && rm -f /etc/apache2/mods-enabled/mpm_event.load \
    && rm -f /etc/apache2/mods-enabled/mpm_worker.conf \
    && rm -f /etc/apache2/mods-enabled/mpm_worker.load \
    && a2enmod mpm_prefork

# Copiar el código
COPY . /var/www/html/

EXPOSE 80

CMD ["apache2-foreground"]