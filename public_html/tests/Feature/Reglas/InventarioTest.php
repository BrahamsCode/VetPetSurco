<?php

declare(strict_types=1);

namespace Tests\Feature\Reglas;

use App\Enums\CategoriaProducto;
use App\Enums\Semaforo;
use App\Enums\TipoOrigen;
use App\Exceptions\StockInsuficienteException;
use App\Models\Producto;
use App\Models\Usuario;
use App\Services\InventarioService;
use App\Services\PedidoService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Catálogo e inventario.
 *
 *  - RN-05: cada producto tiene un SKU irrepetible.
 *  - RN-06: ningún producto se vende a precio cero o negativo.
 *  - RN-07: el inventario nunca queda en negativo.
 *  - RN-08: el semáforo decide cuándo reponer.
 *
 * RN-05, RN-06 y RN-07 viven en dos capas: el Form Request del panel
 * administrativo y, por debajo, el índice UNIQUE y los CHECK del motor.
 */
final class InventarioTest extends TestCase
{
    /** Datos mínimos válidos del formulario de alta de producto. */
    private function formulario(array $cambios = []): array
    {
        return array_merge([
            'codigo_sku'    => 'ALI-NUE-01',
            'nombre'        => 'Alimento nuevo',
            'categoria'     => CategoriaProducto::ALIMENTO->value,
            'precio'        => '89.90',
            'stock_actual'  => 20,
            'punto_reorden' => 5,
        ], $cambios);
    }

    // -----------------------------------------------------------------
    // RN-05: cada producto tiene un SKU irrepetible
    // -----------------------------------------------------------------

    /** Capa de aplicación: `CrearProductoRequest` rechaza el SKU repetido. */
    public function test_rn05_el_alta_rechaza_un_sku_ya_registrado(): void
    {
        $admin = Usuario::factory()->admin()->create();
        Producto::factory()->create(['codigo_sku' => 'ALI-PER-15K']);

        $respuesta = $this->actingAs($admin)
            ->from(route('admin'))
            ->post(route('admin.productos.crear'), $this->formulario(['codigo_sku' => 'ALI-PER-15K']));

        $respuesta->assertSessionHasErrors('codigo_sku');
        $respuesta->assertSessionHas('resultado', fn (array $r): bool => $r['regla'] === 'RN-05' && $r['ok'] === false);

        // El catálogo sigue teniendo un solo producto con ese SKU.
        $this->assertDatabaseCount('productos', 1);
    }

    /** Capa del motor: el índice UNIQUE `productos_codigo_sku_unique`. */
    public function test_rn05_el_motor_rechaza_un_sku_duplicado(): void
    {
        Producto::factory()->create(['codigo_sku' => 'ALI-PER-15K']);

        $this->expectException(QueryException::class);

        // INSERT directo: se salta el Form Request y el modelo.
        DB::table('productos')->insert([
            'codigo_sku'    => 'ALI-PER-15K',
            'nombre'        => 'Duplicado',
            'categoria'     => CategoriaProducto::ALIMENTO->value,
            'precio'        => 50.00,
            'stock_actual'  => 10,
            'punto_reorden' => 5,
            'activo'        => 1,
        ]);
    }

    /** Un SKU libre sí se da de alta. */
    public function test_rn05_un_sku_libre_si_registra_el_producto(): void
    {
        $admin = Usuario::factory()->admin()->create();

        $this->actingAs($admin)
            ->from(route('admin'))
            ->post(route('admin.productos.crear'), $this->formulario(['codigo_sku' => 'acc-nue-01']))
            ->assertSessionHasNoErrors();

        // El Form Request normaliza el SKU a mayúsculas antes de validarlo.
        $this->assertDatabaseHas('productos', ['codigo_sku' => 'ACC-NUE-01']);
    }

    // -----------------------------------------------------------------
    // RN-06: ningún producto se vende a precio cero o negativo
    // -----------------------------------------------------------------

