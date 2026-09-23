<?php

declare(strict_types=1);

namespace Tests\Feature\Reglas;

use App\Models\Usuario;
use Tests\TestCase;

/**
 * Alta de mascotas del cliente.
 *
 *  - RN-04: toda mascota pertenece a un solo cliente registrado, el de la
 *    sesion; el formulario nunca decide a nombre de quien se guarda.
 */
final class MascotaTest extends TestCase
{
    /** La mascota queda a nombre de la cuenta que inicio sesion. */
    public function test_rn04_el_alta_registra_la_mascota_a_nombre_del_cliente_de_la_sesion(): void
    {
        $cliente = Usuario::factory()->cliente()->create();

        $respuesta = $this->actingAs($cliente)->post('/app/mascotas', [
            'nombre' => 'Toby',
            'especie' => 'PERRO',
            'raza' => 'Mestizo',
        ]);

        $respuesta->assertRedirect();
        $this->assertDatabaseHas('mascotas', [
            'cliente_id' => $cliente->usuario_id,
            'nombre' => 'Toby',
            'especie' => 'PERRO',
        ]);
    }

    /** Capa de aplicacion: un cliente_id ajeno en el form se ignora. */
    public function test_rn04_no_se_puede_registrar_una_mascota_a_nombre_de_otro_cliente(): void
    {
        $duena = Usuario::factory()->cliente()->create();
        $otro = Usuario::factory()->cliente()->create();

        $this->actingAs($duena)->post('/app/mascotas', [
            'cliente_id' => $otro->usuario_id,
            'nombre' => 'Luna',
            'especie' => 'GATO',
        ]);

        $this->assertDatabaseHas('mascotas', ['nombre' => 'Luna', 'cliente_id' => $duena->usuario_id]);
        $this->assertDatabaseMissing('mascotas', ['nombre' => 'Luna', 'cliente_id' => $otro->usuario_id]);
    }

    /** La especie se valida contra el catalogo del enum. */
    public function test_rn04_una_especie_fuera_del_catalogo_se_rechaza(): void
    {
        $cliente = Usuario::factory()->cliente()->create();

        $respuesta = $this->actingAs($cliente)->from('/app/mascotas')->post('/app/mascotas', [
            'nombre' => 'Roco',
            'especie' => 'DRAGON',
        ]);

        $respuesta->assertSessionHasErrors('especie');
        $this->assertDatabaseMissing('mascotas', ['nombre' => 'Roco']);
    }

    /** Sin sesion no hay alta: la plataforma redirige al ingreso. */
    public function test_rn04_sin_sesion_no_se_puede_registrar_una_mascota(): void
    {
        $respuesta = $this->post('/app/mascotas', [
            'nombre' => 'Fantasma',
            'especie' => 'GATO',
        ]);

        $respuesta->assertRedirect(route('ingresar'));
        $this->assertDatabaseMissing('mascotas', ['nombre' => 'Fantasma']);
    }
}
