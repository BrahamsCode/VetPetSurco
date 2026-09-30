<?php

declare(strict_types=1);

/*
| Senales que desempatan cuando el cliente nombra un producto.
|
| "cuanto cuesta la arena" y "queda arena" nombran el mismo producto: lo que
| cambia es el verbo. Estas listas deciden si la pregunta es de precio o de
| disponibilidad.
*/

return [

    'disponibilidad' => [
        'stock', 'disponible', 'disponibilidad', 'quedar', 'queda', 'quedan',
        'tener', 'tienen', 'tienes', 'hay', 'agotado', 'agotarse', 'existencia',
        'unidades', 'vender', 'venden', 'conseguir', 'consigo', 'llevar',
    ],

    'precio' => [
        'precio', 'precios', 'costar', 'cuesta', 'valer', 'vale', 'valor',
        'cobran', 'sale', 'tarifa', 'costo',
    ],
];
