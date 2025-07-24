<?php

namespace Lucent;

use Illuminate\Support\ServiceProvider;
use Lucent\Console\Commands\Generators\MakePipeCommand;
use Lucent\Console\Commands\Generators\MakeServiceCommand;

/**
 * @author Damian Ułan <damian.ulan@protonmail.com>
 * @copyright 2025 damianulan
 * @license MIT
 */
class LucentServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/lucent.php', 'lucent');
    }

    /**
     * When this method is apply we have all laravel providers and methods available
     */
    public function boot(): void
    {

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'lucent');

        $this->publishes([
            __DIR__.'/../lang' => $this->app->langPath('vendor/lucent'),
        ], 'lucent-langs');

        $this->publishes([
            __DIR__.'/../config/lucent.php' => config_path('lucent.php'),
        ], 'lucent-config');

        $this->publishes([
            __DIR__.'/../stubs' => base_path('stubs'),
            __DIR__.'/../config/lucent.php' => config_path('lucent.php'),
        ], 'lucent');

        $this->registerBladeDirectives();
        $this->registerCommands();
    }

    public function registerBladeDirectives(): void {}

    public function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeServiceCommand::class,
                MakePipeCommand::class,
            ]);
        }
    }
}
