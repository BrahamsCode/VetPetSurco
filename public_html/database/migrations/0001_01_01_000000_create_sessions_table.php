<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla de sesiones de Laravel (SESSION_DRIVER=database).
 *
 * Las tablas `users` y `password_reset_tokens` que trae Laravel de fabrica se
 * eliminaron: la autenticacion de VetPet Connect se apoya en `usuarios`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            // Guarda el `usuario_id` del usuario autenticado; sin clave foranea
            // para que cerrar sesion nunca dependa de la tabla de usuarios.
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
