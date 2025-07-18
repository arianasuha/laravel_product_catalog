<?php

use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

Route::post('/register', [RegisterController::class, 'register'])
->name('api.register');

Route::post('/login', [AuthController::class, 'login'])
->name('api.login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])
    ->name('api.logout');
});

Route::get('/products', [ProductController::class, 'index'])
->name('api.index');

Route::post('/products', [ProductController::class, 'store'])
->name('api.store');

Route::get('/products/create', [ProductController::class, 'create'])
->name('api.create');

Route::get('/products/{id}', [ProductController::class, 'show'])
->name('api.show');

Route::put('/products/{id}', [ProductController::class, 'update'])
->name('api.update');

Route::delete('/products/{id}', [ProductController::class, 'destroy'])
->name('api.delete');

Route::get('/products/{id}/edit', [ProductController::class, 'edit'])
->name('api.edit');
