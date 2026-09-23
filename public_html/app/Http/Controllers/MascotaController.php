<?php

namespace App\Http\Controllers;

use App\Enums\EstadoSuscripcion;
use App\Models\Mascota;
use App\Models\Producto;
use App\Models\Suscripcion;
use App\Services\SuscripcionService;
use Illuminate\Http\RedirectResponse;
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

        // RN-22: lo que cada mascota ya tiene vigente no se vuelve a ofrecer.
        // Una pausada tambien cuenta: sigue siendo un contrato, solo detenido.
        $vigentesPorMascota = $suscripciones
            ->whereIn('estado', [EstadoSuscripcion::ACTIVA, EstadoSuscripcion::PAUSADA])
            ->groupBy('mascota_id')
            ->map(fn ($grupo) => $grupo->pluck('producto_id')->map(intval(...))->values()->all());

        return view('app.mascotas', [
            'mascotas' => $mascotas,
            'suscripciones' => $suscripciones,
            'productos' => $productos,
            // Para el formulario de contratacion.
            'catalogo' => Producto::query()->activos()->orderBy('nombre')->get(),
            'planes' => SuscripcionService::planes(),
            'vigentesPorMascota' => $vigentesPorMascota,
        ]);
    }

    /**
     * Alta de mascota. — RN-04: siempre queda a nombre de la cuenta de la
     * sesion; si el formulario envia otro cliente_id, se ignora.
     */
    public function guardar(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:60'],
            'especie' => ['required', 'in:PERRO,GATO,OTRO'],
            'raza' => ['nullable', 'string', 'max:60'],
            'fecha_nacimiento' => ['nullable', 'date', 'before_or_equal:today'],
            'peso_kg' => ['nullable', 'numeric', 'min:0.1', 'max:200'],
            'alergias' => ['nullable', 'string', 'max:200'],
        ], [
            'nombre.required' => 'Escribe el nombre de tu mascota.',
            'nombre.max' => 'El nombre es muy largo: usa 60 caracteres o menos.',
            'especie.required' => 'Elige la especie de tu mascota.',
            'especie.in' => 'La especie debe ser PERRO, GATO u OTRO.',
            'raza.max' => 'La raza es muy larga: usa 60 caracteres o menos.',
            'fecha_nacimiento.date' => 'Esa fecha de nacimiento no es valida.',
            'fecha_nacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
            'peso_kg.numeric' => 'El peso debe ser un numero en kilogramos.',
            'peso_kg.min' => 'El peso debe ser mayor que cero.',
            'peso_kg.max' => 'Revisa el peso: no puede superar los 200 kg.',
            'alergias.max' => 'Las alergias son muy largas: usa 200 caracteres o menos.',
        ]);

        Mascota::create([
            'cliente_id' => (int) $request->user()->usuario_id,
            'nombre' => $datos['nombre'],
            'especie' => $datos['especie'],
            'raza' => $datos['raza'] ?? null,
            'fecha_nacimiento' => $datos['fecha_nacimiento'] ?? null,
            'peso_kg' => $datos['peso_kg'] ?? null,
            'alergias' => $datos['alergias'] ?? null,
        ]);

        return back()->with('resultado', [
            'regla' => null,
            'mensaje' => 'Mascota registrada en tu cuenta.',
            'ok' => true,
        ]);
    }
}
