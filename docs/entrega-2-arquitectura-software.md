# Entrega 2 — Arquitectura de Software y Core Transaccional

**Curso de E-business — Semana 5**
**Proyecto:** VetPet Connect — Portal Integral para Centros Veterinarios y Pet Shops
**Empresa:** VetPet Surco E.I.R.L. — Modelo B2C
**Equipo 4 — Lima, Perú — 2026**

El objetivo de esta fase es traducir el modelo de negocio definido en la Entrega 1 a una
arquitectura de software concreta: los requerimientos del sistema, el modelo de datos
relacional que soporta las transacciones y el diseño de las pantallas con las que operarán
el cliente y el personal de la veterinaria.

---

## Capítulo III: Ingeniería de Requerimientos y Base de Datos

### 3.1. Especificación de Requerimientos del Sistema

A partir del diagnóstico realizado en la Entrega 1 (agenda telefónica sin control, fichas
clínicas en papel, inventario en cuaderno y ausencia de canal de venta en línea), se
definieron los siguientes requerimientos para la plataforma VetPet Connect.

#### Requerimientos Funcionales (RF)

| Código | Módulo | Descripción |
| --- | --- | --- |
| **RF-01** | Autenticación | El sistema debe permitir el inicio de sesión con control de roles estrictos: Cliente (dueño de mascota), Veterinario y Administrador. Cada rol accede únicamente a los módulos que le corresponden. |
| **RF-02** | Catálogo y carrito de compras | El Cliente debe poder buscar productos por categoría (alimento, accesorios, medicamentos de venta libre), agregarlos a un carrito de compras y generar un pedido con el detalle de los ítems y el monto total. |
| **RF-03** | Suscripción mensual | El sistema debe permitir al Cliente contratar un plan de suscripción, registrar su frecuencia de despacho y generar automáticamente el pedido recurrente en la fecha programada. |
| **RF-04** | Agenda de citas | El Cliente debe poder reservar una cita veterinaria seleccionando servicio, fecha y hora dentro de los horarios efectivamente disponibles del profesional, evitando el cruce de horarios que hoy se produce por la coordinación telefónica. |
| **RF-05** | Historia clínica digital y alertas (CRM) | El Veterinario debe poder registrar en la ficha de cada mascota el diagnóstico, el tratamiento y las vacunas aplicadas. El sistema debe generar alertas automáticas al Cliente antes de la próxima fecha de vacunación o desparasitación. |
| **RF-06** | Control de inventario en tiempo real (ERP) | Cada pedido confirmado debe descontar el stock del producto en tiempo real y, al alcanzar el punto de reorden, generar una alerta de reposición dirigida al Administrador. |

#### Requerimientos No Funcionales (RNF)

| Código | Atributo | Descripción |
| --- | --- | --- |
| **RNF-01** | Diseño responsivo | La interfaz debe ser 100% adaptable a dispositivos móviles, dado que la mayor parte de los dueños de mascotas realizará la compra y la reserva de citas desde el celular. |
| **RNF-02** | Seguridad y protección de datos | Las contraseñas deben almacenarse cifradas mediante hash (BCrypt) y los datos personales del cliente deben tratarse conforme a la Ley N.° 29733 de Protección de Datos Personales del Perú. |
| **RNF-03** | Persistencia y concurrencia | La base de datos debe soportar transacciones simultáneas de compra y reserva de citas sin generar bloqueos ni sobreventa de stock en las tablas críticas de productos y citas. |
| **RNF-04** | Rendimiento | El catálogo de productos debe cargar en un tiempo no mayor a 3 segundos con una conexión móvil estándar, para no perder al cliente durante el proceso de compra. |

### 3.2. Diseño del Modelo de Datos Relacional (MySQL)

Se estructuró un esquema relacional normalizado en MySQL, compuesto por ocho tablas que
cubren los tres pilares del modelo: comercio electrónico, suscripción recurrente y salud animal.

El script completo y ejecutable se encuentra en
[`basedatos/01_esquema.sql`](../basedatos/01_esquema.sql).
El diagrama entidad-relación está en [`docs/modelo-de-datos.md`](modelo-de-datos.md).

