<?php

namespace App\Http\Controllers;

use App\Enums\Servicio;
use App\Exceptions\ReglaDeNegocioException;
use App\Http\Requests\ReservarCitaRequest;
use App\Mail\ConfirmacionCitaMail;
use App\Models\Cita;
use App\Models\Mascota;
use App\Models\Usuario;
use App\Services\AgendaService;
use App\Services\CorreoService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Agenda de citas del cliente.
 * RN-04: solo se ofrecen las mascotas de la cuenta que inicio sesion.
 * RN-17: un veterinario no atiende dos citas a la misma hora.
 */
class CitaController extends Controller
{
    /** Bloques de atencion del local. */
    private const HORARIOS = ['09:00', '10:00', '11:00', '12:00', '15:00', '16:00', '17:00', '18:00'];

    public function __construct(private readonly AgendaService $agenda)
    {
    }

    public function index(Request $request): View
    {
        $cliente = $request->user();

        // RN-04: el desplegable solo ofrece las mascotas de este cliente.
        $mias = Mascota::deCliente($cliente)->orderBy('nombre')->get();

        $veterinarios = Usuario::query()->where('rol', 'VETERINARIO')->orderBy('nombre')->get();

        $veterinarioId = (int) $request->query('veterinario_id', (string) ($veterinarios->first()?->usuario_id ?? 0));
        $veterinario = $veterinarios->firstWhere('usuario_id', $veterinarioId) ?? $veterinarios->first();

        $fecha = (string) $request->query('fecha', Carbon::today()->addDays(2)->toDateString());

        // RN-17: los bloques ya tomados se pintan deshabilitados.
        $ocupados = $veterinario !== null
            ? $this->agenda->horariosOcupados($veterinario, $fecha)
            : [];

        $citas = $mias->isEmpty()
            ? collect()
            : Cita::query()
                ->whereIn('mascota_id', $mias->pluck('mascota_id'))
                ->orderBy('fecha_hora')
                ->get();

        return view('app.citas', [
            'mascotas' => $mias,
            'veterinarios' => $veterinarios,
            'veterinarioId' => $veterinario?->usuario_id,
            'mascotaId' => (int) $request->query('mascota_id', (string) ($mias->first()?->mascota_id ?? 0)),
            'servicio' => (string) $request->query('servicio', Servicio::CONSULTA->value),
            'fecha' => $fecha,
            'fechaMinima' => Carbon::today()->toDateString(),
            'horarios' => self::HORARIOS,
            'ocupados' => $ocupados,
            'horaElegida' => (string) $request->query('hora', ''),
            'citas' => $citas,
        ]);
    }

    public function reservar(ReservarCitaRequest $solicitud): RedirectResponse
    {
        $mascota = Mascota::query()->findOrFail($solicitud->integer('mascota_id'));
        $veterinario = Usuario::query()->findOrFail($solicitud->integer('veterinario_id'));

        try {
            // RN-17: la unicidad del horario la hace cumplir el servicio.
            $cita = $this->agenda->reservar(
                $mascota,
                $veterinario,
                Servicio::from($solicitud->string('servicio')->value()),
                Carbon::parse($solicitud->fechaHora()),
            );
        } catch (ReglaDeNegocioException $e) {
            return back()->withInput()->with('resultado', [
                'regla' => $e->regla(),
                'mensaje' => $e->getMessage(),
                'ok' => false,
            ]);
        }

        // Confirmacion por correo: un fallo del correo no rompe la reserva.
        app(CorreoService::class)->enviar(
            (string) $solicitud->user()->correo,
            new ConfirmacionCitaMail($cita, $mascota, $veterinario, $solicitud->user()),
        );

        return redirect()->route('citas', [
            'mascota_id' => $mascota->mascota_id,
            'veterinario_id' => $veterinario->usuario_id,
            'servicio' => $solicitud->string('servicio')->value(),
            'fecha' => $solicitud->string('fecha')->value(),
        ])->with('resultado', [
            'regla' => null,
            'mensaje' => 'Cita reservada para el '.$solicitud->string('fecha').' a las '.$solicitud->string('hora').'.',
            'ok' => true,
        ]);
    }
}
