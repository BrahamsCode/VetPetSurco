<?php

namespace App\Http\Requests;

/**
 * Registro clinico de una cita atendida.
 * RN-19: cada atencion genera un unico registro.
 * RN-20: la fecha del proximo control alimenta el recordatorio.
 */
class RegistrarAtencionRequest extends SolicitudDeRegla
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'diagnostico' => ['required', 'string', 'max:2000'],
            'tratamiento' => ['required', 'string', 'max:2000'],
            'vacuna_aplicada' => ['nullable', 'string', 'max:100'],
            // RN-20: si hay proximo control, no puede quedar en el pasado.
            'proxima_fecha' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:today'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'diagnostico.required' => 'El diagnostico es obligatorio.',
            'tratamiento.required' => 'El tratamiento es obligatorio.',
            'proxima_fecha.after_or_equal' => 'El proximo control no puede quedar en una fecha pasada.',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function reglasDeNegocio(): array
    {
        return ['diagnostico' => 'RN-19', 'proxima_fecha' => 'RN-20'];
    }
}
