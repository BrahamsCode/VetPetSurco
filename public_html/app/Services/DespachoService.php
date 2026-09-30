<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TipoOrigen;
use App\Exceptions\ReglaDeNegocioException;
use App\Mail\DespachoSuscripcionMail;
use App\Models\Suscripcion;
use Illuminate\Support\Carbon;

/**
 * Generacion automatica de los despachos de las suscripciones. — RF-03 / RN-14
 *
 * Es lo que convierte la suscripcion en ingreso recurrente de verdad: cuando
 * llega `proximo_despacho`, el sistema emite el pedido solo. El cliente no
 * elige que un pedido sea "recurrente"; lo es porque nacio de un plan suyo.
 *
 * Reglas que hace cumplir:
 *  - RN-12: si no hay stock no se despacha, y no se registra nada a medias.
 *  - RN-14: el pedido generado queda marcado como SUSCRIPCION.
 *  - RN-16: tras despachar, la fecha salta un ciclo completo.
 */
final class DespachoService
{
    public function __construct(private readonly PedidoService $pedidos)
    {
    }

    /**
     * Emite los despachos de todas las suscripciones activas que ya vencieron.
     *
     * @return array{despachados: list<array{suscripcion: int, pedido: int, cliente: string}>,
     *               omitidos: list<array{suscripcion: int, motivo: string}>}
     */
    public function generarPendientes(?Carbon $hasta = null): array
    {
        $corte = $hasta ?? Carbon::today();

        $despachados = [];
        $omitidos = [];

        $vencidas = Suscripcion::query()
            ->activas()                                  // RN-15: las pausadas no despachan
            ->whereDate('proximo_despacho', '<=', $corte)
            ->orderBy('suscripcion_id')
            ->get();

        foreach ($vencidas as $suscripcion) {
            $cliente = $suscripcion->cliente;

            if ($cliente === null) {
                $omitidos[] = [
                    'suscripcion' => (int) $suscripcion->getKey(),
                    'motivo' => 'La suscripcion no tiene cliente.',
                ];

                continue;
            }

            try {
                // El despacho es un pedido normal: el producto sale a su precio
                // vigente (RN-11) y descuenta stock dentro de la transaccion.
                // Las unidades las manda el plan contratado.
                $pedido = $this->pedidos->confirmar(
                    $cliente,
                    [[
                        'producto_id' => $suscripcion->producto_id,
                        'cantidad' => SuscripcionService::unidadesDe((string) $suscripcion->plan),
                    ]],
                    TipoOrigen::SUSCRIPCION,
                );
            } catch (ReglaDeNegocioException $e) {
                // RN-12: sin stock no se despacha. La fecha NO avanza, asi que
                // se vuelve a intentar en la siguiente pasada, cuando repongan.
                $omitidos[] = [
                    'suscripcion' => (int) $suscripcion->getKey(),
                    'motivo' => $e->getMessage(),
                ];

                continue;
            }

            // RN-16: el ciclo cuenta desde la fecha que tocaba, no desde hoy,
            // para que los despachos no se vayan corriendo con cada retraso.
            $suscripcion->proximo_despacho = $suscripcion->proximo_despacho
                ->copy()
                ->addDays((int) $suscripcion->frecuencia_dias);
            $suscripcion->save();

            // Aviso al cliente: su despacho mensual ya se emitio. Un fallo del
            // correo nunca interrumpe la generacion de los demas despachos.
            app(CorreoService::class)->enviar(
                (string) $cliente->correo,
                new DespachoSuscripcionMail($suscripcion, $pedido, $cliente),
            );

            $despachados[] = [
                'suscripcion' => (int) $suscripcion->getKey(),
                'pedido' => (int) $pedido->getKey(),
                'cliente' => (string) $cliente->nombre,
            ];
        }

        return ['despachados' => $despachados, 'omitidos' => $omitidos];
    }
}
