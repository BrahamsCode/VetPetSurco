---
proyecto: VetPet Connect
actualizado: 2026-09-30
avance_requerimientos: 85%
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
> **El 85 % mide requerimientos, no producción.**
>
> El sistema no está desplegado. Dominio, alojamiento, respaldo y medición de rendimiento
> están en cero. Un requerimiento cumplido en local no es un requerimiento en producción.

## Resumen

| Bloque | Terminados | Parciales | Sin empezar | Avance |
| --- | --- | --- | --- | --- |
| Requerimientos funcionales (RF) | 15 | 4 | 0 | **89 %** |
| Requerimientos no funcionales (RNF) | 4 | 4 | 4 | **50 %** |
| Reglas de negocio (RN) | 22 | 0 | 0 | **100 %** |
| **Total ponderado por ítem** | 41 | 8 | 4 | **85 %** |

El parcial cuenta como medio punto. Total = (41 + 8/2) / 53 ítems.

---

## 1. Requerimientos funcionales

### Terminados

- [x] **RF-01** Acceso diferenciado por perfil — tres perfiles con middleware `rol`; probado
- [x] **RF-02** Registro autónomo del cliente — correo único y contraseña cifrada
- [x] **RF-04** Carrito de compras — acumula, actualiza, quita y persiste
- [x] **RF-05** Confirmación del pedido con descuento de inventario — bloqueo por producto, todo o nada
- [x] **RF-06** Pago en línea con tarjeta — Culqi y pasarela simulada detrás de una interfaz
- [x] **RF-08** Suscripción mensual por mascota — tres planes, pausar y cancelar
- [x] **RF-09** Despacho recurrente automático — `suscripciones:despachar`, cada día a las 03:00
- [x] **RF-10** Registro de las mascotas del cliente — alta y listado propio
- [x] **RF-11** Agenda de citas sin cruce de horarios — restricción única en el motor
- [x] **RF-12** Cierre de la cita con desenlace — atendida, cancelada o no asistió
- [x] **RF-14** Alertas de vacunación y desparasitación — `vetpet:recordatorios`, cada día a las 08:00
- [x] **RF-15** Control de inventario con alerta de reposición — semáforo y lista de reposición
- [x] **RF-17** Sitio institucional público — inicio, nosotros, productos y contacto
- [x] **RF-18** Asistente de atención en el sitio — 14 intenciones y 8 temas de menú
- [x] **RF-19** Avisos automáticos por correo — 7 salientes más el buzón de contacto

### Parciales

- [ ] **RF-03** Catálogo de productos en línea — *falta la búsqueda por nombre*
      - [x] Filtro por categoría, precio vigente y semáforo de stock
      - [ ] Buscar por nombre: el criterio de aceptación lo pide y hoy solo filtra por categoría
      - Dónde: `app/Http/Controllers/CatalogoController.php`
- [ ] **RF-07** Seguimiento del estado del pedido — *falta la pantalla del cliente*
      - [x] El Administrador avanza el estado y la secuencia no se puede saltar
      - [x] Pelusa informa el estado si el cliente pregunta
      - [ ] No existe una página «Mis pedidos»: el criterio dice que el cliente lo ve en su historial
      - Dónde: falta una ruta `GET app/pedidos` y su vista
- [ ] **RF-13** Historia clínica digital — *el dueño todavía no la ve*
      - [x] El Veterinario registra diagnóstico, tratamiento, vacuna y próxima fecha
      - [x] Un único registro clínico por atención
      - [ ] El requerimiento dice «el dueño debe poder verlo también»; hoy solo aparece en la vista del Veterinario
      - Dónde: `resources/views/app/mascotas.blade.php` no muestra historial
- [ ] **RF-16** Mantenimiento del catálogo y de los pedidos — *solo se puede crear*
      - [x] Alta de producto con SKU único y precio positivo
      - [x] Gestión de pedidos: avanzar estado
      - [ ] Editar producto (precio, stock, punto de reorden)
      - [ ] Dar de baja un producto: hay columna `activo`, falta el control en pantalla
      - Dónde: `app/Http/Controllers/AdminController.php` solo tiene `crearProducto`

