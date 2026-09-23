<?php

namespace App\Http\Controllers;

use App\Mail\MensajeContactoMail;
use App\Services\CorreoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Sitio institucional de VetPet Surco: las cuatro paginas publicas.
 */
class PaginaPublicaController extends Controller
{
    public function inicio(): View
    {
        return view('publico.inicio');
    }

    public function nosotros(): View
    {
        return view('publico.nosotros');
    }

    public function productos(): View
    {
        return view('publico.productos');
    }

    public function contacto(): View
    {
        return view('publico.contacto');
    }

    /**
     * Mensaje del formulario de contacto: del cliente al buzon de la tienda.
     */
    public function recibirContacto(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:80'],
            'correo' => ['required', 'email', 'max:120'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'mascota' => ['nullable', 'string', 'max:60'],
            'motivo' => ['required', 'in:consulta,vacuna,grooming,suscripcion,pedido'],
            'mensaje' => ['required', 'string', 'max:800'],
        ], [
            'nombre.required' => 'Escribe tu nombre para saber quien escribe.',
            'correo.required' => 'Necesitamos tu correo para poder responderte.',
            'correo.email' => 'Ese correo no tiene un formato valido.',
            'motivo.in' => 'Elige el motivo de tu mensaje.',
            'mensaje.required' => 'Escribe tu mensaje.',
            'mensaje.max' => 'Tu mensaje es muy largo: resumelo en 800 caracteres.',
        ]);

        $etiquetas = [
            'consulta' => 'Reservar consulta veterinaria',
            'vacuna' => 'Vacunación o desparasitación',
            'grooming' => 'Baño y grooming',
            'suscripcion' => 'Suscripción mensual de alimento',
            'pedido' => 'Consulta sobre un pedido',
        ];

        $datos['motivo_texto'] = $etiquetas[$datos['motivo']];

        // Del cliente a la empresa: va al buzon con la cola de su motivo y
        // Reply-To del cliente, para poder contestarle de inmediato.
        app(CorreoService::class)->enviar(
            (string) config('correos.buzon'),
            new MensajeContactoMail($datos),
        );

        return back()->with('contacto_ok', true);
    }
}
