<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EstadoCita;
use App\Enums\Servicio;
use Database\Factories\CitaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Cita de la agenda veterinaria. — RF-04 / RN-17 / RN-18
 *
 * @property int $cita_id
 * @property Servicio $servicio
 * @property EstadoCita $estado
 */
class Cita extends Model
{
    /** @use HasFactory<CitaFactory> */
    use HasFactory;

    protected $table = 'citas';

    protected $primaryKey = 'cita_id';

    /** La tabla no lleva columnas de tiempo de Laravel (`fecha_hora` es del negocio). */
    public $timestamps = false;

    protected $fillable = [
        'mascota_id',
        'veterinario_id',
        'servicio',
        'fecha_hora',
        'estado',
    ];

    protected $casts = [
        'servicio' => Servicio::class,
        'fecha_hora' => 'datetime',
        'estado' => EstadoCita::class,
    ];

    // ---------------------------------------------------------------
    // Relaciones
    // ---------------------------------------------------------------

    /** Mascota atendida. */
    public function mascota(): BelongsTo
    {
        return $this->belongsTo(Mascota::class, 'mascota_id', 'mascota_id');
    }

    /** Veterinario que atiende. — RN-17 */
    public function veterinario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'veterinario_id', 'usuario_id');
    }

    /** Historia clinica generada por la atencion (1 a 1). — RN-19 */
    public function historiaClinica(): HasOne
    {
        return $this->hasOne(HistoriaClinica::class, 'cita_id', 'cita_id');
    }

    /** Alias corto de `historiaClinica()`. */
    public function historia(): HasOne
    {
        return $this->historiaClinica();
    }
}
