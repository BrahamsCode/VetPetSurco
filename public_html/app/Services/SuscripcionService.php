<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\EstadoSuscripcion;
use App\Exceptions\SuscripcionDuplicadaException;
use App\Models\Mascota;
use App\Models\Producto;
use App\Models\Suscripcion;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

/**
 * Alta de la suscripcion mensual: como el cliente contrata el ingreso
 * recurrente del negocio. — RF-03
 *
 * Reglas que hace cumplir:
 *  - RN-16: toda suscripcion nace con su frecuencia y su primer despacho.
 *  - RN-22: una mascota tiene un solo plan vigente (ACTIVO o PAUSADO).
 */
final class SuscripcionService
{
    /**
     * Los tres planes: cuota mensual y cuantas unidades del producto entra en
     * cada despacho. Lo que diferencia un plan de otro es esa cantidad.
     *
     * Los montos son los que ya usaba `SuscripcionSeeder`: el plan fija la
     * cuota, no el precio del producto que se despacha.
     */
    private const PLANES = [
        'BASICO' => ['monto' => '99.00', 'unidades' => 1],
        'CUIDADO' => ['monto' => '149.00', 'unidades' => 2],
        'INTEGRAL' => ['monto' => '219.00', 'unidades' => 3],
    ];

    /** RN-16: todos los planes despachan cada 30 dias. */
    private const FRECUENCIA_DIAS = 30;

    /**
     * Planes disponibles con su cuota y sus unidades, para el formulario.
     *
     * @return array<string, array{monto: string, unidades: int}>
     */
    public static function planes(): array
    {
        return self::PLANES;
    }

    public static function montoDe(string $plan): string
    {
        return self::PLANES[$plan]['monto'];
    }

    /** Unidades que entran en cada despacho de ese plan. */
    public static function unidadesDe(string $plan): int
    {
        return self::PLANES[$plan]['unidades'];
    }

    /** RN-16: fecha del primer despacho de un plan contratado hoy (Y-m-d). */
    public static function primerDespacho(): string
    {
        return now()->addDays(self::FRECUENCIA_DIAS)->toDateString();
    }

    /**
     * Contrata un plan para una mascota del cliente.
     *
     * @throws SuscripcionDuplicadaException si la mascota ya tiene un plan vigente (RN-22)
     */
    public function contratar(
        Usuario $cliente,
        Mascota $mascota,
        Producto $producto,
        string $plan,
    ): Suscripcion {
        return DB::transaction(function () use ($cliente, $mascota, $producto, $plan): Suscripcion {
            // RN-22 sin carreras: se bloquea la fila de la mascota hasta el
            // commit. Dos envios simultaneos (doble clic, dos pestanas) quedan
            // en fila y el segundo ya ve el plan que creo el primero.
            Mascota::query()->whereKey($mascota->getKey())->lockForUpdate()->first();

            // RN-22: una mascota tiene un solo plan vigente, sea del producto que
            // sea. Vigente es tanto ACTIVA como PAUSADA; una pausada sigue siendo
            // un contrato, solo esta detenida.
            $vigente = Suscripcion::query()
                ->where('mascota_id', $mascota->getKey())
                ->whereIn('estado', [
                    EstadoSuscripcion::ACTIVA->value,
                    EstadoSuscripcion::PAUSADA->value,
                ])
                ->exists();

            if ($vigente) {
                throw new SuscripcionDuplicadaException();
            }

            $suscripcion = new Suscripcion();
            $suscripcion->cliente_id = $cliente->getKey();
            $suscripcion->mascota_id = $mascota->getKey();
            $suscripcion->producto_id = $producto->getKey();
            $suscripcion->plan = $plan;
            $suscripcion->frecuencia_dias = self::FRECUENCIA_DIAS;
            $suscripcion->monto_mensual = self::montoDe($plan);
            // RN-16: el primer despacho sale a un ciclo completo desde hoy.
            $suscripcion->proximo_despacho = self::primerDespacho();
            $suscripcion->estado = EstadoSuscripcion::ACTIVA;
            $suscripcion->save();

            return $suscripcion;
        });
    }
}
