FROM php:8.2-apache

# Instalar extensiones pdo
RUN docker-php-ext-install pdo pdo_mysql

# Copiar la configuración personalizada que fuerza el uso de mpm_prefork
# (Reemplaza el archivo de configuración que tiene los conflictos)
COPY apache-mpm.conf /etc/apache2/conf-available/mpm.conf

# Habilitar esa configuración y deshabilitar los módulos conflictivos
RUN ln -sf /etc/apache2/conf-available/mpm.conf /etc/apache2/conf-enabled/mpm.conf \
    && a2dismod mpm_event mpm_worker 2>/dev/null; a2enmod mpm_prefork

# Copiar el código de tu web
COPY . /var/www/html/

# Exponer el puerto 80
EXPOSE 80

# Arrancar Apache
CMD ["apache2-foreground"]