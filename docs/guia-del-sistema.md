# VetPet Connect explicado

Gu&iacute;a de lectura del sistema: qu&eacute; es, c&oacute;mo funciona por dentro y por qu&eacute;
est&aacute; hecho as&iacute;. Pensada para entender la plataforma en 10 minutos, tanto
para la sustentaci&oacute;n como para un repaso r&aacute;pido.

---

## 1. De qu&eacute; se trata

**VetPet Connect** es el portal de comercio electr&oacute;nico **B2C** de *VetPet
Surco E.I.R.L.*, una veterinaria y pet shop MYPE de Santiago de Surco.
Integra tres pilares:

| Pilar | Qu&eacute; resuelve |
| --- | --- |
| 🛒 **Tienda en l&iacute;nea** | Alimento, arena, accesorios y medicamentos con stock real, carrito y pago con tarjeta. |
| 🔁 **Suscripci&oacute;n mensual** | El alimento de la mascota llega solo cada mes: ingreso recurrente para la tienda, comodidad para el cliente. |
| 💉 **Historia cl&iacute;nica digital** | Vacunas, desparasitaciones y controles registrados, con recordatorio autom&aacute;tico 15 d&iacute;as antes. |

> Proyecto acad&eacute;mico del Curso de E-business (Equipo 4, 2026). La empresa es
> ficticia: los comprobantes llevan la leyenda *"sin validez tributaria"*.

---

## 2. Los cuatro roles (RN-01)

Cada cuenta tiene **un solo rol**, excluyente, y solo ve sus m&oacute;dulos:

| Rol | M&oacute;dulos | Pantallas |
| --- | --- | --- |
| **CLIENTE** | Comprar y cuidar a su mascota | Cat&aacute;logo · Carrito · Pago · Reservar cita · Mis mascotas |
| **VETERINARIO** | Atender la agenda | Ficha cl&iacute;nica (atender citas, desenlaces) |
| **ADMIN** | Operar la tienda | Dashboard (inventario, pedidos, ingreso recurrente, recordatorios) |
| Invitado | Sitio institucional | Inicio · Nosotros · Productos · Contacto |

Un cliente que pide la URL del admin recibe **403**: el rol se comprueba en el
servidor, nunca en el navegador.

---

## 3. El flujo de compra, paso a paso

```
Catálogo ──► Carrito ──► Confirmar ──► Pagar con tarjeta ──► Comprobante por correo
(stock real)  (RN-09..11)  (RN-12/14)    (RN-21)              (factura con IGV)
```

Qu&eacute; protege cada paso:

| Paso | Regla | Qu&eacute; impide |
| --- | --- | --- |
| Agregar al carrito | **RN-09** | Que un producto se duplique: repetirlo **acumula** cantidades. |
| Cantidad | **RN-10** | Comprar 0 o menos. |
| Precio | **RN-11** | Que el precio cambie despu&eacute;s: queda **congelado** en la l&iacute;nea. |
| Confirmar | **RN-12** | La sobreventa: valida stock de **todas** las l&iacute;neas antes de tocar el inventario. Si una falla, **no se registra nada** (transacci&oacute;n con `FOR UPDATE`). |
| Origen | **RN-14** | Mezclar ventas: el carrito es **COMPRA_DIRECTA**; los despachos de suscripci&oacute;n nacen **SUSCRIPCION**. |
| Pagar | **RN-21** | Que un cobro no aprobado marque el pedido como PAGADO. El n&uacute;mero de tarjeta **nunca** llega al servidor: se tokeniza en el navegador. Todo intento (aprobado o no) queda en el historial. |

Al aprobarse el cobro, el cliente recibe su **comprobante por correo**
(boleta `B001-0000XX` con subtotal, **IGV 18%** y total &mdash; precios de venta
al p&uacute;blico con IGV incluido, D.S. 055-99-EF).

---

## 4. El ingreso recurrente (lo que hace negocio al negocio)

