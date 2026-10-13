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
    // Imagen
    // ---------------------------------------------------------------

    /** Ruta publica de la foto, si existe: public/img/productos/{SKU}.webp. */
    private function rutaFoto(): string
    {
        // El SKU se limpia antes de armar la ruta: nunca sale de la carpeta.
        return 'img/productos/'.preg_replace('/[^A-Za-z0-9_-]/', '', (string) $this->codigo_sku).'.webp';
    }

    public function tieneFoto(): bool
    {
        return is_file(public_path($this->rutaFoto()));
    }

    /**
     * Foto del producto o, si todavia no tiene (un producto recien creado
     * desde el panel), el icono de su categoria: la tarjeta nunca queda vacia.
     */
    public function urlImagen(): string
    {
        if ($this->tieneFoto()) {
            return asset($this->rutaFoto());
        }

        $categoria = $this->categoria instanceof CategoriaProducto
            ? $this->categoria
            : CategoriaProducto::tryFrom((string) $this->categoria);

        return asset(match ($categoria) {
            CategoriaProducto::ALIMENTO => 'img/icono-alimento.svg',
            CategoriaProducto::ACCESORIO => 'img/icono-accesorios.svg',
            CategoriaProducto::MEDICAMENTO => 'img/icono-salud.svg',
            default => 'img/icono-tienda.svg',
        });
    }

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
