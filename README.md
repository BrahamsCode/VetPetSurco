# VetPet Connect — VetPet Surco

Proyecto del **Curso de E-business**, Equipo 4, Lima – Perú, 2026.

VetPet Connect es el portal de comercio electrónico **B2C** de *VetPet Surco E.I.R.L.*, una
veterinaria y pet shop MYPE de Santiago de Surco. La plataforma integra tres pilares:
**tienda en línea**, **suscripción mensual de alimento** e **historia clínica digital** con
recordatorios automáticos de vacunación.

> Empresa ficticia con fines exclusivamente académicos.

---

## Estructura del repositorio

```
.
├── app/              Aplicación Laravel: modelos, servicios de dominio, controladores
├── database/         Migraciones con las restricciones, semillas y factories
├── resources/views/  Vistas Blade: sitio institucional y plataforma
├── routes/           Rutas con nombre y guardia de acceso por rol
├── tests/            Las 20 reglas comprobadas contra MySQL real
├── docker/           Imagen PHP 8.2-fpm, nginx y entrypoint
├── basedatos/        Esquema MySQL de la Entrega 2 (referencia histórica)
├── backend/          Carrito en Java de la Entrega 2 (superado por app/Services)
├── docs/             Entregas del curso, reglas de negocio y capturas anotadas
└── pruebas/          Recorrido del navegador sobre la plataforma
```

| Carpeta | Contenido |
| --- | --- |
| [`app/`](app/) | Modelos Eloquent, enums, los cinco servicios de dominio con las 20 reglas, controladores y Form Requests. |
| [`resources/views/`](resources/views/) | Vistas Blade del sitio institucional y de los módulos de los cuatro roles. |
| [`basedatos/`](basedatos/) | `01_esquema.sql`, `02_datos_prueba.sql`, `03_transaccion_compra.sql`, `04_consultas_ejemplo.sql` |
| [`backend/`](backend/) | `CarritoCompra`, `ItemCarrito` y `DemoCarrito` en `pe.vetpetsurco.carrito` |
| [`docs/`](docs/) | [Entrega 1](docs/entrega-1-modelo-de-negocio.md), [Entrega 2](docs/entrega-2-arquitectura-software.md), [modelo de datos](docs/modelo-de-datos.md), las [presentaciones](docs/presentacion/), capturas y los `.docx` originales |

---

## Entregas del curso

| Entrega | Semana | Contenido | Documento |
| --- | --- | --- | --- |
| 1 | 3 | Modelo de negocio y plan estratégico (Capítulos I y II) | [Markdown](docs/entrega-1-modelo-de-negocio.md) · [Word](docs/originales/Entrega1_VetPetConnect.docx) |
| 2 | 5 | Arquitectura de software y core transaccional (Capítulos III y IV) | [Markdown](docs/entrega-2-arquitectura-software.md) · [Word](docs/originales/Entrega2_VetPetConnect.docx) · [Presentación](docs/presentacion/Entrega2_VetPetConnect_Presentacion.pptx) |

### Presentaciones

| Presentación | Contenido |
| --- | --- |
| [Entrega 2](docs/presentacion/Entrega2_VetPetConnect_Presentacion.pptx) | Arquitectura de software y core transaccional: requerimientos, modelo de datos, pantallas y flujo de compra |
| [Reglas de negocio](docs/presentacion/ReglasDeNegocio_VetPetConnect.pptx) | Las 20 reglas del sistema, enmarcadas sobre capturas del prototipo, más dos casos trazados de principio a fin |

---

## Cómo ejecutar cada parte

### 1. Levantar el proyecto con Docker

```bash
cp .env.example .env
make build && make up
make migrate && make seed
# sitio institucional y plataforma: http://localhost:8080
```

Detalle de los servicios y de cada objetivo del Makefile en [`docs/docker.md`](docs/docker.md).

### 1b. Sin Docker, con PHP 8.2 y MySQL locales

```bash
composer install
cp .env.example .env && php artisan key:generate
# apuntar DB_HOST a 127.0.0.1 en .env
php artisan migrate --seed
php artisan serve
```

### 2. Base de datos (MySQL 8.0)

```bash
mysql -u root -p < basedatos/01_esquema.sql
mysql -u root -p < basedatos/02_datos_prueba.sql
mysql -u root -p < basedatos/03_transaccion_compra.sql
mysql -u root -p < basedatos/04_consultas_ejemplo.sql
```

El procedimiento `sp_confirmar_pedido` implementa el flujo de compra del Capítulo IV: abre una
transacción, bloquea las filas de producto con `FOR UPDATE`, valida el stock, lo descuenta y
hace `ROLLBACK` completo si algún producto no alcanza, evitando la sobreventa (RNF-03).

### 3. Carrito de compras (Java 17+)

```bash
cd backend
javac -d out src/pe/vetpetsurco/carrito/*.java
java -cp out pe.vetpetsurco.carrito.DemoCarrito
```

La demo verifica las reglas del algoritmo: no duplicar líneas, acumular cantidades, recalcular el
total y rechazar cantidades inválidas.

---

## Despliegue

El proyecto dejó de ser un sitio estático: ahora es una aplicación Laravel que necesita PHP y
MySQL, así que **GitHub Pages ya no aplica** y su flujo de trabajo se retiró. Las opciones
razonables son un servicio que ejecute contenedores (Render, Railway, Fly.io) o un VPS con
Docker, apuntando a `compose.yaml`.

## Características técnicas

- **Laravel 12 sobre PHP 8.2**, con `composer.json` fijado a `"php": "^8.2"`.
- **HTML5 semántico** en las vistas Blade: `header`, `nav`, `main`, `section`, `article`, `footer`.
- **CSS3 sin frameworks**: variables personalizadas, Flexbox, CSS Grid y un punto de quiebre en 760 px (RNF-01).
- **Accesibilidad**: enlace para saltar al contenido, `aria-current` en la página activa, `scope` en
  cabeceras de tabla, `caption` para lectores de pantalla, foco visible y soporte de `prefers-reduced-motion`.
- **Rendimiento** (RNF-04): las páginas institucionales no cargan JavaScript; íconos SVG y una sola hoja de estilos.
- **Reglas en el motor**: las restricciones `UNIQUE` y `CHECK` viven en las migraciones, no solo en el código.
- **Extras**: página 404, metadatos Open Graph, favicon SVG y hoja de estilos de impresión.

---

## Equipo

Equipo 4 — Curso de E-business, 2026.
