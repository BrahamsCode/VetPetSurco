<?php

declare(strict_types=1);

namespace Tests\Feature\Reglas;

use App\Enums\EstadoSuscripcion;
use App\Models\Suscripcion;
use App\Models\Usuario;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Suscripción mensual: el ingreso recurrente del negocio.
 *
 *  - RN-15: el cliente pausa o cancela su plan cuando quiera.
 *  - RN-16: cada plan tiene frecuencia y fecha de próximo despacho.
 */
final class SuscripcionTest extends TestCase
{
    // -----------------------------------------------------------------
    // RN-15: el cliente pausa o cancela su plan cuando quiera
    // -----------------------------------------------------------------

    public function test_rn15_el_cliente_pausa_su_suscripcion_activa(): void
    {
        $cliente = Usuario::factory()->cliente()->create();
        $suscripcion = Suscripcion::factory()->create([
            'cliente_id' => $cliente->getKey(),
            'estado' => EstadoSuscripcion::ACTIVA,
        ]);

        $respuesta = $this->actingAs($cliente)->patch(
            route('suscripciones.estado', $suscripcion->getKey()),
            ['estado' => 'PAUSADA']
        );

        $respuesta->assertRedirect();
        $this->assertDatabaseHas('suscripciones', [
            'suscripcion_id' => $suscripcion->getKey(),
            'estado' => 'PAUSADA',
        ]);
    }

    public function test_rn15_una_suscripcion_pausada_se_reanuda(): void
    {
        $cliente = Usuario::factory()->cliente()->create();
        $suscripcion = Suscripcion::factory()->pausada()->create([
            'cliente_id' => $cliente->getKey(),
        ]);

        $this->actingAs($cliente)->patch(
            route('suscripciones.estado', $suscripcion->getKey()),
            ['estado' => 'ACTIVA']
        );

        $this->assertDatabaseHas('suscripciones', [
            'suscripcion_id' => $suscripcion->getKey(),
            'estado' => 'ACTIVA',
        ]);
    }

    public function test_rn15_el_cliente_cancela_su_suscripcion(): void
    {
        $cliente = Usuario::factory()->cliente()->create();
        $suscripcion = Suscripcion::factory()->create([
            'cliente_id' => $cliente->getKey(),
            'estado' => EstadoSuscripcion::ACTIVA,
        ]);

        $this->actingAs($cliente)->patch(
            route('suscripciones.estado', $suscripcion->getKey()),
            ['estado' => 'CANCELADA']
        );

        $this->assertDatabaseHas('suscripciones', [
            'suscripcion_id' => $suscripcion->getKey(),
            'estado' => 'CANCELADA',
        ]);
    }

    /**
     * Una suscripción cancelada no se reactiva: se contrata de nuevo.
     * El controlador tiene que impedirlo.
     */
    public function test_rn15_una_suscripcion_cancelada_no_se_reactiva(): void
    {
        $cliente = Usuario::factory()->cliente()->create();
        $suscripcion = Suscripcion::factory()->cancelada()->create([
            'cliente_id' => $cliente->getKey(),
        ]);

        $this->actingAs($cliente)->patch(
            route('suscripciones.estado', $suscripcion->getKey()),
            ['estado' => 'ACTIVA']
        );

        $this->assertDatabaseHas('suscripciones', [
            'suscripcion_id' => $suscripcion->getKey(),
            'estado' => 'CANCELADA',
        ]);
    }

    /**
     * RN-04 aplicada a las suscripciones: nadie toca el plan de otro.
     */
    public function test_rn15_un_cliente_no_puede_cambiar_la_suscripcion_de_otro(): void
    {
        $ana = Usuario::factory()->cliente()->create();
        $marco = Usuario::factory()->cliente()->create();
        $deMarco = Suscripcion::factory()->create([
            'cliente_id' => $marco->getKey(),
            'estado' => EstadoSuscripcion::ACTIVA,
        ]);

        $respuesta = $this->actingAs($ana)->patch(
            route('suscripciones.estado', $deMarco->getKey()),
            ['estado' => 'CANCELADA']
        );

        $this->assertNotSame(200, $respuesta->getStatusCode());
        $this->assertDatabaseHas('suscripciones', [
            'suscripcion_id' => $deMarco->getKey(),
            'estado' => 'ACTIVA',
        ]);
    }

    public function test_rn15_el_motor_rechaza_un_estado_que_no_existe(): void
    {
        $suscripcion = Suscripcion::factory()->create();

        $this->expectException(QueryException::class);
        DB::table('suscripciones')
            ->where('suscripcion_id', $suscripcion->getKey())
            ->update(['estado' => 'INVENTADO']);
    }

    // -----------------------------------------------------------------
    // RN-16: cada plan tiene frecuencia y fecha de próximo despacho
    // -----------------------------------------------------------------

    public function test_rn16_toda_suscripcion_guarda_frecuencia_y_proximo_despacho(): void
    {
        $suscripcion = Suscripcion::factory()->create([
            'frecuencia_dias' => 30,
            'proximo_despacho' => now()->addDays(12)->toDateString(),
        ]);

        $this->assertSame(30, (int) $suscripcion->frecuencia_dias);
        $this->assertNotNull($suscripcion->proximo_despacho);
    }

    public function test_rn16_el_motor_exige_la_fecha_de_proximo_despacho(): void
    {
        $base = Suscripcion::factory()->create();

        $this->expectException(QueryException::class);
        DB::table('suscripciones')->insert([
            'cliente_id' => $base->cliente_id,
            'mascota_id' => $base->mascota_id,
            'producto_id' => $base->producto_id,
            'plan' => 'BASICO',
            'frecuencia_dias' => 30,
            'monto_mensual' => '99.00',
            // falta proximo_despacho, que es NOT NULL
            'estado' => 'ACTIVA',
        ]);
    }

    public function test_rn16_la_pantalla_del_cliente_muestra_su_proximo_despacho(): void
    {
        $cliente = Usuario::factory()->cliente()->create();
        $fecha = now()->addDays(9)->toDateString();
        Suscripcion::factory()->create([
            'cliente_id' => $cliente->getKey(),
            'estado' => EstadoSuscripcion::ACTIVA,
            'proximo_despacho' => $fecha,
        ]);

        $respuesta = $this->actingAs($cliente)->get(route('mascotas'));

        $respuesta->assertOk();
        $respuesta->assertSee($fecha);
    }

    public function test_rn16_el_cliente_solo_ve_sus_propias_suscripciones(): void
    {
        $ana = Usuario::factory()->cliente()->create();
        $marco = Usuario::factory()->cliente()->create(['nombre' => 'Marco Salazar']);

        Suscripcion::factory()->create(['cliente_id' => $ana->getKey(), 'plan' => 'BASICO']);
        Suscripcion::factory()->create(['cliente_id' => $marco->getKey(), 'plan' => 'INTEGRAL']);

        $respuesta = $this->actingAs($ana)->get(route('mascotas'));

        $respuesta->assertOk();
        $respuesta->assertDontSee('INTEGRAL');
    }
}
