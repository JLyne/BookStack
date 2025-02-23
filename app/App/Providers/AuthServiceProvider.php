<?php

namespace BookStack\App\Providers;

use BookStack\Access\ExternalBaseUserProvider;
use BookStack\Access\Guards\AsyncExternalBaseSessionGuard;
use BookStack\Access\Guards\LdapSessionGuard;
use BookStack\Access\Guards\RemoteAuthSessionGuard;
use BookStack\Access\LdapService;
use BookStack\Access\LoginService;
use BookStack\Access\RegistrationService;
use BookStack\Api\ApiTokenGuard;
use BookStack\Users\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        // Password Configuration
        // Changes here must be reflected in ApiDocsGenerate@getValidationAsString.
        Password::defaults(fn () => Password::min(8));

        // Custom guards
        Auth::extend('api-token', function ($app, $name, array $config) {
            return new ApiTokenGuard($app['request'], $app->make(LoginService::class));
        });

        Auth::extend('ldap-session', function ($app, $name, array $config) {
            $provider = Auth::createUserProvider($config['provider']);

            return new LdapSessionGuard(
                $name,
                $provider,
                $app['session.store'],
                $app[LdapService::class],
                $app[RegistrationService::class]
            );
        });

        Auth::extend('async-external-session', function ($app, $name, array $config) {
            $provider = Auth::createUserProvider($config['provider']);

            return new AsyncExternalBaseSessionGuard(
                $name,
                $provider,
                $app['session.store'],
                $app[RegistrationService::class]
            );
        });

        Auth::extend('remote-session', function ($app, $name, array $config) {
            $provider = Auth::createUserProvider($config['provider']);
            return new RemoteAuthSessionGuard(
                $name,
                $provider,
                $this->app['session.store'],
                $app[RegistrationService::class]
            );
        });
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        Auth::provider('external-users', function ($app, array $config) {
            return new ExternalBaseUserProvider($config['model']);
        });

        // Bind and provide the default system user as a singleton to the app instance when needed.
        // This effectively "caches" fetching the user at an app-instance level.
        $this->app->singleton('users.default', function () {
            return User::query()->where('system_name', '=', 'public')->first();
        });
    }
}
