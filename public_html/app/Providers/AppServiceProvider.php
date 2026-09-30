<?php

namespace App\Providers;

use App\Services\CarritoService;
use App\Services\Pasarela;
use App\Services\PasarelaCulqi;
use App\Services\PasarelaSimulada;
use Illuminate\Support\Facades\View;
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
        // El contador del carrito acompaña todas las pantallas de la plataforma,
        // para mostrarlo en el atajo del encabezado sin cargarlo en cada controlador.
        View::composer('layouts.app', static function ($view): void {
            $unidades = auth()->check() ? app(CarritoService::class)->unidades() : 0;
            $view->with('unidadesCarrito', $unidades);
        });
    }
}
