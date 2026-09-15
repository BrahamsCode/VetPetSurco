# Atajos para el entorno Docker de VetPet Connect.
# Uso: make up, make shell, make test, ...

COMPOSE ?= docker compose
APP     ?= app

.DEFAULT_GOAL := ayuda
.PHONY: ayuda up down build shell migrate seed test fresh logs

# Muestra los objetivos disponibles.
ayuda:
	@echo "Objetivos: up down build shell migrate seed test fresh logs"

# Construye las imágenes (si hace falta) y levanta los tres servicios en segundo plano.
up:
	$(COMPOSE) up -d

# Detiene y elimina los contenedores y la red (el volumen de MySQL se conserva).
down:
	$(COMPOSE) down

# Reconstruye la imagen de la aplicación sin usar la caché de capas.
build:
	$(COMPOSE) build --no-cache

# Abre una shell interactiva dentro del contenedor de la aplicacion.
shell:
	$(COMPOSE) exec $(APP) bash

# Aplica las migraciones pendientes sobre la base de datos de Docker.
migrate:
	$(COMPOSE) exec $(APP) php artisan migrate --force

# Carga los datos de ejemplo ejecutando los seeders.
seed:
	$(COMPOSE) exec $(APP) php artisan db:seed --force

# Ejecuta la suite de pruebas de Laravel dentro del contenedor.
test:
	$(COMPOSE) exec $(APP) php artisan test

# Rehace la base de datos desde cero y vuelve a cargar los datos de ejemplo.
fresh:
	$(COMPOSE) exec $(APP) php artisan migrate:fresh --seed --force

# Sigue en vivo los registros de los tres servicios.
logs:
	$(COMPOSE) logs -f
