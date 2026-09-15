<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Usuarios de demostracion. Replica el bloque de `basedatos/02_datos_prueba.sql`.
 *
 * Los hashes del archivo original eran de ejemplo y no correspondian a ninguna
 * contrasena real; aqui se generan con BCrypt sobre la clave de demo `demo123`
 * para que las cuentas sirvan de verdad en la sustentacion. — RN-03 / RNF-02
 */
class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        // Un solo hash reutilizado: BCrypt es costoso a proposito.
        $clave = Hash::make('demo123');

        $usuarios = [
            [1, 'Brahams Ramos', 'admin@vetpetsurco.pe', '987111222', 'Av. Velasco Astete 1245, Surco', Rol::ADMIN],
            [2, 'Lucia Bernal', 'lbernal@vetpetsurco.pe', '987333444', 'Av. Velasco Astete 1245, Surco', Rol::VETERINARIO],
            [3, 'Diego Palacios', 'dpalacios@vetpetsurco.pe', '987555666', 'Av. Velasco Astete 1245, Surco', Rol::VETERINARIO],
            [4, 'Ana Quispe', 'ana.quispe@correo.com', '987654321', 'Calle Los Cedros 320, Surco', Rol::CLIENTE],
            [5, 'Marco Salazar', 'marco.s@correo.com', '986222333', 'Jr. Monte Bello 118, Surco', Rol::CLIENTE],
            [6, 'Rosa Ibanez', 'rosa.ibanez@correo.com', '985444555', 'Av. Caminos del Inca 890, Surco', Rol::CLIENTE],
        ];

        foreach ($usuarios as [$id, $nombre, $correo, $telefono, $direccion, $rol]) {
            Usuario::forceCreate([
                'usuario_id' => $id,
                'nombre' => $nombre,
                // RN-02: correo unico por cuenta.
                'correo' => $correo,
                'password_hash' => $clave,
                'telefono' => $telefono,
                'direccion' => $direccion,
                'rol' => $rol,
                'fecha_registro' => now(),
            ]);
        }
    }
}
