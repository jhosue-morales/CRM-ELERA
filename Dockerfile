ARG CACHE_BUST=10000003
FROM php:8.2-apache

# Instalar pdo
RUN docker-php-ext-install pdo pdo_mysql

# Copiar tu código
COPY . /var/www/html/

EXPOSE 80

# BORRAR EL ARCHIVO CONFLICTIVO Y ARRANCAR APACHE
CMD ["/bin/bash", "-c", "rm -f /etc/apache2/mods-enabled/mpm_event.conf && rm -f /etc/apache2/mods-enabled/mpm_event.load && rm -f /etc/apache2/mods-enabled/mpm_worker.conf && rm -f /etc/apache2/mods-enabled/mpm_worker.load && a2enmod mpm_prefork >/dev/null 2>&1; apache2-foreground"]