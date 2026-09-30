<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Services\AsistenteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Asistente de ayuda Pelusa — Nivel 2: responde con datos reales de la
 * cuenta. — RN-01 y RN-04: solo consulta lo del propio usuario autenticado.
 */
class AyudaController extends Controller
{
    public function __construct(
        private readonly AsistenteService $asistente,
    ) {
    }

    /** Guia del menu del chat (fuente unica: config/asistente.php). */
    public function guias(): JsonResponse
    {
        return response()->json([
            'guia' => $this->asistente->guia(),
            'saltos' => [
                'catalogo' => route('catalogo'),
                'carrito' => route('carrito'),
                'citas' => route('citas'),
                'mascotas' => route('mascotas'),
                'contacto' => route('contacto'),
            ],
        ]);
    }

    /** Responde una pregunta escrita por el cliente. */
    public function consultar(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'mensaje' => ['required', 'string', 'max:300'],
        ], [
            'mensaje.required' => 'Escribe tu pregunta para poder ayudarte.',
            'mensaje.max' => 'Tu pregunta es muy larga: escríbela en 300 caracteres o menos.',
        ]);

        $usuario = $request->user();

        return response()->json(
            $this->asistente->responder($datos['mensaje'], $usuario instanceof Usuario ? $usuario : $request->user())
        );
    }
}