| # | Tabla | Rol en el modelo |
| --- | --- | --- |
| 1 | `usuarios` | Cuentas y roles: CLIENTE, VETERINARIO, ADMIN (RF-01) |
| 2 | `mascotas` | Perfil del animal asociado a su dueño |
| 3 | `productos` | Catálogo con stock y punto de reorden (RF-06) |
| 4 | `pedidos` | Cabecera de la venta, con su origen y estado |
| 5 | `detalle_pedidos` | Contenido del carrito ya confirmado |
| 6 | `suscripciones` | Plan mensual y fecha del próximo despacho (RF-03) |
| 7 | `citas` | Agenda veterinaria con horario único por profesional (RF-04) |
| 8 | `historias_clinicas` | Diagnóstico, tratamiento y próxima fecha de control (RF-05) |

#### Justificación de las decisiones de diseño

- **Restricción `UNIQUE` en la agenda.** La clave `uk_agenda (veterinario_id, fecha_hora)`
  impide a nivel de base de datos que dos clientes reserven el mismo horario con el mismo
  veterinario, lo que resuelve el cruce de citas descrito en el diagnóstico (RNF-03).
- **Campo `tipo_origen` en `pedidos`.** Permite diferenciar una compra puntual de un despacho
  generado por la suscripción, y así medir el ingreso recurrente que plantea el objetivo del proyecto.
- **Campo `punto_reorden` en `productos`.** Sostiene la alerta automática de reposición hacia el
  proveedor sin necesidad de una tabla adicional.
- **Campo `proxima_fecha` en `historias_clinicas`.** Es el dato del que se alimenta el recordatorio
  automático de vacunación y desparasitación (RF-05).
- **Precio unitario histórico en `detalle_pedidos`.** El precio se copia al momento de la compra,
  de modo que un cambio posterior en el catálogo no altera el monto de un pedido ya emitido.

#### Resumen de relaciones principales

| Relación | Cardinalidad | Regla de negocio |
| --- | --- | --- |
| `usuarios` → `mascotas` | 1 a N | Un cliente puede registrar varias mascotas. |
| `usuarios` → `pedidos` | 1 a N | Un cliente genera muchos pedidos en el tiempo. |
| `pedidos` → `detalle_pedidos` | 1 a N | Un pedido contiene varios productos. |
| `productos` → `detalle_pedidos` | 1 a N | Un producto aparece en muchos pedidos. |
| `mascotas` → `citas` | 1 a N | Una mascota puede tener varias citas. |
| `citas` → `historias_clinicas` | 1 a 1 | Cada atención genera un registro clínico. |
| `mascotas` → `suscripciones` | 1 a N | Cada mascota puede tener su plan mensual. |

---

## Capítulo IV: Diseño de Interfaz (UI/UX) y Flujo Lógico

### 4.1. Wireframes y Prototipos de Pantalla

El diseño de las pantallas parte de un criterio principal: la mayor parte del tráfico proviene
de dueños de mascotas que compran desde el celular, por lo que la navegación se resolvió en
pocos pasos y con elementos de gran superficie táctil.

- **Pantalla de Catálogo (Cliente).** Interfaz con buscador superior y filtros por categoría
  (Alimento, Accesorios, Medicamentos, Arena). Cada producto se muestra en una tarjeta con imagen,
  precio e indicador de stock. Un botón flotante de carrito muestra un contador dinámico con la
  cantidad de ítems agregados y el subtotal acumulado.
- **Pantalla de Reserva de Cita (Cliente).** Flujo de tres pasos: elegir mascota registrada,
  elegir servicio y elegir horario. Los horarios ya ocupados se muestran deshabilitados en gris,
  de modo que el usuario nunca puede seleccionar un bloque no disponible.
- **Ficha Clínica (Veterinario).** Vista de la mascota con sus datos base a la izquierda (especie,
  raza, edad, peso, alergias) y el historial de atenciones en línea de tiempo a la derecha. Un botón
  permite registrar la nueva atención y programar la fecha del próximo control.
- **Dashboard Administrativo (Administrador).** Panel analítico con los indicadores del negocio:
  ventas del mes, número de suscripciones activas y pedidos pendientes de despacho. El módulo de
  inventario resalta automáticamente en color rojo los productos cuyo stock cayó por debajo del punto
  de reorden, en ámbar los que están próximos a alcanzarlo y en verde los que tienen stock suficiente.

