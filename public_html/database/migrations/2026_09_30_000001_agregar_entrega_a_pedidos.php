<?php

use App\Enums\ModalidadEntrega;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Entrega y ciclo de vida del pedido.
 *  - Como se entrega (delivery o recojo en tienda), a donde y cuanto cuesta.
 *  - Cuando salio, cuando se entrego y, si se anulo, cuando y por que.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->enum('modalidad_entrega', array_column(ModalidadEntrega::cases(), 'value'))
                ->default(ModalidadEntrega::RECOJO->value)
                ->after('tipo_origen');
            $table->string('direccion_entrega', 255)->nullable()->after('modalidad_entrega');
            // El envio va aparte del monto de productos: la suma de los
            // subtotales sigue cuadrando con monto_total (RN-11).
            $table->decimal('costo_envio', 10, 2)->default(0)->after('monto_total');
            $table->timestamp('enviado_en')->nullable();
            $table->timestamp('entregado_en')->nullable();
            $table->timestamp('anulado_en')->nullable();
            $table->string('motivo_anulacion', 150)->nullable();

            // Lo consulta la tarea que anula los pedidos sin pagar.
            $table->index(['estado', 'fecha_pedido'], 'idx_pedidos_estado_fecha');
        });

        // Los despachos de suscripcion que ya existian eran a domicilio.
        DB::table('pedidos')
            ->where('tipo_origen', 'SUSCRIPCION')
            ->update(['modalidad_entrega' => ModalidadEntrega::DELIVERY->value]);
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropIndex('idx_pedidos_estado_fecha');
            $table->dropColumn([
                'modalidad_entrega', 'direccion_entrega', 'costo_envio',
                'enviado_en', 'entregado_en', 'anulado_en', 'motivo_anulacion',
            ]);
        });
    }
};
