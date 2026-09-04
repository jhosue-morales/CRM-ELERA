ARG CACHE_BUST=10000002
FROM php:8.2-apache

# Instalar pdo
RUN docker-php-ext-install pdo pdo_mysql

# Eliminar archivos conflictivos
RUN rm -f /etc/apache2/mods-enabled/mpm_event.conf \
    && rm -f /etc/apache2/mods-enabled/mpm_event.load \
    && rm -f /etc/apache2/mods-enabled/mpm_worker.conf \
    && rm -f /etc/apache2/mods-enabled/mpm_worker.load \
    && a2enmod mpm_prefork

# Copiar tu código
COPY . /var/www/html/

EXPOSE 80

# ARRANCAR APACHE OBLIGÁNDOLO A USAR SOLO PREFORK
CMD ["/bin/bash", "-c", "sed -i 's/^LoadModule mpm_event_module/#LoadModule mpm_event_module/g; s/^LoadModule mpm_worker_module/#LoadModule mpm_worker_module/g' /etc/apache2/mods-enabled/*.load 2>/dev/null; apache2-foreground"]