<?php

namespace App\Http\Controllers;

use App\Models\Mascota;
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
        $clienteId = $request->user()->getAuthIdentifier();

        // RN-04: aqui solo aparecen las mascotas de esta cuenta.
        $mascotas = Mascota::deCliente($clienteId)->orderBy('nombre')->get();

        $suscripciones = Suscripcion::query()
            ->where('cliente_id', $clienteId)
            ->orderBy('suscripcion_id')
            ->get();

        return view('app.mascotas', [
            'mascotas' => $mascotas,
            'suscripciones' => $suscripciones,
        ]);
    }
}
