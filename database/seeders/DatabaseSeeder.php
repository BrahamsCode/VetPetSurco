<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Carga completa de datos de demostracion.
 *
 * Replica `basedatos/02_datos_prueba.sql`. El orden respeta las claves
 * foraneas: usuarios -> mascotas -> productos -> pedidos -> suscripciones ->
 * citas -> historias clinicas.
 *
 * Todas las cuentas usan la contrasena de demo `demo123`.
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            UsuarioSeeder::class,
            MascotaSeeder::class,
            ProductoSeeder::class,
            PedidoSeeder::class,
            SuscripcionSeeder::class,
            CitaSeeder::class,
            HistoriaClinicaSeeder::class,
        ]);
    }
}
