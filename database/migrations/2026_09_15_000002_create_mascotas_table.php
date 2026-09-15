<?php

use App\Enums\Especie;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla 2: mascotas. Cada cliente puede registrar varias.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mascotas', function (Blueprint $table) {
            $table->increments('mascota_id');
            // RN-04: la mascota pertenece a un unico cliente y solo el la ve.
            $table->unsignedInteger('cliente_id');
            $table->string('nombre', 60);
            $table->enum('especie', array_column(Especie::cases(), 'value'));
            $table->string('raza', 60)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->decimal('peso_kg', 5, 2)->nullable();
            $table->string('alergias', 200)->nullable();

            // RN-04: clave foranea hacia el dueño.
            $table->foreign('cliente_id', 'fk_mascotas_cliente')
                ->references('usuario_id')->on('usuarios');
            $table->index('cliente_id', 'idx_mascotas_cliente');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mascotas');
    }
};
