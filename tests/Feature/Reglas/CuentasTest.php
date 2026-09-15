<?php

declare(strict_types=1);

namespace Tests\Feature\Reglas;

use App\Enums\Rol;
use App\Models\Usuario;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Cuentas de la plataforma.
 *
 *  - RN-01: cada persona tiene un solo rol y es excluyente.
 *  - RN-02: un correo identifica a una sola cuenta.
 *  - RN-03: la contraseña nunca se guarda legible.
 *
 * Cada regla se comprueba en las dos capas donde existe: la de la aplicación
 * (middleware, Form Request, Hash) y la del motor (columna ENUM, índice UNIQUE).
 */
final class CuentasTest extends TestCase
{
    // -----------------------------------------------------------------
    // RN-01: cada persona tiene un solo rol y es excluyente
    // -----------------------------------------------------------------

    /** Capa de aplicación: el middleware `EnsureRol` cierra el paso. */
    public function test_rn01_un_cliente_que_pide_el_modulo_de_admin_no_recibe_200(): void
    {
        $cliente = Usuario::factory()->cliente()->create();

        $respuesta = $this->actingAs($cliente)->get('/app/admin');

        $this->assertNotSame(200, $respuesta->getStatusCode(), 'RN-01: un CLIENTE no puede abrir el módulo del ADMIN.');
        $respuesta->assertForbidden();
    }

    /** Capa de aplicación: el rol es excluyente en las tres direcciones. */
    public function test_rn01_cada_rol_solo_entra_a_los_modulos_de_su_rol(): void
    {
        $cliente     = Usuario::factory()->cliente()->create();
        $veterinario = Usuario::factory()->veterinario()->create();
        $admin       = Usuario::factory()->admin()->create();

        // El CLIENTE no entra ni a la clínica ni al panel administrativo.
        $this->actingAs($cliente)->get('/app/clinica')->assertForbidden();
        $this->actingAs($cliente)->get('/app/admin')->assertForbidden();

        // El VETERINARIO no entra al catálogo del cliente ni al panel de admin.
        $this->actingAs($veterinario)->get('/app/catalogo')->assertForbidden();
        $this->actingAs($veterinario)->get('/app/admin')->assertForbidden();

        // El ADMIN no entra a los módulos de cliente ni de veterinario.
        $this->actingAs($admin)->get('/app/catalogo')->assertForbidden();
        $this->actingAs($admin)->get('/app/clinica')->assertForbidden();
    }

    /** Capa de aplicación: sin sesión abierta no hay rol, se vuelve al ingreso. */
    public function test_rn01_sin_sesion_los_modulos_de_la_plataforma_devuelven_al_ingreso(): void
    {
        $this->get('/app/catalogo')->assertRedirect(route('ingresar'));
        $this->get('/app/clinica')->assertRedirect(route('ingresar'));
        $this->get('/app/admin')->assertRedirect(route('ingresar'));
    }

    /** Capa del motor: la columna ENUM `usuarios.rol` no admite un cuarto rol. */
    public function test_rn01_el_motor_rechaza_un_rol_que_no_existe_en_el_enum(): void
    {
        $this->expectException(QueryException::class);

        // Se salta el modelo a propósito: el INSERT va directo contra la tabla.
        DB::table('usuarios')->insert([
            'nombre'        => 'Rol inventado',
            'correo'        => 'inventado@vetpetsurco.pe',
            'password_hash' => Hash::make('demo123'),
            'rol'           => 'SUPERUSUARIO',
        ]);
    }

    /** El enum de PHP y la columna ENUM declaran exactamente los mismos roles. */
    public function test_rn01_el_enum_rol_tiene_los_tres_roles_del_catalogo(): void
    {
        $this->assertSame(
            ['CLIENTE', 'VETERINARIO', 'ADMIN'],
            array_column(Rol::cases(), 'value'),
        );
    }

    // -----------------------------------------------------------------
    // RN-02: un correo identifica a una sola cuenta
    // -----------------------------------------------------------------

