<?php

use App\Enums\Rol;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla 1: usuarios. Clientes, veterinarios y administradores. — RF-01
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->increments('usuario_id');
            $table->string('nombre', 100);
            // RN-02: el correo identifica de forma unica a cada cuenta.
            $table->string('correo', 100)->unique('usuarios_correo_unique');
            // RNF-02 / RN-03: siempre BCrypt, nunca texto plano.
            $table->string('password_hash', 255);
            $table->string('telefono', 15)->nullable();
            $table->string('direccion', 200)->nullable();
            // RN-01: el rol decide a que zona de la plataforma se entra.
            $table->enum('rol', array_column(Rol::cases(), 'value'));
            // Columna de tiempo propia del esquema original, no un timestamp de Laravel.
            $table->timestamp('fecha_registro')->useCurrent();

            $table->index('rol', 'idx_usuarios_rol');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
