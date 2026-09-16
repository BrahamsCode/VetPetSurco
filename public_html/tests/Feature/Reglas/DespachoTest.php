<?php

declare(strict_types=1);

namespace Tests\Feature\Reglas;

use App\Enums\EstadoPedido;
use App\Enums\EstadoSuscripcion;
use App\Enums\TipoOrigen;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Suscripcion;
use App\Models\Usuario;
use App\Services\DespachoService;
use Tests\TestCase;

/**
 * Despacho automatico de las suscripciones: el ingreso recurrente de verdad.
 *
 *  - RN-12: sin stock no se despacha y no queda nada a medias.
 *  - RN-14: el pedido generado nace marcado como SUSCRIPCION.
 *  - RN-15: una suscripcion pausada no despacha.
 *  - RN-16: al despachar, la fecha salta un ciclo completo.
 */
final class DespachoTest extends TestCase
{
    public function test_rn14_una_suscripcion_vencida_genera_su_pedido_recurrente(): void
    {
        $cliente = Usuario::factory()->cliente()->create();
        $producto = Producto::factory()->create(['precio' => '129.90', 'stock_actual' => 10]);

        $suscripcion = Suscripcion::factory()->create([
            'cliente_id' => $cliente->getKey(),
            'producto_id' => $producto->getKey(),
            'plan' => 'BASICO',               // 1 unidad por despacho
            'estado' => EstadoSuscripcion::ACTIVA,
            'frecuencia_dias' => 30,
            'proximo_despacho' => now()->subDay()->toDateString(),
        ]);

        $resultado = app(DespachoService::class)->generarPendientes();

        $this->assertCount(1, $resultado['despachados']);
        $this->assertSame([], $resultado['omitidos']);

        $pedido = Pedido::query()->where('cliente_id', $cliente->getKey())->sole();
        $this->assertSame(TipoOrigen::SUSCRIPCION, $pedido->tipo_origen);
        // RN-21: el despacho tampoco se da por pagado solo.
        $this->assertSame(EstadoPedido::PENDIENTE, $pedido->estado);
        // RN-11: el producto sale al precio vigente del catalogo.
        $this->assertSame('129.90', (string) $pedido->monto_total);

        // El inventario se movio de verdad.
        $this->assertSame(9, (int) $producto->fresh()->stock_actual);
        $this->assertSame($suscripcion->getKey(), $resultado['despachados'][0]['suscripcion']);
    }

    /** Lo que diferencia a los planes: cuantas unidades entran en el despacho. */
    public function test_el_plan_decide_cuantas_unidades_lleva_el_despacho(): void
    {
        $cliente = Usuario::factory()->cliente()->create();
        $producto = Producto::factory()->create(['precio' => '20.00', 'stock_actual' => 10]);

        Suscripcion::factory()->create([
            'cliente_id' => $cliente->getKey(),
            'producto_id' => $producto->getKey(),
            'plan' => 'INTEGRAL',             // 3 unidades por despacho
            'estado' => EstadoSuscripcion::ACTIVA,
            'proximo_despacho' => now()->subDay()->toDateString(),
        ]);

        app(DespachoService::class)->generarPendientes();

        $pedido = Pedido::query()->where('cliente_id', $cliente->getKey())->sole();
        $this->assertSame('60.00', (string) $pedido->monto_total);
        $this->assertSame(3, (int) $pedido->detalles()->sole()->cantidad);
        $this->assertSame(7, (int) $producto->fresh()->stock_actual);
    }

    /** RN-16: el siguiente ciclo cuenta desde la fecha que tocaba, no desde hoy. */
    public function test_rn16_tras_despachar_la_fecha_salta_un_ciclo_completo(): void
    {
        $cliente = Usuario::factory()->cliente()->create();
        $producto = Producto::factory()->create(['stock_actual' => 10]);
        $vencia = now()->subDays(3)->startOfDay();

        $suscripcion = Suscripcion::factory()->create([
            'cliente_id' => $cliente->getKey(),
            'producto_id' => $producto->getKey(),
            'estado' => EstadoSuscripcion::ACTIVA,
            'frecuencia_dias' => 30,
            'proximo_despacho' => $vencia->toDateString(),
        ]);

        app(DespachoService::class)->generarPendientes();

        $this->assertSame(
            $vencia->copy()->addDays(30)->toDateString(),
            $suscripcion->fresh()->proximo_despacho->toDateString(),
        );
    }

