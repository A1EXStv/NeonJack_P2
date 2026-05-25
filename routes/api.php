<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\PostController;

use App\Http\Controllers\Api\TransaccionController;
use App\Http\Controllers\Api\RedsysController;
use App\Http\Controllers\Api\SkinController;
use App\Http\Controllers\Api\LogroController;
use App\Http\Controllers\Api\LogController;
use App\Http\Controllers\Api\BlackjackController;
use App\Http\Controllers\Api\SalaController;
use App\Http\Controllers\Api\ManoController;
use App\Http\Controllers\Api\AjustesController;
use App\Http\Controllers\Api\CarteraController;
use App\Http\Controllers\Api\RankingController;

use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────
// PÚBLICAS — lectura de catálogo y rankings
// ─────────────────────────────────────────────
Route::get('category-list', [CategoryController::class, 'getList']);

Route::get('/posts',        [PostController::class, 'index']);
Route::get('/posts/{post}', [PostController::class, 'show']);

Route::get('/skins',        [SkinController::class, 'index']);
Route::get('/skins/{skin}', [SkinController::class, 'show']);

Route::get('/logros',        [LogroController::class, 'index']);
Route::get('/logros/{logro}', [LogroController::class, 'show']);

Route::get('/ranking',             [RankingController::class, 'index']);
Route::get('/ranking-beneficio',   [RankingController::class, 'topBeneficio']);
Route::get('/ranking/top-mano',    [RankingController::class, 'topBeneficioPorMano']);

// ─────────────────────────────────────────────
// AUTENTICADO — operaciones propias del jugador
// ─────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Perfil propio
    Route::get('/user',        [ProfileController::class, 'user']);
    Route::get('/user/signin', [ProfileController::class, 'user']);
    Route::put('/user',        [ProfileController::class, 'update']);

    // Permisos del usuario autenticado
    Route::get('abilities', function (Request $request) {
        return $request->user()->roles()->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->pluck('name')
            ->unique()
            ->values()
            ->toArray();
    });

    // Transacciones propias
    Route::get('/mis-transacciones', [TransaccionController::class, 'misTransacciones']);

    // Redsys — iniciar pago propio
    Route::post('/redsys/create-payment', [RedsysController::class, 'createPayment']);

    // Skins del jugador
    Route::get('/user/skins',              [UserController::class, 'mySkins']);
    Route::post('/buy-skin',               [UserController::class, 'buy']);
    Route::post('/skins/{skin}/activate',  [SkinController::class, 'activate']);

    // Salas y partidas de Blackjack
    Route::get('/salas',             [SalaController::class, 'index']);
    Route::post('/salas',            [SalaController::class, 'store']);
    Route::get('/salas/{sala}',      [SalaController::class, 'show']);
    Route::post('/salas/{sala}',     [SalaController::class, 'update']);
    Route::delete('/salas/{sala}',   [SalaController::class, 'destroy']);
    Route::post('/salas/{sala}/join',  [SalaController::class, 'join']);
    Route::delete('/salas/{sala}/leave', [SalaController::class, 'leave']);

    Route::post('/salas/{sala}/iniciar',     [BlackjackController::class, 'iniciar']);
    Route::get('/partidas/{partida}/estado', [BlackjackController::class, 'estado']);
    Route::post('/partidas/{partida}/apostar', [BlackjackController::class, 'apostar']);
    Route::post('/partidas/{partida}/hit',     [BlackjackController::class, 'hit']);
    Route::post('/partidas/{partida}/stand',   [BlackjackController::class, 'stand']);
    Route::post('/partidas/{partida}/doblar',  [BlackjackController::class, 'doblar']);
    Route::post('/partidas/{partida}/dividir', [BlackjackController::class, 'dividir']);
});

// ─────────────────────────────────────────────
// ADMIN — gestión del sistema (auth + role:admin)
// ─────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {

    // Usuarios
    Route::apiResource('users', UserController::class);
    Route::post('users/updateimg', [UserController::class, 'updateimg']);

    // Categorías
    Route::apiResource('categories', CategoryController::class);

    // Roles y permisos
    Route::apiResource('roles', RoleController::class);
    Route::get('role-list',                  [RoleController::class, 'getList']);
    Route::get('role-permissions/{id}',      [PermissionController::class, 'getRolePermissions']);
    Route::put('/role-permissions',          [PermissionController::class, 'updateRolePermissions']);
    Route::apiResource('permissions', PermissionController::class);

    // Posts (escritura)
    Route::post('/posts',         [PostController::class, 'store']);
    Route::put('/posts/{post}',   [PostController::class, 'update']);
    Route::delete('/posts/{post}', [PostController::class, 'destroy']);

    // Transacciones (vista y gestión completa)
    Route::get('/transacciones',                    [TransaccionController::class, 'index']);
    Route::get('/transacciones/{transaccion}',      [TransaccionController::class, 'show']);
    Route::post('/transacciones',                   [TransaccionController::class, 'store']);
    Route::delete('/transacciones/{transaccion}',   [TransaccionController::class, 'destroy']);

    // Skins (gestión del catálogo)
    Route::post('/skins',               [SkinController::class, 'store']);
    Route::put('/skins/{skin}',         [SkinController::class, 'update']);
    Route::delete('/skins/{skin}',      [SkinController::class, 'destroy']);
    Route::post('/skins/updateimg',     [SkinController::class, 'updateimg']);

    // Logros (gestión)
    Route::post('/logros',              [LogroController::class, 'store']);
    Route::post('/logros/{logro}',      [LogroController::class, 'update']);
    Route::delete('/logros/{logro}',    [LogroController::class, 'destroy']);

    // Logs (solo admin puede leer/borrar logs del sistema)
    Route::get('/logs',          [LogController::class, 'index']);
    Route::get('/logs/{log}',    [LogController::class, 'show']);
    Route::post('/logs',         [LogController::class, 'store']);
    Route::delete('/logs/{log}', [LogController::class, 'destroy']);

    // Manos (historial de partidas)
    Route::get('/manos',          [ManoController::class, 'index']);
    Route::get('/manos/{mano}',   [ManoController::class, 'show']);
    Route::post('/manos',         [ManoController::class, 'store']);
    Route::delete('/manos/{mano}', [ManoController::class, 'destroy']);

    // Ajustes del sistema
    Route::get('/ajustes',              [AjustesController::class, 'index']);
    Route::get('/ajustes/{ajuste}',     [AjustesController::class, 'show']);
    Route::post('/ajustes',             [AjustesController::class, 'store']);
    Route::post('/ajustes/{ajuste}',    [AjustesController::class, 'update']);
    Route::delete('/ajustes/{ajuste}',  [AjustesController::class, 'destroy']);

    // Carteras (movimientos financieros)
    Route::get('/carteras',             [CarteraController::class, 'index']);
    Route::get('/carteras/{cartera}',   [CarteraController::class, 'show']);
    Route::post('/carteras',            [CarteraController::class, 'store']);
    Route::delete('/carteras/{cartera}', [CarteraController::class, 'destroy']);
});
