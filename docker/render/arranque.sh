#!/usr/bin/env bash
# Arranque del contenedor en Render: prepara la aplicacion y levanta Apache.
set -euo pipefail

PUERTO="${PORT:-10000}"
echo "Listen ${PUERTO}" > /etc/apache2/ports.conf
export PORT="${PUERTO}"

cd /var/www/html

# Clave de la aplicacion: si el entorno no la trae, se genera una al vuelo
# para que el contenedor pueda arrancar (en Render viene de render.yaml).
if [ -z "${APP_KEY:-}" ]; then
  export APP_KEY="base64:$(head -c 32 /dev/urandom | base64)"
  echo "AVISO: APP_KEY no estaba definida; se generó una temporal."
fi

# Render (generateValue) entrega 32 bytes en base64 pero sin el prefijo que
# Laravel necesita para decodificarla; sin el, la toma como texto plano de 44
# caracteres y falla con "Unsupported cipher or incorrect key length".
if [[ "${APP_KEY}" != base64:* ]]; then
  export APP_KEY="base64:${APP_KEY}"
fi

# Esquema y datos de demostracion. --force porque en produccion Laravel pide
# confirmacion interactiva y aqui no hay nadie para darla.
php artisan migrate --force --seed || echo "AVISO: las migraciones fallaron; revisa la conexión a la base."

php artisan config:cache
php artisan route:cache
php artisan view:cache

exec apache2-foreground
