<?php

declare(strict_types=1);

namespace Tests\Feature\Reglas;

use App\Enums\TipoOrigen;
use App\Exceptions\CantidadInvalidaException;
use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Usuario;
use App\Services\CarritoService;
use App\Services\PedidoService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Carrito y líneas del pedido.
 *
 *  - RN-09: un producto aparece una sola vez por pedido.
 *  - RN-10: no se compran cantidades menores o iguales a cero.
 *  - RN-11: el precio de un pedido emitido ya no cambia.
 */
final class CarritoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // El carrito vive en la sesión: hay que tenerla abierta.
        $this->startSession();
    }

    // -----------------------------------------------------------------
    // RN-09: un producto aparece una sola vez por pedido
    // -----------------------------------------------------------------

    /** Capa de aplicación: el segundo «agregar» acumula, no duplica. */
    public function test_rn09_agregar_dos_veces_el_mismo_producto_deja_una_linea_con_la_cantidad_acumulada(): void
    {
        $carrito  = app(CarritoService::class);
        $producto = Producto::factory()->create(['precio' => '129.90', 'stock_actual' => 40]);

        $carrito->agregar($producto, 1);
        $carrito->agregar($producto, 1);

        $items = $carrito->items();

        $this->assertCount(1, $items, 'RN-09: el carrito solo puede tener una línea por producto.');
        $this->assertSame(2, $items[0]['cantidad'], 'RN-09: la segunda vez se acumula la cantidad.');
        $this->assertSame(2, $carrito->unidades());
        $this->assertSame('259.80', $carrito->total());
    }

    /** Capa de aplicación: la acumulación funciona con cantidades mayores que uno. */
    public function test_rn09_la_acumulacion_suma_las_cantidades_de_cada_agregado(): void
    {
        $carrito  = app(CarritoService::class);
        $producto = Producto::factory()->create(['precio' => '10.00']);
        $otro     = Producto::factory()->create(['precio' => '5.00']);

        $carrito->agregar($producto, 3);
        $carrito->agregar($otro, 1);
        $carrito->agregar($producto, 4);

        $items = collect($carrito->items())->keyBy('producto_id');

        $this->assertCount(2, $items);
        $this->assertSame(7, $items[$producto->producto_id]['cantidad']);
        $this->assertSame(1, $items[$otro->producto_id]['cantidad']);
        $this->assertSame(8, $carrito->unidades());
    }

    /** Capa de aplicación: el pedido consolida dos líneas del mismo producto. */
    public function test_rn09_el_pedido_consolida_dos_lineas_del_mismo_producto_en_una(): void
    {
        $cliente  = Usuario::factory()->cliente()->create();
        $producto = Producto::factory()->create(['precio' => '20.00', 'stock_actual' => 30]);

        $pedido = app(PedidoService::class)->confirmar($cliente, [
            ['producto_id' => $producto->producto_id, 'cantidad' => 2],
            ['producto_id' => $producto->producto_id, 'cantidad' => 3],
        ], TipoOrigen::COMPRA_DIRECTA);

        $this->assertDatabaseCount('detalle_pedidos', 1);
        $detalle = DetallePedido::query()->where('pedido_id', $pedido->pedido_id)->firstOrFail();
        $this->assertSame(5, (int) $detalle->cantidad);
        $this->assertSame(25, (int) $producto->fresh()->stock_actual);
    }

    /** Capa del motor: `uk_linea_pedido (pedido_id, producto_id)`. */
    public function test_rn09_el_motor_rechaza_dos_lineas_del_mismo_producto_en_el_mismo_pedido(): void
    {
        $pedido   = Pedido::factory()->create();
        $producto = Producto::factory()->create();

        DB::table('detalle_pedidos')->insert([
            'pedido_id'       => $pedido->pedido_id,
            'producto_id'     => $producto->producto_id,
            'cantidad'        => 1,
            'precio_unitario' => 20.00,
            'subtotal'        => 20.00,
        ]);

        $this->expectException(QueryException::class);

        // Segunda línea del mismo producto: la rechaza el índice único.
        DB::table('detalle_pedidos')->insert([
            'pedido_id'       => $pedido->pedido_id,
            'producto_id'     => $producto->producto_id,
            'cantidad'        => 1,
            'precio_unitario' => 20.00,
            'subtotal'        => 20.00,
        ]);
    }

    // -----------------------------------------------------------------
    // RN-10: no se compran cantidades menores o iguales a cero
    // -----------------------------------------------------------------

    /** Capa de aplicación: `CarritoService::agregar()` exige cantidad positiva. */
    public function test_rn10_el_carrito_rechaza_agregar_una_cantidad_cero_o_negativa(): void
    {
        $carrito  = app(CarritoService::class);
        $producto = Producto::factory()->create();

        foreach ([0, -1, -25] as $cantidad) {
            try {
                $carrito->agregar($producto, $cantidad);
                $this->fail('RN-10: la cantidad ' . $cantidad . ' tenía que ser rechazada.');
            } catch (CantidadInvalidaException $e) {
                $this->assertSame('RN-10', $e->regla());
            }
        }

        $this->assertSame([], $carrito->items());
    }

    /** Capa de aplicación: tampoco se deja fijar una línea en cero. */
    public function test_rn10_el_carrito_rechaza_actualizar_una_linea_a_cero(): void
    {
        $carrito  = app(CarritoService::class);
        $producto = Producto::factory()->create(['precio' => '30.00']);
        $carrito->agregar($producto, 2);

        $this->expectException(CantidadInvalidaException::class);

        $carrito->actualizarCantidad($producto->producto_id, 0);
    }

    /** Capa de aplicación: el Form Request del carrito también corta el cero. */
    public function test_rn10_la_ruta_del_carrito_rechaza_una_cantidad_cero(): void
    {
        $cliente  = Usuario::factory()->cliente()->create();
        $producto = Producto::factory()->create();

        $respuesta = $this->actingAs($cliente)
            ->from(route('carrito'))
            ->post(route('carrito.agregar'), [
                'producto_id' => $producto->producto_id,
                'cantidad'    => 0,
            ]);

        $respuesta->assertSessionHasErrors('cantidad');
        $respuesta->assertSessionHas('resultado', fn (array $r): bool => $r['regla'] === 'RN-10' && $r['ok'] === false);
    }

    /** Capa del motor: `CHECK (cantidad > 0)` sobre `detalle_pedidos`. */
    public function test_rn10_el_motor_rechaza_una_linea_con_cantidad_cero(): void
    {
        $pedido   = Pedido::factory()->create();
        $producto = Producto::factory()->create();

        $this->expectException(QueryException::class);

        DB::table('detalle_pedidos')->insert([
            'pedido_id'       => $pedido->pedido_id,
            'producto_id'     => $producto->producto_id,
            'cantidad'        => 0,
            'precio_unitario' => 20.00,
            'subtotal'        => 0.00,
        ]);
    }

    /** Capa del motor: `CHECK (cantidad > 0)` tampoco admite negativos. */
    public function test_rn10_el_motor_rechaza_una_linea_con_cantidad_negativa(): void
    {
        $pedido   = Pedido::factory()->create();
        $producto = Producto::factory()->create();

        $this->expectException(QueryException::class);

        DB::table('detalle_pedidos')->insert([
            'pedido_id'       => $pedido->pedido_id,
            'producto_id'     => $producto->producto_id,
            'cantidad'        => -3,
            'precio_unitario' => 20.00,
            'subtotal'        => -60.00,
        ]);
    }

    // -----------------------------------------------------------------
    // RN-11: el precio de un pedido emitido ya no cambia
    // -----------------------------------------------------------------

    /**
     * El caso que defiende la regla: el catálogo sube de precio DESPUÉS de
     * emitir el pedido y el detalle conserva el precio histórico.
     */
    public function test_rn11_si_cambia_el_precio_del_catalogo_el_precio_del_detalle_no_cambia(): void
    {
        $cliente  = Usuario::factory()->cliente()->create();
        $producto = Producto::factory()->create(['precio' => '129.90', 'stock_actual' => 10]);

        $pedido = app(PedidoService::class)->confirmar(
            $cliente,
            [['producto_id' => $producto->producto_id, 'cantidad' => 2]],
            TipoOrigen::COMPRA_DIRECTA,
        );

        $this->assertDatabaseHas('detalle_pedidos', [
            'pedido_id'       => $pedido->pedido_id,
            'producto_id'     => $producto->producto_id,
            'precio_unitario' => '129.90',
            'subtotal'        => '259.80',
        ]);
        $this->assertSame('259.80', (string) $pedido->fresh()->monto_total);

        // El catálogo sube de precio después de emitido el pedido.
        $producto->precio = '199.90';
        $producto->save();

        $detalle = DetallePedido::query()->where('pedido_id', $pedido->pedido_id)->firstOrFail();

        $this->assertSame('199.90', (string) $producto->fresh()->precio, 'El catálogo sí cambia.');
        $this->assertSame('129.90', (string) $detalle->precio_unitario, 'RN-11: el precio del detalle es histórico.');
        $this->assertSame('259.80', (string) $detalle->subtotal);
        $this->assertSame('259.80', (string) $pedido->fresh()->monto_total, 'RN-11: el monto del pedido tampoco cambia.');
    }

    /** El precio del carrito se congela al agregar la línea. */
    public function test_rn11_el_carrito_congela_el_precio_al_agregar_la_linea(): void
    {
        $carrito  = app(CarritoService::class);
        $producto = Producto::factory()->create(['precio' => '49.50']);

        $carrito->agregar($producto, 2);

        $producto->precio = '99.00';
        $producto->save();

        $items = $carrito->items();

        $this->assertSame('49.50', $items[0]['precio_unitario']);
        $this->assertSame('99.00', $items[0]['subtotal'], 'El subtotal es 2 × 49.50, no 2 × el precio nuevo.');
        $this->assertSame('99.00', $carrito->total());
    }

    /** El subtotal se recalcula siempre: nunca queda desfasado de la cantidad. */
    public function test_rn11_el_subtotal_se_recalcula_al_cambiar_la_cantidad(): void
    {
        $carrito  = app(CarritoService::class);
        $producto = Producto::factory()->create(['precio' => '19.90']);

        $carrito->agregar($producto, 1);
        $this->assertSame('19.90', $carrito->items()[0]['subtotal']);

        $carrito->actualizarCantidad($producto->producto_id, 3);
        $this->assertSame('59.70', $carrito->items()[0]['subtotal']);
        $this->assertSame('59.70', $carrito->total());

        $carrito->quitar($producto->producto_id);
        $this->assertSame([], $carrito->items());
        $this->assertSame('0.00', $carrito->total());
    }
}
