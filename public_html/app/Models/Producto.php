<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CategoriaProducto;
use Database\Factories\ProductoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Producto del catalogo con control de stock. — RF-06
 *
 * @property int $producto_id
 * @property string $codigo_sku
 * @property CategoriaProducto $categoria
 * @property string $precio
 * @property int $stock_actual
 * @property int $punto_reorden
 */
class Producto extends Model
{
    /** @use HasFactory<ProductoFactory> */
    use HasFactory;

    protected $table = 'productos';

    protected $primaryKey = 'producto_id';

    /** La tabla no lleva columnas de tiempo. */
    public $timestamps = false;

    protected $fillable = [
        'codigo_sku',
        'nombre',
        'categoria',
        'precio',
        'stock_actual',
        'punto_reorden',
        'activo',
    ];

    protected $casts = [
        'categoria' => CategoriaProducto::class,
        'precio' => 'decimal:2',
        'stock_actual' => 'integer',
        'punto_reorden' => 'integer',
        'activo' => 'boolean',
    ];

    // ---------------------------------------------------------------
    // Relaciones
    // ---------------------------------------------------------------

    /** Lineas de pedido que incluyen este producto. */
    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePedido::class, 'producto_id', 'producto_id');
    }

    /** Alias de `detalles()`. */
    public function detallePedidos(): HasMany
    {
        return $this->detalles();
    }

    /** Suscripciones que despachan este producto. */
    public function suscripciones(): HasMany
    {
        return $this->hasMany(Suscripcion::class, 'producto_id', 'producto_id');
    }

    // ---------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------

    /** Solo los productos publicados en el catalogo. */
    public function scopeActivos(Builder $consulta): Builder
    {
        return $consulta->where('activo', true);
    }
}
