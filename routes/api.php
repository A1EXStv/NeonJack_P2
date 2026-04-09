<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\PostController;

use App\Http\Controllers\Api\TransaccionController;
use App\Http\Controllers\Api\SkinController;
use App\Http\Controllers\Api\LogroController;
use App\Http\Controllers\Api\LogController;
use App\Http\Controllers\Api\SalaController;
use App\Http\Controllers\Api\ManoController;
use App\Http\Controllers\Api\AjustesController;
use App\Http\Controllers\Api\CarteraController;
use App\Http\Controllers\Api\blackjackController;

use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;


Route::group(['middleware' => 'auth:sanctum'], function() {

    Route::apiResource('users', UserController::class);
    Route::post('users/updateimg', [UserController::class,'updateimg']);


    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('roles', RoleController::class);

    Route::get('role-list', [RoleController::class, 'getList']);
    Route::get('role-permissions/{id}', [PermissionController::class, 'getRolePermissions']);
    Route::put('/role-permissions', [PermissionController::class, 'updateRolePermissions']);
    Route::apiResource('permissions', PermissionController::class);
    
    Route::get('/user', [ProfileController::class, 'user']);
    Route::get('/user/signin', [ProfileController::class, 'user']);
    Route::put('/user', [ProfileController::class, 'update']);


    Route::get('abilities', function(Request $request) {
        return $request->user()->roles()->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->pluck('name')
            ->unique()
            ->values()
            ->toArray();
    });
});
Route::get('category-list', [CategoryController::class, 'getList']);



Route::get('/transacciones', [TransaccionController::class, 'index']);
Route::get('/transacciones/{transaccion}', [TransaccionController::class, 'show']);
Route::delete('/transacciones/{transaccion}', [TransaccionController::class, 'destroy']);
Route::post('/transacciones', [TransaccionController::class, 'store']);

// Route::apiResource('transacciones', TransaccionController::class);
Route::post('/skins', [SkinController::class, 'store']);
Route::get('/skins', [SkinController::class, 'index']);
Route::get('/skins/{skin}', [SkinController::class, 'show']);
Route::delete('/skins/{skin}', [SkinController::class, 'destroy']);
Route::put('/skins/{skin}', [SkinController::class, 'update']);
Route::post('/skins/updateimg', [SkinController::class, 'updateimg']);

// LOGROS
Route::get('/logros', [LogroController::class, 'index']);
Route::get('/logros/{logro}', [LogroController::class, 'show']);
Route::delete('/logros/{logro}', [LogroController::class, 'destroy']);
Route::post('/logros', [LogroController::class, 'store']);
Route::post('/logros/{logro}', [LogroController::class, 'update']);

//LOGS

Route::get('/logs', [LogController::class, 'index']);
Route::get('/logs/{log}', [LogController::class, 'show']);
Route::delete('/logs/{log}', [LogController::class, 'destroy']);
Route::post('/logs', [LogController::class, 'store']);
Route::post('/skins', [SkinController::class, 'update']);


//MANOS
Route::get('/manos', [ManoController::class, 'index']);
Route::get('/manos/{mano}', [ManoController::class, 'show']);
Route::delete('/manos/{mano}', [ManoController::class, 'destroy']);
Route::post('/manos', [ManoController::class, 'store']);
// Route::post('/manos/{mano}', [ManoController::class, 'update']);

//AJUSTES
Route::get('/ajustes', [AjustesController::class, 'index']);
Route::get('/ajustes/{ajuste}', [AjustesController::class, 'show']);
Route::delete('/ajustes/{ajuste}', [AjustesController::class, 'destroy']);
Route::post('/ajustes', [AjustesController::class, 'store']);
Route::post('/ajustes/{ajuste}', [AjustesController::class, 'update']);

//CARTERA
Route::get('/carteras', [CarteraController::class, 'index']);
Route::get('/carteras/{cartera}', [CarteraController::class, 'show']);
Route::delete('/carteras/{cartera}', [CarteraController::class, 'destroy']);
Route::post('/carteras', [CarteraController::class, 'store']);
// Route::post('/carteras/{cartera}', [CarteraController::class, 'update']);

Route::middleware('auth:sanctum')->group(function () {
 
    // ── Salas ─────────────────────────────────────────────────────
    Route::apiResource('salas', SalaController::class);
    Route::post  ('salas/{sala}/join',  [SalaController::class, 'join']);
    Route::delete('salas/{sala}/leave', [SalaController::class, 'leave']);
 
    // ── Blackjack ─────────────────────────────────────────────────
    Route::post('salas/{sala}/iniciar',       [BlackjackController::class, 'iniciar']);
    Route::get ('partidas/{partida}/estado',  [BlackjackController::class, 'estado']);
    Route::post('partidas/{partida}/apostar', [BlackjackController::class, 'apostar']);
    Route::post('partidas/{partida}/hit',     [BlackjackController::class, 'hit']);
    Route::post('partidas/{partida}/stand',   [BlackjackController::class, 'stand']);
    Route::post('partidas/{partida}/doblar',  [BlackjackController::class, 'doblar']);
    Route::post('partidas/{partida}/dividir', [BlackjackController::class, 'dividir']);
});