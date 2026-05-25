#!/bin/sh
# entrypoint.sh

#Crea la carpeta por si acaso
mkdir -p /var/www/html/admin/uploads

#Como Docker tiene control del montaje, esto va a impactar hacia afuera en la VPS
chown -R www-data:www-data /var/www/html/admin/uploads

#Le da permiso al usuario de apache de modificar archivos
chmod -R 775 /var/www/html/admin/uploads

#Arranca Apache normalmente
exec apache2-foreground