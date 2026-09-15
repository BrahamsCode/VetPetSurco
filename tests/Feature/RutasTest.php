<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Usuario;
use Tests\TestCase;

/**
 * Las rutas del contrato responden y el guardia de rol (RN-01) bloquea
 * a quien no corresponde.
 */
final class RutasTest extends TestCase
{
    /** Las páginas institucionales son públicas. */
    public static function rutasPublicas(): array
    {
        return [
            'inicio' => ['inicio'],
            'nosotros' => ['nosotros'],
            'productos' => ['productos'],
            'contacto' => ['contacto'],
        ];
    }

    /** @dataProvider rutasPublicas */
    public function test_la_pagina_publica_responde_sin_sesion(string $ruta): void
    {
        $this->get(route($ruta))->assertOk();
    }

    public function test_el_ingreso_es_accesible_para_un_invitado(): void
    {
        $this->get(route('ingresar'))->assertOk();
        $this->get(route('registro'))->assertOk();
    }

    /** Cada módulo de la plataforma con el rol que le corresponde. */
    public static function modulosPorRol(): array
    {
        return [
            'catálogo del cliente' => ['catalogo', 'cliente'],
            'carrito del cliente' => ['carrito', 'cliente'],
            'citas del cliente' => ['citas', 'cliente'],
            'mascotas del cliente' => ['mascotas', 'cliente'],
            'ficha clínica del veterinario' => ['clinica', 'veterinario'],
            'dashboard del administrador' => ['admin', 'admin'],
        ];
    }

    /** @dataProvider modulosPorRol */
    public function test_el_rol_correcto_entra_a_su_modulo(string $ruta, string $rol): void
    {
        $usuario = Usuario::factory()->{$rol}()->create();

        $this->actingAs($usuario)->get(route($ruta))->assertOk();
    }

    /** @dataProvider modulosPorRol */
    public function test_rn01_un_rol_ajeno_no_entra_al_modulo(string $ruta, string $rol): void
    {
        // Se elige a propósito un rol distinto del que la ruta exige.
        $ajeno = $rol === 'admin' ? 'cliente' : 'admin';
        $usuario = Usuario::factory()->{$ajeno}()->create();

        $respuesta = $this->actingAs($usuario)->get(route($ruta));

        $this->assertNotSame(200, $respuesta->getStatusCode());
    }

    /** @dataProvider modulosPorRol */
    public function test_sin_sesion_la_plataforma_redirige_al_ingreso(string $ruta): void
    {
        $this->get(route($ruta))->assertRedirect(route('ingresar'));
    }

    public function test_una_direccion_que_no_existe_devuelve_404(): void
    {
        $this->get('/esto-no-existe')->assertNotFound();
    }
}
