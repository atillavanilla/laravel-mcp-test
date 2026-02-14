<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

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
        //
        Passport::loadKeysFrom(storage_path('oauth'));

        Passport::authorizationView(function ($parameters) {
            return view('auth.oauth.authorize', [
                'request' => $parameters['request'],
                'authToken' => $parameters['authToken'],
                'client' => $parameters['client'],
                'user' => $parameters['user'],
                'scopes' => $parameters['scopes'],

                'app_name' => config('app.name'),
                'support_email' => 'help@example.com',
                'is_vip_user' => $parameters['user']->isVip(),
            ]);
        });
    }
}