    /** Capa de aplicación: `numeric|gt:0` sobre el precio. */
    public function test_rn06_el_alta_rechaza_un_precio_cero_o_negativo(): void
    {
        $admin = Usuario::factory()->admin()->create();

        foreach (['0', '0.00', '-15.00'] as $precio) {
            $respuesta = $this->actingAs($admin)
                ->from(route('admin'))
                ->post(route('admin.productos.crear'), $this->formulario(['precio' => $precio]));

            $respuesta->assertSessionHasErrors('precio');
            $respuesta->assertSessionHas('resultado', fn (array $r): bool => $r['regla'] === 'RN-06');
        }

        $this->assertDatabaseCount('productos', 0);
    }

    /** Capa del motor: `CHECK (precio > 0)`. */
    public function test_rn06_el_motor_rechaza_un_precio_cero(): void
    {
        $this->expectException(QueryException::class);

        DB::table('productos')->insert([
            'codigo_sku'    => 'ALI-GRA-00',
            'nombre'        => 'Producto gratis',
            'categoria'     => CategoriaProducto::ALIMENTO->value,
            'precio'        => 0,
            'stock_actual'  => 10,
            'punto_reorden' => 5,
            'activo'        => 1,
        ]);
    }

    /** Capa del motor: `CHECK (precio > 0)` también con precio negativo. */
    public function test_rn06_el_motor_rechaza_un_precio_negativo(): void
    {
        $this->expectException(QueryException::class);

        DB::table('productos')->insert([
            'codigo_sku'    => 'ALI-NEG-01',
            'nombre'        => 'Producto con precio negativo',
            'categoria'     => CategoriaProducto::ALIMENTO->value,
            'precio'        => -1.00,
            'stock_actual'  => 10,
            'punto_reorden' => 5,
            'activo'        => 1,
        ]);
    }

    // -----------------------------------------------------------------
    // RN-07: el inventario nunca queda en negativo
    // -----------------------------------------------------------------

    /** Capa del motor: `CHECK (stock_actual >= 0)` al insertar. */
    public function test_rn07_el_motor_rechaza_un_stock_inicial_negativo(): void
    {
        $this->expectException(QueryException::class);

        DB::table('productos')->insert([
            'codigo_sku'    => 'ALI-NEG-02',
            'nombre'        => 'Producto con stock negativo',
            'categoria'     => CategoriaProducto::ALIMENTO->value,
            'precio'        => 50.00,
            'stock_actual'  => -1,
            'punto_reorden' => 5,
            'activo'        => 1,
        ]);
    }

    /** Capa del motor: el CHECK también impide dejar el stock negativo con un UPDATE. */
    public function test_rn07_el_motor_rechaza_un_update_que_deja_el_stock_negativo(): void
    {
        $producto = Producto::factory()->create(['stock_actual' => 3]);

        $this->expectException(QueryException::class);

        // Descontar 10 unidades de un stock de 3 es exactamente la sobreventa
        // que la regla prohíbe (RNF-03).
        DB::table('productos')
            ->where('producto_id', $producto->producto_id)
            ->update(['stock_actual' => DB::raw('stock_actual - 10')]);
    }

    /** Capa de aplicación: la compra descuenta el stock y nunca lo deja negativo. */
    public function test_rn07_la_compra_descuenta_el_stock_sin_dejarlo_negativo(): void
    {
        $cliente  = Usuario::factory()->cliente()->create();
        $producto = Producto::factory()->create(['stock_actual' => 24, 'precio' => '129.90']);

        app(PedidoService::class)->confirmar(
            $cliente,
            [['producto_id' => $producto->producto_id, 'cantidad' => 1]],
            TipoOrigen::COMPRA_DIRECTA,
        );

        $this->assertSame(23, (int) $producto->fresh()->stock_actual);
        $this->assertGreaterThanOrEqual(0, (int) $producto->fresh()->stock_actual);
    }

