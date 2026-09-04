FROM php:8.2-apache

# Instalar pdo
RUN docker-php-ext-install pdo pdo_mysql

# 1. DESACTIVAR TODO RASTRO DE OTROS MPMS Y FORZAR PREFORK
# Usamos 'sed' para editar el archivo de configuración directamente (esto evita errores de sintaxis de a2dismod en imágenes nuevas)
RUN a2dismod mpm_event 2>/dev/null; \
    a2dismod mpm_worker 2>/dev/null; \
    a2enmod mpm_prefork rewrite && \
    sed -i 's/^LoadModule mpm_event_module/#LoadModule mpm_event_module/g' /etc/apache2/mods-available/mpm_event.load 2>/dev/null; \
    sed -i 's/^LoadModule mpm_worker_module/#LoadModule mpm_worker_module/g' /etc/apache2/mods-available/mpm_worker.load 2>/dev/null;

# 2. COPIAR LA CONFIGURACIÓN VIRTUAL HOST
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

# 3. COPIAR TU CÓDIGO
COPY . /var/www/html/

# Exponer puerto
EXPOSE 80

# Arrancar
CMD ["apache2-foreground"]