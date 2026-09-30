<?php

namespace App\Http\Controllers;

use App\Exceptions\ReglaDeNegocioException;
use App\Mail\FacturaPedidoMail;
use App\Models\Pedido;
use App\Services\CorreoService;
use App\Services\PagoService;
use App\Services\Pasarela;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Pago del pedido con tarjeta. — RN-21
 *
 * El numero de tarjeta nunca pasa por aqui: el checkout de Culqi lo cambia
 * por un token en el navegador y este controlador solo recibe ese token.
 */
class PagoController extends Controller
{
    public function __construct(
        private readonly PagoService $pagos,
        private readonly Pasarela $pasarela,
    ) {
    }

    public function mostrar(Request $request, Pedido $pedido): View
    {
        $this->exigirPropiedad($request, $pedido);

        return view('app.pago', [
            // RN-21: los intentos previos se muestran como historial del pedido.
            'pedido' => $pedido->load(['detalles.producto', 'pagos']),
            'llavePublica' => (string) config('services.culqi.llave_publica'),
            'simulada' => $this->pasarela->esSimulada(),
        ]);
    }

    public function procesar(Request $request, Pedido $pedido): RedirectResponse
    {
        $this->exigirPropiedad($request, $pedido);

        $datos = $request->validate([
            'token' => ['required', 'string', 'starts_with:tkn_'],
        ], [
            'token.required' => 'No llego el token de la tarjeta.',
            'token.starts_with' => 'El token de la tarjeta no tiene el formato esperado.',
        ]);

        try {
            $pago = $this->pagos->cobrar(
                $pedido,
                $datos['token'],
                (string) $request->user()->correo,
            );
        } catch (ReglaDeNegocioException $e) {
            return back()->with('resultado', [
                'regla' => $e->regla(),
                'mensaje' => $e->getMessage(),
                'ok' => false,
            ])->with('pago_estado', 'rechazado');
        }

        // Comprobante por correo (demo): CorreoService absorve cualquier fallo
        // para que un cobro aprobado jamas se pierda por un problema de correo.
        app(CorreoService::class)->enviar(
            (string) $request->user()->correo,
            new FacturaPedidoMail($pedido->load('detalles.producto')),
        );

        return redirect()->route('catalogo')->with('resultado', [
            'regla' => null,
            'mensaje' => 'Pago aprobado. El pedido '.$pedido->getKey().' quedo PAGADO.'
                .($pago->ultimos_cuatro !== null ? ' Tarjeta terminada en '.$pago->ultimos_cuatro.'.' : ''),
            'ok' => true,
        ])->with('pago_estado', 'aprobado');
    }

    /** Un cliente solo puede pagar sus propios pedidos. — RN-01 */
    private function exigirPropiedad(Request $request, Pedido $pedido): void
    {
        abort_unless((int) $pedido->cliente_id === (int) $request->user()->getKey(), 403);
    }
}
