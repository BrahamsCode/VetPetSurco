<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

/**
 * Reserva de una cita veterinaria.
 * RN-04: la mascota tiene que ser del cliente de la sesion.
 * RN-17: un veterinario no atiende dos citas a la misma hora.
 */
class ReservarCitaRequest extends SolicitudDeRegla
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // RN-04: solo mascotas cuyo cliente_id es el usuario de la sesion.
            'mascota_id' => [
                'required',
                'integer',
                Rule::exists('mascotas', 'mascota_id')
                    ->where('cliente_id', $this->user()?->usuario_id),
            ],
            'veterinario_id' => [
                'required',
                'integer',
                Rule::exists('usuarios', 'usuario_id')->where('rol', 'VETERINARIO'),
            ],
            'servicio' => ['required', Rule::in(['CONSULTA', 'VACUNACION', 'DESPARASITACION', 'GROOMING'])],
            'fecha' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'hora' => ['required', 'date_format:H:i'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'mascota_id.required' => 'Elige la mascota que sera atendida.',
            'mascota_id.exists' => 'Esa mascota no esta registrada a tu nombre.',
            'veterinario_id.required' => 'Elige el veterinario que atendera la cita.',
            'veterinario_id.exists' => 'Ese veterinario no existe.',
            'servicio.required' => 'Elige el servicio que necesita tu mascota.',
            'servicio.in' => 'Ese servicio no forma parte de la atencion veterinaria.',
            'fecha.required' => 'Elige la fecha de la cita.',
            'fecha.after_or_equal' => 'La cita no se puede reservar en una fecha pasada.',
            'hora.required' => 'Elige primero un horario disponible.',
            'hora.date_format' => 'Elige primero un horario disponible.',
        ];
    }

    /**
     * Fecha y hora unidas, tal como las guarda la columna citas.fecha_hora.
     */
    public function fechaHora(): string
    {
        return $this->string('fecha').' '.$this->string('hora').':00';
    }

    /**
     * @return array<string, string>
     */
    protected function reglasDeNegocio(): array
    {
        return ['mascota_id' => 'RN-04', 'hora' => 'RN-17', 'fecha' => 'RN-17'];
    }
}
