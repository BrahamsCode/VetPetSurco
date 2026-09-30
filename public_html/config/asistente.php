<?php

/*
|--------------------------------------------------------------------------
| Fuente unica de verdad del asistente Pelusa
|--------------------------------------------------------------------------
| Guia del menu, intenciones y palabras clave del motor de respuestas.
| Tanto el chat como el backend leen de aqui: si cambia un horario, un
| telefono o una regla, se cambia en un solo lugar.
*/

return [

    /* Menu del chat: temas con su respuesta y sus atajos. */
    'guia' => [

        'inicio' => [
            'texto' => '¡Hola! Soy Pelusa 🐾, el asistente de VetPet Surco. Puedes preguntarme escribiendo o elegir un tema.',
            'opciones' => [
                ['texto' => '🛒 ¿Cómo compro?', 'destino' => 'tema:comprar'],
                ['texto' => '🚚 Envíos y entregas', 'destino' => 'tema:envios'],
                ['texto' => '📦 Mi suscripción', 'destino' => 'tema:mi_suscripcion'],
                ['texto' => '📦 Mis pedidos', 'destino' => 'tema:mis_pedidos'],
                ['texto' => '💉 Vacunas y recordatorios', 'destino' => 'tema:salud'],
                ['texto' => '🕒 Horarios y contacto', 'destino' => 'tema:horarios'],
                ['texto' => '👤 Hablar con una persona', 'destino' => 'tema:persona'],
            ],
        ],

        'comprar' => [
            'texto' => 'Comprar es rapidísimo: 1) agrega productos desde el Catálogo, 2) revisa tu Carrito, 3) confirma el pedido y paga con tarjeta. El stock que ves es el real y los precios incluyen IGV (18%).',
            'opciones' => [
                ['texto' => 'Ir al catálogo', 'destino' => 'salto:catalogo'],
                ['texto' => '🔙 Volver al menú', 'destino' => 'tema:inicio'],
            ],
        ],

        'envios' => [
            'texto' => 'Hacemos despacho el mismo día en Santiago de Surco. Los pedidos confirmados antes de las 6 p.m. salen ese día; los posteriores, a la mañana siguiente.',
            'opciones' => [
                ['texto' => '📦 Ver mis pedidos', 'destino' => 'tema:mis_pedidos'],
                ['texto' => '🔙 Volver al menú', 'destino' => 'tema:inicio'],
            ],
        ],

        'salud' => [
            'texto' => 'Cada mascota tiene su historia clínica digital: vacunas, desparasitaciones y controles. El sistema te avisa 15 días antes de cada próxima fecha, así nunca se te pasa una vacuna.',
            'opciones' => [
                ['texto' => '📅 Reservar una cita', 'destino' => 'salto:citas'],
                ['texto' => '🐾 Mis mascotas', 'destino' => 'tema:mis_mascotas'],
                ['texto' => '🔙 Volver al menú', 'destino' => 'tema:inicio'],
            ],
        ],

        'horarios' => [
            'texto' => 'Estamos en Av. Velasco Astete 1245, Surco. Horario: lunes a viernes de 9:00 a 20:00 y sábados de 9:00 a 14:00. Teléfonos: (01) 445-8820 / 987 654 321.',
            'opciones' => [
                ['texto' => '✉️ Escribir al equipo', 'destino' => 'salto:contacto'],
                ['texto' => '🔙 Volver al menú', 'destino' => 'tema:inicio'],
            ],
        ],

        'persona' => [
            'texto' => 'Claro, con gusto. Llámanos al 987 654 321 o escríbenos a contacto@vetpetsurco.pe y te atiende el equipo del local en horario de atención.',
            'opciones' => [
                ['texto' => '✉️ Formulario de contacto', 'destino' => 'salto:contacto'],
                ['texto' => '🔙 Volver al menú', 'destino' => 'tema:inicio'],
            ],
        ],

        'pago_info' => [
            'texto' => 'Pagas con tarjeta de crédito o débito al confirmar el pedido. El número de tarjeta no pasa por nuestros servidores: lo tokeniza el checkout seguro y solo queda registrado el resultado del cobro.',
            'opciones' => [
                ['texto' => '🛒 Ir al catálogo', 'destino' => 'salto:catalogo'],
                ['texto' => '🔙 Volver al menú', 'destino' => 'tema:inicio'],
            ],
        ],

        'suscripcion_info' => [
            'texto' => 'Con la suscripción mensual eliges el alimento y la frecuencia, y te llega solo cada mes. Puedes pausarla o cancelarla cuando quieras desde "Mis mascotas", sin llamadas ni trámites.',
            'opciones' => [
                ['texto' => '🐾 Ir a mis mascotas', 'destino' => 'salto:mascotas'],
                ['texto' => '🔙 Volver al menú', 'destino' => 'tema:inicio'],
            ],
        ],
    ],

    /*
    | Intenciones del motor. Las dinamicas consultan la base de datos y
    | responden con los datos del propio usuario autenticado (RN-01 y RN-04).
    | 'frases' pesan doble; 'palabras' suman una unidad cada una.
    */
    'intenciones' => [

        'mis_pedidos' => [
            'dinamica' => 'pedidos',
            'frases' => ['donde esta mi pedido', 'estado de mi pedido', 'mis pedidos', 'mis compras', 'cuando llega mi pedido', 'ya llego mi pedido'],
            'palabras' => ['pedido', 'pedidos', 'compra', 'compras', 'compre', 'comprado', 'llega', 'llego', 'llegada', 'estado'],
        ],
        'mi_suscripcion' => [
            'dinamica' => 'suscripcion',
            'frases' => ['mi suscripcion', 'proximo despacho', 'cuando me llega el alimento', 'cuando me toca el alimento'],
            'palabras' => ['suscripcion', 'suscripciones', 'despacho', 'despachos', 'alimento', 'plan', 'frecuencia', 'renovacion'],
        ],
        'mis_citas' => [
            'dinamica' => 'citas',
            'frases' => ['mis citas', 'mi cita', 'cita reservada', 'reservar una cita'],
            'palabras' => ['cita', 'citas', 'reserva', 'reservada', 'reservar', 'veterinario'],
        ],
        'mis_mascotas' => [
            'dinamica' => 'mascotas',
            'frases' => ['mis mascotas', 'mi mascota', 'ficha de mi mascota'],
            'palabras' => ['mascota', 'mascotas', 'ficha', 'registrada', 'registradas'],
        ],
        'carrito_estado' => [
            'dinamica' => 'carrito',
            'frases' => ['mi carrito', 'que tengo en el carrito', 'en el carrito'],
            'palabras' => ['carrito'],
        ],
        'stock_producto' => [
            'dinamica' => 'stock',
            'frases' => ['hay stock', 'tienen stock', 'queda stock', 'esta disponible', 'sigue disponible'],
            'palabras' => ['stock', 'disponible', 'disponibilidad', 'quedan', 'quedan', 'agotado', 'ultimas', 'unidades'],
        ],
        'precio_producto' => [
            'dinamica' => 'precio',
            'frases' => ['cuanto cuesta', 'cuanto vale', 'cuanto esta', 'precio de'],
            'palabras' => ['precio', 'precios', 'cuesta', 'vale', 'cuesta', 'valor'],
        ],

        'comprar' => [
            'respuesta' => 'Comprar es rapidísimo: 1) agrega productos desde el Catálogo, 2) revisa tu Carrito, 3) confirma el pedido y paga con tarjeta. El stock que ves es el real y los precios incluyen IGV (18%).',
            'sugerencias' => [['texto' => 'Ir al catálogo', 'destino' => 'salto:catalogo'], ['texto' => '¿Cómo pago?', 'destino' => 'tema:pago_info']],
            'frases' => ['como compro', 'como comprar', 'quiero comprar', 'agregar al carrito'],
            'palabras' => ['comprar', 'compro', 'comprar', 'adquirir', 'checkout'],
        ],
        'envios' => [
            'respuesta' => 'Hacemos despacho el mismo día en Santiago de Surco. Los pedidos confirmados antes de las 6 p.m. salen ese día; los posteriores, a la mañana siguiente.',
            'sugerencias' => [['texto' => '📦 Ver mis pedidos', 'destino' => 'tema:mis_pedidos'], ['texto' => '🔙 Menú', 'destino' => 'tema:inicio']],
            'frases' => ['cuanto tarda el envio', 'envio a domicilio', 'hacen envios', 'entrega a domicilio'],
            'palabras' => ['envio', 'envios', 'entrega', 'entregan', 'domicilio', 'tarda', 'llevan'],
        ],
        'pago_info' => [
            'respuesta' => 'Pagas con tarjeta de crédito o débito al confirmar el pedido. El número de tarjeta no pasa por nuestros servidores: lo tokeniza el checkout seguro y solo queda registrado el resultado del cobro.',
            'sugerencias' => [['texto' => '🛒 Ir al catálogo', 'destino' => 'salto:catalogo'], ['texto' => '🔙 Menú', 'destino' => 'tema:inicio']],
            'frases' => ['como pago', 'metodos de pago', 'puedo pagar', 'con tarjeta'],
            'palabras' => ['pago', 'pagar', 'tarjeta', 'credito', 'debito', 'visa', 'mastercard', 'culqi', 'yape', 'plin'],
        ],
        'salud' => [
            'respuesta' => 'Cada mascota tiene su historia clínica digital: vacunas, desparasitaciones y controles. El sistema te avisa 15 días antes de cada próxima fecha, así nunca se te pasa una vacuna.',
            'sugerencias' => [['texto' => '📅 Reservar una cita', 'destino' => 'salto:citas'], ['texto' => '🐾 Mis mascotas', 'destino' => 'tema:mis_mascotas']],
            'frases' => ['historia clinica', 'recordatorio de vacunas', 'proxima vacuna', 'desparasitacion'],
            'palabras' => ['vacuna', 'vacunas', 'desparasitacion', 'historia', 'clinica', 'recordatorio', 'recordatorios', 'vacunar', 'enfermo', 'sintoma', 'sintomas'],
        ],
        'horarios' => [
            'respuesta' => 'Estamos en Av. Velasco Astete 1245, Surco. Horario: lunes a viernes de 9:00 a 20:00 y sábados de 9:00 a 14:00. Teléfonos: (01) 445-8820 / 987 654 321.',
            'sugerencias' => [['texto' => '✉️ Escribir al equipo', 'destino' => 'salto:contacto'], ['texto' => '🔙 Menú', 'destino' => 'tema:inicio']],
            'frases' => ['a que hora abren', 'donde estan', 'donde queda el local', 'horario de atencion'],
            'palabras' => ['horario', 'horarios', 'abren', 'cierran', 'direccion', 'ubicacion', 'contacto', 'telefono', 'telefonos', 'correo', 'local'],
        ],
        'persona' => [
            'respuesta' => 'Claro, con gusto. Llámanos al 987 654 321 o escríbenos a contacto@vetpetsurco.pe y te atiende el equipo del local en horario de atención.',
            'sugerencias' => [['texto' => '✉️ Formulario de contacto', 'destino' => 'salto:contacto'], ['texto' => '🔙 Menú', 'destino' => 'tema:inicio']],
            'frases' => ['hablar con una persona', 'hablar con alguien', 'quiero un humano', 'atencion al cliente'],
            'palabras' => ['persona', 'humano', 'humana', 'asesor', 'asesora', 'agente', 'llamar', 'representante'],
        ],
        'suscripcion_info' => [
            'respuesta' => 'Con la suscripción mensual eliges el alimento y la frecuencia, y te llega solo cada mes. Puedes pausarla o cancelarla cuando quieras desde "Mis mascotas", sin llamadas ni trámites.',
            'sugerencias' => [['texto' => '🐾 Ir a mis mascotas', 'destino' => 'salto:mascotas'], ['texto' => '🔙 Menú', 'destino' => 'tema:inicio']],
            'frases' => ['como funciona la suscripcion', 'que es la suscripcion', 'quiero una suscripcion', 'contratar el plan'],
            'palabras' => ['planes', 'contratar', 'pausar', 'cancelarla', 'mensualidad'],
        ],
    ],

    /* Palabras que no aportan significado al buscar la intencion. */
    'vacias' => [
        'hola', 'buenas', 'buenos', 'dias', 'tardes', 'noches', 'que', 'como', 'cuando', 'donde',
        'cual', 'quien', 'necesito', 'quiero', 'puedo', 'tengo', 'por', 'favor', 'gracias', 'para',
        'una', 'uno', 'un', 'el', 'la', 'los', 'las', 'del', 'de', 'en', 'y', 'o', 'u', 'mi', 'mis',
        'me', 'te', 'al', 'lo', 'sus', 'tu', 'tus', 'sobre', 'porfa', 'hacer', 'esta', 'estan', 'hay',
        'tienen', 'tienes', 'ser', 'es', 'son', 'su', 'con', 'ya', 'ahi', 'ver', 'dime', 'diga', 'saber',
    ],

    /* Palabras que se ignoran al extraer el nombre de un producto. */
    'ruido_producto' => [
        'stock', 'precio', 'precios', 'cuesta', 'vale', 'disponible', 'disponibilidad', 'quedan',
        'queda', 'agotado', 'unidades', 'producto', 'productos', 'tienen', 'hay', 'buscar', 'busco',
    ],
];
