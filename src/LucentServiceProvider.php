<?php

namespace Lucent;

use Illuminate\Support\ServiceProvider;
use Lucent\Console\Commands\Eloquent\PruneSoftDeletes;
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
        $this->mergeConfigFrom(__DIR__ . '/../config/lucent.php', 'lucent');
    }

    /**
     * When this method is apply we have all laravel providers and methods available
     */
    public function boot(): void
    {

        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'lucent');

        $this->publishes(array(
            __DIR__ . '/../lang' => $this->app->langPath('vendor/lucent'),
        ), 'lucent-langs');

        $this->publishes(array(
            __DIR__ . '/../config/lucent.php' => config_path('lucent.php'),
        ), 'lucent-config');

        $this->publishes(array(
            __DIR__ . '/../stubs' => base_path('stubs'),
            __DIR__ . '/../config/lucent.php' => config_path('lucent.php'),
        ), 'lucent');

        $this->registerCommands();
        $this->overridePurifierConfig();
    }

    public function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands(array(
                MakeServiceCommand::class,
                PruneSoftDeletes::class,
            ));
        }
    }

    public function overridePurifierConfig(): void
    {
        $settings = array_merge(config('purifier.settings'), array(
            'lucent_config' => config('lucent.mews_purifier_setting'),
        ));

        config(array(
            'purifier.settings' => $settings,
        ));
    }
}
