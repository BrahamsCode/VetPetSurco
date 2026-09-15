<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CategoriaProducto;
use App\Models\Producto;
use Illuminate\Database\Seeder;

/**
 * Catalogo de demostracion. Los precios son todos mayores que cero (RN-06) y
 * el stock nunca negativo (RN-07); varios quedan por debajo del punto de
 * reorden para que el semaforo de inventario muestre los tres colores (RN-08).
 */
class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        $productos = [
            [1, 'ALI-PER-15K', 'Alimento perro adulto 15 kg', CategoriaProducto::ALIMENTO, 189.90, 24, 10],
            [2, 'ALI-PER-08K', 'Alimento perro adulto 8 kg', CategoriaProducto::ALIMENTO, 109.90, 18, 8],
            [3, 'ALI-CAC-03K', 'Alimento cachorro 3 kg', CategoriaProducto::ALIMENTO, 89.90, 6, 8],
            [4, 'ALI-GAT-08K', 'Alimento gato adulto 8 kg', CategoriaProducto::ALIMENTO, 129.90, 15, 8],
            [5, 'ARE-SAN-10K', 'Arena sanitaria aglomerante 10 kg', CategoriaProducto::ARENA, 39.90, 30, 12],
            [6, 'ACC-COR-M', 'Correa retractil mediana', CategoriaProducto::ACCESORIO, 45.00, 12, 5],
            [7, 'ACC-CAM-L', 'Cama acolchada talla L', CategoriaProducto::ACCESORIO, 139.00, 4, 5],
            [8, 'ACC-JUG-01', 'Juguete mordedor resistente', CategoriaProducto::ACCESORIO, 19.90, 40, 10],
            [9, 'MED-ANT-01', 'Antipulgas topico (pipeta)', CategoriaProducto::MEDICAMENTO, 34.90, 9, 6],
            [10, 'MED-VIT-01', 'Vitaminas multiproposito 60 tabs', CategoriaProducto::MEDICAMENTO, 24.90, 3, 6],
        ];

        foreach ($productos as [$id, $sku, $nombre, $categoria, $precio, $stock, $reorden]) {
            Producto::forceCreate([
                'producto_id' => $id,
                // RN-05: SKU unico.
                'codigo_sku' => $sku,
                'nombre' => $nombre,
                'categoria' => $categoria,
                'precio' => $precio,
                'stock_actual' => $stock,
                'punto_reorden' => $reorden,
                'activo' => true,
            ]);
        }
    }
}
