<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Product\ProductController;

Route::get('/', function () {
    return 'Laravel Backend';
});

Route::resource('products', ProductController::class);
