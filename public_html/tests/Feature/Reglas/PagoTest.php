<?php

declare(strict_types=1);

namespace Tests\Feature\Reglas;

use App\Enums\EstadoPago;
use App\Enums\EstadoPedido;
use App\Exceptions\EstadoPedidoInvalidoException;
use App\Exceptions\PagoRechazadoException;
use App\Models\Pago;
use App\Models\Pedido;
use App\Models\Usuario;
use App\Services\PagoService;
use App\Services\Pasarela;
use App\Services\PasarelaSimulada;
use App\Services\ResultadoCargo;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * Cobro del pedido contra la pasarela.
 *
 *  - RN-21: el pedido solo pasa a PAGADO si la pasarela aprueba, y todo
 *    intento queda registrado.
 *  - RN-13: no se cobra dos veces el mismo pedido.
 *  - RN-01: un cliente solo paga sus propios pedidos.
 *
 * La pasarela se sustituye por un doble: los tests no salen a internet.
 */
final class PagoTest extends TestCase
{
    // -----------------------------------------------------------------
    // RN-21: sin aprobacion de la pasarela no hay pedido PAGADO
    // -----------------------------------------------------------------

    public function test_rn21_un_cobro_aprobado_deja_el_pedido_pagado_y_registra_el_intento(): void
    {
        $pedido = Pedido::factory()
            ->enEstado(EstadoPedido::PENDIENTE)
            ->create(['monto_total' => '129.90']);

        $this->fingirPasarela(ResultadoCargo::aprobado('chr_test_aprobado', 'Visa', '1111'));

        $pago = app(PagoService::class)->cobrar($pedido, 'tkn_test_ok', 'ana@correo.com');

        $this->assertSame(EstadoPedido::PAGADO, $pedido->fresh()->estado);
        $this->assertSame(EstadoPago::APROBADO, $pago->estado);
        $this->assertSame('chr_test_aprobado', $pago->cargo_culqi);
        $this->assertSame('1111', $pago->ultimos_cuatro);
        // El monto cobrado es el del pedido, al centimo.
        $this->assertSame('129.90', (string) $pago->monto);
    }

    /**
     * El caso que importa: una tarjeta rechazada no puede dejar el pedido como
     * pagado, pero si tiene que dejar rastro del intento.
     */
    public function test_rn21_un_cobro_rechazado_deja_el_pedido_pendiente_y_registra_el_motivo(): void
    {
        $pedido = Pedido::factory()
            ->enEstado(EstadoPedido::PENDIENTE)
            ->create(['monto_total' => '80.00']);

        $this->fingirPasarela(ResultadoCargo::rechazado('Tu tarjeta no tiene saldo suficiente.'));

        try {
            app(PagoService::class)->cobrar($pedido, 'tkn_test_sin_saldo', 'ana@correo.com');
            $this->fail('Se esperaba PagoRechazadoException.');
        } catch (PagoRechazadoException $e) {
            $this->assertSame('RN-21', $e->regla());
            $this->assertSame('Tu tarjeta no tiene saldo suficiente.', $e->getMessage());
        }

        $this->assertSame(EstadoPedido::PENDIENTE, $pedido->fresh()->estado);

        $pago = Pago::query()->where('pedido_id', $pedido->pedido_id)->sole();
        $this->assertSame(EstadoPago::RECHAZADO, $pago->estado);
        $this->assertSame('Tu tarjeta no tiene saldo suficiente.', $pago->mensaje);
    }

    /** Un fallo tecnico tampoco paga el pedido, y se distingue del rechazo. */
    public function test_rn21_un_error_de_la_pasarela_no_paga_el_pedido(): void
    {
        $pedido = Pedido::factory()
            ->enEstado(EstadoPedido::PENDIENTE)
            ->create(['monto_total' => '45.00']);

        $this->fingirPasarela(ResultadoCargo::error('Imposible conectar a Culqi API'));

        $this->expectException(PagoRechazadoException::class);

        try {
            app(PagoService::class)->cobrar($pedido, 'tkn_test_caido', 'ana@correo.com');
        } finally {
            $this->assertSame(EstadoPedido::PENDIENTE, $pedido->fresh()->estado);
            $this->assertSame(
                EstadoPago::ERROR,
                Pago::query()->where('pedido_id', $pedido->pedido_id)->sole()->estado,
            );
        }
    }

