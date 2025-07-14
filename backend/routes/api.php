<?php

use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;

Route::post('/register', [RegisterController::class, 'register'])
->name('api.register');

Route::post('/login', [LoginController::class, 'login'])->name('api.login');

Route::middleware('auth:sanctum')->post('/logout', [LoginController::class, 'logout'])->name('api.logout');
