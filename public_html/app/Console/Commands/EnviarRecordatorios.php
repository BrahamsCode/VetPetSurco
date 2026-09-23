<?php

namespace App\Console\Commands;

use App\Mail\RecordatorioVacunaMail;
use App\Services\CorreoService;
use App\Services\HistoriaClinicaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * RN-20: recordatorios de vacunas y controles por correo.
 *
 * Recorre la ventana de controles proximos y avisa a cada cliente. Cada
 * control se avisa UNA sola vez por fecha: la llave de cache deduplica los
 * envios aunque el comando corra todos los dias o mas de una vez al dia.
 */
final class EnviarRecordatorios extends Command
{
    /** @var string */
    protected $signature = 'vetpet:recordatorios {--dias=15 : Ventana de aviso en dias}';

    /** @var string */
    protected $description = 'Envia por correo los recordatorios de vacunas y controles (RN-20).';

    public function handle(HistoriaClinicaService $historias, CorreoService $correo): int
    {
        $dias = max(1, (int) $this->option('dias'));
        $enviados = 0;

        foreach ($historias->recordatorios($dias) as $fila) {
            // Una sola vez por mascota y fecha de control.
            $llave = sprintf('recordatorio:%d:%s', $fila['mascota_id'], $fila['proxima_fecha']);

            if (! Cache::add($llave, true, now()->addDays(60))) {
                continue;
            }

            $correo->enviar($fila['correo'], new RecordatorioVacunaMail($fila));
            $enviados++;
        }

        $this->info('Recordatorios enviados: '.$enviados.'.');

        return self::SUCCESS;
    }
}