    /** Capa de aplicación: pedir más de lo que hay no toca el inventario. */
    public function test_rn07_un_pedido_mayor_que_el_stock_deja_el_inventario_intacto(): void
    {
        $cliente  = Usuario::factory()->cliente()->create();
        $producto = Producto::factory()->create(['stock_actual' => 3]);

        try {
            app(PedidoService::class)->confirmar(
                $cliente,
                [['producto_id' => $producto->producto_id, 'cantidad' => 99]],
                TipoOrigen::COMPRA_DIRECTA,
            );
            $this->fail('RN-07/RN-12: pedir 99 unidades de un stock de 3 tenía que fallar.');
        } catch (StockInsuficienteException $e) {
            $this->assertSame('RN-12', $e->regla());
        }

        $this->assertSame(3, (int) $producto->fresh()->stock_actual);
    }

    // -----------------------------------------------------------------
    // RN-08: el semáforo decide cuándo reponer
    // -----------------------------------------------------------------

    /** El semáforo depende de `stock_actual` frente a `punto_reorden`. */
    public function test_rn08_el_semaforo_distingue_rojo_ambar_y_verde(): void
    {
        $inventario = app(InventarioService::class);

        $agotado = Producto::factory()->create(['stock_actual' => 0, 'punto_reorden' => 5]);
        $justo   = Producto::factory()->create(['stock_actual' => 5, 'punto_reorden' => 5]);
        $ajustado = Producto::factory()->create(['stock_actual' => 8, 'punto_reorden' => 5]);
        $holgado = Producto::factory()->create(['stock_actual' => 40, 'punto_reorden' => 5]);

        $this->assertSame(Semaforo::ROJO, $inventario->semaforo($agotado));
        // El punto de reorden exacto ya es ROJO: hay que reponer.
        $this->assertSame(Semaforo::ROJO, $inventario->semaforo($justo));
        $this->assertSame(Semaforo::AMBAR, $inventario->semaforo($ajustado));
        $this->assertSame(Semaforo::VERDE, $inventario->semaforo($holgado));
    }

    /** `porReponer()` lista exactamente los productos activos en ROJO. */
    public function test_rn08_por_reponer_lista_solo_los_productos_en_rojo(): void
    {
        $inventario = app(InventarioService::class);

        $agotado = Producto::factory()->create(['stock_actual' => 0, 'punto_reorden' => 5]);
        $justo   = Producto::factory()->create(['stock_actual' => 5, 'punto_reorden' => 5]);
        Producto::factory()->create(['stock_actual' => 40, 'punto_reorden' => 5]);
        // Un producto retirado del catálogo no entra en la alerta de reposición.
        Producto::factory()->inactivo()->create(['stock_actual' => 0, 'punto_reorden' => 5]);

        $porReponer = $inventario->porReponer();

        $this->assertCount(2, $porReponer);
        $this->assertEqualsCanonicalizing(
            [$agotado->producto_id, $justo->producto_id],
            $porReponer->pluck('producto_id')->all(),
        );
    }

    /** El semáforo se recalcula solo: al bajar el stock, el producto pasa a ROJO. */
    public function test_rn08_una_compra_puede_dejar_el_producto_en_rojo(): void
    {
        $inventario = app(InventarioService::class);
        $cliente    = Usuario::factory()->cliente()->create();
        $producto   = Producto::factory()->create(['stock_actual' => 6, 'punto_reorden' => 5]);

        $this->assertSame(Semaforo::AMBAR, $inventario->semaforo($producto));

        app(PedidoService::class)->confirmar(
            $cliente,
            [['producto_id' => $producto->producto_id, 'cantidad' => 2]],
            TipoOrigen::COMPRA_DIRECTA,
        );

        $this->assertSame(Semaforo::ROJO, $inventario->semaforo($producto->fresh()));
    }
}
