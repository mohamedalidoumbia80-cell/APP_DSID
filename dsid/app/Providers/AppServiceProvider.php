<?php

namespace App\Providers;

use App\Models\Activite;
use App\Models\Document;
use App\Models\Tache;
use App\Models\User;
use App\Observers\AuditObserver;
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
        Activite::observe(AuditObserver::class);
        Tache::observe(AuditObserver::class);
        Document::observe(AuditObserver::class);
        User::observe(AuditObserver::class);
    }
}
