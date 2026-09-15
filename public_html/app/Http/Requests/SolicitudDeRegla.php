<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Base de los Form Requests que hacen cumplir una regla de negocio.
 *
 * Cuando la validacion falla, ademas del error normal de Laravel deja en la
 * sesion el arreglo 'resultado' con el codigo RN-xx y el mensaje, que es lo
 * que pinta el partial components/aviso.blade.php.
 */
abstract class SolicitudDeRegla extends FormRequest
{
    /**
     * Codigo de regla asociado a cada campo del formulario.
     *
     * @return array<string, string>
     */
    protected function reglasDeNegocio(): array
    {
        return [];
    }

    public function authorize(): bool
    {
        return true;
    }

    protected function failedValidation(Validator $validator): void
    {
        $primerCampo = $validator->errors()->keys()[0] ?? null;
        $codigos = $this->reglasDeNegocio();

        $this->session()->flash('resultado', [
            'regla' => $primerCampo !== null ? ($codigos[$primerCampo] ?? null) : null,
            'mensaje' => (string) $validator->errors()->first(),
            'ok' => false,
        ]);

        parent::failedValidation($validator);
    }
}
