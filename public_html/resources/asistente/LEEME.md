# Asistente Pelusa

Todo lo que el asistente sabe decir vive en esta carpeta. No hace falta tocar
código para cambiar una respuesta, agregar una palabra o enseñarle una forma
nueva de preguntar.

| Archivo | Qué contiene |
| --- | --- |
| `guion.php` | El menú del chat y las respuestas fijas que la empresa autoriza. |
| `intenciones.php` | Cada intención con sus frases de ejemplo y sus palabras clave. |
| `sinonimos.php` | Cómo llama la gente a lo mismo: «michi» es gato, «croquetas» es alimento. |
| `vacias.php` | Palabras sin significado propio, que se descartan. |
| `senales.php` | Lo que separa una pregunta de precio de una de disponibilidad. |
| `corpus.php` | Preguntas reales con la intención que deberían reconocer. Es la nota del asistente. |

El motor está en [`app/Services/Asistente/`](../../app/Services/Asistente/).

## Cómo se entiende una pregunta

No hay inteligencia artificial externa ni servicios pagados. El motor compara
**raíces de palabras**, no texto literal, usando el algoritmo Snowball para
español (`wamania/php-stemmer`). Por eso «queda», «quedan» y «quedar» son la
misma palabra para el asistente.

1. **Normalizar** — minúsculas, sin tildes, sin puntuación.
2. **Corregir el tecleo** — «alimeto» se acerca a «alimento» y se corrige, pero
   solo si la distancia es corta: preferimos no entender antes que inventar.
3. **Reducir a raíces y aplicar sinónimos** — «michis» termina en la raíz de gato.
4. **Puntuar cada intención** — parecido con sus frases de ejemplo, más sus
   palabras clave, más una señal fuerte si la pregunta nombra un producto del
   catálogo.
5. **Decidir** — gana la de más puntaje si pasa el umbral. Por debajo del umbral
   el asistente dice que no entendió.

## Cómo enseñarle algo nuevo

**Una pregunta que no entendió.** Agrégala a `corpus.php` con la intención que
debería haber reconocido y ejecuta la prueba: va a fallar. Después agrega la
frase o la palabra que falte en `intenciones.php` hasta que pase. Ese ciclo es
el entrenamiento, y deja constancia de que el arreglo funciona.

```bash
php artisan test tests/Feature/Asistente/
```

**Una forma distinta de llamar a algo.** Va en `sinonimos.php`, bajo la palabra
que usa el catálogo. No hace falta poner los plurales: se comparan raíces.

**Una respuesta nueva.** Va en `guion.php` si es un tema del menú, o en la
intención correspondiente de `intenciones.php`.

## Lo que el asistente no debe hacer

- **No inventa.** Si no entiende, lo dice y ofrece el menú.
- **No sale de la cuenta del cliente.** Las intenciones con `dinamica` consultan
  la base de datos filtrando siempre por el usuario conectado (RN-01 y RN-04).
  `RespuestaTest` verifica que no se filtren datos de otro cliente.
- **No informa un stock que no sea el real.** Lee la tabla de productos, y avisa
  cuando quedan pocas unidades usando el mismo semáforo del inventario (RN-08).
