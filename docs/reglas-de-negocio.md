# Reglas de negocio de VetPet Connect

Las veinte reglas que gobiernan el sistema, dónde se hace cumplir cada una y en qué
pantalla del prototipo se puede ver funcionando.

- Presentación: [`docs/presentacion/ReglasDeNegocio_VetPetConnect.pptx`](presentacion/ReglasDeNegocio_VetPetConnect.pptx)
- Capturas anotadas: [`docs/capturas-reglas/`](capturas-reglas/)
- Prototipo navegable: [`sitio-web/app/`](../sitio-web/app/)
- Implementación en el navegador: [`sitio-web/app/js/reglas.js`](../sitio-web/app/js/reglas.js)
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

## El prototipo

`sitio-web/app/` es una demostración navegable de la plataforma, sin servidor: los datos viven en
`localStorage` y se reinician limpiando el almacenamiento del navegador.

| Pantalla | Rol | Reglas que demuestra |
| --- | --- | --- |
| `index.html` | — | RN-01, RN-02, RN-03 |
| `catalogo.html` | Cliente | RN-05, RN-06, RN-08, RN-09, RN-10 |
| `carrito.html` | Cliente | RN-10, RN-11, RN-12, RN-14 |
| `citas.html` | Cliente | RN-04, RN-17 |
| `mascotas.html` | Cliente | RN-04, RN-15, RN-16 |
| `clinica.html` | Veterinario | RN-18, RN-19, RN-20 |
| `admin.html` | Administrador | RN-05, RN-06, RN-07, RN-08, RN-13, RN-14, RN-20 |

### Cuentas de demostración

Todas usan la contraseña `demo123`. Son cuentas ficticias de un prototipo académico.

| Correo | Rol |
| --- | --- |
| `ana@correo.com` | Cliente |
| `marco@correo.com` | Cliente |
| `lbernal@vetpetsurco.pe` | Veterinario |
| `admin@vetpetsurco.pe` | Administrador |

### Cómo probarlo

```bash
cd sitio-web
python3 -m http.server 8000
# abrir http://localhost:8000/app/
```

## Qué está y qué no está implementado

El prototipo **sí** aplica las veinte reglas del lado del cliente, y su comportamiento fue
verificado con un recorrido automatizado de 32 comprobaciones sobre el navegador real
(ver el flujo `Verificar proyecto` de GitHub Actions).

El prototipo **no** es el sistema en producción. En particular:

- No hay servidor ni base de datos: los datos viven en el navegador de cada persona.
- El hash de la contraseña se calcula con SHA-256 del navegador solo para demostrar que no se
  almacena el texto en claro. En producción es BCrypt del lado del servidor (RNF-02).
- Las reglas que en el diseño viven en el motor de datos (`UNIQUE`, `CHECK`, `FOR UPDATE`) aquí
  están reimplementadas en JavaScript. Es exactamente la diferencia que explica la presentación:
  una regla en la aplicación se puede saltar; la misma regla en la base de datos, no.

### Capturas anotadas

Las imágenes de [`docs/capturas-reglas/`](capturas-reglas/) se generan automáticamente: un script
abre el prototipo, recorre los elementos marcados con `data-rn` en el DOM y dibuja el marco y la
chapa sobre el control real. Por eso señalan el elemento exacto que aplica cada regla y no una
anotación colocada a mano.
