<?php

namespace App\Providers;

use App\Services\Pasarela;
use App\Services\PasarelaCulqi;
use App\Services\PasarelaSimulada;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // RN-21: con llaves de Culqi en el .env se cobra de verdad; sin ellas
        // entra la pasarela simulada para poder demostrar el flujo completo.
        $this->app->bind(Pasarela::class, static function (): Pasarela {
            $llave = (string) config('services.culqi.llave_secreta');

            return $llave === '' ? new PasarelaSimulada() : new PasarelaCulqi();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
