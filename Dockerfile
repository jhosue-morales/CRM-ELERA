# Agregamos un ARG para invalidar la caché de Railway
ARG CACHE_BUST=2026-09-03-fix-final

FROM php:8.2-apache

# Instalar pdo
RUN docker-php-ext-install pdo pdo_mysql

# ELIMINAR FÍSICAMENTE LOS CONFLICTOS DE APACHE
# También eliminamos los archivos de configuración huérfanos que causan errores como el de MinSpareThreads
RUN rm -f /etc/apache2/mods-enabled/mpm_event.conf \
    && rm -f /etc/apache2/mods-enabled/mpm_event.load \
    && rm -f /etc/apache2/mods-enabled/mpm_worker.conf \
    && rm -f /etc/apache2/mods-enabled/mpm_worker.load \
    && a2enmod mpm_prefork rewrite

# Copiar tu código
COPY . /var/www/html/

# Exponer puerto
EXPOSE 80

# Comando final y definitivo
CMD ["apache2-foreground"]