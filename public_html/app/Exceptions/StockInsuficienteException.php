<?php

declare(strict_types=1);

namespace App\Exceptions;

use Throwable;

/**
 * RN-12: sin stock suficiente no hay pedido; no se registra ninguna parte.
 *
 * Es el equivalente en Laravel del SIGNAL SQLSTATE '45000' que lanza
 * `sp_confirmar_pedido` (basedatos/03_transaccion_compra.sql): se acumulan
 * TODAS las líneas que no alcanzan y recién entonces se aborta la transacción,
 * para devolverle al cliente el detalle completo de lo que no hay.
 */
final class StockInsuficienteException extends ReglaDeNegocioException
{
    /**
     * Detalle de cada línea que no se pudo atender.
     *
     * @var array<int, array{producto_id: int|null, nombre: string, pedida: int, disponible: int}>
     */
    private array $faltantes;

    /**
     * @param array<int, array{producto_id: int|null, nombre: string, pedida: int, disponible: int}> $faltantes
     */
    public function __construct(array $faltantes = [], string $mensaje = '', ?Throwable $anterior = null)
    {
        $this->faltantes = $faltantes;

        if ($mensaje === '') {
            $mensaje = $faltantes === []
                ? 'No hay líneas que confirmar.'
                : 'Stock insuficiente → ' . self::describir($faltantes)
                  . '. No se registró ninguna parte del pedido.';
        }

        parent::__construct($mensaje, 0, $anterior);
    }

    public function regla(): string
    {
        return 'RN-12';
    }

    /**
     * Líneas que no alcanzaron, con la cantidad pedida y la disponible.
     *
     * @return array<int, array{producto_id: int|null, nombre: string, pedida: int, disponible: int}>
     */
    public function faltantes(): array
    {
        return $this->faltantes;
    }

    /**
     * Arma el mismo texto que produce el procedimiento almacenado:
     * «Nombre (pedido: 5, disponible: 2); Otro (pedido: 3, disponible: 0)».
     *
     * @param array<int, array{producto_id: int|null, nombre: string, pedida: int, disponible: int}> $faltantes
     */
    private static function describir(array $faltantes): string
    {
        $partes = [];

        foreach ($faltantes as $faltante) {
            $partes[] = sprintf(
                '%s (pedido: %d, disponible: %d)',
                $faltante['nombre'],
                $faltante['pedida'],
                $faltante['disponible'],
            );
        }

        return implode('; ', $partes);
    }
}
