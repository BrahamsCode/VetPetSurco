<?php

declare(strict_types=1);

namespace Tests\Feature\Reglas;

use App\Enums\EstadoPedido;
use App\Enums\TipoOrigen;
use App\Exceptions\StockInsuficienteException;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Usuario;
use App\Services\PedidoService;
use DomainException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Confirmación y ciclo de vida del pedido.
 *
 *  - RN-12: sin stock suficiente no hay pedido.
 *  - RN-13: el pedido recorre una secuencia de estados.
 *  - RN-14: se distingue compra puntual de despacho recurrente.
 */
final class PedidoTest extends TestCase
{
    // -----------------------------------------------------------------
    // RN-12: sin stock suficiente no hay pedido
    // -----------------------------------------------------------------

    /**
     * El caso central de toda la presentación: si una sola línea no tiene
     * stock, la transacción se revierte entera y NO queda escrito nada:
     * ni el pedido, ni el detalle, ni el descuento del otro producto.
     */
    public function test_rn12_sin_stock_suficiente_no_se_registra_ninguna_parte_del_pedido(): void
    {
        $cliente = Usuario::factory()->cliente()->create();

        $conStock = Producto::factory()->create([
            'nombre' => 'Alimento premium', 'precio' => '129.90', 'stock_actual' => 24,
        ]);
        $sinStock = Producto::factory()->create([
            'nombre' => 'Vitaminas caninas', 'precio' => '45.00', 'stock_actual' => 3,
        ]);

        $this->assertDatabaseCount('pedidos', 0);
        $this->assertDatabaseCount('detalle_pedidos', 0);

        try {
            app(PedidoService::class)->confirmar($cliente, [
                ['producto_id' => $conStock->producto_id, 'cantidad' => 2],
                // Esta línea pide 99 de un stock de 3: tumba el pedido completo.
                ['producto_id' => $sinStock->producto_id, 'cantidad' => 99],
            ], TipoOrigen::COMPRA_DIRECTA);

            $this->fail('RN-12: el pedido tenía que ser rechazado por falta de stock.');
        } catch (StockInsuficienteException $e) {
            $this->assertSame('RN-12', $e->regla());
            $this->assertStringContainsString('Stock insuficiente', $e->getMessage());
            $this->assertStringContainsString('No se registró ninguna parte del pedido.', $e->getMessage());
        }

        // Nada escrito: ni cabecera ni detalle.
        $this->assertDatabaseCount('pedidos', 0);
        $this->assertDatabaseCount('detalle_pedidos', 0);
        $this->assertDatabaseMissing('pedidos', ['cliente_id' => $cliente->usuario_id]);
        $this->assertDatabaseMissing('detalle_pedidos', ['producto_id' => $conStock->producto_id]);
        $this->assertDatabaseMissing('detalle_pedidos', ['producto_id' => $sinStock->producto_id]);

        // Y el stock del resto de productos quedó intacto: no se descontó nada.
        $this->assertSame(24, (int) $conStock->fresh()->stock_actual, 'RN-12: el producto que sí tenía stock no se tocó.');
        $this->assertSame(3, (int) $sinStock->fresh()->stock_actual);
        $this->assertDatabaseHas('productos', ['producto_id' => $conStock->producto_id, 'stock_actual' => 24]);
        $this->assertDatabaseHas('productos', ['producto_id' => $sinStock->producto_id, 'stock_actual' => 3]);
    }

    /** La excepción reúne TODAS las líneas faltantes, no solo la primera. */
    public function test_rn12_la_excepcion_reune_todas_las_lineas_que_no_alcanzan(): void
    {
        $cliente = Usuario::factory()->cliente()->create();
        $uno  = Producto::factory()->create(['nombre' => 'Vitaminas', 'stock_actual' => 3]);
        $dos  = Producto::factory()->create(['nombre' => 'Arena', 'stock_actual' => 0]);

        try {
            app(PedidoService::class)->confirmar($cliente, [
                ['producto_id' => $uno->producto_id, 'cantidad' => 99],
                ['producto_id' => $dos->producto_id, 'cantidad' => 1],
            ], TipoOrigen::COMPRA_DIRECTA);

            $this->fail('RN-12: el pedido tenía que ser rechazado.');
        } catch (StockInsuficienteException $e) {
            $faltantes = collect($e->faltantes())->keyBy('producto_id');

            $this->assertCount(2, $faltantes);
            $this->assertSame(99, $faltantes[$uno->producto_id]['pedida']);
            $this->assertSame(3, $faltantes[$uno->producto_id]['disponible']);
            $this->assertSame(0, $faltantes[$dos->producto_id]['disponible']);
        }
    }

