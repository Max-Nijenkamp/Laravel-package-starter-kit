<?php

namespace MaxNijenkamp\LaravelPackageStarterKit;

use Illuminate\Support\ServiceProvider;
use MaxNijenkamp\LaravelPackageStarterKit\Commands\MakePackageCommand;

class StarterKitServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/starterkit.php',
            'starterkit'
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakePackageCommand::class,
            ]);

            $this->publishes([
                __DIR__ . '/../config/starterkit.php' => config_path('starterkit.php'),
            ], 'starterkit-config');

            $this->publishes([
                __DIR__ . '/../stubs' => base_path('stubs/starterkit'),
            ], 'starterkit-stubs');
        }
    }
}

