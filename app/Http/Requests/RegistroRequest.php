<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

/**
 * Alta de una cuenta de cliente.
 * RN-02: un correo identifica a una sola cuenta.
 * RN-03: la contrasena se guarda hasheada, nunca legible.
 */
class RegistroRequest extends SolicitudDeRegla
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100'],
            // RN-02: correo unico en la tabla usuarios.
            'correo' => ['required', 'email', 'max:100', Rule::unique('usuarios', 'correo')],
            'clave' => ['required', 'string', 'min:6', 'confirmed'],
            'telefono' => ['nullable', 'string', 'max:15'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre y apellido son obligatorios.',
            'correo.required' => 'El correo electronico es obligatorio.',
            'correo.email' => 'Escribe un correo electronico valido.',
            'correo.unique' => 'Ese correo ya esta registrado en otra cuenta.',
            'clave.required' => 'La contrasena es obligatoria.',
            'clave.min' => 'La contrasena debe tener al menos 6 caracteres.',
            'clave.confirmed' => 'La confirmacion de la contrasena no coincide.',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function reglasDeNegocio(): array
    {
        return ['correo' => 'RN-02', 'clave' => 'RN-03'];
    }
}