    /** Con stock suficiente sí se registra todo: cabecera, detalle y descuento. */
    public function test_rn12_con_stock_suficiente_el_pedido_queda_registrado_por_completo(): void
    {
        $cliente = Usuario::factory()->cliente()->create();
        $uno = Producto::factory()->create(['precio' => '129.90', 'stock_actual' => 24]);
        $dos = Producto::factory()->create(['precio' => '45.00', 'stock_actual' => 10]);

        $pedido = app(PedidoService::class)->confirmar($cliente, [
            ['producto_id' => $uno->producto_id, 'cantidad' => 2],
            ['producto_id' => $dos->producto_id, 'cantidad' => 1],
        ], TipoOrigen::COMPRA_DIRECTA);

        $this->assertDatabaseCount('pedidos', 1);
        $this->assertDatabaseCount('detalle_pedidos', 2);
        $this->assertSame('304.80', (string) $pedido->fresh()->monto_total);
        $this->assertSame(22, (int) $uno->fresh()->stock_actual);
        $this->assertSame(9, (int) $dos->fresh()->stock_actual);
    }

    /** Un carrito vacío tampoco genera pedido. */
    public function test_rn12_un_carrito_vacio_no_genera_pedido(): void
    {
        $cliente = Usuario::factory()->cliente()->create();

        try {
            app(PedidoService::class)->confirmar($cliente, [], TipoOrigen::COMPRA_DIRECTA);
            $this->fail('RN-12: sin líneas no hay pedido que registrar.');
        } catch (StockInsuficienteException $e) {
            $this->assertSame('RN-12', $e->regla());
        }

        $this->assertDatabaseCount('pedidos', 0);
    }

    /** Un producto que ya no existe en el catálogo también tumba el pedido. */
    public function test_rn12_un_producto_inexistente_tumba_el_pedido_completo(): void
    {
        $cliente  = Usuario::factory()->cliente()->create();
        $producto = Producto::factory()->create(['stock_actual' => 10]);

        $this->expectException(StockInsuficienteException::class);

        try {
            app(PedidoService::class)->confirmar($cliente, [
                ['producto_id' => $producto->producto_id, 'cantidad' => 1],
                ['producto_id' => 999999, 'cantidad' => 1],
            ], TipoOrigen::COMPRA_DIRECTA);
        } finally {
            $this->assertDatabaseCount('pedidos', 0);
            $this->assertSame(10, (int) $producto->fresh()->stock_actual);
        }
    }

    // -----------------------------------------------------------------
    // RN-13: el pedido recorre una secuencia de estados
    // -----------------------------------------------------------------

    /** Capa de aplicación: PENDIENTE → PAGADO → ENVIADO → ENTREGADO. */
    public function test_rn13_el_pedido_recorre_la_secuencia_completa_de_estados(): void
    {
        $servicio = app(PedidoService::class);
        $pedido   = Pedido::factory()->enEstado(EstadoPedido::PENDIENTE)->create();

        $this->assertSame(EstadoPedido::PAGADO, $servicio->avanzarEstado($pedido)->estado);
        $this->assertSame(EstadoPedido::ENVIADO, $servicio->avanzarEstado($pedido)->estado);
        $this->assertSame(EstadoPedido::ENTREGADO, $servicio->avanzarEstado($pedido)->estado);

        $this->assertDatabaseHas('pedidos', [
            'pedido_id' => $pedido->pedido_id,
            'estado'    => EstadoPedido::ENTREGADO->value,
        ]);
    }

    /** El enum define la secuencia y dónde se acaba. */
    public function test_rn13_el_enum_define_el_siguiente_estado_de_cada_paso(): void
    {
        $this->assertSame(EstadoPedido::PAGADO, EstadoPedido::PENDIENTE->siguiente());
        $this->assertSame(EstadoPedido::ENVIADO, EstadoPedido::PAGADO->siguiente());
        $this->assertSame(EstadoPedido::ENTREGADO, EstadoPedido::ENVIADO->siguiente());
        $this->assertNull(EstadoPedido::ENTREGADO->siguiente());
        $this->assertNull(EstadoPedido::ANULADO->siguiente());
    }

    /** Un pedido ENTREGADO ya no avanza: no hay estado siguiente. */
    public function test_rn13_un_pedido_entregado_ya_no_avanza(): void
    {
        $pedido = Pedido::factory()->enEstado(EstadoPedido::ENTREGADO)->create();

        $this->expectException(DomainException::class);

        app(PedidoService::class)->avanzarEstado($pedido);
    }

