<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Api\RedsysController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::post('login', [AuthenticatedSessionController::class, 'login']);
Route::post('register', [AuthenticatedSessionController::class, 'register']);
Route::post('logout', [AuthenticatedSessionController::class, 'logout']);

// Redsys callbacks (CSRF exempt — ver VerifyCsrfToken)
Route::post('redsys/notification', [RedsysController::class, 'notification']);
Route::get('redsys/success',       [RedsysController::class, 'success']);
Route::get('redsys/failure',       [RedsysController::class, 'failure']);

 


//Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


Route::view('/{any?}', 'main-view')
    ->name('dashboard')
    ->where('any', '.*');
