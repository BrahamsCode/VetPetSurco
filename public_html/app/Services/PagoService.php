<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\EstadoPedido;
use App\Exceptions\EstadoPedidoInvalidoException;
use App\Exceptions\PagoRechazadoException;
use App\Models\Pago;
use App\Models\Pedido;

/**
 * Cobro de un pedido contra la pasarela. — RN-21
 *
 * Reglas que hace cumplir:
 *  - RN-13: el pedido solo pasa de PENDIENTE a PAGADO, nunca al reves.
 *  - RN-21: el pedido pasa a PAGADO unicamente si la pasarela aprueba, y todo
 *    intento queda registrado en `pagos`, haya sido aprobado o no.
 */
final class PagoService
{
    public function __construct(private readonly Pasarela $pasarela)
    {
    }

    /**
     * Cobra el pedido con el token que genero el checkout en el navegador.
     *
     * El intento se registra siempre, incluso cuando la tarjeta es rechazada:
     * asi el cliente puede reintentar con otra y queda el rastro de por que
     * fallo la primera.
     *
     * @throws EstadoPedidoInvalidoException si el pedido ya no esta PENDIENTE (RN-13)
     * @throws PagoRechazadoException        si la pasarela no aprueba el cobro (RN-21)
     */
    public function cobrar(Pedido $pedido, string $token, string $correo): Pago
    {
        // RN-13: cobrar dos veces el mismo pedido no debe ser posible, ni
        // siquiera reenviando el formulario.
        if ($this->estadoDe($pedido) !== EstadoPedido::PENDIENTE) {
            throw new EstadoPedidoInvalidoException(
                'Este pedido ya no esta pendiente de pago.',
            );
        }

        // Culqi cobra en centimos enteros, la misma unidad con la que el
        // carrito arma los totales.
        $centimos = CarritoService::aCentimos($pedido->monto_total);

        $resultado = $this->pasarela->cobrar(
            $centimos,
            $token,
            $correo,
            'VetPet Connect - pedido '.$pedido->getKey(),
        );

        // RN-21: el registro va antes de decidir, para que un rechazo tambien
        // deje huella.
        $pago = new Pago();
        $pago->pedido_id = $pedido->getKey();
        $pago->cargo_culqi = $resultado->cargoId;
        $pago->monto = CarritoService::aDecimal($centimos);
        $pago->estado = $resultado->estado;
        $pago->marca = $resultado->marca;
        $pago->ultimos_cuatro = $resultado->ultimosCuatro;
        $pago->mensaje = $resultado->mensaje;
        $pago->fecha_pago = now();
        $pago->save();

        if (! $resultado->fueAprobado()) {
            throw new PagoRechazadoException(
                $resultado->mensaje ?? 'La pasarela rechazo el pago.',
            );
        }

        $pedido->estado = EstadoPedido::PAGADO;
        $pedido->save();

        return $pago;
    }

    /**
     * Lee el estado del pedido admitiendo tanto el enum casteado por el modelo
     * como el texto crudo de la columna ENUM.
     */
    private function estadoDe(Pedido $pedido): EstadoPedido
    {
        $estado = $pedido->estado;

        return $estado instanceof EstadoPedido
            ? $estado
            : EstadoPedido::from((string) $estado);
    }
}
