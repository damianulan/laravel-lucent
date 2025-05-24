<?php

namespace Lucent;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\Support\Facades\Blade;
use Lucent\Console\Commands\Generators\MakeServiceCommand;

/**
 * Undocumented class
 *
 * @author Damian Ułan <damian.ulan@protonmail.com>
 * @copyright 2025 damianulan
 * @package Lucent
 * @license MIT
 */
class Provider extends ServiceProvider
{
    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/lucent.php', 'lucent');
    }

    /**
     * When this method is apply we have all laravel providers and methods available
     */
    public function boot(): void
    {

        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'lucent');

        //$this->loadViewsFrom(__DIR__ . '/../resources/views', 'lucent');

        $this->publishes([
            __DIR__ . '/../lang'                   => $this->app->langPath('vendor/lucent'),
        ], 'lucent-langs');

        $this->publishes([
            __DIR__ . '/../config/lucent.php'      => config_path('lucent.php'),
        ], 'lucent-config');

        // $this->publishes([
        //     __DIR__ . '/Views'                     => resource_path('views/vendor/lucent'),
        // ], 'lucent-views');

        // $this->publishes([
        //     __DIR__ . '/../resources/style'        => resource_path('vendor/lucent/style'),
        // ], 'lucent-resources');

        $this->publishes([
            __DIR__ . '/../stubs'                  => base_path('stubs'),
            __DIR__ . '/../config/lucent.php'      => config_path('lucent.php'),
            //__DIR__ . '/../resources/style'        => resource_path('vendor/lucent/style'),
        ], 'lucent');

        $this->registerBladeDirectives();
    }

    public function registerBladeDirectives(): void {}

    public function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeServiceCommand::class,
            ]);
        }
    }
}
