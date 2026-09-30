<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Asistente Pelusa
|--------------------------------------------------------------------------
| Fuente unica de verdad del asistente. El contenido vive en archivos
| separados dentro de resources/asistente/ para poder tocarlo sin entrar al
| codigo: el guion del menu, las intenciones con sus frases de ejemplo, los
| sinonimos, las palabras vacias y las senales que desempatan.
|
| El motor esta en app/Services/Asistente/ y la medicion de que tan bien
| entiende, en tests/Feature/Asistente/.
*/

return [

    /* Menu del chat y respuestas autorizadas por la empresa. */
    'guia' => require resource_path('asistente/guion.php'),

    /* Intenciones con sus frases de ejemplo y sus palabras clave. */
    'intenciones' => require resource_path('asistente/intenciones.php'),

    /* Formas en que el cliente nombra lo mismo. */
    'sinonimos' => require resource_path('asistente/sinonimos.php'),

    /* Palabras sin significado propio. */
    'vacias' => require resource_path('asistente/vacias.php'),

    /* Desempate entre preguntar por precio y preguntar por disponibilidad. */
    'senales' => require resource_path('asistente/senales.php'),

    /* Saludos, agradecimientos y despedidas. */
    'cortesia' => require resource_path('asistente/cortesia.php'),

    /*
    | Puntaje minimo para dar una respuesta. Por debajo, el asistente dice
    | que no entendio en lugar de adivinar: prefiere quedarse corto antes
    | que responder cualquier cosa.
    */
    'umbral' => 1.0,
];
