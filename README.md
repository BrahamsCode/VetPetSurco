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
├── sitio-web/        Sitio web estático institucional (HTML5 + CSS3) — lo que se despliega
├── basedatos/        Esquema MySQL, datos de prueba, transacción de compra y consultas
├── backend/          Algoritmo del carrito de compras en Java
├── docs/             Entregas del curso en Markdown, modelo de datos y capturas
└── .github/workflows Despliegue automático del sitio a GitHub Pages
```

| Carpeta | Contenido |
| --- | --- |
| [`sitio-web/`](sitio-web/) | 5 páginas estáticas, hoja de estilos e íconos SVG. Sin JavaScript ni frameworks. |
| [`basedatos/`](basedatos/) | `01_esquema.sql`, `02_datos_prueba.sql`, `03_transaccion_compra.sql`, `04_consultas_ejemplo.sql` |
| [`backend/`](backend/) | `CarritoCompra`, `ItemCarrito` y `DemoCarrito` en `pe.vetpetsurco.carrito` |
| [`docs/`](docs/) | [Entrega 1](docs/entrega-1-modelo-de-negocio.md), [Entrega 2](docs/entrega-2-arquitectura-software.md), [modelo de datos](docs/modelo-de-datos.md), la [presentación](docs/presentacion/) de la Entrega 2, capturas y los `.docx` originales |

---

## Entregas del curso

| Entrega | Semana | Contenido | Documento |
| --- | --- | --- | --- |
| 1 | 3 | Modelo de negocio y plan estratégico (Capítulos I y II) | [Markdown](docs/entrega-1-modelo-de-negocio.md) · [Word](docs/originales/Entrega1_VetPetConnect.docx) |
| 2 | 5 | Arquitectura de software y core transaccional (Capítulos III y IV) | [Markdown](docs/entrega-2-arquitectura-software.md) · [Word](docs/originales/Entrega2_VetPetConnect.docx) · [Presentación](docs/presentacion/Entrega2_VetPetConnect_Presentacion.pptx) |

---

## Cómo ejecutar cada parte

### 1. Sitio web

No necesita compilación. Basta con abrir `sitio-web/index.html` en el navegador, o levantar un
servidor local para que las rutas relativas se comporten como en producción:

```bash
cd sitio-web
python3 -m http.server 8000
# abrir http://localhost:8000
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

## Despliegue del sitio web

El sitio es 100% estático, así que funciona en cualquier hosting gratuito.

### Opción A — GitHub Pages (ya configurado)

El flujo de trabajo [`.github/workflows/deploy-pages.yml`](.github/workflows/deploy-pages.yml)
publica la carpeta `sitio-web/` en cada push. Solo hay que habilitarlo una vez:

1. Ir a **Settings → Pages** del repositorio.
2. En **Source**, elegir **GitHub Actions**.
3. Volver a la pestaña **Actions** y, si hace falta, re-ejecutar el flujo *Desplegar sitio web*.

La URL queda como `https://<usuario>.github.io/<repositorio>/`.

### Opción B — Netlify

Arrastrar la carpeta `sitio-web/` a [app.netlify.com/drop](https://app.netlify.com/drop), o
conectar el repositorio: la configuración ya está en [`netlify.toml`](netlify.toml)
(sin comando de build, directorio de publicación `sitio-web`).

### Opción C — Vercel

Importar el repositorio en [vercel.com/new](https://vercel.com/new). La configuración está en
[`vercel.json`](vercel.json); no requiere framework ni build.

### Opción D — Cloudflare Pages

Conectar el repositorio, dejar el comando de build vacío y usar `sitio-web` como directorio de salida.

---

## Características técnicas del sitio

- **HTML5 semántico**: `header`, `nav`, `main`, `section`, `article`, `footer`.
- **CSS3 sin frameworks**: variables personalizadas, Flexbox, CSS Grid y un punto de quiebre en 760 px (RNF-01).
- **Accesibilidad**: enlace para saltar al contenido, `aria-current` en la página activa, `scope` en
  cabeceras de tabla, `caption` para lectores de pantalla, foco visible y soporte de `prefers-reduced-motion`.
- **Rendimiento** (RNF-04): sin JavaScript, íconos SVG vectoriales y una sola hoja de estilos.
- **Extras**: página 404, metadatos Open Graph, favicon SVG y hoja de estilos de impresión.

---

## Equipo

Equipo 4 — Curso de E-business, 2026.
