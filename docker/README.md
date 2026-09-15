# Entorno de Docker

Levanta VetPet Connect con **PHP 8.2 sobre Apache**, **MySQL 8.0** y **MailHog**.
La aplicación Laravel vive fuera de esta carpeta, en `public_html/`, y se monta
dentro del contenedor en `/var/www`.

## Estructura

```
docker/
├── .env.example              Variables que consume el compose
├── .env                      Copia local (no se versiona)
├── 000-default.conf          Apache en HTTP
├── default-ssl.conf          Apache en HTTPS
├── dockerfile/
│   ├── Dockerfile            Imagen de la aplicación
│   └── docker-compose.yml    Los tres servicios
├── generar-certificados.sh   Crea my.crt y my.key
├── my.crt / my.key           Certificado autofirmado (no se versionan)
├── php.ini                   Ajustes de PHP
├── policy.xml                Política de ImageMagick
└── README.md
```

## Puesta en marcha

```bash
cp docker/.env.example docker/.env          # variables de la base de datos
sh docker/generar-certificados.sh           # certificado para HTTPS
cp public_html/.env.example public_html/.env

cd docker/dockerfile
docker compose up -d --build

docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

- Aplicación: http://localhost y https://localhost
- Correo de prueba (MailHog): http://localhost:8025
- MySQL desde el host: `127.0.0.1:13306`

## Servicios

| Servicio | Imagen | Puertos | Para qué |
| --- | --- | --- | --- |
| `app` | `php:8.2-apache` (construida) | 80, 443 | Apache con la aplicación |
| `db` | `mysql:8.0` | 13306 → 3306 | Base de datos, con volumen persistente |
| `mailhog` | `mailhog/mailhog` | 8025 | Captura el correo que envía la aplicación |

El servicio `app` espera a que `db` pase su *healthcheck* antes de arrancar.

## Detalles que conviene conocer

**La raíz de Apache es `/var/www/public`, no `/var/www`.** Es lo que evita que
alguien pueda pedir `.env` o el código fuente por HTTP. Los archivos ocultos
además están denegados explícitamente en las dos configuraciones.

**`vendor` es un volumen con nombre.** Así las dependencias que instala el
contenedor no quedan pisadas por la carpeta del host, que es lo que pasa cuando
se monta el proyecto entero encima.

**El certificado es autofirmado y no se versiona.** Una clave privada no debe
vivir en un repositorio, ni siquiera de desarrollo: cada persona genera la suya
con `generar-certificados.sh`. El navegador avisará la primera vez; es lo
esperado.

**Las rutas del compose son relativas a `docker/dockerfile/`**, que es donde está
el archivo: `../../public_html` es la aplicación y `../php.ini` la configuración
de PHP.

## Comandos habituales

```bash
cd docker/dockerfile

docker compose up -d                 # levantar
docker compose down                  # detener
docker compose logs -f app           # ver los registros
docker compose exec app bash         # entrar al contenedor

docker compose exec app php artisan migrate
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app php artisan test
```

## Sin Docker

Con PHP 8.2 y MySQL instalados en la máquina:

```bash
cd public_html
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed
php artisan serve
```

## Advertencia honesta

Esta configuración **no se pudo construir ni levantar** en el entorno donde se
escribió, porque allí no corría el demonio de Docker. Lo que sí está comprobado:
el compose parsea con sus tres servicios, el script de certificados funciona y
genera el par, y la aplicación Laravel pasa sus 112 pruebas. El flujo de
integración continua sí construye la imagen, que es donde se verá si la imagen
compila de verdad.
