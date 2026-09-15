<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

/**
 * Alta de producto desde el dashboard administrativo.
 * RN-05: cada producto tiene un SKU irrepetible.
 * RN-06: ningun producto se vende a precio cero o negativo.
 * RN-07: el inventario nunca queda en negativo.
 */
class CrearProductoRequest extends SolicitudDeRegla
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // RN-05: el SKU no se puede repetir.
            'codigo_sku' => ['required', 'string', 'max:50', Rule::unique('productos', 'codigo_sku')],
            'nombre' => ['required', 'string', 'max:150'],
            'categoria' => ['required', Rule::in(['ALIMENTO', 'ACCESORIO', 'MEDICAMENTO', 'ARENA'])],
            // RN-06: precio estrictamente mayor que cero.
            'precio' => ['required', 'numeric', 'gt:0'],
            // RN-07: el stock inicial nunca es negativo.
            'stock_actual' => ['required', 'integer', 'min:0'],
            'punto_reorden' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'codigo_sku.required' => 'El codigo SKU es obligatorio.',
            'codigo_sku.unique' => 'Ya existe un producto con ese SKU.',
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'categoria.required' => 'Elige una categoria para el producto.',
            'categoria.in' => 'Esa categoria no existe en el catalogo.',
            'precio.required' => 'El precio es obligatorio.',
            'precio.numeric' => 'El precio debe ser mayor que cero.',
            'precio.gt' => 'El precio debe ser mayor que cero.',
            'stock_actual.required' => 'Indica el stock inicial del producto.',
            'stock_actual.min' => 'El stock inicial no puede ser negativo.',
        ];
    }

    /**
     * Normaliza el SKU antes de validarlo: siempre en mayusculas.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('codigo_sku')) {
            $this->merge(['codigo_sku' => mb_strtoupper(trim((string) $this->input('codigo_sku')))]);
        }
    }

    /**
     * @return array<string, string>
     */
    protected function reglasDeNegocio(): array
    {
        return ['codigo_sku' => 'RN-05', 'precio' => 'RN-06', 'stock_actual' => 'RN-07'];
    }
}
