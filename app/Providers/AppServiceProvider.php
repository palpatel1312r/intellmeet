<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Fix for "Specified key was too long" error
        Schema::defaultStringLength(191);
    }

    public function register(): void
    {
        //
    }
}
