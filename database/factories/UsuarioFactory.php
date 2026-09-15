<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Rol;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Usuario>
 */
class UsuarioFactory extends Factory
{
    protected $model = Usuario::class;

    /** Se calcula una sola vez: el hash de BCrypt es caro. */
    protected static ?string $hashDemo = null;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'nombre' => fake()->name(),
            'correo' => fake()->unique()->safeEmail(),
            'password_hash' => static::$hashDemo ??= Hash::make('demo123'),
            'telefono' => fake()->numerify('9########'),
            'direccion' => fake()->streetAddress().', Surco',
            'rol' => Rol::CLIENTE,
            'fecha_registro' => now(),
        ];
    }

    /** Usuario con rol CLIENTE. — RN-01 */
    public function cliente(): static
    {
        return $this->state(fn (array $atributos) => ['rol' => Rol::CLIENTE]);
    }

    /** Usuario con rol VETERINARIO. — RN-01 */
    public function veterinario(): static
    {
        return $this->state(fn (array $atributos) => ['rol' => Rol::VETERINARIO]);
    }

    /** Usuario con rol ADMIN. — RN-01 */
    public function admin(): static
    {
        return $this->state(fn (array $atributos) => ['rol' => Rol::ADMIN]);
    }
}
