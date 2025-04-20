<?php

namespace Luinuxscl\AiPosts;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class AiPostsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        // Publicar configuración
        $this->publishes([
            __DIR__.'/../config/ai-posts.php' => config_path('ai-posts.php'),
        ], 'ai-posts-config');

        // Publicar migraciones
        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations'),
        ], 'ai-posts-migrations');

        // Cargar migraciones
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Registrar rutas
        $this->registerRoutes();
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        // Fusionar configuración
        $this->mergeConfigFrom(
            __DIR__.'/../config/ai-posts.php', 'ai-posts'
        );

        // Registrar la fachada principal
        $this->app->singleton('ai-posts', function ($app) {
            return new AiPosts();
        });

        // Registrar servicios
        $this->registerServices();
        
        // Registrar el proveedor de eventos
        $this->app->register(Providers\EventServiceProvider::class);
    }

    /**
     * Registrar las rutas del package.
     *
     * @return void
     */
    protected function registerRoutes()
    {
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        });
    }

    /**
     * Obtener la configuración de rutas API del package.
     *
     * @return array
     */
    protected function routeConfiguration()
    {
        return [
            'prefix' => config('ai-posts.api.prefix', 'api'),
            'middleware' => config('ai-posts.api.middleware', ['api', 'auth:sanctum']),
        ];
    }

    /**
     * Registrar los servicios del package.
     *
     * @return void
     */
    protected function registerServices()
    {
        $this->app->singleton('ai-posts.state-machine', function ($app) {
            return new Services\StateMachine(config('ai-posts.states'));
        });

        $this->app->singleton('ai-posts.service', function ($app) {
            return new Services\AiPostService(
                $app->make('ai-posts.state-machine')
            );
        });
        
        $this->app->singleton('ai-posts.export', function ($app) {
            return new Services\ExportService();
        });


    }
}
