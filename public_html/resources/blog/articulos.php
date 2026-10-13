<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Blog corporativo de VetPet Surco
|--------------------------------------------------------------------------
| El caso del negocio, publicado y estructurado. El contenido vive aqui y no
| en la base de datos: son articulos que cambian poco y que el equipo edita
| por archivo, igual que el guion del asistente.
|
| Cada articulo: ruta amigable, titulo, fecha, resumen y cuerpo en parrafos.
| El cuerpo acepta parrafos de texto, subtitulos ('h'), listas ('lista') y
| citas ('cita').
*/

return [

    'por-que-vender-en-linea' => [
        'titulo' => 'Por qué una veterinaria de barrio necesita vender en línea',
        'fecha' => '2026-09-08',
        'autor' => 'Equipo VetPet Connect',
        'resumen' => 'El ingreso de un local de barrio depende de quién pase por la puerta. Así se ve ese problema cuando se mide, y por qué un canal digital lo cambia.',
        'cuerpo' => [
            ['p', 'VetPet Surco atiende en Santiago de Surco desde 2019 con seis colaboradores y un solo local. Durante años su facturación siguió una curva conocida: sube en campaña de vacunación y cae el resto del año. La razón es simple y poco cómoda de aceptar: el ingreso es igual al tránsito peatonal de la avenida.'],
            ['h', 'Lo que el diagnóstico encontró'],
            ['p', 'Antes de escribir una línea de código entrevistamos a la gerencia, a los dos médicos veterinarios y a la encargada de tienda, y observamos un día completo de atención. Aparecieron cinco problemas, todos con un costo concreto:'],
            ['lista', [
                'Las citas se coordinaban por llamada y por WhatsApp personal, sin agenda común: dos mascotas podían quedar citadas a la misma hora con el mismo veterinario.',
                'El historial de cada mascota vivía en fichas de cartón archivadas en el local, imposibles de consultar de forma remota.',
                'El inventario se llevaba en un cuaderno, con quiebres de stock del alimento, que es lo que más rota.',
                'No existía ningún canal de venta en línea.',
                'No había ningún recordatorio hacia el cliente: el dueño olvidaba la vacuna y la recompra del alimento.',
            ]],
            ['h', 'Por qué el canal digital no es "tener una página"'],
            ['p', 'La tentación es pensar que el problema se arregla con un sitio bonito. No es así. Un sitio que solo muestra fotos no descuenta inventario, no impide que dos citas choquen y no le recuerda a nadie la vacuna de su perro. Lo que cambia el negocio no es la vitrina: es que cada venta toque el inventario, cada atención quede registrada y cada recordatorio salga solo.'],
            ['cita', 'No queremos un sistema bonito. Queremos dejar de perder clientes por no contestar el teléfono.'],
            ['p', 'Esa frase de la gerencia ordenó todo el proyecto. Lo que vino después —catálogo, carrito, cobro en línea, agenda, historia clínica digital— son consecuencias de ella, no funciones elegidas de un menú.'],
        ],
    ],

    'suscripcion-mensual' => [
        'titulo' => 'Cómo funciona la suscripción mensual, y por qué es el eje del modelo',
        'fecha' => '2026-09-15',
        'autor' => 'Equipo VetPet Connect',
        'resumen' => 'De las tres fuentes de ingreso del negocio, solo una se conoce antes de que empiece el mes. Así está construida.',
        'cuerpo' => [
            ['p', 'Una mascota come todos los meses. Esa obviedad es la base del modelo: el gasto del dueño es predecible, y quien captura esa recurrencia deja de depender de que el cliente se acuerde de volver.'],
            ['h', 'Tres planes que se diferencian en lo que llega'],
            ['p', 'Lo que separa un plan de otro no es una etiqueta comercial: son las unidades que entran en cada despacho, y esa cantidad se usa de verdad al generar el pedido, así que se ve en el detalle y en el stock descontado.'],
            ['lista', [
                'Plan Básico: S/ 99 al mes, una unidad por despacho.',
                'Plan Cuidado: S/ 149 al mes, dos unidades.',
                'Plan Integral: S/ 219 al mes, tres unidades.',
            ]],
            ['h', 'El despacho ocurre sin que nadie lo pida'],
            ['p', 'Cuando una suscripción activa llega a su fecha, el sistema emite el pedido por su cuenta y lo marca como despacho de suscripción, para poder separarlo de la venta directa en los reportes. El cliente no tiene que acordarse y el personal tampoco.'],
            ['p', 'Hay un detalle que importa más de lo que parece: si ese día no hay stock, el despacho no se emite incompleto. Se posterga, la fecha no avanza, y se recupera en la siguiente pasada en cuanto el administrador repone. La empresa prefiere no despachar antes que despachar mal.'],
            ['h', 'El cliente manda sobre su plan'],
            ['p', 'Pausar cuando se va de viaje y cancelar cuando quiera, desde su cuenta, sin llamar ni pedir permiso. Un plan que cuesta trabajo cancelar no fideliza: retiene a la fuerza, y eso se paga después.'],
        ],
    ],

    'reglas-de-negocio' => [
        'titulo' => 'Las 22 reglas que protegen cada compra',
        'fecha' => '2026-09-29',
        'autor' => 'Equipo VetPet Connect',
        'resumen' => 'Las políticas del negocio no viven en la pantalla ni en la memoria del personal: están escritas en el sistema y se comprueban solas.',
        'cuerpo' => [
            ['p', 'Un sistema de comercio electrónico se cae por los bordes: dos personas comprando la última unidad al mismo tiempo, un precio que cambia después de emitido el pedido, dos citas en el mismo horario. Por eso VetPet Connect declara veintidós reglas de negocio y las hace cumplir en dos capas.'],
            ['h', 'Dos capas, no una'],
            ['p', 'La primera capa es la aplicación: comprueba la regla y devuelve un mensaje que el cliente entiende, con qué producto falta y cuántas unidades quedan. La segunda es la base de datos: restricciones en las propias tablas, de modo que aunque un error de programación futuro se salte la capa anterior, el dato inválido no entra.'],
            ['cita', 'Si no hay stock completo, no hay pedido: preferimos no vender antes que prometer lo que no tenemos.'],
            ['p', 'Esa es la regla RN-12, y es la más visible para el cliente. Al confirmar la compra el sistema bloquea cada producto, suma todo lo que falta y, si algo no alcanza, no escribe nada: el pedido es todo o nada.'],
            ['h', 'Comprobables, no declarativas'],
            ['p', 'Cada una de las veintidós reglas tiene al menos una prueba automatizada que se ejecuta contra una base de datos real antes de cada entrega. Si un cambio rompe una regla del negocio, se sabe en el momento y no en el reclamo de un cliente.'],
        ],
    ],

    'alinear-tecnologia-y-negocio' => [
        'titulo' => 'Lo que aprendimos alineando la tecnología con la estrategia comercial',
        'fecha' => '2026-10-13',
        'autor' => 'Equipo VetPet Connect',
        'resumen' => 'Cuatro lecciones de un proyecto donde cada decisión técnica tuvo que justificarse con un problema del negocio.',
        'cuerpo' => [
            ['h', 'El requerimiento manda, la tecnología obedece'],
            ['p', 'Elegimos PHP 8.2 y MySQL no por gusto, sino porque la empresa es una MYPE que debe poder contratar a cualquier programador y mudarse de proveedor si el costo sube. Esa condición estaba escrita como requerimiento antes de elegir nada.'],
            ['h', 'Avisar no es resolver'],
            ['p', 'El sistema señala con un semáforo qué productos hay que reponer, y ahí el proceso termina: alguien debe acordarse de llamar al proveedor. Reconocerlo nos hizo entender que el objetivo de cero quiebres de stock no se cumple con una alerta, y que el siguiente paso es cerrar ese ciclo.'],
            ['h', 'Construir también sirve para descubrir requerimientos'],
            ['p', 'El prototipo navegable reveló reglas que ninguna entrevista había mencionado, como que un mismo producto no debe aparecer dos veces en un pedido. La especificación formal llegó después de construir, y eso no es un defecto del método: es el método.'],
            ['h', 'Lo que no se mide, no se entregó'],
            ['p', 'La frase "el sistema cumple las reglas" no vale nada sin evidencia. Por eso cada regla tiene su prueba y cada cifra del proyecto sale de ejecutarlo, no de un documento. Es la diferencia entre decir que funciona y poder demostrarlo.'],
        ],
    ],
];
