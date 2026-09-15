<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Services\CarritoService;
use App\Services\InventarioService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Catalogo del cliente.
 * RN-06: el precio que se muestra es el precio vigente del producto.
 * RN-08: el semaforo de inventario acompana a cada tarjeta.
 */
class CatalogoController extends Controller
{
    public function __construct(
        private readonly InventarioService $inventario,
        private readonly CarritoService $carrito,
    ) {
    }

    public function index(Request $request): View
    {
        $categoria = (string) $request->query('categoria', '');

        $productos = Producto::query()
            ->where('activo', true)
            ->when($categoria !== '', fn ($consulta) => $consulta->where('categoria', $categoria))
            ->orderBy('nombre')
            ->get();

        // RN-08: se calcula el semaforo de cada producto una sola vez.
        $semaforos = [];
        foreach ($productos as $producto) {
            $semaforos[$producto->producto_id] = $this->inventario->semaforo($producto)->value;
        }

        return view('app.catalogo', [
            'productos' => $productos,
            'semaforos' => $semaforos,
            'categoria' => $categoria,
            'unidades' => $this->carrito->unidades(),
        ]);
    }
}
