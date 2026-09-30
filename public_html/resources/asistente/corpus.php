<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Corpus de evaluacion del asistente
|--------------------------------------------------------------------------
| Preguntas reales escritas como las escribe un cliente: con errores de
| tecleo, sin tildes, en singular y en plural, con jerga peruana. Cada una
| con la intencion que deberia reconocer.
|
| Estas frases NO son las que usa el motor para entender (esas estan en
| intenciones.php). Sirven para medir: si se cambia una palabra clave y el
| asistente empeora, la prueba lo dice. Agregar aqui cada pregunta que el
| asistente falle en produccion es la forma de entrenarlo.
*/

return [

    /* Stock: el caso que fallaba */
    ['hay stock de alimento para gato', 'stock_producto'],
    ['tienen alimento de perro de 15 kilos', 'stock_producto'],
    ['queda arena sanitaria', 'stock_producto'],
    ['hay camas disponibles', 'stock_producto'],
    ['tienen juguetes', 'stock_producto'],
    ['hay vitaminas', 'stock_producto'],
    ['cuantas unidades quedan de la correa', 'stock_producto'],
    ['stock de antipulgas', 'stock_producto'],
    ['me queda alimento para cachorro', 'stock_producto'],
    ['se agoto la cama', 'stock_producto'],
    ['todavia tienen la pipeta', 'stock_producto'],
    ['disponibilidad de arena', 'stock_producto'],
    ['venden comida para michi', 'stock_producto'],
    ['consigo croquetas de perro', 'stock_producto'],
    ['hay alimeto para gato', 'stock_producto'],
    ['tienen corea retractil', 'stock_producto'],

    /* Precio */
    ['cuanto cuesta el alimento de gato', 'precio_producto'],
    ['precio de la cama', 'precio_producto'],
    ['a cuanto sale la arena', 'precio_producto'],
    ['cuanto vale el juguete mordedor', 'precio_producto'],
    ['que precio tiene el antipulgas', 'precio_producto'],
    ['cuanto cobran por las vitaminas', 'precio_producto'],

    /* Cuenta del cliente */
    ['donde esta mi pedido', 'mis_pedidos'],
    ['ya llego mi compra', 'mis_pedidos'],
    ['en que estado va mi pedido', 'mis_pedidos'],
    ['mis ordenes', 'mis_pedidos'],
    ['cuando me toca el despacho', 'mi_suscripcion'],
    ['mi suscripcion', 'mi_suscripcion'],
    ['cuando llega mi plan mensual', 'mi_suscripcion'],
    ['tengo citas', 'mis_citas'],
    ['cuando es mi cita', 'mis_citas'],
    ['mis turnos con el veterinario', 'mis_citas'],
    ['cuales son mis mascotas', 'mis_mascotas'],
    ['la ficha de mi mascota', 'mis_mascotas'],
    ['que tengo en el carrito', 'carrito_estado'],
    ['mi carrito', 'carrito_estado'],

    /* Informacion del negocio */
    ['como compro', 'comprar'],
    ['quiero comprar', 'comprar'],
    ['cuanto tarda el envio', 'envios'],
    ['hacen entrega a domicilio', 'envios'],
    ['como pago', 'pago_info'],
    ['puedo pagar con tarjeta', 'pago_info'],
    ['aceptan visa', 'pago_info'],
    ['cuando toca la proxima vacuna', 'salud'],
    ['la historia clinica de mi perro', 'salud'],
    ['recordatorio de desparasitacion', 'salud'],
    ['a que hora abren', 'horarios'],
    ['donde queda el local', 'horarios'],
    ['cual es el telefono', 'horarios'],
    ['quiero hablar con una persona', 'persona'],
    ['necesito un asesor', 'persona'],
    ['como funciona la suscripcion', 'suscripcion_info'],
    ['quiero contratar el plan', 'suscripcion_info'],

    /* Cortesia: saludar no es preguntar, pero tampoco es no entender */
    ['hola', 'inicio'],
    ['buenas', 'inicio'],
    ['buenos dias', 'inicio'],
    ['hola pelusa', 'inicio'],
    ['que tal', 'inicio'],
    ['hey', 'inicio'],
    ['gracias', 'agradecimiento'],
    ['ok gracias', 'agradecimiento'],
    ['chau', 'despedida'],
    ['adios', 'despedida'],

    /* Con saludo delante manda la pregunta, no el saludo */
    ['hola, queda arena sanitaria', 'stock_producto'],
    ['buenas, cuanto cuesta la cama', 'precio_producto'],
    ['hola, donde esta mi pedido', 'mis_pedidos'],

    /* Lo que debe reconocer como "no entendi" */
    ['asdfghjk', null],
    ['cual es la capital de francia', null],
    ['12345', null],
];
