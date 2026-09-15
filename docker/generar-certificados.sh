#!/bin/sh
# Genera el certificado autofirmado que usa Apache en HTTPS.
#
# La clave privada NO se guarda en el repositorio: cada persona genera la
# suya al clonar. Ejecutar una sola vez:
#
#     sh docker/generar-certificados.sh
#
set -eu

DIR="$(cd "$(dirname "$0")" && pwd)"
CRT="$DIR/my.crt"
KEY="$DIR/my.key"

if [ -f "$CRT" ] && [ -f "$KEY" ]; then
    echo "Los certificados ya existen. Borra my.crt y my.key si quieres rehacerlos."
    exit 0
fi

openssl req -x509 -nodes -newkey rsa:2048 -days 825 \
    -keyout "$KEY" -out "$CRT" \
    -subj "/C=PE/ST=Lima/L=Lima/O=VetPet Surco/CN=localhost" \
    -addext "subjectAltName=DNS:localhost,DNS:vetpet.local,IP:127.0.0.1"

chmod 600 "$KEY"
echo "Certificado generado en docker/my.crt y clave en docker/my.key"
echo "Es autofirmado: el navegador avisara la primera vez. Es lo esperado en desarrollo."
