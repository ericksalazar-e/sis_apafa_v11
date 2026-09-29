<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
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
        // 1. Forzar esquema HTTPS en producción o detrás de proxy inverso (AWS ALB / Cloudflare / Nginx)
        if (app()->isProduction() || request()->header('X-Forwarded-Proto') === 'https') {
            URL::forceScheme('https');
        }

        // 3. Rate Limiters para Alta Disponibilidad y Prevención de DDoS
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Limitador especializado para cajas POS (admite ráfagas de ventanilla pero frena scraping)
        RateLimiter::for('pos-scan', function (Request $request) {
            return Limit::perMinute(180)->by($request->user()?->id ?: $request->ip());
        });

        // Limitador de fuerza bruta en inicio de sesión
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('email').'|'.$request->ip());
        });
    }
}
