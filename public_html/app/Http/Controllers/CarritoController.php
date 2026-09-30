<?php

namespace App\Http\Controllers;

use App\Enums\TipoOrigen;
use App\Exceptions\ReglaDeNegocioException;
use App\Exceptions\StockInsuficienteException;
use App\Http\Requests\AgregarAlCarritoRequest;
use App\Mail\ConfirmacionPedidoMail;
use App\Models\Producto;
use App\Services\CarritoService;
use App\Services\CorreoService;
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
            // Atajo para llegar al carrito sin buscarlo (el aviso lo muestra como boton).
            'accion' => ['url' => route('carrito'), 'texto' => 'Ir al carrito'],
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
            // RN-14: lo que sale del carrito es siempre una compra directa; los
            // despachos recurrentes los emite DespachoService al vencer el plan.
            $pedido = $this->pedidos->confirmar(
                $request->user(),
                $lineas,
                TipoOrigen::COMPRA_DIRECTA,
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

        // Confirmacion por correo con el detalle del pedido.
        app(CorreoService::class)->enviar(
            (string) $request->user()->correo,
            new ConfirmacionPedidoMail($pedido->load('detalles.producto')),
        );

        // RN-21: el pedido queda PENDIENTE; el cobro se hace en la pantalla de pago.
        return redirect()->route('pago', $pedido)->with('resultado', [
            'regla' => null,
            'mensaje' => 'Pedido '.$pedido->pedido_id.' registrado y stock descontado. Falta pagarlo.',
            'ok' => true,
        ]);
    }
}
