# Reglas de negocio de VetPet Connect

Las veinte reglas que gobiernan el sistema, dónde se hace cumplir cada una y en qué
pantalla del prototipo se puede ver funcionando.

- Presentación: [`docs/presentacion/ReglasDeNegocio_VetPetConnect.pptx`](presentacion/ReglasDeNegocio_VetPetConnect.pptx)
- Capturas anotadas: [`docs/capturas-reglas/`](capturas-reglas/)
- Aplicación Laravel: [`public_html/app/`](../public_html/app/)
- Servicios de dominio: [`public_html/app/Services/`](../public_html/app/Services/)
- Pruebas de las 20 reglas: [`public_html/tests/Feature/`](../public_html/tests/Feature/)
- Implementación en la base de datos: [`basedatos/01_esquema.sql`](../basedatos/01_esquema.sql)

## Catálogo

| Regla | Enunciado | En la base de datos | En el prototipo | Captura |
| --- | --- | --- | --- | --- |
| **RN-01** | Cada persona tiene un solo rol y es excluyente | `usuarios.rol ENUM` | `rn01ValidarRol()`, menú por rol | 01 |
| **RN-02** | Un correo identifica a una sola cuenta | `correo UNIQUE` | `rn02CorreoUnico()` | 01 |
| **RN-03** | La contraseña nunca se guarda legible | `password_hash VARCHAR(255)` | `rn03Hash()` | 01 |
| **RN-04** | Toda mascota pertenece a un cliente registrado | `FOREIGN KEY (cliente_id)` | `rn04MascotasDelCliente()` | 05, 06 |
| **RN-05** | Cada producto tiene un SKU irrepetible | `codigo_sku UNIQUE` | `rn05SkuUnico()` | 02, 10 |
| **RN-06** | Ningún producto se vende a precio cero o negativo | `CHECK (precio > 0)` | `rn06PrecioPositivo()` | 02, 10 |
| **RN-07** | El inventario nunca queda en negativo | `CHECK (stock_actual >= 0)` | `rn07StockNoNegativo()` | 08 |
| **RN-08** | El semáforo decide cuándo reponer | `v_semaforo_inventario` | `rn08Semaforo()` | 08 |
| **RN-09** | Un producto aparece una sola vez por pedido | `uk_linea_pedido` | `rn09AgregarAlCarrito()` | 02 |
| **RN-10** | No se compran cantidades menores o iguales a cero | `CHECK (cantidad > 0)` | `rn10CantidadPositiva()` | 02, 04 |
| **RN-11** | El precio de un pedido emitido ya no cambia | `detalle_pedidos.precio_unitario` | `rn11Subtotal()` | 04 |
| **RN-12** | Sin stock suficiente no hay pedido | `sp_confirmar_pedido` | `rn12ConfirmarPedido()` | 04 |
| **RN-13** | El pedido recorre una secuencia de estados | `estado ENUM` | `rn13Avanzar()` | 09 |
| **RN-14** | Se distingue compra puntual de despacho recurrente | `pedidos.tipo_origen` | `rn14EsRecurrente()` | 04, 09 |
| **RN-15** | El cliente pausa o cancela su plan cuando quiera | `estado ENUM` | `rn15CambiarEstado()` | 06 |
| **RN-16** | Cada plan tiene frecuencia y próximo despacho | `frecuencia_dias`, `proximo_despacho` | `rn16ProximoDespacho()` | 06 |
| **RN-17** | Un veterinario no atiende dos citas a la misma hora | `uk_agenda` | `rn17ReservarCita()` | 05 |
| **RN-18** | Toda cita registra su desenlace | `estado ENUM` | `rn18CerrarCita()` | 07 |
| **RN-19** | Cada atención genera un único registro clínico | `cita_id UNIQUE` | `rn19RegistrarAtencion()` | 07 |
| **RN-20** | Se avisa 15 días antes del próximo control | `v_recordatorios_salud` | `rn20Recordatorios()` | 07, 08 |

## Reparto por capa

