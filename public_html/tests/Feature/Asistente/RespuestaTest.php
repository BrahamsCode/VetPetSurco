<?php

declare(strict_types=1);

namespace Tests\Feature\Asistente;

use App\Models\Mascota;
use App\Models\Producto;
use App\Models\Usuario;
use App\Services\Asistente\AsistenteService;
use Database\Seeders\ProductoSeeder;
use Tests\TestCase;

/**
 * Lo que el asistente contesta, no solo lo que entiende.
 *
 *  - RN-01 y RN-04: solo responde con datos del cliente conectado.
 *  - RN-07: el stock que informa es el real del catalogo.
 */
final class RespuestaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ProductoSeeder::class);
    }

    /**
     * El fallo que reporto el equipo: preguntar por el alimento de gato
     * devolvia el de perro, porque bastaba con que coincidiera "alimento".
     */
    public function test_el_stock_responde_por_el_producto_que_se_pregunto(): void
    {
        $cliente = Usuario::factory()->cliente()->create();

        $respuesta = app(AsistenteService::class)
            ->responder('hay stock de alimento para gato', $cliente)['respuesta'];

        $this->assertStringContainsString('gato', mb_strtolower($respuesta));
        $this->assertStringNotContainsString('perro', mb_strtolower($respuesta));
    }

    /** El numero que informa es el de la tabla, no uno inventado. */
    public function test_el_stock_informado_es_el_real(): void
    {
        $cliente = Usuario::factory()->cliente()->create();
        $arena = Producto::query()->where('codigo_sku', 'ARE-SAN-10K')->firstOrFail();

        $respuesta = app(AsistenteService::class)
            ->responder('queda arena sanitaria', $cliente)['respuesta'];

        $this->assertStringContainsString((string) $arena->stock_actual, $respuesta);
    }

    /** Preguntar por las mascotas propias no puede reventar ni mostrar ajenas. */
    public function test_las_mascotas_son_las_del_cliente_conectado(): void
    {
        $cliente = Usuario::factory()->cliente()->create();
        $otro = Usuario::factory()->cliente()->create();

        Mascota::factory()->create(['cliente_id' => $cliente->getKey(), 'nombre' => 'Rocky']);
        Mascota::factory()->create(['cliente_id' => $otro->getKey(), 'nombre' => 'Michi']);

        $respuesta = app(AsistenteService::class)
            ->responder('cuales son mis mascotas', $cliente)['respuesta'];

        $this->assertStringContainsString('Rocky', $respuesta);
        $this->assertStringNotContainsString('Michi', $respuesta);
    }

    /** Cuando no entiende, lo dice: no contesta cualquier cosa. */
    public function test_ante_una_pregunta_ajena_admite_que_no_entiende(): void
    {
        $cliente = Usuario::factory()->cliente()->create();

        $salida = app(AsistenteService::class)->responder('cual es la capital de francia', $cliente);

        $this->assertNull($salida['intencion']);
        $this->assertNotSame([], $salida['sugerencias']);
    }
}
