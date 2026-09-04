FROM php:8.2-apache

# Instala las extensiones de PHP para tu proyecto
RUN docker-php-ext-install pdo pdo_mysql

# Limpia los conflictos de módulos de Apache
RUN rm -f /etc/apache2/mods-enabled/mpm_event.conf \
    && rm -f /etc/apache2/mods-enabled/mpm_event.load \
    && rm -f /etc/apache2/mods-enabled/mpm_worker.conf \
    && rm -f /etc/apache2/mods-enabled/mpm_worker.load \
    && a2enmod mpm_prefork rewrite

# Copia el código de tu sitio web
COPY . /var/www/html/

# Exponer el puerto 80
EXPOSE 80

# Fuerza a Apache a escuchar en el puerto 80 en todas las interfaces
CMD ["apache2-foreground"]