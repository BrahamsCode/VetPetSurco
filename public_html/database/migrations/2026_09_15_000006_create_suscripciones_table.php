<?php

use App\Enums\EstadoSuscripcion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla 6: suscripciones mensuales (ingreso recurrente). — RF-03
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suscripciones', function (Blueprint $table) {
            $table->increments('suscripcion_id');
            $table->unsignedInteger('cliente_id');
            $table->unsignedInteger('mascota_id');
            $table->unsignedInteger('producto_id');
            // El plan no tiene enum de PHP en el contrato: se conserva la
            // columna ENUM del esquema original y se trata como texto.
            $table->enum('plan', ['BASICO', 'CUIDADO', 'INTEGRAL']);
            // RN-16: cada cuantos dias se genera el despacho.
            $table->integer('frecuencia_dias')->default(30);
            $table->decimal('monto_mensual', 10, 2);
            // RN-16: fecha del proximo despacho programado.
            $table->date('proximo_despacho');
            // RN-15: la suscripcion se puede pausar y reanudar sin perderla.
            $table->enum('estado', array_column(EstadoSuscripcion::cases(), 'value'))
                ->default(EstadoSuscripcion::ACTIVA->value);

            $table->foreign('cliente_id', 'fk_suscripciones_cliente')
                ->references('usuario_id')->on('usuarios');
            $table->foreign('mascota_id', 'fk_suscripciones_mascota')
                ->references('mascota_id')->on('mascotas');
            $table->foreign('producto_id', 'fk_suscripciones_producto')
                ->references('producto_id')->on('productos');
            $table->index(['estado', 'proximo_despacho'], 'idx_suscripciones_despacho');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suscripciones');
    }
};
