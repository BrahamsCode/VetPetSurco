<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\DetallePedidoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Linea de un pedido: producto, cantidad y precio historico. — RN-09 / RN-11
 *
 * @property int $detalle_id
 * @property int $cantidad
 * @property string $precio_unitario
 */
class DetallePedido extends Model
{
    /** @use HasFactory<DetallePedidoFactory> */
    use HasFactory;

    protected $table = 'detalle_pedidos';

    protected $primaryKey = 'detalle_id';

    /** La tabla no lleva columnas de tiempo. */
    public $timestamps = false;

    protected $fillable = [
        'pedido_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    // ---------------------------------------------------------------
    // Relaciones
    // ---------------------------------------------------------------

    /** Pedido al que pertenece la linea. */
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id', 'pedido_id');
    }

    /** Producto vendido en la linea. */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id', 'producto_id');
    }
}
