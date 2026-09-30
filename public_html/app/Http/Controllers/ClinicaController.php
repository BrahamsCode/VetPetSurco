<?php

namespace App\Http\Controllers;

use App\Enums\EstadoCita;
use App\Exceptions\ReglaDeNegocioException;
use App\Http\Requests\RegistrarAtencionRequest;
use App\Models\Cita;
use App\Models\HistoriaClinica;
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
        $hoy = today();

        $citas = Cita::query()
            ->with('mascota.cliente')
            ->where('veterinario_id', $veterinarioId)
            ->orderBy('fecha_hora')
            ->get();

        $reservadas = $citas->filter(fn (Cita $c) => $this->agenda->estadoDe($c) === EstadoCita::RESERVADA);

        // La agenda se ordena por lo que el veterinario tiene que hacer:
        //  - pendientes: dias anteriores que quedaron sin desenlace (RN-18);
        //  - hoy: todas las del dia, tambien las ya cerradas, para ver el avance;
        //  - proximas: lo que viene, solo para consultar.
        $pendientes = $reservadas->filter(fn (Cita $c) => $c->fecha_hora->lt($hoy))->values();
        $deHoy = $citas->filter(fn (Cita $c) => $c->fecha_hora->isSameDay($hoy))->values();
        $proximas = $reservadas->filter(fn (Cita $c) => $c->fecha_hora->gte($hoy->copy()->addDay()))->values();

        // Que acciones admite cada cita ahora mismo; la misma regla que se
        // vuelve a comprobar al recibir el formulario.
        $acciones = $citas->mapWithKeys(fn (Cita $c) => [$c->cita_id => [
            'atender' => $this->agenda->sePuedeAtender($c),
            'no_asistio' => $this->agenda->sePuedeMarcarInasistencia($c),
        ]]);

        $historias = $citas->isEmpty()
            ? collect()
            : HistoriaClinica::query()
                ->with(['mascota', 'cita'])
                ->whereIn('cita_id', $citas->pluck('cita_id'))
                ->orderByDesc('fecha_atencion')
                ->get();

        // La cita seleccionada llega por la barra de direcciones al pulsar
        // "Atender". Solo se abre si de verdad se puede atender ahora.
        $citaElegida = null;
        $avisoEleccion = null;
        if ($request->filled('cita')) {
            $pedida = $citas->firstWhere('cita_id', (int) $request->query('cita'));

            if ($pedida !== null && $this->agenda->sePuedeAtender($pedida)) {
                $citaElegida = $pedida;
            } elseif ($pedida !== null) {
                $avisoEleccion = $this->agenda->estadoDe($pedida) === EstadoCita::RESERVADA
                    ? 'Esa cita es del '.$pedida->fecha_hora->format('d/m/Y').'; se atiende ese dia.'
                    : 'Esa cita ya esta cerrada como '.$this->agenda->estadoDe($pedida)->etiqueta().'.';
            }
        }

        // Antecedentes de la mascota elegida: toda su historia, la haya
        // atendido quien la haya atendido, para no tratar a ciegas.
        $antecedentes = $citaElegida === null
            ? collect()
            : HistoriaClinica::query()
                ->where('mascota_id', $citaElegida->mascota_id)
                ->orderByDesc('fecha_atencion')
                ->limit(5)
                ->get();

        return view('app.clinica', [
            'pendientes' => $pendientes,
            'deHoy' => $deHoy,
            'proximas' => $proximas,
            'acciones' => $acciones,
            'historias' => $historias,
            'citaElegida' => $citaElegida,
            'avisoEleccion' => $avisoEleccion,
            'antecedentes' => $antecedentes,
            'proximaSugerida' => now()->addDays(30)->toDateString(),
        ]);
    }

    public function atender(RegistrarAtencionRequest $solicitud, Cita $cita): RedirectResponse
    {
        abort_if((int) $cita->veterinario_id !== (int) $solicitud->user()->usuario_id, 403);

        // Una cita futura todavia no tiene atencion que registrar.
        if ($this->agenda->estadoDe($cita) === EstadoCita::RESERVADA && ! $this->agenda->sePuedeAtender($cita)) {
            return back()->withInput()->with('resultado', [
                'regla' => 'RN-18',
                'mensaje' => 'Esa cita es del '.$cita->fecha_hora->format('d/m/Y').'; se atiende ese dia.',
                'ok' => false,
            ]);
        }

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

        // No se puede dar por ausente a alguien cuya cita todavia no llega.
        if ($datos['desenlace'] === EstadoCita::NO_ASISTIO->value
            && $this->agenda->estadoDe($cita) === EstadoCita::RESERVADA
            && ! $this->agenda->sePuedeMarcarInasistencia($cita)) {
            return back()->with('resultado', [
                'regla' => 'RN-18',
                'mensaje' => 'La cita es el '.$cita->fecha_hora->format('d/m/Y \a \l\a\s H:i')
                    .'; la inasistencia se marca cuando ya paso esa hora.',
                'ok' => false,
            ]);
        }

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
            'mensaje' => 'La cita quedo como '.EstadoCita::from($datos['desenlace'])->etiqueta().'.',
            'ok' => true,
        ]);
    }
}