| Capa | Reglas | Total |
| --- | --- | --- |
| Restricción de la base de datos | RN-01, 02, 04, 05, 06, 07, 11, 13, 14, 15, 16, 17, 18, 19 | 14 |
| Base de datos y aplicación | RN-09, RN-10 | 2 |
| Vista SQL | RN-08, RN-20 | 2 |
| Procedimiento almacenado | RN-12 | 1 |
| Solo en la aplicación | RN-03 | 1 |

## La plataforma

La aplicación Laravel sirve tanto el sitio institucional como los módulos de los cuatro roles.

| Ruta | Rol | Reglas que aplica |
| --- | --- | --- |
| `/ingresar`, `/registro` | — | RN-01, RN-02, RN-03 |
| `/app/catalogo` | Cliente | RN-05, RN-06, RN-08, RN-09, RN-10 |
| `/app/carrito` | Cliente | RN-10, RN-11, RN-12, RN-14 |
| `/app/citas` | Cliente | RN-04, RN-17 |
| `/app/mascotas` | Cliente | RN-04, RN-15, RN-16 |
| `/app/clinica` | Veterinario | RN-18, RN-19, RN-20 |
| `/app/admin` | Administrador | RN-05, RN-06, RN-07, RN-08, RN-13, RN-14, RN-20 |

### Cuentas de demostración

Todas usan la contraseña `demo123`. Son cuentas ficticias de un proyecto académico, sembradas
por `public_html/database/seeders/UsuarioSeeder.php`.

| Correo | Rol |
| --- | --- |
| `ana.quispe@correo.com` | Cliente |
| `marco.s@correo.com` | Cliente |
| `rosa.ibanez@correo.com` | Cliente |
| `lbernal@vetpetsurco.pe` | Veterinario |
| `dpalacios@vetpetsurco.pe` | Veterinario |
| `admin@vetpetsurco.pe` | Administrador |

### Cómo probarlo

```bash
cd docker/dockerfile && docker compose up -d   # con Docker
# o, con PHP y MySQL locales:
cd public_html && php artisan migrate --seed && php artisan serve
```

## Cómo se comprueba que se cumplen

`php artisan test` ejecuta la suite completa contra **MySQL real**, no contra SQLite: eso
importa porque las restricciones `CHECK` y `UNIQUE` solo existen en el motor.

Cada regla que vive en dos capas se comprueba en las dos:

1. Que el servicio lanza su excepción con el código `RN-xx`.
2. Que la restricción del motor rechaza el dato aunque alguien salte el servicio e inserte
   directo con `DB::table(...)->insert(...)`.

Hay además un recorrido de navegador en [`pruebas/navegacion-plataforma.mjs`](../pruebas/navegacion-plataforma.mjs)
que maneja la aplicación como lo haría una persona.

### Advertencia sobre SQLite

Las restricciones `CHECK` se crean con `DB::statement` solo bajo MySQL y MariaDB, porque SQLite
no admite `ALTER TABLE ADD CONSTRAINT`. Si alguien cambiara la suite a SQLite, esa capa de
protección desaparecería y las pruebas del motor dejarían de significar nada.

### Límite heredado del esquema

El `UNIQUE uk_agenda (veterinario_id, fecha_hora)` no distingue el estado de la cita, así que un
horario cuya cita fue **cancelada** no se puede volver a reservar: lo rechaza el motor. Viene del
esquema de la Entrega 2 y se resolvería con un índice parcial o incluyendo el estado en la clave.

### Capturas anotadas

Las imágenes de [`docs/capturas-reglas/`](capturas-reglas/) se generan automáticamente con
[`herramientas/capturas-anotadas.mjs`](../herramientas/capturas-anotadas.mjs): el script abre la
aplicación, recorre los elementos marcados con `data-rn` en el DOM y dibuja el marco y la chapa
sobre el control real. Por eso señalan el elemento exacto que aplica cada regla y no una
anotación colocada a mano. Las vistas Blade conservan esas marcas, así que el generador sigue
funcionando después de la migración.
