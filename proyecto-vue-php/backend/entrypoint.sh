#!/bin/sh

# Path to the flag file
SEED_FLAG="/var/www/html/database/.seeded"

# Esperar a que la base de datos esté lista
echo "Esperando a que la BD esté lista..."
while ! nc -z db 3306; do
  sleep 1
done
echo "BD lista."

# Verificar si la siembra de datos ya se ha realizado
if [ ! -f "$SEED_FLAG" ]; then
  echo "La base de datos no ha sido sembrada. Sembrando ahora..."
  php /var/www/html/database/seed.php
  # Crear el archivo de bandera para prevenir que se vuelva a ejecutar
  touch "$SEED_FLAG"
  echo "Siembra de datos completada."
else
  echo "La base de datos ya ha sido sembrada."
fi

# Ejecutar el CMD del Dockerfile (ej. "apache2-foreground")
exec "$@"
