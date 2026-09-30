# Sistema de correos de VetPet Connect

Gu&iacute;a del sistema de correo electr&oacute;nico: qu&eacute; correos existen, cu&aacute;ndo se
env&iacute;an, c&oacute;mo distinguirlos y c&oacute;mo agregar uno nuevo.

---

## Arquitectura

```
Evento del negocio  ──►  CorreoService::enviar()  ──►  Mailable  ──►  plantilla
(registro, pago,          (nunca rompe el flujo:      (datos        (marca con
 cita, despacho...)        try/catch + report)         tipados)      color por tipo)
```

| Pieza | Archivo | Rol |
| --- | --- | --- |
| Env&iacute;o seguro | `app/Services/CorreoService.php` | Envuelve `Mail::send()` en try/catch: un fallo de correo **nunca** bloquea una compra, cita o registro. Registra el error con `report()`. |
| Tipos y colores | `config/correos.php` | Fuente &uacute;nica: etiqueta y color de cabecera por tipo, buzones y colas del buz&oacute;n de entrada. |
| Plantilla base | `resources/views/emails/plantilla.blade.php` | Cabecera de marca (color por tipo), t&iacute;tulo, contenido y pie. Estilos en l&iacute;nea (los clientes de correo no aplican hojas externas). |
| Tabla de datos | `resources/views/emails/_datos.blade.php` | Parcial reutilizable de filas etiqueta/valor. |
| Mensajeros | `app/Mail/*Mail.php` | Un Mailable tipado por tipo de correo. |
| Vistas | `resources/views/emails/*.blade.php` | Contenido de cada correo; extienden `emails.plantilla`. |

---

## Correo que salen de la EMPRESA al CLIENTE

Remitente: `no-reply@vetpetsurco.pe`. Asunto descriptivo que termina en
`| VetPet Surco`. La cabecera del cuerpo lleva un color seg&uacute;n el tipo.

| Tipo (`config/correos.php`) | Correo | Cu&aacute;ndo se env&iacute;a | D&oacute;nde se dispara | Color |
| --- | --- | --- | --- | --- |
| `bienvenida` | Bienvenido a VetPet Surco | Al crear una cuenta | `AutenticacionController::registrar` | verde medio |
| `pedido` | Tu pedido N qued&oacute; registrado | Al confirmar el carrito (queda PENDIENTE) | `CarritoController::confirmar` | verde oscuro |
| `factura` | Tu comprobante de pago &mdash; Pedido N | Al aprobarse el cobro | `PagoController::procesar` | verde oscuro |
| `cita` | Tu cita est&aacute; confirmada | Al reservar en la agenda | `CitaController::reservar` | verde medio |
| `suscripcion` | Tu suscripci&oacute;n qued&oacute; ESTADO | Al pausar, reanudar o cancelar (RN-15) | `SuscripcionController::estado` | &aacute;mbar |
| `despacho` | Tu despacho mensual ya sali&oacute; &mdash; Pedido N | Al emitirse el pedido recurrente (RN-14/16) | `DespachoService::generarPendientes` | verde medio |
| `recordatorio` | Faltan N d&iacute;as para el control de MASCOTA | 15 d&iacute;as antes del control (RN-20) | comando `vetpet:recordatorios` | &aacute;mbar |

El comprobante (`factura`) es el &uacute;nico con desglose fiscal peruano:
subtotal, IGV 18% y total, m&aacute;s la leyenda *"Documento sin validez
tributaria"* porque VetPet Surco es una empresa ficticia acad&eacute;mica.

### Trabajos programados

```bash
php artisan schedule:list
```

| Hora (Lima) | Comando | Correo que genera |
| --- | --- | --- |
| 3:00 a. m. | `suscripciones:despachar` | `despacho` (por cada pedido recurrente emitido) |
| 8:00 a. m. | `vetpet:recordatorios` | `recordatorio` (por cada control en la ventana de 15 d&iacute;as) |

El recordatorio **deduplica por cach&eacute;** (`Cache::add` con llave
`recordatorio:<mascota>:<fecha>`): cada control se avisa **una sola vez**,
aunque el comando corra todos los d&iacute;as o varias veces al d&iacute;a.

---

## Correo que llegan del CLIENTE a la EMPRESA

Buz&oacute;n: `contacto@vetpetsurco.pe` (`correos.buzon` en la config).
Nacen del formulario de contacto del sitio (`POST /contacto`, con
`throttle:10,1` para frenar spam).

