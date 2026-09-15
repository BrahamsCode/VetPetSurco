<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\EstadoSuscripcion;
use App\Models\Suscripcion;
use Illuminate\Database\Seeder;

/**
 * Suscripciones mensuales activas. — RF-03 / RN-15 / RN-16
 *
 * El proximo despacho se calcula respecto a hoy, igual que el
 * `DATE_ADD(CURDATE(), INTERVAL n DAY)` del archivo original.
 */
class SuscripcionSeeder extends Seeder
{
    public function run(): void
    {
        $suscripciones = [
            [1, 5, 3, 1, 'CUIDADO', 149.00, 12],
            [2, 4, 1, 2, 'BASICO', 99.00, 4],
            [3, 6, 4, 4, 'INTEGRAL', 219.00, 21],
        ];

        foreach ($suscripciones as [$id, $cliente, $mascota, $producto, $plan, $monto, $dias]) {
            Suscripcion::forceCreate([
                'suscripcion_id' => $id,
                'cliente_id' => $cliente,
                'mascota_id' => $mascota,
                'producto_id' => $producto,
                'plan' => $plan,
                'frecuencia_dias' => 30,
                'monto_mensual' => $monto,
                'proximo_despacho' => now()->addDays($dias)->toDateString(),
                'estado' => EstadoSuscripcion::ACTIVA,
            ]);
        }
    }
}
