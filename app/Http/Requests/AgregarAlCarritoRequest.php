<?php

namespace App\Http\Requests;

/**
 * Agregar una linea al carrito.
 * RN-10: no se compran cantidades menores o iguales a cero.
 */
class AgregarAlCarritoRequest extends SolicitudDeRegla
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'producto_id' => ['required', 'integer', 'exists:productos,producto_id'],
            // RN-10: cantidad entera y mayor que cero.
            'cantidad' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'producto_id.required' => 'No se indico el producto que quieres agregar.',
            'producto_id.exists' => 'Ese producto ya no esta en el catalogo.',
            'cantidad.required' => 'Indica cuantas unidades quieres agregar.',
            'cantidad.integer' => 'La cantidad debe ser un numero entero mayor que cero.',
            'cantidad.min' => 'La cantidad debe ser un numero entero mayor que cero.',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function reglasDeNegocio(): array
    {
        return ['cantidad' => 'RN-10'];
    }
}
