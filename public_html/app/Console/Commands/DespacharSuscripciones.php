<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\DespachoService;
use Illuminate\Console\Command;

/**
 * Emite los despachos de las suscripciones vencidas. — RF-03
 *
 * Pensado para correr una vez al dia (ver `routes/console.php`). Tambien se
 * puede lanzar a mano en la sustentacion para enseñar el ingreso recurrente
 * sin esperar a que pase un mes:
 *
 *     php artisan suscripciones:despachar
 */
class DespacharSuscripciones extends Command
{
    protected $signature = 'suscripciones:despachar';

    protected $description = 'Genera los pedidos de las suscripciones que ya vencieron';

    public function handle(DespachoService $despachos): int
    {
        $resultado = $despachos->generarPendientes();

        foreach ($resultado['despachados'] as $fila) {
            $this->info(sprintf(
                'Suscripcion %d: pedido %d para %s.',
                $fila['suscripcion'],
                $fila['pedido'],
                $fila['cliente'],
            ));
        }

        foreach ($resultado['omitidos'] as $fila) {
            $this->warn(sprintf(
                'Suscripcion %d sin despachar: %s',
                $fila['suscripcion'],
                $fila['motivo'],
            ));
        }

        $this->newLine();
        $this->line(sprintf(
            '%d despachos emitidos, %d omitidos.',
            count($resultado['despachados']),
            count($resultado['omitidos']),
        ));

        return self::SUCCESS;
    }
}
