<?php

use App\Http\Controllers\Product\ProductController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\UserController;

Route::post('/login', [AuthController::class, 'login'])
    ->name('api.login');

Route::post('/user', [UserController::class, 'store'])
    ->name('api.createUser');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('api.logout');

    Route::get('/user', [UserController::class, 'index'])
        ->name('api.getUser');

    Route::get('/user/{id}', [UserController::class, 'show'])
        ->name('api.getUserById');

    Route::patch('/user/{id}', [UserController::class, 'update'])
        ->name('api.updateUser');

    Route::delete('/user/{id}', [UserController::class, 'destroy'])
        ->name('api.deleteUser');

    Route::get('/products', [ProductController::class, 'index'])
        ->name('api.index');

    Route::post('/products', [ProductController::class, 'store'])
        ->name('api.store');

    Route::get('/products/{id}', [ProductController::class, 'show'])
        ->name('api.show');

    Route::put('/products/{id}', [ProductController::class, 'update'])
        ->name('api.update');

    Route::delete('/products/{id}', [ProductController::class, 'destroy'])
        ->name('api.delete');
});
