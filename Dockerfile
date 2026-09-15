ARG CACHE_BUST=10000008

FROM php:8.2-apache

# Instalar pdo y mysqli
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Dar permisos a la carpeta uploads (durante el build)
RUN mkdir -p /var/www/html/uploads && chown -R www-data:www-data /var/www/html/uploads && chmod -R 775 /var/www/html/uploads

# Copiar tu código
COPY . /var/www/html/

EXPOSE 80

# UN SOLO CMD: Limpiar Apache + Ajustar permisos + Arrancar
CMD ["/bin/bash", "-c", "rm -f /etc/apache2/mods-enabled/mpm_event.conf && rm -f /etc/apache2/mods-enabled/mpm_event.load && rm -f /etc/apache2/mods-enabled/mpm_worker.conf && rm -f /etc/apache2/mods-enabled/mpm_worker.load && a2enmod mpm_prefork >/dev/null 2>&1; chown -R www-data:www-data /var/www/html/uploads && chmod -R 775 /var/www/html/uploads && apache2-foreground"]