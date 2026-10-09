<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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
        // Inscription : 5 par minute et 30 par heure et par IP (plusieurs personnes peuvent partager une IP).
        RateLimiter::for('register', function (Request $request) {
            return [
                Limit::perMinute(5)->by((string) $request->ip()),
                Limit::perHour(30)->by((string) $request->ip()),
            ];
        });

        // Connexion : 5 essais/minute par couple email+IP, 30/minute par IP.
        RateLimiter::for('login', function (Request $request) {
            $email = Str::lower((string) $request->input('email'));

            return [
                Limit::perMinute(5)->by($email.'|'.$request->ip()),
                Limit::perMinute(30)->by((string) $request->ip()),
            ];
        });
    }
}
