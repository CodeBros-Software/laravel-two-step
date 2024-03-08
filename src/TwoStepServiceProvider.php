<?php

namespace CodeBros\TwoStep;

use CodeBros\TwoStep\Http\Middleware\TwoStepMiddleware;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class TwoStepServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(Router $router)
    {
        $router->middlewareGroup('two-step', [TwoStepMiddleware::class]);
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang/', 'laravel-two-step');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views/', 'laravel-two-step');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-two-step.php', 'laravel-two-step');
        $this->publishFiles();
    }

    /**
     * Publish files for Laravel 2-Step Verification.
     *
     * @return void
     */
    private function publishFiles()
    {
        $publishTag = 'laravel2step';

        $this->publishes([
            __DIR__ . '/../config/laravel-two-step.php' => base_path('config/laravel-two-step.php'),
        ], $publishTag);

        $this->publishes([
            __DIR__.'/../database/migrations/' => base_path('/database/migrations'),
        ], $publishTag);

        $this->publishes([
            __DIR__.'/../public/css' => public_path('css/laravel-two-step'),
        ], $publishTag);

        $this->publishes([
            __DIR__.'/../resources/assets/scss' => resource_path('assets/scss/laravel-two-step'),
        ], $publishTag);

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/laravel-two-step'),
        ], $publishTag);

        $this->publishes([
            __DIR__.'/../resources/lang' => base_path('resources/lang/vendor/laravel-two-step'),
        ], $publishTag);
    }
}
