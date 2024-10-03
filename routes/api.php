<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AplicacionController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OperacionController;
use App\Http\Controllers\Api\RolController;
use App\Http\Controllers\Api\PersonaController;
use App\Http\Controllers\Api\BitacoraController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\ModuloController;
use App\Http\Controllers\Api\ProductoController;
use App\Http\Controllers\Api\Rol_AccesoController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UsuarioRolController;
use App\Http\Controllers\Api\VentaController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::controller(AplicacionController::class)->group(function () {
    Route::get('/aplicaciones', 'index');
    Route::get('/aplicaciones/{id}', 'show');
    Route::put('/aplicaciones/habilitar/{id}', 'habilitar');
    Route::put('/aplicaciones/{id}', 'update');
    Route::post('/aplicaciones', 'store');
    Route::delete('/aplicaciones/{id}', 'destroy');
});

Route::controller(RolController::class)->group(function () {
    Route::get('/roles', 'index');
    Route::post('/roles', 'store');
    Route::post('/roles/accesos', 'addAccesos');
    Route::get('/roles/{id}', 'show');
    Route::put('/roles/{id}', 'update');
    Route::delete('/roles/{id}', 'destroy');
    Route::put('/roles/habilitar/{id}', 'habilitar');
});

Route::controller(OperacionController::class)->group(function () {
    Route::get('/operaciones', 'index');
    Route::post('/operacion', 'store');
    Route::get('/operacion/{id}', 'show');
    Route::put('/operacion/{id}', 'update');
    //Route::delete('/resina/{id}','destroy');
});

Route::controller(UsuarioController::class)->group(function () {
    Route::get('/usuarios', 'index');
    Route::post('/usuario', 'store');
    Route::get('/usuario/{id}', 'show');
    Route::put('/usuario/{id}', 'update');
    Route::delete('/usuario/{id}', 'destroy');
});

Route::controller(BitacoraController::class)->group(function () {
    Route::get('/bitacoras', 'index');
    Route::post('/bitacora', 'store');
    Route::get('/bitacora/{id}', 'show');
    Route::put('/bitacora/{id}', 'update');
    //Route::delete('/resina/{id}','destroy');
});

Route::controller(PersonaController::class)->group(function () {
    Route::get('/personas/getByUN2', 'peopleByUnidadNegocio2');
    Route::get('/personas/getByUN/{un}', 'peopleByUnidadNegocio');
    Route::get('/personas/getByDivision/{division_id}', 'peopleByDivision');
    Route::get('/personas/v2', 'indexV2');
    Route::put('/personas/habilitar/{id}', 'habilitar');
    Route::post('/personas-upload', 'upload');
    Route::post('/personas/filtrar', 'filtrar');
    Route::put('/personas/{id}', 'update');
    Route::get('/personas/{id}', 'show');
    Route::delete('/personas/{id}', 'destroy');
    Route::get('/personas', 'index');
    Route::post('/personas', 'store');
});

Route::controller(ModuloController::class)->group(function () {
    Route::get('/modulos', 'index');
    Route::post('/modulos', 'store');
    Route::get('/modulos/{id}', 'show');
    Route::get('/modulos/app/{codigo_app}', 'showByApp');
    Route::put('/modulos/{id}', 'update');
    Route::put('/modulos/habilitar/{id}', 'habilitar');
});

Route::controller(Rol_AccesoController::class)->group(function () {
    Route::get('/accesos', 'index');
    Route::post('/acceso', 'store');
    Route::get('/acceso/{id}', 'show');
    Route::put('/acceso/{id}', 'update');
    Route::put('/acceso/{id}', 'update');
    //Route::delete('/resina/{id}','destroy');
});

// Route::controller(UserController::class)->group(function () {
//     Route::put('/users/habilitar/{id}', 'habilitar');
//     Route::post('/users/getByModulo', 'usersByModulo');
//     Route::post('/users/getByComponente', 'usersByComponente');
//     Route::get('/users/usersByApp', 'usersByApp');
//     Route::get('/users/{id}', 'show');
//     Route::put('/users/{id}', 'update');
//     Route::get('/users', 'index');
//     Route::post('/users', 'store');
// });

Route::controller(UsuarioRolController::class)->group(function () {
    Route::get('/usuario-rol', 'index');
    Route::post('/usuario-rol', 'store');
    Route::post('/usuario-rol/set-rol-base', 'setRolBase');
    Route::post('/eliminar-usuario-rol', 'destroy');
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/users', [AuthController::class, 'register']);
Route::get('/users', [AuthController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::delete('/users/delete/{id}', [AuthController::class, 'destroy']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::controller(ProductoController::class)->group(function () {
    Route::put('/productos/habilitar', 'habilitar');
    Route::put('/productos/{id}', 'update');
    Route::get('/productos/{id}', 'show');
    Route::get('/productos', 'index');
    Route::post('/productos', 'store');
});

Route::controller(VentaController::class)->group(function () {
    Route::put('/ventas/habilitar', 'habilitar');
    Route::put('/ventas/{id}', 'update');
    Route::get('/ventas/{id}', 'show');
    Route::get('/ventas', 'index');
    Route::post('/ventas', 'store');
});

Route::controller(ClienteController::class)->group(function () {
    Route::put('/clientes/habilitar', 'habilitar');
    Route::put('/clientes/{id}', 'update');
    Route::get('/clientes/{id}', 'show');
    Route::get('/clientes', 'index');
    Route::post('/clientes', 'store');
});
