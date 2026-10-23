<?php

namespace App\Providers;

use App\Settings\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

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
        View::composer('*', function (): void {
            $this->applyLocale();
        });
    }

    private function applyLocale(): void
    {
        try {
            if (! Schema::hasTable('settings')) {
                return;
            }

            app()->setLocale(app(Config::class)->locale);
        } catch (Throwable) {
            // Settings are unavailable while migrations are still creating them.
        }
    }
}