> El semáforo del dashboard está implementado como la vista `v_semaforo_inventario`
> en [`basedatos/01_esquema.sql`](../basedatos/01_esquema.sql).

### 4.2. Algoritmo del Carrito de Compras (Backend Java)

La clase `CarritoCompra` controla el flujo dinámico de ítems antes de registrar el pedido en la
base de datos. La lógica principal consiste en no duplicar líneas: si el producto ya se encuentra
en el carrito, únicamente se acumula la cantidad. Esto respeta la restricción
`uk_linea_pedido (pedido_id, producto_id)` del esquema.

Código fuente: [`backend/src/pe/vetpetsurco/carrito/`](../backend/src/pe/vetpetsurco/carrito/)

```java
public class CarritoCompra {

    private final List<ItemCarrito> items = new ArrayList<>();

    // Agrega el producto o acumula la cantidad si ya existe en el carrito
    public void agregarProducto(int productoId, String nombre,
                                double precio, int cantidad) {
        if (cantidad <= 0) {
            throw new IllegalArgumentException("La cantidad debe ser mayor que cero.");
        }
        for (ItemCarrito item : items) {
            if (item.getProductoId() == productoId) {
                item.setCantidad(item.getCantidad() + cantidad);
                return;
            }
        }
        items.add(new ItemCarrito(productoId, nombre, precio, cantidad));
    }

    // Elimina por completo una linea del carrito
    public void quitarProducto(int productoId) {
        items.removeIf(item -> item.getProductoId() == productoId);
    }

    // Calcula el monto total que se guardara en pedidos.monto_total
    public double calcularTotal() {
        double total = 0;
        for (ItemCarrito item : items) {
            total += item.getSubtotal();
        }
        return total;
    }

    // Alimenta el contador del boton flotante del catalogo
    public int contarItems() {
        int suma = 0;
        for (ItemCarrito item : items) {
            suma += item.getCantidad();
        }
        return suma;
    }
}
```

La clase `ItemCarrito` mantiene el producto, su precio y su cantidad. El subtotal **nunca se
almacena**: se recalcula siempre como `precio * cantidad`, de modo que no puede quedar
desactualizado cuando el cliente modifica la cantidad desde la pantalla del carrito.

La clase `DemoCarrito` ejecuta el algoritmo y verifica sus reglas (no duplicar líneas, acumular
cantidades, recalcular el total y rechazar cantidades inválidas):

```
cd backend
javac -d out src/pe/vetpetsurco/carrito/*.java
java -cp out pe.vetpetsurco.carrito.DemoCarrito
```

### 4.3. Flujo Lógico de la Transacción de Compra

La secuencia que sigue una compra en la plataforma, desde la acción del cliente hasta la
actualización del inventario, es la siguiente:

| Paso | Acción del usuario | Respuesta del sistema |
| --- | --- | --- |
| 1 | El cliente inicia sesión | Valida el correo y el hash de la contraseña, y carga el rol CLIENTE |
| 2 | Agrega productos al carrito | Ejecuta `agregarProducto()` y actualiza el contador y el subtotal |
| 3 | Confirma el pedido | Verifica que `stock_actual` sea mayor o igual a la cantidad solicitada |
| 4 | Registra el pago | Inserta el registro en `pedidos` y en `detalle_pedidos` dentro de una transacción |
| 5 | — | Descuenta el stock de cada producto en tiempo real |
| 6 | — | Si el stock queda por debajo del punto de reorden, genera la alerta de reposición |
| 7 | Recibe la confirmación | Envía el correo de confirmación y limpia el carrito |

Si en el paso 3 el stock resulta insuficiente, la transacción no se registra y el sistema devuelve
al carrito indicando la cantidad realmente disponible, evitando la sobreventa señalada en el RNF-03.

Este flujo está implementado en el procedimiento almacenado `sp_confirmar_pedido`
([`basedatos/03_transaccion_compra.sql`](../basedatos/03_transaccion_compra.sql)), que abre una
transacción, bloquea las filas de producto involucradas con `FOR UPDATE`, valida el stock,
descuenta el inventario y hace `ROLLBACK` completo ante cualquier faltante.

---

