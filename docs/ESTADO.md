---
proyecto: VetPet Connect
actualizado: 2026-09-30
avance_requerimientos: 82%
avance_despliegue: 0%
tags: [vetpet, estado, backlog]
---

# Estado del proyecto

Documento de trabajo del equipo. No es una entrega del curso: sirve para saber qué falta
y quién lo toma. Se mide **contra los requerimientos del documento
[VPC-REQ-001](requerimientos/Requerimientos_VetPetConnect.docx)**, no contra la sensación
de avance.

**Cómo se lee un estado.** Un requerimiento está terminado cuando cumple su criterio de
aceptación, no cuando el código existe. Por eso hay parciales: funcionan, pero no cumplen
todo lo que el documento pide.

> [!WARNING]
> **El 82 % mide requerimientos, no producción.**
>
> El sistema no está desplegado. Dominio, alojamiento, respaldo y medición de rendimiento
> están en cero. Un requerimiento cumplido en local no es un requerimiento en producción.

> [!NOTE]
> **Por qué bajó de 86 % a 82 % (revisión del 30/09).** Se volvió a leer cada criterio de
> aceptación contra el código. RF-10, RF-12 y RF-15 estaban marcados como terminados pero
> les falta algo que el documento pide, y RNF-03 no tenía la prueba simultánea que exige su
> criterio. RF-07 sí se cerró. El número es más bajo porque es más exacto.

## Resumen

| Bloque | Terminados | Parciales | Sin empezar | Avance |
| --- | --- | --- | --- | --- |
| Requerimientos funcionales (RF) | 13 | 6 | 0 | **84 %** |
| Requerimientos no funcionales (RNF) | 3 | 5 | 4 | **46 %** |
| Reglas de negocio (RN) | 22 | 0 | 0 | **100 %** |
| **Total ponderado por ítem** | 38 | 11 | 4 | **82 %** |

El parcial cuenta como medio punto. Total = (38 + 11/2) / 53 ítems.

---

## 1. Requerimientos funcionales

### Terminados

- [x] **RF-01** Acceso diferenciado por perfil — tres perfiles con middleware `rol`; probado
- [x] **RF-02** Registro autónomo del cliente — correo único y contraseña cifrada
- [x] **RF-04** Carrito de compras — acumula, actualiza, quita y persiste
- [x] **RF-05** Confirmación del pedido con descuento de inventario — bloqueo por producto, todo o nada
- [x] **RF-06** Pago en línea con tarjeta — Culqi y pasarela simulada detrás de una interfaz;
      el pedido solo pasa a pagado si la pasarela aprueba (el panel ya no puede marcarlo a mano)
- [x] **RF-07** Seguimiento del estado del pedido — pantalla «Mis pedidos» con barra de avance
      (En camino / Listo para recoger según la modalidad) y correo cuando el pedido sale
- [x] **RF-08** Suscripción mensual por mascota — tres planes, pausar y cancelar; una mascota
      tiene un solo plan vigente (ver el riesgo del texto de RF-08 en la sección 5)
- [x] **RF-09** Despacho recurrente automático — `suscripciones:despachar`, programado a las 03:00
      ⚠️ solo corre solo si el programador de tareas está activo (sección 5)
- [x] **RF-11** Agenda de citas sin cruce de horarios — restricción única en el motor; el
      cliente elige profesional y solo ve los horarios libres
- [x] **RF-14** Alertas de vacunación y desparasitación — `vetpet:recordatorios`, programado a las 08:00;
      lista de los próximos 15 días en el panel ⚠️ mismo aviso que RF-09
- [x] **RF-17** Sitio institucional público — inicio, nosotros, productos y contacto
- [x] **RF-18** Asistente de atención en el sitio — 14 intenciones y 8 temas de menú
- [x] **RF-19** Avisos automáticos por correo — 8 salientes (se sumó «pedido en camino /
      listo para recoger») más el buzón de contacto ⚠️ el recordatorio diario depende del
      programador de tareas

### Parciales

- [ ] **RF-03** Catálogo de productos en línea — *falta la búsqueda por nombre*
      - [x] Filtro por categoría, precio vigente y semáforo de stock
      - [x] Un producto con `activo = false` no aparece (hoy solo se puede desactivar en la base, ver RF-16)
      - [ ] Buscar por nombre: el documento lo pide y hoy solo filtra por categoría
      - Dónde: `app/Http/Controllers/CatalogoController.php`
- [ ] **RF-10** Registro de las mascotas del cliente — *no se pueden editar*
      - [x] Alta con nombre, especie, raza, fecha de nacimiento y peso
      - [x] En todos los formularios el cliente solo ve y elige sus propias mascotas
      - [ ] «Mantenerlas actualizadas»: no hay forma de corregir el peso, la raza o las alergias
      - Dónde: `routes/web.php` solo tiene `GET` y `POST /mascotas`; falta editar
