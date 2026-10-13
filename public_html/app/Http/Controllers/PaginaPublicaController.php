<?php

namespace App\Http\Controllers;

use App\Mail\BienvenidaBoletinMail;
use App\Mail\MensajeContactoMail;
use App\Models\SuscriptorBoletin;
use App\Services\CorreoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
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

    public function terminos(): View
    {
        return view('publico.terminos');
    }

    public function privacidad(): View
    {
        return view('publico.privacidad');
    }

    /* -------------------------------------------------------------------
     | Blog corporativo: el caso del negocio, publicado
     * ------------------------------------------------------------------- */

    public function blog(): View
    {
        return view('publico.blog', ['articulos' => $this->articulos()]);
    }

    public function articulo(string $ruta): View
    {
        $articulo = $this->articulos()[$ruta] ?? abort(404);

        return view('publico.blog-articulo', ['articulo' => $articulo, 'ruta' => $ruta]);
    }

    /** @return array<string, array<string, mixed>> */
    private function articulos(): array
    {
        return require base_path('resources/blog/articulos.php');
    }

    /* -------------------------------------------------------------------
     | Boletin: el canal propio de e-marketing
     * ------------------------------------------------------------------- */

    /**
     * Alta en el boletin. El consentimiento es explicito y separado de los
     * avisos del servicio, como exige la Ley N.o 29733.
     */
    public function suscribirBoletin(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'correo' => ['required', 'email:rfc', 'max:150'],
            'nombre' => ['nullable', 'string', 'max:100'],
            'acepto' => ['accepted'],
        ], [
            'acepto.accepted' => 'Necesitamos tu autorización para escribirte.',
        ]);

        $suscriptor = SuscriptorBoletin::query()->firstOrNew(['correo' => $datos['correo']]);

        if ($suscriptor->exists && $suscriptor->activo) {
            return back()->with('boletin', 'Ese correo ya está suscrito. ¡Gracias por acompañarnos!');
        }

        $suscriptor->nombre = $datos['nombre'] ?? $suscriptor->nombre;
        $suscriptor->token_baja = $suscriptor->token_baja ?: Str::random(48);
        $suscriptor->activo = true;
        $suscriptor->fecha_baja = null;
        $suscriptor->fecha_alta = now();
        $suscriptor->save();

        app(CorreoService::class)->enviar($suscriptor->correo, new BienvenidaBoletinMail($suscriptor));

        return back()->with('boletin', 'Listo: te llegará el boletín una vez al mes. Revisa tu correo.');
    }

    /** Baja con un clic, sin iniciar sesion: es un derecho, no un tramite. */
    public function bajaBoletin(string $token): View
    {
        $suscriptor = SuscriptorBoletin::query()->where('token_baja', $token)->first();

        if ($suscriptor !== null && $suscriptor->activo) {
            $suscriptor->activo = false;
            $suscriptor->fecha_baja = now();
            $suscriptor->save();
        }

        return view('publico.boletin-baja', ['suscriptor' => $suscriptor]);
    }

    /* -------------------------------------------------------------------
     | SEO
     * ------------------------------------------------------------------- */

    /** Mapa del sitio para los buscadores: solo lo publico. */
    public function sitemap(): Response
    {
        $paginas = [
            ['ruta' => route('inicio'), 'prioridad' => '1.0', 'frecuencia' => 'weekly'],
            ['ruta' => route('nosotros'), 'prioridad' => '0.7', 'frecuencia' => 'monthly'],
            ['ruta' => route('productos'), 'prioridad' => '0.9', 'frecuencia' => 'weekly'],
            ['ruta' => route('contacto'), 'prioridad' => '0.6', 'frecuencia' => 'monthly'],
            ['ruta' => route('blog'), 'prioridad' => '0.8', 'frecuencia' => 'weekly'],
            ['ruta' => route('terminos'), 'prioridad' => '0.3', 'frecuencia' => 'yearly'],
            ['ruta' => route('privacidad'), 'prioridad' => '0.3', 'frecuencia' => 'yearly'],
        ];

        foreach ($this->articulos() as $slug => $articulo) {
            $paginas[] = [
                'ruta' => route('blog.articulo', $slug),
                'prioridad' => '0.7',
                'frecuencia' => 'yearly',
                'fecha' => $articulo['fecha'],
            ];
        }

        return response()
            ->view('publico.sitemap', ['paginas' => $paginas])
            ->header('Content-Type', 'application/xml');
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
