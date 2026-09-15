<?php

namespace App\Http\Controllers;

use App\Enums\EstadoSuscripcion;
use App\Models\Suscripcion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Cambio de estado de la suscripcion mensual.
 * RN-15: el cliente pausa o cancela su plan cuando quiera; una suscripcion
 * cancelada no se reactiva, se contrata de nuevo.
 */
class SuscripcionController extends Controller
{
    public function estado(Request $request, Suscripcion $suscripcion): RedirectResponse
    {
        // La suscripcion tiene que ser de la cuenta que inicio sesion.
        abort_if($suscripcion->cliente_id !== $request->user()->getAuthIdentifier(), 403);

        $datos = $request->validate([
            'estado' => ['required', 'in:ACTIVA,PAUSADA,CANCELADA'],
        ], [
            'estado.required' => 'Indica el nuevo estado de la suscripcion.',
            'estado.in' => 'Estado de suscripcion no valido.',
        ]);

        $actual = $suscripcion->estado instanceof EstadoSuscripcion
            ? $suscripcion->estado
            : EstadoSuscripcion::from((string) $suscripcion->estado);

        // RN-15: lo cancelado no vuelve atras.
        if ($actual === EstadoSuscripcion::CANCELADA) {
            return back()->with('resultado', [
                'regla' => 'RN-15',
                'mensaje' => 'Una suscripcion cancelada no se reactiva: se contrata de nuevo.',
                'ok' => false,
            ]);
        }

        $nuevo = EstadoSuscripcion::from($datos['estado']);
        $suscripcion->estado = $nuevo;
        $suscripcion->save();

        return back()->with('resultado', [
            'regla' => null,
            'mensaje' => 'La suscripcion quedo '.$nuevo->value.'.',
            'ok' => true,
        ]);
    }
}