- [ ] **RF-12** Cierre de la cita con desenlace — *falta el indicador de inasistencia*
      - [x] El veterinario cierra cada cita como atendida o no asistió desde su panel
      - [x] Las citas de días anteriores sin cerrar aparecen como «Pendientes de cerrar»
      - [ ] El criterio pide que «el indicador de inasistencia se calcula sobre ellos»:
            no existe ese indicador en ninguna pantalla
      - Dónde: un indicador más en `AdminController::index` (no asistió ÷ citas cerradas)
- [ ] **RF-13** Historia clínica digital — *el dueño todavía no la ve*
      - [x] El Veterinario registra diagnóstico, tratamiento, vacuna y próxima fecha
      - [x] Un único registro clínico por atención; historial con buscador y páginas
      - [ ] El requerimiento dice «el dueño debe poder verlo también»; hoy solo lo ve el Veterinario
      - Dónde: `resources/views/app/mascotas.blade.php` no muestra historial
- [ ] **RF-15** Control de inventario con alerta de reposición — *falta la alerta posterior a la venta*
      - [x] Punto de reorden por producto y semáforo en el panel del Administrador
      - [ ] El criterio pide que el producto aparezca también «en la alerta posterior a la venta»:
            al confirmar un pedido no se avisa nada
      - [ ] La pantalla del carrito **promete** «avisa qué productos quedaron bajo el punto de
            reorden», y no ocurre
      - Dónde: `CarritoController::confirmar` y `resources/views/app/carrito.blade.php`
- [ ] **RF-16** Mantenimiento del catálogo y de los pedidos — *solo se puede crear*
      - [x] Alta de producto con SKU único y precio positivo
      - [x] Gestión de pedidos: tablero por etapas, avanzar estado y anular lo no pagado con devolución de stock
      - [ ] Editar producto (precio, stock, punto de reorden)
      - [ ] Dar de baja un producto: hay columna `activo`, falta el control en pantalla
      - Dónde: `app/Http/Controllers/AdminController.php` solo tiene `crearProducto`

---

## 2. Requerimientos no funcionales

### Cumplidos

- [x] **RNF-08** Reglas garantizadas por el almacén de datos — CHECK y UNIQUE en las tablas;
      hay pruebas que escriben directo en la base y el motor las rechaza
- [x] **RNF-09** Precio histórico del pedido — el detalle guarda su propio precio
- [x] **RNF-12** Evidencia comprobable — 170 pruebas, 441 aserciones, contra MySQL
      (`vetpet_connect_test`); las 22 reglas tienen al menos una prueba con su código

### Parciales

- [ ] **RNF-01** Diseño adaptable al celular
      - [x] Media queries, tablas que se reordenan en tarjetas, etiquetas por celda
      - [x] Medido el 30/09 a 360 px: catálogo, carrito, mis pedidos, pago, citas y mascotas
            sin desplazamiento horizontal
      - [ ] Falta completar una compra y una reserva de punta a punta en un teléfono y guardar las capturas
- [ ] **RNF-02** Protección de credenciales y datos personales
      - [x] Contraseñas cifradas, imposibles de leer
      - [ ] Falta el aviso de privacidad y el consentimiento en el registro (Ley N.º 29733)
- [ ] **RNF-03** Operación simultánea sin sobreventa — *falta la prueba simultánea*
      - [x] El mecanismo existe: bloqueo por fila en compra, anulación y planes; UNIQUE en la agenda
      - [ ] El criterio pide «dos compras y dos reservas simultáneas»: las pruebas actuales son
            secuenciales. La demostración de carrera (`DemoCarreraStock`) está en un stash local,
            no en el repositorio
- [ ] **RNF-07** Moneda, idioma y horario locales
      - [x] Importes en soles; zona horaria America/Lima en la aplicación (`config/app.php`
            estaba en UTC: «hoy» y las horas de los correos salían 5 horas adelantadas)
      - [ ] Quedan fechas en formato aaaa-mm-dd:
            `app/citas.blade.php:107` (mis citas), `app/mascotas.blade.php:102` (próximo despacho),
            `SuscripcionController.php:74` (aviso al contratar) y los recordatorios del panel admin
- [ ] **RNF-11** Independencia del proveedor
      - [x] Dockerfile y compose escritos, con los tres servicios
      - [x] El entorno se levanta en local (app en :8080, MySQL en :13306, Mailhog en :8026)
      - [ ] Falta documentar cómo levantarlo y agregar el programador de tareas

### Sin empezar

- [ ] **RNF-04** Tiempo de respuesta — medir la carga del catálogo en 4G y dejar el número
- [ ] **RNF-05** Disponibilidad fuera del horario — depende del despliegue
- [ ] **RNF-06** Facilidad de uso del personal — falta el manual y la sesión de capacitación
- [ ] **RNF-10** Respaldo de la información — no existe script de copia ni prueba de restauración

---

## 3. Reglas de negocio

- [x] **22 de 22** con al menos una prueba automatizada que las verifica (`test_rn01` a `test_rn22`)

No hay nada pendiente aquí. Si se agrega una regla, se agrega su prueba en el mismo cambio:
esa es la condición del RNF-12.

