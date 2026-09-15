<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\EstadoCita;
use App\Enums\Servicio;
use App\Exceptions\HorarioOcupadoException;
use App\Models\Cita;
use App\Models\Mascota;
use App\Models\Usuario;
use Carbon\CarbonInterface;
use DateTimeInterface;
use DomainException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;

/**
 * Agenda de citas veterinarias.
 *
 * Reglas que hace cumplir:
 *  - RN-17: un veterinario no atiende dos citas a la misma hora.
 *  - RN-18: toda cita registra su desenlace.
 *
 * RN-17 se defiende en dos capas a propósito: la comprobación previa da un
 * mensaje claro al cliente, y la restricción `UNIQUE KEY uk_agenda
 * (veterinario_id, fecha_hora)` es la barrera final que ninguna carrera entre
 * dos reservas simultáneas puede saltarse.
 */
final class AgendaService
{
    /**
     * Reserva una cita.
     *
     * Primera capa (aplicación): busca un choque de horario antes de insertar.
     * Segunda capa (motor): si otra reserva se coló entre la comprobación y el
     * INSERT, el UNIQUE rechaza la fila y ese rechazo se traduce a la misma
     * excepción de negocio.
     *
     * @throws HorarioOcupadoException si el veterinario ya tiene esa hora tomada (RN-17)
     */
    public function reservar(Mascota $m, Usuario $vet, Servicio $s, CarbonInterface $fechaHora): Cita
    {
        // La agenda trabaja al minuto: se descartan los segundos para que la
        // comprobación previa y el UNIQUE comparen exactamente el mismo valor.
        $momento = $fechaHora->format('Y-m-d H:i') . ':00';

        // RN-17, primera capa: choque de horario con el mismo veterinario.
        // Una cita CANCELADA no ocupa la agenda, igual que en el prototipo.
        $ocupado = Cita::query()
            ->where('veterinario_id', $vet->getKey())
            ->where('fecha_hora', $momento)
            ->where('estado', '<>', EstadoCita::CANCELADA->value)
            ->exists();

        if ($ocupado) {
            throw new HorarioOcupadoException();
        }

        $cita = new Cita();
        $cita->mascota_id     = $m->getKey();
        $cita->veterinario_id = $vet->getKey();
        $cita->servicio       = $s;
        $cita->fecha_hora     = $momento;
        $cita->estado         = EstadoCita::RESERVADA;   // RN-18: nace sin desenlace

        try {
            $cita->save();
        } catch (QueryException $e) {
            // RN-17, segunda capa: uk_agenda rechazó la fila. La base de datos
            // es la barrera final; aquí solo se traduce su negativa.
            if (self::esEntradaDuplicada($e)) {
                throw new HorarioOcupadoException(anterior: $e);
            }

            throw $e;
        }

        return $cita;
    }

    /**
     * Horas ya tomadas por un veterinario en una fecha, en formato «HH:MM».
     *
     * Alimenta el selector de horarios para que el cliente no llegue siquiera
     * a intentar una hora ocupada (RN-17).
     *
     * @param  string $fecha fecha en formato «AAAA-MM-DD»
     * @return array<int, string>
     */
    public function horariosOcupados(Usuario $vet, string $fecha): array
    {
        $citas = Cita::query()
            ->where('veterinario_id', $vet->getKey())
            ->whereDate('fecha_hora', $fecha)
            ->where('estado', '<>', EstadoCita::CANCELADA->value)
            ->orderBy('fecha_hora')
            ->get();

        $horas = [];

        foreach ($citas as $cita) {
            $momento = $cita->fecha_hora;

            $horas[] = $momento instanceof DateTimeInterface
                ? $momento->format('H:i')
                : Carbon::parse((string) $momento)->format('H:i');
        }

        return array_values(array_unique($horas));
    }

    /**
     * RN-18: toda cita registra su desenlace (ATENDIDA, CANCELADA o NO_ASISTIO).
     *
     * Solo una cita RESERVADA puede cerrarse, y una vez cerrada su desenlace
     * ya no se reescribe.
     *
     * @throws DomainException si el desenlace no es válido o la cita ya está cerrada
     */
    public function cerrar(Cita $cita, EstadoCita $desenlace): Cita
    {
        if ($desenlace === EstadoCita::RESERVADA) {
            throw new DomainException('RESERVADA no es un desenlace: la cita debe quedar ATENDIDA, CANCELADA o NO_ASISTIO.');
        }

        $actual = $this->estadoDe($cita);

        if ($actual !== EstadoCita::RESERVADA) {
            throw new DomainException('La cita ya tiene un desenlace registrado: ' . $actual->value . '.');
        }

        $cita->estado = $desenlace;
        $cita->save();

        return $cita;
    }

    /**
     * Lee el estado de la cita admitiendo tanto el enum casteado por el modelo
     * como el texto crudo de la columna ENUM.
     */
    private function estadoDe(Cita $cita): EstadoCita
    {
        $estado = $cita->estado;

        return $estado instanceof EstadoCita
            ? $estado
            : EstadoCita::from((string) $estado);
    }

    /**
     * ¿El motor rechazó la fila por una clave única repetida?
     *
     * MySQL responde «Duplicate entry ... for key 'uk_agenda'» (errno 1062);
     * SQLite, que es lo que usa la suite de pruebas, responde
     * «UNIQUE constraint failed: citas.veterinario_id, citas.fecha_hora».
     */
    public static function esEntradaDuplicada(QueryException $e): bool
    {
        $codigoMotor = isset($e->errorInfo[1]) ? (int) $e->errorInfo[1] : 0;
        $mensaje     = $e->getMessage();

        return $codigoMotor === 1062
            || str_contains($mensaje, 'Duplicate entry')
            || str_contains($mensaje, 'UNIQUE constraint failed');
    }
}
