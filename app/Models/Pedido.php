<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EstadoPedido;
use App\Enums\TipoOrigen;
use Database\Factories\PedidoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Pedido de compra directa o despacho de suscripcion. — RF-02 / RF-03
 *
 * @property int $pedido_id
 * @property EstadoPedido $estado
 * @property TipoOrigen $tipo_origen
 */
class Pedido extends Model
{
    /** @use HasFactory<PedidoFactory> */
    use HasFactory;

    protected $table = 'pedidos';

    protected $primaryKey = 'pedido_id';

    /** La tabla tiene su propia columna de tiempo (`fecha_pedido`). */
    public $timestamps = false;

    protected $fillable = [
        'cliente_id',
        'fecha_pedido',
        'monto_total',
        'tipo_origen',
        'estado',
    ];

    protected $casts = [
        'fecha_pedido' => 'datetime',
        'monto_total' => 'decimal:2',
        'tipo_origen' => TipoOrigen::class,
        'estado' => EstadoPedido::class,
    ];

    // ---------------------------------------------------------------
    // Relaciones
    // ---------------------------------------------------------------

    /** Cliente que hizo el pedido. */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'cliente_id', 'usuario_id');
    }

    /** Lineas del pedido. — RN-09 */
    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePedido::class, 'pedido_id', 'pedido_id');
    }

    /** Alias de `detalles()`. */
    public function detallePedidos(): HasMany
    {
        return $this->detalles();
    }
}