```
Cliente contrata plan ──► cada N días (3:00 a. m.) ──► sistema emite el pedido SOLO
     (RN-22, sin duplicados)        (RN-16)                (RN-12/14) + correo al cliente
```

- **RN-22**: no se puede contratar dos veces el mismo plan vigente para la misma mascota y producto.
- **RN-15**: el cliente **pausa o cancela cuando quiera**; lo cancelado no se reactiva (se contrata de nuevo).
- **RN-16**: tras despachar, el pr&oacute;ximo despacho salta un ciclo completo desde la fecha que tocaba, no desde hoy.
- **RN-12**: si no hay stock, no se despacha y **la fecha no avanza** &mdash; se reintenta cuando repongan.

El comando `suscripciones:despachar` corre cada madrugada y por cada pedido
emitido env&iacute;a el correo *"Tu despacho mensual ya sali&oacute;"*.

---

## 5. Salud: la historia cl&iacute;nica

- **RN-04**: cada mascota pertenece a un solo cliente; solo su due&ntilde;o la ve.
- **RN-17/18**: la agenda impide el choque de horarios y el desenlace de la cita
  solo lo cierra el veterinario (ATENDIDA, CANCELADA, NO_ASISTIO).
- **RN-20**: cada atenci&oacute;n fija una **pr&oacute;xima fecha** y el sistema avisa por
  correo **15 d&iacute;as antes** (`vetpet:recordatorios`, 8:00 a. m., una sola vez
  por control gracias a la deduplicaci&oacute;n por cach&eacute;).

---

## 6. Los correos (8 mensajes, 2 direcciones)

| Sentido | Buz&oacute;n | C&oacute;mo se reconoce |
| --- | --- | --- |
| Empresa &rarr; cliente | sale de `no-reply@vetpetsurco.pe` | Asunto descriptivo + `\| VetPet Surco`, plantilla con color por tipo |
| Cliente &rarr; empresa | llega a `contacto@vetpetsurco.pe` | Asunto con cola: `[PEDIDO]`, `[CONSULTA VET]`, `[SALUD]`, `[GROOMING]`, `[SUSCRIPCION]` y **Reply-To** del cliente |

Correos salientes: **bienvenida, pedido registrado, comprobante (factura),
cita confirmada, cambio de suscripci&oacute;n, despacho mensual y recordatorio de
salud**. Todos con plantilla de marca y un dise&ntilde;o por tipo (fuente &uacute;nica:
`config/correos.php`).

Regla de oro del sistema: **un fallo de correo nunca rompe una compra** &mdash;
todos los env&iacute;os pasan por `CorreoService`, que absorve el error y lo deja
en el log. Detalle completo en [`sistema-de-correos.md`](sistema-de-correos.md).

---

## 7. Pelusa, el asistente de ayuda

El chat de la esquina no es un bot de juguete:

1. **Entiende preguntas escritas** con un clasificador de intenciones en
   espa&ntilde;ol (frases y palabras clave ponderadas, normalizaci&oacute;n de acentos).
2. **Responde con datos reales de la cuenta** v&iacute;a un endpoint autenticado
   (`/app/ayuda/consultar`): *"d&oacute;nde est&aacute; mi pedido"*, *"cu&aacute;nto cuesta el
   alimento"*, *"qu&eacute; tengo en el carrito"*, *"mi suscripci&oacute;n"*, *"mis citas"*.
3. **Nunca alucina**: lo que no entiende lo admite y ofrece temas. Consulta
   solo lo del usuario autenticado (RN-01/RN-04) y el endpoint lleva
   throttling, CSRF y validaci&oacute;n.

Sin JavaScript se muestra una ayuda est&aacute;tica con los tel&eacute;fonos y el formulario.

---

## 8. Calidad: la prueba es el sistema

```
137 pruebas · 346 aserciones · contra MySQL real
```

La suite (`tests/Feature/Reglas/`) comprueba cada regla en **sus dos capas**:
la de la aplicaci&oacute;n (middleware, requests, servicios) y la del **motor**
(ENUM, UNIQUE, CHECK y claves for&aacute;neas). Corre contra una base
`vetpet_connect_test` separada, con `RefreshDatabase`, y pasa en Docker y con
MySQL local.

