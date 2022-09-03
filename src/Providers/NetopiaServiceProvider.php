<?php

namespace Codestage\Netopia\Providers;

use Codestage\Netopia\Contracts\PaymentService;
use Codestage\Netopia\Services\DefaultPaymentService;
use Illuminate\Support\ServiceProvider;

class NetopiaServiceProvider extends ServiceProvider
{
    /**
     * All the container bindings that should be registered.
     *
     * @var array
     */
    public array $bindings = [
        PaymentService::class => DefaultPaymentService::class
    ];

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

        // Register views
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'netopia');

        // Register migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }
}