    public function test_una_suscripcion_que_aun_no_vence_no_se_despacha(): void
    {
        $cliente = Usuario::factory()->cliente()->create();
        $producto = Producto::factory()->create(['stock_actual' => 10]);

        Suscripcion::factory()->create([
            'cliente_id' => $cliente->getKey(),
            'producto_id' => $producto->getKey(),
            'estado' => EstadoSuscripcion::ACTIVA,
            'proximo_despacho' => now()->addDays(5)->toDateString(),
        ]);

        $resultado = app(DespachoService::class)->generarPendientes();

        $this->assertSame([], $resultado['despachados']);
        $this->assertSame(0, Pedido::query()->count());
    }

    /** RN-15: mientras esta pausada no llega nada a casa. */
    public function test_rn15_una_suscripcion_pausada_no_despacha(): void
    {
        $cliente = Usuario::factory()->cliente()->create();
        $producto = Producto::factory()->create(['stock_actual' => 10]);

        Suscripcion::factory()->pausada()->create([
            'cliente_id' => $cliente->getKey(),
            'producto_id' => $producto->getKey(),
            'proximo_despacho' => now()->subDay()->toDateString(),
        ]);

        $resultado = app(DespachoService::class)->generarPendientes();

        $this->assertSame([], $resultado['despachados']);
        $this->assertSame(0, Pedido::query()->count());
    }

    /**
     * RN-12: sin stock no se despacha. Y lo importante: la fecha NO avanza, asi
     * que el despacho se recupera en cuanto repongan.
     */
    public function test_rn12_sin_stock_no_se_despacha_y_la_fecha_no_avanza(): void
    {
        $cliente = Usuario::factory()->cliente()->create();
        $producto = Producto::factory()->create(['stock_actual' => 0]);
        $vencia = now()->subDay()->startOfDay();

        $suscripcion = Suscripcion::factory()->create([
            'cliente_id' => $cliente->getKey(),
            'producto_id' => $producto->getKey(),
            'estado' => EstadoSuscripcion::ACTIVA,
            'proximo_despacho' => $vencia->toDateString(),
        ]);

        $resultado = app(DespachoService::class)->generarPendientes();

        $this->assertSame([], $resultado['despachados']);
        $this->assertCount(1, $resultado['omitidos']);
        $this->assertSame(0, Pedido::query()->count());
        $this->assertSame(
            $vencia->toDateString(),
            $suscripcion->fresh()->proximo_despacho->toDateString(),
        );
    }

    /** Un cliente con dos planes vencidos recibe los dos despachos. */
    public function test_se_despachan_todas_las_suscripciones_vencidas(): void
    {
        $cliente = Usuario::factory()->cliente()->create();
        $uno = Producto::factory()->create(['stock_actual' => 5]);
        $otro = Producto::factory()->create(['stock_actual' => 5]);

        foreach ([$uno, $otro] as $producto) {
            Suscripcion::factory()->create([
                'cliente_id' => $cliente->getKey(),
                'producto_id' => $producto->getKey(),
                'estado' => EstadoSuscripcion::ACTIVA,
                'proximo_despacho' => now()->subDay()->toDateString(),
            ]);
        }

        $resultado = app(DespachoService::class)->generarPendientes();

        $this->assertCount(2, $resultado['despachados']);
        $this->assertSame(2, Pedido::query()->where('tipo_origen', TipoOrigen::SUSCRIPCION->value)->count());
    }

    public function test_el_comando_de_consola_emite_los_despachos(): void
    {
        $cliente = Usuario::factory()->cliente()->create();
        $producto = Producto::factory()->create(['stock_actual' => 5]);

        Suscripcion::factory()->create([
            'cliente_id' => $cliente->getKey(),
            'producto_id' => $producto->getKey(),
            'estado' => EstadoSuscripcion::ACTIVA,
            'proximo_despacho' => now()->subDay()->toDateString(),
        ]);

        $this->artisan('suscripciones:despachar')->assertSuccessful();

        $this->assertSame(1, Pedido::query()->where('tipo_origen', TipoOrigen::SUSCRIPCION->value)->count());
    }
}
