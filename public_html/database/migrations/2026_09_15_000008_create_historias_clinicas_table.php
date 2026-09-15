<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla 8: historias_clinicas. Historia clinica digital. — RF-05
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historias_clinicas', function (Blueprint $table) {
            $table->increments('historia_id');
            $table->unsignedInteger('mascota_id');
            // RN-19: relacion 1 a 1 con la cita; cada atencion genera un solo
            // registro, un segundo intento lo rechaza el motor.
            $table->unsignedInteger('cita_id')->nullable()->unique('historias_clinicas_cita_id_unique');
            // Columna de tiempo propia del esquema original, no un timestamp de Laravel.
            $table->timestamp('fecha_atencion')->useCurrent();
            $table->text('diagnostico')->nullable();
            $table->text('tratamiento')->nullable();
            $table->string('vacuna_aplicada', 100)->nullable();
            // RN-20: alimenta el recordatorio automatico de salud.
            $table->date('proxima_fecha')->nullable();

            $table->foreign('mascota_id', 'fk_historias_mascota')
                ->references('mascota_id')->on('mascotas');
            $table->foreign('cita_id', 'fk_historias_cita')
                ->references('cita_id')->on('citas');
            $table->index('proxima_fecha', 'idx_historias_proxima');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historias_clinicas');
    }
};