    /** Un pedido ANULADO se queda fuera de la secuencia. */
    public function test_rn13_un_pedido_anulado_no_cambia_de_estado(): void
    {
        $pedido = Pedido::factory()->enEstado(EstadoPedido::ANULADO)->create();

        try {
            app(PedidoService::class)->avanzarEstado($pedido);
            $this->fail('RN-13: un pedido anulado no puede avanzar.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('anulado', $e->getMessage());
        }

        $this->assertDatabaseHas('pedidos', [
            'pedido_id' => $pedido->pedido_id,
            'estado'    => EstadoPedido::ANULADO->value,
        ]);
    }

    /** La ruta del panel administrativo avanza el pedido un paso. */
    public function test_rn13_la_ruta_del_admin_avanza_el_pedido_un_solo_paso(): void
    {
        $admin  = Usuario::factory()->admin()->create();
        $pedido = Pedido::factory()->enEstado(EstadoPedido::PAGADO)->create();

        $respuesta = $this->actingAs($admin)
            ->from(route('admin'))
            ->patch(route('admin.pedidos.avanzar', $pedido));

        $respuesta->assertRedirect(route('admin'));
        $respuesta->assertSessionHas('resultado', fn (array $r): bool => $r['ok'] === true);
        $this->assertDatabaseHas('pedidos', [
            'pedido_id' => $pedido->pedido_id,
            'estado'    => EstadoPedido::ENVIADO->value,
        ]);
    }

    /**
     * Intentar avanzar un pedido ya ENTREGADO desde el panel devuelve el aviso
     * de la regla, no un error del servidor.
     */
    public function test_rn13_la_ruta_del_admin_avisa_cuando_el_pedido_ya_no_puede_avanzar(): void
    {
        $admin  = Usuario::factory()->admin()->create();
        $pedido = Pedido::factory()->enEstado(EstadoPedido::ENTREGADO)->create();

        $respuesta = $this->actingAs($admin)
            ->from(route('admin'))
            ->patch(route('admin.pedidos.avanzar', $pedido));

        $respuesta->assertRedirect(route('admin'));
        $respuesta->assertSessionHas('resultado', fn (array $r): bool => $r['ok'] === false && $r['regla'] === 'RN-13');
        $this->assertDatabaseHas('pedidos', [
            'pedido_id' => $pedido->pedido_id,
            'estado'    => EstadoPedido::ENTREGADO->value,
        ]);
    }

    /** Capa del motor: la columna ENUM no admite un estado inventado. */
    public function test_rn13_el_motor_rechaza_un_estado_que_no_existe_en_el_enum(): void
    {
        $cliente = Usuario::factory()->cliente()->create();

        $this->expectException(QueryException::class);

        DB::table('pedidos')->insert([
            'cliente_id'  => $cliente->usuario_id,
            'monto_total' => 100.00,
            'tipo_origen' => TipoOrigen::COMPRA_DIRECTA->value,
            'estado'      => 'DEVUELTO',
        ]);
    }

    // -----------------------------------------------------------------
    // RN-14: compra puntual frente a despacho recurrente
    // -----------------------------------------------------------------

    /** Capa de aplicación: el origen viaja con el pedido y queda guardado. */
    public function test_rn14_el_pedido_distingue_la_compra_directa_del_despacho_de_suscripcion(): void
    {
        $cliente = Usuario::factory()->cliente()->create();
        $producto = Producto::factory()->create(['precio' => '50.00', 'stock_actual' => 20]);
        $servicio = app(PedidoService::class);

        $directo = $servicio->confirmar(
            $cliente,
            [['producto_id' => $producto->producto_id, 'cantidad' => 1]],
            TipoOrigen::COMPRA_DIRECTA,
        );
        $recurrente = $servicio->confirmar(
            $cliente,
            [['producto_id' => $producto->producto_id, 'cantidad' => 1]],
            TipoOrigen::SUSCRIPCION,
        );

        $this->assertSame(TipoOrigen::COMPRA_DIRECTA, $directo->fresh()->tipo_origen);
        $this->assertSame(TipoOrigen::SUSCRIPCION, $recurrente->fresh()->tipo_origen);

        $this->assertDatabaseHas('pedidos', [
            'pedido_id'   => $recurrente->pedido_id,
            'tipo_origen' => TipoOrigen::SUSCRIPCION->value,
        ]);
        $this->assertSame(
            1,
            Pedido::query()->where('tipo_origen', TipoOrigen::SUSCRIPCION->value)->count(),
        );
    }

    /**
     * El pedido nace PENDIENTE: el stock ya quedo reservado, pero solo pasa a
     * PAGADO cuando la pasarela aprueba el cobro (RN-21).
     */
    public function test_rn14_el_pedido_confirmado_nace_pendiente_y_con_su_origen(): void
    {
        $cliente  = Usuario::factory()->cliente()->create();
        $producto = Producto::factory()->create(['precio' => '50.00', 'stock_actual' => 20]);

        $pedido = app(PedidoService::class)->confirmar(
            $cliente,
            [['producto_id' => $producto->producto_id, 'cantidad' => 1]],
            TipoOrigen::SUSCRIPCION,
        );

        $this->assertSame(EstadoPedido::PENDIENTE, $pedido->fresh()->estado);
        $this->assertSame(TipoOrigen::SUSCRIPCION, $pedido->fresh()->tipo_origen);
    }

    /** Capa del motor: `pedidos.tipo_origen` solo admite los dos orígenes. */
    public function test_rn14_el_motor_rechaza_un_origen_que_no_existe_en_el_enum(): void
    {
        $cliente = Usuario::factory()->cliente()->create();

        $this->expectException(QueryException::class);

        DB::table('pedidos')->insert([
            'cliente_id'  => $cliente->usuario_id,
            'monto_total' => 100.00,
            'tipo_origen' => 'REGALO',
            'estado'      => EstadoPedido::PENDIENTE->value,
        ]);
    }
}