---

## 4. Lo que falta, por prioridad

### Primero: completar lo que el documento ya pide

- [x] Pantalla «Mis pedidos» del cliente — **RF-07**
- [ ] Alerta de reposición al confirmar un pedido, o quitar la promesa del carrito — **RF-15**
- [ ] Indicador de inasistencia en el panel del Administrador — **RF-12**
- [ ] Editar los datos de una mascota — **RF-10**
- [ ] Historial clínico visible para el dueño — **RF-13**
- [ ] Editar y dar de baja productos — **RF-16**
- [ ] Búsqueda por nombre en el catálogo — **RF-03**
- [ ] Aviso de privacidad y consentimiento en el registro — **RNF-02**
- [ ] Prueba de compras y reservas simultáneas dentro del repositorio — **RNF-03**

### Después: salir a producción

- [ ] Agregar el programador de tareas al compose y documentar el entorno — **RNF-11**, y de paso RF-09, RF-14 y RF-19
- [ ] Script de respaldo diario y una restauración probada — **RNF-10**
- [ ] Registrar dominio y contratar alojamiento — **RNF-05**
- [ ] Medir la carga del catálogo en conexión móvil — **RNF-04**
- [ ] Manual de uso y capacitación de treinta minutos — **RNF-06**

### Al final: cerrar la entrega

- [ ] Pasar a dd/mm/aaaa las cuatro fechas que quedan — **RNF-07**
- [ ] Completar compra y reserva en un teléfono y guardar la evidencia — **RNF-01**
- [ ] Regenerar las capturas anotadas contra la aplicación actual
- [ ] Alinear el documento de requerimientos con lo construido y regenerar el `.docx` (sección 5)

---

## 5. Deuda técnica y riesgos

- [ ] **El programador de tareas no corre en Docker.** El contenedor no ejecuta
      `schedule:work` ni cron, así que `suscripciones:despachar` (RF-09),
      `vetpet:recordatorios` (RF-14, RF-19) y `pedidos:anular-vencidos` solo corren a mano.
      Falta un servicio `scheduler` en el compose. Mientras tanto, en la demo hay que
      lanzarlos con `php artisan`.
- [ ] **El documento de requerimientos quedó atrás del sistema.**
      - El texto de **RF-08** todavía dice «dos planes vigentes del mismo producto», pero
        RN-22 ya dice «un solo plan vigente por mascota». Hay que igualar RF-08 en
        `herramientas/requerimientos/datos.js`.
      - La **modalidad de entrega** (delivery en Surco a S/ 8.00, gratis desde S/ 80, o recojo
        en tienda) y la **anulación automática a las 48 h** se construyeron sin estar escritas
        en VPC-REQ-001. Según la regla de la sección 6, hay que agregarlas (como RF nuevo o
        ampliando RF-05 y RF-07).
      - El `.docx` no se regeneró después de esos cambios.
- [ ] **Los datos de demostración no cumplen las reglas nuevas.** Hay mascotas con más de un
      plan vigente (creados antes del cambio de RN-22) y 5 pedidos marcados como pagados sin
      cobro (antes de cerrar el paso manual a PAGADO). Conviene sembrar de nuevo la base
      antes de presentar.
- [ ] **Las capturas de `docs/capturas-reglas/` son del prototipo.** La del carrito muestra
      un selector de «origen del pedido» que la aplicación ya no tiene, porque el origen lo
      decide el sistema. Hay que rehacerlas antes de presentar.
- [ ] **Las pruebas necesitan MySQL por TCP.** `phpunit.xml` fija `DB_CONNECTION=mysql` y la base
      `vetpet_connect_test`, y hereda host y credenciales del `.env`; si la base local solo
      escucha por socket, fallan todas. Conviene dejarlo escrito en el README.
- [ ] **Culqi está integrado pero no probado contra el ambiente de pruebas real.** Las pruebas
      usan la pasarela simulada.

---

## 6. Cómo mantener este documento

- Se actualiza en el mismo cambio que mueve un requerimiento, no después.
- Un `- [ ]` pasa a `- [x]` solo cuando se cumple el **criterio de aceptación** del documento
  de requerimientos, no cuando el código compila.
- Si aparece algo que el negocio no pidió, primero va al documento de requerimientos como
  RF nuevo; si no está ahí, no se construye.
- Al cerrar un bloque, actualizar la tabla del resumen y la fecha del encabezado.

### En Obsidian

Las casillas son sintaxis estándar, así que funcionan sin instalar nada: se marcan con un
clic y Obsidian guarda el cambio en el archivo. Dos cosas que ayudan:

- El plugin **Tasks** junta en una sola nota todos los `- [ ]` del repositorio, con filtros.
- Las propiedades del encabezado (`avance_requerimientos`, `tags`) las lee **Dataview** si
  se quiere armar un tablero con varios proyectos.

Nada de eso es obligatorio: el archivo se lee igual en GitHub y en cualquier editor de texto.
