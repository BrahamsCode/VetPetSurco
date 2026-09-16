<?php

use App\Enums\EstadoPago;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla 9: pagos. Cada intento de cobro contra Culqi, aprobado o no. — RN-21
 *
 * Es una tabla de auditoria: no se actualiza un intento fallido, se registra
 * uno nuevo. Asi queda el historial de por que un pedido tardo en pagarse.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->increments('pago_id');
            $table->unsignedInteger('pedido_id');
            // Identificador del cargo en Culqi (chr_test_...). Nulo cuando el
            // intento ni siquiera llego a crear un cargo.
            $table->string('cargo_culqi', 60)->nullable();
            $table->decimal('monto', 10, 2);
            $table->enum('estado', array_column(EstadoPago::cases(), 'value'));
            // Datos no sensibles que devuelve la pasarela para mostrar al cliente.
            $table->string('marca', 30)->nullable();
            $table->string('ultimos_cuatro', 4)->nullable();
            // Motivo del rechazo tal como lo explica Culqi.
            $table->string('mensaje', 255)->nullable();
            $table->timestamp('fecha_pago')->useCurrent();

            $table->foreign('pedido_id', 'fk_pagos_pedido')
                ->references('pedido_id')->on('pedidos');
            $table->index('pedido_id', 'idx_pagos_pedido');
            // RN-21: un cargo de Culqi no puede registrarse dos veces.
            $table->unique('cargo_culqi', 'uk_pagos_cargo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
