<?php

/**
 * Mensajes de validacion en espanol.
 *
 * Sin este archivo Laravel no encuentra el texto (APP_LOCALE=es y
 * APP_FALLBACK_LOCALE=es) y muestra la clave cruda, por ejemplo
 * "validation.max.string". Los Form Requests pueden seguir definiendo
 * mensajes propios en messages(); estos son la red de seguridad.
 */
return [

    'accepted' => 'Debes aceptar el campo :attribute.',
    'after' => 'El campo :attribute debe ser una fecha posterior a :date.',
    'after_or_equal' => 'El campo :attribute debe ser una fecha igual o posterior a :date.',
    'array' => 'El campo :attribute debe ser una lista.',
    'before' => 'El campo :attribute debe ser una fecha anterior a :date.',
    'before_or_equal' => 'El campo :attribute debe ser una fecha igual o anterior a :date.',
    'between' => [
        'array' => 'El campo :attribute debe tener entre :min y :max elementos.',
        'file' => 'El archivo :attribute debe pesar entre :min y :max kilobytes.',
        'numeric' => 'El campo :attribute debe estar entre :min y :max.',
        'string' => 'El campo :attribute debe tener entre :min y :max caracteres.',
    ],
    'boolean' => 'El campo :attribute debe ser verdadero o falso.',
    'confirmed' => 'La confirmacion de :attribute no coincide.',
    'date' => 'El campo :attribute no es una fecha valida.',
    'date_format' => 'El campo :attribute debe tener el formato :format.',
    'decimal' => 'El campo :attribute debe tener :decimal decimales.',
    'digits' => 'El campo :attribute debe tener :digits digitos.',
    'digits_between' => 'El campo :attribute debe tener entre :min y :max digitos.',
    'email' => 'El campo :attribute debe ser un correo electronico valido.',
    'exists' => 'El :attribute seleccionado no existe.',
    'gt' => [
        'array' => 'El campo :attribute debe tener mas de :value elementos.',
        'file' => 'El archivo :attribute debe pesar mas de :value kilobytes.',
        'numeric' => 'El campo :attribute debe ser mayor que :value.',
        'string' => 'El campo :attribute debe tener mas de :value caracteres.',
    ],
    'gte' => [
        'array' => 'El campo :attribute debe tener :value elementos o mas.',
        'file' => 'El archivo :attribute debe pesar :value kilobytes o mas.',
        'numeric' => 'El campo :attribute debe ser mayor o igual que :value.',
        'string' => 'El campo :attribute debe tener :value caracteres o mas.',
    ],
    'image' => 'El campo :attribute debe ser una imagen.',
    'in' => 'El valor elegido en :attribute no es valido.',
    'integer' => 'El campo :attribute debe ser un numero entero.',
    'lt' => [
        'array' => 'El campo :attribute debe tener menos de :value elementos.',
        'file' => 'El archivo :attribute debe pesar menos de :value kilobytes.',
        'numeric' => 'El campo :attribute debe ser menor que :value.',
        'string' => 'El campo :attribute debe tener menos de :value caracteres.',
    ],
    'lte' => [
        'array' => 'El campo :attribute no debe tener mas de :value elementos.',
        'file' => 'El archivo :attribute no debe pesar mas de :value kilobytes.',
        'numeric' => 'El campo :attribute debe ser menor o igual que :value.',
        'string' => 'El campo :attribute debe tener :value caracteres o menos.',
    ],
    'max' => [
        'array' => 'El campo :attribute no debe tener mas de :max elementos.',
        'file' => 'El archivo :attribute no debe pesar mas de :max kilobytes.',
        'numeric' => 'El campo :attribute no debe ser mayor que :max.',
        'string' => 'El campo :attribute no debe tener mas de :max caracteres.',
    ],
    'mimes' => 'El archivo :attribute debe ser de tipo: :values.',
    'min' => [
        'array' => 'El campo :attribute debe tener al menos :min elementos.',
        'file' => 'El archivo :attribute debe pesar al menos :min kilobytes.',
        'numeric' => 'El campo :attribute debe ser al menos :min.',
        'string' => 'El campo :attribute debe tener al menos :min caracteres.',
    ],
    'numeric' => 'El campo :attribute debe ser un numero.',
    'regex' => 'El formato de :attribute no es valido.',
    'required' => 'El campo :attribute es obligatorio.',
    'size' => [
        'array' => 'El campo :attribute debe tener :size elementos.',
        'file' => 'El archivo :attribute debe pesar :size kilobytes.',
        'numeric' => 'El campo :attribute debe ser :size.',
        'string' => 'El campo :attribute debe tener :size caracteres.',
    ],
    'string' => 'El campo :attribute debe ser texto.',
    'unique' => 'Ese :attribute ya esta registrado.',

    // Nombres legibles de los campos que valida la aplicacion.
    'attributes' => [
        'accion' => 'accion',
        'alergias' => 'alergias',
        'cantidad' => 'cantidad',
        'categoria' => 'categoria',
        'clave' => 'contrasena',
        'codigo_sku' => 'codigo SKU',
        'correo' => 'correo electronico',
        'desenlace' => 'desenlace',
        'diagnostico' => 'diagnostico',
        'especie' => 'especie',
        'estado' => 'estado',
        'fecha' => 'fecha',
        'fecha_nacimiento' => 'fecha de nacimiento',
        'hora' => 'hora',
        'indicadores' => 'indicadores',
        'mascota' => 'mascota',
        'mascota_id' => 'mascota',
        'mensaje' => 'mensaje',
        'motivo' => 'motivo',
        'nombre' => 'nombre',
        'peso_kg' => 'peso (kg)',
        'plan' => 'plan',
        'precio' => 'precio',
        'producto_id' => 'producto',
        'proxima_fecha' => 'proxima fecha',
        'punto_reorden' => 'punto de reorden',
        'raza' => 'raza',
        'servicio' => 'servicio',
        'stock_actual' => 'stock actual',
        'telefono' => 'telefono',
        'tratamiento' => 'tratamiento',
        'vacuna_aplicada' => 'vacuna aplicada',
        'veterinario_id' => 'veterinario',
    ],

];
