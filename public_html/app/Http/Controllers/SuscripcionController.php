<?php

namespace App\Http\Controllers;

use App\Enums\EstadoSuscripcion;
use App\Exceptions\ReglaDeNegocioException;
use App\Models\Mascota;
use App\Models\Producto;
use App\Models\Suscripcion;
use App\Services\SuscripcionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Alta y cambio de estado de la suscripcion mensual.
 * RN-15: el cliente pausa o cancela su plan cuando quiera; una suscripcion
 * cancelada no se reactiva, se contrata de nuevo.
 * RN-22: no se duplica un plan vigente para la misma mascota y producto.
 */
class SuscripcionController extends Controller
{
    public function __construct(private readonly SuscripcionService $suscripciones)
    {
    }

    /** Contratacion de un plan nuevo. — RF-03 / RN-16 / RN-22 */
    public function contratar(Request $request): RedirectResponse
    {
        $cliente = $request->user();

        $datos = $request->validate([
            // RN-04: solo se puede suscribir una mascota de la propia cuenta.
            'mascota_id' => [
                'required',
                Rule::exists('mascotas', 'mascota_id')
                    ->where('cliente_id', $cliente->usuario_id),
            ],
            'producto_id' => [
                'required',
                Rule::exists('productos', 'producto_id')->where('activo', true),
            ],
            'plan' => ['required', Rule::in(array_keys(SuscripcionService::planes()))],
        ], [
            'mascota_id.required' => 'Elige a que mascota le contratas el plan.',
            'mascota_id.exists' => 'Esa mascota no es de tu cuenta.',
            'producto_id.required' => 'Elige el producto que quieres recibir cada mes.',
            'producto_id.exists' => 'Ese producto no esta disponible.',
            'plan.required' => 'Elige un plan.',
            'plan.in' => 'Ese plan no existe.',
        ]);

        try {
            $suscripcion = $this->suscripciones->contratar(
                $cliente,
                Mascota::query()->findOrFail($datos['mascota_id']),
                Producto::query()->findOrFail($datos['producto_id']),
                $datos['plan'],
            );
        } catch (ReglaDeNegocioException $e) {
            return back()->with('resultado', [
                'regla' => $e->regla(),
                'mensaje' => $e->getMessage(),
                'ok' => false,
            ]);
        }

        return back()->with('resultado', [
            'regla' => null,
            'mensaje' => 'Plan '.$suscripcion->plan.' contratado por S/ '
                .number_format((float) $suscripcion->monto_mensual, 2)
                .' al mes. Primer despacho el '.$suscripcion->proximo_despacho->format('Y-m-d').'.',
            'ok' => true,
        ]);
    }

    public function estado(Request $request, Suscripcion $suscripcion): RedirectResponse
    {
        // La suscripcion tiene que ser de la cuenta que inicio sesion.
        abort_if((int) $suscripcion->cliente_id !== (int) $request->user()->usuario_id, 403);

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
