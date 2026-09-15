<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Usuario;
use Database\Seeders\UsuarioSeeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * El ingreso real por formulario. Las demás pruebas usan actingAs(), que
 * salta la autenticación: aquí se comprueba que Auth::attempt funciona de
 * verdad contra la columna password_hash (RN-03) y que cada rol aterriza
 * en su propio módulo (RN-01).
 */
final class IngresoTest extends TestCase
{
    public function test_rn03_el_ingreso_funciona_con_la_contrasena_correcta(): void
    {
        $usuario = Usuario::factory()->cliente()->create([
            'correo' => 'prueba@correo.com',
            'password_hash' => Hash::make('demo123'),
        ]);

        $respuesta = $this->post(route('ingresar.enviar'), [
            'correo' => 'prueba@correo.com',
            'clave' => 'demo123',
        ]);

        $this->assertAuthenticatedAs($usuario);
        $respuesta->assertRedirect(route('catalogo'));
    }

    public function test_rn03_una_contrasena_equivocada_no_deja_entrar(): void
    {
        Usuario::factory()->cliente()->create([
            'correo' => 'prueba@correo.com',
            'password_hash' => Hash::make('demo123'),
        ]);

        $this->post(route('ingresar.enviar'), [
            'correo' => 'prueba@correo.com',
            'clave' => 'equivocada',
        ]);

        $this->assertGuest();
    }

    public function test_rn03_la_columna_password_hash_alimenta_la_autenticacion(): void
    {
        $usuario = Usuario::factory()->cliente()->create([
            'password_hash' => Hash::make('demo123'),
        ]);

        // Lo que se guarda no es el texto plano...
        $this->assertNotSame('demo123', $usuario->password_hash);
        // ...y aun así valida.
        $this->assertTrue(Hash::check('demo123', $usuario->getAuthPassword()));
        $this->assertTrue(Auth::attempt([
            'correo' => $usuario->correo,
            'password' => 'demo123',
        ]));
    }

    /**
     * Las cuentas que siembra el proyecto tienen que poder entrar de
     * verdad: es lo que se documenta para probar la plataforma.
     */
    public function test_rn01_cada_cuenta_sembrada_entra_y_aterriza_en_su_modulo(): void
    {
        $this->seed(UsuarioSeeder::class);

        $esperado = [
            'ana.quispe@correo.com' => 'catalogo',
            'lbernal@vetpetsurco.pe' => 'clinica',
            'admin@vetpetsurco.pe' => 'admin',
        ];

        foreach ($esperado as $correo => $ruta) {
            $this->post(route('ingresar.enviar'), ['correo' => $correo, 'clave' => 'demo123'])
                ->assertRedirect(route($ruta));
            $this->assertAuthenticated();
            $this->post(route('salir'));
            $this->assertGuest();
        }
    }
}
