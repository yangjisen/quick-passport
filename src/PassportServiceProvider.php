<?php

namespace YangJiSen\QuickPassport;

use Laravel\Passport\Passport;
use Laravel\Passport\PassportServiceProvider as ServiceProvider;

class PassportServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/passport.php', 'passport');

        parent::register();
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

}
