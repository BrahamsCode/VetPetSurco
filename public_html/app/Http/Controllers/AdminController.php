<?php

namespace App\Http\Controllers;

use App\Exceptions\ReglaDeNegocioException;
use App\Http\Requests\CrearProductoRequest;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Suscripcion;
use App\Models\Usuario;
use App\Services\InventarioService;
use App\Services\HistoriaClinicaService;
use App\Services\PedidoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Dashboard administrativo.
 * RN-05: el SKU de cada producto es irrepetible.
 * RN-06: ningun producto se vende a precio cero o negativo.
 * RN-07: el inventario nunca queda en negativo.
 * RN-08: el semaforo decide cuando reponer.
 * RN-13: el pedido recorre su secuencia de estados sin saltarse pasos.
 * RN-14: se distingue la compra puntual del despacho por suscripcion.
 * RN-20: se avisa de los controles de los proximos 15 dias.
 */
class AdminController extends Controller
{
    public function __construct(
        private readonly InventarioService $inventario,
        private readonly PedidoService $pedidos,
        private readonly HistoriaClinicaService $historias,
    ) {
    }

    public function index(): View
    {
        $productos = Producto::query()->where('activo', true)->orderBy('codigo_sku')->get();

        // RN-08: semaforo de cada producto del inventario.
        $semaforos = [];
        foreach ($productos as $producto) {
            $semaforos[$producto->producto_id] = $this->inventario->semaforo($producto)->value;
        }

        $pedidos = Pedido::query()->orderByDesc('pedido_id')->get();
        $vivos = $pedidos->where('estado', '!=', 'ANULADO');

        // RN-14: la venta recurrente es la que viene de una suscripcion.
        $recurrente = $vivos->filter(function (Pedido $pedido): bool {
            $origen = $pedido->tipo_origen;

            return ($origen instanceof \BackedEnum ? $origen->value : (string) $origen) === 'SUSCRIPCION';
        })->sum('monto_total');

        // Clientes de los pedidos, indexados para pintar su nombre.
        $clientes = $pedidos->isEmpty()
            ? collect()
            : Usuario::query()
                ->whereIn('usuario_id', $pedidos->pluck('cliente_id')->unique())
                ->get()
                ->keyBy('usuario_id');

        return view('app.admin', [
            'productos' => $productos,
            'clientes' => $clientes,
            'semaforos' => $semaforos,
            'pedidos' => $pedidos,
            'indicadores' => [
                ['valor' => $vivos->sum('monto_total'), 'etiqueta' => 'Venta registrada', 'soles' => true],
                ['valor' => $recurrente, 'etiqueta' => 'De ella, recurrente', 'soles' => true],
                ['valor' => Suscripcion::query()->where('estado', 'ACTIVA')->count(), 'etiqueta' => 'Suscripciones activas', 'soles' => false],
                ['valor' => $this->inventario->porReponer()->count(), 'etiqueta' => 'Productos por reponer', 'soles' => false],
            ],
            // RN-20: controles programados dentro de los proximos 15 dias.
            'recordatorios' => $this->historias->recordatorios(15),
        ]);
    }

    public function crearProducto(CrearProductoRequest $solicitud): RedirectResponse
    {
        // RN-05, RN-06 y RN-07 ya quedaron comprobadas en el Form Request.
        $producto = Producto::create([
            'codigo_sku' => $solicitud->string('codigo_sku')->value(),
            'nombre' => $solicitud->string('nombre')->trim()->value(),
            'categoria' => $solicitud->string('categoria')->value(),
            'precio' => $solicitud->input('precio'),
            'stock_actual' => $solicitud->integer('stock_actual'),
            'punto_reorden' => $solicitud->filled('punto_reorden') ? $solicitud->integer('punto_reorden') : 5,
            'activo' => true,
        ]);

        return back()->with('resultado', [
            'regla' => null,
            'mensaje' => 'Producto '.$producto->codigo_sku.' registrado.',
            'ok' => true,
        ]);
    }

    public function avanzarPedido(Pedido $pedido): RedirectResponse
    {
        try {
            // RN-13: PENDIENTE, PAGADO, ENVIADO, ENTREGADO; sin saltos.
            $pedido = $this->pedidos->avanzarEstado($pedido);
        } catch (ReglaDeNegocioException $e) {
            return back()->with('resultado', [
                'regla' => $e->regla(),
                'mensaje' => $e->getMessage(),
                'ok' => false,
            ]);
        }

        $estado = $pedido->estado instanceof \BackedEnum ? $pedido->estado->value : (string) $pedido->estado;

        return back()->with('resultado', [
            'regla' => null,
            'mensaje' => 'El pedido paso a '.$estado.'.',
            'ok' => true,
        ]);
    }
}
