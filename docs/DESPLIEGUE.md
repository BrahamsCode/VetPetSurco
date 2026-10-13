# Despliegue en Render

El repositorio trae todo lo que Render necesita. El despliegue son cinco pasos
y no requiere tocar código.

## Lo que se publica

| Recurso | Qué es | Plan |
| --- | --- | --- |
| `vetpet-connect` | La aplicación Laravel dentro de un contenedor | Free |
| `vetpet-db` | Base de datos PostgreSQL | Free |

## Pasos

1. **Entra a tu proyecto en Render** y elige **New → Blueprint**.
2. **Selecciona este repositorio** y la rama **`main`**, que es la que reúne todo
   el trabajo. Render detecta el archivo `render.yaml` de la raíz, así que el
   campo *Blueprint Path* se deja vacío.
3. **Revisa el plan** de los dos recursos: ambos deben quedar en *Free*.
4. **Apply**. Render construye la imagen a partir de `docker/render/Dockerfile`,
   crea la base de datos e inyecta sus credenciales en el servicio web.
5. **Espera a que el estado sea *Deployed*.** La primera construcción tarda entre
   cinco y diez minutos, porque instala las dependencias de PHP.

Al terminar, la URL pública aparece arriba del servicio, con la forma
`https://vetpet-connect.onrender.com`.

## Qué ocurre en el primer arranque

El script `docker/render/arranque.sh` hace tres cosas antes de levantar Apache:

1. Pone a Apache a escuchar en el puerto que Render inyecta como `$PORT`.
2. Ejecuta `php artisan migrate --force --seed`, de modo que la base queda con
   el esquema y los datos de demostración.
3. Cachea configuración, rutas y vistas.

## Cuentas de demostración

Todas usan la contraseña `demo123`.

| Correo | Perfil |
| --- | --- |
| `ana.quispe@correo.com` | Cliente |
| `lbernal@vetpetsurco.pe` | Veterinario |
| `admin@vetpetsurco.pe` | Administrador |

## Direcciones útiles una vez desplegado

| Ruta | Para qué |
| --- | --- |
| `/` | Sitio institucional |
| `/blog` | Blog corporativo con el caso del negocio |
| `/terminos` y `/privacidad` | Documentos legales |
| `/sitemap.xml` | Mapa del sitio, para dar de alta en buscadores |
| `/ingresar` | Acceso a la plataforma |

## Variables que quedan a tu criterio

Dos están declaradas en `render.yaml` con `sync: false`, lo que significa que
Render las pide vacías y tú decides si las llenas:

- `CULQI_LLAVE_PUBLICA` y `CULQI_LLAVE_SECRETA`. **Si las dejas vacías**, el
  sistema usa la pasarela simulada, que permite demostrar el cobro aprobado y el
  rechazado sin mover dinero. Es lo recomendable para la sustentación.

El correo sale configurado como `log`: los avisos quedan registrados y no se
envían. Si quieres que lleguen de verdad, cambia `MAIL_MAILER` a `smtp` y agrega
las variables del servidor de correo que uses.

## Consideraciones del plan gratuito

- El servicio **se duerme tras quince minutos sin tráfico** y tarda unos treinta
  segundos en despertar con la siguiente visita. Antes de la sustentación,
  ábrelo una vez para que esté despierto.
- La base de datos gratuita de Render **caduca a los treinta días**. Si la
  sustentación es posterior, crea una nueva y vuelve a aplicar el blueprint.

## Alta en buscadores

Una vez publicado el dominio:

1. Registra el sitio en Google Search Console y en Bing Webmaster Tools.
2. Envía en ambos la dirección `https://<tu-dominio>/sitemap.xml`.
3. Comprueba que `https://<tu-dominio>/robots.txt` responde: permite indexar lo
   público y bloquea la plataforma del cliente.