    /** Capa de aplicación: `RegistroRequest` rechaza el correo repetido. */
    public function test_rn02_el_registro_rechaza_un_correo_ya_usado(): void
    {
        Usuario::factory()->cliente()->create(['correo' => 'ana@correo.com']);

        $respuesta = $this->from(route('registro'))->post(route('registro.enviar'), [
            'nombre'            => 'Otra Ana',
            'correo'            => 'ana@correo.com',
            'clave'             => 'clave123',
            'clave_confirmation' => 'clave123',
        ]);

        $respuesta->assertSessionHasErrors('correo');
        $respuesta->assertSessionHas('resultado', fn (array $r): bool => $r['regla'] === 'RN-02' && $r['ok'] === false);

        // No se creó una segunda cuenta con ese correo.
        $this->assertSame(1, Usuario::query()->where('correo', 'ana@correo.com')->count());
        $this->assertGuest();
    }

    /** Capa del motor: el índice UNIQUE sobre `usuarios.correo` es la barrera final. */
    public function test_rn02_el_motor_rechaza_un_correo_duplicado(): void
    {
        Usuario::factory()->cliente()->create(['correo' => 'ana@correo.com']);

        $this->expectException(QueryException::class);

        // INSERT directo, sin pasar por el Form Request ni por el modelo.
        DB::table('usuarios')->insert([
            'nombre'        => 'Ana repetida',
            'correo'        => 'ana@correo.com',
            'password_hash' => Hash::make('demo123'),
            'rol'           => Rol::CLIENTE->value,
        ]);
    }

    /** Un correo libre sí se registra: la regla no bloquea lo legítimo. */
    public function test_rn02_un_correo_libre_si_crea_la_cuenta(): void
    {
        $respuesta = $this->post(route('registro.enviar'), [
            'nombre'            => 'Nueva Clienta',
            'correo'            => 'nueva@correo.com',
            'clave'             => 'clave123',
            'clave_confirmation' => 'clave123',
        ]);

        $respuesta->assertRedirect(route('catalogo'));
        $this->assertDatabaseHas('usuarios', ['correo' => 'nueva@correo.com', 'rol' => Rol::CLIENTE->value]);
    }

    // -----------------------------------------------------------------
    // RN-03: la contraseña nunca se guarda legible
    // -----------------------------------------------------------------

    /** La columna guarda el hash BCrypt y `Hash::check` lo valida. */
    public function test_rn03_la_contrasena_almacenada_no_es_el_texto_plano(): void
    {
        $this->post(route('registro.enviar'), [
            'nombre'            => 'Nueva Clienta',
            'correo'            => 'nueva@correo.com',
            'clave'             => 'clave123',
            'clave_confirmation' => 'clave123',
        ])->assertRedirect(route('catalogo'));

        // Se lee la fila cruda: así se ve exactamente lo que quedó guardado.
        $fila = DB::table('usuarios')->where('correo', 'nueva@correo.com')->first();

        $this->assertNotNull($fila);
        $this->assertNotSame('clave123', $fila->password_hash, 'RN-03: jamás se guarda la contraseña legible.');
        $this->assertStringNotContainsString('clave123', $fila->password_hash);
        $this->assertStringStartsWith('$2y$', $fila->password_hash, 'RN-03: el hash es BCrypt (RNF-02).');
        $this->assertTrue(Hash::check('clave123', $fila->password_hash), 'RN-03: el hash valida la contraseña original.');
        $this->assertFalse(Hash::check('otraclave', $fila->password_hash));
    }

    /** El ingreso compara contra el hash, no contra texto plano. */
    public function test_rn03_el_ingreso_valida_la_contrasena_contra_el_hash(): void
    {
        $cliente = Usuario::factory()->cliente()->create([
            'correo'        => 'ana@correo.com',
            'password_hash' => Hash::make('demo123'),
        ]);

        // Contraseña equivocada: no entra.
        $this->post(route('ingresar.enviar'), ['correo' => 'ana@correo.com', 'clave' => 'noesesta'])
            ->assertRedirect();
        $this->assertGuest();

        // Contraseña correcta: entra y aterriza en el módulo de su rol (RN-01).
        $this->post(route('ingresar.enviar'), ['correo' => 'ana@correo.com', 'clave' => 'demo123'])
            ->assertRedirect(route('catalogo'));
        $this->assertAuthenticatedAs($cliente);

        // El modelo expone el hash como contraseña de autenticación.
        $this->assertSame($cliente->password_hash, Auth::user()->getAuthPassword());
    }

    /** El hash nunca viaja en la serialización del modelo. */
    public function test_rn03_el_modelo_oculta_el_hash_al_serializarse(): void
    {
        $cliente = Usuario::factory()->cliente()->create();

        $this->assertArrayNotHasKey('password_hash', $cliente->toArray());
    }
}
