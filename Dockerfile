# Fuerza a Railway a ignorar cualquier build anterior (cambia el hash)
ARG CACHE_BUST=20260904-FINAL

FROM php:8.2-apache

# Instalar extensión para la base de datos
RUN docker-php-ext-install pdo pdo_mysql

# Eliminar de raíz los módulos conflictivos y forzar el bueno (mpm_prefork)
RUN rm -f /etc/apache2/mods-enabled/mpm_event.conf \
    && rm -f /etc/apache2/mods-enabled/mpm_event.load \
    && rm -f /etc/apache2/mods-enabled/mpm_worker.conf \
    && rm -f /etc/apache2/mods-enabled/mpm_worker.load \
    && a2enmod mpm_prefork rewrite

# Copiar tu proyecto
COPY . /var/www/html/

# Exponer el puerto 80
EXPOSE 80

# Arrancar Apache
CMD ["apache2-foreground"]