<?php

declare(strict_types=1);

namespace BrewAndBytes\AcornConversionPixels;

use Illuminate\Support\ServiceProvider;

class ConversionPixelsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/conversion-pixels.php', 'conversion-pixels');

        $this->app->singleton(
            ConversionPixels::class,
            fn ($app): ConversionPixels => ConversionPixels::make($app)
        );
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/conversion-pixels.php' => $this->app->configPath('conversion-pixels.php'),
        ], 'conversion-pixels-config');

        $this->app->make(ConversionPixels::class);
    }
}
