<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;

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
        // Enregistrer le driver Keycloak pour Socialite
        Event::listen(
            \SocialiteProviders\Manager\SocialiteWasCalled::class,
            [\SocialiteProviders\Keycloak\KeycloakExtendSocialite::class, 'handle']
        );

        // Rate limiter pour la recherche API externe
        RateLimiter::for('api-search', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });
    }
}

