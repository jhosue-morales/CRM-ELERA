ARG CACHE_BUST=10000001
FROM php:8.2-apache

# Instalar pdo
RUN docker-php-ext-install pdo pdo_mysql

# ELIMINAR LOS ARCHIVOS CONFLICTIVOS DE RAÍZ (Sin posibilidad de que vuelvan a cargarse)
RUN rm -rf /etc/apache2/mods-enabled/mpm_event* \
    && rm -rf /etc/apache2/mods-enabled/mpm_worker* \
    && rm -rf /etc/apache2/mods-available/mpm_event* \
    && rm -rf /etc/apache2/mods-available/mpm_worker* \
    && a2enmod mpm_prefork rewrite

# Copiar tu código
COPY . /var/www/html/

# Exponer puerto
EXPOSE 80

# COMANDO FINAL: Arranca Apache forzando que solo use mpm_prefork
CMD ["/bin/bash", "-c", "sed -i 's/^LoadModule mpm_event_module/#LoadModule mpm_event_module/g; s/^LoadModule mpm_worker_module/#LoadModule mpm_worker_module/g' /etc/apache2/mods-enabled/*.load 2>/dev/null; apache2-foreground"]