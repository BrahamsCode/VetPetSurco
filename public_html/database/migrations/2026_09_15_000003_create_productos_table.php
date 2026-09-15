<?php

use App\Enums\CategoriaProducto;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla 3: productos. Catalogo con control de stock (ERP). — RF-06
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->increments('producto_id');
            // RN-05: el SKU no se repite en todo el catalogo.
            $table->string('codigo_sku', 50)->unique('productos_codigo_sku_unique');
            $table->string('nombre', 150);
            $table->enum('categoria', array_column(CategoriaProducto::cases(), 'value'));
            $table->decimal('precio', 10, 2);
            $table->integer('stock_actual')->default(0);
            // RN-08: umbral del semaforo de inventario.
            $table->integer('punto_reorden')->default(5);
            $table->boolean('activo')->default(true);

            $table->index('categoria', 'idx_productos_categoria');
        });

        // Laravel no tiene API de esquema para CHECK: se añaden con SQL directo.
        // SQLite (suite de pruebas) no admite ALTER TABLE ... ADD CONSTRAINT.
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            // RN-06: no se puede publicar un producto con precio cero o negativo.
            DB::statement('ALTER TABLE productos ADD CONSTRAINT chk_precio_positivo CHECK (precio > 0)');
            // RN-07: el stock nunca queda negativo, evita la sobreventa (RNF-03).
            DB::statement('ALTER TABLE productos ADD CONSTRAINT chk_stock_no_negativo CHECK (stock_actual >= 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
