FROM php:8.2-apache

# Instalar pdo
RUN docker-php-ext-install pdo pdo_mysql

# 1. ELIMINAR archivos conflictivos y activar SOLO prefork
# Eliminamos los archivos de configuración de mpm_event y mpm_worker de raíz
RUN rm -f /etc/apache2/mods-enabled/mpm_event.conf \
    && rm -f /etc/apache2/mods-enabled/mpm_event.load \
    && rm -f /etc/apache2/mods-enabled/mpm_worker.conf \
    && rm -f /etc/apache2/mods-enabled/mpm_worker.load \
    && a2enmod mpm_prefork rewrite

# 2. COPIAR TU CÓDIGO
COPY . /var/www/html/

# Exponer puerto
EXPOSE 80

# Arrancar
CMD ["apache2-foreground"]