<?php

declare(strict_types=1);

namespace Tests\Feature\Reglas;

use App\Enums\EstadoPedido;
use App\Enums\ModalidadEntrega;
use App\Enums\TipoOrigen;
use App\Exceptions\EntregaInvalidaException;
use App\Exceptions\EstadoPedidoInvalidoException;
use App\Mail\PedidoEnCaminoMail;
use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Usuario;
use App\Services\PagoService;
use App\Services\Pasarela;
use App\Services\PedidoService;
use App\Services\ResultadoCargo;
use Illuminate\Support\Facades\Mail;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * Entrega y ciclo de vida del pedido:
 *  - delivery o recojo, con el costo de envio que corresponde;
 *  - se cobra productos mas envio;
 *  - lo no pagado se anula (a mano o a las 48 h) y devuelve el stock;
 *  - el cliente sigue su pedido y recibe aviso cuando sale.
 */
final class EntregaPedidoTest extends TestCase
{
    private function servicio(): PedidoService
    {
        return app(PedidoService::class);
    }

    /** Pedido de un producto a un precio dado, con stock de sobra. */
    private function comprar(string $precio, ModalidadEntrega $modalidad, ?string $direccion = null, int $cantidad = 1): Pedido
    {
        $cliente = Usuario::factory()->cliente()->create();
        $producto = Producto::factory()->create(['precio' => $precio, 'stock_actual' => 20, 'activo' => true]);

        return $this->servicio()->confirmar(
            $cliente,
            [['producto_id' => $producto->producto_id, 'cantidad' => $cantidad]],
            TipoOrigen::COMPRA_DIRECTA,
            $modalidad,
            $direccion,
        );
    }

    // -----------------------------------------------------------------
    // Costo de envio
    // -----------------------------------------------------------------

    public function test_el_delivery_de_una_compra_menor_a_80_cuesta_8_soles(): void
    {
        $pedido = $this->comprar('45.00', ModalidadEntrega::DELIVERY, 'Av. Primavera 123, Surco');

        $this->assertSame('8.00', $pedido->fresh()->costo_envio);
        $this->assertSame('53.00', $pedido->fresh()->totalACobrar());
        $this->assertSame('Av. Primavera 123, Surco', $pedido->direccion_entrega);
    }

    public function test_el_delivery_es_gratis_desde_80_soles(): void
    {
        $pedido = $this->comprar('80.00', ModalidadEntrega::DELIVERY, 'Av. Primavera 123, Surco');

        $this->assertSame('0.00', $pedido->fresh()->costo_envio);
    }

    public function test_el_recojo_en_tienda_no_cobra_envio_ni_guarda_direccion(): void
    {
        $pedido = $this->comprar('20.00', ModalidadEntrega::RECOJO, 'Calle que no se usa 1');

        $this->assertSame('0.00', $pedido->fresh()->costo_envio);
        $this->assertNull($pedido->fresh()->direccion_entrega);
    }

    public function test_el_despacho_de_suscripcion_no_cobra_envio(): void
    {
        $this->assertSame(0, PedidoService::costoEnvio(ModalidadEntrega::DELIVERY, 1000, TipoOrigen::SUSCRIPCION));
    }

    public function test_delivery_sin_direccion_no_crea_pedido_ni_toca_stock(): void
    {
        $this->expectException(EntregaInvalidaException::class);

        try {
            $this->comprar('20.00', ModalidadEntrega::DELIVERY, '   ');
        } finally {
            $this->assertDatabaseCount('pedidos', 0);
        }
    }

    public function test_el_carrito_exige_direccion_para_delivery(): void
    {
        $cliente = Usuario::factory()->cliente()->create(['direccion' => null]);
        $producto = Producto::factory()->create(['precio' => '20.00', 'stock_actual' => 5, 'activo' => true]);

        $this->actingAs($cliente)->post(route('carrito.agregar'), ['producto_id' => $producto->producto_id, 'cantidad' => 1]);
        $this->actingAs($cliente)
            ->post(route('carrito.confirmar'), ['modalidad_entrega' => 'DELIVERY', 'direccion_entrega' => ''])
            ->assertSessionHasErrors('direccion_entrega');

        $this->assertDatabaseCount('pedidos', 0);
    }

    public function test_el_carrito_registra_la_entrega_y_guarda_la_direccion_en_el_perfil(): void
    {
        $cliente = Usuario::factory()->cliente()->create(['direccion' => null]);
        $producto = Producto::factory()->create(['precio' => '20.00', 'stock_actual' => 5, 'activo' => true]);

        $this->actingAs($cliente)->post(route('carrito.agregar'), ['producto_id' => $producto->producto_id, 'cantidad' => 1]);
        $this->actingAs($cliente)->post(route('carrito.confirmar'), [
            'modalidad_entrega' => 'DELIVERY',
            'direccion_entrega' => 'Jr. Las Magnolias 456, Surco',
        ]);

        $this->assertDatabaseHas('pedidos', [
            'cliente_id' => $cliente->usuario_id,
            'modalidad_entrega' => 'DELIVERY',
            'direccion_entrega' => 'Jr. Las Magnolias 456, Surco',
            'costo_envio' => '8.00',
        ]);
        $this->assertSame('Jr. Las Magnolias 456, Surco', $cliente->fresh()->direccion);
    }

