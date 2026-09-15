<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EstadoSuscripcion;
use Database\Factories\SuscripcionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Suscripcion mensual de un cliente. — RF-03 / RN-15 / RN-16
 *
 * @property int $suscripcion_id
 * @property EstadoSuscripcion $estado
 */
class Suscripcion extends Model
{
    /** @use HasFactory<SuscripcionFactory> */
    use HasFactory;

    protected $table = 'suscripciones';

    protected $primaryKey = 'suscripcion_id';

    /** La tabla no lleva columnas de tiempo. */
    public $timestamps = false;

    protected $fillable = [
        'cliente_id',
        'mascota_id',
        'producto_id',
        'plan',
        'frecuencia_dias',
        'monto_mensual',
        'proximo_despacho',
        'estado',
    ];

    protected $casts = [
        'frecuencia_dias' => 'integer',
        'monto_mensual' => 'decimal:2',
        'proximo_despacho' => 'date',
        'estado' => EstadoSuscripcion::class,
    ];

    // ---------------------------------------------------------------
    // Relaciones
    // ---------------------------------------------------------------

    /** Cliente titular de la suscripcion. */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'cliente_id', 'usuario_id');
    }

    /** Mascota beneficiaria del plan. */
    public function mascota(): BelongsTo
    {
        return $this->belongsTo(Mascota::class, 'mascota_id', 'mascota_id');
    }

    /** Producto que se despacha en cada ciclo. */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id', 'producto_id');
    }

    // ---------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------

    /** Solo las suscripciones vigentes. — RN-15 */
    public function scopeActivas(Builder $consulta): Builder
    {
        return $consulta->where('estado', EstadoSuscripcion::ACTIVA);
    }
}
