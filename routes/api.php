<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\PostController;

use App\Http\Controllers\Api\TransaccionController;
use App\Http\Controllers\Api\SkinController;

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



Route::apiResource('posts', PostController::class);

// Route::get('/posts', [PostController::class, 'index']);
// Route::get('/posts/{post}', [PostController::class, 'show']);
// Route::delete('/posts/{post}', [PostController::class, 'destroy']);
// Route::post('/posts/{post}', [PostController::class, 'store']);


Route::get('/transacciones', [TransaccionController::class, 'index']);
Route::get('/transacciones/{transaccion}', [TransaccionController::class, 'show']);
Route::delete('/transacciones/{transaccion}', [TransaccionController::class, 'destroy']);
Route::post('/transacciones', [TransaccionController::class, 'store']);

// Route::apiResource('transacciones', TransaccionController::class);

Route::get('/skins', [SkinController::class, 'index']);
Route::get('/skins/{skin}', [SkinController::class, 'show']);
Route::delete('/skins/{skin}', [SkinController::class, 'destroy']);
Route::post('/skins', [SkinController::class, 'store']);
Route::post('/skins', [SkinController::class, 'update']);
 

Route::get('/skins', [SalaController::class, 'index']);
Route::get('/skins/{skin}', [SalaController::class, 'show']);
Route::delete('/skins/{skin}', [SalaController::class, 'destroy']);
Route::post('/skins', [SalaController::class, 'store']);
Route::post('/skins', [SalaController::class, 'update']);

Route::get('/manos', [ManoController::class, 'index']);
Route::get('/manos/{mano}', [ManoController::class, 'show']);
Route::delete('/manos/{mano}', [ManoController::class, 'destroy']);
Route::post('/manos', [ManoController::class, 'store']);
Route::post('/manos', [ManoController::class, 'update']);
