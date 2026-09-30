<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Cortesia
|--------------------------------------------------------------------------
| Saludar no es preguntar, pero tampoco es no entender.
|
| Estas palabras se revisan antes de descartar las vacias, porque "hola" y
| "gracias" justamente son vacias: si se filtran primero, el mensaje se queda
| sin nada que clasificar y el asistente contesta que no entendio un saludo.
|
| Solo deciden cuando el mensaje no trae otra intencion: si alguien escribe
| "hola, queda arena", manda la pregunta.
*/

return [

    'inicio' => [
        'hola', 'holi', 'holaa', 'ola', 'buenas', 'buenos', 'buen', 'dias', 'tardes',
        'noches', 'hey', 'saludos', 'que tal', 'quetal', 'alo', 'hello', 'hi',
    ],

    'agradecimiento' => [
        'gracias', 'graciass', 'agradezco', 'agradecido', 'agradecida', 'vale',
        'genial', 'perfecto', 'excelente', 'ok', 'oka', 'okay', 'listo',
    ],

    'despedida' => [
        'chau', 'chao', 'adios', 'bye', 'hasta luego', 'nos vemos', 'me voy',
    ],
];
