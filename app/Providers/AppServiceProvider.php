<?php

namespace App\Providers;

use App\Domain\PostalCode\PostalCodeGatewayInterface;
use App\Infra\PostalCode\ViaCepGateway;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PostalCodeGatewayInterface::class, ViaCepGateway::class);
    }

    public function boot(): void
    {
        //
    }
}
