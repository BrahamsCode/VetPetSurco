<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\EstadoCita;
use App\Exceptions\AtencionYaRegistradaException;
use App\Models\Cita;
use App\Models\HistoriaClinica;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Historia clínica digital de las mascotas.
 *
 * Reglas que hace cumplir:
 *  - RN-19: cada atención genera un único registro clínico (relación 1 a 1
 *    con la cita, impuesta además por `historias_clinicas.cita_id UNIQUE`).
 *  - RN-18: al registrar la atención la cita queda cerrada como ATENDIDA.
 *  - RN-20: se avisa 15 días antes del próximo control.
 */
final class HistoriaClinicaService
{
    /**
     * `new` en el valor por omisión permite seguir usando
     * `new HistoriaClinicaService()` sin pasar por el contenedor.
     */
    public function __construct(
        private readonly AgendaService $agenda = new AgendaService(),
    ) {
    }

    /**
     * Registra la atención de una cita y la cierra en la misma transacción.
     *
     * @param array{diagnostico?: string|null, tratamiento?: string|null, vacuna_aplicada?: string|null, proxima_fecha?: string|null} $datos
     *
     * @throws AtencionYaRegistradaException si la cita ya tiene registro clínico (RN-19)
     */
    public function registrar(Cita $cita, array $datos): HistoriaClinica
    {
        return DB::transaction(function () use ($cita, $datos): HistoriaClinica {
            // RN-19, primera capa: la cita no puede tener ya su registro.
            // Se lee con bloqueo para que dos veterinarios que guarden a la vez
            // no pasen los dos la comprobación.
            $yaExiste = HistoriaClinica::query()
                ->where('cita_id', $cita->getKey())
                ->lockForUpdate()
                ->exists();

            if ($yaExiste) {
                throw new AtencionYaRegistradaException();
            }

            $historia = new HistoriaClinica();
            $historia->mascota_id      = $cita->mascota_id;
            $historia->cita_id         = $cita->getKey();
            $historia->fecha_atencion  = now();
            $historia->diagnostico     = $datos['diagnostico']     ?? null;
            $historia->tratamiento     = $datos['tratamiento']     ?? null;
            $historia->vacuna_aplicada = $datos['vacuna_aplicada'] ?? null;
            // RN-20: la próxima fecha es la que alimenta el recordatorio.
            $historia->proxima_fecha   = ($datos['proxima_fecha'] ?? null) ?: null;

            try {
                $historia->save();
            } catch (QueryException $e) {
                // RN-19, segunda capa: el UNIQUE de cita_id es la barrera final.
                if (AgendaService::esEntradaDuplicada($e)) {
                    throw new AtencionYaRegistradaException(anterior: $e);
                }

                throw $e;
            }

            // RN-18: la misma transacción deja el desenlace de la cita. Si esto
            // fallara, el registro clínico tampoco quedaría escrito.
            $this->agenda->cerrar($cita, EstadoCita::ATENDIDA);

            return $historia;
        });
    }

    /**
     * RN-20: controles que vencen dentro de los próximos días.
     *
     * Reproduce la vista `v_recordatorios_salud` del esquema. Se consulta con
     * el constructor de consultas sobre los nombres de tabla del esquema para
     * no depender de cómo se llamen las relaciones en los modelos.
     *
     * @param  int $dias ventana de aviso, 15 días por omisión
     * @return Collection<int, array{mascota_id: int, mascota: string, cliente: string, correo: string, vacuna_aplicada: string|null, proxima_fecha: string, dias_restantes: int}>
     */
    public function recordatorios(int $dias = 15): Collection
    {
        $hoy    = Carbon::today();
        $limite = $hoy->copy()->addDays(max($dias, 0));

        $filas = DB::table('historias_clinicas as h')
            ->join('mascotas as m', 'm.mascota_id', '=', 'h.mascota_id')
            ->join('usuarios as u', 'u.usuario_id', '=', 'm.cliente_id')
            ->whereNotNull('h.proxima_fecha')
            ->whereBetween('h.proxima_fecha', [$hoy->toDateString(), $limite->toDateString()])
            ->orderBy('h.proxima_fecha')
            ->get([
                'h.historia_id',
                'h.mascota_id',
                'h.vacuna_aplicada',
                'h.proxima_fecha',
                'm.nombre as mascota',
                'u.nombre as cliente',
                'u.correo',
            ]);

        return $filas
            ->map(static function (object $fila) use ($hoy): array {
                $proxima = Carbon::parse((string) $fila->proxima_fecha)->startOfDay();

                return [
                    'mascota_id'      => (int) $fila->mascota_id,
                    'mascota'         => (string) $fila->mascota,
                    'cliente'         => (string) $fila->cliente,
                    'correo'          => (string) $fila->correo,
                    'vacuna_aplicada' => $fila->vacuna_aplicada === null ? null : (string) $fila->vacuna_aplicada,
                    'proxima_fecha'   => $proxima->toDateString(),
                    // Días que faltan para el control; 0 significa que es hoy.
                    'dias_restantes'  => (int) $hoy->diffInDays($proxima, false),
                ];
            })
            ->sortBy('dias_restantes')
            ->values();
    }
}
