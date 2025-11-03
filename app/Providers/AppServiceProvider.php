<?php

namespace App\Providers;

use App\Models\LetterheadTemplate;
use Illuminate\Support\ServiceProvider;
use App\Observers\PrintTemplateObserver;


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
        LetterheadTemplate::observe(PrintTemplateObserver::class);
    }
}
