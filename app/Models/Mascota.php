<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Especie;
use Database\Factories\MascotaFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Mascota registrada por un cliente. — RN-04
 *
 * @property int $mascota_id
 * @property int $cliente_id
 * @property Especie $especie
 */
class Mascota extends Model
{
    /** @use HasFactory<MascotaFactory> */
    use HasFactory;

    protected $table = 'mascotas';

    protected $primaryKey = 'mascota_id';

    /** La tabla no lleva columnas de tiempo. */
    public $timestamps = false;

    protected $fillable = [
        'cliente_id',
        'nombre',
        'especie',
        'raza',
        'fecha_nacimiento',
        'peso_kg',
        'alergias',
    ];

    protected $casts = [
        'especie' => Especie::class,
        'fecha_nacimiento' => 'date',
        'peso_kg' => 'decimal:2',
    ];

    // ---------------------------------------------------------------
    // Relaciones
    // ---------------------------------------------------------------

    /** Dueño de la mascota. — RN-04 */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'cliente_id', 'usuario_id');
    }

    /** Citas agendadas para esta mascota. */
    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class, 'mascota_id', 'mascota_id');
    }

    /** Historias clinicas de esta mascota. — RF-05 */
    public function historiasClinicas(): HasMany
    {
        return $this->hasMany(HistoriaClinica::class, 'mascota_id', 'mascota_id');
    }

    /** Alias corto de `historiasClinicas()`. */
    public function historias(): HasMany
    {
        return $this->historiasClinicas();
    }

    /** Suscripciones asociadas a esta mascota. */
    public function suscripciones(): HasMany
    {
        return $this->hasMany(Suscripcion::class, 'mascota_id', 'mascota_id');
    }

    // ---------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------

    /**
     * Limita la consulta a las mascotas de un cliente: nadie ve las de otro. — RN-04
     */
    public function scopeDeCliente(Builder $consulta, Usuario|int $cliente): Builder
    {
        $id = $cliente instanceof Usuario ? $cliente->usuario_id : $cliente;

        return $consulta->where('cliente_id', $id);
    }
}
