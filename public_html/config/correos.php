<?php

/*
|--------------------------------------------------------------------------
| Plantillas de correo por tipo de mensaje
|--------------------------------------------------------------------------
| Fuente unica del diseno de los correos: cada tipo define su etiqueta y
| el color de su cabecera. La vista emails/plantilla.blade.php aplica estas
| reglas; agregar un tipo de correo nuevo es agregar una entrada aqui.
*/

return [

    /* Buzon de la empresa: aqui llegan los mensajes de los clientes. */
    'buzon' => 'contacto@vetpetsurco.pe',

    /*
    | Colas del buzón de entrada. El asunto de todo mensaje de cliente
    | empieza con su etiqueta, asi el equipo filtra la bandeja de un vistazo:
    | [PEDIDO] es distinto de [CONSULTA VET], y ambos de un saliente.
    */
    'colas_contacto' => [
        'consulta' => '[CONSULTA VET]',
        'vacuna' => '[SALUD]',
        'grooming' => '[GROOMING]',
        'suscripcion' => '[SUSCRIPCION]',
        'pedido' => '[PEDIDO]',
    ],

    'tipos' => [

        'bienvenida' => [
            'etiqueta' => 'Bienvenida',
            'color' => '#2c7a6b',
        ],
        'pedido' => [
            'etiqueta' => 'Tu pedido',
            'color' => '#14524a',
        ],
        'factura' => [
            'etiqueta' => 'Comprobante de pago',
            'color' => '#14524a',
        ],
        'cita' => [
            'etiqueta' => 'Tu cita',
            'color' => '#2c7a6b',
        ],
        'suscripcion' => [
            'etiqueta' => 'Tu suscripción',
            'color' => '#c9822a',
        ],
        'despacho' => [
            'etiqueta' => 'Tu despacho mensual',
            'color' => '#2c7a6b',
        ],
        'recordatorio' => [
            'etiqueta' => 'Recordatorio de salud',
            'color' => '#c9822a',
        ],
        'contacto' => [
            'etiqueta' => 'Mensaje de cliente',
            'color' => '#5d6b66',
        ],
    ],
];
