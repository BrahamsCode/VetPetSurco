<?php

namespace App\Http\Controllers;

use App\Exceptions\ReglaDeNegocioException;
use App\Models\Pedido;
use App\Services\PedidoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * "Mis pedidos" del cliente: en que va cada pedido y, si no lo pago,
 * pagarlo o anularlo.
 */
class PedidoController extends Controller
{
    /** Pedidos por pagina. */
    private const POR_PAGINA = 10;

    public function __construct(private readonly PedidoService $pedidos)
    {
    }

    public function index(Request $request): View
    {
        $pedidos = Pedido::query()
            ->with(['detalles.producto', 'pagos'])
            ->where('cliente_id', $request->user()->usuario_id)
            ->orderByDesc('pedido_id')
            ->paginate(self::POR_PAGINA);

        return view('app.pedidos', ['pedidos' => $pedidos]);
    }

    /** El cliente desiste de un pedido que todavia no pago: el stock vuelve. */
    public function anular(Request $request, Pedido $pedido): RedirectResponse
    {
        // RN-04: cada cliente solo toca sus propios pedidos.
        abort_if((int) $pedido->cliente_id !== (int) $request->user()->usuario_id, 403);

        try {
            $this->pedidos->anular($pedido, 'Anulado por el cliente.');
        } catch (ReglaDeNegocioException $e) {
            return back()->with('resultado', [
                'regla' => $e->regla(),
                'mensaje' => $e->getMessage(),
                'ok' => false,
            ]);
        }

        return back()->with('resultado', [
            'regla' => null,
            'mensaje' => 'Anulaste el pedido '.$pedido->pedido_id.'. Los productos volvieron a la tienda.',
            'ok' => true,
        ]);
    }
}
