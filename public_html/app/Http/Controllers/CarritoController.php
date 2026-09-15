<?php

namespace App\Http\Controllers;

use App\Enums\TipoOrigen;
use App\Exceptions\ReglaDeNegocioException;
use App\Exceptions\StockInsuficienteException;
use App\Http\Requests\AgregarAlCarritoRequest;
use App\Models\Producto;
use App\Services\CarritoService;
use App\Services\PedidoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Carrito de compras del cliente.
 * RN-09: un producto aparece una sola vez por pedido; repetirlo acumula.
 * RN-10: no se compran cantidades menores o iguales a cero.
 * RN-11: el precio unitario queda congelado al agregar la linea.
 * RN-12: sin stock suficiente no se registra ninguna parte del pedido.
 * RN-14: el pedido distingue la compra directa del despacho de suscripcion.
 */
class CarritoController extends Controller
{
    public function __construct(
        private readonly CarritoService $carrito,
        private readonly PedidoService $pedidos,
    ) {
    }

    public function index(): View
    {
        return view('app.carrito', [
            'lineas' => $this->carrito->items(),
            'total' => $this->carrito->total(),
            'unidades' => $this->carrito->unidades(),
        ]);
    }

    public function agregar(AgregarAlCarritoRequest $solicitud): RedirectResponse
    {
        $producto = Producto::query()->findOrFail($solicitud->integer('producto_id'));

        try {
            // RN-09 y RN-10 viven dentro del servicio.
            $this->carrito->agregar($producto, $solicitud->integer('cantidad'));
        } catch (ReglaDeNegocioException $e) {
            return back()->with('resultado', [
                'regla' => $e->regla(),
                'mensaje' => $e->getMessage(),
                'ok' => false,
            ]);
        }

        return back()->with('resultado', [
            'regla' => null,
            'mensaje' => 'Producto agregado al carrito.',
            'ok' => true,
        ]);
    }

    public function actualizar(Request $request, int $producto): RedirectResponse
    {
        // RN-10: la cantidad tiene que ser un entero mayor que cero.
        $datos = $request->validate([
            'cantidad' => ['required', 'integer', 'min:1'],
        ], [
            'cantidad.required' => 'La cantidad debe ser un numero entero mayor que cero.',
            'cantidad.integer' => 'La cantidad debe ser un numero entero mayor que cero.',
            'cantidad.min' => 'La cantidad debe ser un numero entero mayor que cero.',
        ]);

        try {
            $this->carrito->actualizarCantidad($producto, (int) $datos['cantidad']);
        } catch (ReglaDeNegocioException $e) {
            return back()->with('resultado', [
                'regla' => $e->regla(),
                'mensaje' => $e->getMessage(),
                'ok' => false,
            ]);
        }

        return back()->with('resultado', [
            'regla' => null,
            'mensaje' => 'Se actualizo la cantidad de la linea.',
            'ok' => true,
        ]);
    }

    public function quitar(int $producto): RedirectResponse
    {
        $this->carrito->quitar($producto);

        return back()->with('resultado', [
            'regla' => null,
            'mensaje' => 'Se quito el producto del carrito.',
            'ok' => true,
        ]);
    }

    public function confirmar(Request $request): RedirectResponse
    {
        // RN-14: el origen viaja con el pedido.
        $datos = $request->validate([
            'origen' => ['required', 'in:COMPRA_DIRECTA,SUSCRIPCION'],
        ], [
            'origen.required' => 'Indica el origen del pedido.',
            'origen.in' => 'Ese origen de pedido no existe.',
        ]);

        $lineas = $this->carrito->items();

        if ($lineas === []) {
            return back()->with('resultado', [
                'regla' => 'RN-12',
                'mensaje' => 'El carrito esta vacio.',
                'ok' => false,
            ]);
        }

        try {
            // RN-12: transaccion con bloqueo de filas; o entra todo o no entra nada.
            $pedido = $this->pedidos->confirmar(
                $request->user(),
                $lineas,
                TipoOrigen::from($datos['origen']),
            );
        } catch (StockInsuficienteException $e) {
            return back()->with('resultado', [
                'regla' => $e->regla(),
                'mensaje' => $e->getMessage(),
                'ok' => false,
            ]);
        } catch (ReglaDeNegocioException $e) {
            return back()->with('resultado', [
                'regla' => $e->regla(),
                'mensaje' => $e->getMessage(),
                'ok' => false,
            ]);
        }

        $this->carrito->vaciar();

        return redirect()->route('carrito')->with('resultado', [
            'regla' => null,
            'mensaje' => 'Pedido '.$pedido->pedido_id.' registrado y stock descontado.',
            'ok' => true,
        ]);
    }
}