    public function test_la_pasarela_cobra_productos_mas_envio(): void
    {
        $pedido = $this->comprar('45.00', ModalidadEntrega::DELIVERY, 'Av. Primavera 123, Surco');

        $this->mock(Pasarela::class, function (MockInterface $doble): void {
            // 45.00 de productos + 8.00 de envio = 5300 centimos.
            $doble->shouldReceive('cobrar')->once()
                ->withArgs(fn (int $centimos) => $centimos === 5300)
                ->andReturn(ResultadoCargo::aprobado('chr_test', 'Visa', '1111'));
        });

        $pago = app(PagoService::class)->cobrar($pedido, 'tkn_test_ok', 'ana@correo.com');

        $this->assertSame('53.00', $pago->monto);
    }

    // -----------------------------------------------------------------
    // Anulacion y devolucion de stock
    // -----------------------------------------------------------------

    public function test_anular_un_pedido_pendiente_devuelve_el_stock(): void
    {
        $pedido = $this->comprar('20.00', ModalidadEntrega::RECOJO, null, 3);
        $producto = Producto::query()->findOrFail(DetallePedido::query()->where('pedido_id', $pedido->pedido_id)->value('producto_id'));
        $this->assertSame(17, (int) $producto->stock_actual);

        $this->servicio()->anular($pedido, 'Prueba.');

        $this->assertSame(20, (int) $producto->fresh()->stock_actual);
        $this->assertSame(EstadoPedido::ANULADO, $pedido->fresh()->estado);
        $this->assertNotNull($pedido->fresh()->anulado_en);
    }

    public function test_un_pedido_pagado_no_se_anula(): void
    {
        $pedido = Pedido::factory()->enEstado(EstadoPedido::PAGADO)->create();

        $this->expectException(EstadoPedidoInvalidoException::class);

        $this->servicio()->anular($pedido, 'Prueba.');
    }

    public function test_los_pedidos_sin_pagar_se_anulan_a_las_48_horas(): void
    {
        $vencido = $this->comprar('20.00', ModalidadEntrega::RECOJO, null, 2);
        $vencido->forceFill(['fecha_pedido' => now()->subHours(49)])->save();
        $aTiempo = $this->comprar('20.00', ModalidadEntrega::RECOJO);
        $aTiempo->forceFill(['fecha_pedido' => now()->subHours(47)])->save();

        $this->artisan('pedidos:anular-vencidos')->assertSuccessful();

        $this->assertSame(EstadoPedido::ANULADO, $vencido->fresh()->estado);
        $this->assertSame(EstadoPedido::PENDIENTE, $aTiempo->fresh()->estado);
        $productoVencido = DetallePedido::query()->where('pedido_id', $vencido->pedido_id)->value('producto_id');
        $this->assertSame(20, (int) Producto::query()->findOrFail($productoVencido)->stock_actual);
    }

    public function test_no_se_puede_pagar_un_pedido_vencido(): void
    {
        $pedido = Pedido::factory()->enEstado(EstadoPedido::PENDIENTE)->create(['fecha_pedido' => now()->subHours(50)]);

        $this->expectException(EstadoPedidoInvalidoException::class);

        app(PagoService::class)->cobrar($pedido, 'tkn_test_ok', 'ana@correo.com');
    }

    public function test_el_cliente_anula_su_pedido_pero_no_el_de_otro(): void
    {
        $pedido = $this->comprar('20.00', ModalidadEntrega::RECOJO);
        $dueno = Usuario::query()->findOrFail($pedido->cliente_id);
        $otro = Usuario::factory()->cliente()->create();

        $this->actingAs($otro)->patch(route('pedidos.anular', $pedido))->assertForbidden();
        $this->assertSame(EstadoPedido::PENDIENTE, $pedido->fresh()->estado);

        $this->actingAs($dueno)->patch(route('pedidos.anular', $pedido));
        $this->assertSame(EstadoPedido::ANULADO, $pedido->fresh()->estado);
    }

    // -----------------------------------------------------------------
    // Seguimiento
    // -----------------------------------------------------------------

    public function test_al_salir_el_pedido_se_avisa_al_cliente(): void
    {
        Mail::fake();
        $admin = Usuario::factory()->admin()->create();
        $pedido = Pedido::factory()->enEstado(EstadoPedido::PAGADO)->create(['modalidad_entrega' => ModalidadEntrega::RECOJO]);

        $this->actingAs($admin)->from(route('admin'))->patch(route('admin.pedidos.avanzar', $pedido));

        $this->assertSame(EstadoPedido::ENVIADO, $pedido->fresh()->estado);
        $this->assertSame('Listo para recoger', $pedido->fresh()->etiquetaEstado());
        Mail::assertSent(PedidoEnCaminoMail::class);
    }

    public function test_mis_pedidos_muestra_solo_los_pedidos_del_cliente(): void
    {
        $mio = $this->comprar('20.00', ModalidadEntrega::DELIVERY, 'Av. Primavera 123, Surco');
        $ajeno = $this->comprar('30.00', ModalidadEntrega::RECOJO);
        $cliente = Usuario::query()->findOrFail($mio->cliente_id);

        $this->actingAs($cliente)->get(route('pedidos'))
            ->assertOk()
            ->assertSee('Pedido #'.$mio->pedido_id)
            ->assertSee('Av. Primavera 123, Surco')
            ->assertSee('Pagar ahora')
            ->assertDontSee('Pedido #'.$ajeno->pedido_id);
    }
}
