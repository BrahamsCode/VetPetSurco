<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Boletin informativo.
 *
 * Es el canal de e-marketing propio: una lista de correos que se construye con
 * consentimiento explicito, separada de los avisos del servicio. El correo es
 * unico (misma regla que las cuentas) y cada suscriptor lleva su token para
 * darse de baja sin tener que iniciar sesion.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suscriptores_boletin', function (Blueprint $tabla): void {
            $tabla->id('suscriptor_id');
            $tabla->string('correo', 150)->unique('uk_boletin_correo');
            $tabla->string('nombre', 100)->nullable();
            $tabla->string('token_baja', 64)->unique('uk_boletin_token');
            $tabla->boolean('activo')->default(true);
            $tabla->timestamp('fecha_alta')->useCurrent();
            $tabla->timestamp('fecha_baja')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suscriptores_boletin');
    }
};