    // -----------------------------------------------------------------
    // RN-13: el pedido no retrocede ni se cobra dos veces
    // -----------------------------------------------------------------

    public function test_rn13_un_pedido_ya_pagado_no_se_vuelve_a_cobrar(): void
    {
        $pedido = Pedido::factory()
            ->enEstado(EstadoPedido::PAGADO)
            ->create(['monto_total' => '60.00']);

        // Si el servicio intentara cobrar, este doble lo delataria.
        $this->mock(Pasarela::class, function (MockInterface $doble): void {
            $doble->shouldNotReceive('cobrar');
        });

        $this->expectException(EstadoPedidoInvalidoException::class);

        try {
            app(PagoService::class)->cobrar($pedido, 'tkn_test_ok', 'ana@correo.com');
        } finally {
            $this->assertSame(0, Pago::query()->where('pedido_id', $pedido->pedido_id)->count());
        }
    }

    // -----------------------------------------------------------------
    // RN-01: cada cliente solo paga lo suyo
    // -----------------------------------------------------------------

    public function test_rn01_un_cliente_no_puede_pagar_el_pedido_de_otro(): void
    {
        $dueno   = Usuario::factory()->cliente()->create();
        $intruso = Usuario::factory()->cliente()->create();

        $pedido = Pedido::factory()
            ->enEstado(EstadoPedido::PENDIENTE)
            ->create(['cliente_id' => $dueno->usuario_id, 'monto_total' => '30.00']);

        $this->mock(Pasarela::class, function (MockInterface $doble): void {
            $doble->shouldNotReceive('cobrar');
        });

        $this->actingAs($intruso)
            ->post(route('pago.procesar', $pedido), ['token' => 'tkn_test_ok'])
            ->assertForbidden();

        $this->assertSame(EstadoPedido::PENDIENTE, $pedido->fresh()->estado);
    }

    // -----------------------------------------------------------------
    // Pasarela simulada: la que se usa mientras no haya llaves de Culqi
    // -----------------------------------------------------------------

    public function test_la_pasarela_simulada_aprueba_una_tarjeta_normal(): void
    {
        $resultado = (new PasarelaSimulada())
            ->cobrar(1990, 'tkn_sim_visa_1111', 'ana@correo.com', 'pedido de prueba');

        $this->assertTrue($resultado->fueAprobado());
        $this->assertSame('Visa', $resultado->marca);
        $this->assertSame('1111', $resultado->ultimosCuatro);
        $this->assertStringStartsWith('chr_sim_', (string) $resultado->cargoId);
    }

    /** Las terminadas en 0000 simulan el rechazo del emisor. */
    public function test_la_pasarela_simulada_rechaza_la_tarjeta_terminada_en_ceros(): void
    {
        $resultado = (new PasarelaSimulada())
            ->cobrar(1990, 'tkn_sim_visa_0000', 'ana@correo.com', 'pedido de prueba');

        $this->assertFalse($resultado->fueAprobado());
        $this->assertSame(EstadoPago::RECHAZADO, $resultado->estado);
        $this->assertNotNull($resultado->mensaje);
    }

    public function test_la_pasarela_simulada_rechaza_un_token_con_formato_invalido(): void
    {
        $resultado = (new PasarelaSimulada())
            ->cobrar(1990, 'tkn_cualquier_cosa', 'ana@correo.com', 'pedido de prueba');

        $this->assertSame(EstadoPago::ERROR, $resultado->estado);
    }

    /** Sustituye la pasarela real por un doble con la respuesta indicada. */
    private function fingirPasarela(ResultadoCargo $resultado): void
    {
        $this->mock(Pasarela::class, function (MockInterface $doble) use ($resultado): void {
            $doble->shouldReceive('cobrar')->once()->andReturn($resultado);
        });
    }
}
