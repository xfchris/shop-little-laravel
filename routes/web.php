<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Auth::routes([
    'register' => false, // Registration Routes...
    'reset' => false, // Password Reset Routes...
    'verify' => false, // Email Verification Routes...
]);

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('index');
Route::get('/ordenes', [App\Http\Controllers\OrderController::class, 'listarOrdenes'])->middleware('auth')->name('home');
Route::get('/orden/{id}', [App\Http\Controllers\OrderController::class, 'mostrarOrden'])->name('mostrarOrden');
Route::post('/json/buscar-orden', [App\Http\Controllers\OrderController::class, 'buscarOrden'])->name('buscarOrden');
Route::post('/json/iniciar-pago', [App\Http\Controllers\OrderController::class, 'iniciarPago'])->name('iniciarPago');

//Callback de PlaceToPay
Route::get('/ptp/aceptar-pago/{id}', [App\Http\Controllers\OrderController::class, 'aceptarPago'])->name('aceptarPago');
Route::get('/ptp/cancelar-pago/{id}', [App\Http\Controllers\OrderController::class, 'cancelarPago'])->name('cancelarPago');

Route::get('/actualizar-ordenes', [App\Http\Controllers\OrderController::class, 'actualizarPagosPendientes'])->middleware('auth');