---

## 9. C&oacute;mo verlo funcionando

```bash
cd docker/dockerfile
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

| Qu&eacute; | D&oacute;nde |
| --- | --- |
| Sitio y plataforma | <http://localhost:8080> |
| Bandeja de correos (demo) | <http://localhost:8026> |
| Cuentas de demostraci&oacute;n | un clic en "Cuentas de demostraci&oacute;n" del ingreso (clave `demo123`) |
| Suite de pruebas | `php artisan test` |

### Guion r&aacute;pido de demo

1. **Invitado**: sitio institucional &rarr; formulario de contacto (llega al buz&oacute;n con `[COLA]`).
2. **Cliente** (`ana.quispe@correo.com`): agregar al carrito &rarr; carrito con **IGV** &rarr; confirmar &rarr; checkout con tarjeta 3D &rarr; tarjeta rechazada y luego aprobada &rarr; **confeti + comprobante en el correo**.
3. **Pelusa**: *"d&oacute;nde est&aacute; mi pedido"* (responde con el pedido real).
4. **Admin** (`admin@vetpetsurco.pe`): dashboard con sem&aacute;foro de inventario y avance de pedidos.
5. **Automatizaci&oacute;n**: `php artisan suscripciones:despachar` y `php artisan vetpet:recordatorios` (correos solos).

---

## 10. Mapa de reglas de negocio

| Regla | En una frase |
| --- | --- |
| RN-01 | Un rol por persona, excluyente, verificado en el servidor. |
| RN-02 | Un correo identifica una sola cuenta. |
| RN-03 | La contrase&ntilde;a se guarda hasheada (BCrypt), nunca legible. |
| RN-04 | Cada mascota es de un solo cliente y solo &eacute;l la ve. |
| RN-05 | El SKU es irrepetible (&iacute;ndice UNIQUE). |
| RN-06 | El precio siempre es mayor que cero (CHECK). |
| RN-07 | El stock nunca queda negativo (CHECK + transacci&oacute;n). |
| RN-08 | Sem&aacute;foro de reposici&oacute;n frente al punto de reorden. |
| RN-09 | Un producto aparece una vez por pedido; repetirlo acumula. |
| RN-10 | La cantidad siempre es un entero mayor que cero. |
| RN-11 | El precio se congela en la l&iacute;nea al agregarla. |
| RN-12 | Sin stock no hay pedido, ni a medias. |
| RN-13 | El pedido avanza por la secuencia PENDIENTE &rarr; PAGADO &rarr; ENVIADO &rarr; ENTREGADO. |
| RN-14 | Compra directa y despacho de suscripci&oacute;n son or&iacute;genes distintos. |
| RN-15 | El cliente pausa o cancela su plan cuando quiere. |
| RN-16 | Cada plan tiene frecuencia y pr&oacute;ximo despacho. |
| RN-17 | Un veterinario no atiende dos citas a la misma hora. |
| RN-18 | El desenlace de la cita lo cierra el veterinario. |
| RN-20 | Aviso de control 15 d&iacute;as antes, por correo, una sola vez. |
| RN-21 | Todo intento de cobro deja rastro; sin aprobaci&oacute;n no hay PAGADO. |
| RN-22 | No se duplica un plan vigente para la misma mascota y producto. |

---

## 11. Pila t&eacute;cnica

- **Laravel 12** sobre **PHP 8.2**, **MySQL 8.0**, entorno con **Docker** (Apache + MailHog).
- HTML5 sem&aacute;ntico + CSS3 **sin frameworks** (una hoja por capa, un quiebre de 760&nbsp;px).
- JavaScript solo donde aporta (tarjeta 3D del pago, avisos, Pelusa); las
  p&aacute;ginas institucionales no cargan JS (RNF-04).
- Accesibilidad: skip-link, `aria-current`, foco visible, `prefers-reduced-motion`,
  tablas con caption y scope.
