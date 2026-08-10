<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\CloverService;
use App\Services\PaymentGatewayInterface;
use App\Services\SquareService;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(PaymentGatewayInterface::class, function ($app) {
            $defaultGateway = config('payment.default', 'square');

            return match ($defaultGateway) {
                'clover' => new CloverService(),
                'square' => new SquareService(),
                default => new CloverService(),
            };
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
