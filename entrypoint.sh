#!/bin/sh
# entrypoint.sh

# Aseguramos que la carpeta de uploads y el root sean de Apache
# Esto arregla el problema de los volúmenes montados
chown -R www-data:www-data /var/www/html/admin/uploads
chmod -R 775 /var/www/html/admin/uploads

# Ejecuta el comando que Apache trae por defecto
exec apache2-foreground