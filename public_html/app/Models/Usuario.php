<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Rol;
use Database\Factories\UsuarioFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Usuario de la plataforma: cliente, veterinario o administrador. — RN-01
 *
 * @property int $usuario_id
 * @property string $nombre
 * @property string $correo
 * @property string $password_hash
 * @property Rol $rol
 */
class Usuario extends Authenticatable
{
    /** @use HasFactory<UsuarioFactory> */
    use HasFactory;

    protected $table = 'usuarios';

    protected $primaryKey = 'usuario_id';

    /** La tabla tiene su propia columna de tiempo (`fecha_registro`). */
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'correo',
        'password_hash',
        'telefono',
        'direccion',
        'rol',
        'fecha_registro',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected $casts = [
        'rol' => Rol::class,
        'fecha_registro' => 'datetime',
    ];

    /**
     * Laravel busca `password` por defecto; aqui la columna se llama
     * `password_hash` (RNF-02 / RN-03).
     */
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    // ---------------------------------------------------------------
    // Relaciones
    // ---------------------------------------------------------------

    /** Mascotas registradas por este cliente. — RN-04 */
    public function mascotas(): HasMany
    {
        return $this->hasMany(Mascota::class, 'cliente_id', 'usuario_id');
    }

    /** Pedidos realizados por este cliente. */
    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'cliente_id', 'usuario_id');
    }

    /** Suscripciones contratadas por este cliente. — RN-15 */
    public function suscripciones(): HasMany
    {
        return $this->hasMany(Suscripcion::class, 'cliente_id', 'usuario_id');
    }

    /** Citas de la agenda de este veterinario. — RN-17 */
    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class, 'veterinario_id', 'usuario_id');
    }

    /** Alias legible de `citas()` cuando el usuario es veterinario. */
    public function citasComoVeterinario(): HasMany
    {
        return $this->citas();
    }

    // ---------------------------------------------------------------
    // Ayudas de rol — RN-01
    // ---------------------------------------------------------------

    public function esCliente(): bool
    {
        return $this->rol === Rol::CLIENTE;
    }

    public function esVeterinario(): bool
    {
        return $this->rol === Rol::VETERINARIO;
    }

    public function esAdmin(): bool
    {
        return $this->rol === Rol::ADMIN;
    }
}
