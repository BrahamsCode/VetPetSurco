#!/bin/sh
# Arranque del contenedor de aplicación de VetPet Connect.
#
# Pasos, todos idempotentes (se pueden repetir en cada reinicio sin romper nada):
#   0. Asegurar las carpetas de storage/ y bootstrap/cache que Laravel exige.
#   1. Crear .env a partir de .env.example si todavía no existe.
#   2. Instalar dependencias si vendor/ no está disponible (ocurre cuando el
#      proyecto se monta como volumen y tapa el vendor/ de la imagen); tiene que
#      ir antes que cualquier "php artisan", que no arranca sin vendor/.
#   3. Generar APP_KEY solo si está vacía.
#   4. Esperar a que MySQL acepte conexiones.
#   5. Aplicar las migraciones pendientes con --force.
#   6. Ceder el control al comando del contenedor (php-fpm).
set -e

RAIZ="${RAIZ_APP:-/var/www/html}"
cd "$RAIZ"

# Valores por defecto alineados con compose.yaml y .env.example.
export DB_HOST="${DB_HOST:-db}"
export DB_PORT="${DB_PORT:-3306}"
export DB_DATABASE="${DB_DATABASE:-vetpet_connect}"
export DB_USERNAME="${DB_USERNAME:-vetpet}"
export DB_PASSWORD="${DB_PASSWORD:-vetpet}"

ESPERA_INTENTOS="${ESPERA_INTENTOS:-60}"
ESPERA_SEGUNDOS="${ESPERA_SEGUNDOS:-2}"

aviso() {
    echo "[vetpet] $1"
}

# --- 0. Carpetas y permisos de escritura de Laravel ------------------------------
# mkdir -p no hace nada si ya existen; garantiza las rutas que Laravel exige.
mkdir -p \
    "$RAIZ/storage/logs" \
    "$RAIZ/storage/framework/cache/data" \
    "$RAIZ/storage/framework/sessions" \
    "$RAIZ/storage/framework/views" \
    "$RAIZ/storage/framework/testing" \
    "$RAIZ/bootstrap/cache"

# No es fatal si falla (con volúmenes montados los permisos los fija el host).
chmod -R ug+rw "$RAIZ/storage" "$RAIZ/bootstrap/cache" 2>/dev/null || true

# --- 1. Archivo .env ----------------------------------------------------------
if [ ! -f "$RAIZ/.env" ] && [ -f "$RAIZ/.env.example" ]; then
    aviso "No hay .env; se copia desde .env.example"
    cp "$RAIZ/.env.example" "$RAIZ/.env"
fi

# --- 2. Dependencias ----------------------------------------------------------
if [ ! -f "$RAIZ/vendor/autoload.php" ]; then
    aviso "No se encuentra vendor/autoload.php; se ejecuta composer install"
    composer install --no-interaction --prefer-dist
fi

# --- 3. Clave de la aplicación ------------------------------------------------
if [ -f "$RAIZ/.env" ] && grep -q '^APP_KEY=$' "$RAIZ/.env"; then
    aviso "APP_KEY vacía; se genera una nueva"
    php artisan key:generate --force --no-interaction
fi

# --- 4. Espera a MySQL --------------------------------------------------------
aviso "Esperando a MySQL en ${DB_HOST}:${DB_PORT} ..."
intento=1
while [ "$intento" -le "$ESPERA_INTENTOS" ]; do
    if php -r 'try { new PDO(sprintf("mysql:host=%s;port=%s", getenv("DB_HOST"), getenv("DB_PORT")), getenv("DB_USERNAME"), getenv("DB_PASSWORD")); exit(0); } catch (Throwable $e) { exit(1); }' 2>/dev/null; then
        aviso "MySQL responde (intento ${intento})"
        break
    fi
    if [ "$intento" -eq "$ESPERA_INTENTOS" ]; then
        aviso "MySQL no respondió tras ${ESPERA_INTENTOS} intentos; se aborta el arranque"
        exit 1
    fi
    intento=$((intento + 1))
    sleep "$ESPERA_SEGUNDOS"
done

# --- 5. Migraciones -----------------------------------------------------------
# migrate solo aplica lo pendiente, así que repetirlo no tiene efecto.
aviso "Aplicando migraciones pendientes"
php artisan migrate --force --no-interaction

# --- 6. Comando del contenedor (por defecto php-fpm) --------------------------
aviso "Arrancando: $*"
exec "$@"
