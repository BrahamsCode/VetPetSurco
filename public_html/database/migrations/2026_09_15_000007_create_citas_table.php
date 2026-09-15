<?php

use App\Enums\EstadoCita;
use App\Enums\Servicio;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla 7: citas. Agenda veterinaria. — RF-04
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('citas', function (Blueprint $table) {
            $table->increments('cita_id');
            $table->unsignedInteger('mascota_id');
            $table->unsignedInteger('veterinario_id');
            $table->enum('servicio', array_column(Servicio::cases(), 'value'));
            $table->dateTime('fecha_hora');
            // RN-18: el desenlace lo cierra el veterinario.
            $table->enum('estado', array_column(EstadoCita::cases(), 'value'))
                ->default(EstadoCita::RESERVADA->value);

            $table->foreign('mascota_id', 'fk_citas_mascota')
                ->references('mascota_id')->on('mascotas');
            $table->foreign('veterinario_id', 'fk_citas_veterinario')
                ->references('usuario_id')->on('usuarios');
            // RN-17: el motor impide que dos clientes tomen el mismo horario con
            // el mismo veterinario; resuelve el cruce de citas (RNF-03).
            $table->unique(['veterinario_id', 'fecha_hora'], 'uk_agenda');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};
