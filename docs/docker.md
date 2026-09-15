# Entorno Docker de VetPet Connect

> **Aviso honesto:** esta configuración se escribió y se revisó a mano, pero **no se
> pudo construir ni levantar en el entorno donde se redactó**: allí no corría el
> demonio de Docker (no existía `/var/run/docker.sock`). Lo único que se verificó de
> verdad fue la sintaxis de los archivos (YAML de `compose.yaml`, análisis del script
> de arranque y del `Makefile`) y la coherencia de las rutas entre archivos. La
> primera persona que la ejecute en una máquina con Docker debe tratarla como código
> sin estrenar y reportar cualquier ajuste.

## Qué levanta

| Servicio | Imagen | Para qué sirve | Puertos |
| --- | --- | --- | --- |
| `app` | se construye desde `docker/php/Dockerfile` (base `php:8.2-fpm`) | Ejecuta Laravel sobre PHP-FPM: artisan, migraciones, pruebas | `9000` solo dentro de la red interna |
| `nginx` | `nginx:alpine` | Sirve `public/` y reenvía los `.php` a `app:9000` | `8080` del host → `80` del contenedor |
| `db` | `mysql:8.0` | Base de datos de desarrollo, con volumen persistente `datos-mysql` | `3307` del host → `3306` del contenedor |

Los tres comparten la red `vetpet`. `app` no arranca hasta que el *healthcheck* de
`db` responde (`depends_on: condition: service_healthy`).

## Puesta en marcha

```bash
cp .env.example .env     # si aún no tienes .env
make up                  # equivale a: docker compose up -d
```

La primera vez la construcción tarda varios minutos: compila las extensiones de PHP
e instala las dependencias de Composer.

Cuando termine, la aplicación está en **http://localhost:8080**.

Si algo no responde, mira los registros:

```bash
make logs                # docker compose logs -f
```

## Qué hace el arranque del contenedor `app`

`docker/php/entrypoint.sh` se ejecuta en cada arranque y es idempotente:

0. Crea, si faltan, las carpetas de `storage/` y `bootstrap/cache` que Laravel exige.
1. Copia `.env.example` a `.env` si no existe.
2. Ejecuta `composer install` solo si falta `vendor/autoload.php` (va antes que
   cualquier `php artisan`, que no arranca sin `vendor/`).
3. Genera `APP_KEY` solo si está vacía.
4. Espera a que MySQL acepte conexiones (hasta 60 intentos, 2 s entre uno y otro).
5. Ejecuta `php artisan migrate --force`.
6. Arranca `php-fpm`.

## Comandos habituales (`Makefile`)

| Objetivo | Qué hace |
| --- | --- |
| `make up` | Levanta los tres servicios en segundo plano |
| `make down` | Para y elimina contenedores y red (el volumen de MySQL se conserva) |
| `make build` | Reconstruye la imagen de `app` sin caché |
| `make shell` | Abre una `bash` dentro del contenedor de la aplicación |
| `make migrate` | Aplica las migraciones pendientes |
| `make seed` | Carga los datos de ejemplo |
| `make test` | Ejecuta `php artisan test` dentro del contenedor |
| `make fresh` | Rehace la base de datos desde cero y vuelve a sembrarla |
| `make logs` | Sigue los registros de los tres servicios |

Todo eso son atajos de `docker compose exec app ...`; se puede escribir a mano:

```bash
docker compose exec app bash
docker compose exec app php artisan migrate --force
docker compose exec app php artisan test
```

## Entrar al contenedor

```bash
make shell
# ya dentro, por ejemplo:
php artisan route:list
php artisan tinker
```

La shell se abre con el usuario **`vetpet`** (uid 1000), no con `root`: la imagen no
ejecuta la aplicación como superusuario.

## Pruebas

La suite corre sobre **SQLite en memoria** (así lo fija `phpunit.xml`), de modo que
`make test` no toca la base de datos MySQL del contenedor `db`:

```bash
make test
```

## Base de datos

Credenciales de desarrollo (definidas en `compose.yaml` y en `.env.example`):

| Dato | Valor |
| --- | --- |
| Base de datos | `vetpet_connect` |
| Usuario | `vetpet` |
| Contraseña | `vetpet` |
| Contraseña de root | `root` |
| Host dentro de Docker | `db:3306` |
| Host desde la máquina anfitriona | `127.0.0.1:3307` |

Son credenciales de desarrollo, no sirven para producción.

Conexión desde el host con un cliente cualquiera:

```bash
mysql -h 127.0.0.1 -P 3307 -u vetpet -p vetpet_connect
```

Las variables `DB_*` que declara `compose.yaml` para el servicio `app` tienen
prioridad sobre las del archivo `.env` (Laravel carga `.env` de forma *inmutable*:
no pisa variables de entorno que ya existen). Por eso el contenedor habla siempre con
`db`, aunque tu `.env` local apunte a `127.0.0.1` para trabajar fuera de Docker.

Para borrar los datos y empezar de cero:

```bash
docker compose down -v    # elimina también el volumen datos-mysql
make up
```

## Archivos de la configuración

```
compose.yaml                 # los tres servicios, la red y el volumen
Makefile                     # atajos
.dockerignore                # qué NO entra en la imagen
docker/php/Dockerfile        # imagen de la aplicación (PHP 8.2 + extensiones + Composer 2)
docker/php/php.ini           # límites de subida, memoria y opcache
docker/php/entrypoint.sh     # espera a MySQL, migra y arranca php-fpm
docker/nginx/default.conf    # sitio nginx que sirve public/ y habla con app:9000
```

Extensiones de PHP incluidas en la imagen: `pdo_mysql`, `mbstring` (viene compilada
en la imagen oficial), `exif`, `pcntl`, `bcmath`, `gd`, `zip`, `intl` y `opcache`.

## Notas

- El proyecto se monta como volumen (`.:/var/www/html`), así que editar el código no
  obliga a reconstruir la imagen. Ese montaje tapa el `vendor/` que trae la imagen;
  por eso el *entrypoint* vuelve a ejecutar `composer install` si hace falta.
- `docker/php/php.ini` se monta además como `:ro` sobre la misma ruta a la que lo
  copia el `Dockerfile` (`/usr/local/etc/php/conf.d/vetpet.ini`), de modo que se puede
  ajustar y reiniciar sin reconstruir nada.
- El puerto de MySQL se publica en el **3307** para no chocar con un MySQL instalado
  en la máquina.
