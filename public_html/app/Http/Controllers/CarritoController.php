<?php

namespace App\Http\Controllers;

use App\Enums\ModalidadEntrega;
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
use Illuminate\Validation\Rule;
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

    public function index(Request $request): View
    {
        $lineas = $this->carrito->items();

        // Miniatura de cada linea, indexada por producto.
        $imagenes = Producto::query()
            ->whereIn('producto_id', array_map(fn ($l) => (int) data_get($l, 'producto_id'), $lineas))
            ->get()
            ->mapWithKeys(fn (Producto $p) => [$p->producto_id => $p->urlImagen()]);

        return view('app.carrito', [
            'lineas' => $lineas,
            'imagenes' => $imagenes,
            'total' => $this->carrito->total(),
            'unidades' => $this->carrito->unidades(),
            // Para precargar la direccion del delivery y calcular el envio en pantalla.
            'direccionPerfil' => (string) $request->user()->direccion,
            'envioCentimos' => PedidoService::ENVIO_CENTIMOS,
            'envioGratisDesdeCentimos' => PedidoService::ENVIO_GRATIS_DESDE_CENTIMOS,
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

        $datos = $request->validate([
            'modalidad_entrega' => ['required', Rule::enum(ModalidadEntrega::class)],
            'direccion_entrega' => ['required_if:modalidad_entrega,DELIVERY', 'nullable', 'string', 'min:8', 'max:255'],
        ], [
            'modalidad_entrega.required' => 'Elige si quieres delivery o recojo en tienda.',
            'direccion_entrega.required_if' => 'Para el delivery necesitamos la direccion de entrega.',
            'direccion_entrega.min' => 'Escribe la direccion completa: calle, numero y referencia.',
        ]);
        $modalidad = ModalidadEntrega::from($datos['modalidad_entrega']);
        $direccion = trim((string) ($datos['direccion_entrega'] ?? ''));

        try {
            // RN-12: transaccion con bloqueo de filas; o entra todo o no entra nada.
            // RN-14: lo que sale del carrito es siempre una compra directa; los
            // despachos recurrentes los emite DespachoService al vencer el plan.
            $pedido = $this->pedidos->confirmar(
                $request->user(),
                $lineas,
                TipoOrigen::COMPRA_DIRECTA,
                $modalidad,
                $direccion,
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

        // La primera direccion de delivery queda en el perfil para la proxima
        // compra y para los despachos de suscripcion.
        if ($modalidad === ModalidadEntrega::DELIVERY && blank($request->user()->direccion)) {
            $request->user()->forceFill(['direccion' => $direccion])->save();
        }

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