---

## 2. Requerimientos no funcionales

### Cumplidos

- [x] **RNF-03** Operación simultánea sin sobreventa — bloqueo por fila y pruebas de concurrencia
- [x] **RNF-08** Reglas garantizadas por el almacén de datos — CHECK y UNIQUE en las tablas
- [x] **RNF-09** Precio histórico del pedido — el detalle guarda su propio precio
- [x] **RNF-12** Evidencia comprobable — 137 pruebas, 346 aserciones, contra MySQL real

### Parciales

- [ ] **RNF-01** Diseño adaptable al celular
      - [x] Media queries, tablas que se reordenan en tarjetas, etiquetas por celda
      - [ ] Falta probarlo de verdad en 360 px y dejar la evidencia
- [ ] **RNF-02** Protección de credenciales y datos personales
      - [x] Contraseñas cifradas, imposibles de leer
      - [ ] Falta el aviso de privacidad y el consentimiento en el registro (Ley N.º 29733)
- [ ] **RNF-07** Moneda, idioma y horario locales
      - [x] Importes en soles, zona horaria America/Lima en el contenedor
      - [ ] Falta revisar que toda fecha en pantalla salga en dd/mm/aaaa
- [ ] **RNF-11** Independencia del proveedor
      - [x] Dockerfile y compose escritos, con los tres servicios
      - [ ] Nunca se levantó el entorno completo: hay que construirlo y dejarlo documentado

### Sin empezar

- [ ] **RNF-04** Tiempo de respuesta — medir la carga del catálogo en 4G y dejar el número
- [ ] **RNF-05** Disponibilidad fuera del horario — depende del despliegue
- [ ] **RNF-06** Facilidad de uso del personal — falta el manual y la sesión de capacitación
- [ ] **RNF-10** Respaldo de la información — no existe script de copia ni prueba de restauración

---

## 3. Reglas de negocio

- [x] **22 de 22** con al menos una prueba automatizada que las verifica

No hay nada pendiente aquí. Si se agrega una regla, se agrega su prueba en el mismo cambio:
esa es la condición del RNF-12.

---

## 4. Lo que falta, por prioridad

### Primero: completar lo que el documento ya pide

- [ ] Pantalla «Mis pedidos» del cliente — **RF-07**
- [ ] Historial clínico visible para el dueño — **RF-13**
- [ ] Editar y dar de baja productos — **RF-16**
- [ ] Búsqueda por nombre en el catálogo — **RF-03**
- [ ] Aviso de privacidad y consentimiento en el registro — **RNF-02**

### Después: salir a producción

- [ ] Levantar el entorno completo con Docker y documentarlo — **RNF-11**
- [ ] Script de respaldo diario y una restauración probada — **RNF-10**
- [ ] Registrar dominio y contratar alojamiento — **RNF-05**
- [ ] Medir la carga del catálogo en conexión móvil — **RNF-04**
- [ ] Manual de uso y capacitación de treinta minutos — **RNF-06**

### Al final: cerrar la entrega

- [ ] Revisar el formato de fechas en todas las pantallas — **RNF-07**
- [ ] Probar la interfaz en 360 px y guardar la evidencia — **RNF-01**
- [ ] Regenerar las capturas anotadas contra la aplicación actual

---

## 5. Deuda técnica y riesgos

- [ ] **Las capturas de `docs/capturas-reglas/` son del prototipo.** La del carrito muestra
      un selector de «origen del pedido» que la aplicación ya no tiene, porque el origen lo
      decide el sistema. Hay que rehacerlas antes de presentar.
- [ ] **El entorno Docker nunca se construyó.** Está escrito y es coherente, pero decir que
      funciona sin haberlo levantado es afirmar lo que no se comprobó.
- [ ] **Las pruebas necesitan MySQL por TCP.** `phpunit.xml` hereda host y credenciales del
      `.env`; si la base local solo escucha por socket, fallan todas. Conviene dejarlo escrito
      en el README para que no vuelva a costar una tarde.
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
