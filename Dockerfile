FROM php:8.2-apache

# Desactivar módulos incompatibles (mpm_event y mpm_worker) y activar mpm_prefork
RUN a2dismod mpm_event mpm_worker 2>/dev/null; a2enmod mpm_prefork

# Instalar extensiones necesarias para conectar con MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Copiar los archivos de tu proyecto al directorio web de Apache
COPY . /var/www/html/

# Exponer el puerto 80
EXPOSE 80

# Comando esencial para iniciar Apache y mantener el contenedor vivo
CMD ["apache2-foreground"]