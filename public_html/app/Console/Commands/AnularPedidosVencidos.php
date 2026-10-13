<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Pedido;
use App\Services\PedidoService;
use Illuminate\Console\Command;

/**
 * Anula los pedidos que pasaron el plazo de pago sin pagarse y devuelve su
 * stock al inventario, para que no quede reservado para siempre.
 *
 * Corre cada hora (ver `routes/console.php`). Tambien se puede lanzar a mano:
 *
 *     php artisan pedidos:anular-vencidos
 */
class AnularPedidosVencidos extends Command
{
    protected $signature = 'pedidos:anular-vencidos';

    protected $description = 'Anula los pedidos sin pagar tras el plazo y devuelve su stock';

    public function handle(PedidoService $pedidos): int
    {
        $anulados = $pedidos->anularVencidos();

        foreach ($anulados as $pedido) {
            $this->info(sprintf('Pedido %d anulado: stock devuelto.', $pedido->pedido_id));
        }

        $this->line(sprintf(
            '%d pedidos anulados por no pagarse en %d horas.',
            count($anulados),
            Pedido::HORAS_PARA_PAGAR,
        ));

        return self::SUCCESS;
    }
}
