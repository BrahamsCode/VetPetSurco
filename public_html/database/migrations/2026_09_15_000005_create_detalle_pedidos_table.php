<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla 5: detalle_pedidos. Contenido del carrito ya confirmado.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_pedidos', function (Blueprint $table) {
            $table->increments('detalle_id');
            $table->unsignedInteger('pedido_id');
            $table->unsignedInteger('producto_id');
            $table->integer('cantidad');
            // RN-11: precio historico congelado al momento de confirmar.
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);

            // Si se borra el pedido se van sus lineas: no quedan huerfanas.
            $table->foreign('pedido_id', 'fk_detalle_pedido')
                ->references('pedido_id')->on('pedidos')
                ->onDelete('cascade');
            $table->foreign('producto_id', 'fk_detalle_producto')
                ->references('producto_id')->on('productos');
            // RN-09: un producto aparece una sola vez por pedido; si se repite
            // se acumula la cantidad en la misma linea.
            $table->unique(['pedido_id', 'producto_id'], 'uk_linea_pedido');
        });

        // Laravel no tiene API de esquema para CHECK: se añade con SQL directo.
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            // RN-10: no se admiten lineas con cantidad cero o negativa.
            DB::statement('ALTER TABLE detalle_pedidos ADD CONSTRAINT chk_cantidad_positiva CHECK (cantidad > 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_pedidos');
    }
};
