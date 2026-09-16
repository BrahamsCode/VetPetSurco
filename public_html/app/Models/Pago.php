<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EstadoPago;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Intento de cobro contra la pasarela, aprobado o no. — RN-21
 *
 * @property int $pago_id
 * @property EstadoPago $estado
 */
class Pago extends Model
{
    protected $table = 'pagos';

    protected $primaryKey = 'pago_id';

    /** La tabla tiene su propia columna de tiempo (`fecha_pago`). */
    public $timestamps = false;

    protected $fillable = [
        'pedido_id',
        'cargo_culqi',
        'monto',
        'estado',
        'marca',
        'ultimos_cuatro',
        'mensaje',
        'fecha_pago',
    ];

    protected $casts = [
        'fecha_pago' => 'datetime',
        'monto' => 'decimal:2',
        'estado' => EstadoPago::class,
    ];

    /** Pedido que se intento cobrar. */
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id', 'pedido_id');
    }
}
