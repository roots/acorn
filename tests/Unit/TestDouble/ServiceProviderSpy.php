<?php

namespace Roots\Acorn\Tests\Unit\TestDouble;

use Roots\Acorn\Clover\ServiceProvider;

final class ServiceProviderSpy extends ServiceProvider
{
    private $registerCalled = false;

    public function register(): void
    {
        $this->app->bind(self::class, function (): self {
            return $this;
        });
        $this->registerCalled = true;
    }

    public function isRegisterCalled(): bool
    {
        return $this->registerCalled;
    }
}
