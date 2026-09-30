<?php

declare(strict_types=1);

/*
| Menu del chat: cada tema con su respuesta y sus atajos. Es el guion que
| la empresa autoriza; el asistente no dice nada que no salga de aqui.
*/

return [
    'inicio' => [
        'texto' => '¡Hola! Soy Pelusa 🐾, el asistente de VetPet Surco. Puedes preguntarme escribiendo o elegir un tema.',
        'opciones' => [
            0 => [
                'texto' => '🛒 ¿Cómo compro?',
                'destino' => 'tema:comprar',
            ],
            1 => [
                'texto' => '🚚 Envíos y entregas',
                'destino' => 'tema:envios',
            ],
            2 => [
                'texto' => '📦 Mi suscripción',
                'destino' => 'tema:mi_suscripcion',
            ],
            3 => [
                'texto' => '📦 Mis pedidos',
                'destino' => 'tema:mis_pedidos',
            ],
            4 => [
                'texto' => '💉 Vacunas y recordatorios',
                'destino' => 'tema:salud',
            ],
            5 => [
                'texto' => '🕒 Horarios y contacto',
                'destino' => 'tema:horarios',
            ],
            6 => [
                'texto' => '👤 Hablar con una persona',
                'destino' => 'tema:persona',
            ],
        ],
    ],
    'comprar' => [
        'texto' => 'Comprar es rapidísimo: 1) agrega productos desde el Catálogo, 2) revisa tu Carrito, 3) confirma el pedido y paga con tarjeta. El stock que ves es el real y los precios incluyen IGV (18%).',
        'opciones' => [
            0 => [
                'texto' => 'Ir al catálogo',
                'destino' => 'salto:catalogo',
            ],
            1 => [
                'texto' => '🔙 Volver al menú',
                'destino' => 'tema:inicio',
            ],
        ],
    ],
    'envios' => [
        'texto' => 'Hacemos despacho el mismo día en Santiago de Surco. Los pedidos confirmados antes de las 6 p.m. salen ese día; los posteriores, a la mañana siguiente.',
        'opciones' => [
            0 => [
                'texto' => '📦 Ver mis pedidos',
                'destino' => 'tema:mis_pedidos',
            ],
            1 => [
                'texto' => '🔙 Volver al menú',
                'destino' => 'tema:inicio',
            ],
        ],
    ],
    'salud' => [
        'texto' => 'Cada mascota tiene su historia clínica digital: vacunas, desparasitaciones y controles. El sistema te avisa 15 días antes de cada próxima fecha, así nunca se te pasa una vacuna.',
        'opciones' => [
            0 => [
                'texto' => '📅 Reservar una cita',
                'destino' => 'salto:citas',
            ],
            1 => [
                'texto' => '🐾 Mis mascotas',
                'destino' => 'tema:mis_mascotas',
            ],
            2 => [
                'texto' => '🔙 Volver al menú',
                'destino' => 'tema:inicio',
            ],
        ],
    ],
    'horarios' => [
        'texto' => 'Estamos en Av. Velasco Astete 1245, Surco. Horario: lunes a viernes de 9:00 a 20:00 y sábados de 9:00 a 14:00. Teléfonos: (01) 445-8820 / 987 654 321.',
        'opciones' => [
            0 => [
                'texto' => '✉️ Escribir al equipo',
                'destino' => 'salto:contacto',
            ],
            1 => [
                'texto' => '🔙 Volver al menú',
                'destino' => 'tema:inicio',
            ],
        ],
    ],
    'persona' => [
        'texto' => 'Claro, con gusto. Llámanos al 987 654 321 o escríbenos a contacto@vetpetsurco.pe y te atiende el equipo del local en horario de atención.',
        'opciones' => [
            0 => [
                'texto' => '✉️ Formulario de contacto',
                'destino' => 'salto:contacto',
            ],
            1 => [
                'texto' => '🔙 Volver al menú',
                'destino' => 'tema:inicio',
            ],
        ],
    ],
    'pago_info' => [
        'texto' => 'Pagas con tarjeta de crédito o débito al confirmar el pedido. El número de tarjeta no pasa por nuestros servidores: lo tokeniza el checkout seguro y solo queda registrado el resultado del cobro.',
        'opciones' => [
            0 => [
                'texto' => '🛒 Ir al catálogo',
                'destino' => 'salto:catalogo',
            ],
            1 => [
                'texto' => '🔙 Volver al menú',
                'destino' => 'tema:inicio',
            ],
        ],
    ],
    'suscripcion_info' => [
        'texto' => 'Con la suscripción mensual eliges el alimento y la frecuencia, y te llega solo cada mes. Puedes pausarla o cancelarla cuando quieras desde "Mis mascotas", sin llamadas ni trámites.',
        'opciones' => [
            0 => [
                'texto' => '🐾 Ir a mis mascotas',
                'destino' => 'salto:mascotas',
            ],
            1 => [
                'texto' => '🔙 Volver al menú',
                'destino' => 'tema:inicio',
            ],
        ],
    ],
];
