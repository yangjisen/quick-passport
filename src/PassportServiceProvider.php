<?php

namespace YangJiSen\QuickPassport;

use Illuminate\Config\Repository as Config;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Http\Controllers\AuthorizationController;
use Laravel\Passport\Passport;
use Laravel\Passport\PassportServiceProvider as ServiceProvider;
use YangJiSen\QuickPassport\Http\Controllers\PassportController;

class PassportServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/passport.php', 'passport');

        Passport::setClientUuids($this->app->make(Config::class)->get('passport.client_uuids', false));

        $this->app->when(AuthorizationController::class)
            ->needs(StatefulGuard::class)
            ->give(fn () => Auth::guard(config('passport.guard', null)));

        $this->registerAuthorizationServer();
        $this->registerClientRepository();
        $this->registerJWTParser();
        $this->registerResourceServer();
        $this->registerGuard();

        Passport::authorizationView('passport::authorize');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        /* 配置有效期 */
        Passport::tokensExpireIn(now()->addDays(config('passport.tokens_expire_in', 7)));
        Passport::refreshTokensExpireIn(now()->addDays(config('passport.refresh_tokens_expire_in', 15)));
        Passport::personalAccessTokensExpireIn(now()->addDays(config('passport.personal_expire_id', 7)));

        /* 不注册路由 */
        Passport::$registersRoutes = false;

        parent::boot();
    }

    /**
     * Register the client repository.
     *
     * @return void
     */
    protected function registerClientRepository(): void
    {
        /* 获取客户端进行缓存 */
        $this->app->singleton(
            'Laravel\Passport\ClientRepository',
            'YangJiSen\QuickPassport\ClientRepository'
        );

        /* 获取用户进行缓存 */
        $this->app->singleton(
            'Laravel\Passport\TokenRepository',
            'YangJiSen\QuickPassport\TokenRepository'
        );
    }

    /**
     * Register the Passport Artisan commands.
     *
     * @return void
     */
    protected function registerCommands(): void
    {
        parent::registerCommands();
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\EnvClient::class,
                Console\QuickInstall::class
            ]);
        }
    }

    /**
     * Register the Passport routes.
     *
     * @return void
     */
    protected function registerRoutes(): void
    {
        Route::namespace('YangJiSen\QuickPassport\Http\Controllers')
            ->prefix(config('passport.path', 'passport'))
            ->group(function (Router $router) {
                $router->post('/issueToken', [PassportController::class, 'issueToken']);
                $router->post('/programToken', [PassportController::class, 'programToken']);
            });

        parent::registerRoutes();
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    protected function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $basePath = base_path('vendor/laravel/passport');

            $this->publishes([
                $basePath.'/database/migrations' => database_path('migrations'),
            ], 'passport-migrations');

            $this->publishes([
                $basePath.'/resources/views' => base_path('resources/views/vendor/passport'),
            ], 'passport-views');

            $this->publishes([
                __DIR__.'/../config/passport.php' => config_path('passport.php'),
            ], 'passport-config');
        }
    }

}
