<?php

namespace App\Http\Controllers;

use App\Enums\EstadoCita;
use App\Exceptions\ReglaDeNegocioException;
use App\Http\Requests\RegistrarAtencionRequest;
use App\Models\Cita;
use App\Models\HistoriaClinica;
use App\Models\Mascota;
use App\Services\AgendaService;
use App\Services\HistoriaClinicaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Ficha clinica del veterinario.
 * RN-18: toda cita registra su desenlace.
 * RN-19: cada atencion genera un unico registro clinico.
 * RN-20: la fecha del proximo control alimenta el recordatorio.
 */
class ClinicaController extends Controller
{
    public function __construct(
        private readonly AgendaService $agenda,
        private readonly HistoriaClinicaService $historias,
    ) {
    }

    public function index(Request $request): View
    {
        $veterinarioId = $request->user()->usuario_id;

        $citas = Cita::query()
            ->where('veterinario_id', $veterinarioId)
            ->orderByDesc('fecha_hora')
            ->get();

        $historias = $citas->isEmpty()
            ? collect()
            : HistoriaClinica::query()
                ->whereIn('cita_id', $citas->pluck('cita_id'))
                ->orderByDesc('historia_id')
                ->get();

        // La cita seleccionada llega por la barra de direcciones al pulsar "Atender".
        $citaElegida = null;
        if ($request->filled('cita')) {
            $citaElegida = $citas->firstWhere('cita_id', (int) $request->query('cita'));
        }

        // Mascotas de esas citas, indexadas por su clave primaria.
        $mascotas = $citas->isEmpty()
            ? collect()
            : Mascota::query()
                ->whereIn('mascota_id', $citas->pluck('mascota_id')->merge($historias->pluck('mascota_id'))->unique())
                ->get()
                ->keyBy('mascota_id');

        return view('app.clinica', [
            'citas' => $citas,
            'mascotas' => $mascotas,
            'historias' => $historias,
            'citaElegida' => $citaElegida,
            'proximaSugerida' => now()->addDays(30)->toDateString(),
        ]);
    }

    public function atender(RegistrarAtencionRequest $solicitud, Cita $cita): RedirectResponse
    {
        abort_if((int) $cita->veterinario_id !== (int) $solicitud->user()->usuario_id, 403);

        try {
            // RN-19 y RN-20 viven dentro del servicio.
            $this->historias->registrar($cita, [
                'diagnostico' => $solicitud->string('diagnostico')->trim()->value(),
                'tratamiento' => $solicitud->string('tratamiento')->trim()->value(),
                'vacuna_aplicada' => $solicitud->string('vacuna_aplicada')->trim()->value(),
                'proxima_fecha' => $solicitud->input('proxima_fecha'),
            ]);
        } catch (ReglaDeNegocioException $e) {
            return back()->with('resultado', [
                'regla' => $e->regla(),
                'mensaje' => $e->getMessage(),
                'ok' => false,
            ]);
        }

        return redirect()->route('clinica')->with('resultado', [
            'regla' => null,
            'mensaje' => 'Atencion registrada en la historia clinica.',
            'ok' => true,
        ]);
    }

    public function desenlace(Request $request, Cita $cita): RedirectResponse
    {
        abort_if((int) $cita->veterinario_id !== (int) $request->user()->usuario_id, 403);

        // RN-18: el desenlace tiene que ser uno de los estados previstos.
        $datos = $request->validate([
            'desenlace' => ['required', 'in:ATENDIDA,CANCELADA,NO_ASISTIO'],
        ], [
            'desenlace.required' => 'Indica el desenlace de la cita.',
            'desenlace.in' => 'Desenlace no valido.',
        ]);

        try {
            $this->agenda->cerrar($cita, EstadoCita::from($datos['desenlace']));
        } catch (ReglaDeNegocioException $e) {
            return back()->with('resultado', [
                'regla' => $e->regla(),
                'mensaje' => $e->getMessage(),
                'ok' => false,
            ]);
        }

        return back()->with('resultado', [
            'regla' => null,
            'mensaje' => 'La cita quedo como '.$datos['desenlace'].'.',
            'ok' => true,
        ]);
    }
}
