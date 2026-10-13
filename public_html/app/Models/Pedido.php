<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EstadoPedido;
use App\Enums\ModalidadEntrega;
use App\Enums\TipoOrigen;
use App\Services\CarritoService;
use Database\Factories\PedidoFactory;
use Illuminate\Support\Carbon;
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
        'costo_envio',
        'tipo_origen',
        'modalidad_entrega',
        'direccion_entrega',
        'estado',
        'enviado_en',
        'entregado_en',
        'anulado_en',
        'motivo_anulacion',
    ];

    protected $casts = [
        'fecha_pedido' => 'datetime',
        'monto_total' => 'decimal:2',
        'costo_envio' => 'decimal:2',
        'tipo_origen' => TipoOrigen::class,
        'modalidad_entrega' => ModalidadEntrega::class,
        'estado' => EstadoPedido::class,
        'enviado_en' => 'datetime',
        'entregado_en' => 'datetime',
        'anulado_en' => 'datetime',
    ];

    /** Horas que tiene el cliente para pagar antes de que el pedido se anule. */
    public const HORAS_PARA_PAGAR = 48;

    // ---------------------------------------------------------------
    // Reglas de lectura
    // ---------------------------------------------------------------

    /** Lo que se cobra: productos mas envio, en centimos exactos. */
    public function totalACobrar(): string
    {
        return CarritoService::aDecimal(
            CarritoService::aCentimos($this->monto_total) + CarritoService::aCentimos($this->costo_envio ?? 0),
        );
    }

    public function modalidad(): ModalidadEntrega
    {
        return $this->modalidad_entrega instanceof ModalidadEntrega
            ? $this->modalidad_entrega
            : ModalidadEntrega::from((string) ($this->modalidad_entrega ?? ModalidadEntrega::RECOJO->value));
    }

    public function estadoActual(): EstadoPedido
    {
        return $this->estado instanceof EstadoPedido ? $this->estado : EstadoPedido::from((string) $this->estado);
    }

    /** Estado con el nombre que corresponde a la modalidad ("En camino", "Listo para recoger"). */
    public function etiquetaEstado(): string
    {
        return $this->estadoActual()->etiquetaPara($this->modalidad());
    }

    /** Hasta cuando puede pagarse un pedido pendiente. */
    public function vencePagoEl(): Carbon
    {
        return $this->fecha_pedido->copy()->addHours(self::HORAS_PARA_PAGAR);
    }

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

    /** Intentos de cobro contra la pasarela. — RN-21 */
    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'pedido_id', 'pedido_id');
    }
}
