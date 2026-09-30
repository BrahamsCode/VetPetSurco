<?php

declare(strict_types=1);

/*
| Intenciones del motor.
|
| "frases" son los ejemplos con los que se entrena: no hace falta que el
| cliente escriba igual, se comparan las raices de las palabras. "palabras"
| son las que por si solas ya delatan la intencion.
|
| Las intenciones con "dinamica" consultan la base de datos y responden con
| los datos del propio usuario autenticado (RN-01 y RN-04).
*/

return [
    'mis_pedidos' => [
        'dinamica' => 'pedidos',
        'frases' => [
            0 => 'donde esta mi pedido',
            1 => 'estado de mi pedido',
            2 => 'mis pedidos',
            3 => 'cuando llega mi pedido',
            4 => 'ya llego mi pedido',
            5 => 'en que estado va mi pedido',
            6 => 'mis ordenes',
        ],
        'palabras' => [
            0 => 'pedido',
            1 => 'pedidos',
            2 => 'orden',
            3 => 'ordenes',
            4 => 'llega',
            5 => 'llego',
            6 => 'llegada',
            7 => 'estado',
            8 => 'despachado',
            9 => 'enviado',
        ],
    ],
    'mi_suscripcion' => [
        'dinamica' => 'suscripcion',
        'frases' => [
            0 => 'mi suscripcion',
            1 => 'proximo despacho',
            2 => 'cuando me llega el alimento',
            3 => 'cuando me toca el alimento',
            4 => 'cuando me toca el despacho',
            5 => 'cuando llega mi plan mensual',
        ],
        'palabras' => [
            0 => 'suscripcion',
            1 => 'suscripciones',
            2 => 'despacho',
            3 => 'despachos',
            4 => 'plan',
            5 => 'planes',
            6 => 'frecuencia',
            7 => 'renovacion',
            8 => 'mensualidad',
        ],
    ],
    'mis_citas' => [
        'dinamica' => 'citas',
        'frases' => [
            0 => 'mis citas',
            1 => 'mi cita',
            2 => 'cita reservada',
            3 => 'reservar una cita',
        ],
        'palabras' => [
            0 => 'cita',
            1 => 'citas',
            2 => 'reserva',
            3 => 'reservada',
            4 => 'reservar',
            5 => 'veterinario',
        ],
    ],
    'mis_mascotas' => [
        'dinamica' => 'mascotas',
        'frases' => [
            0 => 'mis mascotas',
            1 => 'mi mascota',
            2 => 'ficha de mi mascota',
        ],
        'palabras' => [
            0 => 'mascota',
            1 => 'mascotas',
            2 => 'ficha',
            3 => 'registrada',
            4 => 'registradas',
        ],
    ],
    'carrito_estado' => [
        'dinamica' => 'carrito',
        'frases' => [
            0 => 'mi carrito',
            1 => 'que tengo en el carrito',
            2 => 'en el carrito',
        ],
        'palabras' => [
            0 => 'carrito',
        ],
    ],
    'stock_producto' => [
        'dinamica' => 'stock',
        'frases' => [
            0 => 'hay stock',
            1 => 'tienen stock',
            2 => 'queda stock',
            3 => 'esta disponible',
            4 => 'sigue disponible',
            5 => 'cuantas unidades quedan',
            6 => 'les queda',
            7 => 'tienen en stock',
            8 => 'hay disponible',
            9 => 'se agoto',
        ],
        'palabras' => [
            0 => 'stock',
            1 => 'disponible',
            2 => 'disponibilidad',
            3 => 'quedan',
            4 => 'queda',
            5 => 'quedar',
            6 => 'agotado',
            7 => 'agotarse',
            8 => 'ultimas',
            9 => 'unidades',
            10 => 'existencia',
            11 => 'existencias',
            12 => 'tienen',
            13 => 'tienes',
            14 => 'venden',
            15 => 'consigo',
        ],
    ],
    'precio_producto' => [
        'dinamica' => 'precio',
        'frases' => [
            0 => 'cuanto cuesta',
            1 => 'cuanto vale',
            2 => 'cuanto esta',
            3 => 'precio de',
            4 => 'a cuanto sale',
            5 => 'que precio tiene',
        ],
        'palabras' => [
            0 => 'precio',
            1 => 'precios',
            2 => 'cuesta',
            3 => 'costar',
            4 => 'vale',
            5 => 'valer',
            6 => 'valor',
            7 => 'cobran',
            8 => 'sale',
        ],
    ],
    'comprar' => [
        'respuesta' => 'Comprar es rapidísimo: 1) agrega productos desde el Catálogo, 2) revisa tu Carrito, 3) confirma el pedido y paga con tarjeta. El stock que ves es el real y los precios incluyen IGV (18%).',
        'sugerencias' => [
            0 => [
                'texto' => 'Ir al catálogo',
                'destino' => 'salto:catalogo',
            ],
            1 => [
                'texto' => '¿Cómo pago?',
                'destino' => 'tema:pago_info',
            ],
        ],
        'frases' => [
            0 => 'como compro',
            1 => 'como comprar',
            2 => 'quiero comprar',
            3 => 'agregar al carrito',
        ],
        'palabras' => [
            0 => 'comprar',
            1 => 'compro',
            2 => 'comprar',
            3 => 'adquirir',
            4 => 'checkout',
        ],
    ],
    'envios' => [
        'respuesta' => 'Hacemos despacho el mismo día en Santiago de Surco. Los pedidos confirmados antes de las 6 p.m. salen ese día; los posteriores, a la mañana siguiente.',
        'sugerencias' => [
            0 => [
                'texto' => '📦 Ver mis pedidos',
                'destino' => 'tema:mis_pedidos',
            ],
            1 => [
                'texto' => '🔙 Menú',
                'destino' => 'tema:inicio',
            ],
        ],
        'frases' => [
            0 => 'cuanto tarda el envio',
            1 => 'envio a domicilio',
            2 => 'hacen envios',
            3 => 'entrega a domicilio',
        ],
        'palabras' => [
            0 => 'envio',
            1 => 'envios',
            2 => 'entrega',
            3 => 'entregan',
            4 => 'domicilio',
            5 => 'tarda',
            6 => 'llevan',
        ],
    ],
    'pago_info' => [
        'respuesta' => 'Pagas con tarjeta de crédito o débito al confirmar el pedido. El número de tarjeta no pasa por nuestros servidores: lo tokeniza el checkout seguro y solo queda registrado el resultado del cobro.',
        'sugerencias' => [
            0 => [
                'texto' => '🛒 Ir al catálogo',
                'destino' => 'salto:catalogo',
            ],
            1 => [
                'texto' => '🔙 Menú',
                'destino' => 'tema:inicio',
            ],
        ],
        'frases' => [
            0 => 'como pago',
            1 => 'metodos de pago',
            2 => 'puedo pagar',
            3 => 'con tarjeta',
            4 => 'aceptan visa',
            5 => 'aceptan tarjeta',
        ],
        'palabras' => [
            0 => 'pago',
            1 => 'pagar',
            2 => 'tarjeta',
            3 => 'credito',
            4 => 'debito',
            5 => 'visa',
            6 => 'mastercard',
            7 => 'culqi',
            8 => 'yape',
            9 => 'plin',
            10 => 'aceptan',
        ],
    ],
    'salud' => [
        'respuesta' => 'Cada mascota tiene su historia clínica digital: vacunas, desparasitaciones y controles. El sistema te avisa 15 días antes de cada próxima fecha, así nunca se te pasa una vacuna.',
        'sugerencias' => [
            0 => [
                'texto' => '📅 Reservar una cita',
                'destino' => 'salto:citas',
            ],
            1 => [
                'texto' => '🐾 Mis mascotas',
                'destino' => 'tema:mis_mascotas',
            ],
        ],
        'frases' => [
            0 => 'historia clinica',
            1 => 'recordatorio de vacunas',
            2 => 'proxima vacuna',
            3 => 'desparasitacion',
        ],
        'palabras' => [
            0 => 'vacuna',
            1 => 'vacunas',
            2 => 'desparasitacion',
            3 => 'historia',
            4 => 'clinica',
            5 => 'recordatorio',
            6 => 'recordatorios',
            7 => 'vacunar',
            8 => 'enfermo',
            9 => 'sintoma',
            10 => 'sintomas',
        ],
    ],
    'horarios' => [
        'respuesta' => 'Estamos en Av. Velasco Astete 1245, Surco. Horario: lunes a viernes de 9:00 a 20:00 y sábados de 9:00 a 14:00. Teléfonos: (01) 445-8820 / 987 654 321.',
        'sugerencias' => [
            0 => [
                'texto' => '✉️ Escribir al equipo',
                'destino' => 'salto:contacto',
            ],
            1 => [
                'texto' => '🔙 Menú',
                'destino' => 'tema:inicio',
            ],
        ],
        'frases' => [
            0 => 'a que hora abren',
            1 => 'donde estan',
            2 => 'donde queda el local',
            3 => 'horario de atencion',
        ],
        'palabras' => [
            0 => 'horario',
            1 => 'horarios',
            2 => 'abren',
            3 => 'cierran',
            4 => 'direccion',
            5 => 'ubicacion',
            6 => 'contacto',
            7 => 'telefono',
            8 => 'telefonos',
            9 => 'correo',
            10 => 'local',
        ],
    ],
    'persona' => [
        'respuesta' => 'Claro, con gusto. Llámanos al 987 654 321 o escríbenos a contacto@vetpetsurco.pe y te atiende el equipo del local en horario de atención.',
        'sugerencias' => [
            0 => [
                'texto' => '✉️ Formulario de contacto',
                'destino' => 'salto:contacto',
            ],
            1 => [
                'texto' => '🔙 Menú',
                'destino' => 'tema:inicio',
            ],
        ],
        'frases' => [
            0 => 'hablar con una persona',
            1 => 'hablar con alguien',
            2 => 'quiero un humano',
            3 => 'atencion al cliente',
        ],
        'palabras' => [
            0 => 'persona',
            1 => 'humano',
            2 => 'humana',
            3 => 'asesor',
            4 => 'asesora',
            5 => 'agente',
            6 => 'llamar',
            7 => 'representante',
        ],
    ],
    'suscripcion_info' => [
        'respuesta' => 'Con la suscripción mensual eliges el alimento y la frecuencia, y te llega solo cada mes. Puedes pausarla o cancelarla cuando quieras desde "Mis mascotas", sin llamadas ni trámites.',
        'sugerencias' => [
            0 => [
                'texto' => '🐾 Ir a mis mascotas',
                'destino' => 'salto:mascotas',
            ],
            1 => [
                'texto' => '🔙 Menú',
                'destino' => 'tema:inicio',
            ],
        ],
        'frases' => [
            0 => 'como funciona la suscripcion',
            1 => 'que es la suscripcion',
            2 => 'quiero una suscripcion',
            3 => 'contratar el plan',
        ],
        'palabras' => [
            0 => 'planes',
            1 => 'contratar',
            2 => 'pausar',
            3 => 'cancelarla',
            4 => 'mensualidad',
        ],
    ],
];
