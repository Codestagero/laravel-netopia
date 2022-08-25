<?php

namespace iRealWorlds\Netopia\Providers;

use Illuminate\Support\ServiceProvider;

class NetopiaServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../../config/netopia.php' => $this->app->configPath('netopia.php'),
        ]);

        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/netopia.php',
            'netopia'
        );
    }
}
