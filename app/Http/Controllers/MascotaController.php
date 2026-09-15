<?php

namespace App\Http\Controllers;

use App\Models\Mascota;
use App\Models\Producto;
use App\Models\Suscripcion;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Mis mascotas y suscripciones del cliente.
 * RN-04: toda mascota pertenece a un solo cliente registrado.
 * RN-16: cada plan lleva su frecuencia y su fecha de proximo despacho.
 */
class MascotaController extends Controller
{
    public function index(Request $request): View
    {
        $cliente = $request->user();

        // RN-04: aqui solo aparecen las mascotas de esta cuenta.
        $mascotas = Mascota::deCliente($cliente)->orderBy('nombre')->get();

        $suscripciones = Suscripcion::query()
            ->where('cliente_id', $cliente->usuario_id)
            ->orderBy('suscripcion_id')
            ->get();

        // Productos de las suscripciones, indexados para pintarlos en la tabla.
        $productos = $suscripciones->isEmpty()
            ? collect()
            : Producto::query()
                ->whereIn('producto_id', $suscripciones->pluck('producto_id'))
                ->get()
                ->keyBy('producto_id');

        return view('app.mascotas', [
            'mascotas' => $mascotas,
            'suscripciones' => $suscripciones,
            'productos' => $productos,
        ]);
    }
}
