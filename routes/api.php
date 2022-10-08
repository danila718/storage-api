<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('user')->group(function () {
        Route::get('info', [UserController::class, 'info']);
    });

    Route::prefix('files')->group(function () {
        Route::get('', [FileController::class, 'index']);
        Route::post('', [FileController::class, 'upload']);
        Route::get('{id}', [FileController::class, 'download']);
        Route::delete('{id}', [FileController::class, 'destroy']);
    });
});

Route::controller(AuthController::class)->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'login');
});