**Convenci&oacute;n de asuntos: empiezan con la cola del motivo.** As&iacute; el equipo
distingue el tema de un vistazo, sin abrir el mensaje:

| Cola | Motivo del formulario | Ejemplo de asunto |
| --- | --- | --- |
| `[CONSULTA VET]` | Reservar consulta veterinaria | `[CONSULTA VET] Reservar consulta veterinaria — Rosa Ibanez` |
| `[SALUD]` | Vacunaci&oacute;n o desparasitaci&oacute;n | `[SALUD] Vacunación o desparasitación — Ana Quispe` |
| `[GROOMING]` | Ba&ntilde;o y grooming | `[GROOMING] Baño y grooming — Marco Salazar` |
| `[SUSCRIPCION]` | Suscripci&oacute;n mensual de alimento | `[SUSCRIPCION] Suscripción mensual de alimento — Ana Quispe` |
| `[PEDIDO]` | Consulta sobre un pedido | `[PEDIDO] Consulta sobre un pedido — Marco Salazar` |

Cada mensaje lleva **Reply-To del cliente** (nombre + correo): responder el
correo contesta directo a quien escribi&oacute;. El cuerpo trae los datos del
formulario (nombre, correo, tel&eacute;fono, mascota y mensaje) en la plantilla
gris *"Mensaje de cliente"*.

### C&oacute;mo distinguir cualquier correo de un vistazo

| Se&ntilde;al | Del cliente (entrada) | De la empresa (salida) |
| --- | --- | --- |
| Para | `contacto@vetpetsurco.pe` | el correo del cliente |
| De | el cliente | `no-reply@vetpetsurco.pe` |
| Asunto | empieza con `[COLA]` | termina en `\| VetPet Surco` |
| Plantilla | gris *"Mensaje de cliente"* | marca con color por tipo |

---

## Correo de prueba vs producci&oacute;n

**Demo (actual): MailHog captura todo sin enviar nada de verdad.**

```env
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
```

Bandeja de prueba: <http://localhost:8026> (puerto 8026 de este entorno;
8025 en el compose original).

**Producci&oacute;n: solo cambiar el `.env` por un SMTP real** (SendGrid,
Mailgun, Amazon SES, etc.). El c&oacute;digo de los correos no cambia:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=la-clave
MAIL_ENCRYPTION=tls
```

---

## C&oacute;mo agregar un correo nuevo

1. **Registra el tipo** en `config/correos.php` (etiqueta y color de cabecera).
2. **Crea la vista** `resources/views/emails/<tipo>.blade.php`:

   ```blade
   @extends('emails.plantilla', ['tipo' => 'mio'])

   @section('titulo')T&iacute;tulo del correo@endsection

   @section('contenido')
     <p>Texto de ejemplo.</p>
     @include('emails._datos', ['filas' => [['Etiqueta', 'Valor']]])
   @endsection
   ```

3. **Crea el Mailable** `app/Mail/MioMail.php` (copia cualquier otro como
   modelo: `envelope()` con el asunto y `content()` con la vista).
4. **Disp&aacute;ralo** donde ocurre el evento, siempre a trav&eacute;s del env&iacute;o seguro:

   ```php
   app(CorreoService::class)->enviar($correoDelCliente, new MioMail($datos));
   ```

5. **Prueba**: dispara el evento y revisa la bandeja de MailHog.

> Regla de oro: los correos se env&iacute;an **siempre** por `CorreoService`, nunca
> con `Mail::send()` a pelo. Un correo jam&aacute;s debe tumbar una compra.

---

## Errores conocidos y sus causas

| S&iacute;ntoma | Causa | Soluci&oacute;n |
| --- | --- | --- |
| `UnexpectedValueException: storage/logs/laravel.log could not be opened` | El log lo cre&oacute; root (artisan por consola) y el proceso web no puede escribir. | `chmod 666 storage/logs/laravel.log` (o unificar el usuario del contenedor). |
| `Email "Nombre" does not comply with addr-spec` | Un `Address` suelto en `replyTo` de `Envelope`; espera una **lista**. | `replyTo: [new Address($correo, $nombre)]`. |
| El correo no llega y hay `report()` en el log | El SMTP no responde (MailHog detenido o credenciales malas). | Revisar `docker compose ps` y la config `MAIL_*`. |
