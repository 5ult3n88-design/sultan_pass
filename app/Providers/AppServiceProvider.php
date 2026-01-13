<?php

namespace App\Providers;

use App\Models\ParticipantResponse;
use App\Observers\ParticipantResponseObserver;
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
        // Register observer for automatic score calculation
        ParticipantResponse::observe(ParticipantResponseObserver::class);
    }
}
