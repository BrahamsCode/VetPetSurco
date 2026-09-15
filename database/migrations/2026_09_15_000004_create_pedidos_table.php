<?php

use App\Enums\EstadoPedido;
use App\Enums\TipoOrigen;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla 4: pedidos. Compra directa o despacho de suscripcion. — RF-02 / RF-03
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->increments('pedido_id');
            $table->unsignedInteger('cliente_id');
            // Columna de tiempo propia del esquema original, no un timestamp de Laravel.
            $table->timestamp('fecha_pedido')->useCurrent();
            $table->decimal('monto_total', 10, 2);
            // RN-14: distingue la venta de mostrador del despacho recurrente.
            $table->enum('tipo_origen', array_column(TipoOrigen::cases(), 'value'))
                ->default(TipoOrigen::COMPRA_DIRECTA->value);
            // RN-13: el estado solo avanza por la secuencia del enum EstadoPedido.
            $table->enum('estado', array_column(EstadoPedido::cases(), 'value'))
                ->default(EstadoPedido::PENDIENTE->value);

            $table->foreign('cliente_id', 'fk_pedidos_cliente')
                ->references('usuario_id')->on('usuarios');
            $table->index('cliente_id', 'idx_pedidos_cliente');
            $table->index('fecha_pedido', 'idx_pedidos_fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