> **Nota posterior a la entrega.** El sitio estático y el prototipo en JavaScript que describe
> este anexo fueron migrados después a una aplicación Laravel sobre PHP 8.2 y MySQL, con Docker.
> Este anexo se conserva como el registro de lo que se entregó en la Semana 5; el estado actual
> del proyecto está en [`README.md`](../README.md) y en
> [`docs/reglas-de-negocio.md`](reglas-de-negocio.md). Las rutas `sitio-web/…` que se citan a
> continuación corresponden a esa versión y ya no existen en el repositorio: su contenido vive
> ahora en `resources/views/` y `public/`.

## Anexo: Evidencia del Sitio Web Estático

Como evidencia práctica de esta entrega se desarrolló el sitio web estático institucional de
VetPet Surco, construido con HTML5 y CSS3, con maquetación responsiva mediante Flexbox, CSS Grid
y media queries. El código está en [`sitio-web/`](../sitio-web/).

### Breve descripción del negocio

VetPet Surco E.I.R.L. es una micro y pequeña empresa peruana ubicada en la Av. Velasco Astete 1245,
Santiago de Surco, Lima. Comercializa alimento balanceado, accesorios y medicamentos de venta libre
para mascotas, y brinda atención veterinaria básica: consulta, vacunación, desparasitación y grooming.
Cuenta con un local físico y seis colaboradores. Opera bajo un modelo de comercio electrónico B2C,
en el que el portal VetPet Connect le permite vender en línea, administrar suscripciones mensuales
de alimento y mantener la historia clínica digital de cada mascota atendida.

### Estructura de carpetas del sitio

```
sitio-web/
  |-- index.html          (Inicio)
  |-- nosotros.html       (Nosotros)
  |-- productos.html      (Productos y servicios)
  |-- contacto.html       (Contacto)
  |-- 404.html            (Pagina de error)
  |
  |-- css/
  |     |-- estilos.css
  |
  |-- img/
        |-- logo.svg
        |-- portada-mascotas.svg
        |-- icono-tienda.svg
        |-- icono-suscripcion.svg
        |-- icono-historia.svg
        |-- icono-alimento.svg
        |-- icono-accesorios.svg
        |-- icono-salud.svg
        |-- icono-grooming.svg
```

Las capturas de pantalla del sitio se encuentran en [`docs/capturas/`](capturas/).

### Prototipo navegable de la plataforma

Sobre el sitio institucional se añadió `sitio-web/app/`, un prototipo funcional de VetPet Connect
con los módulos de los cuatro roles. Implementa las veinte reglas de negocio del sistema y sirve
de evidencia de que el diseño de esta entrega es ejecutable. No tiene servidor: los datos viven en
el navegador. Su documentación está en [`docs/reglas-de-negocio.md`](reglas-de-negocio.md).

### Contenido de cada página

| Página | Contenido |
| --- | --- |
| Inicio | Portada, propuesta digital (tienda en línea, suscripción, historia clínica) y resumen de servicios |
| Nosotros | Descripción del negocio, datos de la empresa, misión, visión, valores y equipo |
| Productos y servicios | Catálogo por categoría, tarifario de servicios veterinarios y planes de suscripción |
| Contacto | Formulario de contacto y reserva, dirección, teléfonos, horario y redes sociales |

### Cumplimiento de los requerimientos no funcionales en el sitio

- **RNF-01 (responsivo).** Diseño fluido con Flexbox y CSS Grid, un único punto de quiebre en 760 px,
  imágenes con `max-width: 100%` y tablas dentro de un contenedor con desplazamiento lateral.
- **RNF-04 (rendimiento).** Las páginas institucionales son 100% estáticas, sin JavaScript ni
  frameworks; los íconos son SVG vectoriales de pocos kilobytes. El prototipo de la plataforma
  (`sitio-web/app/`) añade JavaScript propio, sin librerías externas ni proceso de compilación.
- **Accesibilidad.** Marcado semántico (`header`, `nav`, `main`, `section`, `footer`), enlace para
  saltar al contenido, `aria-current` en la página activa, `scope` en las cabeceras de tabla,
  foco visible en todos los controles y respeto por `prefers-reduced-motion`.

---

*Documento original en Word: [`docs/originales/Entrega2_VetPetConnect.docx`](originales/Entrega2_VetPetConnect.docx)*
