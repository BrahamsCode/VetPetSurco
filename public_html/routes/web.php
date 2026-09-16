<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AutenticacionController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\ClinicaController;
use App\Http\Controllers\MascotaController;
use App\Http\Controllers\PaginaPublicaController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\SuscripcionController;
use Illuminate\Support\Facades\Route;

/*
|----------------------------------------------------------------------
| Sitio institucional (publico)
|----------------------------------------------------------------------
*/
Route::get('/', [PaginaPublicaController::class, 'inicio'])->name('inicio');
Route::get('/nosotros', [PaginaPublicaController::class, 'nosotros'])->name('nosotros');
Route::get('/productos', [PaginaPublicaController::class, 'productos'])->name('productos');
Route::get('/contacto', [PaginaPublicaController::class, 'contacto'])->name('contacto');

/*
|----------------------------------------------------------------------
| Acceso a la plataforma
|----------------------------------------------------------------------
*/
Route::middleware('guest')->group(function (): void {
    Route::get('/ingresar', [AutenticacionController::class, 'mostrarIngreso'])->name('ingresar');
    Route::post('/ingresar', [AutenticacionController::class, 'ingresar'])->name('ingresar.enviar');
    Route::get('/registro', [AutenticacionController::class, 'mostrarRegistro'])->name('registro');
    Route::post('/registro', [AutenticacionController::class, 'registrar'])->name('registro.enviar');
});

Route::post('/salir', [AutenticacionController::class, 'salir'])
    ->middleware('auth')
    ->name('salir');

/*
|----------------------------------------------------------------------
| Plataforma VetPet Connect (RN-01: un modulo por rol)
|----------------------------------------------------------------------
*/
Route::middleware('auth')->prefix('app')->group(function (): void {

    // ---- Modulos del CLIENTE ----
    Route::middleware('rol:CLIENTE')->group(function (): void {
        Route::get('/catalogo', [CatalogoController::class, 'index'])->name('catalogo');

        Route::get('/carrito', [CarritoController::class, 'index'])->name('carrito');
        Route::post('/carrito/agregar', [CarritoController::class, 'agregar'])->name('carrito.agregar');
        Route::post('/carrito/confirmar', [CarritoController::class, 'confirmar'])->name('carrito.confirmar');
        Route::patch('/carrito/{producto}', [CarritoController::class, 'actualizar'])->name('carrito.actualizar');
        Route::delete('/carrito/{producto}', [CarritoController::class, 'quitar'])->name('carrito.quitar');

        // RN-21: el pedido nace PENDIENTE y se paga aqui con tarjeta.
        Route::get('/pedidos/{pedido}/pagar', [PagoController::class, 'mostrar'])->name('pago');
        Route::post('/pedidos/{pedido}/pagar', [PagoController::class, 'procesar'])->name('pago.procesar');

        Route::get('/citas', [CitaController::class, 'index'])->name('citas');
        Route::post('/citas', [CitaController::class, 'reservar'])->name('citas.reservar');

        Route::get('/mascotas', [MascotaController::class, 'index'])->name('mascotas');
        Route::patch('/suscripciones/{suscripcion}', [SuscripcionController::class, 'estado'])
            ->name('suscripciones.estado');
    });

    // ---- Modulo del VETERINARIO ----
    Route::middleware('rol:VETERINARIO')->group(function (): void {
        Route::get('/clinica', [ClinicaController::class, 'index'])->name('clinica');
        Route::post('/clinica/{cita}/atender', [ClinicaController::class, 'atender'])->name('clinica.atender');
        Route::patch('/clinica/{cita}/desenlace', [ClinicaController::class, 'desenlace'])->name('clinica.desenlace');
    });

    // ---- Modulo del ADMIN ----
    Route::middleware('rol:ADMIN')->group(function (): void {
        Route::get('/admin', [AdminController::class, 'index'])->name('admin');
        Route::post('/admin/productos', [AdminController::class, 'crearProducto'])->name('admin.productos.crear');
        Route::patch('/admin/pedidos/{pedido}', [AdminController::class, 'avanzarPedido'])->name('admin.pedidos.avanzar');
    });
});
