<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // if (env('APP_DEBUG')) { // Only log queries in debug mode
        //     DB::listen(function ($query) {
        //         Log::info('Database Query', [
        //             'sql' => $query->sql,
        //             'bindings' => $query->bindings,
        //             'time' => $query->time,
        //         ]);
        //     });
        // }
    }
}