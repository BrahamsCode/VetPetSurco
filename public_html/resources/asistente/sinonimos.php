<?php

declare(strict_types=1);

/*
| Sinonimos del cliente peruano.
|
| La clave es la palabra que el catalogo usa; la lista son las formas en que
| la gente la escribe de verdad. Se comparan por raiz, asi que no hace falta
| poner los plurales: "michis" ya cae en "michi".
*/

return [

    /* Animales */
    'gato' => ['michi', 'felino', 'gatito', 'gatita', 'minino'],
    'perro' => ['can', 'firulais', 'perrito', 'perrita', 'cachorrito'],
    'cachorro' => ['bebe', 'puppy'],

    /* Productos */
    'alimento' => ['comida', 'croqueta', 'croquetas', 'concentrado', 'balanceado', 'pienso', 'racion'],
    'arena' => ['piedra', 'piedritas', 'sanitaria'],
    'antipulgas' => ['pulga', 'pulgas', 'garrapata', 'garrapatas', 'pipeta'],
    'juguete' => ['pelota', 'mordedor', 'hueso'],
    'correa' => ['cadena', 'ronzal'],
    'cama' => ['colchon', 'colchoneta', 'camita'],
    'vitaminas' => ['vitamina', 'suplemento', 'suplementos'],

    /* Verbos y giros de por aca */
    'quedar' => ['sobrar', 'restar'],
    'comprar' => ['llevar', 'adquirir'],
    'precio' => ['costo', 'tarifa'],
    'disponible' => ['habilitado', 'hay'],
    'cita' => ['turno', 'consulta'],
    'pedido' => ['orden'],
];
